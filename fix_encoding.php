<?php
/**
 * SCRIPT DE USO ÚNICO - Corrige nombres de carrera en la BD.
 * ¡ELIMINAR ESTE ARCHIVO DESPUÉS DE EJECUTARLO!
 */
$host = "localhost"; $user = "root"; $pass = ""; $db = "inscripcioncursado";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Error: " . $conn->connect_error);
$conn->set_charset("utf8mb4");
$conn->query("SET NAMES utf8mb4");

// 1. Agrandar la columna por si acaso (VARCHAR 100 y utf8mb4)
$conn->query("ALTER TABLE carrera MODIFY nombre_Carrera VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
$conn->query("ALTER TABLE alumnos MODIFY carrera VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

// 2. Ver qué hay actualmente en la tabla carrera
$log = [];
$carreras_actuales = [];
$res = $conn->query("SELECT idCarrera, nombre_Carrera FROM carrera");
if ($res) {
    while ($r = $res->fetch_assoc()) $carreras_actuales[] = $r;
}

// 3. Mapeo: detectar por palabra clave y actualizar con nombre correcto EXACTO
//    Ajustá los nombres de abajo si difieren de los reales
$nombres_correctos = [
    'gesti' => 'Tecnicatura Superior en Gestión de las Organizaciones',
    'soft'  => 'Tecnicatura Superior en Desarrollo de Software',
];

foreach ($carreras_actuales as $c) {
    $lower = mb_strtolower($c['nombre_Carrera'], 'UTF-8');
    $nuevo = null;
    foreach ($nombres_correctos as $clave => $nombre) {
        if (strpos($lower, $clave) !== false) { $nuevo = $nombre; break; }
    }
    if ($nuevo !== null) {
        $stmt = $conn->prepare("UPDATE carrera SET nombre_Carrera = ? WHERE idCarrera = ?");
        $stmt->bind_param("si", $nuevo, $c['idCarrera']);
        $ok = $stmt->execute();
        $stmt->close();
        $log[] = ['tabla'=>'carrera','id'=>$c['idCarrera'],'antes'=>$c['nombre_Carrera'],'despues'=>$nuevo,'ok'=>$ok];
    }
}

// 4. Corregir campo carrera en alumnos
$res2 = $conn->query("SELECT id, carrera FROM alumnos");
if ($res2) {
    while ($row = $res2->fetch_assoc()) {
        $lower = mb_strtolower($row['carrera'], 'UTF-8');
        $nuevo = null;
        foreach ($nombres_correctos as $clave => $nombre) {
            if (strpos($lower, $clave) !== false) { $nuevo = $nombre; break; }
        }
        if ($nuevo !== null && $nuevo !== $row['carrera']) {
            $stmt = $conn->prepare("UPDATE alumnos SET carrera = ? WHERE id = ?");
            $stmt->bind_param("si", $nuevo, $row['id']);
            $ok = $stmt->execute();
            $stmt->close();
            $log[] = ['tabla'=>'alumnos','id'=>$row['id'],'antes'=>$row['carrera'],'despues'=>$nuevo,'ok'=>$ok];
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Fix DB</title>
<style>
body{font-family:Arial,sans-serif;padding:20px}
table{border-collapse:collapse;width:100%;margin:14px 0}
td,th{border:1px solid #ccc;padding:8px;font-size:.95rem}th{background:#f0f0f0}
.ok{color:green;font-weight:bold}.err{color:red;font-weight:bold}
.warn{background:#fff3cd;border:1px solid #ffc107;padding:14px;border-radius:6px;margin-top:14px}
</style></head>
<body>
<h2>Estado actual de la tabla <code>carrera</code></h2>
<table>
<tr><th>ID</th><th>Nombre actual en BD</th><th>Largo (chars)</th></tr>
<?php foreach ($carreras_actuales as $c): ?>
<tr>
  <td><?= $c['idCarrera'] ?></td>
  <td><?= htmlspecialchars($c['nombre_Carrera']) ?></td>
  <td><?= mb_strlen($c['nombre_Carrera'], 'UTF-8') ?></td>
</tr>
<?php endforeach; ?>
</table>

<h2>Cambios aplicados</h2>
<?php if (empty($log)): ?>
<p class="ok">✅ No se necesitaron cambios (o ya estaban correctos).</p>
<?php else: ?>
<table>
<tr><th>Tabla</th><th>ID</th><th>Antes</th><th>Después</th><th>Estado</th></tr>
<?php foreach ($log as $r): ?>
<tr>
  <td><code><?= $r['tabla'] ?></code></td>
  <td><?= $r['id'] ?></td>
  <td style="color:red"><?= htmlspecialchars($r['antes']) ?></td>
  <td style="color:green"><?= htmlspecialchars($r['despues']) ?></td>
  <td><?= $r['ok'] ? '<span class="ok">✔</span>' : '<span class="err">✘ Error</span>' ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<div class="warn">
  ⚠️ <strong>Verificá</strong> que los nombres en la tabla de arriba sean correctos.<br>
  Si algún nombre quedó mal, avisame y lo corrijo.<br>
  Una vez todo OK, <strong>eliminá este archivo</strong> del servidor.
</div>
</body>
</html>
