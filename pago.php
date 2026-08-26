<?php

require_once "conexion.php";
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

if (
    !isset($_POST['id_rifa']) ||
    !isset($_POST['numeros']) ||
    empty($_POST['numeros'])
) {
    header("Location: index.php");
    exit;
}

$id_rifa = intval($_POST['id_rifa']);

$numeros = array_map(
    'intval',
    $_POST['numeros']
);


/* DATOS RIFA */

$sql = "SELECT * FROM rifas WHERE id_rifa = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_rifa);
$stmt->execute();

$rifa = $stmt->get_result()->fetch_assoc();

if (!$rifa) {
    header("Location: index.php");
    exit;
}

$total = count($numeros) * $rifa['precio_numero'];

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pago - RifaGo</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

</head>

<body>

<div class="app-container">

    <header class="page-header">

        <a
            href="javascript:history.back()"
            class="back-button"
        >
            ←
        </a>

        <h1>Método de pago</h1>

    </header>


    <div class="payment-total">

        <span>
            Total a pagar
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


    <form
        action="procesar_pago.php"
        method="POST"
    >

        <input
            type="hidden"
            name="id_rifa"
            value="<?= $id_rifa ?>"
        >

        <input
            type="hidden"
            name="total"
            value="<?= $total ?>"
        >

        <?php foreach ($numeros as $numero): ?>

            <input
                type="hidden"
                name="numeros[]"
                value="<?= $numero ?>"
            >

        <?php endforeach; ?>


        <h3 class="payment-title">
            Elegí tu método de pago
        </h3>


        <label class="payment-option">

            <input
                type="radio"
                name="metodo_pago"
                value="Mercado Pago"
                checked
            >

            <div>

                <strong>
                    Mercado Pago
                </strong>

                <span>
                    Pagar con Mercado Pago
                </span>

            </div>

        </label>


        <label class="payment-option">

            <input
                type="radio"
                name="metodo_pago"
                value="Transferencia bancaria"
            >

            <div>

                <strong>
                    Transferencia bancaria
                </strong>

                <span>
                    Realizar una transferencia
                </span>

            </div>

        </label>


        <label class="payment-option">

            <input
                type="radio"
                name="metodo_pago"
                value="Tarjeta"
            >

            <div>

                <strong>
                    Tarjeta de crédito/débito
                </strong>

                <span>
                    Pagar con tarjeta
                </span>

            </div>

        </label>


        <button
            type="submit"
            class="btn-primary full-button"
        >
            Pagar $<?= number_format(
                $total,
                0,
                ',',
                '.'
            ) ?>
        </button>

    </form>

</div>

</body>

</html>