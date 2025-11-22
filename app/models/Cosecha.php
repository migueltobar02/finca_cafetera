<?php
require_once 'Model.php';

class Cosecha extends Model {
    public function __construct() {
        parent::__construct('cosechas', false); // false = no usa borrado suave
    }

    public function getCosechasMes() {
    $sql = "SELECT 
                SUM(kilos_cosechados) AS total_cosechado,
                AVG(rendimiento) AS rendimiento_promedio
            FROM {$this->table}
            WHERE EXTRACT(MONTH FROM fecha_cosecha) = EXTRACT(MONTH FROM CURRENT_DATE)
              AND EXTRACT(YEAR FROM fecha_cosecha) = EXTRACT(YEAR FROM CURRENT_DATE)";

    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetch();
}


    public function getWithLote() {
        $sql = "SELECT c.*, l.nombre as lote_nombre, l.codigo_lote
                FROM {$this->table} c
                JOIN lotes l ON c.lote_id = l.id
                ORDER BY c.fecha_cosecha DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Sobrescribir getAll
    public function getAll() {
        return $this->getWithLote();
    }
}
?>