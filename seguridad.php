<?php

require_once "conexion.php";
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

$mensaje = "";
$error = "";


/* ==========================================
   DATOS DEL USUARIO
========================================== */

$sql_usuario = "SELECT nombre, apellido
                FROM usuarios
                WHERE id_usuario = ?";

$stmt_usuario = $conexion->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $id_usuario);
$stmt_usuario->execute();

$resultado_usuario = $stmt_usuario->get_result();
$usuario = $resultado_usuario->fetch_assoc();

$stmt_usuario->close();

if (!$usuario) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$iniciales =
    strtoupper(substr($usuario['nombre'], 0, 1)) .
    strtoupper(substr($usuario['apellido'], 0, 1));


/* ==========================================
   CAMBIAR CONTRASEÑA
========================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $password_actual = $_POST['password_actual'] ?? "";
    $password_nueva = $_POST['password_nueva'] ?? "";
    $password_confirmar = $_POST['password_confirmar'] ?? "";


    if (
        $password_actual === "" ||
        $password_nueva === "" ||
        $password_confirmar === ""
    ) {

        $error = "Completá todos los campos.";

    } elseif (strlen($password_nueva) < 6) {

        $error = "La nueva contraseña debe tener al menos 6 caracteres.";

    } elseif ($password_nueva !== $password_confirmar) {

        $error = "Las contraseñas nuevas no coinciden.";

    } else {


        /* Obtener contraseña actual */

        $sql = "SELECT password
                FROM usuarios
                WHERE id_usuario = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $datos = $resultado->fetch_assoc();

        $stmt->close();


        if (!$datos) {

            $error = "No se pudo encontrar tu cuenta.";

        } else {


            /*
             * Comprobar contraseña.
             *
             * Esto funciona si tu registro/login
             * utiliza password_hash().
             */

            if (!password_verify($password_actual, $datos['password'])) {

                $error = "La contraseña actual es incorrecta.";

            } else {


                /* Generar nueva contraseña */

                $password_hash = password_hash(
                    $password_nueva,
                    PASSWORD_DEFAULT
                );


                $sql_update = "UPDATE usuarios
                               SET password = ?
                               WHERE id_usuario = ?";

                $stmt_update = $conexion->prepare($sql_update);

                $stmt_update->bind_param(
                    "si",
                    $password_hash,
                    $id_usuario
                );


                if ($stmt_update->execute()) {

                    $mensaje = "Tu contraseña fue actualizada correctamente.";

                } else {

                    $error = "No se pudo actualizar la contraseña.";
                }

                $stmt_update->close();
            }
        }
    }
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

    <title>Seguridad - RifaGo</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

</head>

<body>

<div class="app">

    <header class="header">

        <a href="index.php" class="logo">

            <span class="logo-blue">Rifa</span><span class="logo-red">Go</span><sup>+</sup>

        </a>

        <a href="perfil.php" class="user-icon">

            <span>
                <?php echo $iniciales; ?>
            </span>

        </a>

    </header>


    <main class="main-content">


        <a
            href="perfil.php"
            class="back-link"
        >
            ← Volver al perfil
        </a>


        <section class="page-title">

            <h1>
                Seguridad
            </h1>

            <p>
                Mantené protegida tu cuenta de RifaGo.
            </p>

        </section>


        <?php if ($mensaje !== ""): ?>

            <div class="success-message">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>

        <?php endif; ?>


        <?php if ($error !== ""): ?>

            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <form
            method="POST"
            action="seguridad.php"
            class="data-form"
        >


            <div class="form-group">

                <label for="password_actual">
                    Contraseña actual
                </label>

                <input
                    type="password"
                    id="password_actual"
                    name="password_actual"
                    required
                >

            </div>


            <div class="form-group">

                <label for="password_nueva">
                    Nueva contraseña
                </label>

                <input
                    type="password"
                    id="password_nueva"
                    name="password_nueva"
                    minlength="6"
                    required
                >

            </div>


            <div class="form-group">

                <label for="password_confirmar">
                    Repetir nueva contraseña
                </label>

                <input
                    type="password"
                    id="password_confirmar"
                    name="password_confirmar"
                    minlength="6"
                    required
                >

            </div>


            <button
                type="submit"
                class="save-button"
            >
                Cambiar contraseña
            </button>


        </form>


    </main>


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

        <a href="participaciones.php" class="nav-item">

            <span class="nav-icon">♧</span>

            <span>Participaciones</span>

        </a>

        <a href="perfil.php" class="nav-item active">

            <span class="nav-icon">♙</span>

            <span>Perfil</span>

        </a>

    </nav>

</div>

</body>
</html>