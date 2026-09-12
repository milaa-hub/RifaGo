<?php

// ==================================================
// CONEXIÓN
// ==================================================

require_once __DIR__ . "/../conexion.php";


// ==================================================
// CONFIGURACIÓN DE ZONA HORARIA
// ==================================================

date_default_timezone_set(
    "America/Argentina/Buenos_Aires"
);


// ==================================================
// MOSTRAR ERRORES EN CONSOLA
// ==================================================

error_reporting(E_ALL);

ini_set(
    "display_errors",
    1
);


// ==================================================
// MENSAJE INICIAL
// ==================================================

echo "\n";

echo "========================================\n";

echo "   RIFAGO+ - EJECUCIÓN DE SORTEOS\n";

echo "========================================\n\n";


// ==================================================
// BUSCAR RIFAS QUE YA DEBEN SORTEARSE
// ==================================================
//
// La fecha y hora se combinan:
//
// fecha_sorteo + hora_sorteo
//
// Solo buscamos rifas activas.
// ==================================================

$sql_rifas = "

    SELECT

        id_rifa,
        titulo,
        fecha_sorteo,
        hora_sorteo

    FROM rifas

    WHERE estado = 'activa'

    AND CONCAT(

        DATE(fecha_sorteo),
        ' ',
        hora_sorteo

    ) <= NOW()

";


$resultado_rifas =
    $conexion->query(
        $sql_rifas
    );


// ==================================================
// VERIFICAR ERROR
// ==================================================

if (!$resultado_rifas) {

    echo "ERROR AL BUSCAR RIFAS:\n";

    echo $conexion->error . "\n";

    exit;

}


// ==================================================
// NO HAY RIFAS PARA SORTEAR
// ==================================================

if (

    $resultado_rifas->num_rows === 0

) {

    echo "No hay rifas pendientes de sorteo.\n\n";

    echo "Finalizado.\n";

    exit;

}


// ==================================================
// RECORRER RIFAS
// ==================================================

