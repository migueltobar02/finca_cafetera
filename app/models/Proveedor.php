<?php
require_once 'Model.php';

class Proveedor extends Model {
    public function __construct() {
        parent::__construct('proveedores');
    }

    public function getProveedoresActivos() {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} 
             WHERE estado = 'activo' 
             ORDER BY razon_social"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getHistorialCompras($proveedorId) {
        $sql = "SELECT e.*
                FROM egresos e
                WHERE e.proveedor_id = ?
                ORDER BY e.fecha_egreso DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$proveedorId]);
        return $stmt->fetchAll();
    }

    public function getTotalCompras($proveedorId) {
        $sql = "SELECT SUM(monto) AS total_compras
                FROM egresos
                WHERE proveedor_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$proveedorId]);
        $result = $stmt->fetch();
        return $result['total_compras'] ?? 0;
    }
}
?>
