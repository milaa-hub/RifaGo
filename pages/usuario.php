<?php

require_once "../conexion.php";
session_start();


/* ==========================================
   VERIFICAR ID DEL USUARIO
========================================== */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$id_usuario = intval($_GET['id']);


/* ==========================================
   OBTENER USUARIO
========================================== */

$sql_usuario = "SELECT
                    id_usuario,
                    nombre,
                    apellido,
                    fecha_registro
                FROM usuarios
                WHERE id_usuario = ?";

$stmt_usuario = $conexion->prepare($sql_usuario);

if (!$stmt_usuario) {
    die("Error al consultar el usuario.");
}

$stmt_usuario->bind_param("i", $id_usuario);
$stmt_usuario->execute();

$resultado_usuario = $stmt_usuario->get_result();

if ($resultado_usuario->num_rows === 0) {
    header("Location: ../index.php");
    exit;
}

$usuario = $resultado_usuario->fetch_assoc();

$stmt_usuario->close();


/* ==========================================
   DATOS DEL USUARIO
========================================== */

$nombre = htmlspecialchars($usuario['nombre']);
$apellido = htmlspecialchars($usuario['apellido']);

$nombre_completo = $nombre . " " . $apellido;


/* ==========================================
   INICIALES
========================================== */

$inicial_nombre = strtoupper(
    substr($usuario['nombre'], 0, 1)
);

$inicial_apellido = strtoupper(
    substr($usuario['apellido'], 0, 1)
);

$iniciales = $inicial_nombre . $inicial_apellido;


/* ==========================================
   FECHA DE REGISTRO
========================================== */

$fecha_registro = date(
    "F Y",
    strtotime($usuario['fecha_registro'])
);


/*
 * Convertir nombres de meses al español
 */

$meses = [
    "January" => "enero",
    "February" => "febrero",
    "March" => "marzo",
    "April" => "abril",
    "May" => "mayo",
    "June" => "junio",
    "July" => "julio",
    "August" => "agosto",
    "September" => "septiembre",
    "October" => "octubre",
    "November" => "noviembre",
    "December" => "diciembre"
];

$fecha_registro = str_replace(
    array_keys($meses),
    array_values($meses),
    $fecha_registro
);


/* ==========================================
   CANTIDAD DE RIFAS CREADAS
========================================== */

$sql_rifas = "SELECT COUNT(*) AS total
              FROM rifas
              WHERE id_usuario = ?";

$stmt_rifas = $conexion->prepare($sql_rifas);
$stmt_rifas->bind_param("i", $id_usuario);
$stmt_rifas->execute();

$resultado_rifas = $stmt_rifas->get_result();
$total_rifas = $resultado_rifas->fetch_assoc()['total'];

$stmt_rifas->close();


/* ==========================================
   CANTIDAD DE PARTICIPACIONES
========================================== */

$sql_participaciones = "SELECT COUNT(*) AS total
                        FROM participaciones
                        WHERE id_usuario = ?";

$stmt_participaciones = $conexion->prepare(
    $sql_participaciones
);

$stmt_participaciones->bind_param(
    "i",
    $id_usuario
);

$stmt_participaciones->execute();

$resultado_participaciones =
    $stmt_participaciones->get_result();

$total_participaciones =
    $resultado_participaciones->fetch_assoc()['total'];

$stmt_participaciones->close();


/* ==========================================
   OBTENER RIFAS DEL USUARIO
========================================== */

$sql_rifas_usuario = "SELECT
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
                      ORDER BY fecha_sorteo ASC";

$stmt_rifas_usuario =
    $conexion->prepare($sql_rifas_usuario);

$stmt_rifas_usuario->bind_param(
    "i",
    $id_usuario
);

$stmt_rifas_usuario->execute();

