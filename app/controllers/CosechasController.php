<?php
require_once __DIR__ . '/../models/Cosecha.php';
require_once __DIR__ . '/../models/Lote.php';

class CosechasController {
    private $cosechaModel;
    private $loteModel;

    public function __construct() {
        $this->cosechaModel = new Cosecha();
        $this->loteModel = new Lote();
    }

    public function index() {
        return $this->cosechaModel->getWithLote();
    }

    public function crear($data) {
        $data['usuario_id'] = $_SESSION['usuario']['id'];
        
        // Calcular rendimiento (kg/hectárea)
        $lote = $this->loteModel->find($data['lote_id']);
        if ($lote && $lote['area'] > 0) {
            $data['rendimiento'] = $data['kilos_cosechados'] / $lote['area'];
        }
        
        return $this->cosechaModel->create($data);
    }

    public function obtenerLotes() {
        return $this->loteModel->getActivos();
    }

    public function getEstadisticas() {
        return [
            'total_cosechado' => $this->cosechaModel->getCosechasMes(),
            'rendimiento_promedio' => $this->getRendimientoPromedio(),
            'distribucion_calidad' => $this->getDistribucionCalidad()
        ];
    }

    private function getRendimientoPromedio() {
        $sql = "SELECT AVG(rendimiento) as promedio 
                FROM cosechas 
                WHERE MONTH(fecha_cosecha) = MONTH(CURRENT_DATE())";
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['promedio'] ?? 0;
    }

    private function getDistribucionCalidad() {
        $db = Database::getInstance();
        $sql = "SELECT calidad, COUNT(*) as cantidad, SUM(kilos_cosechados) as kilos
                FROM cosechas
                WHERE MONTH(fecha_cosecha) = MONTH(CURRENT_DATE())
                GROUP BY calidad";
    
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>