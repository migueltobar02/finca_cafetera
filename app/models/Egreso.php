<?php
require_once 'Model.php';

class Egreso extends Model {
    public function __construct() {
        parent::__construct('egresos', false); // false = no usa borrado suave
    }

    public function getEgresosMes() {
        $sql = "SELECT SUM(monto) AS total
                FROM {$this->table}
                WHERE EXTRACT(MONTH FROM fecha_egreso) = EXTRACT(MONTH FROM CURRENT_DATE)
                AND EXTRACT(YEAR FROM fecha_egreso) = EXTRACT(YEAR FROM CURRENT_DATE)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getByTipo($tipo) {
        $sql = "SELECT e.*, p.razon_social AS proveedor_nombre
                FROM {$this->table} e
                LEFT JOIN proveedores p ON e.proveedor_id = p.id
                WHERE e.tipo = ?
                ORDER BY e.fecha_egreso DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tipo]);
        return $stmt->fetchAll();
    }

    // Sobrescribir getAll para no usar estado
    public function getAll() {
        $sql = "SELECT e.*, p.razon_social AS proveedor_nombre
                FROM {$this->table} e
                LEFT JOIN proveedores p ON e.proveedor_id = p.id
                ORDER BY e.fecha_egreso DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
