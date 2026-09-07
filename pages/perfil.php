<?php

require_once "../conexion.php";
session_start();

/* ==========================================
   VERIFICAR SESIÓN
========================================== */

$usuario_logueado = isset($_SESSION['id_usuario']);

$usuario = null;

if ($usuario_logueado) {

    $id_usuario = $_SESSION['id_usuario'];

    /* ==========================================
       OBTENER DATOS DEL USUARIO
    ========================================== */

    $sql = "SELECT id_usuario, nombre, apellido, email, telefono, rol
            FROM usuarios
            WHERE id_usuario = ?";

    $stmt = $conexion->prepare($sql);

    if ($stmt) {

        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {

            $usuario = $resultado->fetch_assoc();

        } else {

            /*
             * Si la sesión existe pero el usuario
             * ya no existe en la base de datos,
             * cerramos la sesión.
             */

            session_unset();
            session_destroy();

            $usuario_logueado = false;
        }

        $stmt->close();
    }
}


/* ==========================================
   DATOS PARA MOSTRAR
========================================== */

if ($usuario_logueado && $usuario) {

    $nombre = htmlspecialchars($usuario['nombre']);
    $apellido = htmlspecialchars($usuario['apellido']);

    $nombre_completo = $nombre . " " . $apellido;

    /*
     * Obtener iniciales automáticamente
     */

    $inicial_nombre = strtoupper(substr($usuario['nombre'], 0, 1));
    $inicial_apellido = strtoupper(substr($usuario['apellido'], 0, 1));

    $iniciales = $inicial_nombre . $inicial_apellido;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Perfil - RifaGo</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

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

        <a href="../index.php" class="logo">
            <span class="logo-blue">Rifa</span><span class="logo-red">Go</span><sup>+</sup>
        </a>


        <a
            href="usuario.php?id=<?php echo $_SESSION['id_usuario']; ?>"
            class="user-icon"
        >

            <?php if ($usuario_logueado && $usuario): ?>

                <span>
                    <?php echo $iniciales; ?>
                </span>

            <?php else: ?>

                <span>?</span>

            <?php endif; ?>

        </a>

    </header>


    <!-- ==========================================
         CONTENIDO
    =========================================== -->

    <main class="main-content">


        <?php if ($usuario_logueado && $usuario): ?>


            <!-- ==========================================
                 PERFIL CON SESIÓN
            =========================================== -->

            <section class="profile-header">


                <div class="profile-avatar">

                    <?php echo $iniciales; ?>

                </div>


                <div class="profile-name">

                    <h1>
                        <?php echo $nombre_completo; ?>
                    </h1>


                    <a
                        href="usuario.php?id=<?php echo $_SESSION['id_usuario']; ?>"
                        class="edit-profile"
                    >
                        Ver perfil
                    </a>

                </div>

            </section>


            <!-- ==========================================
                 OPCIONES DEL PERFIL
            =========================================== -->

            <section class="profile-menu">


                <!-- MIS DATOS -->

                <a
                    href="mis_datos.php"
                    class="profile-option"
                >

                    <span class="option-icon">
                        ♙
                    </span>

                    <span class="option-text">
                        Mis datos
                    </span>

                    <span class="option-arrow">
                        ›
                    </span>

                </a>


                <!-- MÉTODOS DE PAGO -->

                <a
                    href="metodos_pago.php"
                    class="profile-option"
                >

                    <span class="option-icon">
                        ▣
                    </span>

                    <span class="option-text">
                        Métodos de pago
                    </span>

                    <span class="option-arrow">
                        ›
                    </span>

                </a>


                <!-- HISTORIAL -->

                <a
                    href="historial.php"
                    class="profile-option"
                >

                    <span class="option-icon">
                        ◷
                    </span>

                    <span class="option-text">
                        Historial de transacciones
                    </span>

                    <span class="option-arrow">
                        ›
                    </span>

                </a>


                <!-- SEGURIDAD -->

                <a
                    href="seguridad.php"
                    class="profile-option"
                >

                    <span class="option-icon">
                        ◉
                    </span>

                    <span class="option-text">
                        Seguridad
                    </span>

                    <span class="option-arrow">
                        ›
                    </span>

                </a>


                <!-- CONFIGURACIÓN -->

                <a
                    href="configuracion.php"
                    class="profile-option"
                >

                    <span class="option-icon">
                        ⚙
                    </span>

                    <span class="option-text">
                        Configuración
                    </span>

                    <span class="option-arrow">
                        ›
                    </span>

                </a>


            </section>


            <!-- ==========================================
                 CERRAR SESIÓN
            =========================================== -->

            <a
                href="../acciones/logout.php"
                class="logout-button"
            >

                <span>
                    ⎋
                </span>

                Cerrar sesión

            </a>


        <?php else: ?>


            <!-- ==========================================
                 PERFIL SIN SESIÓN
            =========================================== -->

            <section class="profile-header">


                <div class="profile-avatar">
                    ?
                </div>


                <div class="profile-name">

                    <h1>
                        Mi perfil
                    </h1>

                    <p>
                        Iniciá sesión para acceder a tu perfil.
                    </p>

                </div>

            </section>


            <!-- ==========================================
                 AVISO
            =========================================== -->

            <section class="profile-menu">

                <div class="profile-option">

                    <span class="option-icon">
                        ♙
                    </span>

                    <span class="option-text">
                        Registrate para acceder a tu perfil
                    </span>

                </div>

            </section>


            <!-- ==========================================
                 BOTONES
            =========================================== -->

            <a
                href="../registro.php"
                class="edit-profile"
            >
                Crear una cuenta
            </a>


            <br><br>


            <a
                href="../login.php"
                class="edit-profile"
            >
                Iniciar sesión
            </a>


        <?php endif; ?>


    </main>


    <!-- ==========================================
         NAVEGACIÓN INFERIOR
    =========================================== -->

    <nav class="bottom-nav">


        <!-- INICIO -->

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


        <!-- MIS RIFAS -->

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


        <!-- CREAR RIFA -->

        <button
            class="create-button"
            onclick="crearRifa()"
        >

            <span>
                +
            </span>

        </button>


        <!-- PARTICIPACIONES -->

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


        <!-- PERFIL -->

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


<!-- ==========================================
     JAVASCRIPT
=========================================== -->

<script>

function crearRifa() {

    <?php if ($usuario_logueado): ?>

        window.location.href = "crear_rifa.php";

    <?php else: ?>

        window.location.href = "../login.php";

    <?php endif; ?>

}

</script>


</body>
</html>