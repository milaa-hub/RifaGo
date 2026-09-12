<?php


require_once "../conexion.php";

require_once "../acciones/notificar.php";


session_start();

// ==========================================
// EVITAR CACHÉ DE NOTIFICACIONES
// ==========================================

header(

    "Cache-Control: no-store, no-cache, must-revalidate, max-age=0"

);

header(
    "Cache-Control: post-check=0, pre-check=0",
    false
);

header(
    "Pragma: no-cache"
);

header(
    "Expires: 0"
);

// ==========================================
// VERIFICAR SESIÓN
// ==========================================

if (!isset($_SESSION["id_usuario"])) {

    header("Location: ../login.php");
    exit;

}


$usuario_logueado = true;

$id_usuario = intval(
    $_SESSION["id_usuario"]
);


// ==========================================
// MARCAR TODAS COMO LEÍDAS
// ==========================================

if (

    isset($_POST["accion"])

    && $_POST["accion"] === "marcar_todas"

) {


    marcarTodasLeidas(

        $conexion,
        $id_usuario

    );


    header(
        "Location: notificaciones.php"
    );

    exit;

}


// ==========================================
// MARCAR UNA NOTIFICACIÓN COMO LEÍDA
// ==========================================

if (

    isset($_GET["leer"])

    && is_numeric($_GET["leer"])

) {


    $id_notificacion = intval(
        $_GET["leer"]
    );


    // ==========================================
    // BUSCAR NOTIFICACIÓN
    // ==========================================

    $sql = "

        SELECT
            enlace

        FROM notificaciones

        WHERE id_notificacion = ?

        AND id_usuario = ?

        LIMIT 1

    ";


    $stmt = $conexion->prepare($sql);


    if ($stmt) {


        $stmt->bind_param(

            "ii",

            $id_notificacion,

            $id_usuario

        );


        $stmt->execute();


        $resultado = $stmt->get_result();


        $notificacion =
            $resultado->fetch_assoc();


        $stmt->close();


        // ==========================================
        // SI LA NOTIFICACIÓN EXISTE
        // ==========================================

        if ($notificacion) {


            // ==========================================
            // MARCAR COMO LEÍDA DIRECTAMENTE
            // ==========================================

            $sql_update = "

                UPDATE notificaciones

                SET leida = 1

                WHERE id_notificacion = ?

                AND id_usuario = ?

            ";


            $stmt_update =
                $conexion->prepare(
                    $sql_update
                );


            if ($stmt_update) {


                $stmt_update->bind_param(

                    "ii",

                    $id_notificacion,

                    $id_usuario

                );


                $stmt_update->execute();


                $stmt_update->close();

            }


            // ==========================================
            // REDIRIGIR AL ENLACE
            // ==========================================

            if (

                !empty(
                    $notificacion["enlace"]
                )

            ) {


                header(

                    "Location: " .
                    $notificacion["enlace"]

                );

                exit;

            }


        }


    }


    // ==========================================
    // VOLVER A NOTIFICACIONES
    // ==========================================

    header(
        "Location: notificaciones.php"
    );

    exit;

}



// ==========================================
// OBTENER NOTIFICACIONES
// ==========================================

$notificaciones = obtenerNotificaciones(

    $conexion,
    $id_usuario

);

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
        Notificaciones | RifaGo+
    </title>


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >


    <!-- CSS GENERAL -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <!-- CSS NOTIFICACIONES -->

    <link
        rel="stylesheet"
        href="../assets/css/notificaciones.css?=v2"
    >

</head>


