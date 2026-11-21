<?php
session_start();

if (!isset($_SESSION["dnialu"])) {
    header("Location: index.php");
    exit();
}

include('primero.php');
include('header_pagina.php');
include('conexion.php');

$dni_alumno = $_SESSION["dnialu"];
$anioActual = (int)date('Y');

// Datos del alumno
$stmtA = mysqli_prepare($conex,
    "SELECT a.id, a.nombre, a.apellido, a.carrera
     FROM alumnos a WHERE a.dni = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmtA, "s", $dni_alumno);
mysqli_stmt_execute($stmtA);
$alumno = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtA));
mysqli_stmt_close($stmtA);
$alumnoId = (int)($alumno['id'] ?? 0);

// Todas las inscripciones
$stmtI = mysqli_prepare($conex,
    "SELECT i.id, i.materia, i.condicion_cursado, i.fecha_inscripcion,
            YEAR(i.fecha_inscripcion) AS anio
     FROM inscripciones i
     WHERE i.alumno_id = ?
     ORDER BY i.fecha_inscripcion DESC"
);
mysqli_stmt_bind_param($stmtI, "i", $alumnoId);
mysqli_stmt_execute($stmtI);
$resI = mysqli_stmt_get_result($stmtI);

$inscripciones = [];
while ($f = mysqli_fetch_assoc($resI)) $inscripciones[] = $f;
mysqli_stmt_close($stmtI);

// Separar: año actual vs historial
$actuales  = array_filter($inscripciones, fn($f) => (int)$f['anio'] === $anioActual);
$historial = array_filter($inscripciones, fn($f) => (int)$f['anio'] !== $anioActual);

// Años únicos para el selector de historial
$aniosHistorial = array_unique(array_map(fn($f) => $f['anio'], $historial));
rsort($aniosHistorial);
?>

