<?php

require_once "../conexion.php";
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];


/* ==========================================
   DATOS DEL USUARIO
========================================== */

$sql_usuario = "SELECT nombre, apellido
                FROM usuarios
                WHERE id_usuario = ?";

$stmt_usuario = $conexion->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $id_usuario);
$stmt_usuario->execute();

$resultado_usuario = $stmt_usuario->get_result();
$usuario = $resultado_usuario->fetch_assoc();

$stmt_usuario->close();

if (!$usuario) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

$iniciales =
    strtoupper(substr($usuario['nombre'], 0, 1)) .
    strtoupper(substr($usuario['apellido'], 0, 1));


/* ==========================================
   HISTORIAL
========================================== */

$sql = "SELECT
            p.id_participacion,
            p.fecha_compra,
            p.estado AS estado_participacion,
            nr.numero,
            r.titulo,
            r.premio,
            r.imagen,
            pa.monto,
            pa.metodo_pago,
            pa.estado AS estado_pago

        FROM participaciones p

        INNER JOIN numeros_rifa nr
            ON p.id_numero = nr.id_numero

        INNER JOIN rifas r
            ON nr.id_rifa = r.id_rifa

        LEFT JOIN pagos pa
            ON p.id_participacion = pa.id_participacion

        WHERE p.id_usuario = ?

        ORDER BY p.fecha_compra DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();

$resultado = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Historial - RifaGo</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

</head>

<body>

<div class="app">

    <!-- HEADER -->

    <header class="header">

        <a href="../index.php" class="logo">

            <span class="logo-blue">Rifa</span><span class="logo-red">Go</span><sup>+</sup>

        </a>

        <a href="perfil.php" class="user-icon">

            <span>
                <?php echo $iniciales; ?>
            </span>

        </a>

    </header>


    <main class="main-content">

        <a
            href="perfil.php"
            class="back-link"
        >
            ← Volver al perfil
        </a>


        <section class="page-title">

            <h1>
                Historial de transacciones
            </h1>

            <p>
                Acá podés consultar tus compras y participaciones.
            </p>

        </section>


        <?php if ($resultado->num_rows > 0): ?>


            <section class="history-list">


                <?php while ($registro = $resultado->fetch_assoc()): ?>


                    <div class="history-card">


                        <div class="history-image">

                            <?php if (!empty($registro['imagen'])): ?>

                                <img
                                    src="<?php echo htmlspecialchars($registro['imagen']); ?>"
                                    alt="Rifa"
                                >

                            <?php else: ?>

                                <span>
                                    🎟
                                </span>

                            <?php endif; ?>

                        </div>


                        <div class="history-content">

                            <h3>
                                <?php echo htmlspecialchars($registro['titulo']); ?>
                            </h3>

                            <p>
                                Número:
                                <strong>
                                    <?php echo htmlspecialchars($registro['numero']); ?>
                                </strong>
                            </p>

                            <p>
                                Fecha:
                                <?php
                                echo date(
                                    "d/m/Y",
                                    strtotime($registro['fecha_compra'])
                                );
                                ?>
                            </p>


                            <?php if ($registro['monto'] !== null): ?>

                                <p>
                                    Monto:
                                    $<?php echo number_format(
                                        $registro['monto'],
                                        2,
                                        ',',
                                        '.'
                                    ); ?>
                                </p>

                            <?php endif; ?>


                            <?php if (!empty($registro['metodo_pago'])): ?>

                                <p>
                                    Pago:
                                    <?php echo htmlspecialchars(
                                        $registro['metodo_pago']
                                    ); ?>
                                </p>

                            <?php endif; ?>


                            <span
                                class="history-status
                                <?php
                                echo ($registro['estado_pago'] === 'aprobado')
                                    ? 'status-success'
                                    : 'status-pending';
                                ?>"
                            >

                                <?php

                                if ($registro['estado_pago'] === 'aprobado') {
                                    echo "Pago aprobado";
                                } elseif ($registro['estado_pago'] === 'rechazado') {
                                    echo "Pago rechazado";
                                } else {
                                    echo "Pendiente";
                                }

                                ?>

                            </span>

                        </div>

                    </div>


                <?php endwhile; ?>


            </section>


        <?php else: ?>


            <section class="empty-state">

                <div class="empty-icon">
                    ◷
                </div>

                <h2>
                    Todavía no tenés transacciones
                </h2>

                <p>
                    Cuando compres una participación,
                    va a aparecer acá.
                </p>

                <a
                    href="../index.php"
                    class="save-button"
                >
                    Ver rifas
                </a>

            </section>


        <?php endif; ?>


    </main>


    <!-- NAV -->

    <nav class="bottom-nav">

        <a href="../index.php" class="nav-item">

            <span class="nav-icon">⌂</span>

            <span>Inicio</span>

        </a>

        <a href="mis_rifas.php" class="nav-item">

            <span class="nav-icon">▤</span>

            <span>Mis rifas</span>

        </a>

        <button
            class="create-button"
            onclick="window.location.href='crear_rifa.php'"
        >
            <span>+</span>
        </button>

        <a href="participaciones.php" class="nav-item">

            <span class="nav-icon">♧</span>

            <span>Participaciones</span>

        </a>

        <a href="perfil.php" class="nav-item active">

            <span class="nav-icon">♙</span>

            <span>Perfil</span>

        </a>

    </nav>

</div>

</body>
</html>