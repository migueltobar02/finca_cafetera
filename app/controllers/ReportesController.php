<?php
require_once __DIR__ . '/../models/Reporte.php';
require_once __DIR__ . '/../models/Ingreso.php';
require_once __DIR__ . '/../models/Egreso.php';
require_once __DIR__ . '/../models/Cosecha.php';
require_once __DIR__ . '/../models/Venta.php';

class ReportesController {
    private $reporteModel;
    private $ingresoModel;
    private $egresoModel;
    private $cosechaModel;
    private $ventaModel;

    public function __construct() {
        $this->reporteModel = new Reporte();
        $this->ingresoModel = new Ingreso();
        $this->egresoModel = new Egreso();
        $this->cosechaModel = new Cosecha();
        $this->ventaModel = new Venta();
    }

    public function generarReporteFinanciero($fechaInicio, $fechaFin) {
        return $this->reporteModel->getReporteFinanciero($fechaInicio, $fechaFin);
    }

    public function generarReporteProduccion($fechaInicio, $fechaFin) {
        return $this->reporteModel->getReporteProduccion($fechaInicio, $fechaFin);
    }

    public function getResumenMensual($mes, $ano) {
        $ingresos = $this->ingresoModel->getIngresosMes();
        $egresos = $this->egresoModel->getEgresosMes();
        $cosechas = $this->cosechaModel->getCosechasMes();
        $ventas = $this->ventaModel->getVentasMes();

        return [
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'utilidad' => $ingresos - $egresos,
            'cosechas' => $cosechas,
            'ventas' => $ventas
        ];
    }

    public function getComparativoMensual($meses = 6) {
        // CORREGIDO: LIMIT con concatenación
        $sql = "SELECT 
                    YEAR(fecha_ingreso) as ano,
                    MONTH(fecha_ingreso) as mes,
                    COALESCE(SUM(monto), 0) as ingresos
                FROM ingresos
                WHERE fecha_ingreso >= DATE_SUB(CURRENT_DATE, INTERVAL " . (int)$meses . " MONTH)
                GROUP BY YEAR(fecha_ingreso), MONTH(fecha_ingreso)
                ORDER BY ano DESC, mes DESC
                LIMIT " . (int)$meses;
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Método alternativo más simple (ya corregido)
    public function getComparativoSimple($meses = 6) {
        $resultados = [];
        
        for ($i = 0; $i < $meses; $i++) {
            $fecha = date('Y-m', strtotime("-$i months"));
            $ano = date('Y', strtotime("-$i months"));
            $mes = date('m', strtotime("-$i months"));
            $mesNombre = $this->getNombreMes($mes);
            
            // Obtener ingresos del mes
            $ingresos = $this->getIngresosPorMes($ano, $mes);
            $egresos = $this->getEgresosPorMes($ano, $mes);
            $utilidad = $ingresos - $egresos;
            $margen = $ingresos > 0 ? ($utilidad / $ingresos) * 100 : 0;
            
            $resultados[] = [
                'ano' => $ano,
                'mes' => $mes,
                'mes_nombre' => $mesNombre,
                'ingresos' => $ingresos,
                'egresos' => $egresos,
                'utilidad' => $utilidad,
                'margen' => $margen
            ];
        }
        
        return $resultados;
    }

    private function getNombreMes($mes) {
        $meses = [
            '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
            '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
            '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
        ];
        return $meses[$mes] ?? $mes;
    }

    private function getIngresosPorMes($ano, $mes) {
        $sql = "SELECT COALESCE(SUM(monto), 0) as total 
                FROM ingresos 
                WHERE YEAR(fecha_ingreso) = ? 
                AND MONTH(fecha_ingreso) = ?";
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$ano, $mes]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    private function getEgresosPorMes($ano, $mes) {
        $sql = "SELECT COALESCE(SUM(monto), 0) as total 
                FROM egresos 
                WHERE YEAR(fecha_egreso) = ? 
                AND MONTH(fecha_egreso) = ?";
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$ano, $mes]); // pasas año y mes, no $termino
        $result = $stmt->fetch(PDO::FETCH_ASSOC); // un solo registro
        return $result['total'] ?? 0; // devuelve el total correctamente
    }
}
?>