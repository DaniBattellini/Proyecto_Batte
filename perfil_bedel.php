<?php
session_start();

if (!isset($_SESSION['dnibedel'])) {
    header("Location: index.php");
    exit();
}

$host = "localhost"; $user = "root"; $pass = ""; $db = "inscripcioncursado";
$conex = new mysqli($host, $user, $pass, $db);
$conex->set_charset("utf8mb4");

$dni = $_SESSION['dnibedel'];

// Cargar datos del usuario bedel (busca por idUsuario desde bedelia para evitar duplicados)
$sql = "SELECT u.idUsuario, u.dni, u.telefono, u.email, u.foto_perfil,
               b.rol
        FROM bedelia b
        INNER JOIN usuario u ON u.idUsuario = b.Usuario_idUsuario
        WHERE b.dni = ?
        LIMIT 1";
$stmt = $conex->prepare($sql);
$stmt->bind_param("s", $dni);
$stmt->execute();
$res = $stmt->get_result();
$usuario = $res->fetch_assoc();
$stmt->close();
$conex->close();

include('primero.php');
include('header_pagina.php');
?>

<section>
<div class="container mt-4 mb-5">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-8">

      <!-- Encabezado con foto -->
      <div class="card shadow-sm mb-4">
        <div class="card-body d-flex align-items-center gap-4 flex-wrap">
          <div>
            <?php if (!empty($usuario['foto_perfil']) && file_exists($usuario['foto_perfil'])): ?>
              <img src="<?= htmlspecialchars($usuario['foto_perfil']) ?>"
                   alt="Foto de perfil"
                   class="rounded-circle border border-success"
                   style="width:90px;height:90px;object-fit:cover;">
            <?php else: ?>
              <div class="rounded-circle bg-success d-flex align-items-center justify-content-center"
                   style="width:90px;height:90px;">
                <span class="text-white fw-bold" style="font-size:2rem;">
                  👩‍💼
                </span>
              </div>
            <?php endif; ?>
          </div>
          <div>
            <h4 class="mb-0">Bedel — DNI: <?= htmlspecialchars($dni) ?></h4>
            <span class="badge bg-success mt-1"><?= htmlspecialchars(ucfirst($usuario['rol'] ?? 'bedel')) ?></span>
          </div>
          <div class="ms-auto">
            <a href="inicio_bedelia.php" class="btn btn-secondary btn-sm">← Volver al Menú</a>
          </div>
        </div>
      </div>

      <?php if (isset($_GET['ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          ✔ Perfil actualizado correctamente.
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          ❌ Error al actualizar el perfil: <?= htmlspecialchars($_GET['error']) ?>.
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- Formulario de datos personales bedel -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white fw-bold">
          ✏️ Modificar Datos Personales
        </div>
        <div class="card-body">
          <form action="actualizar_perfil_bedel.php" method="POST" enctype="multipart/form-data">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Teléfono</label>
                <input type="tel" name="telefono" class="form-control"
                       placeholder="Ej: 3814123456"
                       value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control"
                       placeholder="Ej: bedel@escuela.edu.ar"
                       value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Foto de Perfil
                  <span class="text-muted fw-normal">(opcional, PNG/JPG hasta 2MB)</span>
                </label>
                <input type="file" name="foto" class="form-control" accept="image/png,image/jpeg,image/jpg">
                <?php if (!empty($usuario['foto_perfil']) && file_exists($usuario['foto_perfil'])): ?>
                  <small class="text-muted">
                    Foto actual: <em><?= htmlspecialchars(basename($usuario['foto_perfil'])) ?></em>
                    (subir otra reemplazará la actual)
                  </small>
                <?php endif; ?>
              </div>
            </div>

            <div class="mt-4">
              <button type="submit" class="btn btn-success">
                💾 Guardar Cambios
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Card cambio de contraseña -->
      <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark fw-bold">
          🔑 Cambiar Contraseña
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">
            Podés cambiar tu contraseña de acceso al sistema en cualquier momento.
          </p>
          <a href="cambiar_password.php" class="btn btn-warning">
            🔒 Ir a Cambiar Contraseña
          </a>
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
