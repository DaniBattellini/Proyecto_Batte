<?php
// Configuración de la conexión a la base de datos
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'inscripcioncursado';

// Conexión a la base de datos
$conn = new mysqli($host, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// --- REGISTRO DE INSCRIPCIÓN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni'];
    $cuil = $_POST['cuil'];
    $carrera = $_POST['carrera'];
    $materia = $_POST['materia'];
    $condicion = $_POST['condicion'];

    // Preparar y ejecutar la consulta SQL
    $sql = "INSERT INTO inscripciones (nombre, apellido, dni, cuil, carrera, materia, condicion) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $nombre, $apellido, $dni, $cuil, $carrera, $materia, $condicion);

    if ($stmt->execute()) {
        echo "<div style='color:green;'>Inscripción registrada con éxito.</div>";
    } else {
        echo "<div style='color:red;'>Error al registrar inscripción: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// --- MOSTRAR INSCRIPCIONES ---
$sql = "SELECT nombre, apellido, dni, cuil, carrera, materia, condicion FROM inscripciones";
$result = $conn->query($sql);

echo "<h2>Listado de Inscripciones</h2>";
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>DNI</th>
            <th>CUIL</th>
            <th>Carrera</th>
            <th>Materia</th>
            <th>Condición</th>
          </tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>".htmlspecialchars($row["nombre"])."</td>";
        echo "<td>".htmlspecialchars($row["apellido"])."</td>";
        echo "<td>".htmlspecialchars($row["dni"])."</td>";
        echo "<td>".htmlspecialchars($row["cuil"])."</td>";
        echo "<td>".htmlspecialchars($row["carrera"])."</td>";
        echo "<td>".htmlspecialchars($row["materia"])."</td>";
        echo "<td>".htmlspecialchars($row["condicion"])."</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No hay inscripciones registradas.";
}

$conn->close();
?>