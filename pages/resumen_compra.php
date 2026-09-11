<?php

require_once "../conexion.php";
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

if (
    !isset($_POST['id_rifa']) ||
    !isset($_POST['numeros']) ||
    empty($_POST['numeros'])
) {
    header("Location: ../index.php");
    exit;
}

$id_rifa = intval($_POST['id_rifa']);

$ids_numeros = $_POST['numeros'];

if (!is_array($ids_numeros)) {
    header("Location: ../index.php");
    exit;
}


/* ============================
   DATOS DE LA RIFA
============================ */

$sql = "SELECT * FROM rifas WHERE id_rifa = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_rifa);
$stmt->execute();

$rifa = $stmt->get_result()->fetch_assoc();

if (!$rifa) {
    header("Location: ../index.php");
    exit;
}


/* ============================
   OBTENER NÚMEROS
============================ */

$placeholders = implode(
    ',',
    array_fill(0, count($ids_numeros), '?')
);

$tipos = str_repeat('i', count($ids_numeros));

$sql = "
    SELECT *
    FROM numeros_rifa
    WHERE id_numero IN ($placeholders)
    AND id_rifa = ?
";

$stmt = $conexion->prepare($sql);

$ids_limpios = array_map('intval', $ids_numeros);

$parametros = $ids_limpios;
$parametros[] = $id_rifa;

$tipos .= 'i';

$stmt->bind_param(
    $tipos,
    ...$parametros
);

$stmt->execute();

$resultado = $stmt->get_result();

$numeros = [];

while ($numero = $resultado->fetch_assoc()) {

    if ($numero['estado'] !== 'disponible') {
        header("Location: seleccion_numero.php?id_rifa=" . $id_rifa);
        exit;
    }

    $numeros[] = $numero;

}


/* ============================
   TOTAL
============================ */

$total = count($numeros) * $rifa['precio_numero'];

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Resumen de compra - RifaGo</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

</head>

<body>

<div class="app-container">

    <header class="page-header">

        <a
            href="seleccion_numero.php?id_rifa=<?= $id_rifa ?>"
            class="back-button"
        >
            ←
        </a>

        <h1>Resumen de compra</h1>

    </header>


    <div class="summary-card">

        <div class="summary-title">
            Números seleccionados
        </div>

        <div class="selected-numbers">

            <?php foreach ($numeros as $numero): ?>

                <span>
                    <?= str_pad(
                        $numero['numero'],
                        2,
                        '0',
                        STR_PAD_LEFT
                    ) ?>
                </span>

            <?php endforeach; ?>

        </div>

    </div>


    <div class="summary-card">

        <div class="summary-title">
            Rifa
        </div>

        <div class="summary-raffle">

            <?php if (!empty($rifa['imagen'])): ?>

                <img
                    src="../<?= htmlspecialchars($rifa['imagen']) ?>"
                    alt="Premio"
                >

            <?php endif; ?>

            <div>

                <strong>
                    <?= htmlspecialchars($rifa['titulo']) ?>
                </strong>

                <p>
                    <?= count($numeros) ?> números seleccionados
                </p>

            </div>

        </div>

    </div>


    <div class="summary-card">

        <div class="summary-row">

            <span>
                Precio por número
            </span>

            <strong>
                $<?= number_format(
                    $rifa['precio_numero'],
                    0,
                    ',',
                    '.'
                ) ?>
            </strong>

        </div>


        <div class="summary-row">

            <span>
                Cantidad
            </span>

            <strong>
                <?= count($numeros) ?>
            </strong>

        </div>


        <div class="summary-total">

            <span>
                Total
            </span>

            <strong>
                $<?= number_format(
                    $total,
                    0,
                    ',',
                    '.'
                ) ?>
            </strong>

        </div>

    </div>


    <form action="pago.php" method="POST">

        <input
            type="hidden"
            name="id_rifa"
            value="<?= $id_rifa ?>"
        >

        <?php foreach ($numeros as $numero): ?>

            <input
                type="hidden"
                name="numeros[]"
                value="<?= $numero['id_numero'] ?>"
            >

        <?php endforeach; ?>


        <button
            type="submit"
            class="btn-primary full-button"
        >
            Continuar al pago
        </button>

    </form>

</div>

</body>

</html>