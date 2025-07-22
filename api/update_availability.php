<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'mecanico') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['available'])) {
    echo json_encode(['success' => false, 'message' => 'Estado de disponibilidad requerido']);
    exit();
}

try {
    require_once '../config/database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    $available = $input['available'] ? 1 : 0;
    
    $query = "UPDATE mecanicos SET disponible = :available WHERE usuario_id = :mechanic_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':available', $available);
    $stmt->bindParam(':mechanic_id', $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Disponibilidad actualizada']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar disponibilidad']);
    }
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>