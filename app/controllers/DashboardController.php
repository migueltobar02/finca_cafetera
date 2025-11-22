<?php
require_once __DIR__ . '/../models/Ingreso.php';
require_once __DIR__ . '/../models/Egreso.php';
require_once __DIR__ . '/../models/Cosecha.php';
require_once __DIR__ . '/../models/Venta.php';
require_once __DIR__ . '/../models/Empleado.php';
require_once __DIR__ . '/../models/Jornal.php';
require_once __DIR__ . '/../models/Cliente.php';

class DashboardController
{
    private $ingreso;
    private $egreso;
    private $cosecha;
    private $actividad;
    private $jornal;
    private $cliente;

    public function __construct()
    {
        $this->ingreso = new Ingreso();
        $this->egreso   = new Egreso();
        $this->cosecha  = new Cosecha();
        $this->actividad = new Actividad();
        $this->jornal    = new Jornal();
        $this->cliente   = new Cliente();
    }

    /* ---------------------------------------------------
     *  ESTADÍSTICAS GENERALES
     * --------------------------------------------------- */
    public function getEstadisticas()
    {
        return [
            "ingresos_mes"      => $this->ingreso->getIngresosMes(),
            "egresos_mes"       => $this->egreso->getEgresosMes(),
            "cosechas_mes"      => $this->cosecha->getCosechasMes(),
            "top_clientes"      => $this->cliente->getTopClientes(),
            "actividad_reciente" => $this->getActividadReciente(),
            "proximas_cosechas"  => $this->getProximasCosechas(),
            "jornales_pendientes" => $this->getJornalesPendientes(),
        ];
    }

    /* ---------------------------------------------------
     *  ACTIVIDAD RECIENTE
     * --------------------------------------------------- */
    public function getActividadReciente()
    {
        $db = Database::getInstance(); // PDO directo

        $sql = "
            SELECT descripcion, fecha
            FROM actividades
            ORDER BY fecha DESC
            LIMIT 5
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /* ---------------------------------------------------
     *  PRÓXIMAS COSECHAS
     * --------------------------------------------------- */
    public function getProximasCosechas()
    {
        $db = Database::getInstance();

        $sql = "
            SELECT lote_id, fecha_cosecha_estimada 
            FROM cosechas
            WHERE fecha_cosecha_estimada > CURRENT_DATE
            ORDER BY fecha_cosecha_estimada ASC
            LIMIT 5
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /* ---------------------------------------------------
     *  JORNALES PENDIENTES
     * --------------------------------------------------- */
    public function getJornalesPendientes()
    {
        $db = Database::getInstance();

        $sql = "
            SELECT empleado_id, fecha, horas, actividad
            FROM jornales
            WHERE pagado = false
            ORDER BY fecha DESC
            LIMIT 5
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
