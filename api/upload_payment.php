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

if (!isset($_FILES['payment_proof']) || !isset($_POST['service_id'])) {
    echo json_encode(['success' => false, 'message' => 'Archivo y ID de servicio requeridos']);
    exit();
}

try {
    $serviceId = $_POST['service_id'];
    $file = $_FILES['payment_proof'];
    
    // Validate file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido']);
        exit();
    }
    
    if ($file['size'] > 5000000) { // 5MB max
        echo json_encode(['success' => false, 'message' => 'Archivo muy grande (máximo 5MB)']);
        exit();
    }
    
    // Create uploads directory if it doesn't exist
    $uploadDir = '../uploads/payments/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'payment_' . $serviceId . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Update database
        require_once '../config/database.php';
        
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "UPDATE solicitudes SET comprobante_pago = :filename WHERE id = :service_id AND cliente_id = :client_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':filename', $filename);
        $stmt->bindParam(':service_id', $serviceId);
        $stmt->bindParam(':client_id', $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            // Add activity log
            $logQuery = "INSERT INTO actividades (solicitud_id, usuario_id, comentario, tipo_actividad) 
                         VALUES (:solicitud_id, :usuario_id, 'Comprobante de pago subido', 'pago')";
            $logStmt = $db->prepare($logQuery);
            $logStmt->bindParam(':solicitud_id', $serviceId);
            $logStmt->bindParam(':usuario_id', $_SESSION['user_id']);
            $logStmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Comprobante subido exitosamente']);
        } else {
            unlink($filepath); // Delete file if database update fails
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la base de datos']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
    }
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>