<?php

require_once "conexion.php";

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mis rifas - RifaGo</title>

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


    <!-- CONTENIDO -->

    <main class="main-content">

        <div class="page-title">

            <h1>Mis rifas</h1>

            <p>
                Administrá las rifas que creaste.
            </p>

        </div>


        <!-- PESTAÑAS -->

        <div class="tabs">

            <button class="tab active" data-tab="activas">
                Activas
            </button>

            <button class="tab" data-tab="finalizadas">
                Finalizadas
            </button>

            <button class="tab" data-tab="borradores">
                Borradores
            </button>

        </div>


        <!-- RIFAS ACTIVAS -->

        <section class="raffle-list tab-content active" id="activas">


            <article class="my-raffle-card">

                <div class="my-raffle-image">

                    <img
                        src="assets/img/macbook.webp"
                        alt="MacBook Air M2"
                    >

                </div>


                <div class="my-raffle-info">

                    <div>

                        <h2>MacBook Air M2</h2>

                        <p class="raffle-price">
                            $1.500 por número
                        </p>

                    </div>


                    <div class="raffle-details">

                        <p>
                            <strong>Sorteo:</strong>
                            16/09/2026
                        </p>

                        <p>
                            <strong>Números:</strong>
                            500 / 1000
                        </p>

                    </div>


                    <div class="my-progress">

                        <div class="my-progress-bar">
                            <span style="width: 50%;"></span>
                        </div>

                        <small>
                            50% vendido
                        </small>

                    </div>


                    <button class="secondary-button">
                        Ver rifa
                    </button>

                </div>

            </article>


            <article class="my-raffle-card">

                <div class="my-raffle-image">

                    <img
                        src="assets/img/bicicleta.webp"
                        alt="Bicicleta Mountain Bike"
                    >

                </div>


                <div class="my-raffle-info">

                    <div>

                        <h2>Bicicleta Mountain Bike</h2>

                        <p class="raffle-price">
                            $1.500 por número
                        </p>

                    </div>


                    <div class="raffle-details">

                        <p>
                            <strong>Sorteo:</strong>
                            05/10/2026
                        </p>

                        <p>
                            <strong>Números:</strong>
                            300 / 800
                        </p>

                    </div>


                    <div class="my-progress">

                        <div class="my-progress-bar">
                            <span style="width: 37.5%;"></span>
                        </div>

                        <small>
                            37,5% vendido
                        </small>

                    </div>


                    <button class="secondary-button">
                        Ver rifa
                    </button>

                </div>

            </article>


            <article class="my-raffle-card">

                <div class="my-raffle-image">

                    <img
                        src="assets/img/smarttv.webp"
                        alt="Smart TV 50 pulgadas"
                    >

                </div>


                <div class="my-raffle-info">

                    <div>

                        <h2>Smart TV 50"</h2>

                        <p class="raffle-price">
                            $1.000 por número
                        </p>

                    </div>


                    <div class="raffle-details">

                        <p>
                            <strong>Sorteo:</strong>
                            20/10/2026
                        </p>

                        <p>
                            <strong>Números:</strong>
                            600 / 1000
                        </p>

                    </div>


                    <div class="my-progress">

                        <div class="my-progress-bar">
                            <span style="width: 60%;"></span>
                        </div>

                        <small>
                            60% vendido
                        </small>

                    </div>


                    <button class="secondary-button">
                        Ver rifa
                    </button>

                </div>

            </article>

        </section>


        <!-- FINALIZADAS -->

        <section class="raffle-list tab-content" id="finalizadas">

            <div class="empty-state">

                <div class="empty-icon">
                    ✓
                </div>

                <h2>No hay rifas finalizadas</h2>

                <p>
                    Cuando una de tus rifas termine,
                    aparecerá acá.
                </p>

            </div>

        </section>


        <!-- BORRADORES -->

        <section class="raffle-list tab-content" id="borradores">

            <div class="empty-state">

                <div class="empty-icon">
                    +
                </div>

                <h2>No tenés borradores</h2>

                <p>
                    Las rifas que guardes como borrador
                    aparecerán acá.
                </p>

                <a href="crear_rifa.php" class="primary-button">
                    Crear una rifa
                </a>

            </div>

        </section>

    </main>


    <!-- NAVEGACIÓN -->

    <nav class="bottom-nav">

        <a href="index.php" class="nav-item">

            <span class="nav-icon">⌂</span>

            <span>Inicio</span>

        </a>


        <a href="mis_rifas.php" class="nav-item active">

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


        <a href="perfil.php" class="nav-item">

            <span class="nav-icon">♙</span>

            <span>Perfil</span>

        </a>

    </nav>

</div>

<script src="assets/js/mis_rifas.js"></script>
</body>
</html>