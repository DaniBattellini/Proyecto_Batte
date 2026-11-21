<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION["dnialu"])) {
    header("Location: index.php");
    exit();
}

include('primero.php');
include('header_pagina.php');
include('conexion.php');

// Obtener la carrera del alumno logueado
$carrera_alumno = "";
$dnialu = $_SESSION["dnialu"];
$stmtC = $conex->prepare("SELECT carrera FROM alumnos WHERE dni = ?");
if ($stmtC) {
    $stmtC->bind_param("s", $dnialu);
    $stmtC->execute();
    $stmtC->bind_result($carrera_alumno);
    $stmtC->fetch();
    $stmtC->close();
}

// Detectar qué tecnicatura corresponde
$carrera_lower = mb_strtolower($carrera_alumno, 'UTF-8');
$esSoft  = str_contains($carrera_lower, 'software') || str_contains($carrera_lower, 'soft');
$esGest  = str_contains($carrera_lower, 'gesti') || str_contains($carrera_lower, 'organiz');
?>

<section>
  <div class="container mt-4 mb-5">
    <div class="row">
      <div class="col-12 col-md-6">
        <img class="foto_home" src="./imagenes/alumno2.jpg" alt="escuela">
      </div>
      <div class="col-12 col-md-6">
        <h2 class="titulo__parrafo">Menú del Alumno</h2>
        <h3>Sistema de Gestión de Alumnado</h3>
        <h3>Tecnicaturas Superiores</h3>

        <!-- Opciones de perfil -->
        <div class="mt-3 d-flex gap-2 flex-wrap">
          <a href="perfil.php" class="btn btn-outline-primary">
            👤 Mi Perfil
          </a>
          <a href="cambiar_password.php" class="btn btn-warning">
            🔑 Cambiar Contraseña
          </a>
        </div>

        <hr>

        <!-- Inscripción al Cursado -->
        <div class="mt-3">
          <h4>Inscripciones de Cursado</h4>
          <?php if ($esSoft): ?>
            <a href="listado_materia2TSoft.php" class="btn btn-primary btn-lg btn-block mb-3">
              🖥️ Tecnicatura en Desarrollo de Software
            </a>
          <?php elseif ($esGest): ?>
            <a href="listado_materia2TGest.php" class="btn btn-primary btn-lg btn-block mb-3">
              📊 Tecnicatura en Gestión de las Organizaciones
            </a>
          <?php else: ?>
            <a href="listado_materia2TSoft.php" class="btn btn-primary btn-lg btn-block mb-3">
              🖥️ Tecnicatura en Desarrollo de Software
            </a>
            <a href="listado_materia2TGest.php" class="btn btn-primary btn-lg btn-block mb-3">
              📊 Tecnicatura en Gestión de las Organizaciones
            </a>
          <?php endif; ?>
        </div>

        <hr>

        <div class="mt-3">
          <h4>Inscripción a Mesas</h4>
          <p class="text-muted">Elegí en qué materias te vas a presentar a rendir.</p>
          <a href="inscripcion_mesas.php" class="btn btn-warning btn-lg btn-block mb-3">
            📝 Inscripción a Mesas de Examen
          </a>
        </div>

        <hr>

        <div class="mt-3">
          <h4>Mis Notas</h4>
          <p class="text-muted">Consultá las calificaciones cargadas para tus mesas.</p>
          <a href="mis_notas.php" class="btn btn-outline-info btn-lg btn-block mb-3">
            📊 Ver Mis Notas
          </a>
        </div>

        <hr>

        <!-- Mis inscripciones y QR -->
        <div class="d-grid gap-2">
          <a href="mis_inscripciones.php" class="btn btn-outline-success btn-lg">
            📋 Ver Mis Inscripciones
          </a>
          <a href="generar_qr.php" class="btn btn-outline-dark btn-lg" target="_blank">
            📲 Descargar QR de Inscripción
          </a>
        </div>

      </div>
    </div>
  </div>
</section>

<?php include('footer.php'); ?>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
