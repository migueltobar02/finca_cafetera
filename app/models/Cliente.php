<?php
require_once 'Model.php';

class Cliente extends Model {

    public function __construct() {
        parent::__construct('clientes');
    }

    /**
     * Clientes frecuentes (más compras y mayor monto)
     */
    public function getClientesFrecuentes() {
        $sql = "SELECT 
                    c.*, 
                    COUNT(v.id) AS total_compras, 
                    SUM(v.total_venta) AS monto_total
                FROM clientes c
                LEFT JOIN ventas v 
                    ON c.id = v.cliente_id 
                    AND v.estado = 'pagada'
                WHERE c.estado = 'activo'
                GROUP BY c.id
                HAVING COUNT(v.id) > 0
                ORDER BY monto_total DESC NULLS LAST
                LIMIT 10";

        // PostgreSQL no tiene problemas con LIMIT fijo
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Historial de compras de un cliente
     */
    public function getHistorialCompras($clienteId) {
        $sql = "SELECT v.*
                FROM ventas v
                WHERE v.cliente_id = $1
                AND v.estado = 'pagada'
                ORDER BY v.fecha_venta DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clienteId]);

        return $stmt->fetchAll();
    }
}
?>
