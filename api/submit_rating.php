<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['service_id']) || !isset($input['rating'])) {
    echo json_encode(['success' => false, 'message' => 'ID de servicio y calificación requeridos']);
    exit();
}

$serviceId = $input['service_id'];
$rating = $input['rating'];
$comment = $input['comment'] ?? '';

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Calificación debe estar entre 1 y 5']);
    exit();
}

try {
    require_once '../config/database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Verify service belongs to client and is completed
    $verifyQuery = "SELECT mecanico_id FROM solicitudes WHERE id = :service_id AND cliente_id = :client_id AND estatus = 'completado'";
    $verifyStmt = $db->prepare($verifyQuery);
    $verifyStmt->bindParam(':service_id', $serviceId);
    $verifyStmt->bindParam(':client_id', $_SESSION['user_id']);
    $verifyStmt->execute();
    
    $service = $verifyStmt->fetch(PDO::FETCH_ASSOC);
    if (!$service) {
        echo json_encode(['success' => false, 'message' => 'Servicio no encontrado o no completado']);
        exit();
    }
    
    $mechanicId = $service['mecanico_id'];
    
    // Check if already rated
    $checkQuery = "SELECT id FROM calificaciones WHERE solicitud_id = :service_id AND cliente_id = :client_id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':service_id', $serviceId);
    $checkStmt->bindParam(':client_id', $_SESSION['user_id']);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya has calificado este servicio']);
        exit();
    }
    
    // Insert rating
    $insertQuery = "INSERT INTO calificaciones (solicitud_id, cliente_id, mecanico_id, puntuacion, comentario) 
                    VALUES (:service_id, :client_id, :mechanic_id, :rating, :comment)";
    $insertStmt = $db->prepare($insertQuery);
    $insertStmt->bindParam(':service_id', $serviceId);
    $insertStmt->bindParam(':client_id', $_SESSION['user_id']);
    $insertStmt->bindParam(':mechanic_id', $mechanicId);
    $insertStmt->bindParam(':rating', $rating);
    $insertStmt->bindParam(':comment', $comment);
    
    if ($insertStmt->execute()) {
        // Update mechanic's average rating
        $updateQuery = "UPDATE mecanicos SET 
                        calificacion_promedio = (
                            SELECT AVG(puntuacion) FROM calificaciones WHERE mecanico_id = :mechanic_id
                        ),
                        total_servicios = (
                            SELECT COUNT(*) FROM solicitudes WHERE mecanico_id = :mechanic_id AND estatus = 'completado'
                        )
                        WHERE usuario_id = :mechanic_id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':mechanic_id', $mechanicId);
        $updateStmt->execute();
        
        // Add activity log
        $logQuery = "INSERT INTO actividades (solicitud_id, usuario_id, comentario, tipo_actividad) 
                     VALUES (:solicitud_id, :usuario_id, :comentario, 'calificacion')";
        $logStmt = $db->prepare($logQuery);
        $logStmt->bindParam(':solicitud_id', $serviceId);
        $logStmt->bindParam(':usuario_id', $_SESSION['user_id']);
        $logComment = "Calificación: $rating estrellas";
        $logStmt->bindParam(':comentario', $logComment);
        $logStmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Calificación enviada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la calificación']);
    }
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>