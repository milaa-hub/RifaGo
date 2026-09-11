<?php

session_start();

if (
    !isset($_SESSION['compra_exitosa']) ||
    $_SESSION['compra_exitosa'] !== true
) {
    header("Location: ../index.php");
    exit;
}

$id_rifa =
    $_SESSION['compra_id_rifa'];

$numeros =
    $_SESSION['compra_numeros'];

$total =
    $_SESSION['compra_total'];

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Compra realizada - RifaGo</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

</head>

<body>

<div class="app-container confirmation-page">

    <div class="confirmation-icon">
        ✓
    </div>

    <h1>
        ¡Pago realizado<br>
        con éxito!
    </h1>

    <p>
        Ya estás participando de la rifa 🎉
    </p>


    <div class="confirmation-card">

        <h3>
            Detalle de compra
        </h3>


        <div class="confirmation-row">

            <span>
                Números
            </span>

            <strong>

                <?php foreach ($numeros as $numero): ?>

                    <?= str_pad(
                        $numero,
                        2,
                        '0',
                        STR_PAD_LEFT
                    ) ?>

                    <?php if (
                        $numero !== end($numeros)
                    ): ?>

                        ,

                    <?php endif; ?>

                <?php endforeach; ?>

            </strong>

        </div>


        <div class="confirmation-row">

            <span>
                Total pagado
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


        <div class="confirmation-row">

            <span>
                Fecha
            </span>

            <strong>
                <?= date('d/m/Y - H:i') ?>
            </strong>

        </div>

    </div>


    <a
        href="participaciones.php"
        class="btn-primary full-button"
        style="text-decoration: none"
    >
        Ver mis participaciones
    </a>


    <a
        href="../index.php"
        class="confirmation-home"
    >
        Ir al inicio
    </a>

</div>

</body>

</html>