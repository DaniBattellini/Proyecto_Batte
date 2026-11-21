<?php
function obtenerPeriodoInscripcionAbierto(mysqli $conexion): ?array
{
    $stmt = $conexion->prepare(
        "SELECT idPeriodo, fecha_inicio, fecha_fin, descripcion
         FROM periodos_inscripcion
         WHERE estado = 'activo'
           AND CURDATE() BETWEEN fecha_inicio AND fecha_fin
         ORDER BY idPeriodo DESC
         LIMIT 1"
    );
    $stmt->execute();
    $periodo = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $periodo ?: null;
}
?>
