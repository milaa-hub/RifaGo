<?php

require_once "conexion.php";

session_start();

$usuario_logueado = isset($_SESSION['id_usuario']);


// ==================================================
// OBTENER LAS RIFAS DEL USUARIO
// ==================================================

$rifas_activas = [];
$rifas_finalizadas = [];
$rifas_borradores = [];

if ($usuario_logueado) {

    $id_usuario = $_SESSION['id_usuario'];

    $consulta = $conexion->prepare("
        SELECT
            id_rifa,
            titulo,
            descripcion,
            premio,
            imagen,
            precio_numero,
            cantidad_numeros,
            fecha_sorteo,
            estado
        FROM rifas
        WHERE id_usuario = ?
        ORDER BY id_rifa DESC
    ");

    $consulta->bind_param("i", $id_usuario);

    $consulta->execute();

    $resultado = $consulta->get_result();


    while ($rifa = $resultado->fetch_assoc()) {

        $estado = strtolower(trim($rifa['estado']));


        if (
            $estado === "finalizada" ||
            $estado === "finalizado"
        ) {

            $rifas_finalizadas[] = $rifa;

        } elseif (
            $estado === "borrador" ||
            $estado === "borradores"
        ) {

            $rifas_borradores[] = $rifa;

        } else {

            $rifas_activas[] = $rifa;

        }

    }


    $consulta->close();

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mis rifas - RifaGo</title>

    <link rel="stylesheet" href="assets/css/style.css">

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

    <header class="header">

        <div class="logo">

            <span class="logo-blue">Rifa</span><span class="logo-red">Go</span><sup>+</sup>

        </div>


        <div class="user-icon">

            <?php if ($usuario_logueado): ?>

                <span>
                    <?= strtoupper(substr($_SESSION['nombre'] ?? 'U', 0, 2)) ?>
                </span>

            <?php else: ?>

                <span>?</span>

            <?php endif; ?>

        </div>

    </header>


    <!-- CONTENIDO -->

    <main class="main-content">

        <?php if ($usuario_logueado): ?>

            <!-- ========================= -->
            <!-- USUARIO LOGUEADO -->
            <!-- ========================= -->

            <div class="page-title">

                <h1>Mis rifas</h1>

                <p>
                    Administrá las rifas que creaste.
                </p>

            </div>


            <!-- PESTAÑAS -->

            <div class="tabs">

                <button
                    class="tab active"
                    data-tab="activas"
                >
                    Activas
                </button>

                <button
                    class="tab"
                    data-tab="finalizadas"
                >
                    Finalizadas
                </button>

                <button
                    class="tab"
                    data-tab="borradores"
                >
                    Borradores
                </button>

            </div>


            <!-- ==========================================
                 RIFAS ACTIVAS
            =========================================== -->

            <section
                class="raffle-list tab-content active"
                id="activas"
            >

                <?php if (count($rifas_activas) > 0): ?>


                    <?php foreach ($rifas_activas as $rifa): ?>


                        <article class="my-raffle-card">

                            <!-- IMAGEN -->

                            <div class="my-raffle-image">

                                <?php if (!empty($rifa['imagen'])): ?>

                                    <img
                                        src="<?= htmlspecialchars($rifa['imagen']) ?>"
                                        alt="<?= htmlspecialchars($rifa['premio']) ?>"
                                    >

                                <?php else: ?>

                                    <div class="empty-image">
                                        Sin imagen
                                    </div>

                                <?php endif; ?>

                            </div>


                            <!-- INFORMACIÓN -->

                            <div class="my-raffle-info">

                                <div>

                                    <h2>
                                        <?= htmlspecialchars($rifa['titulo']) ?>
                                    </h2>

                                    <p class="raffle-price">

                                        $<?= number_format(
                                            $rifa['precio_numero'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                        por número

                                    </p>

                                </div>


                                <div class="raffle-details">

                                    <p>

                                        <strong>Sorteo:</strong>

                                        <?= date(
                                            "d/m/Y",
                                            strtotime($rifa['fecha_sorteo'])
                                        ) ?>

                                    </p>


                                    <p>

                                        <strong>Números:</strong>

                                        <?= number_format(
                                            $rifa['cantidad_numeros'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    </p>

                                </div>


                                <!-- ESTADO -->

                                <div class="my-raffle-status">

                                    <?= htmlspecialchars(
                                        ucfirst($rifa['estado'])
                                    ) ?>

                                </div>


                                <a
                                    href="detalle_rifa.php?id=<?= $rifa['id_rifa'] ?>"
                                    class="secondary-button"
                                >
                                    Ver rifa
                                </a>

                            </div>

                        </article>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="empty-state">

                        <div class="empty-icon">
                            +
                        </div>

                        <h2>
                            No tenés rifas activas
                        </h2>

                        <p>
                            Cuando crees una rifa,
                            aparecerá acá.
                        </p>

                        <a
                            href="crear_rifa.php"
                            class="primary-button"
                        >
                            Crear una rifa
                        </a>

                    </div>


                <?php endif; ?>

            </section>


            <!-- ==========================================
                 FINALIZADAS
            =========================================== -->

            <section
                class="raffle-list tab-content"
                id="finalizadas"
            >

                <?php if (count($rifas_finalizadas) > 0): ?>


                    <?php foreach ($rifas_finalizadas as $rifa): ?>


                        <article class="my-raffle-card">

                            <div class="my-raffle-image">

                                <?php if (!empty($rifa['imagen'])): ?>

                                    <img
                                        src="<?= htmlspecialchars($rifa['imagen']) ?>"
                                        alt="<?= htmlspecialchars($rifa['premio']) ?>"
                                    >

                                <?php else: ?>

                                    <div class="empty-image">
                                        Sin imagen
                                    </div>

                                <?php endif; ?>

                            </div>


                            <div class="my-raffle-info">

                                <div>

                                    <h2>
                                        <?= htmlspecialchars($rifa['titulo']) ?>
                                    </h2>

                                    <p class="raffle-price">

                                        $<?= number_format(
                                            $rifa['precio_numero'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                        por número

                                    </p>

                                </div>


                                <div class="raffle-details">

                                    <p>

                                        <strong>Sorteo:</strong>

                                        <?= date(
                                            "d/m/Y",
                                            strtotime($rifa['fecha_sorteo'])
                                        ) ?>

                                    </p>


                                    <p>

                                        <strong>Números:</strong>

                                        <?= number_format(
                                            $rifa['cantidad_numeros'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    </p>

                                </div>


                                <div class="my-raffle-status">

                                    Finalizada

                                </div>


                                <a
                                    href="detalle_rifa.php?id=<?= $rifa['id_rifa'] ?>"
                                    class="secondary-button"
                                >
                                    Ver rifa
                                </a>

                            </div>

                        </article>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="empty-state">

                        <div class="empty-icon">
                            ✓
                        </div>

                        <h2>
                            No hay rifas finalizadas
                        </h2>

                        <p>
                            Cuando una de tus rifas termine,
                            aparecerá acá.
                        </p>

                    </div>


                <?php endif; ?>

            </section>


            <!-- ==========================================
                 BORRADORES
            =========================================== -->

            <section
                class="raffle-list tab-content"
                id="borradores"
            >

                <?php if (count($rifas_borradores) > 0): ?>


                    <?php foreach ($rifas_borradores as $rifa): ?>


                        <article class="my-raffle-card">

                            <div class="my-raffle-image">

                                <?php if (!empty($rifa['imagen'])): ?>

                                    <img
                                        src="<?= htmlspecialchars($rifa['imagen']) ?>"
                                        alt="<?= htmlspecialchars($rifa['premio']) ?>"
                                    >

                                <?php else: ?>

                                    <div class="empty-image">
                                        Sin imagen
                                    </div>

                                <?php endif; ?>

                            </div>


                            <div class="my-raffle-info">

                                <div>

                                    <h2>
                                        <?= htmlspecialchars($rifa['titulo']) ?>
                                    </h2>

                                    <p class="raffle-price">

                                        $<?= number_format(
                                            $rifa['precio_numero'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                        por número

                                    </p>

                                </div>


                                <div class="my-raffle-status">

                                    Borrador

                                </div>


                                <a
                                    href="detalle_rifa.php?id=<?= $rifa['id_rifa'] ?>"
                                    class="secondary-button"
                                >
                                    Ver rifa
                                </a>

                            </div>

                        </article>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="empty-state">

                        <div class="empty-icon">
                            +
                        </div>

                        <h2>
                            No tenés borradores
                        </h2>

                        <p>
                            Las rifas que guardes como borrador
                            aparecerán acá.
                        </p>

                        <a
                            href="crear_rifa.php"
                            class="primary-button"
                        >
                            Crear una rifa
                        </a>

                    </div>


                <?php endif; ?>

            </section>


        <?php else: ?>

            <!-- ========================= -->
            <!-- USUARIO SIN CUENTA -->
            <!-- ========================= -->

            <section class="empty-state">

                <div class="empty-icon">
                    +
                </div>

                <h2>
                    Creá tu cuenta para crear rifas
                </h2>

                <p>
                    Registrate en RifaGo para crear,
                    administrar y seguir tus propias rifas.
                </p>

                <a
                    href="registro.php"
                    class="primary-button"
                >
                    Crear una cuenta
                </a>

                <br><br>

                <a
                    href="login.php"
                    class="secondary-button"
                >
                    Ya tengo una cuenta
                </a>

            </section>

        <?php endif; ?>

    </main>


    <!-- NAVEGACIÓN -->

    <nav class="bottom-nav">

        <a
            href="index.php"
            class="nav-item"
        >

            <span class="nav-icon">⌂</span>

            <span>Inicio</span>

        </a>


        <a
            href="mis_rifas.php"
            class="nav-item active"
        >

            <span class="nav-icon">▤</span>

            <span>Mis rifas</span>

        </a>


        <button
            class="create-button"
            onclick="window.location.href='crear_rifa.php'"
        >

            <span>+</span>

        </button>


        <a
            href="participaciones.php"
            class="nav-item"
        >

            <span class="nav-icon">♧</span>

            <span>Participaciones</span>

        </a>


        <a
            href="perfil.php"
            class="nav-item"
        >

            <span class="nav-icon">♙</span>

            <span>Perfil</span>

        </a>

    </nav>

</div>


<script src="assets/js/mis_rifas.js"></script>

</body>

</html>