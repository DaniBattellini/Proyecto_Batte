<?php
// ── La sesión SIEMPRE va primero ──────────────────────────────────────────────
session_start();

ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();
header('Content-Type: application/json; charset=utf-8');

// ── Verificar sesión ──────────────────────────────────────────────────────────
if (!isset($_SESSION['dnialu'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Sesión expirada. Iniciá sesión nuevamente.']);
    exit;
}

$dni_sesion = $_SESSION['dnialu'];

// ── Conexión ──────────────────────────────────────────────────────────────────
$conn = new mysqli('localhost', 'root', '', 'inscripcioncursado');
if ($conn->connect_error) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $conn->connect_error]);
    exit;
}
$conn->set_charset("utf8mb4");
$conn->query("SET NAMES 'utf8mb4'");

require_once __DIR__ . '/periodo_inscripcion.php';
if (!obtenerPeriodoInscripcionAbierto($conn)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'El período de inscripción está cerrado. No se pueden guardar inscripciones.']);
    exit;
}

// ── Obtener alumno_id desde sesión ────────────────────────────────────────────
$alumno_id = null;
$s = $conn->prepare("SELECT id FROM alumnos WHERE dni = ? LIMIT 1");
$s->bind_param("s", $dni_sesion);
$s->execute();
$s->bind_result($alumno_id);
$s->fetch();
$s->close();

if (!$alumno_id) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Alumno no encontrado para el DNI ' . $dni_sesion]);
    exit;
}

// ── Materias de Gestión de las Organizaciones ─────────────────────────────────
require_once __DIR__ . '/materias_carrera.php';
$materias = obtenerMateriasPorCarrera(1);

$validCondiciones = ['presencial', 'semipresencial', 'libre'];
$seleccionadas = [];

foreach ($materias as $num => $nombreMateria) {
    $val = isset($_POST["materia$num"]) ? strtolower(trim($_POST["materia$num"])) : '';
    if ($val !== 'no' && $val !== '' && in_array($val, $validCondiciones)) {
        $seleccionadas[] = ['materia' => $nombreMateria, 'condicion' => $val];
    }
}

if (empty($seleccionadas)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Seleccioná al menos una materia válida.']);
    exit;
}

// ── Eliminar inscripciones anteriores del alumno ──────────────────────────────
$stmtDel = $conn->prepare("DELETE FROM inscripciones WHERE alumno_id = ?");
$stmtDel->bind_param('i', $alumno_id);
$stmtDel->execute();
$stmtDel->close();

// ── Insertar nuevas inscripciones ─────────────────────────────────────────────
$correo = trim($_POST['correo'] ?? '');
date_default_timezone_set('America/Argentina/Buenos_Aires');
$fecha  = date('Y-m-d H:i:s');

$stmtIns = $conn->prepare(
    "INSERT INTO inscripciones (alumno_id, materia, condicion_cursado, fecha_inscripcion, correo_electronico)
     VALUES (?, ?, ?, ?, ?)"
);

if (!$stmtIns) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Error al preparar inserción: ' . $conn->error]);
    exit;
}

$p_materia   = "";
$p_condicion = "";
$stmtIns->bind_param("issss", $alumno_id, $p_materia, $p_condicion, $fecha, $correo);

foreach ($seleccionadas as $insc) {
    $p_materia   = $insc['materia'];
    $p_condicion = $insc['condicion'];
    if (!$stmtIns->execute()) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Error al insertar: ' . $stmtIns->error]);
        exit;
    }
}

$stmtIns->close();
$conn->close();

ob_clean();
echo json_encode(['success' => true, 'guardadas' => count($seleccionadas)]);
exit;
?>
