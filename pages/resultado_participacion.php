<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../conexion.php";

session_start();


// =========================================
// VERIFICAR USUARIO
// =========================================

if (!isset($_SESSION["id_usuario"])) {

    header("Location: ../login.php");
    exit;

}


$id_usuario = intval(
    $_SESSION["id_usuario"]
);


// =========================================
// VERIFICAR ID DE LA RIFA
// =========================================

if (!isset($_GET["id"])) {

    header(
        "Location: participaciones.php"
    );

    exit;

}


$id_rifa = intval(
    $_GET["id"]
);


// =========================================
// OBTENER DATOS DE LA RIFA
// =========================================

$sql = "

    SELECT
        r.id_rifa,
        r.titulo,
        r.descripcion,
        r.premio,
        r.imagen,
        r.precio_numero,
        r.cantidad_numeros,
        r.fecha_sorteo,
        r.estado,
        r.numero_ganador,
        r.id_ganador,
        r.fecha_resultado,

        u.nombre AS ganador_nombre,
        u.apellido AS ganador_apellido

    FROM rifas r

    LEFT JOIN usuarios u
        ON r.id_ganador = u.id_usuario

    WHERE r.id_rifa = ?

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


// =========================================
// VERIFICAR QUE EXISTA
// =========================================

if ($resultado->num_rows === 0) {

    header(
        "Location: participaciones.php"
    );

    exit;

}


$rifa = $resultado->fetch_assoc();


$consulta->close();


// =========================================
// VERIFICAR QUE ESTÉ FINALIZADA
// =========================================

if ($rifa["estado"] !== "finalizada") {

    header(
        "Location: participaciones.php"
    );

    exit;

}


// =========================================
// OBTENER NÚMEROS DEL USUARIO
// =========================================

$sql_numeros = "

    SELECT
        nr.numero

    FROM participaciones p

    INNER JOIN numeros_rifa nr
        ON p.id_numero = nr.id_numero

    WHERE
        p.id_usuario = ?
        AND nr.id_rifa = ?

    ORDER BY nr.numero ASC

";


$consulta_numeros = $conexion->prepare(
    $sql_numeros
);


$consulta_numeros->bind_param(
    "ii",
    $id_usuario,
    $id_rifa
);


$consulta_numeros->execute();


$resultado_numeros =
    $consulta_numeros->get_result();


$numeros_usuario = [];


// =========================================
// GUARDAR NÚMEROS
// =========================================

while (
    $numero =
        $resultado_numeros->fetch_assoc()
) {

    $numeros_usuario[] =
        $numero["numero"];

}


$consulta_numeros->close();


// =========================================
// VERIFICAR QUE EL USUARIO PARTICIPÓ
// =========================================

if (count($numeros_usuario) === 0) {

    header(
        "Location: participaciones.php"
    );

    exit;

}


// =========================================
// VERIFICAR SI GANÓ
// =========================================

$gano = false;


if (
    !empty($rifa["numero_ganador"])
) {

    if (
        in_array(
            $rifa["numero_ganador"],
            $numeros_usuario
        )
    ) {

        $gano = true;

    }

}


// =========================================
// FORMATEAR FECHA
// =========================================

$fecha_sorteo = "Sin fecha";


if (
    !empty($rifa["fecha_sorteo"])
) {

    $fecha_sorteo = date(
        "d/m/Y",
        strtotime(
            $rifa["fecha_sorteo"]
        )
    );

}


// =========================================
// FORMATEAR NÚMEROS
// =========================================

$numeros_texto =
    implode(
        " - ",
        $numeros_usuario
    );

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
        Resultado de participación
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/resultado.css"
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


    <!-- =====================================
         HEADER
    ====================================== -->

    <header class="resultado-header">


        <a
            href="participaciones.php"
            class="back-button"
        >
            ←
        </a>


        <div class="logo">

            <span class="logo-blue">
                Rifa
            </span>

            <span class="logo-red">
                Go
            </span>

            <sup>
                +
            </sup>

        </div>


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
                Sorteo finalizado
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
             INFORMACIÓN
        ====================================== -->

        <section class="resultado-info">


            <h1>

                <?= htmlspecialchars(
                    $rifa["titulo"]
                ) ?>

            </h1>


            <p class="premio">

                🏆

                <?= htmlspecialchars(
                    $rifa["premio"]
                ) ?>

            </p>


        </section>



        <!-- =====================================
             RESULTADO DEL USUARIO
        ====================================== -->

        <section
            class="
                resultado-usuario
                <?= $gano ? 'ganaste' : 'no-ganaste' ?>
            "
        >


            <?php if ($gano): ?>


                <div class="resultado-icon">

                    🎉

                </div>


                <h2>

                    ¡Felicitaciones!

                </h2>


                <p>

                    ¡Tu número fue el ganador!

                </p>


            <?php else: ?>


                <div class="resultado-icon">

                    😔

                </div>


                <h2>

                    Esta vez no ganaste

                </h2>


                <p>

                    ¡Gracias por participar!

                </p>


            <?php endif; ?>


        </section>



        <!-- =====================================
             TUS NÚMEROS
        ====================================== -->

        <section class="numeros-card">


            <h2>

                🎟️ Tus números

            </h2>


            <div class="numeros-lista">


                <?php foreach (
                    $numeros_usuario
                    as
                    $numero
                ): ?>


                    <span
                        class="
                            numero
                            <?= (
                                $numero ==
                                $rifa["numero_ganador"]
                            )
                            ?
                            'numero-ganador'
                            :
                            ''
                            ?>
                        "
                    >

                        <?= htmlspecialchars(
                            $numero
                        ) ?>

                    </span>


                <?php endforeach; ?>


            </div>


        </section>



        <!-- =====================================
             NÚMERO GANADOR
        ====================================== -->

        <section class="ganador-card">


            <div class="ganador-icon">

                🏆

            </div>


            <h2>

                Número ganador

            </h2>


            <p class="ganador-numero">

                <?= !empty(
                    $rifa["numero_ganador"]
                )

                ?

                htmlspecialchars(
                    $rifa["numero_ganador"]
                )

                :

                "--"

                ?>

            </p>



            <?php if (
                !empty(
                    $rifa["ganador_nombre"]
                )
            ): ?>


                <p class="ganador-nombre">

                    Ganador:

                    <strong>

                        <?= htmlspecialchars(
                            $rifa["ganador_nombre"]
                        ) ?>

                        <?= htmlspecialchars(
                            $rifa["ganador_apellido"]
                        ) ?>

                    </strong>

                </p>


            <?php endif; ?>


        </section>



        <!-- =====================================
             INFORMACIÓN
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

                    📅 Fecha del sorteo

                </span>


                <strong>

                    <?= $fecha_sorteo ?>

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


        </section>



        <!-- =====================================
             VOLVER
        ====================================== -->

        <a
            href="participaciones.php"
            class="volver-button"
        >

            Volver a participaciones

        </a>


    </main>


</div>


</body>

</html>