<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// Control de sesión
if (!isset($_SESSION["dnialu"]) && !isset($_SESSION["dnibedel"])) {
    header("Location: index.php");
    exit();
} elseif (isset($_SESSION["dnialu"])) {
    header("Location: inicio_alumno.php");
    exit();
}

// Conexión a la base de datos
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

$sql = "SELECT id AS idalumno, dni, cuil, nombre, apellido, carrera 
        FROM alumnos 
        ORDER BY apellido, nombre";

$result = $conex->query($sql);

$filas = [];
$carreras_set = [];
if ($result && $result->num_rows > 0) {
    while ($f = $result->fetch_assoc()) {
        $filas[] = $f;
        if (!empty(trim($f['carrera']))) {
            $carreras_set[$f['carrera']] = true;
        }
    }
}
ksort($carreras_set);
$conex->close();

?>
<?php
include('primero.php');
include('header_pagina.php');
?>

<section class="pagina flex-grow-1">
    <div class="container text-center">
        <div class="text-center mt-5 mb-3">
            <h3>Listado de Alumnos Inscriptos</h3>
        </div>

        <div class="row mb-3">
            <div class="col-1"></div>
            <div class="col-3 text-end">
                <a class="btn btn-primary" href="bedelia_agregar_registro.php">+ Agregar</a>
                <a class="btn btn-secondary ms-2" href="inicio_bedelia.php">Volver a Inicio</a>
            </div>
        </div>

        <!-- Buscador y filtros -->
        <div class="row mb-3 g-2 align-items-end justify-content-center">
            <div class="col-12 col-md-5">
                <input type="text" id="inputBuscar" class="form-control" placeholder="🔍 Buscar por nombre, apellido, DNI...">
            </div>
            <div class="col-12 col-md-3">
                <select id="filtroCarrera" class="form-select">
                    <option value="">— Todas las carreras —</option>
                    <?php foreach (array_keys($carreras_set) as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button id="btnLimpiar" class="btn btn-outline-secondary">✕ Limpiar</button>
            </div>
        </div>
        <div id="contadorResultados" class="text-muted small mb-2">Mostrando <?= count($filas) ?> alumno(s)</div>

        <table class="table table-striped table-hover" id="tablaAlumnos">
            <thead class="titulo__tabla">
                <tr>
                    <th scope="col">DNI</th>
                    <th scope="col">CUIL</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Apellido</th>
                    <th scope="col">Carrera</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($filas)): ?>
                    <tr><td colspan="6">No hay alumnos registrados.</td></tr>
                <?php else: foreach ($filas as $fila): ?>
                    <tr
                        data-nombre="<?= mb_strtolower(htmlspecialchars($fila['nombre'])) ?>"
                        data-apellido="<?= mb_strtolower(htmlspecialchars($fila['apellido'])) ?>"
                        data-dni="<?= htmlspecialchars($fila['dni']) ?>"
                        data-cuil="<?= htmlspecialchars($fila['cuil']) ?>"
                        data-carrera="<?= htmlspecialchars($fila['carrera']) ?>"
                    >
                        <th scope="row"><?= htmlspecialchars($fila["dni"]) ?></th>
                        <td><?= htmlspecialchars($fila["cuil"]) ?></td>
                        <td><?= htmlspecialchars($fila["nombre"]) ?></td>
                        <td><?= htmlspecialchars($fila["apellido"]) ?></td>
                        <td><small><?= htmlspecialchars($fila["carrera"]) ?></small></td>
                        <td>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <a class="btn btn-info btn-sm" href="ver_inscripciones_alumno.php?id=<?= urlencode($fila['idalumno']) ?>">Ver Inscripciones</a>
                                <a class="btn btn-success btn-sm" href="bedelia_editar_registro.php?id=<?= urlencode($fila['idalumno']) ?>">Editar</a>
                                <a class="btn btn-danger btn-sm" href="bedelia_eliminar_registro.php?id=<?= urlencode($fila['idalumno']) ?>"
                                   onclick="return confirm('¿Estás seguro de eliminar este registro?');">Borrar</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include('footer.php'); ?>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const inputBuscar   = document.getElementById('inputBuscar');
    const filtroCarrera = document.getElementById('filtroCarrera');
    const btnLimpiar    = document.getElementById('btnLimpiar');
    const contador      = document.getElementById('contadorResultados');
    const filas         = document.querySelectorAll('#tablaAlumnos tbody tr[data-nombre]');

    function filtrar() {
        const buscar  = inputBuscar.value.toLowerCase().trim();
        const carrera = filtroCarrera.value.toLowerCase();
        let visibles  = 0;

        filas.forEach(tr => {
            const texto =
                (tr.dataset.nombre   || '') + ' ' +
                (tr.dataset.apellido || '') + ' ' +
                (tr.dataset.dni      || '') + ' ' +
                (tr.dataset.cuil     || '');

            const matchBuscar  = !buscar  || texto.includes(buscar);
            const matchCarrera = !carrera || (tr.dataset.carrera || '').toLowerCase() === carrera;

            if (matchBuscar && matchCarrera) {
                tr.style.display = '';
                visibles++;
            } else {
                tr.style.display = 'none';
            }
        });
        contador.textContent = 'Mostrando ' + visibles + ' alumno(s)';
    }

    inputBuscar.addEventListener('input', filtrar);
    filtroCarrera.addEventListener('change', filtrar);
    btnLimpiar.addEventListener('click', () => {
        inputBuscar.value   = '';
        filtroCarrera.value = '';
        filtrar();
    });
})();
</script>

</body>
</html>












