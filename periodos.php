<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['dnibedel'])) {
    header("Location: index.php");
    exit();
}

$host = "localhost"; $user = "root"; $pass = ""; $db = "inscripcioncursado";
$conex = new mysqli($host, $user, $pass, $db);
$conex->set_charset("utf8mb4");

$msg = '';
$msgType = '';

// ─── Acciones POST ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // Nuevo período
    if ($accion === 'nuevo') {
        $fi   = $_POST['fecha_inicio'] ?? '';
        $ff   = $_POST['fecha_fin']    ?? '';
        $desc = trim($_POST['descripcion'] ?? '');
        if ($fi && $ff && $fi <= $ff) {
            // Desactivar todos los existentes primero
            $conex->query("UPDATE periodos_inscripcion SET estado='inactivo'");
            $s = $conex->prepare(
                "INSERT INTO periodos_inscripcion (fecha_inicio, fecha_fin, estado, descripcion)
                 VALUES (?, ?, 'activo', ?)"
            );
            $s->bind_param("sss", $fi, $ff, $desc);
            $s->execute();
            $msg = "✔ Período creado y activado. Las inscripciones estarán abiertas durante las fechas configuradas.";
            $msgType = "success";
        } else {
            $msg = "⚠ Revisá las fechas ingresadas (la fecha de fin debe ser posterior a la de inicio).";
            $msgType = "warning";
        }
    }

    // Activar/desactivar un período
    if ($accion === 'toggle') {
        $id         = (int)($_POST['id'] ?? 0);
        $nuevoEstado = $_POST['nuevo_estado'] ?? '';
        if ($id && in_array($nuevoEstado, ['activo','inactivo'])) {
            if ($nuevoEstado === 'activo') {
                // Solo uno activo a la vez
                $conex->query("UPDATE periodos_inscripcion SET estado='inactivo'");
            }
            $s = $conex->prepare("UPDATE periodos_inscripcion SET estado=? WHERE idPeriodo=?");
            $s->bind_param("si", $nuevoEstado, $id);
            $s->execute();
            $msg = $nuevoEstado === 'activo'
                ? "✔ Período activado. Las inscripciones estarán abiertas durante las fechas configuradas."
                : "⏸ Período desactivado. Las inscripciones están cerradas.";
            $msgType = $nuevoEstado === 'activo' ? "success" : "warning";
        }
    }

    // Eliminar período
    if ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $s = $conex->prepare("DELETE FROM periodos_inscripcion WHERE idPeriodo=?");
            $s->bind_param("i", $id);
            $s->execute();
            $msg = "🗑 Período eliminado.";
            $msgType = "danger";
        }
    }
}

// ─── Cargar períodos ─────────────────────────────────────────────────────────
$periodos = [];
$res = $conex->query("SELECT * FROM periodos_inscripcion ORDER BY idPeriodo DESC");
while ($f = $res->fetch_assoc()) $periodos[] = $f;

$fechaHoy = date('Y-m-d');
$conex->close();

include('primero.php');
include('header_pagina.php');
?>

