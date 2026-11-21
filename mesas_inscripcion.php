<?php
function obtenerPeriodoMesasAbierto(mysqli $conexion): ?array
{
    $stmt = $conexion->prepare(
        "SELECT idPeriodoMesa, descripcion, fecha_inicio, fecha_fin
         FROM periodos_mesas
         WHERE estado = 'activo'
           AND CURDATE() BETWEEN fecha_inicio AND fecha_fin
         ORDER BY idPeriodoMesa DESC
         LIMIT 1"
    );
    $stmt->execute();
    $periodo = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $periodo ?: null;
}
?>
