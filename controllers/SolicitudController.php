<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../models/Solicitud.php';
require_once '../models/Usuario.php';

$database = new Database();
$db = $database->getConnection();

$solicitud = new Solicitud($db);
$usuario = new Usuario($db);

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch($action) {
    case 'create':
        createSolicitud($input, $solicitud, $usuario);
        break;
    case 'buscar_telefono':
        buscarTelefono($input, $solicitud);
        break;
    case 'list':
        listSolicitudes($solicitud);
        break;
    case 'update_status':
        updateStatus($input, $solicitud);
        break;
    case 'assign_mechanic':
        assignMechanic($input, $solicitud);
        break;
    case 'get_stats':
        getStats($solicitud);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

function createSolicitud($data, $solicitud, $usuario) {
    try {
        // First, check if client exists or create new one
        $clienteId = null;
        $existingUser = $solicitud->buscarPorTelefono($data['telefono']);
        
        if ($existingUser) {
            // Update existing user data
            $query = "SELECT id FROM usuarios WHERE telefono = :telefono";
            $stmt = $solicitud->conn->prepare($query);
            $stmt->bindParam(':telefono', $data['telefono']);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $clienteId = $result['id'];
            
            // Update user info
            $updateData = [
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'empresa' => $data['empresa']
            ];
            $usuario->updateProfile($clienteId, $updateData);
        } else {
            // Create new client
            $usuario->nombre = $data['nombre'];
            $usuario->email = $data['email'];
            $usuario->telefono = $data['telefono'];
            $usuario->password = $usuario->generateRandomPassword();
            $usuario->tipo_usuario = 'cliente';
            $usuario->empresa = $data['empresa'];
            
            $clienteId = $usuario->create();
            
            if (!$clienteId) {
                echo json_encode(['success' => false, 'message' => 'Error al crear el cliente']);
                return;
            }
            
            // Send email with credentials (you would implement email sending here)
            // For now, we'll just log the password
            error_log("New client password for {$data['email']}: {$usuario->password}");
        }
        
        // Create service request
        $solicitud->cliente_id = $clienteId;
        $solicitud->tipo_servicio = $data['tipo_servicio'];
        $solicitud->descripcion = $data['descripcion'];
        $solicitud->direccion_completa = $data['direccion'];
        $solicitud->ubicacion_lat = $data['ubicacion_lat'];
        $solicitud->ubicacion_lng = $data['ubicacion_lng'];
        $solicitud->fecha_programada = $data['fecha_programada'];
        $solicitud->costo_estimado = $data['costo_estimado'];
        $solicitud->metodo_pago = $data['metodo_pago'];
        $solicitud->notas_cliente = $data['notas'];
        $solicitud->prioridad = $data['prioridad'];
        
        $solicitudId = $solicitud->create();
        
        if ($solicitudId) {
            echo json_encode([
                'success' => true, 
                'message' => 'Solicitud creada exitosamente',
                'solicitud_id' => $solicitudId
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear la solicitud']);
        }
        
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function buscarTelefono($data, $solicitud) {
    try {
        $result = $solicitud->buscarPorTelefono($data['telefono']);
        
        if ($result) {
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontraron datos']);
        }
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function listSolicitudes($solicitud) {
    try {
        $stmt = $solicitud->read();
        $solicitudes = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $solicitudes[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $solicitudes]);
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function updateStatus($data, $solicitud) {
    try {
        $solicitud->id = $data['id'];
        $solicitud->estatus = $data['estatus'];
        
        if ($solicitud->updateEstatus()) {
            // Add activity log
            $query = "INSERT INTO actividades (solicitud_id, usuario_id, comentario, tipo_actividad) 
                      VALUES (:solicitud_id, :usuario_id, :comentario, 'cambio_estatus')";
            $stmt = $solicitud->conn->prepare($query);
            $stmt->bindParam(':solicitud_id', $data['id']);
            $stmt->bindParam(':usuario_id', $data['usuario_id']);
            $comentario = "Estatus cambiado a: " . $data['estatus'];
            $stmt->bindParam(':comentario', $comentario);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Estatus actualizado']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el estatus']);
        }
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function assignMechanic($data, $solicitud) {
    try {
        $solicitud->id = $data['solicitud_id'];
        $solicitud->mecanico_id = $data['mecanico_id'];
        
        if ($solicitud->asignarMecanico()) {
            // Add activity log
            $query = "INSERT INTO actividades (solicitud_id, usuario_id, comentario, tipo_actividad) 
                      VALUES (:solicitud_id, :usuario_id, :comentario, 'asignacion')";
            $stmt = $solicitud->conn->prepare($query);
            $stmt->bindParam(':solicitud_id', $data['solicitud_id']);
            $stmt->bindParam(':usuario_id', $data['usuario_id']);
            $comentario = "Mecánico asignado";
            $stmt->bindParam(':comentario', $comentario);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Mecánico asignado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al asignar el mecánico']);
        }
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getStats($solicitud) {
    try {
        $stats = $solicitud->getEstadisticas();
        echo json_encode(['success' => true, 'data' => $stats]);
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>