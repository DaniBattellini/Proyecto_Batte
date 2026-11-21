<?php
header('Content-Type: application/json');
$conexion = new mysqli('localhost', 'root', '', 'inscripcioncursado');
if ($conexion->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']));
}

$success = true;
$message = "";

$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$dni = isset($_POST['dni']) ? trim($_POST['dni']) : '';
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
$cuil = isset($_POST['cuil']) ? trim($_POST['cuil']) : '';
$carrera = isset($_POST['carrera']) ? trim($_POST['carrera']) : '';

if ($dni && $correo) {
    // Buscar o crear alumno
    $stmt = $conexion->prepare("SELECT id FROM alumnos WHERE dni = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $stmt->bind_result($alumno_id);
    if (!$stmt->fetch()) {
        $stmt->close();
        $stmt = $conexion->prepare("INSERT INTO alumnos (nombre, apellido, dni, cuil, carrera, correo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nombre, $apellido, $dni, $cuil, $carrera, $correo);
        $stmt->execute();
        $alumno_id = $stmt->insert_id;
        $stmt->close();
    } else {
        $stmt->close();
    }

    // Recorrer materias
    $materias = [
        1 => "Economía",
        2 => "Comunicación",
        3 => "Unidad de Definición Institucional I - Lengua",
        4 => "Matemática y Estadística",
        5 => "Contabilidad",
        6 => "Administración",
        7 => "Informática",
        8 => "Gestión de la Producción",
        9 => "Gestión del Capital Humano",
        10 => "Unidad de Definición Institucional II",
        11 => "Problemáticas Socio Contemporáneas",
        12 => "Inglés Técnico I",
        13 => "Innovación y Desarrollo Emprendedor"
    ];

    foreach ($materias as $i => $nombre_materia) {
        $campo = "materia$i";
        if (isset($_POST[$campo]) && $_POST[$campo] !== "No me inscribo") {
            $condicion = $_POST[$campo];
            $stmt = $conexion->prepare("INSERT INTO inscripciones (alumno_id, materia, condicion_cursado, nombre_carrera, correo_electronico) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $alumno_id, $nombre_materia, $condicion, $carrera, $correo);
            $stmt->execute();
            $stmt->close();
        }
    }
    $message = "Inscripción realizada correctamente.";
} else {
    $success = false;
    $message = "Datos insuficientes.";
}

$conexion->close();
echo json_encode(['success' => $success, 'message' => $message]);
?>
