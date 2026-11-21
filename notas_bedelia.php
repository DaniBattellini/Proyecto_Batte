<?php
session_start();

if (!isset($_SESSION['dnibedel'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/conexion.php';

$msg = '';
$msgType = '';
$periodoId = (int)($_GET['periodo_id'] ?? 0);
$mesaId = (int)($_GET['mesa_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar_notas') {
    $periodoId = (int)($_POST['periodo_id'] ?? 0);
    $mesaId = (int)($_POST['mesa_id'] ?? 0);
    $notas = $_POST['nota'] ?? null;

    $stmt = $conex->prepare(
        "SELECT i.alumno_id
         FROM inscripciones_mesas i
         JOIN mesas_materias m ON m.idMesa = i.mesa_id
         WHERE i.mesa_id = ? AND m.periodo_id = ? AND i.rinde = 1"
    );
    $stmt->bind_param("ii", $mesaId, $periodoId);
    $stmt->execute();
    $result = $stmt->get_result();
    $alumnosMesa = [];
    while ($fila = $result->fetch_assoc()) {
        $alumnosMesa[(int)$fila['alumno_id']] = true;
    }
    $stmt->close();

    if (!$mesaId || !$periodoId || empty($alumnosMesa) || !is_array($notas) || count($notas) !== count($alumnosMesa)) {
        $msg = "No se pudieron validar los alumnos inscriptos a esta mesa.";
        $msgType = "danger";
    } else {
        $notasValidadas = [];
        foreach ($notas as $alumnoId => $nota) {
            $alumnoId = (int)$alumnoId;
            $nota = trim((string)$nota);
            if (!isset($alumnosMesa[$alumnoId])) {
                $notasValidadas = [];
                break;
            }

            if ($nota === '') {
                $notasValidadas[$alumnoId] = null;
                continue;
            }

            $nota = str_replace(',', '.', $nota);
            if (!preg_match('/^(?:10(?:\.0{1,2})?|[1-9](?:\.\d{1,2})?)$/', $nota)) {
                $notasValidadas = [];
                break;
            }
            $notasValidadas[$alumnoId] = $nota;
        }

        if (count($notasValidadas) !== count($alumnosMesa)) {
            $msg = "Las notas deben estar entre 1 y 10, con hasta dos decimales.";
            $msgType = "warning";
        } else {
            mysqli_begin_transaction($conex);
            $stmtGuardar = $conex->prepare(
                "INSERT INTO notas_mesas (mesa_id, alumno_id, nota)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE nota = VALUES(nota), actualizada_en = CURRENT_TIMESTAMP"
            );
            $stmtBorrar = $conex->prepare(
                "DELETE FROM notas_mesas WHERE mesa_id = ? AND alumno_id = ?"
            );

            foreach ($notasValidadas as $alumnoId => $nota) {
                if ($nota === null) {
                    $stmtBorrar->bind_param("ii", $mesaId, $alumnoId);
                    $stmtBorrar->execute();
                } else {
                    $stmtGuardar->bind_param("iid", $mesaId, $alumnoId, $nota);
                    $stmtGuardar->execute();
                }
            }

            $stmtGuardar->close();
            $stmtBorrar->close();
            mysqli_commit($conex);
            $msg = "Las notas se guardaron correctamente.";
            $msgType = "success";
        }
    }
}

$periodos = [];
$result = $conex->query("SELECT idPeriodoMesa, descripcion, fecha_inicio, fecha_fin FROM periodos_mesas ORDER BY idPeriodoMesa DESC");
while ($fila = $result->fetch_assoc()) {
    $periodos[] = $fila;
}

$periodoIds = array_map(static function ($periodo) {
    return (int)$periodo['idPeriodoMesa'];
}, $periodos);
if (!in_array($periodoId, $periodoIds, true)) {
    $periodoId = !empty($periodos) ? (int)$periodos[0]['idPeriodoMesa'] : 0;
}

$mesas = [];
if ($periodoId > 0) {
    $stmt = $conex->prepare(
        "SELECT m.idMesa, m.materia, m.fecha_examen, c.nombre_Carrera,
                COUNT(i.alumno_id) AS cantidad_inscriptos
         FROM mesas_materias m
         JOIN carrera c ON c.idCarrera = m.carrera_id
         LEFT JOIN inscripciones_mesas i
           ON i.mesa_id = m.idMesa AND i.rinde = 1
         WHERE m.periodo_id = ? AND m.fecha_examen IS NOT NULL
         GROUP BY m.idMesa, m.materia, m.fecha_examen, c.nombre_Carrera
         ORDER BY m.fecha_examen, c.nombre_Carrera, m.materia"
    );
    $stmt->bind_param("i", $periodoId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($fila = $result->fetch_assoc()) {
        $mesas[] = $fila;
    }
    $stmt->close();
}

$mesaSeleccionada = null;
$alumnos = [];
foreach ($mesas as $mesa) {
    if ((int)$mesa['idMesa'] === $mesaId) {
        $mesaSeleccionada = $mesa;
        break;
    }
}

if ($mesaSeleccionada) {
    $stmt = $conex->prepare(
        "SELECT a.id, a.nombre, a.apellido, a.dni, n.nota
         FROM inscripciones_mesas i
         JOIN alumnos a ON a.id = i.alumno_id
         LEFT JOIN notas_mesas n ON n.mesa_id = i.mesa_id AND n.alumno_id = i.alumno_id
         WHERE i.mesa_id = ? AND i.rinde = 1
         ORDER BY a.apellido, a.nombre"
    );
    $stmt->bind_param("i", $mesaId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($fila = $result->fetch_assoc()) {
        $alumnos[] = $fila;
    }
    $stmt->close();
}

$conex->close();
include('primero.php');
include('header_pagina.php');
?>

<section>
<div class="container mt-4 mb-5">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h2 class="mb-0">Carga de Notas de Mesas</h2>
      <p class="text-muted mb-0">Las notas se guardan por alumno, materia y convocatoria.</p>
    </div>
    <a href="inicio_bedelia.php" class="btn btn-secondary">← Volver al Menú</a>
  </div>

  <?php if ($msg !== ''): ?>
    <div class="alert alert-<?= htmlspecialchars($msgType) ?>" role="alert">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <form method="GET" class="row g-2 align-items-end mb-4">
    <div class="col-12 col-md-8">
      <label for="periodo_id" class="form-label">Convocatoria</label>
      <select id="periodo_id" name="periodo_id" class="form-select" required>
        <?php foreach ($periodos as $periodo): ?>
          <option value="<?= (int)$periodo['idPeriodoMesa'] ?>" <?= $periodoId === (int)$periodo['idPeriodoMesa'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($periodo['descripcion']) ?>
            (<?= date('d/m/Y', strtotime($periodo['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($periodo['fecha_fin'])) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12 col-md-4">
      <button type="submit" class="btn btn-outline-primary">Ver materias</button>
    </div>
  </form>

  <?php if (empty($periodos)): ?>
    <div class="alert alert-info">Todavía no hay convocatorias de mesas.</div>
  <?php elseif ($mesaSeleccionada): ?>
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <strong><?= htmlspecialchars($mesaSeleccionada['materia']) ?></strong>
        — <?= htmlspecialchars($mesaSeleccionada['nombre_Carrera']) ?>
        — <?= date('d/m/Y H:i', strtotime($mesaSeleccionada['fecha_examen'])) ?>
      </div>
      <div class="card-body">
        <a class="btn btn-sm btn-outline-secondary mb-3" href="notas_bedelia.php?periodo_id=<?= $periodoId ?>">
          ← Volver a materias
        </a>
        <?php if (empty($alumnos)): ?>
          <p class="text-muted mb-0">No hay alumnos anotados para rendir esta mesa.</p>
        <?php else: ?>
          <form method="POST">
            <input type="hidden" name="accion" value="guardar_notas">
            <input type="hidden" name="periodo_id" value="<?= $periodoId ?>">
            <input type="hidden" name="mesa_id" value="<?= $mesaId ?>">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-dark">
                  <tr><th>Alumno</th><th>DNI</th><th>Nota (1 a 10)</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($alumnos as $alumno): ?>
                    <tr>
                      <td><?= htmlspecialchars($alumno['apellido'] . ', ' . $alumno['nombre']) ?></td>
                      <td><?= htmlspecialchars($alumno['dni']) ?></td>
                      <td>
                        <input type="number" class="form-control" name="nota[<?= (int)$alumno['id'] ?>]"
                               min="1" max="10" step="0.01" value="<?= $alumno['nota'] !== null ? htmlspecialchars((string)$alumno['nota']) : '' ?>">
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <p class="form-text">Dejá el campo vacío si todavía no hay una nota cargada. Si borrás una nota existente, se eliminará al guardar.</p>
            <button type="submit" class="btn btn-primary">Guardar notas</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  <?php else: ?>
    <?php if (empty($mesas)): ?>
      <div class="alert alert-info">No hay fechas de examen cargadas para esta convocatoria.</div>
    <?php else: ?>
      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white fw-bold">Materias y alumnos anotados para rendir</div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr><th>Materia</th><th>Carrera</th><th>Fecha y hora</th><th>Anotados</th><th></th></tr>
            </thead>
            <tbody>
              <?php foreach ($mesas as $mesa): ?>
                <tr>
                  <td><?= htmlspecialchars($mesa['materia']) ?></td>
                  <td><?= htmlspecialchars($mesa['nombre_Carrera']) ?></td>
                  <td><?= date('d/m/Y H:i', strtotime($mesa['fecha_examen'])) ?></td>
                  <td><?= (int)$mesa['cantidad_inscriptos'] ?></td>
                  <td>
                    <a class="btn btn-sm btn-primary"
                       href="notas_bedelia.php?periodo_id=<?= $periodoId ?>&mesa_id=<?= (int)$mesa['idMesa'] ?>">
                      Cargar notas
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
</section>

<?php include('footer.php'); ?>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
