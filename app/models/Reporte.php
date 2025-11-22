<?php
require_once 'Model.php';

class Reporte extends Model {
    public function __construct() {
        parent::__construct(null); // No usa tabla
    }

    public function getReporteFinanciero($fechaInicio, $fechaFin) {

        $sql = "SELECT *
                FROM (
                    SELECT 
                        'ingresos' AS tipo,
                        fecha_ingreso AS fecha,
                        descripcion,
                        monto,
                        NULL AS proveedor
                    FROM ingresos
                    WHERE fecha_ingreso BETWEEN ? AND ?

                    UNION ALL

                    SELECT 
                        'egresos' AS tipo,
                        fecha_egreso AS fecha,
                        descripcion,
                        monto * -1 AS monto,
                        p.razon_social AS proveedor
                    FROM egresos e
                    LEFT JOIN proveedores p ON e.proveedor_id = p.id
                    WHERE fecha_egreso BETWEEN ? AND ?
                ) AS reporte
                ORDER BY fecha DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fechaInicio, $fechaFin, $fechaInicio, $fechaFin]);
        return $stmt->fetchAll();
    }

    public function getReporteProduccion($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    c.fecha_cosecha,
                    l.nombre AS lote,
                    l.variedad_cafe,
                    c.kilos_cosechados,
                    c.calidad,
                    c.rendimiento
                FROM cosechas c
                JOIN lotes l ON c.lote_id = l.id
                WHERE c.fecha_cosecha BETWEEN ? AND ?
                ORDER BY c.fecha_cosecha DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll();
    }
}
?>
