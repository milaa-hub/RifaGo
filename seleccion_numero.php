<?php

require_once "conexion.php";
session_start();


// ==========================================
// VERIFICAR SESIÓN
// ==========================================

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}


// ==========================================
// VERIFICAR ID DE RIFA
// ==========================================

if (!isset($_GET["id_rifa"]) || !is_numeric($_GET["id_rifa"])) {
    header("Location: rifas.php");
    exit;
}

$id_rifa = intval($_GET["id_rifa"]);


// ==========================================
// OBTENER RIFA
// ==========================================

$sql = "
    SELECT
        id_rifa,
        titulo,
        premio,
        imagen,
        precio_numero,
        cantidad_numeros,
        fecha_sorteo,
        estado
    FROM rifas
    WHERE id_rifa = ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_rifa);
$stmt->execute();

$resultado_rifa = $stmt->get_result();


if ($resultado_rifa->num_rows === 0) {
    header("Location: rifas.php");
    exit;
}

$rifa = $resultado_rifa->fetch_assoc();

$stmt->close();


// ==========================================
// OBTENER NÚMEROS DE LA RIFA
// ==========================================

$sql_numeros = "
    SELECT
        id_numero,
        numero,
        estado
    FROM numeros_rifa
    WHERE id_rifa = ?
    ORDER BY numero ASC
";

$stmt = $conexion->prepare($sql_numeros);
$stmt->bind_param("i", $id_rifa);
$stmt->execute();

$resultado_numeros = $stmt->get_result();


// ==========================================
// GUARDAR NÚMEROS
// ==========================================

$numeros = [];

while ($fila = $resultado_numeros->fetch_assoc()) {
    $numeros[] = $fila;
}

$stmt->close();


// ==========================================
// SI NO EXISTEN NÚMEROS, CREARLOS
// ==========================================
//
// Esto hace que la selección sea dinámica.
// Los números se generan según
// cantidad_numeros de la rifa.
//

if (count($numeros) === 0) {

    $cantidad = intval($rifa["cantidad_numeros"]);

    if ($cantidad > 0) {

        $conexion->begin_transaction();

        try {

            $insertar = $conexion->prepare("
                INSERT INTO numeros_rifa
                (id_rifa, numero, estado)
                VALUES (?, ?, 'disponible')
            ");

            for ($i = 1; $i <= $cantidad; $i++) {

                $insertar->bind_param(
                    "ii",
                    $id_rifa,
                    $i
                );

                $insertar->execute();
            }

            $insertar->close();

            $conexion->commit();


            // Volver a consultar los números

            $stmt = $conexion->prepare("
                SELECT
                    id_numero,
                    numero,
                    estado
                FROM numeros_rifa
                WHERE id_rifa = ?
                ORDER BY numero ASC
            ");

            $stmt->bind_param(
                "i",
                $id_rifa
            );

            $stmt->execute();

            $resultado_numeros = $stmt->get_result();

            $numeros = [];

            while ($fila = $resultado_numeros->fetch_assoc()) {
                $numeros[] = $fila;
            }

            $stmt->close();

        } catch (Exception $e) {

            $conexion->rollback();

            die(
                "No se pudieron generar los números de la rifa."
            );
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
        Seleccioná tus números - RifaGo
    </title>


    <!-- GOOGLE FONT -->

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


    <!-- CSS -->

    <link
        rel="stylesheet"
        href="assets/css/seleccion_numeros.css"
    >

</head>


<body>


<div class="app-container">


    <!-- ==========================================
         HEADER
    =========================================== -->

    <header class="page-header">

        <a
            href="detalle_rifa.php?id=<?= $id_rifa ?>"
            class="back-button"
        >
            ←
        </a>


        <div>

            <h1>
                Seleccioná tus números
            </h1>

            <p>

                $<?= number_format(
                    $rifa["precio_numero"],
                    0,
                    ",",
                    "."
                ) ?>

                por número

            </p>

        </div>

    </header>



    <!-- ==========================================
         REFERENCIA
    =========================================== -->

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



    <!-- ==========================================
         NÚMEROS
    =========================================== -->

    <form
        action="resumen_compra.php"
        method="POST"
        id="formNumeros"
    >

        <input
            type="hidden"
            name="id_rifa"
            value="<?= $id_rifa ?>"
        >


        <div class="numbers-grid">


            <?php if (count($numeros) > 0): ?>


                <?php foreach ($numeros as $numero): ?>


                    <?php

                    $ocupado =
                        strtolower(
                            trim($numero["estado"])
                        ) !== "disponible";

                    ?>


                    <label
                        class="number-item
                        <?= $ocupado
                            ? "number-disabled"
                            : ""
                        ?>"
                    >


                        <input
                            type="checkbox"

                            name="numeros[]"

                            value="<?= $numero["id_numero"] ?>"

                            data-numero="<?= htmlspecialchars(
                                $numero["numero"]
                            ) ?>"

                            data-precio="<?= htmlspecialchars(
                                $rifa["precio_numero"]
                            ) ?>"

                            <?= $ocupado
                                ? "disabled"
                                : ""
                            ?>
                        >


                        <span>

                            <?= str_pad(
                                $numero["numero"],
                                2,
                                "0",
                                STR_PAD_LEFT
                            ) ?>

                        </span>


                    </label>


                <?php endforeach; ?>


            <?php else: ?>


                <div class="no-numbers">

                    No hay números disponibles
                    para esta rifa.

                </div>


            <?php endif; ?>


        </div>



        <!-- ==========================================
             BARRA INFERIOR
        =========================================== -->

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


<script>

document.addEventListener("DOMContentLoaded", function () {

    const checkboxes = document.querySelectorAll(
        'input[name="numeros[]"]:not(:disabled)'
    );

    const cantidadSeleccionada =
        document.getElementById("cantidadSeleccionada");

    const totalSeleccionado =
        document.getElementById("totalSeleccionado");

    const botonContinuar =
        document.getElementById("btnContinuar");


    // ==========================================
    // ACTUALIZAR RESUMEN
    // ==========================================

    function actualizarResumen() {

        const seleccionados =
            document.querySelectorAll(
                'input[name="numeros[]"]:checked'
            );


        let cantidad = seleccionados.length;

        let total = 0;


        seleccionados.forEach(function (checkbox) {

            const precio =
                parseFloat(checkbox.dataset.precio);

            if (!isNaN(precio)) {
                total += precio;
            }

        });


        // Mostrar cantidad

        cantidadSeleccionada.textContent =
            cantidad;


        // Mostrar precio

        totalSeleccionado.textContent =
            "$" + total.toLocaleString("es-AR", {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });


        // Activar / desactivar botón

        if (cantidad > 0) {

            botonContinuar.disabled = false;

        } else {

            botonContinuar.disabled = true;

        }

    }


    // ==========================================
    // DETECTAR SELECCIÓN
    // ==========================================

    checkboxes.forEach(function (checkbox) {

        checkbox.addEventListener(
            "change",
            actualizarResumen
        );

    });


    // ==========================================
    // ESTADO INICIAL
    // ==========================================

    actualizarResumen();

});

</script>


</body>

</html>