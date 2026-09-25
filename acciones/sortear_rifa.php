<?php

require_once "../conexion.php";
session_start();


// ==================================================
// VERIFICAR SESIÓN
// ==================================================

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit;
}

$id_usuario = $_SESSION["id_usuario"];


// ==================================================
// OBTENER ID DE LA RIFA
// ==================================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: ../pages/mis_rifas.php");
    exit;
}

$id_rifa = (int) $_GET["id"];


// ==================================================
// OBTENER RIFA
// SOLO SI PERTENECE AL USUARIO
// ==================================================

$consulta = $conexion->prepare("
    SELECT
        id_rifa,
        titulo,
        fecha_sorteo,
        estado,
        numero_ganador
    FROM rifas
    WHERE id_rifa = ?
    AND id_usuario = ?
    LIMIT 1
");

$consulta->bind_param(
    "ii",
    $id_rifa,
    $id_usuario
);

$consulta->execute();

$resultado = $consulta->get_result();


// ==================================================
// VERIFICAR QUE EXISTA
// ==================================================

if ($resultado->num_rows === 0) {

    $consulta->close();

    header("Location: ../pages/mis_rifas.php");
    exit;
}

$rifa = $resultado->fetch_assoc();

$consulta->close();


// ==================================================
// VERIFICAR FECHA DEL SORTEO
// ==================================================

$hoy = date("Y-m-d");

$fecha_sorteo = !empty($rifa["fecha_sorteo"])
    ? date("Y-m-d", strtotime($rifa["fecha_sorteo"]))
    : null;

if ($fecha_sorteo !== $hoy) {

    header(
        "Location: ../pages/administrar_rifa.php?id=" . $id_rifa
    );

    exit;
}


// ==================================================
// VERIFICAR SI YA FUE SORTEADA
// ==================================================

if (
    !empty($rifa["numero_ganador"]) ||
    strtolower(trim($rifa["estado"])) === "finalizada"
) {

    header(
        "Location: ../pages/administrar_rifa.php?id=" . $id_rifa
    );

    exit;
}


// ==================================================
// OBTENER NÚMEROS VENDIDOS
// ==================================================

$consulta_numeros = $conexion->prepare("
    SELECT
        nr.id_numero,
        nr.numero
    FROM numeros_rifa nr
    WHERE nr.id_rifa = ?
    AND (
        LOWER(nr.estado) = 'vendido'
        OR LOWER(nr.estado) = 'vendida'
    )
");

$consulta_numeros->bind_param(
    "i",
    $id_rifa
);

$consulta_numeros->execute();

$resultado_numeros = $consulta_numeros->get_result();


// ==================================================
// VERIFICAR QUE HAYA PARTICIPANTES
// ==================================================

if ($resultado_numeros->num_rows === 0) {

    $consulta_numeros->close();

    header(
        "Location: ../pages/administrar_rifa.php?id=" . $id_rifa
    );

    exit;
}


// ==================================================
// GUARDAR NÚMEROS VENDIDOS
// ==================================================

$numeros_vendidos = [];

while ($fila = $resultado_numeros->fetch_assoc()) {

    $numeros_vendidos[] = $fila;
}

$consulta_numeros->close();


// ==================================================
// ELEGIR GANADOR
// ==================================================

$indice_ganador = random_int(
    0,
    count($numeros_vendidos) - 1
);

$ganador = $numeros_vendidos[$indice_ganador];

$id_numero_ganador = (int) $ganador["id_numero"];
$numero_ganador = (int) $ganador["numero"];


// ==================================================
// BUSCAR PARTICIPANTE GANADOR
// ==================================================

$consulta_ganador = $conexion->prepare("
    SELECT
        p.id_usuario
    FROM participaciones p
    WHERE p.id_numero = ?
    ORDER BY p.id_participacion DESC
    LIMIT 1
");

$consulta_ganador->bind_param(
    "i",
    $id_numero_ganador
);

$consulta_ganador->execute();

$resultado_ganador = $consulta_ganador->get_result();


// ==================================================
// VERIFICAR QUE EL NÚMERO TENGA PARTICIPANTE
// ==================================================

if ($resultado_ganador->num_rows === 0) {

    $consulta_ganador->close();

    header(
        "Location: ../pages/administrar_rifa.php?id=" . $id_rifa
    );

    exit;
}

$fila_ganador = $resultado_ganador->fetch_assoc();

$id_usuario_ganador = (int) $fila_ganador["id_usuario"];

$consulta_ganador->close();


// ==================================================
// GUARDAR RESULTADO EN LA RIFA
// ==================================================

$fecha_realizacion = date("Y-m-d H:i:s");

$actualizar = $conexion->prepare("
    UPDATE rifas
    SET
        numero_ganador = ?,
        id_ganador = ?,
        fecha_resultado = ?,
        estado = 'finalizada'
    WHERE id_rifa = ?
    AND id_usuario = ?
    AND numero_ganador IS NULL
");

$actualizar->bind_param(
    "iisii",
    $numero_ganador,
    $id_usuario_ganador,
    $fecha_realizacion,
    $id_rifa,
    $id_usuario
);

$actualizar->execute();

$actualizar->close();


// ==================================================
// CREAR NOTIFICACIÓN
// ==================================================

$tipo_notificacion = "ganador_rifa";

$titulo_notificacion =
    "¡Ganaste una rifa!";

$mensaje_notificacion =
    "¡Felicitaciones! Ganaste la rifa \"" .
    $rifa["titulo"] .
    "\" con el número #" .
    $numero_ganador .
    ".";

$enlace_notificacion =
    "resultado_participacion.php?id=" . $id_rifa;

$leida = 0;


// ==================================================
// INSERTAR NOTIFICACIÓN
// ==================================================

$notificacion = $conexion->prepare("
    INSERT INTO notificaciones
    (
        id_usuario,
        id_rifa,
        tipo,
        titulo,
        mensaje,
        enlace,
        leida,
        fecha_creacion
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$notificacion->bind_param(
    "iissssis",
    $id_usuario_ganador,
    $id_rifa,
    $tipo_notificacion,
    $titulo_notificacion,
    $mensaje_notificacion,
    $enlace_notificacion,
    $leida,
    $fecha_realizacion
);

$notificacion->execute();

$notificacion->close();


// ==================================================
// IR AL RESULTADO
// ==================================================

header(
    "Location: ../pages/resultado_rifa.php?id=" . $id_rifa
);

exit;

?>
