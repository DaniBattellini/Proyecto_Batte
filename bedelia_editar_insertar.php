<?php
session_start();

// Solo permitir acceso si hay sesión de bedel/admin
if (!isset($_SESSION['ids'])) {
    header("Location: inicio_bedelia.php?msje=Acceso denegado");
    exit();
}

// Conexion a la BD
require_once "conexion.php";
require_once "funcionesval.php";

$error = "";
$id = $_SESSION['ids'];

// Validar campos requeridos
if (
    !empty(trim($_POST['nombre'])) &&
    !empty(trim($_POST['apellido'])) &&
    !empty(trim($_POST['dni'])) &&
    !empty(trim($_POST['cuil'])) &&
    !empty(trim($_POST['usuario'])) &&
    !empty(trim($_POST['carrera']))
) {
    // Validar datos (debe devolver true si todo está bien)
    if (ValidacionDatos()) {
        // Sanitizar entradas
        $nombre = mysqli_real_escape_string($conex, preg_replace('/\s+/', ' ', $_POST['nombre']));
        $apellido = mysqli_real_escape_string($conex, preg_replace('/\s+/', ' ', $_POST['apellido']));
        $cuil = mysqli_real_escape_string($conex, $_POST['cuil']);
        $dni = mysqli_real_escape_string($conex, $_POST['dni']);
        $usuario = mysqli_real_escape_string($conex, $_POST['usuario']);
        $carrera = mysqli_real_escape_string($conex, $_POST['carrera']);

        // Actualizar datos del alumno
        $sql = "UPDATE alumnos SET dni='$dni', cuil='$cuil', nombre='$nombre', apellido='$apellido', carrera='$carrera', Usuario_idUsuario='$usuario' WHERE id=$id";
        mysqli_query($conex, $sql);

        if (mysqli_affected_rows($conex) == 1) {
            header("Location: listado_alumno.php?msje=ok");
            exit();
        } else {
            $error = "No se realizó Actualización!";
            header("Location: inicio_bedelia.php?msje=" . urlencode($error));
            exit();
        }
    } else {
        $error = "Datos inválidos!";
        header("Location: inicio_bedelia.php?msje=" . urlencode($error));
        exit();
    }
} else {
    $error = "Faltan Datos";
    header("Location: inicio_bedelia.php?msje=" . urlencode($error));
    exit();
}
?>
