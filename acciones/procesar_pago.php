<?php


require_once "../conexion.php";

require_once "notificar.php";


session_start();


// ==========================================
// VERIFICAR SESIÓN
// ==========================================

if (!isset($_SESSION['id_usuario'])) {

    header("Location: ../login.php");
    exit;

}


// ==========================================
// VERIFICAR DATOS
// ==========================================

if (
    !isset($_POST['id_rifa']) ||
    !isset($_POST['numeros']) ||
    !isset($_POST['metodo_pago'])
) {

    header("Location: ../index.php");
    exit;

}


// ==========================================
// DATOS
// ==========================================

$id_usuario =
    intval($_SESSION['id_usuario']);


$id_rifa =
    intval($_POST['id_rifa']);


$numeros = array_map(
    'intval',
    $_POST['numeros']
);


$metodo_pago =
    $_POST['metodo_pago'];



/* ==========================================
   TRANSACCIÓN
========================================== */

$conexion->begin_transaction();


try {


    /* ==========================================
       VERIFICAR LOS NÚMEROS
    ========================================== */

    $placeholders = implode(
        ',',
        array_fill(
            0,
            count($numeros),
            '?'
        )
    );


    $tipos =
        str_repeat(
            'i',
            count($numeros)
        );


    $sql = "

        SELECT *

        FROM numeros_rifa

        WHERE id_numero IN ($placeholders)

        AND id_rifa = ?

        FOR UPDATE

    ";


    $stmt =
        $conexion->prepare($sql);


    $parametros =
        $numeros;


    $parametros[] =
        $id_rifa;


    $tipos .= 'i';


    $stmt->bind_param(
        $tipos,
        ...$parametros
    );


    $stmt->execute();


    $resultado =
        $stmt->get_result();


    $numeros_db = [];


    while (
        $numero =
            $resultado->fetch_assoc()
    ) {


        if (
            $numero['estado']
            !== 'disponible'
        ) {

            throw new Exception(
                "Uno de los números ya no está disponible."
            );

        }


        $numeros_db[] =
            $numero;

    }


    // ==========================================
    // VERIFICAR CANTIDAD
    // ==========================================

    if (
        count($numeros_db)
        !== count($numeros)
    ) {

        throw new Exception(
            "Algunos números ya no están disponibles."
        );

    }



    /* ==========================================
       OBTENER DATOS DE LA RIFA
    ========================================== */

    $sql = "

        SELECT

            id_usuario,
            precio_numero

        FROM rifas

        WHERE id_rifa = ?

    ";


    $stmt =
        $conexion->prepare($sql);


    $stmt->bind_param(
        "i",
        $id_rifa
    );


    $stmt->execute();


    $rifa =
        $stmt
        ->get_result()
        ->fetch_assoc();


    // ==========================================
    // VERIFICAR RIFA
    // ==========================================

    if (!$rifa) {

        throw new Exception(
            "La rifa no existe."
        );

    }


    // ==========================================
    // DATOS DE LA RIFA
    // ==========================================

    $id_creador =
        intval(
            $rifa['id_usuario']
        );


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



    /* ==========================================
       CREAR PAGOS
    ========================================== */

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
        $conexion->prepare(
            $sql_pago
        );



    /* ==========================================
       PROCESAR CADA NÚMERO
    ========================================== */

    foreach (
        $numeros_db
        as $numero
    ) {


        $id_numero =
            $numero['id_numero'];



        // ==========================================
        // CREAR PARTICIPACIÓN
        // ==========================================

        $stmt_participacion->bind_param(

            "ii",

            $id_usuario,

            $id_numero

        );


        $stmt_participacion->execute();


        $id_participacion =
            $conexion->insert_id;



        // ==========================================
        // CREAR PAGO
        // ==========================================

        $monto =
            $rifa['precio_numero'];


        $stmt_pago->bind_param(

            "ids",

            $id_participacion,

            $monto,

            $metodo_pago

        );


        $stmt_pago->execute();



        // ==========================================
        // MARCAR NÚMERO COMO VENDIDO
        // ==========================================

        $sql_update = "

            UPDATE numeros_rifa

            SET estado = 'vendido'

            WHERE id_numero = ?

        ";


        $stmt_update =
            $conexion->prepare(
                $sql_update
            );


        $stmt_update->bind_param(

            "i",

            $id_numero

        );


        $stmt_update->execute();


        $stmt_update->close();


    }



    /* ==========================================
       CONFIRMAR TRANSACCIÓN
    ========================================== */

    $conexion->commit();



    /* ==========================================
   NOTIFICACIÓN PARA EL COMPRADOR
    ========================================== */

    crearNotificacion(

        $conexion,

        $id_usuario,

        "participacion_confirmada",

        "¡Participación confirmada!",

        "Tus números fueron registrados correctamente.",

        $id_rifa,

        "../pages/participaciones.php"

    );


    /* ==========================================
    NOTIFICACIÓN PARA EL CREADOR
    ========================================== */

    if (

        $id_creador
        !== $id_usuario

    ) {

        crearNotificacion(

            $conexion,

            $id_creador,

            "nueva_participacion",

            "¡Nueva participación!",

            "Un usuario acaba de participar en tu rifa.",

            $id_rifa,

            "../pages/mis_rifas.php"

        );

    }

    



    /* ==========================================
       GUARDAR DATOS DE CONFIRMACIÓN
    ========================================== */

    $_SESSION['compra_exitosa'] =
        true;


    $_SESSION['compra_id_rifa'] =
        $id_rifa;


    $_SESSION['compra_numeros'] =
        $numeros;


    $_SESSION['compra_total'] =
        $total;



    /* ==========================================
       REDIRIGIR
    ========================================== */

    header(
        "Location: ../pages/confirmacion_compra.php"
    );

    exit;



} catch (Exception $e) {


    $conexion->rollback();


    die(

        "No se pudo completar la compra: "

        . $e->getMessage()

    );

}

