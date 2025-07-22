<?php
session_start();
require_once '../config/database.php';
require_once '../models/Usuario.php';

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'login':
            login($_POST, $usuario);
            break;
        case 'logout':
            logout();
            break;
        case 'change_password':
            changePassword($_POST, $usuario);
            break;
        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} else {
    // Show login form if no POST data
    if (isset($_SESSION['user_id'])) {
        // Redirect to appropriate dashboard based on user type
        $userType = $_SESSION['user_type'];
        switch($userType) {
            case 'admin':
                header('Location: ../public/dashboard.php');
                break;
            case 'cliente':
                header('Location: ../public/dashboard_cliente.php');
                break;
            case 'mecanico':
                header('Location: ../public/dashboard_mecanico.php');
                break;
        }
        exit();
    }
}

function login($data, $usuario) {
    $email_or_phone = $data['email_or_phone'] ?? '';
    $password = $data['password'] ?? '';
    $redirect = $data['redirect'] ?? '';
    
    if (empty($email_or_phone) || empty($password)) {
        if ($redirect === 'json') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Email/teléfono y contraseña son requeridos']);
        } else {
            $_SESSION['error'] = 'Email/teléfono y contraseña son requeridos';
            header('Location: ../views/auth/login.php');
        }
        return;
    }
    
    $user = $usuario->login($email_or_phone, $password);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_type'] = $user['tipo_usuario'];
        
        if ($redirect === 'json') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Login exitoso',
                'user_type' => $user['tipo_usuario'],
                'redirect_url' => getDashboardUrl($user['tipo_usuario'])
            ]);
        } else {
            // Redirect to appropriate dashboard
            header('Location: ' . getDashboardUrl($user['tipo_usuario']));
        }
    } else {
        if ($redirect === 'json') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
        } else {
            $_SESSION['error'] = 'Email/teléfono o contraseña incorrectos';
            header('Location: ../views/auth/login.php');
        }
    }
}

function logout() {
    session_destroy();
    header('Location: ../public/index.php');
    exit();
}

function changePassword($data, $usuario) {
    if (!isset($_SESSION['user_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        return;
    }
    
    $currentPassword = $data['current_password'] ?? '';
    $newPassword = $data['new_password'] ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        return;
    }
    
    if ($newPassword !== $confirmPassword) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
        return;
    }
    
    if (strlen($newPassword) < 6) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
        return;
    }
    
    // Verify current password
    $user = $usuario->login($_SESSION['user_email'], $currentPassword);
    if (!$user) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Contraseña actual incorrecta']);
        return;
    }
    
    // Update password
    if ($usuario->updatePassword($_SESSION['user_id'], $newPassword)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Contraseña actualizada exitosamente']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña']);
    }
}

function getDashboardUrl($userType) {
    switch($userType) {
        case 'admin':
            return '../public/dashboard.php';
        case 'cliente':
            return '../public/dashboard_cliente.php';
        case 'mecanico':
            return '../public/dashboard_mecanico.php';
        default:
            return '../public/index.php';
    }
}

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function requireAuth($allowedTypes = []) {
    if (!isAuthenticated()) {
        header('Location: ../views/auth/login.php');
        exit();
    }
    
    if (!empty($allowedTypes) && !in_array($_SESSION['user_type'], $allowedTypes)) {
        header('Location: ../public/unauthorized.php');
        exit();
    }
}
?>