<?php
require_once __DIR__ . '/../models/Jornal.php';
require_once __DIR__ . '/../models/Empleado.php';
require_once __DIR__ . '/../models/Actividad.php';

class JornalesController {
    private $jornalModel;
    private $empleadoModel;
    private $actividadModel;

    public function __construct() {
        $this->jornalModel = new Jornal();
        $this->empleadoModel = new Empleado();
        $this->actividadModel = new Actividad();
    }

    public function index() {
        return $this->jornalModel->getAll();
    }

    public function crear($data) {
        $data['usuario_id'] = $_SESSION['usuario']['id'];
        $data['total_pago'] = $data['horas_trabajadas'] * $data['tarifa_hora'];
        return $this->jornalModel->create($data);
    }

    public function obtenerEmpleados() {
        return $this->empleadoModel->getActivos();
    }

    public function obtenerActividades() {
        return $this->actividadModel->getActividadesActivas();
    }

    public function getJornalesPorFecha($fecha) {
        $sql = "SELECT j.*, e.nombres, e.apellidos, a.nombre as actividad
                FROM jornales j
                JOIN empleados e ON j.empleado_id = e.id
                JOIN actividades a ON j.actividad_id = a.id
                WHERE j.fecha_jornal = ?
                ORDER BY e.nombres";
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$fecha]);
        return $stmt->fetchAll();
    }

    public function marcarComoPagado($jornalId) {
        $data = ['estado' => 'pagado'];
        return $this->jornalModel->update($jornalId, $data);
    }
}
?>