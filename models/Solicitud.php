<?php
require_once '../config/database.php';

class Solicitud {
    private $conn;
    private $table_name = "solicitudes";

    public $id;
    public $cliente_id;
    public $mecanico_id;
    public $tipo_servicio;
    public $descripcion;
    public $ubicacion_lat;
    public $ubicacion_lng;
    public $direccion_completa;
    public $fecha_solicitud;
    public $fecha_programada;
    public $fecha_completado;
    public $estatus;
    public $costo_estimado;
    public $costo_final;
    public $metodo_pago;
    public $comprobante_pago;
    public $notas_cliente;
    public $notas_mecanico;
    public $prioridad;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET cliente_id=:cliente_id, tipo_servicio=:tipo_servicio, 
                    descripcion=:descripcion, direccion_completa=:direccion_completa,
                    ubicacion_lat=:ubicacion_lat, ubicacion_lng=:ubicacion_lng,
                    fecha_programada=:fecha_programada, costo_estimado=:costo_estimado,
                    metodo_pago=:metodo_pago, notas_cliente=:notas_cliente,
                    prioridad=:prioridad";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":cliente_id", $this->cliente_id);
        $stmt->bindParam(":tipo_servicio", $this->tipo_servicio);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":direccion_completa", $this->direccion_completa);
        $stmt->bindParam(":ubicacion_lat", $this->ubicacion_lat);
        $stmt->bindParam(":ubicacion_lng", $this->ubicacion_lng);
        $stmt->bindParam(":fecha_programada", $this->fecha_programada);
        $stmt->bindParam(":costo_estimado", $this->costo_estimado);
        $stmt->bindParam(":metodo_pago", $this->metodo_pago);
        $stmt->bindParam(":notas_cliente", $this->notas_cliente);
        $stmt->bindParam(":prioridad", $this->prioridad);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function read() {
        $query = "SELECT s.*, u.nombre as cliente_nombre, u.telefono as cliente_telefono,
                         m.nombre as mecanico_nombre
                  FROM " . $this->table_name . " s
                  LEFT JOIN usuarios u ON s.cliente_id = u.id
                  LEFT JOIN usuarios m ON s.mecanico_id = m.id
                  ORDER BY s.fecha_solicitud DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByCliente($cliente_id) {
        $query = "SELECT s.*, m.nombre as mecanico_nombre
                  FROM " . $this->table_name . " s
                  LEFT JOIN usuarios m ON s.mecanico_id = m.id
                  WHERE s.cliente_id = :cliente_id
                  ORDER BY s.fecha_solicitud DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cliente_id", $cliente_id);
        $stmt->execute();
        return $stmt;
    }

    public function readByMecanico($mecanico_id) {
        $query = "SELECT s.*, u.nombre as cliente_nombre, u.telefono as cliente_telefono
                  FROM " . $this->table_name . " s
                  JOIN usuarios u ON s.cliente_id = u.id
                  WHERE s.mecanico_id = :mecanico_id
                  ORDER BY s.fecha_solicitud DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":mecanico_id", $mecanico_id);
        $stmt->execute();
        return $stmt;
    }

    public function updateEstatus() {
        $query = "UPDATE " . $this->table_name . " 
                SET estatus = :estatus
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estatus", $this->estatus);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function asignarMecanico() {
        $query = "UPDATE " . $this->table_name . " 
                SET mecanico_id = :mecanico_id, estatus = 'asignado'
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":mecanico_id", $this->mecanico_id);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function getEstadisticas() {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estatus = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estatus = 'asignado' THEN 1 ELSE 0 END) as asignados,
                    SUM(CASE WHEN estatus = 'en_progreso' THEN 1 ELSE 0 END) as en_progreso,
                    SUM(CASE WHEN estatus = 'completado' THEN 1 ELSE 0 END) as completados,
                    SUM(CASE WHEN estatus = 'cancelado' THEN 1 ELSE 0 END) as cancelados,
                    AVG(costo_final) as promedio_costo,
                    SUM(costo_final) as ingresos_totales
                  FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorTelefono($telefono) {
        $query = "SELECT u.nombre, u.email, u.telefono, u.empresa
                  FROM usuarios u
                  WHERE u.telefono = :telefono AND u.tipo_usuario = 'cliente'
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>