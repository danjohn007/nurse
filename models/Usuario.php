<?php
require_once '../config/database.php';

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nombre;
    public $email;
    public $telefono;
    public $password;
    public $tipo_usuario;
    public $empresa;
    public $direccion;
    public $fecha_registro;
    public $estatus;
    public $avatar;
    public $fecha_nacimiento;
    public $genero;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET nombre=:nombre, email=:email, telefono=:telefono, 
                    password=:password, tipo_usuario=:tipo_usuario, 
                    empresa=:empresa, direccion=:direccion";

        $stmt = $this->conn->prepare($query);

        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":tipo_usuario", $this->tipo_usuario);
        $stmt->bindParam(":empresa", $this->empresa);
        $stmt->bindParam(":direccion", $this->direccion);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function login($email_or_phone, $password) {
        $query = "SELECT id, nombre, email, telefono, password, tipo_usuario, estatus 
                  FROM " . $this->table_name . " 
                  WHERE (email = :email_or_phone OR telefono = :email_or_phone) 
                  AND estatus = 'activo'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email_or_phone", $email_or_phone);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $row['password'])) {
                return $row;
            }
        }
        return false;
    }

    public function readMecanicos() {
        $query = "SELECT u.*, m.especialidades, m.experiencia_anos, m.calificacion_promedio, 
                         m.total_servicios, m.ingresos_totales, m.disponible, m.tarifa_base
                  FROM " . $this->table_name . " u
                  JOIN mecanicos m ON u.id = m.usuario_id
                  WHERE u.tipo_usuario = 'mecanico'
                  ORDER BY u.nombre";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getMecanicoById($id) {
        $query = "SELECT u.*, m.especialidades, m.experiencia_anos, m.calificacion_promedio, 
                         m.total_servicios, m.ingresos_totales, m.disponible, m.tarifa_base
                  FROM " . $this->table_name . " u
                  JOIN mecanicos m ON u.id = m.usuario_id
                  WHERE u.id = :id AND u.tipo_usuario = 'mecanico'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($id, $new_password) {
        $query = "UPDATE " . $this->table_name . " 
                SET password = :password 
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function updateProfile($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        foreach($data as $key => $value) {
            if($key !== 'id' && $value !== null) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }
        
        if(empty($fields)) return false;
        
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute($params);
    }

    public function getEstadisticasClientes() {
        $query = "SELECT 
                    COUNT(*) as total_clientes,
                    COUNT(CASE WHEN fecha_registro >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as nuevos_mes,
                    COUNT(CASE WHEN estatus = 'activo' THEN 1 END) as activos
                  FROM " . $this->table_name . " 
                  WHERE tipo_usuario = 'cliente'";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function generateRandomPassword() {
        return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);
    }
}
?>