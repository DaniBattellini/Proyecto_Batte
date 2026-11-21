<?php
// Configuración de la base de datos
$host = "localhost";
$user = "root";
$pass = "";
$db = "inscripcioncursado";

$conex = new mysqli($host, $user, $pass, $db);
if ($conex->connect_error) {
    die("Error de conexión: " . $conex->connect_error);
}

// Obtener datos del formulario
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$materia = isset($_POST['materia']) ? trim($_POST['materia']) : '';
$condicion_cursado = isset($_POST['condicion_cursado']) ? trim($_POST['condicion_cursado']) : '';

if (!$nombre || !$apellido || !$correo || !$materia || !$condicion_cursado) {
    die("Por favor completa todos los campos.");
}

// Buscar si el alumno ya existe por correo
$stmt = $conex->prepare("SELECT id FROM alumnos2 WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->bind_result($alumno_id);
if (!$stmt->fetch()) {
    // No existe, insertar alumno nuevo
    $stmt->close();
    $stmt = $conex->prepare("INSERT INTO alumnos2 (nombre, apellido, correo) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $apellido, $correo);
    if (!$stmt->execute()) {
        die("Error al insertar alumno: " . $stmt->error);
    }
    $alumno_id = $stmt->insert_id;
    $stmt->close();
} else {
    $stmt->close();
}

// Insertar inscripción
$stmt = $conex->prepare("INSERT INTO inscripcioncursado (alumno_id, materia, condicion_cursado) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $alumno_id, $materia, $condicion_cursado);
if ($stmt->execute()) {
    echo "Inscripción guardada correctamente.";
} else {
    echo "Error al guardar inscripción: " . $stmt->error;
}
$stmt->close();
$conex->close();
?>
