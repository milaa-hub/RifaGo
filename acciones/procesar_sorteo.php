
<?php
require_once "../conexion.php";
session_start();

date_default_timezone_set("America/Argentina/Buenos_Aires");

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

function responder(int $codigo, string $mensaje, array $datos = []): void
{
    http_response_code($codigo);

    echo json_encode(
        array_merge([
            "ok" => false,
            "mensaje" => $mensaje
        ], $datos),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

// --------------------------------------------------
// VALIDAR SOLICITUD Y SESIÓN
// --------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responder(405, "Método no permitido.");
}

if (empty($_SESSION["id_usuario"])) {
    responder(401, "Tu sesión venció. Volvé a iniciar sesión.");
}

$entrada = json_decode(file_get_contents("php://input"), true);

if (!is_array($entrada)) {
    responder(400, "La solicitud no es válida.");
}

$csrf = (string) ($entrada["csrf"] ?? "");

if (
    empty($_SESSION["csrf_sorteo"]) ||
    !hash_equals($_SESSION["csrf_sorteo"], $csrf)
) {
    responder(
        403,
        "La sesión de seguridad venció. Recargá la página."
    );
}

$id_rifa = filter_var(
    $entrada["id_rifa"] ?? null,
    FILTER_VALIDATE_INT
);

if (!$id_rifa || $id_rifa < 1) {
    responder(400, "El ID de la rifa no es válido.");
}

$id_usuario = (int) $_SESSION["id_usuario"];

// --------------------------------------------------
// COMPROBAR QUE LA RIFA PERTENECE AL USUARIO
// --------------------------------------------------

$stmt = $conexion->prepare("
    SELECT id_rifa, titulo, fecha_sorteo, estado, numero_ganador
    FROM rifas
    WHERE id_rifa = ? AND id_usuario = ?
    LIMIT 1
");

if (!$stmt) {
    responder(500, "No se pudo consultar la rifa.");
}

$stmt->bind_param("ii", $id_rifa, $id_usuario);
$stmt->execute();

$rifa = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$rifa) {
    responder(404, "La rifa no existe o no te pertenece.");
}

// --------------------------------------------------
// VALIDAR FECHA Y ESTADO
// --------------------------------------------------

$fecha = !empty($rifa["fecha_sorteo"])
    ? date("Y-m-d", strtotime($rifa["fecha_sorteo"]))
    : "";

if ($fecha !== date("Y-m-d")) {
    responder(
        409,
        "El sorteo solo se puede realizar en la fecha programada."
    );
}

if (
    $rifa["numero_ganador"] !== null ||
    strtolower(trim($rifa["estado"])) === "finalizada"
) {
    responder(409, "La rifa ya fue sorteada.");
}

// --------------------------------------------------
// OBTENER LOS NÚMEROS VENDIDOS Y SUS PARTICIPANTES
// --------------------------------------------------

$stmt = $conexion->prepare("
    SELECT nr.id_numero, nr.numero, p.id_usuario
    FROM numeros_rifa nr
    INNER JOIN participaciones p
        ON p.id_numero = nr.id_numero
    WHERE nr.id_rifa = ?
      AND LOWER(nr.estado) IN ('vendido', 'vendida')
      AND LOWER(p.estado) IN ('vendido', 'vendida', 'confirmada', 'pagada')
    ORDER BY p.id_participacion DESC
");

if (!$stmt) {
    responder(500, "No se pudieron consultar los participantes.");
}

$stmt->bind_param("i", $id_rifa);
$stmt->execute();

$resultado = $stmt->get_result();
$vendidos = [];

while ($fila = $resultado->fetch_assoc()) {
    $id_numero = (int) $fila["id_numero"];

    // Conservar una sola participación por número.
    if (!isset($vendidos[$id_numero])) {
        $vendidos[$id_numero] = [
            "id_numero" => $id_numero,
            "numero" => (int) $fila["numero"],
            "id_usuario" => (int) $fila["id_usuario"]
        ];
    }
}

$stmt->close();

if (empty($vendidos)) {
    responder(
        409,
        "No hay números vendidos con participantes válidos. La rifa sigue pendiente."
    );
}

// --------------------------------------------------
// ELEGIR UN NÚMERO GANADOR AL AZAR
// --------------------------------------------------

$participantes = array_values($vendidos);

$indiceGanador = random_int(0, count($participantes) - 1);

$ganador = $participantes[$indiceGanador];

$numeroGanador = $ganador["numero"];
$idGanador = $ganador["id_usuario"];

// --------------------------------------------------
// GUARDAR GANADOR Y NOTIFICACIÓN
// --------------------------------------------------

try {
    $conexion->begin_transaction();

    // Bloquear y actualizar solamente si la rifa sigue pendiente.
    $stmt = $conexion->prepare("
        UPDATE rifas
        SET numero_ganador = ?,
            id_ganador = ?,
            fecha_resultado = ?,
            estado = 'finalizada'
        WHERE id_rifa = ?
          AND id_usuario = ?
          AND numero_ganador IS NULL
          AND LOWER(estado) <> 'finalizada'
          AND fecha_sorteo = ?
    ");

    if (!$stmt) {
        throw new RuntimeException(
            "No se pudo preparar el guardado del ganador."
        );
    }

    $fechaRealizacion = date("Y-m-d H:i:s");

    $stmt->bind_param(
        "iisiss",
        $numeroGanador,
        $idGanador,
        $fechaRealizacion,
        $id_rifa,
        $id_usuario,
        $fecha
    );

    $stmt->execute();
    $afectadas = $stmt->affected_rows;
    $stmt->close();

    if ($afectadas !== 1) {
        throw new RuntimeException(
            "La rifa ya fue sorteada o cambió de estado."
        );
    }

    // Crear notificación para el ganador.
    $tipo = "ganador_rifa";
    $tituloNotificacion = "¡Ganaste una rifa!";

    $mensaje = '¡Felicitaciones! Ganaste la rifa "'
        . $rifa["titulo"]
        . '" con el número #'
        . $numeroGanador
        . ".";

    $enlace = "../pages/resultado_participacion.php?id=" . $id_rifa;
    $leida = 0;

    $stmt = $conexion->prepare("
        INSERT INTO notificaciones
        (
            id_usuario, id_rifa, tipo, titulo,
            mensaje, enlace, leida, fecha_creacion
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new RuntimeException(
            "No se pudo preparar la notificación."
        );
    }

    $stmt->bind_param(
        "iissssis",
        $idGanador,
        $id_rifa,
        $tipo,
        $tituloNotificacion,
        $mensaje,
        $enlace,
        $leida,
        $fechaRealizacion
    );

    $stmt->execute();
    $stmt->close();

    $conexion->commit();

    unset($_SESSION["csrf_sorteo"]);

    echo json_encode([
        "ok" => true,
        "mensaje" => "¡El sorteo se realizó correctamente!",
        "numero_ganador" => $numeroGanador
    ], JSON_UNESCAPED_UNICODE);

    exit;

} catch (Throwable $e) {
    try {
        $conexion->rollback();
    } catch (Throwable $errorRollback) {
        error_log("RifaGo - error al cancelar transacción: "
            . $errorRollback->getMessage());
    }

    error_log(
        "RifaGo - error al guardar ganador: " . $e->getMessage()
    );

    responder(
        500,
        "No se pudo guardar el resultado. La rifa sigue pendiente."
    );
}
