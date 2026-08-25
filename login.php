<?php

require_once "conexion.php";
session_start();

$mensaje_error = "";


// ==========================================
// PROCESAR LOGIN
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = trim($_POST["correo"]);
    $password = $_POST["password"];


    // Buscar usuario por correo

    $consulta = $conexion->prepare("
        SELECT
            id_usuario,
            nombre,
            apellido,
            email,
            password,
            telefono,
            rol
        FROM usuarios
        WHERE email = ?
    ");

    $consulta->bind_param("s", $correo);

    $consulta->execute();

    $resultado = $consulta->get_result();


    // Comprobar que exista el usuario

    if ($resultado->num_rows === 1) {

        $usuario = $resultado->fetch_assoc();


        // Comprobar contraseña

        if (password_verify($password, $usuario["password"])) {


            // ==========================================
            // CREAR SESIÓN
            // ==========================================

            $_SESSION["id_usuario"] = $usuario["id_usuario"];

            $_SESSION["nombre"] = $usuario["nombre"];

            $_SESSION["apellido"] = $usuario["apellido"];

            $_SESSION["email"] = $usuario["email"];

            $_SESSION["telefono"] = $usuario["telefono"];

            $_SESSION["rol"] = $usuario["rol"];


            // ==========================================
            // IR A LA PÁGINA PRINCIPAL
            // ==========================================

            header("Location: index.php");
            exit;


        } else {

            $mensaje_error = "Correo o contraseña incorrectos.";

        }


    } else {

        $mensaje_error = "Correo o contraseña incorrectos.";

    }


    $consulta->close();
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

    <title>Iniciar sesión - RifaGo</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
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


<div class="login-page">


    <main class="login-container">


        <!-- LOGO -->

        <div class="login-logo">

            <a href="index.php" class="logo">

                <span class="logo-blue">
                    Rifa
                </span>

                <span class="logo-red">
                    Go
                </span>

                <sup>+</sup>

            </a>


            <p>
                Tu próxima suerte, más cerca.
            </p>

        </div>



        <!-- TARJETA -->

        <div class="login-card">


            <div class="login-header">

                <h1>
                    Bienvenido de nuevo
                </h1>

                <p>
                    Ingresá a tu cuenta para continuar.
                </p>

            </div>



            <!-- MENSAJE DE ERROR -->

            <?php if (!empty($mensaje_error)): ?>

                <div class="login-error">

                    <?php echo htmlspecialchars($mensaje_error); ?>

                </div>

            <?php endif; ?>



            <!-- FORMULARIO -->

            <form
                action="login.php"
                method="POST"
                class="login-form"
            >


                <!-- CORREO -->

                <div class="form-group">

                    <label for="correo">
                        Correo electrónico
                    </label>


                    <div class="input-container">

                        <span class="input-icon">
                            ✉
                        </span>


                        <input
                            type="email"
                            id="correo"
                            name="correo"
                            placeholder="tu@email.com"
                            required
                        >

                    </div>

                </div>



                <!-- CONTRASEÑA -->

                <div class="form-group">


                    <div class="password-label">

                        <label for="password">
                            Contraseña
                        </label>


                        <a
                            href="#"
                            class="forgot-password"
                        >
                            ¿Olvidaste tu contraseña?
                        </a>

                    </div>



                    <div class="input-container">

                        <span class="input-icon">
                            ▣
                        </span>


                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Ingresá tu contraseña"
                            required
                        >


                        <button
                            type="button"
                            class="show-password"
                            id="mostrarPassword"
                        >
                            Ver
                        </button>

                    </div>

                </div>



                <!-- RECORDAR -->

                <div class="remember-container">

                    <label class="remember-label">

                        <input
                            type="checkbox"
                            name="recordarme"
                        >

                        <span>
                            Recordarme
                        </span>

                    </label>

                </div>



                <!-- BOTON -->

                <button
                    type="submit"
                    class="login-button"
                >
                    Iniciar sesión
                </button>


            </form>



            <!-- REGISTRO -->

            <div class="register-container">

                <span>
                    ¿Todavía no tenés una cuenta?
                </span>


                <a href="registro.php">
                    Crear cuenta
                </a>


                <a
                    href="index.php"
                    class="btn-secundario"
                >
                    Continuar sin iniciar sesión
                </a>

            </div>


        </div>



        <!-- TEXTO INFERIOR -->

        <p class="login-footer">

            Al ingresar aceptás nuestros

            <a href="#">
                Términos y condiciones
            </a>

            y

            <a href="#">
                Política de privacidad
            </a>.

        </p>


    </main>

</div>



<script src="assets/js/login.js"></script>


</body>

</html>