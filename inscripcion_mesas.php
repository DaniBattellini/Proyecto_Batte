<?php
session_start();

if (!isset($_SESSION['dnialu'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/mesas_inscripcion.php';
require_once __DIR__ . '/materias_carrera.php';

$dni = $_SESSION['dnialu'];
$alumno = null;
$stmt = $conex->prepare("SELECT id, carrera FROM alumnos WHERE dni = ? LIMIT 1");
$stmt->bind_param("s", $dni);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();
$stmt->close();

$carreraId = $alumno ? obtenerIdCarreraAlumno($alumno['carrera'] ?? '') : null;
$periodo = obtenerPeriodoMesasAbierto($conex);
$mesas = [];

if ($alumno && $carreraId && $periodo) {
    $stmt = $conex->prepare(
        "SELECT m.idMesa, m.materia, m.fecha_examen, i.rinde
         FROM mesas_materias m
         LEFT JOIN inscripciones_mesas i
           ON i.mesa_id = m.idMesa AND i.alumno_id = ?
         WHERE m.periodo_id = ?
           AND m.carrera_id = ?
           AND m.fecha_examen IS NOT NULL
         ORDER BY m.materia"
    );
    $alumnoId = (int)$alumno['id'];
    $periodoId = (int)$periodo['idPeriodoMesa'];
    $stmt->bind_param("iii", $alumnoId, $periodoId, $carreraId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($fila = $result->fetch_assoc()) {
        $mesas[] = $fila;
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
      <h2 class="mb-0">Inscripción a Mesas de Examen</h2>
      <?php if ($alumno): ?>
        <p class="text-muted mb-0">Alumno: <?= htmlspecialchars($dni) ?> · <?= htmlspecialchars($alumno['carrera'] ?? '') ?></p>
      <?php endif; ?>
    </div>
    <a href="inicio_alumno.php" class="btn btn-secondary">← Volver al Menú</a>
  </div>

  <?php if (!$alumno): ?>
    <div class="alert alert-danger" role="alert">
      No se encontró una ficha de alumno asociada a tu usuario. Contactá a Bedelía.
    </div>
  <?php elseif (!$carreraId): ?>
    <div class="alert alert-warning" role="alert">
      No pudimos identificar tu carrera para mostrar las mesas. Contactá a Bedelía.
    </div>
  <?php elseif (!$periodo): ?>
    <div class="alert alert-danger" role="alert">
      La inscripción a mesas está cerrada. Consultá con Bedelía cuándo se abrirá la próxima convocatoria.
    </div>
  <?php elseif (empty($mesas)): ?>
    <div class="alert alert-info" role="status">
      La convocatoria “<?= htmlspecialchars($periodo['descripcion']) ?>” está abierta, pero Bedelía todavía no cargó fechas de examen para tu carrera.
    </div>
  <?php else: ?>
    <div class="alert alert-success" role="status">
      Convocatoria: <strong><?= htmlspecialchars($periodo['descripcion']) ?></strong>.
      Podés cambiar tu selección hasta el <?= date('d/m/Y', strtotime($periodo['fecha_fin'])) ?>.
    </div>

    <div id="mensajeMesas" class="alert d-none" role="status"></div>
    <form id="formInscripcionMesas">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>Materia</th>
              <th>Fecha y hora del examen</th>
              <th>¿Te inscribís?</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($mesas as $mesa): ?>
              <?php $rinde = $mesa['rinde'] === null ? null : (int)$mesa['rinde']; ?>
              <tr>
                <td><?= htmlspecialchars($mesa['materia']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($mesa['fecha_examen'])) ?></td>
                <td>
                  <div class="d-flex gap-3">
                    <label>
                      <input type="radio" name="rinde[<?= (int)$mesa['idMesa'] ?>]" value="1"
                             <?= $rinde === 1 ? 'checked' : '' ?> required>
                      Rindo
                    </label>
                    <label>
                      <input type="radio" name="rinde[<?= (int)$mesa['idMesa'] ?>]" value="0"
                             <?= $rinde === 0 ? 'checked' : '' ?> required>
                      No rindo
                    </label>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <button type="submit" class="btn btn-primary">Guardar inscripción a mesas</button>
    </form>
  <?php endif; ?>
</div>
</section>

<script>
document.getElementById('formInscripcionMesas')?.addEventListener('submit', async function (event) {
    event.preventDefault();
    const form = event.currentTarget;
    const boton = form.querySelector('button[type="submit"]');
    const mensaje = document.getElementById('mensajeMesas');
    boton.disabled = true;
    boton.textContent = 'Guardando...';

    try {
        const respuesta = await fetch('guardar_inscripcion_mesas.php', {
            method: 'POST',
            body: new FormData(form)
        });
        const datos = await respuesta.json();
        mensaje.textContent = datos.message || (datos.success ? 'Inscripción guardada.' : 'No se pudo guardar la inscripción.');
        mensaje.className = 'alert ' + (datos.success ? 'alert-success' : 'alert-danger');
    } catch (error) {
        mensaje.textContent = 'No se pudo conectar con el servidor.';
        mensaje.className = 'alert alert-danger';
    } finally {
        boton.disabled = false;
        boton.textContent = 'Guardar inscripción a mesas';
    }
});
</script>

<?php include('footer.php'); ?>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
