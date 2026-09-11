<?php

require_once "../conexion.php";
session_start();

$usuario_logueado = isset($_SESSION['id_usuario']);


// ==================================================
// VARIABLES
// ==================================================

$rifas_activas = [];
$rifas_finalizadas = [];
$rifas_borradores = [];


// ==================================================
// OBTENER LAS RIFAS DEL USUARIO
// ==================================================

if ($usuario_logueado) {

    $id_usuario = $_SESSION['id_usuario'];

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
        WHERE id_usuario = ?
        ORDER BY id_rifa DESC
    ");

    $consulta->bind_param("i", $id_usuario);

    $consulta->execute();

    $resultado = $consulta->get_result();


    // ==================================================
    // SEPARAR LAS RIFAS SEGÚN SU ESTADO
    // ==================================================

    while ($rifa = $resultado->fetch_assoc()) {

        $estado = strtolower(trim($rifa['estado']));


        // RIFAS ACTIVAS
        if (
            $estado === "activa" ||
            $estado === "activo"
        ) {

            $rifas_activas[] = $rifa;


        // RIFAS FINALIZADAS
        } elseif (
            $estado === "finalizada" ||
            $estado === "finalizado"
        ) {

            $rifas_finalizadas[] = $rifa;


        // BORRADORES
        } elseif (
            $estado === "borrador" ||
            $estado === "borradores"
        ) {

            $rifas_borradores[] = $rifa;

        }

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

    <title>Mis rifas - RifaGo</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css?=v2"
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

<div class="app">


    <!-- =========================================
         HEADER
    ========================================== -->

    <header class="header">

        <div class="logo">
            <span class="logo-blue">Rifa</span><span class="logo-red">Go</span><sup>+</sup>
        </div>


        <div class="user-icon">

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

                <span>?</span>

            <?php endif; ?>

        </div>

    </header>



    <!-- =========================================
         CONTENIDO
    ========================================== -->

    <main class="main-content">


        <?php if ($usuario_logueado): ?>


            <!-- =====================================
                 TÍTULO
            ====================================== -->

            <div class="page-title">

                <h1>
                    Mis rifas
                </h1>

                <p>
                    Administrá las rifas que creaste.
                </p>

            </div>



            <!-- =====================================
                 PESTAÑAS
            ====================================== -->

            <div class="tabs">

                <button
                    type="button"
                    class="tab active"
                    data-tab="activas"
                >
                    Activas
                </button>


                <button
                    type="button"
                    class="tab"
                    data-tab="finalizadas"
                >
                    Finalizadas
                </button>


                <button
                    type="button"
                    class="tab"
                    data-tab="borradores"
                >
                    Borradores
                </button>

            </div>



            <!-- ==========================================
                 RIFAS ACTIVAS
            =========================================== -->

            <section
                class="raffle-list tab-content active"
                id="activas"
            >

                <?php if (count($rifas_activas) > 0): ?>


                    <?php foreach ($rifas_activas as $rifa): ?>


                        <article class="my-raffle-card">


                            <!-- IMAGEN -->

                            <div class="my-raffle-image">

                                <?php if (!empty($rifa['imagen'])): ?>

                                    <img
                                        src="../<?= htmlspecialchars($rifa['imagen']) ?>"
                                        alt="<?= htmlspecialchars($rifa['premio']) ?>"
                                    >

                                <?php else: ?>

                                    <div class="empty-image">
                                        Sin imagen
                                    </div>

                                <?php endif; ?>

                            </div>



                            <!-- INFORMACIÓN -->

                            <div class="my-raffle-info">

                                <div>

                                    <h2>
                                        <?= htmlspecialchars(
                                            $rifa['titulo']
                                        ) ?>
                                    </h2>


                                    <p class="raffle-price">

                                        $<?= number_format(
                                            $rifa['precio_numero'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                        por número

                                    </p>

                                </div>



                                <div class="raffle-details">

                                    <p>

                                        <strong>
                                            Sorteo:
                                        </strong>

                                        <?= !empty($rifa['fecha_sorteo'])
                                            ? date(
                                                "d/m/Y",
                                                strtotime(
                                                    $rifa['fecha_sorteo']
                                                )
                                            )
                                            : 'Sin fecha'
                                        ?>

                                    </p>


                                    <p>

                                        <strong>
                                            Números:
                                        </strong>

                                        <?= number_format(
                                            $rifa['cantidad_numeros'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    </p>

                                </div>



                                <!-- ESTADO -->

                                <div class="my-raffle-status">
                                    Activa
                                </div>



                                <!-- BOTÓN -->

                                <a
                                    href="administrar_rifa.php?id=<?= $rifa['id_rifa'] ?>"
                                    class="primary-button"
                                    style="text-decoration: none;"
                                >
                                    Administrar rifa
                                </a>

                            </div>

                        </article>


                    <?php endforeach; ?>


                <?php else: ?>


                    <!-- SIN RIFAS -->

                    <div class="empty-state">

                        <div class="empty-icon">
                            +
                        </div>


                        <h2>
                            No tenés rifas activas
                        </h2>


                        <p>
                            Cuando publiques una rifa,
                            aparecerá acá.
                        </p>


                        <a
                            href="crear_rifa.php"
                            class="primary-button"
                        >
                            Crear una rifa
                        </a>

                    </div>


                <?php endif; ?>

            </section>



            <!-- ==========================================
                 RIFAS FINALIZADAS
            =========================================== -->

            <section
                class="raffle-list tab-content"
                id="finalizadas"
            >

                <?php if (count($rifas_finalizadas) > 0): ?>


                    <?php foreach ($rifas_finalizadas as $rifa): ?>


                        <article class="my-raffle-card">


                            <!-- IMAGEN -->

                            <div class="my-raffle-image">

                                <?php if (!empty($rifa['imagen'])): ?>

                                    <img
                                        src="../<?= htmlspecialchars($rifa['imagen']) ?>"
                                        alt="<?= htmlspecialchars($rifa['premio']) ?>"
                                    >

                                <?php else: ?>

                                    <div class="empty-image">
                                        Sin imagen
                                    </div>

                                <?php endif; ?>

                            </div>



                            <!-- INFORMACIÓN -->

                            <div class="my-raffle-info">

                                <div>

                                    <h2>
                                        <?= htmlspecialchars(
                                            $rifa['titulo']
                                        ) ?>
                                    </h2>


                                    <p class="raffle-price">

                                        $<?= number_format(
                                            $rifa['precio_numero'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                        por número

                                    </p>

                                </div>



                                <div class="raffle-details">

                                    <p>

                                        <strong>
                                            Sorteo:
                                        </strong>

                                        <?= !empty($rifa['fecha_sorteo'])
                                            ? date(
                                                "d/m/Y",
                                                strtotime(
                                                    $rifa['fecha_sorteo']
                                                )
                                            )
                                            : 'Sin fecha'
                                        ?>

                                    </p>


                                    <p>

                                        <strong>
                                            Números:
                                        </strong>

                                        <?= number_format(
                                            $rifa['cantidad_numeros'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    </p>

                                </div>



                                <!-- ESTADO -->

                                <div class="my-raffle-status">
                                    Finalizada
                                </div>



                                <!-- BOTÓN -->

                                <a
                                    href="detalle_rifa.php?id=<?= $rifa['id_rifa'] ?>"
                                    class="secondary-button"
                                >
                                    Ver rifa
                                </a>

                            </div>

                        </article>


                    <?php endforeach; ?>


                <?php else: ?>


                    <!-- SIN RIFAS -->

                    <div class="empty-state">

                        <div class="empty-icon">
                            ✓
                        </div>


                        <h2>
                            No hay rifas finalizadas
                        </h2>


                        <p>
                            Cuando una de tus rifas termine,
                            aparecerá acá.
                        </p>

                    </div>


                <?php endif; ?>

            </section>



            <!-- ==========================================
                BORRADORES
            ========================================== -->

            <section
                class="raffle-list tab-content"
                id="borradores"
            >

                <?php if (count($rifas_borradores) > 0): ?>

                    <?php foreach ($rifas_borradores as $rifa): ?>

                        <article class="my-raffle-card">

                            <!-- IMAGEN -->

                            <div class="my-raffle-image">

                                <?php if (!empty($rifa['imagen'])): ?>

                                    <img
                                        src="../<?= htmlspecialchars($rifa['imagen']) ?>"
                                        alt="<?= htmlspecialchars($rifa['premio']) ?>"
                                    >

                                <?php else: ?>

                                    <div class="empty-image">
                                        Sin imagen
                                    </div>

                                <?php endif; ?>

                            </div>


                            <!-- INFORMACIÓN -->

                            <div class="my-raffle-info">

                                <div>

                                    <h2>
                                        <?= !empty($rifa['titulo'])
                                            ? htmlspecialchars($rifa['titulo'])
                                            : 'Rifa sin título'
                                        ?>
                                    </h2>


                                    <p class="raffle-price">

                                        <?php if (!empty($rifa['precio_numero'])): ?>

                                            $<?= number_format(
                                                $rifa['precio_numero'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?>

                                            por número

                                        <?php else: ?>

                                            Precio sin definir

                                        <?php endif; ?>

                                    </p>

                                </div>


                                <!-- ESTADO -->

                                <div class="my-raffle-status draft-status">

                                    Borrador

                                </div>


                                <!-- ACCIONES -->

                                <div class="draft-actions">


                                    <!-- CONTINUAR EDITANDO -->

                                    <a
                                        href="crear_rifa.php?id=<?= $rifa['id_rifa'] ?>"
                                        class="secondary-button"
                                    >

                                        Continuar editando

                                    </a>


                                    <!-- ELIMINAR -->

                                    <form 
                                        action="../acciones/eliminar_borrador.php" 
                                        method="POST"
                                        class="delete-draft-form"
                                    >

                                        <input 
                                            type="hidden" 
                                            name="id_rifa" 
                                            value="<?= $rifa['id_rifa'] ?>"
                                        >

                                        <button 
                                            type="button"
                                            class="delete-draft-button"
                                        >
                                            Eliminar
                                        </button>

                                    </form>


                                </div>

                            </div>

                        </article>

                        

                    <?php endforeach; ?>


                <?php else: ?>


                    <!-- SIN BORRADORES -->

                    <div class="empty-state">

                        <div class="empty-icon">
                            +
                        </div>


                        <h2>
                            No tenés borradores
                        </h2>


                        <p>
                            Las rifas que guardes como borrador
                            aparecerán acá.
                        </p>


                        <a
                            href="crear_rifa.php"
                            class="primary-button"
                        >

                            Crear una rifa

                        </a>

                    </div>


                <?php endif; ?>

            </section>



        <?php else: ?>


            <!-- ==========================================
                 USUARIO SIN CUENTA
            =========================================== -->

            <section class="empty-state">

                <div class="empty-icon">
                    +
                </div>


                <h2>
                    Creá tu cuenta para crear rifas
                </h2>


                <p>
                    Registrate en RifaGo para crear,
                    administrar y seguir tus propias rifas.
                </p>


                <a
                    href="../registro.php"
                    class="primary-button"
                >
                    Crear una cuenta
                </a>


                <br>
                <br>


                <a
                    href="../login.php"
                    class="secondary-button"
                >
                    Ya tengo una cuenta
                </a>

            </section>


        <?php endif; ?>


    </main>

    <!-- =========================================
     MODAL ELIMINAR BORRADOR
    ========================================= -->

    <div
        class="delete-modal"
        id="deleteModal"
    >

        <div class="delete-modal-content">


            <div class="delete-modal-icon">
                🗑
            </div>


            <h2>
                ¿Eliminar borrador?
            </h2>


            <p>
                ¿Estás segura de que querés eliminar este borrador?
                Esta acción no se puede deshacer.
            </p>


            <div class="delete-modal-buttons">


                <button
                    type="button"
                    class="cancel-delete-button"
                    id="cancelDelete"
                >
                    Cancelar
                </button>


                <button
                    type="button"
                    class="confirm-delete-button"
                    id="confirmDelete"
                >
                    Sí, eliminar
                </button>


            </div>


        </div>

</div>


    <!-- ==========================================
         NAVEGACIÓN INFERIOR
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
            class="nav-item active"
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

<script>

document.addEventListener(
    "DOMContentLoaded",
    function() {


        /* =========================================
           TABS
        ========================================= */

        const tabs =
            document.querySelectorAll(".tab");


        const contents =
            document.querySelectorAll(".tab-content");


        tabs.forEach(
            function(tab) {


                tab.addEventListener(
                    "click",
                    function() {


                        const tabSeleccionada =
                            tab.dataset.tab;


                        /* QUITAR ACTIVE DE TABS */

                        tabs.forEach(
                            function(item) {

                                item.classList.remove(
                                    "active"
                                );

                            }
                        );


                        /* OCULTAR CONTENIDOS */

                        contents.forEach(
                            function(content) {

                                content.classList.remove(
                                    "active"
                                );

                            }
                        );


                        /* ACTIVAR TAB */

                        tab.classList.add(
                            "active"
                        );


                        /* MOSTRAR CONTENIDO */

                        const contenido =
                            document.getElementById(
                                tabSeleccionada
                            );


                        if (contenido) {

                            contenido.classList.add(
                                "active"
                            );

                        }


                    }
                );


            }
        );



        /* =========================================
           MODAL ELIMINAR BORRADOR
        ========================================= */

        const deleteModal =
            document.getElementById(
                "deleteModal"
            );


        const cancelDelete =
            document.getElementById(
                "cancelDelete"
            );


        const confirmDelete =
            document.getElementById(
                "confirmDelete"
            );


        const deleteButtons =
            document.querySelectorAll(
                ".delete-draft-button"
            );


        let formAEliminar = null;



        /* =========================================
           ABRIR MODAL
        ========================================= */

        deleteButtons.forEach(
            function(button) {


                button.addEventListener(
                    "click",
                    function() {


                        console.log(
                            "Botón eliminar presionado"
                        );


                        formAEliminar =
                            button.closest(
                                ".delete-draft-form"
                            );


                        if (deleteModal) {

                            deleteModal.classList.add(
                                "active"
                            );

                        }


                    }
                );


            }
        );



        /* =========================================
           CANCELAR
        ========================================= */

        if (cancelDelete) {


            cancelDelete.addEventListener(
                "click",
                function() {


                    deleteModal.classList.remove(
                        "active"
                    );


                    formAEliminar = null;


                }
            );


        }



        /* =========================================
           CONFIRMAR ELIMINACIÓN
        ========================================= */

        if (confirmDelete) {


            confirmDelete.addEventListener(
                "click",
                function() {


                    if (formAEliminar) {


                        formAEliminar.submit();


                    }


                }
            );


        }



        /* =========================================
           CERRAR AL TOCAR AFUERA
        ========================================= */

        if (deleteModal) {


            deleteModal.addEventListener(
                "click",
                function(event) {


                    if (
                        event.target === deleteModal
                    ) {


                        deleteModal.classList.remove(
                            "active"
                        );


                        formAEliminar = null;


                    }


                }
            );


        }


    }
);

</script>

</body>

</html>