<?php
require_once 'Model.php';

class Venta extends Model {
    public function __construct() {
        parent::__construct('ventas', false); // false = no usa borrado suave
    }

    public function getVentasMes() {
        $sql = "SELECT 
                    SUM(kilos_vendidos) AS total_kilos, 
                    SUM(total_venta) AS total_ventas
                FROM {$this->table}
                WHERE EXTRACT(MONTH FROM fecha_venta) = EXTRACT(MONTH FROM CURRENT_DATE)
                AND EXTRACT(YEAR FROM fecha_venta) = EXTRACT(YEAR FROM CURRENT_DATE)
                AND estado = 'pagada'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getWithCliente() {
        $sql = "SELECT v.*, 
                       c.nombres AS cliente_nombres, 
                       c.apellidos AS cliente_apellidos,
                       c.razon_social AS cliente_razon_social
                FROM {$this->table} v
                JOIN clientes c ON v.cliente_id = c.id
                ORDER BY v.fecha_venta DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Top clientes con LIMIT seguro para PostgreSQL
    public function getTopClientes($limit = 5) {
        $limit = (int)$limit; // sanitizar

        $sql = "SELECT 
                    c.id, c.nombres, c.apellidos, c.razon_social,
                    COUNT(v.id) AS total_compras,
                    SUM(v.total_venta) AS total_compras_monto,
                    SUM(v.kilos_vendidos) AS total_kilos
                FROM ventas v
                JOIN clientes c ON v.cliente_id = c.id
                WHERE v.estado = 'pagada'
                GROUP BY c.id
                ORDER BY total_compras_monto DESC
                LIMIT $limit";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDistribucionVentas() {
        $sql = "SELECT 
                    calidad,
                    SUM(kilos_vendidos) AS total_kilos,
                    SUM(total_venta) AS total_ventas,
                    COUNT(*) AS cantidad_ventas
                FROM ventas
                WHERE EXTRACT(MONTH FROM fecha_venta) = EXTRACT(MONTH FROM CURRENT_DATE)
                AND EXTRACT(YEAR FROM fecha_venta) = EXTRACT(YEAR FROM CURRENT_DATE)
                AND estado = 'pagada'
                GROUP BY calidad";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Sobrescribir getAll
    public function getAll() {
        return $this->getWithCliente();
    }
}
?>
