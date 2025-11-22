<?php
require_once 'Model.php';

require_once 'Model.php';

class Ingreso extends Model {
    public function __construct() {
        parent::__construct('ingresos', false); // false = no usa borrado suave
    }

    public function getIngresosMes() {
        // PostgreSQL NO usa MONTH() ni YEAR(), sino EXTRACT()
        $sql = "SELECT SUM(monto) as total 
                FROM {$this->table}
                WHERE EXTRACT(MONTH FROM fecha_ingreso) = EXTRACT(MONTH FROM CURRENT_DATE)
                AND EXTRACT(YEAR FROM fecha_ingreso) = EXTRACT(YEAR FROM CURRENT_DATE)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getByDateRange($fechaInicio, $fechaFin) {
        $sql = "SELECT i.*, c.nombres as cliente_nombre, c.apellidos as cliente_apellidos,
                       c.razon_social as cliente_razon_social
                FROM {$this->table} i
                LEFT JOIN clientes c ON i.cliente_id = c.id
                WHERE i.fecha_ingreso BETWEEN ? AND ?
                ORDER BY i.fecha_ingreso DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll();
    }

    // Sobrescribir getAll para no usar estado
    public function getAll() {
        $sql = "SELECT i.*, c.nombres as cliente_nombre, c.apellidos as cliente_apellidos,
                       c.razon_social as cliente_razon_social
                FROM {$this->table} i
                LEFT JOIN clientes c ON i.cliente_id = c.id
                ORDER BY i.fecha_ingreso DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

?>