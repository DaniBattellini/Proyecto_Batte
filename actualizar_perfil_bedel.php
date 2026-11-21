<?php
session_start();

if (!isset($_SESSION['dnibedel'])) {
    header("Location: index.php");
    exit();
}

$conex = new mysqli("localhost", "root", "", "inscripcioncursado");
$conex->set_charset("utf8mb4");

$dni = $_SESSION['dnibedel'];

// Obtener el idUsuario correcto desde la tabla bedelia (evita duplicados en usuario)
$idUsuario = null;
$stmt = $conex->prepare("SELECT Usuario_idUsuario FROM bedelia WHERE dni = ? LIMIT 1");
$stmt->bind_param("s", $dni);
$stmt->execute();
$stmt->bind_result($idUsuario);
$stmt->fetch();
$stmt->close();

if (!$idUsuario) {
    header("Location: perfil_bedel.php?error=usuario_no_encontrado");
    exit();
}

$telefono = trim($_POST['telefono'] ?? '');
$email    = trim($_POST['email']    ?? '');

if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $directorio = "imagenes/perfiles/";
    if (!is_dir($directorio)) mkdir($directorio, 0777, true);

    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($ext, $allowedExts)) {
        $nombreArchivo = time() . "_bedel_" . $idUsuario . "." . $ext;
        $rutaFoto = $directorio . $nombreArchivo;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaFoto)) {
            $sql  = "UPDATE usuario SET telefono=?, email=?, foto_perfil=? WHERE idUsuario=?";
            $stmt = $conex->prepare($sql);
            $stmt->bind_param("sssi", $telefono, $email, $rutaFoto, $idUsuario);
        } else {
            $sql  = "UPDATE usuario SET telefono=?, email=? WHERE idUsuario=?";
            $stmt = $conex->prepare($sql);
            $stmt->bind_param("ssi", $telefono, $email, $idUsuario);
        }
    } else {
        $sql  = "UPDATE usuario SET telefono=?, email=? WHERE idUsuario=?";
        $stmt = $conex->prepare($sql);
        $stmt->bind_param("ssi", $telefono, $email, $idUsuario);
    }
} else {
    $sql  = "UPDATE usuario SET telefono=?, email=? WHERE idUsuario=?";
    $stmt = $conex->prepare($sql);
    $stmt->bind_param("ssi", $telefono, $email, $idUsuario);
}

$stmt->execute();
$conex->close();

header("Location: perfil_bedel.php?ok=1");
?>
