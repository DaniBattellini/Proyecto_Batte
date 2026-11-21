<?php
session_start();

// Solo bedelia puede acceder
if (!isset($_SESSION["dnibedel"])) {
    header("Location: index.php");
    exit();
}

// Conexión
$host = "localhost";
$user = "root";
$pass = "";
$db   = "inscripcioncursado";

$conex = new mysqli($host, $user, $pass, $db);
if ($conex->connect_error) {
    die("Error de conexión: " . $conex->connect_error);
}
$conex->set_charset("utf8mb4");

$alumno      = null;
$inscripciones = [];
$id_alumno   = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_alumno > 0) {
    // Datos del alumno
    $stmtA = $conex->prepare("SELECT id, nombre, apellido, dni, cuil, carrera FROM alumnos WHERE id = ? LIMIT 1");
    $stmtA->bind_param("i", $id_alumno);
    $stmtA->execute();
    $resA = $stmtA->get_result();
    $alumno = $resA->fetch_assoc();
    $stmtA->close();

    if ($alumno) {
        // Inscripciones del alumno
        $stmtI = $conex->prepare(
            "SELECT materia, condicion_cursado, fecha_inscripcion
             FROM inscripciones
             WHERE alumno_id = ?
             ORDER BY fecha_inscripcion DESC"
        );
        $stmtI->bind_param("i", $id_alumno);
        $stmtI->execute();
        $resI = $stmtI->get_result();
        while ($row = $resI->fetch_assoc()) {
            $inscripciones[] = $row;
        }
        $stmtI->close();
    }
}

include('primero.php');
include('header_pagina.php');
?>

<section>
  <div class="container mt-4 mb-5">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10">

        <!-- Encabezado -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
          <h2 class="mb-0">Inscripciones del Alumno</h2>
          <div class="d-flex gap-2">
            <a href="listado_alumno.php" class="btn btn-secondary">&larr; Volver al Listado</a>
            <a href="inicio_bedelia.php" class="btn btn-outline-secondary">Inicio Bedelia</a>
          </div>
        </div>

        <?php if ($id_alumno <= 0 || !$alumno): ?>
        <!-- Sin alumno seleccionado -->
        <div class="alert alert-warning">
          <strong>No se seleccionó un alumno válido.</strong><br>
          Volvé al <a href="listado_alumno.php">Listado de Alumnos</a> y hacé clic en "Ver Inscripciones".
        </div>

        <?php else: ?>
        <!-- Datos del alumno -->
        <div class="card mb-4 shadow-sm border-primary">
          <div class="card-header bg-primary text-white">
            <strong>Datos del Alumno</strong>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-sm-6">
                <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($alumno['apellido']) ?>, <?= htmlspecialchars($alumno['nombre']) ?></p>
                <p class="mb-1"><strong>DNI:</strong> <?= htmlspecialchars($alumno['dni']) ?></p>
              </div>
              <div class="col-sm-6">
                <p class="mb-1"><strong>CUIL:</strong> <?= htmlspecialchars($alumno['cuil']) ?></p>
                <p class="mb-1"><strong>Carrera:</strong> <?= htmlspecialchars($alumno['carrera']) ?></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabla de inscripciones -->
        <?php if (count($inscripciones) > 0): ?>
        <div class="card shadow-sm">
          <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <strong>Materias Inscriptas</strong>
            <span class="badge bg-light text-dark">Total: <?= count($inscripciones) ?></span>
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
              <thead class="table-dark">
                <tr>
                  <th>#</th>
                  <th>Materia</th>
                  <th>Condición</th>
                  <th>Fecha de Inscripción</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($inscripciones as $i => $insc): ?>
                <tr>
                  <td><?= $i + 1 ?></td>
                  <td><?= htmlspecialchars($insc['materia']) ?></td>
                  <td>
                    <?php
                    $cond = strtolower($insc['condicion_cursado']);
                    if ($cond === 'presencial') $badge = 'bg-success';
                    elseif ($cond === 'semipresencial') $badge = 'bg-warning text-dark';
                    elseif ($cond === 'libre') $badge = 'bg-secondary';
                    else $badge = 'bg-info text-dark';
                    ?>
                    <span class="badge <?= $badge ?>"><?= htmlspecialchars(ucfirst($insc['condicion_cursado'])) ?></span>
                  </td>
                  <td>
                    <?php
                    if (!empty($insc['fecha_inscripcion'])) {
                        $f = new DateTime($insc['fecha_inscripcion']);
                        echo $f->format('d/m/Y H:i');
                    } else {
                        echo '—';
                    }
                    ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <?php else: ?>
        <div class="alert alert-info">
          <strong>Este alumno no tiene inscripciones registradas.</strong>
        </div>
        <?php endif; ?>

        <?php endif; ?>

      </div>
    </div>
  </div>
</section>

<?php include('footer.php'); ?>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conex->close(); ?>
