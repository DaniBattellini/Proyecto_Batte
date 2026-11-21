<?php
session_start();
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$pass = "";
$db = "inscripcioncursado";

$conex = new mysqli($host, $user, $pass, $db);
if ($conex->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
if (empty($correo)) {
    echo json_encode(['success' => false, 'message' => 'El correo es obligatorio.']);
    exit;
}

$materias = [
    1 => "Sistemas Operativos",
    2 => "Comunicación",
    3 => "Unidad de Definición Institucional I - Lengua",
    4 => "Matemática",
    5 => "Inglés Técnico I",
    6 => "Administración",
    7 => "Tecnologías de la Información",
    8 => "Lógica y Estructura de Datos",
    9 => "Ingeniería de Software I",
    10 => "Unidad de Definición Institucional II",
    11 => "Problemáticas Socio Contemporáneas",
    12 => "Inglés Técnico II",
    13 => "Innovación y Desarrollo Emprendedor",
    14 => "Estadística",
    15 => "Programación I",
    16 => "Ingeniería de Software II",
    17 => "Base de Datos I",
    18 => "Práctica Profesionalizante I",
    19 => "Derecho y Legislación Laboral",
    20 => "Ética y Responsabilidad Social",
    21 => "Redes y Comunicación",
    22 => "Programación II",
    23 => "Gestión de Proyectos de Software",
    24 => "Base de Datos II",
    25 => "Práctica Profesionalizante II"
];

// Buscar o crear alumno
$stmt = $conex->prepare("SELECT id FROM alumnos WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->bind_result($alumno_id);
if (!$stmt->fetch()) {
    $stmt->close();
    $nombre = "Sin Nombre";
    $apellido = "Sin Apellido";
    $dni = "";
    $cuil = "";
    $carrera = "";
    $stmt = $conex->prepare("INSERT INTO alumnos (nombre, apellido, dni, cuil, carrera, correo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $nombre, $apellido, $dni, $cuil, $carrera, $correo);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error al crear alumno.']);
        exit;
    }
    $alumno_id = $stmt->insert_id;
    $stmt->close();
} else {
    $stmt->close();
}

if (!$alumno_id) {
    echo json_encode(['success' => false, 'message' => 'No se pudo obtener o crear el alumno.']);
    exit;
}

// Insertar inscripciones
foreach ($materias as $num => $nombre_materia) {
    if (isset($_POST["materia$num"])) {
        $condicion = $_POST["materia$num"];
        $condiciones_validas = ['presencial', 'semipresencial', 'libre'];
        if ($condicion !== "No me inscribo" && in_array($condicion, $condiciones_validas)) {
            // Evitar duplicados
            $checkStmt = $conex->prepare("SELECT COUNT(*) FROM inscripcioncursado WHERE alumno_id = ? AND materia = ?");
            $checkStmt->bind_param("is", $alumno_id, $nombre_materia);
            $checkStmt->execute();
            $checkStmt->bind_result($count);
            $checkStmt->fetch();
            $checkStmt->close();

            if ($count == 0) {
                $stmt = $conex->prepare("INSERT INTO inscripcioncursado (alumno_id, materia, condicion_cursado) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $alumno_id, $nombre_materia, $condicion);
                if (!$stmt->execute()) {
                    error_log("Error al insertar inscripción: " . $stmt->error);
                }
                $stmt->close();
            }
        }
    }
}

$conex->close();

echo json_encode(['success' => true]);
exit;
?>
