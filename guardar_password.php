<?php
session_start();

// Detectar sesión de alumno o bedel
if (isset($_SESSION['dnialu'])) {
    $dni = $_SESSION['dnialu'];
    $redirect = 'perfil.php';
} elseif (isset($_SESSION['dnibedel'])) {
    $dni = $_SESSION['dnibedel'];
    $redirect = 'perfil_bedel.php';
} else {
    header("Location: index.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$db = "inscripcioncursado";

$conex = new mysqli($host, $user, $pass, $db);

if ($conex->connect_error) {
    die("Error de conexión");
}

$clave_actual    = trim($_POST['clave_actual']);
$clave_nueva     = trim($_POST['clave_nueva']);
$confirmar_clave = trim($_POST['confirmar_clave']);

if ($clave_nueva != $confirmar_clave) {
    header("Location: cambiar_password.php?error=" . urlencode("Las contraseñas no coinciden"));
    exit();
}

$sql  = "SELECT clave FROM usuario WHERE dni = ?";
$stmt = $conex->prepare($sql);
$stmt->bind_param("s", $dni);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: cambiar_password.php?error=" . urlencode("Usuario no encontrado"));
    exit();
}

$datos = $resultado->fetch_assoc();

if ($datos['clave'] != $clave_actual) {
    header("Location: cambiar_password.php?error=" . urlencode("La contraseña actual es incorrecta"));
    exit();
}

$update = "UPDATE usuario SET clave = ? WHERE dni = ?";
$stmt2  = $conex->prepare($update);
$stmt2->bind_param("ss", $clave_nueva, $dni);

if ($stmt2->execute()) {
    header("Location: cambiar_password.php?mensaje=ok");
} else {
    header("Location: cambiar_password.php?error=" . urlencode("No se pudo actualizar la contraseña"));
}

$conex->close();
?>
