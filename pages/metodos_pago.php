<?php

require_once "../conexion.php";
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

/* Obtener datos del usuario */

$sql = "SELECT nombre, apellido
        FROM usuarios
        WHERE id_usuario = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();

$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

$stmt->close();

if (!$usuario) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

$iniciales =
    strtoupper(substr($usuario['nombre'], 0, 1)) .
    strtoupper(substr($usuario['apellido'], 0, 1));

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Métodos de pago - RifaGo</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

</head>

<body>

<div class="app">

    <!-- HEADER -->

    <header class="header rifas-header">


        <a
            href="perfil.php"
            class="back-button"
            id="btnVolverCrear"
        >
            ‹
        </a>


        <div class="logo">

            <span class="logo-blue">Rifa</span><span class="logo-red">Go</span><sup>+</sup>

        </div>


        <div class="header-space"></div>


    </header>


    <main class="main-content">

        <section class="page-title">

            <h1>
                Métodos de pago
            </h1>

            <p>
                Elegí cómo querés pagar tus participaciones.
            </p>

        </section>


        <!-- MÉTODOS DISPONIBLES -->

        <section class="payment-menu">


            <div class="payment-option">

                <div class="payment-icon">
                    $
                </div>

                <div class="payment-info">

                    <h3>
                        Mercado Pago
                    </h3>

                    <p>
                        Pagá de forma rápida y segura.
                    </p>

                </div>

            </div>


            <div class="payment-option">

                <div class="payment-icon">
                    $
                </div>

                <div class="payment-info">

                    <h3>
                        Transferencia bancaria
                    </h3>

                    <p>
                        Realizá una transferencia al medio indicado.
                    </p>

                </div>

            </div>


        </section>


        <div class="info-box">

            <span>ⓘ</span>

            <p>
                Los datos de pago se solicitarán al momento
                de comprar una participación.
            </p>

        </div>

    </main>

</div>

</body>
</html>