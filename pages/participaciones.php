<?php

require_once "../conexion.php";
session_start();

$usuario_logueado = isset($_SESSION['id_usuario']);

$participaciones_activas = [];
$participaciones_finalizadas = [];


// ==================================================
// OBTENER PARTICIPACIONES DEL USUARIO
// ==================================================

if ($usuario_logueado) {

    $id_usuario = $_SESSION['id_usuario'];

    /*
     * Relacionamos:
     *
     * participaciones
     *       ↓
     * numero_rifa
     *       ↓
     * rifas
     *
     * De esta forma sabemos en qué rifa participa
     * el usuario y qué número compró.
     */

    $consulta = $conexion->prepare("
        SELECT
            r.id_rifa,
            r.titulo,
            r.premio,
            r.imagen,
            r.precio_numero,
            r.fecha_sorteo,
            r.estado AS estado_rifa,
            nr.numero,
            p.id_participacion,
            p.fecha_compra,
            p.estado AS estado_participacion

        FROM participaciones p

        INNER JOIN numeros_rifa nr
            ON p.id_numero = nr.id_numero

        INNER JOIN rifas r
            ON nr.id_rifa = r.id_rifa

        WHERE p.id_usuario = ?

        ORDER BY r.fecha_sorteo ASC, nr.numero ASC
    ");

    $consulta->bind_param("i", $id_usuario);

    $consulta->execute();

    $resultado = $consulta->get_result();


    // ==================================================
    // AGRUPAR PARTICIPACIONES POR RIFA
    // ==================================================

    while ($fila = $resultado->fetch_assoc()) {

        $id_rifa = $fila['id_rifa'];

        $estado_rifa = strtolower(
            trim($fila['estado_rifa'])
        );


        /*
         * Si todavía no existe esta rifa en nuestro
         * array, la creamos.
         */

        if (!isset($participaciones_activas[$id_rifa]) &&
            !isset($participaciones_finalizadas[$id_rifa])) {

            $datos_rifa = [
                'id_rifa' => $fila['id_rifa'],
                'titulo' => $fila['titulo'],
                'premio' => $fila['premio'],
                'imagen' => $fila['imagen'],
                'precio_numero' => $fila['precio_numero'],
                'fecha_sorteo' => $fila['fecha_sorteo'],
                'numeros' => []
            ];


            /*
             * ACTIVA
             */

            if (
                $estado_rifa === "activa" ||
                $estado_rifa === "activo"
            ) {

                $participaciones_activas[$id_rifa] = $datos_rifa;

            }


            /*
             * FINALIZADA
             */

            elseif (
                $estado_rifa === "finalizada" ||
                $estado_rifa === "finalizado"
            ) {

                $participaciones_finalizadas[$id_rifa] = $datos_rifa;

            }

        }


        /*
         * Agregar el número comprado a la rifa
         */

        if (isset($participaciones_activas[$id_rifa])) {

            $participaciones_activas[$id_rifa]['numeros'][] =
                $fila['numero'];

        }

        elseif (isset($participaciones_finalizadas[$id_rifa])) {

            $participaciones_finalizadas[$id_rifa]['numeros'][] =
                $fila['numero'];

        }

    }


    $consulta->close();


    /*
     * Convertimos los arrays asociativos en arrays
     * normales para poder recorrerlos fácilmente.
     */

    $participaciones_activas =
        array_values($participaciones_activas);

    $participaciones_finalizadas =
        array_values($participaciones_finalizadas);

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

    <title>Mis participaciones - RifaGo</title>


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
                    Mis participaciones
                </h1>

                <p>
                    Consultá las rifas en las que participás.
                </p>

            </div>



            <!-- =====================================
                 TABS
            ====================================== -->

            <div class="tabs">

                <button
                    type="button"
                    class="participation-tab active"
                    data-tab="participaciones-activas"
                >
                    Activas
                </button>


                <button
                    type="button"
                    class="participation-tab"
                    data-tab="participaciones-finalizadas"
                >
                    Finalizadas
                </button>

            </div>



            <!-- =====================================
                 PARTICIPACIONES ACTIVAS
            ====================================== -->

            <section
                class="participation-list participation-content active"
                id="participaciones-activas"
            >


                <?php if (
                    count($participaciones_activas) > 0
                ): ?>


                    <?php foreach (
                        $participaciones_activas
                        as $participacion
                    ): ?>


                        <article class="participation-card">


                            <!-- IMAGEN -->

                            <div class="participation-image">

                                <?php if (
                                    !empty(
                                        $participacion['imagen']
                                    )
                                ): ?>

                                    <img
                                        src="<?= htmlspecialchars(
                                            $participacion['imagen']
                                        ) ?>"
                                        alt="<?= htmlspecialchars(
                                            $participacion['premio']
                                        ) ?>"
                                    >

                                <?php else: ?>

                                    <div class="empty-image">
                                        Sin imagen
                                    </div>

                                <?php endif; ?>

                            </div>



                            <!-- INFORMACIÓN -->

                            <div class="participation-info">


                                <div class="participation-header">

                                    <h2>
                                        <?= htmlspecialchars(
                                            $participacion['titulo']
                                        ) ?>
                                    </h2>


                                    <span
                                        class="status active-status"
                                    >
                                        Activa
                                    </span>

                                </div>



                                <p class="participation-price">

                                    $<?= number_format(
                                        $participacion[
                                            'precio_numero'
                                        ],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                    por número

                                </p>



                                <div class="participation-data">


                                    <!-- NÚMEROS -->

                                    <div>

                                        <span>
                                            Números
                                        </span>

                                        <strong>

                                            <?php

                                            $numeros =
                                                $participacion[
                                                    'numeros'
                                                ];

                                            sort($numeros);

                                            echo htmlspecialchars(
                                                implode(
                                                    ', ',
                                                    $numeros
                                                )
                                            );

                                            ?>

                                        </strong>

                                    </div>



                                    <!-- SORTEO -->

                                    <div>

                                        <span>
                                            Sorteo
                                        </span>

                                        <strong>

                                            <?= !empty(
                                                $participacion[
                                                    'fecha_sorteo'
                                                ]
                                            )
                                                ? date(
                                                    "d/m/Y",
                                                    strtotime(
                                                        $participacion[
                                                            'fecha_sorteo'
                                                        ]
                                                    )
                                                )
                                                : 'Sin fecha'
                                            ?>

                                        </strong>

                                    </div>


                                </div>



                                <a
                                    href="detalle_rifa.php?id=<?= $participacion['id_rifa'] ?>"
                                    class="primary-button participation-button"
                                >
                                    Ver detalle
                                </a>


                            </div>

                        </article>


                    <?php endforeach; ?>


                <?php else: ?>


                    <!-- SIN PARTICIPACIONES -->

                    <div class="empty-state">

                        <div class="empty-icon">
                            ♧
                        </div>


                        <h2>
                            No tenés participaciones activas
                        </h2>


                        <p>
                            Cuando participes en una rifa,
                            aparecerá acá.
                        </p>


                        <a
                            href="../index.php"
                            class="primary-button"
                        >
                            Explorar rifas
                        </a>

                    </div>


                <?php endif; ?>


            </section>



            <!-- =====================================
                 PARTICIPACIONES FINALIZADAS
            ====================================== -->

            <section
                class="participation-list participation-content"
                id="participaciones-finalizadas"
            >


                <?php if (
                    count($participaciones_finalizadas) > 0
                ): ?>


                    <?php foreach (
                        $participaciones_finalizadas
                        as $participacion
                    ): ?>


                        <article class="participation-card">


                            <!-- IMAGEN -->

                            <div class="participation-image">

                                <?php if (
                                    !empty(
                                        $participacion['imagen']
                                    )
                                ): ?>

                                    <img
                                        src="<?= htmlspecialchars(
                                            $participacion['imagen']
                                        ) ?>"
                                        alt="<?= htmlspecialchars(
                                            $participacion['premio']
                                        ) ?>"
                                    >

                                <?php else: ?>

                                    <div class="empty-image">
                                        Sin imagen
                                    </div>

                                <?php endif; ?>

                            </div>



                            <!-- INFORMACIÓN -->

                            <div class="participation-info">


                                <div class="participation-header">

                                    <h2>
                                        <?= htmlspecialchars(
                                            $participacion['titulo']
                                        ) ?>
                                    </h2>


                                    <span
                                        class="status"
                                    >
                                        Finalizada
                                    </span>

                                </div>



                                <p class="participation-price">

                                    $<?= number_format(
                                        $participacion[
                                            'precio_numero'
                                        ],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                    por número

                                </p>



                                <div class="participation-data">


                                    <!-- NÚMEROS -->

                                    <div>

                                        <span>
                                            Números
                                        </span>

                                        <strong>

                                            <?php

                                            $numeros =
                                                $participacion[
                                                    'numeros'
                                                ];

                                            sort($numeros);

                                            echo htmlspecialchars(
                                                implode(
                                                    ', ',
                                                    $numeros
                                                )
                                            );

                                            ?>

                                        </strong>

                                    </div>



                                    <!-- SORTEO -->

                                    <div>

                                        <span>
                                            Sorteo
                                        </span>

                                        <strong>

                                            <?= !empty(
                                                $participacion[
                                                    'fecha_sorteo'
                                                ]
                                            )
                                                ? date(
                                                    "d/m/Y",
                                                    strtotime(
                                                        $participacion[
                                                            'fecha_sorteo'
                                                        ]
                                                    )
                                                )
                                                : 'Sin fecha'
                                            ?>

                                        </strong>

                                    </div>


                                </div>



                                <a
                                    href="detalle_rifa.php?id=<?= $participacion['id_rifa'] ?>"
                                    class="primary-button participation-button"
                                >
                                    Ver detalle
                                </a>


                            </div>

                        </article>


                    <?php endforeach; ?>


                <?php else: ?>


                    <!-- SIN PARTICIPACIONES FINALIZADAS -->

                    <div class="empty-state">

                        <div class="empty-icon">
                            ✓
                        </div>


                        <h2>
                            No hay participaciones finalizadas
                        </h2>


                        <p>
                            Tus participaciones que ya hayan
                            terminado aparecerán acá.
                        </p>

                    </div>


                <?php endif; ?>


            </section>


        <?php else: ?>


            <!-- =====================================
                 USUARIO SIN CUENTA
            ====================================== -->

            <section class="empty-state">

                <div class="empty-icon">
                    ♧
                </div>


                <h2>
                    Registrate para participar
                </h2>


                <p>
                    Creá una cuenta en RifaGo para
                    participar en rifas, guardar tus números
                    y consultar tus participaciones.
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
         NAVEGACIÓN
    ========================================== -->

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
            class="create-button"
            onclick="window.location.href='crear_rifa.php'"
        >

            <span>
                +
            </span>

        </button>



        <a
            href="participaciones.php"
            class="nav-item active"
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


<script src="../assets/js/participaciones.js"></script>

</body>

</html>