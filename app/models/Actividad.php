<?php
require_once 'Model.php';

class Actividad extends Model {

    public function __construct() {
        parent::__construct('actividades');
    }

    /**
     * Obtiene actividades activas
     */
    public function getActividadesActivas() {
        $sql = "SELECT * 
                FROM {$this->table} 
                WHERE estado = 'activa'
                ORDER BY nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtiene actividades filtradas por tipo
     */
    public function getActividadesPorTipo($tipo) {
        $sql = "SELECT *
                FROM {$this->table}
                WHERE tipo = $1
                AND estado = 'activa'
                ORDER BY nombre ASC";

        // PostgreSQL permite parámetros nombrados o numerados ($1, $2...)
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tipo]);

        return $stmt->fetchAll();
    }

    /**
     * Obtiene insumos relacionados a una actividad
     */
    public function getActividadesConInsumos($actividadId) {
        $sql = "SELECT ci.*, 
                       i.nombre AS insumo_nombre, 
                       i.unidad_medida
                FROM consumo_insumos ci
                JOIN insumos i ON ci.insumo_id = i.id
                WHERE ci.actividad_id = $1
                ORDER BY ci.fecha_consumo DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$actividadId]);

        return $stmt->fetchAll();
    }
}
?>
