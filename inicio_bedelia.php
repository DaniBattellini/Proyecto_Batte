<?php
session_start();

if (isset($_SESSION["dnialu"])) {
  header("Location:inicio_alumno.php");
  exit();
}

include('primero.php');
include('header_pagina.php');
?>

<section>
  <div class="container mt-4 mb-1">
    <div class="row">
      <div class="col-12 col-md-6">
        <img class="foto_home" src="./imagenes/post.jpg" alt="escuela">
      </div>
      <div class="col-12 col-md-6">
        <h2 class="titulo__parrafo">Menú de Bedelia</h2>
        <h3>Sistema de Gestión de Alumnado</h3>
        <h3>Tecnicaturas Superiores</h3>

        <?php
        if (isset($_GET["mensaje"])) {
            if ($_GET["mensaje"] === "ok") {
                echo "<div class='alert alert-success mt-3'><strong>✔ Registro exitoso.</strong></div>";
            } else {
                echo "<div class='alert alert-danger mt-3'><strong>" . htmlspecialchars($_GET["mensaje"]) . "</strong></div>";
            }
        }
        ?>

        <!-- Opciones de perfil del bedel -->
        <div class="mt-3 d-flex gap-2 flex-wrap">
          <a href="perfil_bedel.php" class="btn btn-outline-success">
            👤 Mi Perfil
          </a>
          <a href="cambiar_password.php" class="btn btn-warning">
            🔑 Cambiar Contraseña
          </a>
        </div>

        <hr>

        <!-- Opciones de gestión -->
        <div class="mt-3 d-grid gap-2">
          <a href="periodos.php" class="btn btn-danger btn-lg">
            📅 Gestionar Período de Cursado
          </a>
          <a href="mesas_bedelia.php" class="btn btn-warning btn-lg">
            📝 Gestionar Inscripción a Mesas
          </a>
          <a href="notas_bedelia.php" class="btn btn-info btn-lg">
            🧾 Cargar Notas de Mesas
          </a>
          <a href="listado_alumno.php" class="btn btn-primary btn-lg">
            👥 Listado de Alumnos
          </a>
          <a href="bedelia_agregar_registro.php" class="btn btn-success btn-lg">
            ➕ Agregar Alumno
          </a>
          <a href="listado_InscriptosA.php" class="btn btn-info btn-lg">
            📋 Ver Todos los Inscriptos
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