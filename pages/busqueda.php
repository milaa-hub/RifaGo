<?php

require_once "../conexion.php";
session_start();

$usuario_logueado = isset($_SESSION['id_usuario']);

/* =========================================
   RECIBIR DATOS DEL BUSCADOR
========================================= */

$busqueda = $_GET['busqueda'] ?? '';

$categoria = $_GET['categoria'] ?? '';

$precio_min = $_GET['precio_min'] ?? '';
$precio_max = $_GET['precio_max'] ?? '';

$cantidad_min = $_GET['cantidad_min'] ?? '';
$cantidad_max = $_GET['cantidad_max'] ?? '';

$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';


/* =========================================
   CONSULTA BASE
========================================= */

$sql = "
    SELECT *
    FROM rifas
    WHERE 1=1
";


/* =========================================
   BUSCADOR POR TEXTO
========================================= */

if (!empty($busqueda)) {

    $busqueda = mysqli_real_escape_string(
        $conexion,
        $busqueda
    );

    $sql .= "
        AND (
            titulo LIKE '%$busqueda%'
            OR descripcion LIKE '%$busqueda%'
            OR premio LIKE '%$busqueda%'
            OR categoria LIKE '%$busqueda%'
        )
    ";
}


/* =========================================
   PRECIO
========================================= */

if ($precio_min !== '') {

    $precio_min = floatval($precio_min);

    $sql .= "
        AND precio_numero >= $precio_min
    ";
}


if ($precio_max !== '') {

    $precio_max = floatval($precio_max);

    $sql .= "
        AND precio_numero <= $precio_max
    ";
}


/* =========================================
   CANTIDAD DE NÚMEROS
========================================= */

if ($cantidad_min !== '') {

    $cantidad_min = intval($cantidad_min);

    $sql .= "
        AND cantidad_numeros >= $cantidad_min
    ";
}


if ($cantidad_max !== '') {

    $cantidad_max = intval($cantidad_max);

    $sql .= "
        AND cantidad_numeros <= $cantidad_max
    ";
}


/* =========================================
   FECHA DE SORTEO
========================================= */

if (!empty($fecha_desde)) {

    $fecha_desde = mysqli_real_escape_string(
        $conexion,
        $fecha_desde
    );

    $sql .= "
        AND fecha_sorteo >= '$fecha_desde'
    ";
}


if (!empty($fecha_hasta)) {

    $fecha_hasta = mysqli_real_escape_string(
        $conexion,
        $fecha_hasta
    );

    $sql .= "
        AND fecha_sorteo <= '$fecha_hasta'
    ";
}


/* =========================================
   ORDENAR RESULTADOS
========================================= */

$sql .= "
    ORDER BY fecha_sorteo ASC
";


/* =========================================
   EJECUTAR CONSULTA
========================================= */

$resultado = mysqli_query(
    $conexion,
    $sql
);

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Resultados de búsqueda</title>

    <link rel="stylesheet" href="../assets/css/busqueda.css">

</head>


<body>
    
    <!-- ==========================================
         HEADER
    =========================================== -->

    <header class="raffles-header">
        
        <a
            href="../index.php"
            class="back-button"
            style="text-decoration: none;"
        >
            ←
        </a>

        <a href="../index.php" class="raffles-logo" style="text-decoration: none;">

            <span class="logo-blue">Rifa</span><span class="logo-red">Go</span><sup>+</sup>

        </a>


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


    <main>

        <h1>
            Resultados de búsqueda
        </h1>


        <div class="rifas-container">


            <?php if (mysqli_num_rows($resultado) > 0): ?>


                <?php while ($rifa = mysqli_fetch_assoc($resultado)): ?>


                    <div class="rifa-card">

                        <img
                            src="../uploads/../<?php echo htmlspecialchars($rifa['imagen']); ?>"
                            alt="<?php echo htmlspecialchars($rifa['titulo']); ?>"
                        >


                        <div class="rifa-info">

                            <h2>
                                <?php echo htmlspecialchars($rifa['titulo']); ?>
                            </h2>


                            <p class="rifa-descripcion">
                                <?php echo htmlspecialchars($rifa['descripcion']); ?>
                            </p>


                            <div class="rifa-datos">

                                <p>
                                    <strong>Premio:</strong>
                                    <?php echo htmlspecialchars($rifa['premio']); ?>
                                </p>


                                <p>
                                    <strong>Precio:</strong>
                                    $<?php echo $rifa['precio_numero']; ?>
                                </p>


                                <p>
                                    <strong>Números:</strong>
                                    <?php echo $rifa['cantidad_numeros']; ?>
                                </p>


                                <p>
                                    <strong>Sorteo:</strong>
                                    <?php echo $rifa['fecha_sorteo']; ?>
                                </p>

                            </div>

                        </div>


                        <a
                            href="detalle_rifa.php?id=<?php echo $rifa['id_rifa']; ?>"
                        >
                            Ver rifa
                        </a>


                    </div>


                <?php endwhile; ?>


            <?php else: ?>


                <p>

                    No se encontraron rifas.

                </p>


            <?php endif; ?>


        </div>


    </main>


</body>

</html>