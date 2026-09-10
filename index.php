<?php

require_once "conexion.php";
session_start();

$usuario_logueado = isset($_SESSION['id_usuario']);


// ==================================================
// OBTENER RIFAS ACTIVAS
// ==================================================

$rifas = [];

$consulta = $conexion->prepare("
    SELECT
        r.id_rifa,
        r.titulo,
        r.descripcion,
        r.premio,
        r.imagen,
        r.precio_numero,
        r.cantidad_numeros,
        r.fecha_sorteo,

        COUNT(DISTINCT p.id_participacion) AS numeros_vendidos

    FROM rifas r

    LEFT JOIN numeros_rifa nr
        ON r.id_rifa = nr.id_rifa

    LEFT JOIN participaciones p
        ON nr.id_numero = p.id_numero

    WHERE r.estado = 'activa'

    GROUP BY
        r.id_rifa,
        r.titulo,
        r.descripcion,
        r.premio,
        r.imagen,
        r.precio_numero,
        r.cantidad_numeros,
        r.fecha_sorteo

    ORDER BY r.id_rifa DESC
");

$consulta->execute();

$resultado = $consulta->get_result();


// ==================================================
// DETERMINAR CATEGORÍA
// ==================================================

while ($rifa = $resultado->fetch_assoc()) {

    $texto = strtolower(
        $rifa['titulo'] . ' ' .
        $rifa['descripcion'] . ' ' .
        $rifa['premio']
    );


    // Tecnología

    if (
        strpos($texto, 'iphone') !== false ||
        strpos($texto, 'celular') !== false ||
        strpos($texto, 'telefono') !== false ||
        strpos($texto, 'teléfono') !== false ||
        strpos($texto, 'notebook') !== false ||
        strpos($texto, 'laptop') !== false ||
        strpos($texto, 'computadora') !== false ||
        strpos($texto, 'pc') !== false ||
        strpos($texto, 'playstation') !== false ||
        strpos($texto, 'ps5') !== false ||
        strpos($texto, 'ps4') !== false ||
        strpos($texto, 'xbox') !== false ||
        strpos($texto, 'tablet') !== false ||
        strpos($texto, 'ipad') !== false ||
        strpos($texto, 'airpods') !== false ||
        strpos($texto, 'electr') !== false
    ) {

        $rifa['categoria'] = 'tecnologia';

    }


    // Hogar

    elseif (
        strpos($texto, 'heladera') !== false ||
        strpos($texto, 'cocina') !== false ||
        strpos($texto, 'horno') !== false ||
        strpos($texto, 'televisor') !== false ||
        strpos($texto, 'televisión') !== false ||
        strpos($texto, 'television') !== false ||
        strpos($texto, 'sillón') !== false ||
        strpos($texto, 'sillon') !== false ||
        strpos($texto, 'mueble') !== false ||
        strpos($texto, 'cafetera') !== false ||
        strpos($texto, 'hogar') !== false ||
        strpos($texto, 'electrodoméstico') !== false ||
        strpos($texto, 'electrodomestico') !== false
    ) {

        $rifa['categoria'] = 'hogar';

    }


    // Viajes

    elseif (
        strpos($texto, 'viaje') !== false ||
        strpos($texto, 'viajes') !== false ||
        strpos($texto, 'hotel') !== false ||
        strpos($texto, 'vacaciones') !== false ||
        strpos($texto, 'cancún') !== false ||
        strpos($texto, 'cancun') !== false ||
        strpos($texto, 'avión') !== false ||
        strpos($texto, 'avion') !== false ||
        strpos($texto, 'vuelo') !== false ||
        strpos($texto, 'turismo') !== false
    ) {

        $rifa['categoria'] = 'viajes';

    }


    // Otros

    else {

        $rifa['categoria'] = 'mas';

    }


    $rifas[] = $rifa;
}


$consulta->close();


// ==================================================
// FUNCIÓN PARA ESCAPAR TEXTO
// ==================================================

function escapar($texto)
{
    return htmlspecialchars(
        $texto ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>RifaGo</title>


    <link
        rel="stylesheet"
        href="assets/css/style.css?=v3"
    >


    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

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


    <!-- =========================================
         HEADER
    ========================================== -->

    <header class="header">

        <div class="logo">

            <span class="logo-blue">Rifa</span><span class="logo-red">Go</span><sup>+</sup>

        </div>


        <div class="user-icon">

            <?php if ($usuario_logueado): ?>

                <span>

                    <?= strtoupper(
                        substr(
                            $_SESSION['nombre'] ?? 'U',
                            0,
                            2
                        )
                    ) ?>

                </span>

            <?php else: ?>

                <span>
                    ?
                </span>

            <?php endif; ?>

        </div>

    </header>



    <!-- =========================================
         CONTENIDO
    ========================================== -->

    <main class="main-content">


        <!-- =====================================
             BUSCADOR
        ====================================== -->

        <div class="search-container">

            <form 
                action="pages/busqueda.php" 
                method="GET"
                class="search-box"
            >

                <span class="search-icon">
                    ⌕
                </span>

                <input
                    type="text"
                    id="buscador"
                    name="busqueda"
                    placeholder="Buscar rifas..."
                    aria-label="Buscar rifas"
                    autocomplete="off"
                >

            </form>


            <button
                type="button"
                class="filter-button"
                id="btnFiltro"
                aria-label="Mostrar filtros"
            >
                ☷
            </button>

        </div>



        <!-- =====================================
             FILTRO
        ====================================== -->


        <div class="filter-panel" id="filterPanel">

            <form action="pages/busqueda.php" method="GET">

                <!-- PRECIO -->

                <div class="filtro-grupo">

                    <label>
                        Precio por número
                    </label>

                    <div class="filtro-rango">

                        <input
                            type="number"
                            name="precio_min"
                            placeholder="Desde"
                            min="0"
                        >

                        <input
                            type="number"
                            name="precio_max"
                            placeholder="Hasta"
                            min="0"
                        >

                    </div>

                </div>


                <!-- CANTIDAD DE NÚMEROS -->

                <div class="filtro-grupo">

                    <label>
                        Cantidad de números
                    </label>

                    <div class="filtro-rango">

                        <input
                            type="number"
                            name="cantidad_min"
                            placeholder="Desde"
                            min="1"
                        >

                        <input
                            type="number"
                            name="cantidad_max"
                            placeholder="Hasta"
                            min="1"
                        >

                    </div>

                </div>


                <!-- FECHA DE SORTEO -->

                <div class="filtro-grupo">

                    <label>
                        Fecha de sorteo
                    </label>

                    <div class="filtro-rango">

                        <input
                            type="date"
                            name="fecha_desde"
                        >

                        <input
                            type="date"
                            name="fecha_hasta"
                        >

                    </div>

                </div>


                <!-- APLICAR -->

                <button
                    type="submit"
                    class="primary-button"
                >
                    Aplicar filtros
                </button>

            </form>

        </div>



        <!-- =====================================
             HERO
        ====================================== -->

        <section class="hero-banner">

            <div class="hero-text">

                <h1>

                    ¡Participá y ganá
                    <br>
                    premios increíbles!

                </h1>


                <button
                    type="button"
                    class="hero-button"
                    id="btnVerRifas"
                >
                    Ver rifas
                </button>

            </div>


            <div class="hero-image">

                <div class="gift">
                    🎁
                </div>

            </div>

        </section>



        <!-- =====================================
             CATEGORÍAS
        ====================================== -->

        <section class="section">

            <div class="section-header">

                <h2>
                    Categorías
                </h2>

            </div>


            <div class="categories">

                <button
                    type="button"
                    class="category activa"
                    data-categoria="todas"
                >

                    <div class="category-icon">
                        ◉
                    </div>

                    <span>
                        Todas
                    </span>

                </button>


                <button
                    type="button"
                    class="category"
                    data-categoria="tecnologia"
                >

                    <div class="category-icon">
                        ▣
                    </div>

                    <span>
                        Tecnología
                    </span>

                </button>


                <button
                    type="button"
                    class="category"
                    data-categoria="hogar"
                >

                    <div class="category-icon">
                        ⌂
                    </div>

                    <span>
                        Hogar
                    </span>

                </button>


                <button
                    type="button"
                    class="category"
                    data-categoria="viajes"
                >

                    <div class="category-icon">
                        ✈
                    </div>

                    <span>
                        Viajes
                    </span>

                </button>


                <button
                    type="button"
                    class="category"
                    data-categoria="mas"
                >

                    <div class="category-icon">
                        •••
                    </div>

                    <span>
                        Más
                    </span>

                </button>

            </div>

        </section>



        <!-- =====================================
             RIFAS
        ====================================== -->

        <section
            class="section"
            id="seccionRifas"
        >

            <div class="section-header">

                <h2>
                    Rifas destacadas
                </h2>


                <a
                    href="pages/rifas.php"
                    class="see-more"
                    style=text-decoration: none;
                >
                    Ver todas
                </a>

            </div>


            <div
                class="raffles"
                id="listaRifas"
            >


                <?php if (count($rifas) > 0): ?>


                    <?php foreach ($rifas as $rifa): ?>


                        <?php

                        $cantidad =
                            (int) $rifa['cantidad_numeros'];

                        $vendidos =
                            (int) $rifa['numeros_vendidos'];

                        $porcentaje = 0;

                        if ($cantidad > 0) {

                            $porcentaje =
                                ($vendidos / $cantidad) * 100;

                        }

                        if ($porcentaje > 100) {

                            $porcentaje = 100;

                        }


                        $fecha_sorteo = '';

                        if (
                            !empty(
                                $rifa['fecha_sorteo']
                            )
                        ) {

                            $fecha_sorteo = date(
                                'd/m/Y',
                                strtotime(
                                    $rifa['fecha_sorteo']
                                )
                            );

                        }

                        ?>


                        <article
                            class="raffle-card"
                            data-categoria="<?= escapar(
                                $rifa['categoria']
                            ) ?>"
                            data-titulo="<?= escapar(
                                strtolower(
                                    $rifa['titulo']
                                )
                            ) ?>"
                            data-descripcion="<?= escapar(
                                strtolower(
                                    $rifa['descripcion']
                                )
                            ) ?>"
                            data-premio="<?= escapar(
                                strtolower(
                                    $rifa['premio']
                                )
                            ) ?>"
                            data-fecha="<?= escapar(
                                $rifa['fecha_sorteo']
                            ) ?>"
                            data-vendidos="<?= $vendidos ?>"
                            data-cantidad="<?= $cantidad ?>"
                        >


                            <!-- HACER TODA LA TARJETA CLICKEABLE -->

                            <a
                                href="pages/detalle_rifa.php?id=<?= $rifa['id_rifa'] ?>"
                                class="raffle-card-link"
                            >


                                <!-- IMAGEN -->

                                <div class="raffle-image">

                                    <?php if (
                                        !empty(
                                            $rifa['imagen']
                                        )
                                    ): ?>

                                        <img
                                            src="<?= escapar(
                                                $rifa['imagen']
                                            ) ?>"
                                            alt="<?= escapar(
                                                $rifa['premio']
                                            ) ?>"
                                        >

                                    <?php else: ?>

                                        <div class="empty-image">
                                            Sin imagen
                                        </div>

                                    <?php endif; ?>

                                </div>



                                <!-- INFORMACIÓN -->

                                <div class="raffle-info">

                                    <h3>
                                        <?= escapar(
                                            $rifa['titulo']
                                        ) ?>
                                    </h3>


                                    <p class="raffle-price">

                                        $<?= number_format(
                                            $rifa[
                                                'precio_numero'
                                            ],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                        por número

                                    </p>


                                    <p class="raffle-date">

                                        Sorteo
                                        <?= $fecha_sorteo ?>

                                    </p>



                                    <!-- PROGRESO -->

                                    <div class="progress">

                                        <div class="progress-bar">

                                            <span
                                                style="width: <?= $porcentaje ?>%;"
                                            ></span>

                                        </div>


                                        <small>

                                            <?= $vendidos ?>
                                            /
                                            <?= $cantidad ?>

                                        </small>

                                    </div>

                                </div>

                            </a>

                        </article>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="empty-state">

                        <div class="empty-icon">
                            +
                        </div>


                        <h2>
                            No hay rifas disponibles
                        </h2>


                        <p>
                            Actualmente no hay rifas activas.
                            ¡Podés crear la primera!
                        </p>


                        <?php if ($usuario_logueado): ?>

                            <a
                                href="pages/crear_rifa.php"
                                class="primary-button"
                            >
                                Crear una rifa
                            </a>

                        <?php else: ?>

                            <a
                                href="registro.php"
                                class="primary-button"
                            >
                                Crear una cuenta
                            </a>

                        <?php endif; ?>

                    </div>


                <?php endif; ?>


                <!-- MENSAJE CUANDO LOS FILTROS NO ENCUENTRAN NADA -->

                <div
                    class="empty-state"
                    id="sinResultados"
                    style="display: none;"
                >

                    <div class="empty-icon">
                        ⌕
                    </div>


                    <h2>
                        No encontramos rifas
                    </h2>


                    <p>
                        Probá con otro nombre, premio
                        o categoría.
                    </p>

                </div>


            </div>

        </section>

    </main>



    <!-- =========================================
         NAVEGACIÓN
    ========================================== -->

    <nav class="bottom-nav">


        <a
            href="index.php"
            class="nav-item active"
        >

            <span class="nav-icon">
                ⌂
            </span>

            <span>
                Inicio
            </span>

        </a>



        <a
            href="pages/mis_rifas.php"
            class="nav-item"
        >

            <span class="nav-icon">
                ▤
            </span>

            <span>
                Mis rifas
            </span>

        </a>



                
        <a href="pages/crear_rifa.php" class="create-button" style="text-decoration: none;">
            <span>
                +
            </span>
        </a>


        <a
            href="pages/participaciones.php"
            class="nav-item"
        >

            <span class="nav-icon">
                ♧
            </span>

            <span>
                Participaciones
            </span>

        </a>



        <a
            href="pages/perfil.php"
            class="nav-item"
        >

            <span class="nav-icon">
                ♙
            </span>

            <span>
                Perfil
            </span>

        </a>


    </nav>


</div>


<script src="assets/js/script.js"></script>

</body>

</html>