<?php

require_once "../conexion.php";
session_start();


// ==================================================
// VERIFICAR SESIÓN
// ==================================================

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit;
}

$id_usuario = $_SESSION["id_usuario"];
$usuario_logueado = true;


// ==================================================
// OBTENER ID DE LA RIFA
// ==================================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: mis_rifas.php");
    exit;
}

$id_rifa = (int) $_GET["id"];


// ==================================================
// OBTENER RIFA
// SOLO SI PERTENECE AL USUARIO
// ==================================================

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
    LIMIT 1
");

$consulta->bind_param(
    "ii",
    $id_rifa,
    $id_usuario
);

$consulta->execute();

$resultado = $consulta->get_result();


// ==================================================
// SI NO EXISTE O NO LE PERTENECE
// ==================================================

if ($resultado->num_rows === 0) {
    $consulta->close();

    header("Location: mis_rifas.php");
    exit;
}

$rifa = $resultado->fetch_assoc();

$consulta->close();


// ==================================================
// OBTENER NÚMEROS
// ==================================================

$consulta_numeros = $conexion->prepare("
    SELECT
        id_numero,
        numero,
        estado
    FROM numeros_rifa
    WHERE id_rifa = ?
    ORDER BY numero ASC
");

$consulta_numeros->bind_param(
    "i",
    $id_rifa
);

$consulta_numeros->execute();

$resultado_numeros = $consulta_numeros->get_result();


// ==================================================
// ESTADÍSTICAS DE NÚMEROS
// ==================================================

$total_numeros = (int) $rifa["cantidad_numeros"];

$nucleos_vendidos = 0;
$nucleos_reservados = 0;

$numeros = [];

while ($numero = $resultado_numeros->fetch_assoc()) {

    $estado_numero = strtolower(
        trim($numero["estado"])
    );

    $numero["estado_clase"] = "disponible";

    if (
        $estado_numero === "vendido" ||
        $estado_numero === "vendida"
    ) {

        $numero["estado_clase"] = "vendido";

        $nucleos_vendidos++;

    } elseif (
        $estado_numero === "reservado" ||
        $estado_numero === "reservada"
    ) {

        $numero["estado_clase"] = "reservado";

        $nucleos_reservados++;
    }

    $numeros[] = $numero;
}

$consulta_numeros->close();


// ==================================================
// DISPONIBLES
// ==================================================

$nucleos_disponibles =
    $total_numeros
    - $nucleos_vendidos
    - $nucleos_reservados;

if ($nucleos_disponibles < 0) {
    $nucleos_disponibles = 0;
}


// ==================================================
// PORCENTAJE VENDIDO
// ==================================================

$porcentaje_vendido = 0;

if ($total_numeros > 0) {

    $porcentaje_vendido =
        ($nucleos_vendidos / $total_numeros) * 100;
}


// ==================================================
// RECAUDACIÓN
// ==================================================

$recaudado =
    $nucleos_vendidos *
    (float) $rifa["precio_numero"];


// ==================================================
// CANTIDAD DE PARTICIPANTES
// ==================================================

$consulta_participantes = $conexion->prepare("
    SELECT COUNT(DISTINCT p.id_usuario) AS cantidad
    FROM participaciones p
    INNER JOIN numeros_rifa nr
        ON p.id_numero = nr.id_numero
    WHERE nr.id_rifa = ?
");

$consulta_participantes->bind_param(
    "i",
    $id_rifa
);

$consulta_participantes->execute();

$resultado_participantes =
    $consulta_participantes->get_result();

$fila_participantes =
    $resultado_participantes->fetch_assoc();

$cantidad_participantes =
    (int) $fila_participantes["cantidad"];

$consulta_participantes->close();


// ==================================================
// OBTENER PARTICIPANTES
// ==================================================

$consulta_lista = $conexion->prepare("
    SELECT
        u.nombre,
        u.apellido,
        p.id_participacion,
        p.estado AS estado_participacion,
        nr.numero
    FROM participaciones p

    INNER JOIN usuarios u
        ON p.id_usuario = u.id_usuario

    INNER JOIN numeros_rifa nr
        ON p.id_numero = nr.id_numero

    WHERE nr.id_rifa = ?

    ORDER BY
        u.apellido ASC,
        u.nombre ASC,
        nr.numero ASC
");

$consulta_lista->bind_param(
    "i",
    $id_rifa
);

$consulta_lista->execute();

$resultado_lista =
    $consulta_lista->get_result();


// ==================================================
// FORMATEAR ESTADO DE LA RIFA
// ==================================================

$estado_rifa =
    strtolower(trim($rifa["estado"]));

$estado_texto = "Activa";
$estado_clase = "activa";

if (
    $estado_rifa === "finalizada" ||
    $estado_rifa === "finalizado"
) {

    $estado_texto = "Finalizada";
    $estado_clase = "finalizada";

} elseif (
    $estado_rifa === "borrador" ||
    $estado_rifa === "borradores"
) {

    $estado_texto = "Borrador";
    $estado_clase = "borrador";
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
        Administrar rifa - RifaGo
    </title>


    <!-- GOOGLE FONT -->

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


    <!-- CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/administrar_rifa.css?=v2"
    >

</head>


<body>

<div class="admin-page">


    <!-- ==========================================
         HEADER
    =========================================== -->

    <header class="admin-header">

        <a
            href="mis_rifas.php"
            class="back-button"
        >
            ‹
        </a>


        <div class="admin-logo">

            <span class="logo-blue">Rifa</span><span class="logo-red">Go</span><sup>+</sup>

        </div>


        <a
            href="perfil.php" 
            class="user-icon"
            aria-label="Perfil de usuario"
            style="text-decoration: none;"
        >

            <?php if ($usuario_logueado): ?>

                <span>

                    <?= strtoupper(
                        substr(
                            $_SESSION['nombre'] ?? 'U',
                            0,
                            2
                        )
                    ) ?>

                </span>

            <?php else: ?>

                <span>
                    ?
                </span>

            <?php endif; ?>

        </a>

    </header>



    <!-- ==========================================
         CONTENIDO
    =========================================== -->

    <main class="admin-content">


        <!-- =====================================
             TÍTULO
        ====================================== -->

        <section class="admin-title">

            <span class="section-label">
                ADMINISTRACIÓN
            </span>

            <h1>
                <?= htmlspecialchars($rifa["titulo"]) ?>
            </h1>

            <p>
                Administrá y seguí el estado de tu rifa.
            </p>

        </section>



        <!-- =====================================
             INFORMACIÓN PRINCIPAL
        ====================================== -->

        <section class="raffle-main-card">


            <div class="raffle-main-image">

                <?php if (!empty($rifa["imagen"])): ?>

                    <img
                        src="../<?= htmlspecialchars($rifa["imagen"]) ?>"
                        alt="<?= htmlspecialchars($rifa["premio"]) ?>"
                    >

                <?php else: ?>

                    <div class="no-image">
                        Sin imagen
                    </div>

                <?php endif; ?>

            </div>


            <div class="raffle-main-info">

                <div class="raffle-status <?= $estado_clase ?>">
                    <?= $estado_texto ?>
                </div>


                <h2>
                    <?= htmlspecialchars($rifa["premio"]) ?>
                </h2>


                <?php if (!empty($rifa["descripcion"])): ?>

                    <p class="raffle-description">
                        <?= htmlspecialchars($rifa["descripcion"]) ?>
                    </p>

                <?php endif; ?>


                <div class="raffle-data">

                    <div>
                        <span>
                            Precio por número
                        </span>

                        <strong>
                            $<?= number_format(
                                $rifa["precio_numero"],
                                0,
                                ",",
                                "."
                            ) ?>
                        </strong>
                    </div>


                    <div>
                        <span>
                            Fecha del sorteo
                        </span>

                        <strong>

                            <?= !empty($rifa["fecha_sorteo"])
                                ? date(
                                    "d/m/Y",
                                    strtotime(
                                        $rifa["fecha_sorteo"]
                                    )
                                )
                                : "Sin fecha"
                            ?>

                        </strong>
                    </div>

                </div>

            </div>

        </section>



        <!-- =====================================
             PROGRESO
        ====================================== -->

        <section class="progress-card">

            <div class="progress-header">

                <div>

                    <span>
                        Progreso de la rifa
                    </span>

                    <strong>
                        <?= $nucleos_vendidos ?>
                        /
                        <?= $total_numeros ?>
                    </strong>

                </div>

                <strong>
                    <?= round($porcentaje_vendido) ?>%
                </strong>

            </div>


            <div class="progress-bar">

                <div
                    class="progress-fill"
                    style="width: <?= $porcentaje_vendido ?>%;"
                ></div>

            </div>


            <div class="progress-labels">

                <span>
                    <?= $nucleos_vendidos ?> vendidos
                </span>

                <span>
                    <?= $nucleos_disponibles ?> disponibles
                </span>

            </div>

        </section>



        <!-- =====================================
             ESTADÍSTICAS
        ====================================== -->

        <section class="stats-section">

            <h2>
                Resumen
            </h2>


            <div class="stats-grid">


                <div class="stat-card">

                    <span class="stat-icon">
                        #
                    </span>

                    <div>

                        <strong>
                            <?= $nucleos_vendidos ?>
                        </strong>

                        <span>
                            Vendidos
                        </span>

                    </div>

                </div>


                <div class="stat-card">

                    <span class="stat-icon">
                        +
                    </span>

                    <div>

                        <strong>
                            <?= $nucleos_disponibles ?>
                        </strong>

                        <span>
                            Disponibles
                        </span>

                    </div>

                </div>


                <div class="stat-card">

                    <span class="stat-icon">
                        $
                    </span>

                    <div>

                        <strong>
                            $<?= number_format(
                                $recaudado,
                                0,
                                ",",
                                "."
                            ) ?>
                        </strong>

                        <span>
                            Recaudado
                        </span>

                    </div>

                </div>


                <div class="stat-card">

                    <span class="stat-icon">
                        ♙
                    </span>

                    <div>

                        <strong>
                            <?= $cantidad_participantes ?>
                        </strong>

                        <span>
                            Participantes
                        </span>

                    </div>

                </div>


            </div>

        </section>



        <!-- =====================================
             NÚMEROS
        ====================================== -->

        <section class="numbers-section">

            <div class="section-heading">

                <div>

                    <h2>
                        Números
                    </h2>

                    <p>
                        Estado de los números de tu rifa.
                    </p>

                </div>

            </div>


            <div class="number-legend">

                <span>
                    <i class="legend-dot disponible"></i>
                    Disponible
                </span>

                <span>
                    <i class="legend-dot reservado"></i>
                    Reservado
                </span>

                <span>
                    <i class="legend-dot vendido"></i>
                    Vendido
                </span>

            </div>


            <div class="numbers-grid">

                <?php foreach ($numeros as $numero): ?>

                    <div
                        class="
                            raffle-number
                            <?= $numero["estado_clase"] ?>
                        "
                    >

                        <?= htmlspecialchars(
                            $numero["numero"]
                        ) ?>

                    </div>

                <?php endforeach; ?>

            </div>

        </section>



        <!-- =====================================
             PARTICIPANTES
        ====================================== -->

        <section class="participants-section">

            <div class="section-heading">

                <div>

                    <h2>
                        Participantes
                    </h2>

                    <p>
                        Personas que participan en tu rifa.
                    </p>

                </div>

            </div>


            <div class="participants-list">

                <?php if ($resultado_lista->num_rows > 0): ?>

                    <?php while (
                        $participante =
                        $resultado_lista->fetch_assoc()
                    ): ?>


                        <article class="participant-card">


                            <div class="participant-avatar">

                                <?= strtoupper(
                                    substr(
                                        $participante["nombre"],
                                        0,
                                        1
                                    )
                                ) ?>

                            </div>


                            <div class="participant-info">

                                <strong>

                                    <?= htmlspecialchars(
                                        $participante["nombre"]
                                    ) ?>

                                    <?= htmlspecialchars(
                                        $participante["apellido"]
                                    ) ?>

                                </strong>


                                <span>

                                    Número #

                                    <?= htmlspecialchars(
                                        $participante["numero"]
                                    ) ?>

                                </span>

                            </div>


                            <div class="participant-status">

                                <?= htmlspecialchars(
                                    $participante[
                                        "estado_participacion"
                                    ]
                                ) ?>

                            </div>


                        </article>


                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="empty-participants">

                        <div>
                            +
                        </div>

                        <h3>
                            Todavía no hay participantes
                        </h3>

                        <p>
                            Cuando alguien compre un número,
                            aparecerá acá.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    
        <?php if (
            !empty($rifa["numero_ganador"]) &&
            (
                $estado_rifa === "finalizada" ||
                $estado_rifa === "finalizado"
            )
        ): ?>

            <?php

            $consulta_ganador_admin = $conexion->prepare("
                SELECT
                    u.nombre,
                    u.apellido
                FROM participaciones p

                INNER JOIN usuarios u
                    ON p.id_usuario = u.id_usuario

                INNER JOIN numeros_rifa nr
                    ON p.id_numero = nr.id_numero

                WHERE nr.id_rifa = ?
                AND nr.numero = ?

                ORDER BY p.id_participacion DESC

                LIMIT 1
            ");

            $consulta_ganador_admin->bind_param(
                "ii",
                $id_rifa,
                $rifa["numero_ganador"]
            );

            $consulta_ganador_admin->execute();

            $resultado_ganador_admin =
                $consulta_ganador_admin->get_result();

            $ganador_admin =
                $resultado_ganador_admin->fetch_assoc();

            $consulta_ganador_admin->close();

            ?>

            <section class="winner-card">

                <div class="winner-icon">
                    ★
                </div>

                <div class="winner-info">

                    <span>
                        RIFA FINALIZADA
                    </span>

                    <h2>
                        ¡Sorteo realizado!
                    </h2>

                    <?php if ($ganador_admin): ?>

                        <p>
                            Ganador:
                            <strong>
                                <?= htmlspecialchars(
                                    $ganador_admin["nombre"]
                                ) ?>

                                <?= htmlspecialchars(
                                    $ganador_admin["apellido"]
                                ) ?>
                            </strong>
                        </p>

                    <?php endif; ?>

                    <p>
                        Número ganador:
                        <strong>
                            #<?= htmlspecialchars(
                                $rifa["numero_ganador"]
                            ) ?>
                        </strong>
                    </p>

                </div>

                <a
                    href="resultado_rifa.php?id=<?= $id_rifa ?>"
                    class="primary-button"
                >
                    Ver resultado
                </a>

            </section>

        <?php endif; ?>




        <!-- =====================================
             ACCIONES
        ====================================== -->

        <section class="admin-actions">

            <button
                type="button"
                class="secondary-button"
                id="btnCompartir"
            >
                Compartir rifa
            </button>

            <?php
            $hoy = date("Y-m-d");
            $fecha_sorteo = !empty($rifa["fecha_sorteo"])
                ? date("Y-m-d", strtotime($rifa["fecha_sorteo"]))
                : null;

            $puede_sortear = ($fecha_sorteo === $hoy);
            ?>

            <?php if ($puede_sortear): ?>

                <button
                    type="button"
                    class="primary-button"
                    id="btnSortear"
                >
                    Realizar sorteo
                </button>

            <?php else: ?>

                <button
                    type="button"
                    class="primary-button"
                    disabled
                >
                    Sorteo disponible el
                    <?= $fecha_sorteo
                        ? date("d/m/Y", strtotime($rifa["fecha_sorteo"]))
                        : "día indicado"
                    ?>
                </button>

            <?php endif; ?>

        </section>


    </main>

</div>


<script>

const btnCompartir =
    document.getElementById("btnCompartir");

if (btnCompartir) {

    btnCompartir.addEventListener(
        "click",
        async function () {

            const url = window.location.origin +
                "/detalle_rifa.php?id=<?= $id_rifa ?>";

            try {

                await navigator.clipboard.writeText(url);

                btnCompartir.textContent =
                    "¡Enlace copiado!";

                setTimeout(function () {

                    btnCompartir.textContent =
                        "Compartir rifa";

                }, 2000);

            } catch (error) {

                alert(
                    "No se pudo copiar el enlace."
                );

            }

        }
    );

}

const btnSortear =
    document.getElementById("btnSortear");

if (btnSortear) {

    btnSortear.addEventListener(
        "click",
        function () {

            const confirmar = confirm(
                "¿Querés realizar el sorteo?\n\n" +
                "Se seleccionará aleatoriamente uno de los números participantes."
            );

            if (confirmar) {

                window.location.href =
                    "../acciones/sortear_rifa.php?id=<?= $id_rifa ?>";

            }

        }
    );

}
</script>


</body>

</html>

<?php

$consulta_lista->close();

?>