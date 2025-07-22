<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['service_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de servicio requerido']);
    exit();
}

try {
    require_once '../config/database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT s.*, u.nombre as cliente_nombre, u.telefono as cliente_telefono,
                     m.nombre as mecanico_nombre
              FROM solicitudes s
              LEFT JOIN usuarios u ON s.cliente_id = u.id
              LEFT JOIN usuarios m ON s.mecanico_id = m.id
              WHERE s.id = :service_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':service_id', $input['service_id']);
    $stmt->execute();
    
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($service) {
        echo json_encode(['success' => true, 'service' => $service]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Servicio no encontrado']);
    }
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>