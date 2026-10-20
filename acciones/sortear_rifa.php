
<?php
require_once "../conexion.php";
session_start();

date_default_timezone_set("America/Argentina/Buenos_Aires");

$hoy = date("Y-m-d");

$fecha_sorteo = !empty($rifa["fecha_sorteo"])
    ? date("Y-m-d", strtotime($rifa["fecha_sorteo"]))
    : null;

$puede_sortear =
    $fecha_sorteo !== null
    && $hoy >= $fecha_sorteo
    && $rifa["estado"] === "activa"
    && empty($rifa["numero_ganador"]);

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit;
}

$id_usuario = (int) $_SESSION["id_usuario"];

if (!isset($_GET["id"]) || !ctype_digit((string) $_GET["id"])) {
    header("Location: ../pages/mis_rifas.php");
    exit;
}

$id_rifa = (int) $_GET["id"];

$stmt = $conexion->prepare("
    SELECT id_rifa, titulo, fecha_sorteo, estado, numero_ganador
    FROM rifas
    WHERE id_rifa = ? AND id_usuario = ?
    LIMIT 1
");

$stmt->bind_param("ii", $id_rifa, $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$rifa = $resultado->fetch_assoc();
$stmt->close();

if (!$rifa) {
    header("Location: ../pages/mis_rifas.php");
    exit;
}

if (
    $fecha_sorteo !== date("Y-m-d") ||
    !empty($rifa["numero_ganador"]) ||
    strtolower(trim($rifa["estado"])) === "finalizada"
) {
    header("Location: ../pages/administrar_rifa.php?id=" . $id_rifa);
    exit;
}

if (empty($_SESSION["csrf_sorteo"])) {
    $_SESSION["csrf_sorteo"] = bin2hex(random_bytes(32));
}

$titulo = htmlspecialchars(
    $rifa["titulo"],
    ENT_QUOTES,
    "UTF-8"
);

$csrf = htmlspecialchars(
    $_SESSION["csrf_sorteo"],
    ENT_QUOTES,
    "UTF-8"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sortear rifa | RifaGo</title>
</head>
<body>
    <main>
        <h1>Sortear rifa</h1>

        <h2><?= $titulo ?></h2>

        <p>
            Fuente: Quiniela de la Ciudad (LOTBA).
            Modalidad: Nocturna.
        </p>

        <p id="estado-sorteo" role="status" aria-live="polite">
            El sorteo todavía no se ha procesado.
        </p>

        <button
            type="button"
            id="btn-sortear"
            data-id-rifa="<?= $id_rifa ?>"
            data-csrf="<?= $csrf ?>"
        >
            Consultar resultado y sortear
        </button>

        <p>
            <a href="../pages/administrar_rifa.php?id=<?= $id_rifa ?>">
                Volver a administrar la rifa
            </a>
        </p>
    </main>

    <script src="../assets/js/quiniela.js=?v5" defer></script>
</body>
</html>
