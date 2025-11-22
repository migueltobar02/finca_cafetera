<?php
/**
 * Clase base Model para todos los modelos
 */

class Model {
    protected $db;
    protected $table;
    protected $softDelete = true; // Controlar si usa borrado suave

    public function __construct($table, $softDelete = true) {
        // Asegurar que Database esté cargado
        if (!class_exists('Database')) {
            require_once __DIR__ . '/Database.php';
        }

        // Usar PDO directamente
        $this->db = Database::getInstance();
        $this->table = $table;
        $this->softDelete = $softDelete;
    }

    public function getAll() {
        $sql = $this->softDelete
            ? "SELECT * FROM {$this->table} WHERE estado = 'activo'"
            : "SELECT * FROM {$this->table}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $sql = $this->softDelete
            ? "SELECT * FROM {$this->table} WHERE id = ? AND estado = 'activo'"
            : "SELECT * FROM {$this->table} WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $setClause = [];
        foreach ($data as $key => $value) {
            $setClause[] = "$key = :$key";
        }
        $setClause = implode(', ', $setClause);

        $sql = "UPDATE {$this->table} SET $setClause WHERE id = :id";
        $data['id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id) {
        if ($this->softDelete) {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET estado = 'inactivo' WHERE id = ?");
        } else {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        }
        return $stmt->execute([$id]);
    }

    public function count() {
        $sql = $this->softDelete
            ? "SELECT COUNT(*) as total FROM {$this->table} WHERE estado = 'activo'"
            : "SELECT COUNT(*) as total FROM {$this->table}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function execute($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
