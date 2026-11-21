<?php
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$password = "";
$dbname = "inscripcioncursado";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $conn->connect_error]);
    exit;
}

$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
if (!$correo) {
    echo json_encode(['success' => false, 'message' => 'Correo obligatorio.']);
    exit;
}

// Suponemos tabla alumnos y tabla inscripcioncursado
$stmt = $conn->prepare("SELECT id FROM alumnos WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->bind_result($alumno_id);
if (!$stmt->fetch()) {
    $stmt->close();
    $stmt2 = $conn->prepare("INSERT INTO alumnos (correo) VALUES (?)");
    $stmt2->bind_param("s", $correo);
    $stmt2->execute();
    $alumno_id = $stmt2->insert_id;
    $stmt2->close();
} else {
    $stmt->close();
}

$materias = [];
for ($i = 1; $i <= 25; $i++) {
    if (isset($_POST["materia$i"]) && $_POST["materia$i"] !== "No me inscribo") {
        $materias[$i] = $_POST["materia$i"];
    }
}

if (count($materias) == 0) {
    echo json_encode(['success' => false, 'message' => 'Debes seleccionar al menos una materia.']);
    exit;
}

// Opcional: borra inscripciones viejas
$stmt = $conn->prepare("DELETE FROM inscripcioncursado WHERE alumno_id = ?");
$stmt->bind_param("i", $alumno_id);
$stmt->execute();
$stmt->close();

// Inserta inscrips nuevas
$stmt = $conn->prepare("INSERT INTO inscripcioncursado (alumno_id, materia, condicion_cursado) VALUES (?, ?, ?)");
foreach ($materias as $num => $modalidad) {
    $materia_nombre = "materia" . $num;
    $stmt->bind_param("iss", $alumno_id, $materia_nombre, $modalidad);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error insertando: ' . $stmt->error]);
        $stmt->close();
        exit;
    }
}
$stmt->close();
$conn->close();

echo json_encode(['success' => true]);
exit;
?>
