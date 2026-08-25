<?php

require_once "conexion.php";
session_start();

$usuario_logueado = isset($_SESSION['id_usuario']);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mis participaciones - RifaGo</title>

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

            <?php if ($usuario_logueado): ?>

                <span>SM</span>

            <?php else: ?>

                <span>?</span>

            <?php endif; ?>

        </div>

    </header>


    <main class="main-content">

        <?php if ($usuario_logueado): ?>

            <!-- ========================= -->
            <!-- USUARIO LOGUEADO -->
            <!-- ========================= -->

            <div class="page-title">

                <h1>Mis participaciones</h1>

                <p>
                    Consultá las rifas en las que participás.
                </p>

            </div>


            <!-- TABS -->

            <div class="tabs">

                <button
                    class="participation-tab active"
                    data-tab="participaciones-activas"
                >
                    Activas
                </button>

                <button
                    class="participation-tab"
                    data-tab="participaciones-finalizadas"
                >
                    Finalizadas
                </button>

            </div>


            <!-- ACTIVAS -->

            <section
                class="participation-list participation-content active"
                id="participaciones-activas"
            >


                <article class="participation-card">

                    <div class="participation-image">

                        <img
                            src="assets/img/iphone15promax.webp"
                            alt="iPhone 15 Pro Max"
                        >

                    </div>


                    <div class="participation-info">

                        <div class="participation-header">

                            <h2>iPhone 15 Pro Max</h2>

                            <span class="status active-status">
                                Activa
                            </span>

                        </div>


                        <p class="participation-price">
                            $2.000 por número
                        </p>


                        <div class="participation-data">

                            <div>
                                <span>Números</span>

                                <strong>
                                    07, 13, 24
                                </strong>
                            </div>


                            <div>
                                <span>Sorteo</span>

                                <strong>
                                    30/09/2026
                                </strong>
                            </div>

                        </div>


                        <button class="primary-button participation-button">
                            Ver detalle
                        </button>

                    </div>

                </article>


                <article class="participation-card">

                    <div class="participation-image">

                        <img
                            src="assets/img/ps5.webp"
                            alt="PlayStation 5"
                        >

                    </div>


                    <div class="participation-info">

                        <div class="participation-header">

                            <h2>PlayStation 5</h2>

                            <span class="status active-status">
                                Activa
                            </span>

                        </div>


                        <p class="participation-price">
                            $1.500 por número
                        </p>


                        <div class="participation-data">

                            <div>
                                <span>Números</span>

                                <strong>
                                    02, 15
                                </strong>
                            </div>


                            <div>
                                <span>Sorteo</span>

                                <strong>
                                    25/09/2026
                                </strong>
                            </div>

                        </div>


                        <button class="primary-button participation-button">
                            Ver detalle
                        </button>

                    </div>

                </article>


                <article class="participation-card">

                    <div class="participation-image">

                        <img
                            src="assets/img/cancun.jpg"
                            alt="Viaje a Cancún"
                        >

                    </div>


                    <div class="participation-info">

                        <div class="participation-header">

                            <h2>Viaje a Cancún</h2>

                            <span class="status active-status">
                                Activa
                            </span>

                        </div>


                        <p class="participation-price">
                            $3.000 por número
                        </p>


                        <div class="participation-data">

                            <div>
                                <span>Números</span>

                                <strong>
                                    10, 11
                                </strong>
                            </div>


                            <div>
                                <span>Sorteo</span>

                                <strong>
                                    24/10/2026
                                </strong>
                            </div>

                        </div>


                        <button class="primary-button participation-button">
                            Ver detalle
                        </button>

                    </div>

                </article>


            </section>


            <!-- FINALIZADAS -->

            <section
                class="participation-list participation-content"
                id="participaciones-finalizadas"
            >

                <div class="empty-state">

                    <div class="empty-icon">
                        ✓
                    </div>

                    <h2>No hay participaciones finalizadas</h2>

                    <p>
                        Tus participaciones que ya hayan
                        terminado aparecerán acá.
                    </p>

                </div>

            </section>


        <?php else: ?>

            <!-- ========================= -->
            <!-- USUARIO SIN CUENTA -->
            <!-- ========================= -->

            <section class="empty-state">

                <div class="empty-icon">
                    ♧
                </div>

                <h2>Registrate para participar</h2>

                <p>
                    Creá una cuenta en RifaGo para
                    participar en rifas, guardar tus números
                    y consultar tus participaciones.
                </p>

                <a href="registro.php" class="primary-button">
                    Crear una cuenta
                </a>

                <br><br>

                <a href="login.php" class="secondary-button">
                    Ya tengo una cuenta
                </a>

            </section>

        <?php endif; ?>

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


        <a href="participaciones.php" class="nav-item active">

            <span class="nav-icon">♧</span>

            <span>Participaciones</span>

        </a>


        <a href="perfil.php" class="nav-item">

            <span class="nav-icon">♙</span>

            <span>Perfil</span>

        </a>

    </nav>

</div>

<script src="assets/js/participaciones.js"></script>

</body>
</html>