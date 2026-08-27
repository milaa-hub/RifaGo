```php
<?php

require_once "conexion.php";
session_start();

/* ==========================================
   VERIFICAR SESIÓN
========================================== */

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

$mensaje = "";
$error = "";


/* ==========================================
   ACTUALIZAR DATOS
========================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST['nombre'] ?? "");
    $apellido = trim($_POST['apellido'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $telefono = trim($_POST['telefono'] ?? "");


    /* VALIDACIONES */

    if ($nombre === "" || $apellido === "" || $email === "") {

        $error = "Completá todos los campos obligatorios.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Ingresá un email válido.";

    } else {

        /*
         * Comprobar que el email no pertenezca
         * a otro usuario.
         */

        $sql_email = "SELECT id_usuario
                      FROM usuarios
                      WHERE email = ?
                      AND id_usuario != ?";

        $stmt_email = $conexion->prepare($sql_email);

        if ($stmt_email) {

            $stmt_email->bind_param(
                "si",
                $email,
                $id_usuario
            );

            $stmt_email->execute();

            $resultado_email = $stmt_email->get_result();


            if ($resultado_email->num_rows > 0) {

                $error = "Ese email ya está registrado.";

            } else {

                /* ==========================================
                   ACTUALIZAR USUARIO
                ========================================== */

                $sql = "UPDATE usuarios
                        SET nombre = ?,
                            apellido = ?,
                            email = ?,
                            telefono = ?
                        WHERE id_usuario = ?";

                $stmt = $conexion->prepare($sql);

                if ($stmt) {

                    $stmt->bind_param(
                        "ssssi",
                        $nombre,
                        $apellido,
                        $email,
                        $telefono,
                        $id_usuario
                    );


                    if ($stmt->execute()) {

                        $mensaje = "Tus datos fueron actualizados correctamente.";

                    } else {

                        $error = "No se pudieron actualizar tus datos.";
                    }

                    $stmt->close();

                } else {

                    $error = "Ocurrió un error al preparar la actualización.";
                }
            }

            $stmt_email->close();

        } else {

            $error = "Ocurrió un error al comprobar el email.";
        }
    }
}


/* ==========================================
   OBTENER DATOS ACTUALES
========================================== */

$sql = "SELECT nombre, apellido, email, telefono
        FROM usuarios
        WHERE id_usuario = ?";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error al consultar los datos del usuario.");
}

$stmt->bind_param("i", $id_usuario);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {

    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}

$usuario = $resultado->fetch_assoc();

$stmt->close();


/* ==========================================
   DATOS PARA MOSTRAR
========================================== */

$nombre = htmlspecialchars($usuario['nombre']);
$apellido = htmlspecialchars($usuario['apellido']);
$email = htmlspecialchars($usuario['email']);
$telefono = htmlspecialchars($usuario['telefono'] ?? "");

$nombre_completo = $nombre . " " . $apellido;


/* INICIALES */

$inicial_nombre = strtoupper(
    substr($usuario['nombre'], 0, 1)
);

$inicial_apellido = strtoupper(
    substr($usuario['apellido'], 0, 1)
);

$iniciales = $inicial_nombre . $inicial_apellido;

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Mis datos - RifaGo</title>


    <link
        rel="stylesheet"
        href="assets/css/style.css"
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

        <a href="index.php" class="logo">

            <span class="logo-blue">
                Rifa
            </span>

            <span class="logo-red">
                Go
            </span>

            <sup>
                +
            </sup>

        </a>


        <a href="perfil.php" class="user-icon">

            <span>
                <?php echo $iniciales; ?>
            </span>

        </a>

    </header>


    <!-- ==========================================
         CONTENIDO
    =========================================== -->

    <main class="main-content">


        <!-- BOTÓN VOLVER -->

        <a
            href="perfil.php"
            class="back-link"
        >
            ← Volver al perfil
        </a>


        <!-- TÍTULO -->

        <section class="page-title">

            <h1>
                Mis datos
            </h1>

            <p>
                Revisá y actualizá tu información personal.
            </p>

        </section>


        <!-- ==========================================
             AVATAR
        =========================================== -->

        <section class="profile-header">

            <div class="profile-avatar">

                <?php echo $iniciales; ?>

            </div>


            <div class="profile-name">

                <h1>
                    <?php echo $nombre_completo; ?>
                </h1>

                <p>
                    Información de tu cuenta
                </p>

            </div>

        </section>


        <!-- ==========================================
             MENSAJES
        =========================================== -->

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


        <!-- ==========================================
             FORMULARIO
        =========================================== -->

        <form
            method="POST"
            action="mis_datos.php"
            class="data-form"
        >


            <!-- NOMBRE -->

            <div class="form-group">

                <label for="nombre">
                    Nombre
                </label>

                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="<?php echo $nombre; ?>"
                    required
                >

            </div>


            <!-- APELLIDO -->

            <div class="form-group">

                <label for="apellido">
                    Apellido
                </label>

                <input
                    type="text"
                    id="apellido"
                    name="apellido"
                    value="<?php echo $apellido; ?>"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo $email; ?>"
                    required
                >

            </div>


            <!-- TELÉFONO -->

            <div class="form-group">

                <label for="telefono">
                    Teléfono
                </label>

                <input
                    type="tel"
                    id="telefono"
                    name="telefono"
                    value="<?php echo $telefono; ?>"
                >

            </div>


            <!-- BOTÓN -->

            <button
                type="submit"
                class="save-button"
            >
                Guardar cambios
            </button>


        </form>


    </main>


    <!-- ==========================================
         NAVEGACIÓN
    =========================================== -->

    <nav class="bottom-nav">


        <a
            href="index.php"
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
            onclick="crearRifa()"
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
            class="nav-item active"
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


<script>

function crearRifa() {

    window.location.href = "crear_rifa.php";

}

</script>


</body>
</html>
```
