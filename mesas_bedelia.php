<?php
session_start();

if (!isset($_SESSION['dnibedel'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/materias_carrera.php';
require_once __DIR__ . '/mesas_inscripcion.php';

$msg = '';
$msgType = '';
$periodoSeleccionadoId = (int)($_GET['periodo_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $descripcion = trim($_POST['descripcion'] ?? '');
        $fechaInicio = trim($_POST['fecha_inicio'] ?? '');
        $fechaFin = trim($_POST['fecha_fin'] ?? '');
        $fechaInicioValida = DateTime::createFromFormat('Y-m-d', $fechaInicio);
        $fechaFinValida = DateTime::createFromFormat('Y-m-d', $fechaFin);

        if (
            $descripcion === '' ||
            !$fechaInicioValida ||
            !$fechaFinValida ||
            $fechaInicioValida->format('Y-m-d') !== $fechaInicio ||
            $fechaFinValida->format('Y-m-d') !== $fechaFin ||
            $fechaInicio > $fechaFin
        ) {
            $msg = "Completá la convocatoria y verificá las fechas.";
            $msgType = "warning";
        } else {
            mysqli_begin_transaction($conex);
            $conex->query("UPDATE periodos_mesas SET estado='inactivo'");

            $stmt = $conex->prepare(
                "INSERT INTO periodos_mesas (descripcion, fecha_inicio, fecha_fin, estado)
                 VALUES (?, ?, ?, 'activo')"
            );
            $stmt->bind_param("sss", $descripcion, $fechaInicio, $fechaFin);
            $stmt->execute();
            $periodoSeleccionadoId = (int)$conex->insert_id;
            $stmt->close();

            $stmtMesa = $conex->prepare(
                "INSERT INTO mesas_materias (periodo_id, carrera_id, materia)
                 VALUES (?, ?, ?)"
            );
            foreach ([1, 2] as $carreraId) {
                foreach (obtenerMateriasPorCarrera($carreraId) as $materia) {
                    $stmtMesa->bind_param("iis", $periodoSeleccionadoId, $carreraId, $materia);
                    $stmtMesa->execute();
                }
            }
            $stmtMesa->close();
            mysqli_commit($conex);

            $msg = "Convocatoria creada. Cargá las fechas y horarios de examen por materia.";
            $msgType = "success";
        }
    } elseif ($accion === 'guardar_fechas') {
        $periodoSeleccionadoId = (int)($_POST['periodo_id'] ?? 0);
        $fechas = $_POST['fecha_examen'] ?? [];
        $stmt = $conex->prepare("SELECT idMesa FROM mesas_materias WHERE periodo_id = ?");
        $stmt->bind_param("i", $periodoSeleccionadoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $mesasPermitidas = [];
        while ($fila = $result->fetch_assoc()) {
            $mesasPermitidas[(int)$fila['idMesa']] = true;
        }
        $stmt->close();

        if (!$periodoSeleccionadoId || count($mesasPermitidas) === 0 || !is_array($fechas) || count($fechas) !== count($mesasPermitidas)) {
            $msg = "No se pudieron validar las materias de la convocatoria.";
            $msgType = "danger";
        } else {
            $fechasValidadas = [];
            foreach ($fechas as $mesaId => $fecha) {
                $mesaId = (int)$mesaId;
                if (!isset($mesasPermitidas[$mesaId]) || !is_string($fecha)) {
                    $fechasValidadas = [];
                    break;
                }

                if ($fecha === '') {
                    $fechasValidadas[$mesaId] = null;
                    continue;
                }

                $fechaObj = DateTime::createFromFormat('Y-m-d\TH:i', $fecha);
                if (!$fechaObj || $fechaObj->format('Y-m-d\TH:i') !== $fecha) {
                    $fechasValidadas = [];
                    break;
                }
                $fechasValidadas[$mesaId] = $fechaObj->format('Y-m-d H:i:s');
            }

            if (count($fechasValidadas) !== count($mesasPermitidas)) {
                $msg = "Una o más fechas u horarios no son válidos.";
                $msgType = "warning";
            } else {
                mysqli_begin_transaction($conex);
                $stmt = $conex->prepare(
                    "UPDATE mesas_materias
                     SET fecha_examen = ?
                     WHERE idMesa = ? AND periodo_id = ?"
                );
                foreach ($fechasValidadas as $mesaId => $fechaExamen) {
                    $stmt->bind_param("sii", $fechaExamen, $mesaId, $periodoSeleccionadoId);
                    $stmt->execute();
                }
                $stmt->close();
                mysqli_commit($conex);
                $msg = "Fechas y horarios de las mesas guardados.";
                $msgType = "success";
            }
        }
    } elseif ($accion === 'cambiar_estado') {
        $periodoSeleccionadoId = (int)($_POST['periodo_id'] ?? 0);
        $nuevoEstado = $_POST['estado'] ?? '';

        if ($periodoSeleccionadoId > 0 && in_array($nuevoEstado, ['activo', 'inactivo'], true)) {
            mysqli_begin_transaction($conex);
            if ($nuevoEstado === 'activo') {
                $conex->query("UPDATE periodos_mesas SET estado='inactivo'");
            }

            $stmt = $conex->prepare("UPDATE periodos_mesas SET estado = ? WHERE idPeriodoMesa = ?");
            $stmt->bind_param("si", $nuevoEstado, $periodoSeleccionadoId);
            $stmt->execute();
            $stmt->close();
            mysqli_commit($conex);

            $msg = $nuevoEstado === 'activo'
                ? "Convocatoria activada. La inscripción abrirá dentro de las fechas configuradas."
                : "Convocatoria desactivada. La inscripción está cerrada.";
            $msgType = $nuevoEstado === 'activo' ? "success" : "warning";
        }
    }
}

$periodos = [];
$result = $conex->query("SELECT * FROM periodos_mesas ORDER BY idPeriodoMesa DESC");
while ($fila = $result->fetch_assoc()) {
    $periodos[] = $fila;
}

$idsPeriodos = array_map(static function ($periodo) {
    return (int)$periodo['idPeriodoMesa'];
}, $periodos);

if (!in_array($periodoSeleccionadoId, $idsPeriodos, true)) {
    $periodoSeleccionadoId = 0;
    foreach ($periodos as $periodo) {
        if ($periodo['estado'] === 'activo') {
            $periodoSeleccionadoId = (int)$periodo['idPeriodoMesa'];
            break;
        }
    }
    if ($periodoSeleccionadoId === 0 && !empty($periodos)) {
        $periodoSeleccionadoId = (int)$periodos[0]['idPeriodoMesa'];
    }
}

$mesas = [];
if ($periodoSeleccionadoId > 0) {
    $stmt = $conex->prepare(
        "SELECT m.idMesa, m.carrera_id, m.materia, m.fecha_examen, c.nombre_Carrera
         FROM mesas_materias m
         JOIN carrera c ON c.idCarrera = m.carrera_id
         WHERE m.periodo_id = ?
         ORDER BY m.carrera_id, m.materia"
    );
    $stmt->bind_param("i", $periodoSeleccionadoId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($fila = $result->fetch_assoc()) {
        $mesas[] = $fila;
    }
    $stmt->close();
}

$periodoAbierto = obtenerPeriodoMesasAbierto($conex);
$conex->close();

include('primero.php');
include('header_pagina.php');
?>

<section>
<div class="container mt-4 mb-5">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h2 class="mb-0">📅 Inscripción a Mesas de Examen</h2>
      <p class="text-muted mb-0">Administrá la convocatoria y las fechas de examen de cada materia.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a href="notas_bedelia.php" class="btn btn-outline-info">Cargar notas</a>
      <a href="inicio_bedelia.php" class="btn btn-secondary">← Volver al Menú</a>
    </div>
  </div>

  <?php if ($msg !== ''): ?>
    <div class="alert alert-<?= htmlspecialchars($msgType) ?>" role="alert">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <div class="alert <?= $periodoAbierto ? 'alert-success' : 'alert-danger' ?>" role="status">
    <?= $periodoAbierto
        ? 'Inscripción a mesas abierta: ' . htmlspecialchars($periodoAbierto['descripcion']) . ' (hasta ' . date('d/m/Y', strtotime($periodoAbierto['fecha_fin'])) . ').'
        : 'Inscripción a mesas cerrada. No hay una convocatoria activa dentro de sus fechas.' ?>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white fw-bold">Crear convocatoria de mesas</div>
    <div class="card-body">
      <form method="POST" class="row g-3">
        <input type="hidden" name="accion" value="crear">
        <div class="col-12 col-md-4">
          <label for="descripcion" class="form-label">Convocatoria</label>
          <input type="text" id="descripcion" name="descripcion" class="form-control" maxlength="150"
                 placeholder="Ej.: Mesas noviembre-diciembre 2026" required>
        </div>
        <div class="col-6 col-md-3">
          <label for="fecha_inicio" class="form-label">Apertura de inscripción</label>
          <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" required>
        </div>
        <div class="col-6 col-md-3">
          <label for="fecha_fin" class="form-label">Cierre de inscripción</label>
          <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" required>
        </div>
        <div class="col-12 col-md-2 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100">Crear convocatoria</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white fw-bold">Convocatorias</div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>Convocatoria</th><th>Inscripción</th><th>Estado</th><th></th></tr>
        </thead>
        <tbody>
          <?php if (empty($periodos)): ?>
            <tr><td colspan="4" class="text-center text-muted">Todavía no hay convocatorias de mesas.</td></tr>
          <?php else: ?>
            <?php foreach ($periodos as $periodo): ?>
              <?php
              $estaAbierto = $periodo['estado'] === 'activo'
                  && $periodo['fecha_inicio'] <= date('Y-m-d')
                  && $periodo['fecha_fin'] >= date('Y-m-d');
              ?>
              <tr>
                <td><?= htmlspecialchars($periodo['descripcion']) ?></td>
                <td><?= date('d/m/Y', strtotime($periodo['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($periodo['fecha_fin'])) ?></td>
                <td>
                  <?php if ($estaAbierto): ?>
                    <span class="badge bg-success">Abierta</span>
                  <?php elseif ($periodo['estado'] === 'activo'): ?>
                    <span class="badge bg-warning text-dark">Activa, fuera de fechas</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Inactiva</span>
                  <?php endif; ?>
                </td>
                <td class="d-flex gap-2">
                  <a class="btn btn-sm btn-outline-primary"
                     href="mesas_bedelia.php?periodo_id=<?= (int)$periodo['idPeriodoMesa'] ?>">Editar mesas</a>
                  <form method="POST" class="m-0">
                    <input type="hidden" name="accion" value="cambiar_estado">
                    <input type="hidden" name="periodo_id" value="<?= (int)$periodo['idPeriodoMesa'] ?>">
                    <input type="hidden" name="estado" value="<?= $periodo['estado'] === 'activo' ? 'inactivo' : 'activo' ?>">
                    <button type="submit" class="btn btn-sm <?= $periodo['estado'] === 'activo' ? 'btn-warning' : 'btn-success' ?>">
                      <?= $periodo['estado'] === 'activo' ? 'Cerrar' : 'Activar' ?>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($periodoSeleccionadoId > 0): ?>
    <div class="card shadow-sm">
      <div class="card-header bg-secondary text-white fw-bold">Fechas y horarios de examen por materia</div>
      <div class="card-body">
        <?php if (empty($mesas)): ?>
          <p class="text-muted mb-0">Esta convocatoria no tiene materias asociadas.</p>
        <?php else: ?>
          <form method="POST">
            <input type="hidden" name="accion" value="guardar_fechas">
            <input type="hidden" name="periodo_id" value="<?= $periodoSeleccionadoId ?>">
            <div class="table-responsive">
              <table class="table table-striped align-middle">
                <thead><tr><th>Carrera</th><th>Materia</th><th>Fecha y hora de examen</th></tr></thead>
                <tbody>
                  <?php foreach ($mesas as $mesa): ?>
                    <tr>
                      <td><?= htmlspecialchars($mesa['nombre_Carrera']) ?></td>
                      <td><?= htmlspecialchars($mesa['materia']) ?></td>
                      <td>
                        <input type="datetime-local" class="form-control"
                               name="fecha_examen[<?= (int)$mesa['idMesa'] ?>]"
                               value="<?= $mesa['fecha_examen'] ? date('Y-m-d\TH:i', strtotime($mesa['fecha_examen'])) : '' ?>">
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <button type="submit" class="btn btn-primary">Guardar fechas y horarios</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
</section>

<?php include('footer.php'); ?>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
