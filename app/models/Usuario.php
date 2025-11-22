<?php
require_once 'Model.php';

class Usuario extends Model {

    public function __construct() {
        parent::__construct('usuarios');
    }

    /**
     * Buscar usuario por username
     */
    public function findByUsername($username) {
        $sql = "SELECT *
                FROM {$this->table}
                WHERE username = ?
                AND estado = 'activo'
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar contraseña
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Crear usuario nuevo con password encriptado
     */
    public function createUser($data) {
        // Crear hash de password
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);

        return $this->create($data);
    }
}
?>
