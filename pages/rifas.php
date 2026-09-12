<?php

require_once "../conexion.php";
session_start();

$usuario_logueado = isset($_SESSION['id_usuario']);

// ==========================================
// OBTENER RIFAS
// ==========================================

$consulta = $conexion->prepare("
    SELECT
        id_rifa,
        id_usuario,
        titulo,
        descripcion,
        premio,
        imagen,
        precio_numero,
        cantidad_numeros,
        fecha_sorteo,
        estado
    FROM rifas
    WHERE estado = 'activa'
    ORDER BY id_rifa DESC
");

$consulta->execute();

$resultado = $consulta->get_result();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Rifas - RifaGo</title>


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
        href="../assets/css/rifas.css?v=2"
    >
    <link
        rel="stylesheet"
        href="../assets/css/bottom-nav.css"
    >

</head>


<body>


    <!-- ==========================================
         HEADER
    =========================================== -->

    <header class="raffles-header">

        <a href="../index.php" class="raffles-logo">

            <span class="logo-blue">Rifa</span><span class="logo-red">Go</span><sup>+</sup>

        </a>


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

    <main class="raffles-page">


        <!-- ENCABEZADO -->

        <section class="raffles-title">

            <span>
                DESCUBRÍ
            </span>

            <h1>
                Rifas disponibles
            </h1>

            <p>
                Elegí una rifa, seleccioná tu número
                y participá por increíbles premios.
            </p>

        </section>



        <!-- ==========================================
             LISTADO
        =========================================== -->

        <section class="raffles-grid">


            <?php if ($resultado->num_rows > 0): ?>


                <?php while ($rifa = $resultado->fetch_assoc()): ?>


                    <article class="raffle-card">


                        <!-- IMAGEN -->

                        <div class="raffle-image">

                            <?php if (!empty($rifa["imagen"])): ?>

                                <img
                                    src="../<?php echo htmlspecialchars($rifa["imagen"]); ?>"
                                    alt="<?php echo htmlspecialchars($rifa["titulo"]); ?>"
                                >

                            <?php else: ?>

                                <div class="raffle-no-image">
                                    Sin imagen
                                </div>

                            <?php endif; ?>

                        </div>



                        <!-- INFORMACIÓN -->

                        <div class="raffle-content">


                            <span class="raffle-status">
                                Activa
                            </span>


                            <h2>

                                <?php
                                echo htmlspecialchars(
                                    $rifa["titulo"]
                                );
                                ?>

                            </h2>


                            <p>

                                <?php
                                echo htmlspecialchars(
                                    $rifa["descripcion"]
                                );
                                ?>

                            </p>


                            <!-- PREMIO -->

                            <div class="raffle-prize">

                                <span>
                                    PREMIO
                                </span>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $rifa["premio"]
                                    );
                                    ?>

                                </strong>

                            </div>



                            <!-- DATOS -->

                            <div class="raffle-info">


                                <div>

                                    <span>
                                        Por número
                                    </span>

                                    <strong>
                                        $<?php

                                        echo number_format(
                                            $rifa["precio_numero"],
                                            2,
                                            ",",
                                            "."
                                        );

                                        ?>
                                    </strong>

                                </div>


                                <div>

                                    <span>
                                        Sorteo
                                    </span>

                                    <strong>

                                        <?php

                                        echo date(
                                            "d/m/Y",
                                            strtotime(
                                                $rifa["fecha_sorteo"]
                                            )
                                        );

                                        ?>

                                    </strong>

                                </div>


                            </div>



                            <!-- BOTÓN -->

                            <a
                                href="detalle_rifa.php?id=<?php echo $rifa["id_rifa"]; ?>"
                                class="raffle-button"
                            >
                                Ver rifa
                            </a>


                        </div>

                    </article>


                <?php endwhile; ?>


            <?php else: ?>


                <!-- SIN RIFAS -->

                <div class="no-raffles">

                    <div class="no-raffles-icon">
                        ✦
                    </div>

                    <h2>
                        Todavía no hay rifas
                    </h2>

                    <p>
                        Cuando se publique una nueva rifa,
                        aparecerá acá.
                    </p>


                    <?php if (isset($_SESSION["id_usuario"])): ?>

                        <a
                            href="crear_rifa.php"
                            class="raffle-button"
                        >
                            Crear una rifa
                        </a>

                    <?php else: ?>

                        <a
                            href="../login.php"
                            class="raffle-button"
                        >
                            Iniciar sesión
                        </a>

                    <?php endif; ?>

                </div>


            <?php endif; ?>


        </section>

    </main>

    <nav class="bottom-nav">


        <a
            href="../index.php"
            class="nav-item active"
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



                
        <a href="crear_rifa.php" class="create-button" style="text-decoration: none;">
            <span>
                +
            </span>
        </a>


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

</body>

</html>

<?php

$consulta->close();

?>