<section>
<div class="container mt-4 mb-5">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-10">

      <!-- Encabezado -->
      <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
          <h2 class="mb-0">📋 Mis Inscripciones</h2>
          <?php if ($alumnoId > 0): ?>
            <p class="text-muted mb-0">
              <strong><?= htmlspecialchars(($alumno['apellido'] ?? '') . ', ' . ($alumno['nombre'] ?? '')) ?></strong>
              &nbsp;|&nbsp; DNI: <?= htmlspecialchars($dni_alumno) ?>
              &nbsp;|&nbsp; <?= htmlspecialchars($alumno['carrera'] ?? '') ?>
            </p>
          <?php else: ?>
            <p class="text-warning mb-0">
              No se encontró una ficha de alumno asociada al DNI <?= htmlspecialchars($dni_alumno) ?>.
              Contactá a Bedelía para vincularla.
            </p>
          <?php endif; ?>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <?php if ($alumnoId > 0): ?>
          <a href="generar_qr.php" class="btn btn-success" target="_blank">
            📲 Descargar QR <?= $anioActual ?>
          </a>
          <?php endif; ?>
          <a href="inicio_alumno.php" class="btn btn-secondary">← Volver al Menú</a>
        </div>
      </div>

      <!-- ── Inscripciones AÑO ACTUAL ──────────────────────────────────────── -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
          <strong>📅 Inscripciones <?= $anioActual ?> — Año en Curso</strong>
          <span class="badge bg-light text-dark ms-2"><?= count($actuales) ?> materia(s)</span>
        </div>

        <?php if (count($actuales) > 0): ?>
        <!-- Buscador -->
        <div class="card-body border-bottom pb-3">
          <div class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
              <label class="form-label fw-semibold small mb-1">🔍 Buscar materia</label>
              <input type="text" id="buscarActual" class="form-control form-control-sm"
                     placeholder="Nombre de materia...">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label fw-semibold small mb-1">Condición</label>
              <select id="filtroCondActual" class="form-select form-select-sm">
                <option value="">— Todas —</option>
                <option value="presencial">Presencial</option>
                <option value="semipresencial">Semipresencial</option>
                <option value="libre">Libre</option>
              </select>
            </div>
            <div class="col-6 col-md-2">
              <label class="form-label fw-semibold small mb-1">&nbsp;</label>
              <button id="limpiarActual" class="btn btn-outline-secondary btn-sm w-100">✕ Limpiar</button>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover mb-0" id="tablaActual">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Materia</th>
                <th>Condición</th>
                <th>Fecha de Inscripción</th>
              </tr>
            </thead>
            <tbody>
              <?php $num = 1; foreach ($actuales as $insc): ?>
              <tr data-materia="<?= mb_strtolower(htmlspecialchars($insc['materia'])) ?>"
                  data-condicion="<?= strtolower($insc['condicion_cursado']) ?>">
                <td><?= $num++ ?></td>
                <td><?= htmlspecialchars($insc['materia']) ?></td>
                <td>
                  <?php
                  $cond = strtolower($insc['condicion_cursado']);
                  $badge = match($cond) {
                      'presencial'     => 'bg-success',
                      'semipresencial' => 'bg-warning text-dark',
                      'libre'          => 'bg-secondary',
                      default          => 'bg-info text-dark'
                  };
                  ?>
                  <span class="badge <?= $badge ?>">
                    <?= htmlspecialchars(ucfirst($insc['condicion_cursado'])) ?>
                  </span>
                </td>
                <td><?= date('d/m/Y H:i', strtotime($insc['fecha_inscripcion'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div id="sinResultadosActual" class="text-center text-muted py-3" style="display:none;">
            😕 No hay resultados para los filtros aplicados.
          </div>
        </div>

        <?php else: ?>
        <div class="card-body">
          <div class="alert alert-info mb-0">
            <strong>No tenés inscripciones en <?= $anioActual ?>.</strong><br>
            Ingresá a tu Tecnicatura para inscribirte a materias.
          </div>
          <a href="inicio_alumno.php" class="btn btn-primary mt-2">Ir a inscribirme</a>
        </div>
        <?php endif; ?>
      </div>

      <!-- ── Historial de Años Anteriores ──────────────────────────────────── -->
      <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
          <strong>🗂 Historial — Años Anteriores</strong>
          <span class="badge bg-light text-dark ms-2"><?= count($historial) ?> inscripción(es)</span>
        </div>

        <?php if (count($historial) > 0): ?>
        <!-- Filtro por año -->
        <div class="card-body border-bottom pb-3">
          <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
              <label class="form-label fw-semibold small mb-1">Año</label>
              <select id="filtroAnioHistorial" class="form-select form-select-sm">
                <option value="">— Todos los años —</option>
                <?php foreach ($aniosHistorial as $a): ?>
                  <option value="<?= $a ?>"><?= $a ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label fw-semibold small mb-1">🔍 Buscar</label>
              <input type="text" id="buscarHistorial" class="form-control form-control-sm"
                     placeholder="Nombre de materia...">
            </div>
            <div class="col-12 col-md-2">
              <label class="form-label fw-semibold small mb-1">&nbsp;</label>
              <button id="limpiarHistorial" class="btn btn-outline-secondary btn-sm w-100">✕ Limpiar</button>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover mb-0" id="tablaHistorial">
            <thead class="table-secondary">
              <tr>
                <th>#</th>
                <th>Materia</th>
                <th>Condición</th>
                <th>Año</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              <?php $num = 1; foreach ($historial as $insc): ?>
              <tr data-materia="<?= mb_strtolower(htmlspecialchars($insc['materia'])) ?>"
                  data-anio="<?= $insc['anio'] ?>">
                <td><?= $num++ ?></td>
                <td><?= htmlspecialchars($insc['materia']) ?></td>
                <td>
                  <?php
                  $cond  = strtolower($insc['condicion_cursado']);
                  $badge = match($cond) {
                      'presencial'     => 'bg-success',
                      'semipresencial' => 'bg-warning text-dark',
                      'libre'          => 'bg-secondary',
                      default          => 'bg-info text-dark'
                  };
                  ?>
                  <span class="badge <?= $badge ?>">
                    <?= htmlspecialchars(ucfirst($insc['condicion_cursado'])) ?>
                  </span>
                </td>
                <td><?= $insc['anio'] ?></td>
                <td><?= date('d/m/Y', strtotime($insc['fecha_inscripcion'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div id="sinResultadosHistorial" class="text-center text-muted py-3" style="display:none;">
            😕 No hay resultados para los filtros aplicados.
          </div>
        </div>

        <?php else: ?>
        <div class="card-body text-muted">
          No hay inscripciones de años anteriores.
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>
</section>

<script>
// ── Filtros tabla ACTUAL ─────────────────────────────────────────────────────
(function () {
    const input    = document.getElementById('buscarActual');
    const select   = document.getElementById('filtroCondActual');
    const limpiar  = document.getElementById('limpiarActual');
    const tabla    = document.getElementById('tablaActual');
    const sinRes   = document.getElementById('sinResultadosActual');

    if (!input) return;
    const filas = tabla ? tabla.querySelectorAll('tbody tr') : [];

    function filtrar() {
        const texto = (input.value || '').toLowerCase();
        const cond  = (select.value || '').toLowerCase();
        let vis = 0;
        filas.forEach(tr => {
            const m = tr.dataset.materia || '';
            const c = tr.dataset.condicion || '';
            const ok = (!texto || m.includes(texto)) && (!cond || c === cond);
            tr.style.display = ok ? '' : 'none';
            if (ok) vis++;
        });
        if (sinRes) sinRes.style.display = vis === 0 ? 'block' : 'none';
    }

    input.addEventListener('input', filtrar);
    select.addEventListener('change', filtrar);
    limpiar.addEventListener('click', () => { input.value=''; select.value=''; filtrar(); });
})();

// ── Filtros tabla HISTORIAL ──────────────────────────────────────────────────
(function () {
    const inputH   = document.getElementById('buscarHistorial');
    const selectA  = document.getElementById('filtroAnioHistorial');
    const limpiarH = document.getElementById('limpiarHistorial');
    const tablaH   = document.getElementById('tablaHistorial');
    const sinResH  = document.getElementById('sinResultadosHistorial');

    if (!inputH) return;
    const filasH = tablaH ? tablaH.querySelectorAll('tbody tr') : [];

    function filtrarH() {
        const texto = (inputH.value || '').toLowerCase();
        const anio  = (selectA.value || '');
        let vis = 0;
        filasH.forEach(tr => {
            const m = tr.dataset.materia || '';
            const a = tr.dataset.anio || '';
            const ok = (!texto || m.includes(texto)) && (!anio || a === anio);
            tr.style.display = ok ? '' : 'none';
            if (ok) vis++;
        });
        if (sinResH) sinResH.style.display = vis === 0 ? 'block' : 'none';
    }

    inputH.addEventListener('input', filtrarH);
    selectA.addEventListener('change', filtrarH);
    limpiarH.addEventListener('click', () => { inputH.value=''; selectA.value=''; filtrarH(); });
})();
</script>

<?php include('footer.php'); ?>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
