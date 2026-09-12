<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../conexion.php";

session_start();


/* =========================================
   VERIFICAR ID DE LA RIFA
========================================= */

if (!isset($_GET["id"])) {

    header(
        "Location: mis_rifas.php"
    );

    exit;

}


$id_rifa = intval(
    $_GET["id"]
);


/* =========================================
   OBTENER LA RIFA
========================================= */

$sql = "

    SELECT *

    FROM rifas

    WHERE id_rifa = ?

    LIMIT 1

";


$consulta = $conexion->prepare(
    $sql
);


$consulta->bind_param(
    "i",
    $id_rifa
);


$consulta->execute();


$resultado = $consulta->get_result();


/* =========================================
   VERIFICAR SI EXISTE
========================================= */

if ($resultado->num_rows === 0) {

    header(
        "Location: mis_rifas.php"
    );

    exit;

}


$rifa = $resultado->fetch_assoc();


$consulta->close();

/* =========================================
   OBTENER GANADOR
========================================= */

$ganador = null;

if (!empty($rifa["id_ganador"])) {

    $sql_ganador = "

        SELECT
            nombre

        FROM usuarios

        WHERE id_usuario = ?

        LIMIT 1

    ";

    $consulta_ganador = $conexion->prepare(
        $sql_ganador
    );

    $consulta_ganador->bind_param(
        "i",
        $rifa["id_ganador"]
    );

    $consulta_ganador->execute();

    $resultado_ganador =
        $consulta_ganador->get_result();

    if ($resultado_ganador->num_rows > 0) {

        $ganador =
            $resultado_ganador->fetch_assoc();

    }

    $consulta_ganador->close();

}


/* =========================================
   FORMATEAR FECHA
========================================= */

$fecha_sorteo = "Sin fecha";


if (!empty($rifa["fecha_sorteo"])) {

    $fecha_sorteo = date(
        "d/m/Y",
        strtotime(
            $rifa["fecha_sorteo"]
        )
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


    <title>

        Resultado de la rifa

    </title>


    <link
        rel="stylesheet"
        href="../assets/css/resultado.css"
    >

</head>


<body>


<div class="app">


    <!-- =====================================
         HEADER
    ====================================== -->

    <header class="header resultado-header">


        <a
            href="../index.php"
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



    <!-- =====================================
         CONTENIDO
    ====================================== -->

    <main class="resultado-container">


        <!-- =====================================
             ESTADO
        ====================================== -->

        <div class="resultado-status">

            ✓

            <span>

                Rifa finalizada

            </span>

        </div>



        <!-- =====================================
             IMAGEN
        ====================================== -->

        <div class="resultado-image">


            <?php if (
                !empty($rifa["imagen"])
            ): ?>


                <img
                    src="../<?= htmlspecialchars(
                        $rifa["imagen"]
                    ) ?>"
                    alt="<?= htmlspecialchars(
                        $rifa["premio"]
                    ) ?>"
                >


            <?php else: ?>


                <div class="empty-image">

                    Sin imagen

                </div>


            <?php endif; ?>


        </div>



        <!-- =====================================
             INFORMACIÓN PRINCIPAL
        ====================================== -->

        <section class="resultado-info">


            <h2>

                <?= htmlspecialchars(
                    $rifa["titulo"]
                ) ?>

            </h2>


            <p class="premio">

                🏆

                <?= htmlspecialchars(
                    $rifa["premio"]
                ) ?>

            </p>


        </section>



        <!-- =====================================
             GANADOR
        ====================================== -->

        <section class="ganador-card">

            <div class="ganador-icon">

                🏆

            </div>

            <h2>

                Ganador

            </h2>


            <?php if (
                $ganador !== null &&
                !empty($rifa["numero_ganador"])
            ): ?>


                <p class="ganador-nombre">

                    <?= htmlspecialchars(
                        $ganador["nombre"]
                    ) ?>

                </p>


                <p class="ganador-numero">

                    Número ganador:
                    <?= htmlspecialchars(
                        $rifa["numero_ganador"]
                    ) ?>

                </p>


            <?php else: ?>


                <p class="ganador-nombre">

                    Resultado disponible próximamente

                </p>


                <p class="ganador-numero">

                    Número ganador:
                    --

                </p>


            <?php endif; ?>


        </section>



        <!-- =====================================
             DATOS DEL SORTEO
        ====================================== -->

        <section class="datos-sorteo">


            <h2>

                Información del sorteo

            </h2>



            <div class="dato">


                <span>

                    🎁 Premio

                </span>


                <strong>

                    <?= htmlspecialchars(
                        $rifa["premio"]
                    ) ?>

                </strong>


            </div>



            <div class="dato">


                <span>

                    💰 Precio por número

                </span>


                <strong>

                    $<?= number_format(
                        $rifa["precio_numero"],
                        0,
                        ",",
                        "."
                    ) ?>

                </strong>


            </div>



            <div class="dato">


                <span>

                    🎟️ Cantidad de números

                </span>


                <strong>

                    <?= number_format(
                        $rifa["cantidad_numeros"],
                        0,
                        ",",
                        "."
                    ) ?>

                </strong>


            </div>



            <div class="dato">


                <span>

                    📅 Fecha del sorteo

                </span>


                <strong>

                    <?= $fecha_sorteo ?>

                </strong>


            </div>


        </section>



        <!-- =====================================
             DESCRIPCIÓN
        ====================================== -->

        <?php if (
            !empty(
                $rifa["descripcion"]
            )
        ): ?>


            <section class="descripcion-rifa">


                <h2>

                    Descripción

                </h2>


                <p>

                    <?= nl2br(
                        htmlspecialchars(
                            $rifa["descripcion"]
                        )
                    ) ?>

                </p>


            </section>


        <?php endif; ?>



        <!-- =====================================
             VOLVER
        ====================================== -->

        <a
            href="mis_rifas.php"
            class="volver-button"
        >

            Volver a mis rifas

        </a>


    </main>


</div>


</body>

</html>