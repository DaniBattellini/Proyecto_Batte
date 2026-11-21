<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// Solo bedelia puede acceder
if (!isset($_SESSION["dnibedel"])) {
    header("Location: index.php");
    exit();
}

$host = "localhost"; $user = "root"; $pass = ""; $db = "inscripcioncursado";
$conex = new mysqli($host, $user, $pass, $db);
if ($conex->connect_error) die("Error de conexión: " . $conex->connect_error);
$conex->set_charset("utf8mb4");
$conex->query("SET NAMES 'utf8mb4'");

$sql = "SELECT
            a.id AS idalumno,
            a.nombre,
            a.apellido,
            a.dni,
            a.cuil,
            a.carrera,
            i.materia,
            i.condicion_cursado,
            i.fecha_inscripcion,
            YEAR(i.fecha_inscripcion) AS anio
        FROM alumnos a
        INNER JOIN inscripciones i ON a.id = i.alumno_id
        ORDER BY YEAR(i.fecha_inscripcion) DESC, a.apellido, a.nombre, i.materia";

$result = $conex->query($sql);

// Recopilar datos y sets únicos de carrera/condición/año para los filtros
$filas = [];
$carreras_set   = [];
$condiciones_set = [];
$anios_set      = [];
$anioActual     = (int)date('Y');

if ($result && $result->num_rows > 0) {
    while ($f = $result->fetch_assoc()) {
        $filas[] = $f;
        if (!empty(trim($f['carrera'])))         $carreras_set[$f['carrera']] = true;
        if (!empty(trim($f['condicion_cursado']))) $condiciones_set[$f['condicion_cursado']] = true;
        if (!empty($f['anio']))                  $anios_set[$f['anio']] = true;
    }
}
ksort($carreras_set);
ksort($condiciones_set);
krsort($anios_set);

$conex->close();
?>
<?php
include('primero.php');
include('header_pagina.php');
?>

<section class="pagina flex-grow-1">
<div class="container-fluid px-4 py-3">
    <div>
        <h2>Listado de Alumnos Inscriptos</h2>
        <p class="subtitulo">Total en BD: <?= count($filas) ?> inscripciones</p>
    </div>
    <a href="inicio_bedelia.php" class="btn btn-secondary btn-sm mt-1">← Volver</a>
</div>

