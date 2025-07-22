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

if (!isset($input['service_id']) || !isset($input['note'])) {
    echo json_encode(['success' => false, 'message' => 'ID de servicio y nota requeridos']);
    exit();
}

try {
    require_once '../config/database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    $serviceId = $input['service_id'];
    $note = $input['note'];
    
    // Verify service belongs to mechanic
    $verifyQuery = "SELECT id FROM solicitudes WHERE id = :service_id AND mecanico_id = :mechanic_id";
    $verifyStmt = $db->prepare($verifyQuery);
    $verifyStmt->bindParam(':service_id', $serviceId);
    $verifyStmt->bindParam(':mechanic_id', $_SESSION['user_id']);
    $verifyStmt->execute();
    
    if ($verifyStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Servicio no encontrado']);
        exit();
    }
    
    // Add activity log
    $logQuery = "INSERT INTO actividades (solicitud_id, usuario_id, comentario, tipo_actividad) 
                 VALUES (:solicitud_id, :usuario_id, :comentario, 'comentario')";
    $logStmt = $db->prepare($logQuery);
    $logStmt->bindParam(':solicitud_id', $serviceId);
    $logStmt->bindParam(':usuario_id', $_SESSION['user_id']);
    $logStmt->bindParam(':comentario', $note);
    
    if ($logStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Nota agregada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al agregar la nota']);
    }
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>