<?php
session_start();

if (!isset($_SESSION['dnialu']) && !isset($_SESSION['dnibedel'])) {
    header("Location: index.php");
    exit();
}

// Determinar a qué perfil volver
$urlVolver = isset($_SESSION['dnialu']) ? 'perfil.php' : 'perfil_bedel.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cambiar Contraseña</title>
<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">

<style>
.alert-success{
    animation: desaparecer 5s forwards;
}

@keyframes desaparecer{
    0%{opacity:1;}
    80%{opacity:1;}
    100%{opacity:0;}
}
</style>

</head>
<body>

<div class="container mt-5">

    <h2 class="text-center mb-4">Cambiar Contraseña</h2>

    <?php if(isset($_GET['mensaje']) && $_GET['mensaje']=="ok"){ ?>
        <div class="alert alert-success text-center" id="mensajeExito">
            Contraseña modificada correctamente.
        </div>

        <script>
        setTimeout(function(){
            document.getElementById("mensajeExito").style.display="none";
        },5000);
        </script>

    <?php } ?>

    <?php if(isset($_GET['error'])){ ?>
        <div class="alert alert-danger text-center">
            <?php echo $_GET['error']; ?>
        </div>
    <?php } ?>

    <form action="guardar_password.php" method="POST">

        <div class="mb-3">
            <label>Contraseña Actual</label>
            <input type="password" name="clave_actual" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Nueva Contraseña</label>
            <input type="password" name="clave_nueva" class="form-control" minlength="8" required>
        </div>

        <div class="mb-3">
            <label>Confirmar Contraseña</label>
            <input type="password" name="confirmar_clave" class="form-control" minlength="8" required>
        </div>

        <button type="submit" class="btn btn-primary">
            Cambiar Contraseña
        </button>

    </form>

    <div class="mt-3 text-center">
        <a href="<?php echo $urlVolver; ?>" class="btn btn-secondary btn-sm">← Volver al Perfil</a>
    </div>

</div>

</body>
</html>