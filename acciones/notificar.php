<?php


// ==========================================
// CREAR NOTIFICACIÓN
// ==========================================

function crearNotificacion(

    $conexion,
    $id_usuario,
    $tipo,
    $titulo,
    $mensaje,
    $id_rifa = null,
    $enlace = null

) {


    // ==========================================
    // VALIDAR ID USUARIO
    // ==========================================

    $id_usuario = intval($id_usuario);


    if ($id_usuario <= 0) {

        return false;

    }


    // ==========================================
    // VALIDAR ID RIFA
    // ==========================================

    if ($id_rifa !== null) {

        $id_rifa = intval($id_rifa);

    }


    // ==========================================
    // INSERTAR NOTIFICACIÓN
    // ==========================================

    $sql = "

        INSERT INTO notificaciones (

            id_usuario,
            id_rifa,
            tipo,
            titulo,
            mensaje,
            enlace,
            leida

        )

        VALUES (

            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            0

        )

    ";


    $stmt = $conexion->prepare($sql);


    // ==========================================
    // VERIFICAR CONSULTA
    // ==========================================

    if (!$stmt) {

        return false;

    }


    // ==========================================
    // ASIGNAR DATOS
    // ==========================================

    $stmt->bind_param(

        "iissss",

        $id_usuario,
        $id_rifa,
        $tipo,
        $titulo,
        $mensaje,
        $enlace

    );


    // ==========================================
    // EJECUTAR
    // ==========================================

    $resultado = $stmt->execute();


    // ==========================================
    // CERRAR CONSULTA
    // ==========================================

    $stmt->close();


    return $resultado;

}


// ==========================================
// VERIFICAR SI YA EXISTE UNA NOTIFICACIÓN
// ==========================================

function notificacionExiste(

    $conexion,
    $id_usuario,
    $tipo,
    $id_rifa = null

) {


    // ==========================================
    // CONSULTA BASE
    // ==========================================

    $sql = "

        SELECT id_notificacion

        FROM notificaciones

        WHERE id_usuario = ?

        AND tipo = ?

    ";


    // ==========================================
    // AGREGAR RIFA
    // ==========================================

    if ($id_rifa !== null) {

        $sql .= "

            AND id_rifa = ?

        ";

    } else {

        $sql .= "

            AND id_rifa IS NULL

        ";

    }


    $stmt = $conexion->prepare($sql);


    // ==========================================
    // VERIFICAR CONSULTA
    // ==========================================

    if (!$stmt) {

        return false;

    }


    // ==========================================
    // ASIGNAR PARÁMETROS
    // ==========================================

    if ($id_rifa !== null) {

        $id_rifa = intval($id_rifa);

        $stmt->bind_param(

            "isi",

            $id_usuario,
            $tipo,
            $id_rifa

        );

    } else {

        $stmt->bind_param(

            "is",

            $id_usuario,
            $tipo

        );

    }


    // ==========================================
    // EJECUTAR
    // ==========================================

    $stmt->execute();


    $resultado = $stmt->get_result();


    // ==========================================
    // VERIFICAR RESULTADO
    // ==========================================

    $existe = $resultado->num_rows > 0;


    // ==========================================
    // CERRAR CONSULTA
    // ==========================================

    $stmt->close();


    return $existe;

}


// ==========================================
// CREAR NOTIFICACIÓN SIN DUPLICADOS
// ==========================================

function crearNotificacionUnica(

    $conexion,
    $id_usuario,
    $tipo,
    $titulo,
    $mensaje,
    $id_rifa = null,
    $enlace = null

) {


    // ==========================================
    // VERIFICAR DUPLICADO
    // ==========================================

    $existe = notificacionExiste(

        $conexion,
        $id_usuario,
        $tipo,
        $id_rifa

    );


    // ==========================================
    // YA EXISTE
    // ==========================================

    if ($existe) {

        return false;

    }


    // ==========================================
    // CREAR NOTIFICACIÓN
    // ==========================================

    return crearNotificacion(

        $conexion,
        $id_usuario,
        $tipo,
        $titulo,
        $mensaje,
        $id_rifa,
        $enlace

    );

}


// ==========================================
// OBTENER NOTIFICACIONES DEL USUARIO
// ==========================================

function obtenerNotificaciones(

    $conexion,
    $id_usuario

) {


    // ==========================================
    // CONSULTAR NOTIFICACIONES
    // ==========================================

    $sql = "

        SELECT *

        FROM notificaciones

        WHERE id_usuario = ?

        ORDER BY fecha_creacion DESC

    ";


    $stmt = $conexion->prepare($sql);


    // ==========================================
    // VERIFICAR CONSULTA
    // ==========================================

    if (!$stmt) {

        return false;

    }


    // ==========================================
    // ASIGNAR USUARIO
    // ==========================================

    $stmt->bind_param(

        "i",

        $id_usuario

    );


    // ==========================================
    // EJECUTAR
    // ==========================================

    $stmt->execute();


    $resultado = $stmt->get_result();


    // ==========================================
    // GUARDAR NOTIFICACIONES
    // ==========================================

    $notificaciones = [];


    while ($fila = $resultado->fetch_assoc()) {

        $notificaciones[] = $fila;

    }


    // ==========================================
    // CERRAR CONSULTA
    // ==========================================

    $stmt->close();


    return $notificaciones;

}


// ==========================================
// CONTAR NOTIFICACIONES NO LEÍDAS
// ==========================================

function contarNotificacionesNoLeidas(

    $conexion,
    $id_usuario

) {


    $sql = "

        SELECT COUNT(*) AS total

        FROM notificaciones

        WHERE id_usuario = ?

        AND leida = 0

    ";


    $stmt = $conexion->prepare($sql);


    if (!$stmt) {

        return 0;

    }


    $stmt->bind_param(

        "i",

        $id_usuario

    );


    $stmt->execute();


    $resultado = $stmt->get_result();


    $fila = $resultado->fetch_assoc();


    $stmt->close();


    return intval(

        $fila["total"] ?? 0

    );

}


// ==========================================
// MARCAR UNA NOTIFICACIÓN COMO LEÍDA
// ==========================================

function marcarNotificacionLeida(

    $conexion,
    $id_notificacion,
    $id_usuario

) {


    $sql = "

        UPDATE notificaciones

        SET leida = 1

        WHERE id_notificacion = ?

        AND id_usuario = ?

    ";


    $stmt = $conexion->prepare($sql);


    if (!$stmt) {

        return false;

    }


    $stmt->bind_param(

        "ii",

        $id_notificacion,
        $id_usuario

    );


    $resultado = $stmt->execute();


    $stmt->close();


    return $resultado;

}


// ==========================================
// MARCAR TODAS COMO LEÍDAS
// ==========================================

function marcarTodasLeidas(

    $conexion,
    $id_usuario

) {


    $sql = "

        UPDATE notificaciones

        SET leida = 1

        WHERE id_usuario = ?

        AND leida = 0

    ";


    $stmt = $conexion->prepare($sql);


    if (!$stmt) {

        return false;

    }


    $stmt->bind_param(

        "i",

        $id_usuario

    );


    $resultado = $stmt->execute();


    $stmt->close();


    return $resultado;

}