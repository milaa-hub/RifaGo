<?php

require_once "../conexion.php";
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];


/* ==========================================
   DATOS DEL USUARIO
========================================== */

$sql = "SELECT nombre, apellido, email, telefono
        FROM usuarios
        WHERE id_usuario = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();

$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

$stmt->close();

if (!$usuario) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

$nombre = htmlspecialchars(
    $usuario['nombre'] . " " . $usuario['apellido']
);

$email = htmlspecialchars($usuario['email']);

$iniciales =
    strtoupper(substr($usuario['nombre'], 0, 1)) .
    strtoupper(substr($usuario['apellido'], 0, 1));

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Configuración - RifaGo</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

</head>

<body>

<div class="app">

    <!-- HEADER -->

    <header class="header rifas-header">


        <a
            href="perfil.php"
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


    <main class="main-content">

        <section class="page-title">

            <h1>
                Configuración
            </h1>

            <p>
                Administrá las opciones de tu cuenta.
            </p>

        </section>


        <!-- INFORMACIÓN DE CUENTA -->

        <section class="settings-menu">


            <a
                href="mis_datos.php"
                class="settings-option"
            >

                <div class="settings-icon">
                    ♙
                </div>

                <div class="settings-text">

                    <h3>
                        Datos de la cuenta
                    </h3>

                    <p>
                        <?php echo $nombre; ?>
                    </p>

                </div>

                <span class="option-arrow">
                    ›
                </span>

            </a>


            <!-- SEGURIDAD -->

            <a
                href="seguridad.php"
                class="settings-option"
            >

                <div class="settings-icon">
                    ◉
                </div>

                <div class="settings-text">

                    <h3>
                        Seguridad
                    </h3>

                    <p>
                        Cambiar contraseña
                    </p>

                </div>

                <span class="option-arrow">
                    ›
                </span>

            </a>


            <!-- EMAIL -->

            <div class="settings-option">

                <div class="settings-icon">
                    @
                </div>

                <div class="settings-text">

                    <h3>
                        Email
                    </h3>

                    <p>
                        <?php echo $email; ?>
                    </p>

                </div>

            </div>


            <!-- CERRAR SESIÓN -->

            <a
                href="../acciones/logout.php"
                class="settings-option logout-button"
            >

                <div class="settings-icon logout-icon">
                    ⎋
                </div>

                <div class="settings-text">
                    <h3>Cerrar sesión</h3>
                </div>

                <span class="option-arrow">
                    ›
                </span>

            </a>


        </section>


    </main>

</div>

</body>
</html>