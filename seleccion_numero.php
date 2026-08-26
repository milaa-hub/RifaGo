<?php

require_once "conexion.php";
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id_rifa'])) {
    header("Location: index.php");
    exit;
}

$id_rifa = intval($_GET['id_rifa']);

/* ============================
   DATOS DE LA RIFA
============================ */

$sql = "SELECT * FROM rifas WHERE id_rifa = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_rifa);
$stmt->execute();

$resultado_rifa = $stmt->get_result();

if ($resultado_rifa->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$rifa = $resultado_rifa->fetch_assoc();

/* ============================
   NÚMEROS
============================ */

$sql_numeros = "
    SELECT *
    FROM numeros_rifa
    WHERE id_rifa = ?
    ORDER BY numero ASC
";

$stmt = $conexion->prepare($sql_numeros);
$stmt->bind_param("i", $id_rifa);
$stmt->execute();

$numeros = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Seleccioná tus números - RifaGo</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

</head>

<body>

<div class="app-container">

    <header class="page-header">

        <a href="detalle_rifa.php?id_rifa=<?= $id_rifa ?>" class="back-button">
            ←
        </a>

        <div>
            <h1>Seleccioná tus números</h1>
            <p>
                $<?= number_format($rifa['precio_numero'], 0, ',', '.') ?> por número
            </p>
        </div>

    </header>


    <!-- REFERENCIA DE ESTADOS -->

    <div class="number-info">

        <div>
            <span class="legend selected"></span>
            Seleccionado
        </div>

        <div>
            <span class="legend available"></span>
            Disponible
        </div>

        <div>
            <span class="legend unavailable"></span>
            Ocupado
        </div>

    </div>


    <!-- NÚMEROS -->

    <form action="resumen_compra.php" method="POST" id="formNumeros">

        <input
            type="hidden"
            name="id_rifa"
            value="<?= $id_rifa ?>"
        >

        <div class="numbers-grid">

            <?php while ($numero = $numeros->fetch_assoc()): ?>

                <?php

                $ocupado =
                    $numero['estado'] !== 'disponible';

                ?>

                <label
                    class="number-item <?= $ocupado ? 'number-disabled' : '' ?>"
                >

                    <input
                        type="checkbox"
                        name="numeros[]"
                        value="<?= $numero['id_numero'] ?>"
                        data-numero="<?= htmlspecialchars($numero['numero']) ?>"
                        data-precio="<?= $rifa['precio_numero'] ?>"
                        <?= $ocupado ? 'disabled' : '' ?>
                    >

                    <span>
                        <?= str_pad($numero['numero'], 2, '0', STR_PAD_LEFT) ?>
                    </span>

                </label>

            <?php endwhile; ?>

        </div>


        <div class="selection-bottom">

            <div>

                <span id="cantidadSeleccionada">
                    0
                </span>

                números seleccionados

                <strong id="totalSeleccionado">
                    $0
                </strong>

            </div>

            <button
                type="submit"
                class="btn-primary"
                id="btnContinuar"
                disabled
            >
                Continuar
            </button>

        </div>

    </form>

</div>


<script src="assets/js/seleccion_numeros.js"></script>


</body>

</html>