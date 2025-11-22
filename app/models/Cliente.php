<?php
require_once 'Model.php';

class Cliente extends Model {

    public function __construct() {
        parent::__construct('clientes');
    }

    /**
     * Clientes frecuentes (más compras y mayor monto)
     */
    public function getClientesFrecuentes($limit = 10) {
        $limit = (int) $limit; // seguridad para PostgreSQL

        $sql = "
            SELECT 
                c.id,
                c.nombres,
                c.apellidos,
                c.razon_social,
                COUNT(v.id) AS total_compras, 
                COALESCE(SUM(v.total_venta),0) AS monto_total
            FROM clientes c
            LEFT JOIN ventas v 
                ON c.id = v.cliente_id 
                AND v.estado = 'pagada'
            WHERE c.estado = 'activo'
            GROUP BY c.id
            HAVING COUNT(v.id) > 0
            ORDER BY monto_total DESC NULLS LAST
            LIMIT $limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Historial de compras de un cliente
     */
    public function getHistorialCompras($clienteId) {
        $sql = "
            SELECT v.*
            FROM ventas v
            WHERE v.cliente_id = :clienteId
              AND v.estado = 'pagada'
            ORDER BY v.fecha_venta DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':clienteId' => $clienteId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Top clientes (más ventas en monto)
     */
    public function getTopClientes($limit = 5)
    {
        $limit = (int) $limit; // seguridad para PostgreSQL

        $sql = "
            SELECT 
                c.id, 
                c.nombres AS nombre,
                c.apellidos,
                c.razon_social,
                COALESCE(COUNT(v.id), 0) AS total_ventas,
                COALESCE(SUM(v.total_venta), 0) AS total_monto
            FROM clientes c
            LEFT JOIN ventas v 
                ON c.id = v.cliente_id
            WHERE c.estado = 'activo'
            GROUP BY c.id
            ORDER BY total_monto DESC
            LIMIT $limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar cliente por ID
     */
    public function getClienteById($id) {
        $sql = "
            SELECT *
            FROM clientes
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Listar todos los clientes activos
     */
    public function getClientesActivos() {
        $sql = "
            SELECT id, nombres, apellidos, razon_social, email, telefono, municipio
            FROM clientes
            WHERE estado = 'activo'
            ORDER BY nombres ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