<section>
<div class="container mt-4 mb-5">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h2 class="mb-0">📅 Períodos de Inscripción de Cursado</h2>
      <p class="text-muted mb-0">Activá o desactivá el período de inscripción al cursado.</p>
    </div>
    <a href="inicio_bedelia.php" class="btn btn-secondary">← Volver al Menú</a>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- ── Estado actual ──────────────────────────────────────────────────────── -->
  <?php
  $activo = null;
  foreach ($periodos as $p) {
      if ($p['estado'] === 'activo' && $p['fecha_inicio'] <= $fechaHoy && $p['fecha_fin'] >= $fechaHoy) {
          $activo = $p;
          break;
      }
  }
  ?>
  <div class="alert <?= $activo ? 'alert-success' : 'alert-danger' ?> d-flex align-items-center gap-3 mb-4">
    <span style="font-size:2rem;"><?= $activo ? '🟢' : '🔴' ?></span>
    <div>
      <strong><?= $activo ? 'Inscripciones ABIERTAS' : 'Inscripciones CERRADAS' ?></strong><br>
      <?php if ($activo): ?>
        Del <strong><?= date('d/m/Y', strtotime($activo['fecha_inicio'])) ?></strong>
        al <strong><?= date('d/m/Y', strtotime($activo['fecha_fin'])) ?></strong>
        <?php if ($activo['descripcion']): ?>
          — <?= htmlspecialchars($activo['descripcion']) ?>
        <?php endif; ?>
      <?php else: ?>
        No hay un período activo dentro de sus fechas. Las inscripciones están cerradas.
      <?php endif; ?>
    </div>
  </div>

  <div class="row g-4">

    <!-- ── Crear nuevo período ───────────────────────────────────────────────── -->
    <div class="col-12 col-lg-5">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-primary text-white fw-bold">
          ➕ Crear Nuevo Período
        </div>
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="accion" value="nuevo">
            <div class="mb-3">
              <label class="form-label fw-semibold">Fecha de Inicio</label>
              <input type="date" name="fecha_inicio" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Fecha de Fin</label>
              <input type="date" name="fecha_fin" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Descripción <span class="text-muted fw-normal">(opcional)</span></label>
              <input type="text" name="descripcion" class="form-control"
                     placeholder="Ej: Inscripciones 1° cuatrimestre 2026">
            </div>
            <div class="alert alert-info py-2 small mb-3">
              ℹ Al crear un período, se desactivará automáticamente el período anterior.
            </div>
            <button type="submit" class="btn btn-primary w-100">
              🗓 Crear y Activar Período
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- ── Listado de períodos ───────────────────────────────────────────────── -->
    <div class="col-12 col-lg-7">
      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white fw-bold">
          📋 Historial de Períodos
        </div>
        <div class="card-body p-0">
          <?php if (empty($periodos)): ?>
            <div class="text-center text-muted py-5">
              No hay períodos registrados todavía.
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-dark">
                  <tr>
                    <th>Período</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acción</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($periodos as $p): ?>
                    <?php
                    $esActivo = $p['estado'] === 'activo';
                    $estaAbierto = $esActivo && $p['fecha_inicio'] <= $fechaHoy && $p['fecha_fin'] >= $fechaHoy;
                    ?>
                    <tr class="<?= $estaAbierto ? 'table-success' : '' ?>">
                      <td>
                        <strong><?= date('d/m/Y', strtotime($p['fecha_inicio'])) ?></strong><br>
                        <small class="text-muted">al <?= date('d/m/Y', strtotime($p['fecha_fin'])) ?></small>
                      </td>
                      <td>
                        <small><?= htmlspecialchars($p['descripcion'] ?: '—') ?></small>
                      </td>
                      <td>
                        <?php if ($estaAbierto): ?>
                          <span class="badge bg-success">🟢 Abierto</span>
                        <?php elseif ($esActivo): ?>
                          <span class="badge bg-warning text-dark">Activo, fuera de fechas</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">⏸ Inactivo</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="d-flex flex-column gap-1">
                          <!-- Toggle estado -->
                          <form method="POST" class="d-inline">
                            <input type="hidden" name="accion" value="toggle">
                            <input type="hidden" name="id" value="<?= $p['idPeriodo'] ?>">
                            <input type="hidden" name="nuevo_estado" value="<?= $esActivo ? 'inactivo' : 'activo' ?>">
                            <button type="submit" class="btn btn-sm <?= $esActivo ? 'btn-warning' : 'btn-success' ?> w-100">
                              <?= $esActivo ? '⏸ Desactivar' : '▶ Activar' ?>
                            </button>
                          </form>
                          <!-- Eliminar -->
                          <form method="POST" class="d-inline"
                                onsubmit="return confirm('¿Eliminar este período?')">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= $p['idPeriodo'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                              🗑 Eliminar
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>
</section>

<?php include('footer.php'); ?>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
