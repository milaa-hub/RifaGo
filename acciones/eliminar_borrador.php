<?php

require_once "../conexion.php";
session_start();


// ==================================================
// VERIFICAR SESIÓN
// ==================================================

if (!isset($_SESSION['id_usuario'])) {

    header("Location: ../login.php");
    exit;

}


// ==================================================
// VERIFICAR ID
// ==================================================

if (
    !isset($_POST['id_rifa']) ||
    !is_numeric($_POST['id_rifa'])
) {

    header("Location: ../pages/mis_rifas.php");
    exit;

}


$id_rifa = intval($_POST['id_rifa']);

$id_usuario = $_SESSION['id_usuario'];


// ==================================================
// ELIMINAR BORRADOR
// ==================================================

$consulta = $conexion->prepare("
    DELETE FROM rifas
    WHERE id_rifa = ?
    AND id_usuario = ?
    AND estado = 'borrador'
");


$consulta->bind_param(
    "ii",
    $id_rifa,
    $id_usuario
);


$consulta->execute();

$consulta->close();


// ==================================================
// VOLVER A MIS RIFAS
// ==================================================

header("Location: ../pages/mis_rifas.php");

exit;

?>