<body>


    <!-- =========================================
         HEADER
    ========================================== -->

    <header class="header">

         <a
            href="../index.php"
            class="back-button"
        >
            ←
        </a>

        <!-- LOGO -->

        <div class="logo">

            <span class="logo-blue">Rifa</span><span class="logo-red">Go</span><sup>+</sup>

        </div>


        <!-- ICONO USUARIO -->
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



    <!-- =========================================
         CONTENIDO PRINCIPAL
    ========================================== -->

    <main class="notifications-page">


        <!-- =========================================
             TÍTULO
        ========================================== -->

        <section class="notifications-title">


            <div>

                <h1>
                    Notificaciones
                </h1>

                <p>
                    Todas las novedades importantes aparecerán acá.
                </p>

            </div>


            <!-- MARCAR COMO LEÍDAS -->

            <?php if (!empty($notificaciones)): ?>

                <form method="POST">

                    <input
                        type="hidden"
                        name="accion"
                        value="marcar_todas"
                    >

                    <button
                        type="submit"
                        class="mark-read-button"
                    >

                        <i class="fa-solid fa-check-double"></i>

                        Marcar como leídas

                    </button>

                </form>

            <?php endif; ?>


        </section>



        <!-- =========================================
             CONTENEDOR NOTIFICACIONES
        ========================================== -->

        <section class="notifications-list">


            <?php if (empty($notificaciones)): ?>


                <!-- =====================================
                     ESTADO VACÍO
                ====================================== -->

                <div class="empty-notifications">


                    <div class="empty-notifications-icon">

                        <i class="fa-regular fa-bell"></i>

                    </div>


                    <h2>
                        No tenés notificaciones
                    </h2>


                    <p>
                        Cuando haya novedades sobre tus rifas,
                        tus números o tu cuenta,
                        las vas a ver acá.
                    </p>


                </div>


            <?php else: ?>


                <!-- =====================================
                     LISTA DE NOTIFICACIONES
                ====================================== -->

                <?php foreach (

                    $notificaciones

                    as $notificacion

                ): ?>


                    <?php


                    // ======================================
                    // ICONO SEGÚN EL TIPO
                    // ======================================

                    $icono = "fa-bell";


                    switch (
                        $notificacion["tipo"]
                    ) {


                        // PAGOS

                        case "pago_pendiente":

                            $icono =
                                "fa-credit-card";

                            break;


                        case "pago_confirmado":

                            $icono =
                                "fa-circle-check";

                            break;


                        case "pago_rechazado":

                            $icono =
                                "fa-circle-xmark";

                            break;


                        // PARTICIPACIONES

                        case "nueva_participacion":

                            $icono =
                                "fa-ticket";

                            break;


                        case "rifa_completa":

                            $icono =
                                "fa-party-horn";

                            break;


                        // RESERVAS

                        case "reserva_creada":

                            $icono =
                                "fa-ticket";

                            break;


                        case "reserva_proxima_vencer":

                            $icono =
                                "fa-clock";

                            break;


                        case "reserva_vencida":

                            $icono =
                                "fa-triangle-exclamation";

                            break;


                        // SORTEOS

                        case "sorteo_proximo":

                            $icono =
                                "fa-hourglass-half";

                            break;


                        case "rifa_finalizada":

                            $icono =
                                "fa-flag-checkered";

                            break;


                        case "ganador":

                            $icono =
                                "fa-trophy";

                            break;


                        // ENGAGEMENT

                        case "rifa_recomendada":

                            $icono =
                                "fa-star";

                            break;


                        case "rifa_tendencia":

                            $icono =
                                "fa-fire";

                            break;


                        case "ultimos_numeros":

                            $icono =
                                "fa-bolt";

                            break;


                        case "rifa_destacada":

                            $icono =
                                "fa-star";

                            break;


                        case "invitar_participar":

                            $icono =
                                "fa-bullseye";

                            break;


                        case "nuevas_rifas":

                            $icono =
                                "fa-gift";

                            break;


                        case "usuario_inactivo":

                            $icono =
                                "fa-hand";

                            break;


                    }


                    // ======================================
                    // CLASE LEÍDA
                    // ======================================

                    $clase_leida =

                        intval(
                            $notificacion["leida"]
                        ) === 1

                        ? "leida"

                        : "no-leida";


                    ?>


                    <!-- =====================================
                         NOTIFICACIÓN
                    ====================================== -->

                    <a

                        href="notificaciones.php?leer=<?= intval(

                            $notificacion[
                                "id_notificacion"
                            ]

                        ) ?>"

                        class="notification-item <?=

                            $clase_leida

                        ?>"

                    >


                        <!-- ICONO -->

                        <div
                            class="notification-icon"
                        >

                            <i
                                class="fa-solid <?=

                                    htmlspecialchars(
                                        $icono
                                    )

                                ?>"
                            ></i>

                        </div>


                        <!-- CONTENIDO -->

                        <div
                            class="notification-content"
                        >


                            <h2>

                                <?= htmlspecialchars(

                                    $notificacion[
                                        "titulo"
                                    ]

                                ) ?>

                            </h2>


                            <p>

                                <?= htmlspecialchars(

                                    $notificacion[
                                        "mensaje"
                                    ]

                                ) ?>

                            </p>


                            <span
                                class="notification-date"
                            >

                                <i
                                    class="fa-regular fa-clock"
                                ></i>

                                <?= htmlspecialchars(

                                    date(

                                        "d/m/Y H:i",

                                        strtotime(

                                            $notificacion[
                                                "fecha_creacion"
                                            ]

                                        )

                                    )

                                ) ?>

                            </span>


                        </div>


                        <!-- ESTADO NO LEÍDA -->

                        <?php if (

                            intval(

                                $notificacion["leida"]

                            ) === 0

                        ): ?>


                            <div
                                class="notification-unread"
                            ></div>


                        <?php endif; ?>


                    </a>


                <?php endforeach; ?>


            <?php endif; ?>


        </section>


    </main>

    <script>

    window.addEventListener(

        "pageshow",

        function(event) {

            if (

                event.persisted

                ||

                (
                    window.performance
                    &&
                    window.performance.navigation
                    &&
                    window.performance.navigation.type === 2
                )

            ) {

                window.location.reload();

            }

        }

    );

    </script>

</body>

</html>