<!-- Filtros -->
<div class="filtros-card">
    <div class="row g-3 align-items-end">
        <!-- Buscador global -->
        <div class="col-12 col-md-4">
            <label for="inputBuscar">🔍 Buscar (nombre, DNI, materia...)</label>
            <input type="text" id="inputBuscar" class="form-control" placeholder="Escribí para filtrar...">
        </div>
        <!-- Filtro año -->
        <div class="col-12 col-md-2">
            <label for="filtroAnio">Año</label>
            <select id="filtroAnio" class="form-select">
                <option value="">— Todos —</option>
                <?php foreach (array_keys($anios_set) as $a): ?>
                    <option value="<?= $a ?>" <?= $a == $anioActual ? 'selected' : '' ?>>
                        <?= $a ?><?= $a == $anioActual ? ' (actual)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Filtro carrera -->
        <div class="col-12 col-md-3">
            <label for="filtroCarrera">Carrera</label>
            <select id="filtroCarrera" class="form-select">
                <option value="">— Todas —</option>
                <?php foreach (array_keys($carreras_set) as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Filtro condición -->
        <div class="col-12 col-md-2">
            <label for="filtroCondicion">Condición</label>
            <select id="filtroCondicion" class="form-select">
                <option value="">— Todas —</option>
                <?php foreach (array_keys($condiciones_set) as $cond): ?>
                    <option value="<?= htmlspecialchars($cond) ?>"><?= htmlspecialchars($cond) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Limpiar -->
        <div class="col-12 col-md-1">
            <label>&nbsp;</label>
            <button id="btnLimpiar" class="btn btn-outline-secondary w-100">✕</button>
        </div>
    </div>
</div>

<div id="contadorResultados">Mostrando <?= count($filas) ?> resultado(s)</div>

<div class="table-container">
    <table class="table table-hover mb-0" id="tablaInscriptos">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>DNI</th>
                <th>CUIL</th>
                <th>Carrera</th>
                <th>Materia</th>
                <th>Condición</th>
                <th>Año</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($filas)): ?>
                <tr><td colspan="9" class="text-center py-4 text-muted">No hay inscripciones registradas.</td></tr>
            <?php else: foreach ($filas as $f): ?>
                <tr
                    data-nombre="<?= mb_strtolower(htmlspecialchars($f['nombre'])) ?>"
                    data-apellido="<?= mb_strtolower(htmlspecialchars($f['apellido'])) ?>"
                    data-dni="<?= htmlspecialchars($f['dni']) ?>"
                    data-cuil="<?= htmlspecialchars($f['cuil']) ?>"
                    data-carrera="<?= htmlspecialchars($f['carrera']) ?>"
                    data-materia="<?= mb_strtolower(htmlspecialchars($f['materia'])) ?>"
                    data-condicion="<?= mb_strtolower(htmlspecialchars($f['condicion_cursado'])) ?>"
                    data-anio="<?= $f['anio'] ?>"
                >
                    <td><?= htmlspecialchars($f['nombre']) ?></td>
                    <td><?= htmlspecialchars($f['apellido']) ?></td>
                    <td><?= htmlspecialchars($f['dni']) ?></td>
                    <td><?= htmlspecialchars($f['cuil']) ?></td>
                    <td><small><?= htmlspecialchars($f['carrera']) ?></small></td>
                    <td><?= htmlspecialchars($f['materia']) ?></td>
                    <td>
                        <?php
                        $cond = strtolower($f['condicion_cursado']);
                        $bg = match($cond) {
                            'presencial'    => 'bg-primary',
                            'semipresencial'=> 'bg-warning text-dark',
                            'libre'         => 'bg-secondary',
                            default         => 'bg-light text-dark border'
                        };
                        ?>
                        <span class="badge badge-condicion <?= $bg ?>"><?= htmlspecialchars($f['condicion_cursado']) ?></span>
                    </td>
                    <td><?= $f['anio'] ?></td>
                    <td><?= date('d/m/Y', strtotime($f['fecha_inscripcion'])) ?></td>
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
    const inputBuscar    = document.getElementById('inputBuscar');
    const filtroAnio     = document.getElementById('filtroAnio');
    const filtroCarrera  = document.getElementById('filtroCarrera');
    const filtroCondicion= document.getElementById('filtroCondicion');
    const btnLimpiar     = document.getElementById('btnLimpiar');
    const contador       = document.getElementById('contadorResultados');
    const msgSin         = document.getElementById('msgSinResultados');
    const filas          = document.querySelectorAll('#tablaInscriptos tbody tr[data-nombre]');

    function filtrar() {
        const buscar   = inputBuscar.value.toLowerCase().trim();
        const anio     = filtroAnio.value;
        const carrera  = filtroCarrera.value.toLowerCase();
        const condicion= filtroCondicion.value.toLowerCase();
        let visibles   = 0;

        filas.forEach(tr => {
            const textoFila =
                (tr.dataset.nombre   || '') + ' ' +
                (tr.dataset.apellido || '') + ' ' +
                (tr.dataset.dni      || '') + ' ' +
                (tr.dataset.cuil     || '') + ' ' +
                (tr.dataset.materia  || '');

            const matchBuscar   = !buscar   || textoFila.includes(buscar);
            const matchAnio     = !anio     || (tr.dataset.anio     || '') === anio;
            const matchCarrera  = !carrera  || (tr.dataset.carrera  || '').toLowerCase() === carrera;
            const matchCondicion= !condicion|| (tr.dataset.condicion|| '').toLowerCase() === condicion;

            if (matchBuscar && matchAnio && matchCarrera && matchCondicion) {
                tr.classList.remove('oculta');
                visibles++;
            } else {
                tr.classList.add('oculta');
            }
        });

        contador.textContent = 'Mostrando ' + visibles + ' resultado(s)';
        msgSin.style.display = visibles === 0 ? 'block' : 'none';
    }

    // Ejecutar filtro al cargar con el año por defecto ya seleccionado
    filtrar();

    inputBuscar.addEventListener('input', filtrar);
    filtroAnio.addEventListener('change', filtrar);
    filtroCarrera.addEventListener('change', filtrar);
    filtroCondicion.addEventListener('change', filtrar);

    btnLimpiar.addEventListener('click', () => {
        inputBuscar.value    = '';
        filtroAnio.value     = '';
        filtroCarrera.value  = '';
        filtroCondicion.value= '';
        filtrar();
    });
})();
</script>
</div>
</section>

<?php include('footer.php'); ?>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
