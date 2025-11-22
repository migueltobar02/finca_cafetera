<?php
require_once 'Model.php';

class Jornal extends Model {
    public function __construct() {
        parent::__construct('jornales', false); // false = no usa borrado suave
    }

    public function getJornalesEmpleado($empleadoId, $mes = null, $ano = null) {
        if (!$mes) $mes = date('m');
        if (!$ano) $ano = date('Y');

        $sql = "SELECT j.*, a.nombre AS actividad_nombre, e.nombres, e.apellidos
                FROM {$this->table} j
                JOIN actividades a ON j.actividad_id = a.id
                JOIN empleados e ON j.empleado_id = e.id
                WHERE j.empleado_id = ?
                AND EXTRACT(MONTH FROM j.fecha_jornal) = ?
                AND EXTRACT(YEAR FROM j.fecha_jornal) = ?
                ORDER BY j.fecha_jornal DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empleadoId, $mes, $ano]);
        return $stmt->fetchAll();
    }

    public function getTotalPagosMes() {
        $sql = "SELECT SUM(total_pago) AS total
                FROM {$this->table}
                WHERE EXTRACT(MONTH FROM fecha_jornal) = EXTRACT(MONTH FROM CURRENT_DATE)
                AND EXTRACT(YEAR FROM fecha_jornal) = EXTRACT(YEAR FROM CURRENT_DATE)
                AND estado = 'pagado'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    // Sobrescribir getAll
    public function getAll() {
        $sql = "SELECT j.*, a.nombre AS actividad_nombre, e.nombres, e.apellidos
                FROM {$this->table} j
                JOIN actividades a ON j.actividad_id = a.id
                JOIN empleados e ON j.empleado_id = e.id
                ORDER BY j.fecha_jornal DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
