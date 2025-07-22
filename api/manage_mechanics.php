<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../models/Usuario.php';

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch($action) {
    case 'create':
        createMechanic($input, $usuario, $db);
        break;
    case 'list':
        listMechanics($usuario);
        break;
    case 'update':
        updateMechanic($input, $usuario, $db);
        break;
    case 'delete':
        deleteMechanic($input, $usuario, $db);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

function createMechanic($data, $usuario, $db) {
    try {
        // Validate required fields
        $required_fields = ['nombre', 'email', 'telefono', 'especialidades', 'experiencia_anos'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                echo json_encode(['success' => false, 'message' => "Campo requerido: $field"]);
                return;
            }
        }
        
        // Check if email or phone already exists
        $query = "SELECT id FROM usuarios WHERE email = :email OR telefono = :telefono";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->execute();
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode(['success' => false, 'message' => 'El email o teléfono ya están registrados']);
            return;
        }
        
        // Begin transaction
        $db->beginTransaction();
        
        // Create user
        $usuario->nombre = $data['nombre'];
        $usuario->email = $data['email'];
        $usuario->telefono = $data['telefono'];
        $usuario->password = $usuario->generateRandomPassword();
        $usuario->tipo_usuario = 'mecanico';
        $usuario->direccion = $data['direccion'] ?? '';
        
        $userId = $usuario->create();
        
        if (!$userId) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Error al crear el usuario mecánico']);
            return;
        }
        
        // Create mechanic details
        $query = "INSERT INTO mecanicos (usuario_id, especialidades, experiencia_anos, certificaciones, tarifa_base) 
                  VALUES (:usuario_id, :especialidades, :experiencia_anos, :certificaciones, :tarifa_base)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':usuario_id', $userId);
        $stmt->bindParam(':especialidades', json_encode($data['especialidades']));
        $stmt->bindParam(':experiencia_anos', $data['experiencia_anos']);
        $stmt->bindParam(':certificaciones', json_encode($data['certificaciones'] ?? []));
        $stmt->bindParam(':tarifa_base', $data['tarifa_base'] ?? 0.00);
        
        if ($stmt->execute()) {
            $db->commit();
            echo json_encode([
                'success' => true, 
                'message' => 'Mecánico creado exitosamente',
                'user_id' => $userId,
                'password' => $usuario->password
            ]);
        } else {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Error al crear los detalles del mecánico']);
        }
        
    } catch(Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function listMechanics($usuario) {
    try {
        $stmt = $usuario->readMecanicos();
        $mechanics = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Decode JSON fields
            $row['especialidades'] = json_decode($row['especialidades'], true);
            $mechanics[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $mechanics]);
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function updateMechanic($data, $usuario, $db) {
    try {
        if (empty($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID del mecánico es requerido']);
            return;
        }
        
        // Validate mechanic exists
        $query = "SELECT u.id FROM usuarios u 
                  JOIN mecanicos m ON u.id = m.usuario_id 
                  WHERE u.id = :id AND u.tipo_usuario = 'mecanico'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $data['id']);
        $stmt->execute();
        
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode(['success' => false, 'message' => 'Mecánico no encontrado']);
            return;
        }
        
        $db->beginTransaction();
        
        // Update user info
        $userFields = ['nombre', 'email', 'telefono', 'direccion'];
        $updateUserData = [];
        foreach ($userFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $updateUserData[$field] = $data[$field];
            }
        }
        
        if (!empty($updateUserData)) {
            $usuario->updateProfile($data['id'], $updateUserData);
        }
        
        // Update mechanic details
        $mechanicFields = [];
        $params = [':usuario_id' => $data['id']];
        
        if (isset($data['especialidades'])) {
            $mechanicFields[] = "especialidades = :especialidades";
            $params[':especialidades'] = json_encode($data['especialidades']);
        }
        if (isset($data['experiencia_anos'])) {
            $mechanicFields[] = "experiencia_anos = :experiencia_anos";
            $params[':experiencia_anos'] = $data['experiencia_anos'];
        }
        if (isset($data['certificaciones'])) {
            $mechanicFields[] = "certificaciones = :certificaciones";
            $params[':certificaciones'] = json_encode($data['certificaciones']);
        }
        if (isset($data['tarifa_base'])) {
            $mechanicFields[] = "tarifa_base = :tarifa_base";
            $params[':tarifa_base'] = $data['tarifa_base'];
        }
        if (isset($data['disponible'])) {
            $mechanicFields[] = "disponible = :disponible";
            $params[':disponible'] = $data['disponible'] ? 1 : 0;
        }
        
        if (!empty($mechanicFields)) {
            $query = "UPDATE mecanicos SET " . implode(', ', $mechanicFields) . " WHERE usuario_id = :usuario_id";
            $stmt = $db->prepare($query);
            $stmt->execute($params);
        }
        
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Mecánico actualizado exitosamente']);
        
    } catch(Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function deleteMechanic($data, $usuario, $db) {
    try {
        if (empty($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID del mecánico es requerido']);
            return;
        }
        
        // Check if mechanic has active assignments
        $query = "SELECT COUNT(*) as count FROM solicitudes 
                  WHERE mecanico_id = :id AND estatus IN ('asignado', 'en_progreso')";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $data['id']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar el mecánico porque tiene solicitudes activas']);
            return;
        }
        
        // Delete user (cascade will handle mechanic details)
        $query = "DELETE FROM usuarios WHERE id = :id AND tipo_usuario = 'mecanico'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $data['id']);
        
        if ($stmt->execute() && $stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Mecánico eliminado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Mecánico no encontrado']);
        }
        
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>