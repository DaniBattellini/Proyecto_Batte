<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION["dnibedel"])) {
    header("Location: index.php");
    exit();
}

$host = "localhost"; $user = "root"; $pass = ""; $db = "inscripcioncursado";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);
$conn->set_charset("utf8mb4");
$conn->query("SET NAMES 'utf8mb4'");

$sql = "SELECT a.id, a.dni, a.cuil, a.nombre, a.apellido, a.carrera
        FROM alumnos a
        LEFT JOIN inscripciones i ON a.id = i.alumno_id
        WHERE i.alumno_id IS NULL
        ORDER BY a.apellido, a.nombre";

$result = $conn->query($sql);

$filas = [];
$carreras_set = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $filas[] = $row;
        if (!empty(trim($row['carrera']))) {
            $carreras_set[$row['carrera']] = true;
        }
    }
}
ksort($carreras_set);
$conn->close();
?>
<?php
include('primero.php');
include('header_pagina.php');
?>

<section class="pagina flex-grow-1">
<div class="container-fluid px-4 py-3">

<div class="d-flex justify-content-between align-items-start mb-1">
    <div>
        <h2>⚠️ Alumnos sin Inscripciones a Materias</h2>
        <p class="subtitulo">Total en BD: <?= count($filas) ?> alumno(s) sin inscribirse</p>
    </div>
    <a href="inicio_bedelia.php" class="btn btn-secondary btn-sm mt-1">← Volver</a>
</div>

<!-- Filtros -->
<div class="filtros-card">
    <div class="row g-3 align-items-end">
        <div class="col-12 col-md-6">
            <label for="inputBuscar">🔍 Buscar (nombre, apellido, DNI, CUIL...)</label>
            <input type="text" id="inputBuscar" class="form-control" placeholder="Escribí para filtrar...">
        </div>
        <div class="col-12 col-md-4">
            <label for="filtroCarrera">Carrera</label>
            <select id="filtroCarrera" class="form-select">
                <option value="">— Todas —</option>
                <?php foreach (array_keys($carreras_set) as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-2">
            <label>&nbsp;</label>
            <button id="btnLimpiar" class="btn btn-outline-secondary w-100">✕ Limpiar</button>
        </div>
    </div>
</div>

<div id="contadorResultados">Mostrando <?= count($filas) ?> resultado(s)</div>

<div class="table-container">
    <table class="table table-hover mb-0" id="tablaAlumnos">
        <thead>
            <tr>
                <th>DNI</th>
                <th>CUIL</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Carrera</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($filas)): ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">✅ Todos los alumnos están inscriptos en alguna materia.</td></tr>
            <?php else: foreach ($filas as $row): ?>
                <tr
                    data-nombre="<?= mb_strtolower(htmlspecialchars($row['nombre'])) ?>"
                    data-apellido="<?= mb_strtolower(htmlspecialchars($row['apellido'])) ?>"
                    data-dni="<?= htmlspecialchars($row['dni']) ?>"
                    data-cuil="<?= htmlspecialchars($row['cuil']) ?>"
                    data-carrera="<?= htmlspecialchars($row['carrera']) ?>"
                >
                    <td><?= htmlspecialchars($row['dni']) ?></td>
                    <td><?= htmlspecialchars($row['cuil']) ?></td>
                    <td><?= htmlspecialchars($row['nombre']) ?></td>
                    <td><?= htmlspecialchars($row['apellido']) ?></td>
                    <td><small><?= htmlspecialchars($row['carrera']) ?></small></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
    <div id="msgSinResultados" class="text-center text-muted py-4" style="display:none;">
        😕 No se encontraron resultados para los filtros aplicados.
    </div>
</div>

<script>
(function () {
    const inputBuscar   = document.getElementById('inputBuscar');
    const filtroCarrera = document.getElementById('filtroCarrera');
    const btnLimpiar    = document.getElementById('btnLimpiar');
    const contador      = document.getElementById('contadorResultados');
    const msgSin        = document.getElementById('msgSinResultados');
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
                tr.classList.remove('oculta');
                visibles++;
            } else {
                tr.classList.add('oculta');
            }
        });

        contador.textContent = 'Mostrando ' + visibles + ' resultado(s)';
        msgSin.style.display = visibles === 0 ? 'block' : 'none';
    }

    inputBuscar.addEventListener('input', filtrar);
    filtroCarrera.addEventListener('change', filtrar);
    btnLimpiar.addEventListener('click', () => {
        inputBuscar.value   = '';
        filtroCarrera.value = '';
        filtrar();
    });
})();
</div>
</section>

<?php include('footer.php'); ?>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
