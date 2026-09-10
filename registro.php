<?php

require_once "conexion.php";

$mensaje_error = "";
$registro_exitoso = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);
    $apellido = trim($_POST["apellido"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirmar_password = $_POST["confirmar_password"];


    /* VALIDAR CAMPOS */

    if (
        empty($nombre) ||
        empty($apellido) ||
        empty($email) ||
        empty($password) ||
        empty($confirmar_password)
    ) {

        $mensaje_error = "Completá todos los campos.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $mensaje_error = "Ingresá un correo electrónico válido.";

    } elseif ($password !== $confirmar_password) {

        $mensaje_error = "Las contraseñas no coinciden.";

    } elseif (strlen($password) < 6) {

        $mensaje_error = "La contraseña debe tener al menos 6 caracteres.";

    } else {

        /* COMPROBAR SI EL EMAIL YA EXISTE */

        $consulta = $conexion->prepare(
            "SELECT id_usuario FROM usuarios WHERE email = ?"
        );

        $consulta->bind_param("s", $email);

        $consulta->execute();

        $resultado = $consulta->get_result();


        if ($resultado->num_rows > 0) {

            $mensaje_error = "Ya existe una cuenta con ese correo.";

        } else {

            /* ENCRIPTAR CONTRASEÑA */

            $password_segura = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            /* INSERTAR USUARIO */

            $insertar = $conexion->prepare(
                "INSERT INTO usuarios
                (nombre, apellido, email, password)
                VALUES (?, ?, ?, ?)"
            );

            $insertar->bind_param(
                "ssss",
                $nombre,
                $apellido,
                $email,
                $password_segura
            );


            if ($insertar->execute()) {

                $registro_exitoso = true;

            } else {

                $mensaje_error = "Ocurrió un error al crear la cuenta.";

            }

            $insertar->close();
        }

        $consulta->close();
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

    <title>Crear cuenta - RifaGo</title>

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

    <link
        rel="stylesheet"
        href="assets/css/login.css?=v2"
    >

</head>


<body class="login-page">


    <main class="login-container">


        <!-- LOGO -->

        <div class="login-logo">

            <a href="index.php" class="logo">

                <span class="logo-blue">Rifa</span><span class="logo-red">Go</span><sup>+</sup>

            </a>

            <p>
                Creá tu cuenta y empezá a participar
            </p>

        </div>


        <!-- TARJETA -->

        <section class="login-card">


            <!-- TITULO -->

            <div class="login-header">

                <h1>
                    Crear cuenta
                </h1>

                <p>
                    Completá tus datos para registrarte en RifaGo.
                </p>

            </div>


            <?php if (!empty($mensaje_error)): ?>

                <div class="login-error">

                    <?php echo htmlspecialchars($mensaje_error); ?>

                </div>

            <?php endif; ?>


            <?php if ($registro_exitoso): ?>

                <div class="login-success">

                    ¡Tu cuenta fue creada correctamente!

                </div>


                <a
                    href="login.php"
                    class="login-button success-button"
                >
                    Iniciar sesión
                </a>


            <?php else: ?>


                <!-- FORMULARIO -->

                <form
                    action="registro.php"
                    method="POST"
                    class="login-form"
                >


                    <!-- NOMBRE -->

                    <div class="form-group">

                        <label for="nombre">
                            Nombre
                        </label>

                        <div class="input-container">

                            <span class="input-icon">
                                👤
                            </span>

                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                placeholder="Tu nombre"
                                required
                            >

                        </div>

                    </div>


                    <!-- APELLIDO -->

                    <div class="form-group">

                        <label for="apellido">
                            Apellido
                        </label>

                        <div class="input-container">

                            <span class="input-icon">
                                👤
                            </span>

                            <input
                                type="text"
                                id="apellido"
                                name="apellido"
                                placeholder="Tu apellido"
                                required
                            >

                        </div>

                    </div>


                    <!-- EMAIL -->

                    <div class="form-group">

                        <label for="email">
                            Correo electrónico
                        </label>

                        <div class="input-container">

                            <span class="input-icon">
                                ✉
                            </span>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="tucorreo@email.com"
                                required
                            >

                        </div>

                    </div>


                    <!-- CONTRASEÑA -->

                    <div class="form-group">

                        <label for="password">
                            Contraseña
                        </label>

                        <div class="input-container">

                            <span class="input-icon">
                                🔒
                            </span>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Mínimo 6 caracteres"
                                required
                            >

                            <button
                                type="button"
                                class="show-password"
                                onclick="mostrarPassword('password', this)"
                            >
                                Mostrar
                            </button>

                        </div>

                    </div>


                    <!-- CONFIRMAR CONTRASEÑA -->

                    <div class="form-group">

                        <label for="confirmar_password">
                            Confirmar contraseña
                        </label>

                        <div class="input-container">

                            <span class="input-icon">
                                🔒
                            </span>

                            <input
                                type="password"
                                id="confirmar_password"
                                name="confirmar_password"
                                placeholder="Repetí tu contraseña"
                                required
                            >

                            <button
                                type="button"
                                class="show-password"
                                onclick="mostrarPassword('confirmar_password', this)"
                            >
                                Mostrar
                            </button>

                        </div>

                    </div>


                    <!-- BOTON -->

                    <button
                        type="submit"
                        class="login-button"
                    >
                        Crear cuenta
                    </button>


                </form>


                <!-- VOLVER AL LOGIN -->

                <div class="register-container">

                    <span>
                        ¿Ya tenés una cuenta?
                    </span>

                    <a href="login.php">
                        Iniciar sesión
                    </a>

                </div>


            <?php endif; ?>


        </section>


        <!-- FOOTER -->

        <footer class="login-footer">

            <p>
                Al crear una cuenta aceptás nuestros
                <a href="#">
                    términos y condiciones
                </a>.
            </p>

        </footer>


    </main>


    <script>

        function mostrarPassword(id, boton) {

            const input = document.getElementById(id);

            if (input.type === "password") {

                input.type = "text";

                boton.textContent = "Ocultar";

            } else {

                input.type = "password";

                boton.textContent = "Mostrar";

            }

        }

    </script>

</body>

</html>