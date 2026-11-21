<?php
/**
 * generar_qr.php
 * Genera un QR con las últimas inscripciones del alumno y lo descarga como PNG.
 */
session_start();

if (!isset($_SESSION['dnialu'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/phpqrcode/qrlib.php';

$host = "localhost"; $user = "root"; $pass = ""; $db = "inscripcioncursado";
$conex = new mysqli($host, $user, $pass, $db);
$conex->set_charset("utf8mb4");

$dni = $_SESSION['dnialu'];

// Obtener datos del alumno
$stmtA = $conex->prepare(
    "SELECT a.id, a.nombre, a.apellido, a.carrera
     FROM alumnos a WHERE a.dni = ? LIMIT 1"
);
$stmtA->bind_param("s", $dni);
$stmtA->execute();
$alumno = $stmtA->get_result()->fetch_assoc();
$stmtA->close();

if (!$alumno) {
    die("Alumno no encontrado.");
}

// Obtener inscripciones del año en curso
$anio = date('Y');
$stmtI = $conex->prepare(
    "SELECT i.id, i.materia, i.condicion_cursado, i.fecha_inscripcion
     FROM inscripciones i
     WHERE i.alumno_id = ?
       AND YEAR(i.fecha_inscripcion) = ?
     ORDER BY i.fecha_inscripcion DESC"
);
$stmtI->bind_param("ii", $alumno['id'], $anio);
$stmtI->execute();
$res = $stmtI->get_result();
$inscripciones = [];
while ($f = $res->fetch_assoc()) $inscripciones[] = $f;
$stmtI->close();
$conex->close();

if (empty($inscripciones)) {
    die("No tenés inscripciones en el año en curso para generar el QR.");
}

// ── Construir el texto que irá en el QR ────────────────────────────────────
// Formato: ID|NombreMateria|CondicionInicial|Fecha
// Ej: 82|Sistemas Operativos|P|23/02/2026
$lineas = [];
$lineas[] = "INSCRIPCION CURSADO {$anio}";
$lineas[] = "Alumno: {$alumno['apellido']}, {$alumno['nombre']} | DNI: {$dni}";
$lineas[] = "---";
foreach ($inscripciones as $i) {
    $condInicial = strtoupper(substr($i['condicion_cursado'], 0, 1)); // P / S / L
    $fecha = date('d/m/Y', strtotime($i['fecha_inscripcion']));
    $lineas[] = "ID:{$i['id']}|{$i['materia']}|{$condInicial}|{$fecha}";
}
$texto = implode("\n", $lineas);

// ── Directorio temporal para el QR ─────────────────────────────────────────
$dirQr = __DIR__ . '/imagenes/qr/';
if (!is_dir($dirQr)) mkdir($dirQr, 0777, true);

$nombreArchivo = "qr_" . $dni . "_" . $anio . ".png";
$rutaCompleta  = $dirQr . $nombreArchivo;

// Generar el QR (tamaño de píxel 6, nivel de corrección M)
QRcode::png($texto, $rutaCompleta, QR_ECLEVEL_M, 6, 2);

// ── Devolver el PNG al navegador como descarga ─────────────────────────────
if (file_exists($rutaCompleta)) {
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="comprobante_QR_' . $dni . '_' . $anio . '.png"');
    header('Content-Length: ' . filesize($rutaCompleta));
    readfile($rutaCompleta);
    exit();
} else {
    die("Error al generar el archivo QR.");
}
?>
