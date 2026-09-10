<?php

require_once "../conexion.php";
session_start();

$usuario_logueado = isset($_SESSION["id_usuario"]);

$mensaje = "";
$error = "";

$id_usuario = $_SESSION["id_usuario"] ?? null;


// ==================================================
// VARIABLES DEL FORMULARIO
// ==================================================

$nombre_premio = "";
$descripcion = "";
$precio_numero = "";
$cantidad_numeros = "";
$fecha_sorteo = "";
$metodo_sorteo = "Automatico";

$ruta_imagen = "";

$id_borrador = null;


// ==================================================
// SI VIENE UN ID, BUSCAMOS EL BORRADOR
// ==================================================

if (
    $usuario_logueado &&
    isset($_GET["id"]) &&
    is_numeric($_GET["id"])
) {

    $id_borrador = intval($_GET["id"]);


    $consulta = $conexion->prepare("
        SELECT
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
        WHERE id_rifa = ?
        AND id_usuario = ?
        AND estado = 'borrador'
        LIMIT 1
    ");


    $consulta->bind_param(
        "ii",
        $id_borrador,
        $id_usuario
    );


    $consulta->execute();

    $resultado = $consulta->get_result();


    if ($resultado->num_rows === 1) {

        $rifa = $resultado->fetch_assoc();


        $nombre_premio = $rifa["titulo"] ?? "";
        $descripcion = $rifa["descripcion"] ?? "";
        $precio_numero = $rifa["precio_numero"] ?? "";
        $cantidad_numeros = $rifa["cantidad_numeros"] ?? "";
        $fecha_sorteo = $rifa["fecha_sorteo"] ?? "";
        $ruta_imagen = $rifa["imagen"] ?? "";


    } else {

        // El borrador no existe o no pertenece al usuario

        $id_borrador = null;

        $error = "El borrador no existe o no tenés permiso para editarlo.";

    }


    $consulta->close();
}


// ==================================================
// PROCESAR FORMULARIO
// ==================================================

if (
    $usuario_logueado &&
    $_SERVER["REQUEST_METHOD"] === "POST"
) {


    // ==================================================
    // RECIBIR DATOS
    // ==================================================

    $nombre_premio = trim(
        $_POST["nombre_premio"] ?? ""
    );

    $descripcion = trim(
        $_POST["descripcion"] ?? ""
    );

    $precio_numero = $_POST["precio_numero"] ?? "";

    $cantidad_numeros = $_POST["cantidad_numeros"] ?? "";

    $fecha_sorteo = $_POST["fecha_sorteo"] ?? "";

    $metodo_sorteo = $_POST["metodo_sorteo"] ?? "Automatico";

    $accion = $_POST["accion"] ?? "publicar";

    $id_borrador_post = $_POST["id_borrador"] ?? "";



    if (
        is_numeric($id_borrador_post) &&
        intval($id_borrador_post) > 0
    ) {

        $id_borrador = intval($id_borrador_post);

    }



    // ==================================================
    // GUARDAR IMAGEN
    // ==================================================

    $nueva_imagen = null;


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


                $nueva_imagen =
                    $carpeta
                    . $nombre_archivo;


                if (
                    !move_uploaded_file(
                        $_FILES["imagen"]["tmp_name"],
                        $nueva_imagen
                    )
                ) {

                    $error =
                        "No se pudo guardar la imagen.";

                }

            }

        }

    }



    // ==================================================
    // GUARDAR COMO BORRADOR
    // ==================================================

    if (
        empty($error) &&
        $accion === "borrador"
    ) {


        try {

            $conexion->begin_transaction();


            // ------------------------------------------
            // IMAGEN
            // ------------------------------------------

            $imagen_final = $nueva_imagen;


            /*
             * Si no se subió una imagen nueva y estamos
             * editando un borrador, conservamos la anterior.
             */

            if (
                empty($imagen_final) &&
                !empty($id_borrador)
            ) {

                $consulta_imagen = $conexion->prepare("
                    SELECT imagen
                    FROM rifas
                    WHERE id_rifa = ?
                    AND id_usuario = ?
                    AND estado = 'borrador'
                    LIMIT 1
                ");


                $consulta_imagen->bind_param(
                    "ii",
                    $id_borrador,
                    $id_usuario
                );


                $consulta_imagen->execute();

                $resultado_imagen =
                    $consulta_imagen->get_result();


                if (
                    $fila_imagen =
                    $resultado_imagen->fetch_assoc()
                ) {

                    $imagen_final =
                        $fila_imagen["imagen"];

                }


                $consulta_imagen->close();

            }



            // ------------------------------------------
            // ACTUALIZAR BORRADOR EXISTENTE
            // ------------------------------------------

            if (!empty($id_borrador)) {


                $estado = "borrador";


                $consulta = $conexion->prepare("
                    UPDATE rifas
                    SET
                        titulo = ?,
                        descripcion = ?,
                        premio = ?,
                        imagen = ?,
                        precio_numero = NULLIF(?, ''),
                        cantidad_numeros = NULLIF(?, ''),
                        fecha_sorteo = NULLIF(?, ''),
                        estado = ?
                    WHERE id_rifa = ?
                    AND id_usuario = ?
                    AND estado = 'borrador'
                ");


                $consulta->bind_param(
                    "ssssssssii",
                    $nombre_premio,
                    $descripcion,
                    $nombre_premio,
                    $imagen_final,
                    $precio_numero,
                    $cantidad_numeros,
                    $fecha_sorteo,
                    $estado,
                    $id_borrador,
                    $id_usuario
                );


                if (!$consulta->execute()) {

                    throw new Exception(
                        "No se pudo actualizar el borrador."
                    );

                }


                $consulta->close();


            } else {


                // ------------------------------------------
                // CREAR NUEVO BORRADOR
                // ------------------------------------------

                $estado = "borrador";


                $consulta = $conexion->prepare("
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
                    VALUES (?, ?, ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), ?)
                ");


                $consulta->bind_param(
                    "issssssss",
                    $id_usuario,
                    $nombre_premio,
                    $descripcion,
                    $nombre_premio,
                    $imagen_final,
                    $precio_numero,
                    $cantidad_numeros,
                    $fecha_sorteo,
                    $estado
                );


                if (!$consulta->execute()) {

                    throw new Exception(
                        "No se pudo guardar el borrador."
                    );

                }


                $id_borrador =
                    $conexion->insert_id;


                $consulta->close();

            }


            $conexion->commit();


            header(
                "Location: mis_rifas.php"
            );

            exit;


        } catch (Exception $e) {


            $conexion->rollback();


            if (
                !empty($nueva_imagen) &&
                file_exists($nueva_imagen)
            ) {

                unlink($nueva_imagen);

            }


            $error =
                "No se pudo guardar el borrador. "
                . $e->getMessage();

        }

    }



    // ==================================================
    // PUBLICAR RIFA
    // ==================================================

    elseif (
        empty($error) &&
        $accion === "publicar"
    ) {


        // ------------------------------------------
        // VALIDACIONES
        // ------------------------------------------

        if (
            empty($nombre_premio) ||
            empty($descripcion) ||
            empty($precio_numero) ||
            empty($cantidad_numeros) ||
            empty($fecha_sorteo)
        ) {

            $error =
                "Completá todos los campos obligatorios.";

        } elseif (
            !is_numeric($precio_numero) ||
            $precio_numero <= 0
        ) {

            $error =
                "El precio por número debe ser mayor a 0.";

        } elseif (
            !is_numeric($cantidad_numeros) ||
            $cantidad_numeros <= 0
        ) {

            $error =
                "La cantidad de números debe ser mayor a 0.";

        } elseif (
            strtotime($fecha_sorteo)
            <
            strtotime(date("Y-m-d"))
        ) {

            $error =
                "La fecha del sorteo no puede ser anterior a hoy.";

        }



        // ------------------------------------------
        // PUBLICAR
        // ------------------------------------------

        if (empty($error)) {


            try {


                $conexion->begin_transaction();


                $estado_rifa = "activa";


                // ------------------------------------------
                // IMAGEN
                // ------------------------------------------

                $imagen_final = $nueva_imagen;


                /*
                 * Si estamos editando un borrador y no
                 * elegimos una imagen nueva, conservamos
                 * la imagen anterior.
                 */

                if (
                    empty($imagen_final) &&
                    !empty($id_borrador)
                ) {


                    $consulta_imagen = $conexion->prepare("
                        SELECT imagen
                        FROM rifas
                        WHERE id_rifa = ?
                        AND id_usuario = ?
                        AND estado = 'borrador'
                        LIMIT 1
                    ");


                    $consulta_imagen->bind_param(
                        "ii",
                        $id_borrador,
                        $id_usuario
                    );


                    $consulta_imagen->execute();

                    $resultado_imagen =
                        $consulta_imagen->get_result();


                    if (
                        $fila_imagen =
                        $resultado_imagen->fetch_assoc()
                    ) {

                        $imagen_final =
                            $fila_imagen["imagen"];

                    }


                    $consulta_imagen->close();

                }



                // ------------------------------------------
                // SI ES BORRADOR → ACTUALIZAR
                // ------------------------------------------

                if (!empty($id_borrador)) {


                    $consulta_rifa = $conexion->prepare("
                        UPDATE rifas
                        SET
                            titulo = ?,
                            descripcion = ?,
                            premio = ?,
                            imagen = ?,
                            precio_numero = ?,
                            cantidad_numeros = ?,
                            fecha_sorteo = ?,
                            estado = ?
                        WHERE id_rifa = ?
                        AND id_usuario = ?
                        AND estado = 'borrador'
                    ");


                    $consulta_rifa->bind_param(
                        "ssssdissii",
                        $nombre_premio,
                        $descripcion,
                        $nombre_premio,
                        $imagen_final,
                        $precio_numero,
                        $cantidad_numeros,
                        $fecha_sorteo,
                        $estado_rifa,
                        $id_borrador,
                        $id_usuario
                    );


                    if (!$consulta_rifa->execute()) {

                        throw new Exception(
                            "No se pudo publicar la rifa."
                        );

                    }


                    $id_rifa = $id_borrador;


                    $consulta_rifa->close();


                } else {


                    // ------------------------------------------
                    // NUEVA RIFA
                    // ------------------------------------------

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


                    $consulta_rifa->bind_param(
                        "issssdiss",
                        $id_usuario,
                        $nombre_premio,
                        $descripcion,
                        $nombre_premio,
                        $imagen_final,
                        $precio_numero,
                        $cantidad_numeros,
                        $fecha_sorteo,
                        $estado_rifa
                    );


                    if (!$consulta_rifa->execute()) {

                        throw new Exception(
                            "No se pudo guardar la rifa."
                        );

                    }


                    $id_rifa =
                        $conexion->insert_id;


                    $consulta_rifa->close();

                }



                // ------------------------------------------
                // CREAR NÚMEROS DE LA RIFA
                // ------------------------------------------

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
                    $numero <= intval($cantidad_numeros);
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


                // ------------------------------------------
                // CONFIRMAR
                // ------------------------------------------

                $conexion->commit();


                header(
                    "Location: mis_rifas.php"
                );

                exit;


            } catch (Exception $e) {


                $conexion->rollback();


                if (
                    !empty($nueva_imagen) &&
                    file_exists($nueva_imagen)
                ) {

                    unlink($nueva_imagen);

                }


                $error =
                    "No se pudo publicar la rifa. "
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

    <title>
        <?= !empty($id_borrador)
            ? "Editar borrador - RifaGo"
            : "Crear rifa - RifaGo"
        ?>
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


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

</head>


<body>


<div class="app crear-rifa-app">


    <!-- ==========================================
         HEADER
    =========================================== -->

    <header class="header crear-header">


        <a
            href="mis_rifas.php"
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



    <!-- ==========================================
         CONTENIDO
    =========================================== -->

    <main class="main-content crear-main">


        <?php if ($usuario_logueado): ?>


            <!-- ==========================================
                 TÍTULO
            =========================================== -->

            <div class="page-title crear-title">

                <h1>

                    <?= !empty($id_borrador)
                        ? "Editar borrador"
                        : "Crear nueva rifa"
                    ?>

                </h1>


                <p>

                    <?= !empty($id_borrador)
                        ? "Continuá completando los datos de tu rifa."
                        : "Completá los datos para publicar tu rifa."
                    ?>

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
                id="formCrearRifa"
            >


                <?php if (!empty($id_borrador)): ?>

                    <input
                        type="hidden"
                        name="id_borrador"
                        value="<?= $id_borrador ?>"
                    >

                <?php endif; ?>



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
                                value="<?= htmlspecialchars($nombre_premio) ?>"
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
                            ><?= htmlspecialchars($descripcion) ?></textarea>


                        </div>

                        <div class="form-group"> 
                            <select name="categoria" required>
                                <option value="">Seleccionar categoría</option>

                                <option value="Vehículos">Vehículos</option>
                                <option value="Tecnología">Tecnología</option>
                                <option value="Electrónica">Electrónica</option>
                                <option value="Hogar">Hogar</option>
                                <option value="Dinero">Dinero</option>
                                <option value="Viajes">Viajes</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>

                        <!-- IMAGEN -->

                        <div class="form-group">


                            <label for="imagen">

                                Imagen del premio

                            </label>


                            <?php if (!empty($ruta_imagen)): ?>

                                <div class="current-image">

                                    <img
                                        src="<?= htmlspecialchars($ruta_imagen) ?>"
                                        alt="Imagen actual"
                                    >

                                    <small>
                                        Imagen actual
                                    </small>

                                </div>

                            <?php endif; ?>


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
                                        <?= !empty($ruta_imagen)
                                            ? "Cambiar imagen"
                                            : "Agregar imagen"
                                        ?>
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
                                    value="<?= htmlspecialchars($precio_numero) ?>"
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


                                <option
                                    value="100"
                                    <?= $cantidad_numeros == 100 ? "selected" : "" ?>
                                >
                                    100 números
                                </option>


                                <option
                                    value="500"
                                    <?= $cantidad_numeros == 500 ? "selected" : "" ?>
                                >
                                    500 números
                                </option>


                                <option
                                    value="1000"
                                    <?= $cantidad_numeros == 1000 ? "selected" : "" ?>
                                >
                                    1.000 números
                                </option>


                                <option
                                    value="2000"
                                    <?= $cantidad_numeros == 2000 ? "selected" : "" ?>
                                >
                                    2.000 números
                                </option>


                                <option
                                    value="5000"
                                    <?= $cantidad_numeros == 5000 ? "selected" : "" ?>
                                >
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
                                value="<?= htmlspecialchars($fecha_sorteo) ?>"
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


                            <!-- GUARDAR BORRADOR -->

                            <button
                                type="submit"
                                name="accion"
                                value="borrador"
                                class="secondary-button"
                            >
                                Guardar borrador
                            </button>


                            <!-- PUBLICAR -->

                            <button
                                type="submit"
                                name="accion"
                                value="publicar"
                                class="primary-button"
                            >
                                Publicar rifa
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
                    href="../registro.php"
                    class="primary-button"
                >
                    Crear una cuenta
                </a>


                <br><br>


                <a
                    href="../login.php"
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



<script src="../assets/js/crear_rifa.js"></script>


</body>

</html>