while (

    $rifa =
        $resultado_rifas->fetch_assoc()

) {


    $id_rifa =
        intval(
            $rifa["id_rifa"]
        );


    $titulo =
        $rifa["titulo"];


    echo "----------------------------------------\n";

    echo "Procesando rifa:\n";

    echo $titulo . "\n";

    echo "ID: " . $id_rifa . "\n\n";


    // ==============================================
    // BUSCAR NÚMEROS VENDIDOS
    // ==============================================

    $sql_numeros = "

        SELECT

            nr.id_numero,
            nr.numero,
            p.id_usuario

        FROM numeros_rifa nr

        INNER JOIN participaciones p

            ON p.id_numero = nr.id_numero

        WHERE nr.id_rifa = ?

        AND nr.estado = 'vendido'

    ";


    $stmt_numeros =
        $conexion->prepare(
            $sql_numeros
        );


    if (!$stmt_numeros) {

        echo "ERROR AL PREPARAR NÚMEROS:\n";

        echo $conexion->error . "\n";

        continue;

    }


    $stmt_numeros->bind_param(

        "i",

        $id_rifa

    );


    $stmt_numeros->execute();


    $resultado_numeros =
        $stmt_numeros->get_result();


    // ==============================================
    // NO HAY PARTICIPANTES
    // ==============================================

    if (

        $resultado_numeros->num_rows === 0

    ) {

        echo "La rifa no tiene números vendidos.\n";

        echo "No se realizará el sorteo.\n\n";


        $stmt_numeros->close();

        continue;

    }


    // ==============================================
    // GUARDAR PARTICIPANTES
    // ==============================================

    $participantes = [];


    while (

        $numero =
            $resultado_numeros->fetch_assoc()

    ) {

        $participantes[] =
            $numero;

    }


    $stmt_numeros->close();


    // ==============================================
    // ELEGIR GANADOR ALEATORIAMENTE
    // ==============================================

    $indice_ganador =
        array_rand(
            $participantes
        );


    $ganador =
        $participantes[
            $indice_ganador
        ];


    $id_usuario_ganador =
        intval(
            $ganador[
                "id_usuario"
            ]
        );


    $numero_ganador =
        intval(
            $ganador[
                "numero"
            ]
        );


    // ==============================================
    // MOSTRAR RESULTADO
    // ==============================================

    echo "¡GANADOR SELECCIONADO!\n\n";

    echo "Usuario ID: ";

    echo $id_usuario_ganador;

    echo "\n";


    echo "Número ganador: ";

    echo $numero_ganador;

    echo "\n\n";


    // ==============================================
    // ACTUALIZAR RIFA
    // ==============================================

    $sql_actualizar = "

        UPDATE rifas

        SET

            estado = 'finalizada',

            numero_ganador = ?,

            id_ganador = ?,

            fecha_resultado = NOW()

        WHERE id_rifa = ?

        AND estado = 'activa'

    ";


    $stmt_actualizar =
        $conexion->prepare(
            $sql_actualizar
        );


    if (!$stmt_actualizar) {

        echo "ERROR AL ACTUALIZAR RIFA:\n";

        echo $conexion->error . "\n";

        continue;

    }


    $stmt_actualizar->bind_param(

        "iii",

        $numero_ganador,

        $id_usuario_ganador,

        $id_rifa

    );


    $stmt_actualizar->execute();


    // ==============================================
    // VERIFICAR SI SE ACTUALIZÓ
    // ==============================================

    if (

        $stmt_actualizar->affected_rows > 0

    ) {

        echo "Rifa finalizada correctamente.\n";


        // ==========================================
        // NOTIFICACIÓN PARA EL GANADOR
        // ==========================================

        $titulo_notificacion =
            "¡Felicitaciones! Ganaste una rifa";


        $mensaje_notificacion =

            "Ganaste la rifa \"" .

            $titulo .

            "\" con el número " .

            $numero_ganador .

            ". ¡Felicitaciones!";


        $tipo =
            "ganador";


        $enlace =

            "../pages/detalle-rifa.php?id=" .

            $id_rifa;


        $sql_notificacion = "

            INSERT INTO notificaciones (

                id_usuario,
                titulo,
                mensaje,
                tipo,
                enlace,
                leida,
                fecha_creacion

            )

            VALUES (

                ?,
                ?,
                ?,
                ?,
                ?,
                0,
                NOW()

            )

        ";


        $stmt_notificacion =
            $conexion->prepare(
                $sql_notificacion
            );


        if ($stmt_notificacion) {


            $stmt_notificacion->bind_param(

                "issss",

                $id_usuario_ganador,

                $titulo_notificacion,

                $mensaje_notificacion,

                $tipo,

                $enlace

            );


            $stmt_notificacion->execute();


            $stmt_notificacion->close();


            echo "Notificación enviada al ganador.\n";

        }


        // ==========================================
        // NOTIFICACIÓN PARA EL CREADOR
        // ==========================================

        $sql_creador = "

            SELECT id_usuario

            FROM rifas

            WHERE id_rifa = ?

            LIMIT 1

        ";


        $stmt_creador =
            $conexion->prepare(
                $sql_creador
            );


        if ($stmt_creador) {


            $stmt_creador->bind_param(

                "i",

                $id_rifa

            );


            $stmt_creador->execute();


            $resultado_creador =
                $stmt_creador->get_result();


            $creador =
                $resultado_creador->fetch_assoc();


            $stmt_creador->close();


            if ($creador) {


                $id_creador =
                    intval(
                        $creador[
                            "id_usuario"
                        ]
                    );


                // ======================================
                // NO ENVIAR DOS VECES SI EL CREADOR GANÓ
                // ======================================

                if (

                    $id_creador
                    !==
                    $id_usuario_ganador

                ) {


                    $titulo_creador =
                        "Tu rifa finalizó";


                    $mensaje_creador =

                        "La rifa \"" .

                        $titulo .

                        "\" ya realizó su sorteo. " .

                        "El número ganador fue el " .

                        $numero_ganador .

                        ".";


                    $tipo_creador =
                        "rifa_finalizada";


                    $stmt_notificacion_creador =
                        $conexion->prepare(
                            $sql_notificacion
                        );


                    if (
                        $stmt_notificacion_creador
                    ) {


                        $stmt_notificacion_creador
                            ->bind_param(

                                "issss",

                                $id_creador,

                                $titulo_creador,

                                $mensaje_creador,

                                $tipo_creador,

                                $enlace

                            );


                        $stmt_notificacion_creador
                            ->execute();


                        $stmt_notificacion_creador
                            ->close();


                        echo
                            "Notificación enviada al creador.\n";

                    }


                }


            }


        }


        echo "\n";

        echo "SORTEO FINALIZADO CORRECTAMENTE\n";


    } else {


        echo
            "La rifa no pudo actualizarse o ya fue sorteada.\n";


    }


    $stmt_actualizar->close();


    echo "----------------------------------------\n\n";


}


// ==================================================
// FINAL
// ==================================================

echo "\n";

echo "========================================\n";

echo "   PROCESO FINALIZADO\n";

echo "========================================\n";

?>