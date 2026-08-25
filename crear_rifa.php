<?php

require_once "conexion.php";

session_start();

$usuario_logueado = isset($_SESSION["id_usuario"]);

$mensaje = "";
$error = "";


// ==================================================
// PROCESAR FORMULARIO
// ==================================================

if ($usuario_logueado && $_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre_premio = trim($_POST["nombre_premio"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $precio_numero = $_POST["precio_numero"] ?? "";
    $cantidad_numeros = $_POST["cantidad_numeros"] ?? "";
    $fecha_sorteo = $_POST["fecha_sorteo"] ?? "";

    $ruta_imagen = null;


    // ==================================================
    // VALIDACIONES
    // ==================================================

    if (
        empty($nombre_premio) ||
        empty($descripcion) ||
        empty($precio_numero) ||
        empty($cantidad_numeros) ||
        empty($fecha_sorteo)
    ) {

        $error = "Completá todos los campos obligatorios.";

    } elseif (
        !is_numeric($precio_numero) ||
        $precio_numero <= 0
    ) {

        $error = "El precio por número debe ser mayor a 0.";

    } elseif (
        !is_numeric($cantidad_numeros) ||
        $cantidad_numeros <= 0
    ) {

        $error = "La cantidad de números debe ser mayor a 0.";

    } elseif (
        strtotime($fecha_sorteo) < strtotime(date("Y-m-d"))
    ) {

        $error = "La fecha del sorteo no puede ser anterior a hoy.";

    } else {


        // ==================================================
        // GUARDAR IMAGEN
        // ==================================================

        if (
            isset($_FILES["imagen"]) &&
            $_FILES["imagen"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if (
                $_FILES["imagen"]["error"] !== UPLOAD_ERR_OK
            ) {

                $error = "Hubo un problema al subir la imagen.";

            } else {

                $extension = strtolower(
                    pathinfo(
                        $_FILES["imagen"]["name"],
                        PATHINFO_EXTENSION
                    )
                );


                $extensiones_permitidas = [
                    "jpg",
                    "jpeg",
                    "png",
                    "webp"
                ];


                if (
                    !in_array(
                        $extension,
                        $extensiones_permitidas
                    )
                ) {

                    $error = "La imagen debe ser JPG, PNG o WEBP.";

                } else {

                    $carpeta = "uploads/rifas/";


                    if (!is_dir($carpeta)) {

                        mkdir(
                            $carpeta,
                            0777,
                            true
                        );

                    }


                    $nombre_archivo =
                        uniqid("rifa_", true)
                        . "."
                        . $extension;


                    $ruta_imagen =
                        $carpeta
                        . $nombre_archivo;


                    if (
                        !move_uploaded_file(
                            $_FILES["imagen"]["tmp_name"],
                            $ruta_imagen
                        )
                    ) {

                        $error =
                            "No se pudo guardar la imagen.";

                    }

                }

            }

        }


        // ==================================================
        // GUARDAR RIFA EN LA BASE DE DATOS
        // ==================================================

        if (empty($error)) {

            try {

                /*
                 * Iniciamos una transacción.
                 *
                 * Si algo falla al guardar la rifa
                 * o sus números, se deshace todo.
                 */

                $conexion->begin_transaction();


                $estado_rifa = "activa";


                // ==========================================
                // INSERTAR RIFA
                // ==========================================

                $consulta_rifa = $conexion->prepare("
                    INSERT INTO rifas (
                        id_usuario,
                        titulo,
                        descripcion,
                        premio,
                        imagen,
                        precio_numero,
                        cantidad_numeros,
                        fecha_sorteo,
                        estado
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");


                if (!$consulta_rifa) {

                    throw new Exception(
                        "Error al preparar la rifa: "
                        . $conexion->error
                    );

                }


                /*
                 * Como actualmente el formulario tiene
                 * un solo campo para el nombre del premio,
                 * usamos ese mismo valor como:
                 *
                 * titulo
                 * premio
                 */

                $consulta_rifa->bind_param(
                    "issssdiss",
                    $_SESSION["id_usuario"],
                    $nombre_premio,
                    $descripcion,
                    $nombre_premio,
                    $ruta_imagen,
                    $precio_numero,
                    $cantidad_numeros,
                    $fecha_sorteo,
                    $estado_rifa
                );


                if (!$consulta_rifa->execute()) {

                    throw new Exception(
                        "No se pudo guardar la rifa: "
                        . $consulta_rifa->error
                    );

                }


                // ID de la rifa recién creada

                $id_rifa = $conexion->insert_id;


                $consulta_rifa->close();



                // ==========================================
                // CREAR NÚMEROS
                // ==========================================

                $estado_numero = "disponible";


                $consulta_numero = $conexion->prepare("
                    INSERT INTO numeros_rifa (
                        id_rifa,
                        numero,
                        estado
                    )
                    VALUES (?, ?, ?)
                ");


                if (!$consulta_numero) {

                    throw new Exception(
                        "Error al preparar los números: "
                        . $conexion->error
                    );

                }


                for (
                    $numero = 1;
                    $numero <= $cantidad_numeros;
                    $numero++
                ) {

                    $consulta_numero->bind_param(
                        "iis",
                        $id_rifa,
                        $numero,
                        $estado_numero
                    );


                    if (!$consulta_numero->execute()) {

                        throw new Exception(
                            "No se pudo crear el número "
                            . $numero
                        );

                    }

                }


                $consulta_numero->close();


                // ==========================================
                // CONFIRMAR
                // ==========================================

                $conexion->commit();


                // ==========================================
                // IR A MIS RIFAS
                // ==========================================

                header("Location: mis_rifas.php");

                exit;


            } catch (Exception $e) {

                /*
                 * Si algo falló, deshacemos todos
                 * los INSERT realizados.
                 */

                $conexion->rollback();


                // Eliminar imagen si ya se había guardado

                if (
                    !empty($ruta_imagen) &&
                    file_exists($ruta_imagen)
                ) {

                    unlink($ruta_imagen);

                }


                $error =
                    "No se pudo crear la rifa. "
                    . $e->getMessage();

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

    <title>Crear rifa - RifaGo</title>


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


<div class="app crear-rifa-app">


    <!-- ==========================================
         HEADER
    =========================================== -->

    <header class="header crear-header">


        <a
            href="inicio.php"
            class="back-button"
        >
            ‹
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


        <div class="header-space"></div>


    </header>



    <!-- ==========================================
         CONTENIDO
    =========================================== -->

    <main class="main-content crear-main">


        <?php if ($usuario_logueado): ?>


            <!-- ==========================================
                 USUARIO LOGUEADO
            =========================================== -->


            <div class="page-title crear-title">

                <h1>
                    Crear nueva rifa
                </h1>

                <p>
                    Completá los datos para publicar tu rifa.
                </p>

            </div>



            <!-- ==========================================
                 PASOS
            =========================================== -->

            <div class="steps">


                <div class="step active">

                    <span>
                        1
                    </span>

                    <small>
                        Datos
                    </small>

                </div>


                <div class="step-line"></div>


                <div class="step">

                    <span>
                        2
                    </span>

                    <small>
                        Configuración
                    </small>

                </div>


                <div class="step-line"></div>


                <div class="step">

                    <span>
                        3
                    </span>

                    <small>
                        Publicar
                    </small>

                </div>


            </div>



            <!-- ==========================================
                 MENSAJE ERROR
            =========================================== -->

            <?php if (!empty($error)): ?>

                <div class="form-message error-message">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>



            <!-- ==========================================
                 FORMULARIO
            =========================================== -->

            <form
                method="POST"
                action=""
                enctype="multipart/form-data"
                class="crear-rifa-form"
            >


                <!-- ==========================================
                     PASO 1
                =========================================== -->

                <section
                    class="crear-step active"
                    id="step-1"
                >


                    <div class="form-card">


                        <div class="form-card-title">


                            <span class="title-icon">
                                1
                            </span>


                            <div>

                                <h2>
                                    Datos de la rifa
                                </h2>

                                <p>
                                    Contanos qué premio vas a sortear.
                                </p>

                            </div>


                        </div>



                        <!-- NOMBRE DEL PREMIO -->

                        <div class="form-group">


                            <label for="nombre_premio">

                                Nombre del premio

                                <span>
                                    *
                                </span>

                            </label>


                            <input
                                type="text"
                                id="nombre_premio"
                                name="nombre_premio"
                                placeholder="Ej: iPhone 15 Pro Max"
                                value="<?= htmlspecialchars($_POST["nombre_premio"] ?? "") ?>"
                                required
                            >


                        </div>



                        <!-- DESCRIPCIÓN -->

                        <div class="form-group">


                            <label for="descripcion">

                                Descripción del premio

                                <span>
                                    *
                                </span>

                            </label>


                            <textarea
                                id="descripcion"
                                name="descripcion"
                                rows="4"
                                placeholder="Contá los detalles del premio..."
                                required
                            ><?= htmlspecialchars($_POST["descripcion"] ?? "") ?></textarea>


                        </div>



                        <!-- IMAGEN -->

                        <div class="form-group">


                            <label for="imagen">

                                Imagen del premio

                            </label>


                            <div class="image-upload">


                                <input
                                    type="file"
                                    id="imagen"
                                    name="imagen"
                                    accept="image/jpeg,image/png,image/webp"
                                >


                                <label
                                    for="imagen"
                                    class="upload-box"
                                >

                                    <span class="upload-icon">
                                        +
                                    </span>

                                    <strong>
                                        Agregar imagen
                                    </strong>

                                    <small>
                                        JPG, PNG o WEBP
                                    </small>

                                </label>


                            </div>


                        </div>



                        <!-- CONTINUAR -->

                        <button
                            type="button"
                            class="primary-button next-button"
                            id="ir-configuracion"
                        >
                            Continuar
                        </button>


                    </div>


                </section>



                <!-- ==========================================
                     PASO 2
                =========================================== -->

                <section
                    class="crear-step"
                    id="step-2"
                >


                    <div class="form-card">


                        <div class="form-card-title">


                            <span class="title-icon">
                                2
                            </span>


                            <div>

                                <h2>
                                    Configurar sorteo
                                </h2>

                                <p>
                                    Definí cómo funcionará tu rifa.
                                </p>

                            </div>


                        </div>



                        <!-- PRECIO -->

                        <div class="form-group">


                            <label for="precio_numero">

                                Precio por número

                                <span>
                                    *
                                </span>

                            </label>


                            <div class="input-prefix">


                                <span>
                                    $
                                </span>


                                <input
                                    type="number"
                                    id="precio_numero"
                                    name="precio_numero"
                                    min="1"
                                    step="0.01"
                                    placeholder="1000"
                                    value="<?= htmlspecialchars($_POST["precio_numero"] ?? "") ?>"
                                    required
                                >


                            </div>


                        </div>



                        <!-- CANTIDAD -->

                        <div class="form-group">


                            <label for="cantidad_numeros">

                                Cantidad de números

                                <span>
                                    *
                                </span>

                            </label>


                            <select
                                id="cantidad_numeros"
                                name="cantidad_numeros"
                                required
                            >


                                <option value="">
                                    Seleccioná una cantidad
                                </option>


                                <option value="100">
                                    100 números
                                </option>


                                <option value="500">
                                    500 números
                                </option>


                                <option value="1000">
                                    1.000 números
                                </option>


                                <option value="2000">
                                    2.000 números
                                </option>


                                <option value="5000">
                                    5.000 números
                                </option>


                            </select>


                        </div>



                        <!-- FECHA -->

                        <div class="form-group">


                            <label for="fecha_sorteo">

                                Fecha del sorteo

                                <span>
                                    *
                                </span>

                            </label>


                            <input
                                type="date"
                                id="fecha_sorteo"
                                name="fecha_sorteo"
                                min="<?= date("Y-m-d") ?>"
                                value="<?= htmlspecialchars($_POST["fecha_sorteo"] ?? "") ?>"
                                required
                            >


                        </div>



                        <!-- MÉTODO -->

                        <div class="form-group">


                            <label for="metodo_sorteo">

                                Método de sorteo

                            </label>


                            <select
                                id="metodo_sorteo"
                                name="metodo_sorteo"
                            >


                                <option value="Automatico">
                                    Automático
                                </option>


                                <option value="Manual">
                                    Manual
                                </option>


                            </select>


                        </div>



                        <!-- RESUMEN -->

                        <div class="config-summary">


                            <div>

                                <span>
                                    Precio por número
                                </span>

                                <strong id="resumen-precio">
                                    $0
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Cantidad de números
                                </span>

                                <strong id="resumen-cantidad">
                                    0
                                </strong>

                            </div>


                            <div class="summary-total">

                                <span>
                                    Recaudación máxima
                                </span>

                                <strong id="resumen-total">
                                    $0
                                </strong>

                            </div>


                        </div>



                        <!-- BOTONES -->

                        <div class="form-buttons">


                            <button
                                type="button"
                                class="secondary-button"
                                id="volver-datos"
                            >
                                Volver
                            </button>


                            <button
                                type="submit"
                                class="primary-button"
                            >
                                Crear rifa
                            </button>


                        </div>


                    </div>


                </section>


            </form>


        <?php else: ?>


            <!-- ==========================================
                 USUARIO SIN SESIÓN
            =========================================== -->


            <section class="profile-header">


                <div class="profile-avatar">
                    +
                </div>


                <div class="profile-name">

                    <h1>
                        Creá tu propia rifa
                    </h1>

                    <p>
                        Registrate para crear y administrar tus rifas en RifaGo.
                    </p>

                </div>


            </section>



            <section class="profile-menu">


                <div class="profile-option">

                    <span class="option-icon">
                        +
                    </span>

                    <span class="option-text">
                        Creá y administrá tus propias rifas
                    </span>

                </div>


                <div class="profile-option">

                    <span class="option-icon">
                        ✓
                    </span>

                    <span class="option-text">
                        Gestioná tus números y participantes
                    </span>

                </div>


                <div class="profile-option">

                    <span class="option-icon">
                        ◉
                    </span>

                    <span class="option-text">
                        Consultá el estado de tus rifas
                    </span>

                </div>


            </section>



            <div
                style="
                    text-align: center;
                    margin-top: 25px;
                "
            >


                <p>
                    Necesitás una cuenta para crear una rifa.
                </p>


                <a
                    href="registro.php"
                    class="primary-button"
                >
                    Crear una cuenta
                </a>


                <br><br>


                <a
                    href="login.php"
                    class="secondary-button"
                >
                    Ya tengo una cuenta
                </a>


            </div>


        <?php endif; ?>


    </main>



    <!-- ==========================================
         NAVEGACIÓN
    =========================================== -->

    <nav class="bottom-nav">


        <a
            href="inicio.php"
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
            type="button"
            class="create-button active"
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



<script src="assets/js/crear_rifa.js"></script>


</body>

</html>