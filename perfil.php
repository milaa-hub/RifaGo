<?php

require_once "conexion.php";

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Perfil - RifaGo</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

</head>

<body>

<div class="app">

    <!-- HEADER -->

    <header class="header">

        <div class="logo">
            <span class="logo-blue">Rifa</span><span class="logo-red">Go</span><sup>+</sup>
        </div>

        <div class="user-icon">
            <span>SM</span>
        </div>

    </header>


    <main class="main-content">


        <!-- PERFIL -->

        <section class="profile-header">

            <div class="profile-avatar">
                SM
            </div>


            <div class="profile-name">

                <h1>Sofía Martínez</h1>

                <button class="edit-profile">
                    Ver perfil
                </button>

            </div>

        </section>


        <!-- OPCIONES -->

        <section class="profile-menu">


            <button class="profile-option">

                <span class="option-icon">
                    ♙
                </span>

                <span class="option-text">
                    Mis datos
                </span>

                <span class="option-arrow">
                    ›
                </span>

            </button>


            <button class="profile-option">

                <span class="option-icon">
                    ▣
                </span>

                <span class="option-text">
                    Métodos de pago
                </span>

                <span class="option-arrow">
                    ›
                </span>

            </button>


            <button class="profile-option">

                <span class="option-icon">
                    ◷
                </span>

                <span class="option-text">
                    Historial de transacciones
                </span>

                <span class="option-arrow">
                    ›
                </span>

            </button>


            <button class="profile-option">

                <span class="option-icon">
                    ◉
                </span>

                <span class="option-text">
                    Seguridad
                </span>

                <span class="option-arrow">
                    ›
                </span>

            </button>


            <button class="profile-option">

                <span class="option-icon">
                    ⚙
                </span>

                <span class="option-text">
                    Configuración
                </span>

                <span class="option-arrow">
                    ›
                </span>

            </button>


        </section>


        <!-- CERRAR SESIÓN -->

        <button class="logout-button">

            <span>
                ⎋
            </span>

            Cerrar sesión

        </button>


    </main>


    <!-- NAV -->

    <nav class="bottom-nav">

        <a href="index.php" class="nav-item">

            <span class="nav-icon">⌂</span>

            <span>Inicio</span>

        </a>


        <a href="mis_rifas.php" class="nav-item">

            <span class="nav-icon">▤</span>

            <span>Mis rifas</span>

        </a>


        <button
            class="create-button"
            onclick="window.location.href='crear_rifa.php'"
        >
            <span>+</span>
        </button>


        <a href="participaciones.php" class="nav-item">

            <span class="nav-icon">♧</span>

            <span>Participaciones</span>

        </a>


        <a href="perfil.php" class="nav-item active">

            <span class="nav-icon">♙</span>

            <span>Perfil</span>

        </a>

    </nav>

</div>

</body>
</html>