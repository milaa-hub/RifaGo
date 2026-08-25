<?php

require_once "conexion.php";
session_start();


// ==========================================
// VERIFICAR QUE EXISTA EL ID DE LA RIFA
// ==========================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: rifas.php");
    exit;
}

$id_rifa = intval($_GET["id"]);


// ==========================================
// OBTENER DATOS DE LA RIFA
// ==========================================

$consulta = $conexion->prepare("
    SELECT
        r.id_rifa,
        r.id_usuario,
        r.titulo,
        r.descripcion,
        r.premio,
        r.imagen,
        r.precio_numero,
        r.cantidad_numeros,
        r.fecha_sorteo,
        r.estado,
        u.nombre,
        u.apellido
    FROM rifas r
    INNER JOIN usuarios u
        ON r.id_usuario = u.id_usuario
    WHERE r.id_rifa = ?
");

$consulta->bind_param("i", $id_rifa);
$consulta->execute();

$resultado = $consulta->get_result();


// ==========================================
// COMPROBAR QUE EXISTA
// ==========================================

if ($resultado->num_rows === 0) {

    header("Location: rifas.php");
    exit;
}

$rifa = $resultado->fetch_assoc();

$consulta->close();


// ==========================================
// CONTAR NÚMEROS DISPONIBLES
// ==========================================

$consulta_numeros = $conexion->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN estado = 'disponible' THEN 1 ELSE 0 END) AS disponibles,
        SUM(CASE WHEN estado != 'disponible' THEN 1 ELSE 0 END) AS ocupados
    FROM numeros_rifa
    WHERE id_rifa = ?
");

$consulta_numeros->bind_param("i", $id_rifa);
$consulta_numeros->execute();

$resultado_numeros = $consulta_numeros->get_result();

$numeros = $resultado_numeros->fetch_assoc();

$consulta_numeros->close();


// ==========================================
// VALORES PARA MOSTRAR
// ==========================================

$total_numeros = intval($numeros["total"]);
$disponibles = intval($numeros["disponibles"]);
$ocupados = intval($numeros["ocupados"]);


// Si todavía no existen números en la tabla,
// usamos la cantidad configurada en la rifa.

if ($total_numeros === 0) {

    $total_numeros = intval($rifa["cantidad_numeros"]);

    $disponibles = $total_numeros;

    $ocupados = 0;
}


// ==========================================
// PORCENTAJE
// ==========================================

$porcentaje = 0;

if ($total_numeros > 0) {

    $porcentaje = round(
        ($ocupados / $total_numeros) * 100
    );
}


// ==========================================
// SESIÓN
// ==========================================

$usuario_logueado = isset($_SESSION["id_usuario"]);

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
        <?php echo htmlspecialchars($rifa["titulo"]); ?> - RifaGo
    </title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="assets/css/detalle_rifa.css"
    >

</head>


<body>


    <!-- ==============================
         HEADER
    =============================== -->

    <header class="main-header">

        <a href="index.php" class="main-logo">

            <span class="logo-blue">
                Rifa
            </span>

            <span class="logo-red">
                Go
            </span>

            <sup>®</sup>

        </a>


        <a href="rifas.php" class="back-link">
            ← Volver
        </a>

    </header>



    <!-- ==============================
         CONTENIDO
    =============================== -->

    <main class="detail-page">


        <!-- ==============================
             IMAGEN
        =============================== -->

        <section class="detail-image">

            <?php if (!empty($rifa["imagen"])): ?>

                <img
                    src="<?php echo htmlspecialchars($rifa["imagen"]); ?>"
                    alt="<?php echo htmlspecialchars($rifa["titulo"]); ?>"
                >

            <?php else: ?>

                <div class="no-image">
                    Sin imagen
                </div>

            <?php endif; ?>

        </section>



        <!-- ==============================
             INFORMACIÓN
        =============================== -->

        <section class="detail-content">


            <span class="detail-status">
                <?php echo htmlspecialchars($rifa["estado"]); ?>
            </span>


            <h1>
                <?php echo htmlspecialchars($rifa["titulo"]); ?>
            </h1>


            <p class="detail-description">

                <?php
                echo nl2br(
                    htmlspecialchars($rifa["descripcion"])
                );
                ?>

            </p>


            <!-- PREMIO -->

            <div class="detail-prize">

                <span>
                    PREMIO
                </span>

                <strong>
                    <?php echo htmlspecialchars($rifa["premio"]); ?>
                </strong>

            </div>



            <!-- PRECIO -->

            <div class="detail-price">

                <div>

                    <span>
                        Precio por número
                    </span>

                    <strong>
                        $<?php echo number_format(
                            $rifa["precio_numero"],
                            2,
                            ",",
                            "."
                        ); ?>
                    </strong>

                </div>


                <div>

                    <span>
                        Sorteo
                    </span>

                    <strong>
                        <?php
                        echo date(
                            "d/m/Y",
                            strtotime($rifa["fecha_sorteo"])
                        );
                        ?>
                    </strong>

                </div>

            </div>



            <!-- PROGRESO -->

            <div class="detail-progress">

                <div class="progress-header">

                    <span>
                        Números vendidos
                    </span>

                    <span>
                        <?php echo $porcentaje; ?>%
                    </span>

                </div>


                <div class="progress">

                    <div
                        class="progress-bar"
                        style="width: <?php echo $porcentaje; ?>%;"
                    ></div>

                </div>


                <div class="numbers-info">

                    <span>
                        <?php echo $ocupados; ?> ocupados
                    </span>

                    <span>
                        <?php echo $disponibles; ?> disponibles
                    </span>

                </div>

            </div>



            <!-- PARTICIPAR -->

            <div class="participate-box">

                <h2>
                    ¿Querés participar?
                </h2>


                <?php if ($usuario_logueado): ?>

                    <a
                        href="participar.php?id=<?php echo $id_rifa; ?>"
                        class="participate-button"
                    >
                        Elegir número
                    </a>

                <?php else: ?>

                    <p>
                        Iniciá sesión para elegir tu número.
                    </p>

                    <a
                        href="login.php"
                        class="participate-button"
                    >
                        Iniciar sesión
                    </a>

                    <a
                        href="registro.php"
                        class="register-detail-link"
                    >
                        ¿No tenés una cuenta? Crear cuenta
                    </a>

                <?php endif; ?>

            </div>



            <!-- ORGANIZADOR -->

            <div class="organizer">

                <span>
                    Organizada por
                </span>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $rifa["nombre"] . " " . $rifa["apellido"]
                    );
                    ?>
                </strong>

            </div>


        </section>

    </main>


</body>

</html>