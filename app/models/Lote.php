<?php
require_once 'Model.php';

class Lote extends Model {
    public function __construct() {
        parent::__construct('lotes');
    }

    public function getActivos() {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} 
             WHERE estado = 'activo' 
             ORDER BY nombre"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getLotesConCosechas() {
        $sql = "SELECT l.*,
                       COUNT(c.id) AS total_cosechas,
                       SUM(c.kilos_cosechados) AS total_kilos_cosechados,
                       AVG(c.rendimiento) AS rendimiento_promedio
                FROM lotes l
                LEFT JOIN cosechas c ON l.id = c.lote_id
                WHERE l.estado = 'activo'
                GROUP BY l.id
                ORDER BY l.nombre";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getHistorialCosechas($loteId) {
        $sql = "SELECT c.*
                FROM cosechas c
                WHERE c.lote_id = ?
                ORDER BY c.fecha_cosecha DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$loteId]);
        return $stmt->fetchAll();
    }
}
?>
