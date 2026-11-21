<?php
session_start();

ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();
header('Content-Type: application/json; charset=utf-8');

function responderInscripcionMesas(bool $success, string $message, int $guardadas = 0): void
{
    ob_clean();
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'guardadas' => $guardadas,
    ]);
    exit();
}

if (!isset($_SESSION['dnialu'])) {
    responderInscripcionMesas(false, 'La sesión expiró. Iniciá sesión nuevamente.');
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/mesas_inscripcion.php';
require_once __DIR__ . '/materias_carrera.php';

$periodo = obtenerPeriodoMesasAbierto($conex);
if (!$periodo) {
    responderInscripcionMesas(false, 'La inscripción a mesas está cerrada.');
}

$dni = $_SESSION['dnialu'];
$stmt = $conex->prepare("SELECT id, carrera FROM alumnos WHERE dni = ? LIMIT 1");
$stmt->bind_param("s", $dni);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$alumno) {
    responderInscripcionMesas(false, 'No se encontró una ficha de alumno para tu usuario.');
}

$carreraId = obtenerIdCarreraAlumno($alumno['carrera'] ?? '');
if (!$carreraId) {
    responderInscripcionMesas(false, 'No se pudo identificar tu carrera. Contactá a Bedelía.');
}

$alumnoId = (int)$alumno['id'];
$periodoId = (int)$periodo['idPeriodoMesa'];
$stmt = $conex->prepare(
    "SELECT idMesa
     FROM mesas_materias
     WHERE periodo_id = ? AND carrera_id = ? AND fecha_examen IS NOT NULL"
);
$stmt->bind_param("ii", $periodoId, $carreraId);
$stmt->execute();
$result = $stmt->get_result();
$mesasPermitidas = [];
while ($fila = $result->fetch_assoc()) {
    $mesasPermitidas[(int)$fila['idMesa']] = true;
}
$stmt->close();

if (empty($mesasPermitidas)) {
    responderInscripcionMesas(false, 'Bedelía todavía no cargó fechas de examen para tu carrera.');
}

$seleccion = $_POST['rinde'] ?? null;
if (!is_array($seleccion) || count($seleccion) !== count($mesasPermitidas)) {
    responderInscripcionMesas(false, 'Elegí “Rindo” o “No rindo” para cada materia.');
}

foreach ($mesasPermitidas as $mesaId => $permitida) {
    if (
        !array_key_exists($mesaId, $seleccion) ||
        !in_array((string)$seleccion[$mesaId], ['0', '1'], true)
    ) {
        responderInscripcionMesas(false, 'La selección de materias no es válida. Actualizá la página e intentá nuevamente.');
    }
}

mysqli_begin_transaction($conex);
$stmt = $conex->prepare(
    "INSERT INTO inscripciones_mesas (mesa_id, alumno_id, rinde)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE
       rinde = VALUES(rinde),
       fecha_inscripcion = CURRENT_TIMESTAMP"
);

foreach ($mesasPermitidas as $mesaId => $permitida) {
    $rinde = (int)$seleccion[$mesaId];
    $stmt->bind_param("iii", $mesaId, $alumnoId, $rinde);
    $stmt->execute();
}

$stmt->close();
mysqli_commit($conex);
$conex->close();

responderInscripcionMesas(true, 'Tu inscripción a mesas quedó guardada.', count($mesasPermitidas));
?>
