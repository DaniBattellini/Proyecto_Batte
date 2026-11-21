<?php
require_once "conexion.php";
require_once "funcionesval.php";

$error = "";

if (
    empty(trim($_POST['nombre'] ?? '')) ||
    empty(trim($_POST['apellido'] ?? '')) ||
    empty(trim($_POST['dni'] ?? '')) ||
    empty(trim($_POST['cuil'] ?? '')) ||
    empty(trim($_POST['carrera'] ?? ''))
) {
    header("Location:bedelia_agregar_registro.php?mensaje=" . urlencode("Faltan datos."));
    exit();
}

$nombre = preg_replace('/\s+/', ' ', trim($_POST['nombre']));
$apellido = preg_replace('/\s+/', ' ', trim($_POST['apellido']));
$dni = trim($_POST['dni']);
$clave = trim($_POST['clave'] ?? '');
$cuil = trim($_POST['cuil']);
$carrera = trim($_POST['carrera']);
$_POST['nombre'] = $nombre;
$_POST['apellido'] = $apellido;
$_POST['dni'] = $dni;
$_POST['cuil'] = $cuil;

if (!ValidacionDatos()) {
    header("Location:bedelia_agregar_registro.php?mensaje=" . urlencode($error));
    exit();
}

$stmt = mysqli_prepare(
    $conex,
    "SELECT u.idUsuario
     FROM usuario u
     LEFT JOIN alumnos a ON a.Usuario_idUsuario = u.idUsuario
     WHERE u.dni = ? AND u.perfil = '1' AND a.id IS NULL
     ORDER BY u.idUsuario DESC
     LIMIT 2"
);
if (!$stmt) {
    header("Location:bedelia_agregar_registro.php?mensaje=" . urlencode("No se pudo verificar el usuario: " . mysqli_error($conex)));
    exit();
}

mysqli_stmt_bind_param($stmt, "s", $dni);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $usuarioExistenteId);
$usuariosSinFicha = [];
while (mysqli_stmt_fetch($stmt)) {
    $usuariosSinFicha[] = (int)$usuarioExistenteId;
}
mysqli_stmt_close($stmt);

if (count($usuariosSinFicha) > 1) {
    header("Location:bedelia_agregar_registro.php?mensaje=" . urlencode("Hay más de una cuenta sin ficha para ese DNI. Revisá los datos antes de continuar."));
    exit();
}

$vincularUsuarioExistente = count($usuariosSinFicha) === 1;
if ($vincularUsuarioExistente && $clave !== '') {
    header("Location:bedelia_agregar_registro.php?mensaje=" . urlencode("Ese DNI ya tiene una cuenta. Dejá la clave vacía para conservar la contraseña existente."));
    exit();
}

if (!$vincularUsuarioExistente) {
    $stmt = mysqli_prepare($conex, "SELECT idUsuario FROM usuario WHERE dni = ? LIMIT 1");
    if (!$stmt) {
        header("Location:bedelia_agregar_registro.php?mensaje=" . urlencode("No se pudo verificar el DNI: " . mysqli_error($conex)));
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $dni);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $dniYaRegistrado = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if ($dniYaRegistrado) {
        header("Location:bedelia_agregar_registro.php?mensaje=" . urlencode("Ya existe una cuenta asociada a ese DNI y tiene una ficha vinculada."));
        exit();
    }

    if (strlen($clave) < 8) {
        header("Location:bedelia_agregar_registro.php?mensaje=" . urlencode("La clave debe tener al menos 8 caracteres para crear una cuenta nueva."));
        exit();
    }
}

mysqli_begin_transaction($conex);

if (!$vincularUsuarioExistente) {
    $stmt = mysqli_prepare(
        $conex,
        "INSERT INTO usuario (dni, clave, perfil, nombre_carrera) VALUES (?, ?, '1', '')"
    );
    if (!$stmt) {
        mysqli_rollback($conex);
        header("Location:bedelia_agregar_registro.php?mensaje=" . urlencode("No se pudo preparar la creación del usuario: " . mysqli_error($conex)));
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ss", $dni, $clave);
    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        mysqli_rollback($conex);
        header("Location:bedelia_agregar_registro.php?mensaje=" . urlencode("No se pudo crear el usuario: " . $error));
        exit();
    }
    $usuarioId = mysqli_insert_id($conex);
    mysqli_stmt_close($stmt);
} else {
    $usuarioId = $usuariosSinFicha[0];
}

$stmt = mysqli_prepare(
    $conex,
    "INSERT INTO alumnos (dni, cuil, nombre, apellido, carrera, Usuario_idUsuario)
     VALUES (?, ?, ?, ?, ?, ?)"
);
if (!$stmt) {
    mysqli_rollback($conex);
    header("Location:bedelia_agregar_registro.php?mensaje=" . urlencode("No se pudo preparar la ficha del alumno: " . mysqli_error($conex)));
    exit();
}

mysqli_stmt_bind_param($stmt, "sssssi", $dni, $cuil, $nombre, $apellido, $carrera, $usuarioId);
if (!mysqli_stmt_execute($stmt)) {
    $error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    mysqli_rollback($conex);
    header("Location:bedelia_agregar_registro.php?mensaje=" . urlencode("No se pudo guardar la ficha del alumno: " . $error));
    exit();
}

mysqli_stmt_close($stmt);
mysqli_commit($conex);

header("Location:inicio_bedelia.php?mensaje=ok");
exit();
?>
