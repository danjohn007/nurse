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

if (!isset($input['service_id']) || !isset($input['final_cost'])) {
    echo json_encode(['success' => false, 'message' => 'ID de servicio y costo final requeridos']);
    exit();
}

try {
    require_once '../config/database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    $serviceId = $input['service_id'];
    $finalCost = $input['final_cost'];
    $note = $input['note'] ?? '';
    
    // Verify service belongs to mechanic
    $verifyQuery = "SELECT id FROM solicitudes WHERE id = :service_id AND mecanico_id = :mechanic_id AND estatus = 'en_progreso'";
    $verifyStmt = $db->prepare($verifyQuery);
    $verifyStmt->bindParam(':service_id', $serviceId);
    $verifyStmt->bindParam(':mechanic_id', $_SESSION['user_id']);
    $verifyStmt->execute();
    
    if ($verifyStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Servicio no encontrado o no en progreso']);
        exit();
    }
    
    // Update service
    $updateQuery = "UPDATE solicitudes SET 
                    estatus = 'completado',
                    costo_final = :final_cost,
                    notas_mecanico = :note,
                    fecha_completado = NOW()
                    WHERE id = :service_id";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindParam(':final_cost', $finalCost);
    $updateStmt->bindParam(':note', $note);
    $updateStmt->bindParam(':service_id', $serviceId);
    
    if ($updateStmt->execute()) {
        // Update mechanic earnings
        $earningsQuery = "UPDATE mecanicos SET 
                         ingresos_totales = (
                             SELECT SUM(costo_final * 0.7) FROM solicitudes 
                             WHERE mecanico_id = :mechanic_id AND estatus = 'completado'
                         )
                         WHERE usuario_id = :mechanic_id";
        $earningsStmt = $db->prepare($earningsQuery);
        $earningsStmt->bindParam(':mechanic_id', $_SESSION['user_id']);
        $earningsStmt->execute();
        
        // Add activity log
        $logQuery = "INSERT INTO actividades (solicitud_id, usuario_id, comentario, tipo_actividad) 
                     VALUES (:solicitud_id, :usuario_id, :comentario, 'cambio_estatus')";
        $logStmt = $db->prepare($logQuery);
        $logStmt->bindParam(':solicitud_id', $serviceId);
        $logStmt->bindParam(':usuario_id', $_SESSION['user_id']);
        $logComment = "Servicio completado. Costo final: $" . number_format($finalCost, 2);
        $logStmt->bindParam(':comentario', $logComment);
        $logStmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Servicio completado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al completar el servicio']);
    }
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>