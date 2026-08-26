<?php

require_once "conexion.php";
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

if (
    !isset($_POST['id_rifa']) ||
    !isset($_POST['numeros']) ||
    !isset($_POST['metodo_pago'])
) {
    header("Location: index.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

$id_rifa = intval($_POST['id_rifa']);

$numeros = array_map(
    'intval',
    $_POST['numeros']
);

$metodo_pago = $_POST['metodo_pago'];


/* ==========================================
   TRANSACCIÓN
========================================== */

$conexion->begin_transaction();

try {

    /*
     * Verificamos los números y los bloqueamos
     * para evitar que dos personas compren
     * el mismo número al mismo tiempo.
     */

    $placeholders = implode(
        ',',
        array_fill(0, count($numeros), '?')
    );

    $tipos = str_repeat('i', count($numeros));

    $sql = "
        SELECT *
        FROM numeros_rifa
        WHERE id_numero IN ($placeholders)
        AND id_rifa = ?
        FOR UPDATE
    ";

    $stmt = $conexion->prepare($sql);

    $parametros = $numeros;
    $parametros[] = $id_rifa;

    $tipos .= 'i';

    $stmt->bind_param(
        $tipos,
        ...$parametros
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    $numeros_db = [];

    while ($numero = $resultado->fetch_assoc()) {

        if ($numero['estado'] !== 'disponible') {

            throw new Exception(
                "Uno de los números ya no está disponible."
            );

        }

        $numeros_db[] = $numero;

    }


    if (count($numeros_db) !== count($numeros)) {

        throw new Exception(
            "Algunos números ya no están disponibles."
        );

    }


    /* ==========================================
       CALCULAR TOTAL
    ========================================== */

    $sql = "
        SELECT precio_numero
        FROM rifas
        WHERE id_rifa = ?
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "i",
        $id_rifa
    );

    $stmt->execute();

    $rifa = $stmt->get_result()->fetch_assoc();

    $total =
        count($numeros_db)
        * $rifa['precio_numero'];


    /* ==========================================
       CREAR PARTICIPACIONES
    ========================================== */

    $sql_participacion = "
        INSERT INTO participaciones
        (
            id_usuario,
            id_numero,
            fecha_compra,
            estado
        )
        VALUES
        (
            ?,
            ?,
            NOW(),
            'confirmada'
        )
    ";

    $stmt_participacion =
        $conexion->prepare(
            $sql_participacion
        );


    $sql_pago = "
        INSERT INTO pagos
        (
            id_participacion,
            monto,
            metodo_pago,
            estado,
            fecha_pago
        )
        VALUES
        (
            ?,
            ?,
            ?,
            'aprobado',
            NOW()
        )
    ";

    $stmt_pago =
        $conexion->prepare($sql_pago);


    foreach ($numeros_db as $numero) {

        $id_numero =
            $numero['id_numero'];


        /*
         * Crear participación
         */

        $stmt_participacion->bind_param(
            "ii",
            $id_usuario,
            $id_numero
        );

        $stmt_participacion->execute();


        $id_participacion =
            $conexion->insert_id;


        /*
         * Crear pago
         */

        $monto =
            $rifa['precio_numero'];

        $stmt_pago->bind_param(
            "ids",
            $id_participacion,
            $monto,
            $metodo_pago
        );

        $stmt_pago->execute();


        /*
         * Marcar número como vendido
         */

        $sql_update = "
            UPDATE numeros_rifa
            SET estado = 'vendido'
            WHERE id_numero = ?
        ";

        $stmt_update =
            $conexion->prepare($sql_update);

        $stmt_update->bind_param(
            "i",
            $id_numero
        );

        $stmt_update->execute();

    }


    /* ==========================================
       CONFIRMAR
    ========================================== */

    $conexion->commit();


    /*
     * Guardamos algunos datos para la pantalla
     * de confirmación.
     */

    $_SESSION['compra_exitosa'] = true;

    $_SESSION['compra_id_rifa'] =
        $id_rifa;

    $_SESSION['compra_numeros'] =
        $numeros;

    $_SESSION['compra_total'] =
        $total;


    header(
        "Location: confirmacion_compra.php"
    );

    exit;


} catch (Exception $e) {

    $conexion->rollback();

    die(
        "No se pudo completar la compra: "
        . $e->getMessage()
    );

}