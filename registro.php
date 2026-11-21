<?php
session_start();
ob_start();
header('Content-Type: text/html; charset=utf-8');

// Conexión a la base de datos para obtener carreras
$host = "localhost";
$user = "root";
$pass = "";
$db = "inscripcioncursado";

$conex = new mysqli($host, $user, $pass, $db);
if ($conex->connect_error) {
    die("Error de conexión: " . $conex->connect_error);
}
$conex->set_charset("utf8mb4");
$conex->query("SET NAMES 'utf8mb4'");

// Configurar codificación UTF-8
$conex->set_charset("utf8mb4");

// Obtener carreras disponibles
$carreras = array();
$result = $conex->query("SELECT idCarrera, nombre_Carrera FROM carrera ORDER BY nombre_Carrera");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $carreras[] = $row;
    }
}
$conex->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRACTICA</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        .error-msg {
            color: #fff;
            background: #d32f2f;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 14px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include('header_home.php'); ?>

    <section>
        <div class="container mt-4 mb-4">
            <div class="row">
                <div class="col-12 col-md-6">
                    <img class="foto_home" src="./imagenes/escuela.jpg" alt="escuela">
                </div>
                <div class="col-12 col-md-6">
                    <div class="container mt-2 mb-5">
                        <div class="text-center mt-6 mb-2"><h2>Registro del Alumno</h2></div>	
                        <div class="text-secondary"><p><small>* Dato Obligatorio</small></p></div>
                        <!-- Mensaje de error JS -->
                        <div id="error-js" class="error-msg" style="display:none;"></div>
                        <form class="row g-3" action="insertar_datos.php" method="post" id="registroForm" autocomplete="off">
                            <div class="col-sm-6">
                                <label for="nombre" class="form-label">* Nombre</label>
                                <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Ingresa tu Nombre" required>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="apellido" class="form-label">* Apellido</label>
                                <input type="text" class="form-control" name="apellido" id="apellido" placeholder="Ingresa tu Apellido" required>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="dni" class="form-label">* DNI</label>
                                <input type="text" class="form-control" name="dni" id="dni" maxlength="8" placeholder="Ingresa DNI de 8 dígitos numéricos" required>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="cuil" class="form-label">* Cuil</label>
                                <input type="text" class="form-control" name="cuil" id="cuil" maxlength="11" placeholder="Ingresa CUIL de 11 dígitos numéricos" required>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="clave" class="form-label">* Clave</label>
                                <input type="password" class="form-control" name="clave" id="clave" placeholder="Ingresa una clave de 8 caracteres como mínimo" minlength="8" required>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="carrera" class="form-label">* Carrera</label>
                                <select class="form-control" name="carrera" id="carrera" required>
                                    <option value="">-- Selecciona una carrera --</option>
                                    <?php foreach ($carreras as $carrera): ?>
                                        <option value="<?= htmlspecialchars($carrera['nombre_Carrera']) ?>">
                                            <?= htmlspecialchars($carrera['nombre_Carrera']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary" name="btn_registrar" id="btn_registrar">Registrar</button>
                            </div>
                        </form>

                        <?php
                        // Uso de GET para mostrar Mensaje resultante 
                        if (isset($_GET["mensaje"])) {
                            if ($_GET["mensaje"] != "ok") {
                                echo "<div class='text-center mt-4 mb-5'><div class='alert alert-danger' role='alert'><strong>" . $_GET["mensaje"] . "</strong></div></div>";
                            } else {
                                header("refresh:3; url=index.php");
                                echo "<div class='text-center mt-4 mb-5'><div class='alert alert-success' role='alert'><strong>Inscripción exitosa</strong></div></div>";
                            }
                        }
                        ?> 
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include('footer.php'); ?>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación JS en tiempo real y al enviar
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById('registroForm');
            const dni = document.getElementById('dni');
            const cuil = document.getElementById('cuil');
            const errorDiv = document.getElementById('error-js');

            function showError(msg) {
                errorDiv.innerText = msg;
                errorDiv.style.display = "block";
            }
            function hideError() {
                errorDiv.style.display = "none";
            }

            // Validación en tiempo real
            dni.addEventListener('input', function() {
                if (dni.value.length > 8) dni.value = dni.value.slice(0,8);
            });
            cuil.addEventListener('input', function() {
                if (cuil.value.length > 11) cuil.value = cuil.value.slice(0,11);
            });

            form.addEventListener('submit', function(e) {
                hideError();
                if (!/^\d{8}$/.test(dni.value)) {
                    showError("Error de inscripción: El DNI debe tener exactamente 8 dígitos numéricos.");
                    dni.focus();
                    e.preventDefault();
                    return false;
                }
                if (!/^\d{11}$/.test(cuil.value)) {
                    showError("Error de inscripción: El CUIL debe tener exactamente 11 dígitos numéricos.");
                    cuil.focus();
                    e.preventDefault();
                    return false;
                }
                hideError();
            });
        });
    </script>
</body>
</html>
<?php
ob_end_flush();
?>






