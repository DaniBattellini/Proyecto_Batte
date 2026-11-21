<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=inscripcion.csv');

// AJUSTA A TUS DATOS DE CONEXIÓN:
$host = "localhost";
$user = "root";
$password = "";
$dbname = "inscripcioncursado";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) { die("Fallo de conexión"); }

$correo = isset($_GET['correo']) ? $conn->real_escape_string($_GET['correo']) : '';
if (!$correo) { die("No se recibió correo"); }

$result = $conn->query("SELECT materia_numero, modalidad, fecha FROM inscripciones WHERE correo = '$correo' ORDER BY materia_numero");
if (!$result) { die("Error al buscar inscripciones"); }

$output = fopen('php://output', 'w');
fputcsv($output, ['Materia N°', 'Modalidad', 'Fecha']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [$row['materia_numero'], $row['modalidad'], $row['fecha']]);
}
fclose($output);
$conn->close();
?>
