<?php
session_start();

if (!isset($_SESSION['dnialu'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/conexion.php';

$dni = $_SESSION['dnialu'];
$alumno = null;
$stmt = $conex->prepare("SELECT id, nombre, apellido FROM alumnos WHERE dni = ? LIMIT 1");
$stmt->bind_param("s", $dni);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();
$stmt->close();

$notas = [];
if ($alumno) {
    $alumnoId = (int)$alumno['id'];
    $stmt = $conex->prepare(
        "SELECT n.nota, n.actualizada_en, m.materia, m.fecha_examen, p.descripcion
         FROM notas_mesas n
         JOIN mesas_materias m ON m.idMesa = n.mesa_id
         JOIN periodos_mesas p ON p.idPeriodoMesa = m.periodo_id
         WHERE n.alumno_id = ?
         ORDER BY m.fecha_examen DESC, p.idPeriodoMesa DESC, m.materia"
    );
    $stmt->bind_param("i", $alumnoId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($fila = $result->fetch_assoc()) {
        $notas[] = $fila;
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
      <h2 class="mb-0">Mis Notas</h2>
      <?php if ($alumno): ?>
        <p class="text-muted mb-0"><?= htmlspecialchars($alumno['apellido'] . ', ' . $alumno['nombre']) ?> · DNI: <?= htmlspecialchars($dni) ?></p>
      <?php endif; ?>
    </div>
    <a href="inicio_alumno.php" class="btn btn-secondary">← Volver al Menú</a>
  </div>

  <?php if (!$alumno): ?>
    <div class="alert alert-danger" role="alert">
      No se encontró una ficha de alumno asociada a tu usuario. Contactá a Bedelía.
    </div>
  <?php elseif (empty($notas)): ?>
    <div class="alert alert-info" role="status">
      Todavía no hay notas de mesas cargadas para tu usuario.
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Convocatoria</th>
            <th>Materia</th>
            <th>Fecha de examen</th>
            <th>Nota</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($notas as $nota): ?>
            <tr>
              <td><?= htmlspecialchars($nota['descripcion']) ?></td>
              <td><?= htmlspecialchars($nota['materia']) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($nota['fecha_examen'])) ?></td>
              <td><strong><?= number_format((float)$nota['nota'], 2, ',', '.') ?></strong></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
</section>

<?php include('footer.php'); ?>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