$rifas_usuario =
    $stmt_rifas_usuario->get_result();

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
        <?php echo $nombre_completo; ?> - RifaGo
    </title>


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


    <!-- ==========================================
         HEADER
    =========================================== -->

    <header class="header">

        <a
            href="../index.php"
            class="logo"
        >

            <span class="logo-blue">Rifa</span><span class="logo-red">Go</span><sup>+</sup>

        </a>


        <?php if (isset($_SESSION['id_usuario'])): ?>

            <a
                href="perfil.php"
                class="user-icon"
            >

                <span>
                    <?php

                    /*
                     * Mostramos las iniciales del usuario
                     * que tiene la sesión iniciada.
                     */

                    if (
                        isset($_SESSION['nombre']) &&
                        isset($_SESSION['apellido'])
                    ) {

                        echo strtoupper(
                            substr($_SESSION['nombre'], 0, 1) .
                            substr($_SESSION['apellido'], 0, 1)
                        );

                    } else {

                        echo "?";

                    }

                    ?>
                </span>

            </a>

        <?php else: ?>

            <a
                href="../login.php"
                class="user-icon"
            >

                <span>
                    ?
                </span>

            </a>

        <?php endif; ?>

    </header>


    <!-- ==========================================
         CONTENIDO
    =========================================== -->

    <main class="main-content">


        <!-- VOLVER -->

        <a
            href="javascript:history.back()"
            class="back-link"
        >
            ← Volver
        </a>


        <!-- ==========================================
             PERFIL
        =========================================== -->

        <section class="public-profile">


            <!-- FOTO -->

            <div class="public-avatar">

                <?php echo $iniciales; ?>

            </div>


            <!-- NOMBRE -->

            <h1>
                <?php echo $nombre_completo; ?>
            </h1>


            <!-- FECHA -->

            <p class="profile-date">

                Miembro desde
                <?php echo $fecha_registro; ?>

            </p>



        </section>


        <!-- ==========================================
             ESTADÍSTICAS
        =========================================== -->

        <section class="profile-stats">


            <div class="profile-stat">

                <strong>
                    <?php echo $total_rifas; ?>
                </strong>

                <span>
                    Rifas creadas
                </span>

            </div>


            <div class="profile-stat">

                <strong>
                    <?php echo $total_participaciones; ?>
                </strong>

                <span>
                    Participaciones
                </span>

            </div>


        </section>


        <!-- ==========================================
             REPUTACIÓN
        =========================================== -->

        <section class="reputation-card">

            <div class="reputation-icon">
                ★
            </div>

            <div>

                <h3>
                    Reputación
                </h3>

                <p>
                    Las valoraciones estarán disponibles próximamente.
                </p>

            </div>

        </section>


        <!-- ==========================================
             RIFAS DEL USUARIO
        =========================================== -->

        <section class="user-raffles">


            <div class="section-heading">

                <h2>
                    Rifas de <?php echo $nombre; ?>
                </h2>

            </div>


            <?php if ($rifas_usuario->num_rows > 0): ?>


                <div class="raffles-grid">


                    <?php while ($rifa = $rifas_usuario->fetch_assoc()): ?>


                        <a
                            href="detalle_rifa.php?id=<?php echo $rifa['id_rifa']; ?>"
                            class="raffle-card"
                        >


                            <!-- IMAGEN -->

                            <div class="raffle-image">

                                <?php if (!empty($rifa['imagen'])): ?>

                                    <img
                                        src="../<?php echo htmlspecialchars(
                                            $rifa['imagen']
                                        ); ?>"
                                        alt="<?php echo htmlspecialchars(
                                            $rifa['titulo']
                                        ); ?>"
                                    >

                                <?php else: ?>

                                    <div class="raffle-placeholder">
                                        🎟
                                    </div>

                                <?php endif; ?>

                            </div>


                            <!-- INFORMACIÓN -->

                            <div class="raffle-content">

                                <h3>
                                    <?php echo htmlspecialchars(
                                        $rifa['titulo']
                                    ); ?>
                                </h3>


                                <p class="raffle-prize">

                                    <?php echo htmlspecialchars(
                                        $rifa['premio']
                                    ); ?>

                                </p>


                                <div class="raffle-info">

                                    <span>

                                        $<?php echo number_format(
                                            $rifa['precio_numero'],
                                            2,
                                            ',',
                                            '.'
                                        ); ?>

                                    </span>


                                    <span>

                                        Sorteo:
                                        <?php echo date(
                                            "d/m/Y",
                                            strtotime(
                                                $rifa['fecha_sorteo']
                                            )
                                        ); ?>

                                    </span>

                                </div>


                                <span class="raffle-status">

                                    <?php

                                    if ($rifa['estado'] === 'activa') {

                                        echo "Activa";

                                    } elseif (
                                        $rifa['estado'] === 'finalizada'
                                    ) {

                                        echo "Finalizada";

                                    } else {

                                        echo ucfirst(
                                            htmlspecialchars(
                                                $rifa['estado']
                                            )
                                        );

                                    }

                                    ?>

                                </span>


                            </div>


                        </a>


                    <?php endwhile; ?>


                </div>


            <?php else: ?>


                <div class="empty-state">

                    <div class="empty-icon">
                        🎟
                    </div>

                    <h2>
                        Todavía no creó rifas
                    </h2>

                    <p>
                        Este usuario todavía no tiene rifas publicadas.
                    </p>

                </div>


            <?php endif; ?>


        </section>


    </main>


    <!-- ==========================================
         NAV
    =========================================== -->

    <nav class="bottom-nav">


        <a
            href="../index.php"
            class="nav-item"
        >

            <span class="nav-icon">
                ⌂
            </span>

            <span>
                Inicio
            </span>

        </a>


        <a
            href="mis_rifas.php"
            class="nav-item"
        >

            <span class="nav-icon">
                ▤
            </span>

            <span>
                Mis rifas
            </span>

        </a>


        <button
            class="create-button"
            onclick="window.location.href='crear_rifa.php'"
        >

            <span>
                +
            </span>

        </button>


        <a
            href="participaciones.php"
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
            href="perfil.php"
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

</body>
</html>