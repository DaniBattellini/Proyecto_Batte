<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Solo alumnos autenticados
if (!isset($_SESSION["dnialu"])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado', 'inscripciones' => []]);
    exit();
}

$host     = "localhost";
$user     = "root";
$pass     = "";
$db       = "inscripcioncursado";

$conex = new mysqli($host, $user, $pass, $db);
if ($conex->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión', 'inscripciones' => []]);
    exit();
}
$conex->set_charset("utf8mb4");
$conex->query("SET NAMES 'utf8mb4'");

$dnialu = $_SESSION["dnialu"];

// Buscar el alumno_id a partir del DNI en sesión
$stmtA = $conex->prepare("SELECT id FROM alumnos WHERE dni = ?");
$stmtA->bind_param("s", $dnialu);
$stmtA->execute();
$resA = $stmtA->get_result();
$alumno = $resA->fetch_assoc();
$stmtA->close();

if (!$alumno) {
    echo json_encode(['success' => false, 'message' => 'Alumno no encontrado', 'inscripciones' => []]);
    exit();
}

$alumno_id = $alumno['id'];

// Obtener todas las inscripciones del alumno
$stmtI = $conex->prepare(
    "SELECT materia, condicion_cursado, fecha_inscripcion
     FROM inscripciones
     WHERE alumno_id = ?
     ORDER BY fecha_inscripcion DESC"
);
$stmtI->bind_param("i", $alumno_id);
$stmtI->execute();
$resI = $stmtI->get_result();

$inscripciones = [];
while ($row = $resI->fetch_assoc()) {
    $inscripciones[] = [
        'materia'           => $row['materia'],
        'condicion_cursado' => $row['condicion_cursado'],
        'fecha_inscripcion' => $row['fecha_inscripcion']
    ];
}
$stmtI->close();
$conex->close();

echo json_encode([
    'success'       => true,
    'alumno_id'     => $alumno_id,
    'inscripciones' => $inscripciones
]);
?>
