<?php

require_once "conexion.php";

$mensaje = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre_premio = trim($_POST["nombre_premio"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $precio_numero = $_POST["precio_numero"] ?? "";
    $cantidad_numeros = $_POST["cantidad_numeros"] ?? "";
    $fecha_sorteo = $_POST["fecha_sorteo"] ?? "";
    $metodo_sorteo = $_POST["metodo_sorteo"] ?? "Automatico";

    if (
        empty($nombre_premio) ||
        empty($descripcion) ||
        empty($precio_numero) ||
        empty($cantidad_numeros) ||
        empty($fecha_sorteo)
    ) {

        $error = "Completá todos los campos obligatorios.";

    } elseif ($precio_numero <= 0) {

        $error = "El precio por número debe ser mayor a 0.";

    } elseif ($cantidad_numeros <= 0) {

        $error = "La cantidad de números debe ser mayor a 0.";

    } else {

        /*
         * Por ahora mostramos el resultado.
         *
         * Cuando conectemos esta pantalla con tu tabla
         * "rifas", acá vamos a hacer el INSERT.
         */

        $mensaje = "Los datos de la rifa están completos.";
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear rifa - RifaGo</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

</head>

<body>

<div class="app crear-rifa-app">

    <!-- HEADER -->

    <header class="header crear-header">

        <a href="index.php" class="back-button">
            ‹
        </a>

        <div class="logo">
            <span class="logo-blue">Rifa</span><span class="logo-red">Go</span><sup>+</sup>
        </div>

        <div class="header-space"></div>

    </header>


    <main class="main-content crear-main">

        <!-- TITULO -->

        <div class="page-title crear-title">

            <h1>Crear nueva rifa</h1>

            <p>
                Completá los datos para publicar tu rifa.
            </p>

        </div>


        <!-- PASOS -->

        <div class="steps">

            <div class="step active">
                <span>1</span>
                <small>Datos</small>
            </div>

            <div class="step-line"></div>

            <div class="step">
                <span>2</span>
                <small>Configuración</small>
            </div>

            <div class="step-line"></div>

            <div class="step">
                <span>3</span>
                <small>Publicar</small>
            </div>

        </div>


        <?php if (!empty($error)): ?>

            <div class="form-message error-message">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <?php if (!empty($mensaje)): ?>

            <div class="form-message success-message">
                <?= htmlspecialchars($mensaje) ?>
            </div>

        <?php endif; ?>


        <!-- FORMULARIO -->

        <form
            method="POST"
            action=""
            enctype="multipart/form-data"
            class="crear-rifa-form"
        >

            <!-- PASO 1 -->

            <section class="crear-step active" id="step-1">

                <div class="form-card">

                    <div class="form-card-title">

                        <span class="title-icon">1</span>

                        <div>
                            <h2>Datos de la rifa</h2>
                            <p>Contanos qué premio vas a sortear.</p>
                        </div>

                    </div>


                    <!-- NOMBRE -->

                    <div class="form-group">

                        <label for="nombre_premio">
                            Nombre del premio <span>*</span>
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


                    <!-- DESCRIPCION -->

                    <div class="form-group">

                        <label for="descripcion">
                            Descripción del premio <span>*</span>
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
                                accept="image/*"
                            >

                            <label for="imagen" class="upload-box">

                                <span class="upload-icon">+</span>

                                <strong>Agregar imagen</strong>

                                <small>
                                    JPG, PNG o WEBP
                                </small>

                            </label>

                        </div>

                    </div>


                    <button
                        type="button"
                        class="primary-button next-button"
                        id="ir-configuracion"
                    >
                        Continuar
                    </button>

                </div>

            </section>


            <!-- PASO 2 -->

            <section class="crear-step" id="step-2">

                <div class="form-card">

                    <div class="form-card-title">

                        <span class="title-icon">2</span>

                        <div>
                            <h2>Configurar sorteo</h2>
                            <p>Definí cómo funcionará tu rifa.</p>
                        </div>

                    </div>


                    <!-- PRECIO -->

                    <div class="form-group">

                        <label for="precio_numero">
                            Precio por número <span>*</span>
                        </label>

                        <div class="input-prefix">

                            <span>$</span>

                            <input
                                type="number"
                                id="precio_numero"
                                name="precio_numero"
                                min="1"
                                placeholder="1000"
                                value="<?= htmlspecialchars($_POST["precio_numero"] ?? "") ?>"
                                required
                            >

                        </div>

                    </div>


                    <!-- CANTIDAD -->

                    <div class="form-group">

                        <label for="cantidad_numeros">
                            Cantidad de números <span>*</span>
                        </label>

                        <select
                            id="cantidad_numeros"
                            name="cantidad_numeros"
                            required
                        >

                            <option value="">
                                Seleccioná una cantidad
                            </option>

                            <option value="100">100 números</option>
                            <option value="500">500 números</option>
                            <option value="1000">1.000 números</option>
                            <option value="2000">2.000 números</option>
                            <option value="5000">5.000 números</option>

                        </select>

                    </div>


                    <!-- FECHA -->

                    <div class="form-group">

                        <label for="fecha_sorteo">
                            Fecha del sorteo <span>*</span>
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


                    <!-- METODO -->

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
                            <span>Precio por número</span>
                            <strong id="resumen-precio">$0</strong>
                        </div>

                        <div>
                            <span>Cantidad de números</span>
                            <strong id="resumen-cantidad">0</strong>
                        </div>

                        <div class="summary-total">
                            <span>Recaudación máxima</span>
                            <strong id="resumen-total">$0</strong>
                        </div>

                    </div>


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

    </main>


    <!-- NAV -->

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
            class="create-button active"
            onclick="window.location.href='crear_rifa.php'"
        >
            <span>+</span>
        </button>


        <a href="participaciones.php" class="nav-item">

            <span class="nav-icon">♧</span>

            <span>Participaciones</span>

        </a>


        <a href="perfil.php" class="nav-item">

            <span class="nav-icon">♙</span>

            <span>Perfil</span>

        </a>

    </nav>

</div>

<script src="assets/js/crear_rifa.js"></script>
</body>
</html>