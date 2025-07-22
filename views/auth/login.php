<?php
session_start();
if (isset($_SESSION['user_id'])) {
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MechanicalFix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .login-logo {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: white;
            text-align: center;
            padding: 3rem 2rem;
        }
        .login-form {
            padding: 3rem 2rem;
        }
        .btn-primary {
            background: #dc3545;
            border-color: #dc3545;
            padding: 12px 0;
        }
        .btn-primary:hover {
            background: #c82333;
            border-color: #c82333;
        }
        .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        .user-type-selector {
            margin-bottom: 1.5rem;
        }
        .user-type-btn {
            flex: 1;
            margin: 0 0.25rem;
            padding: 0.75rem;
            border: 2px solid #dee2e6;
            background: white;
            color: #6c757d;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .user-type-btn.active {
            border-color: #dc3545;
            background: #dc3545;
            color: white;
        }
        .user-type-btn:hover {
            border-color: #dc3545;
            color: #dc3545;
        }
        .user-type-btn.active:hover {
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-6">
                <div class="login-container">
                    <div class="row g-0">
                        <div class="col-lg-6">
                            <div class="login-logo">
                                <svg width="150" height="75" viewBox="0 0 400 200" xmlns="http://www.w3.org/2000/svg">
                                    <!-- Wrench Icon -->
                                    <g transform="translate(40, 60)">
                                        <rect x="0" y="10" width="60" height="15" fill="#000" stroke="#fff" stroke-width="3"/>
                                        <rect x="0" y="25" width="60" height="15" fill="#000" stroke="#fff" stroke-width="3"/>
                                        <rect x="55" y="0" width="20" height="55" fill="#000" stroke="#fff" stroke-width="3"/>
                                        <rect x="75" y="15" width="15" height="25" fill="#000" stroke="#fff" stroke-width="3"/>
                                    </g>
                                    
                                    <!-- Shield Background -->
                                    <g transform="translate(140, 30)">
                                        <path d="M0,20 L20,0 L80,0 L100,20 L100,60 L50,90 L0,60 Z" fill="white" stroke="#000" stroke-width="3"/>
                                        <text x="50" y="45" text-anchor="middle" fill="#dc3545" font-family="Arial, sans-serif" font-size="24" font-weight="bold">MX</text>
                                    </g>
                                    
                                    <!-- Another Wrench -->
                                    <g transform="translate(280, 60) rotate(45)">
                                        <rect x="0" y="10" width="60" height="15" fill="#000" stroke="#fff" stroke-width="3"/>
                                        <rect x="0" y="25" width="60" height="15" fill="#000" stroke="#fff" stroke-width="3"/>
                                        <rect x="55" y="0" width="20" height="55" fill="#000" stroke="#fff" stroke-width="3"/>
                                        <rect x="75" y="15" width="15" height="25" fill="#000" stroke="#fff" stroke-width="3"/>
                                    </g>
                                    
                                    <!-- Text -->
                                    <text x="200" y="150" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="20" font-weight="bold">MECHANICALFIX</text>
                                </svg>
                                <h3 class="mt-3">Bienvenido</h3>
                                <p>Ingresa a tu cuenta</p>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="login-form">
                                <?php if (isset($_SESSION['error'])): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="user-type-selector">
                                    <div class="d-flex">
                                        <a href="#" class="user-type-btn active" data-type="all">
                                            <i class="bi bi-person-circle"></i><br>
                                            <small>Todos</small>
                                        </a>
                                        <a href="#" class="user-type-btn" data-type="admin">
                                            <i class="bi bi-shield-check"></i><br>
                                            <small>Admin</small>
                                        </a>
                                        <a href="#" class="user-type-btn" data-type="cliente">
                                            <i class="bi bi-person"></i><br>
                                            <small>Cliente</small>
                                        </a>
                                        <a href="#" class="user-type-btn" data-type="mecanico">
                                            <i class="bi bi-tools"></i><br>
                                            <small>Mecánico</small>
                                        </a>
                                    </div>
                                </div>

                                <form method="POST" action="../controllers/AuthController.php" id="loginForm">
                                    <input type="hidden" name="action" value="login">
                                    
                                    <div class="mb-3">
                                        <label for="email_or_phone" class="form-label">Email o Teléfono</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-person"></i>
                                            </span>
                                            <input type="text" class="form-control" id="email_or_phone" name="email_or_phone" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Contraseña</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-lock"></i>
                                            </span>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                                <i class="bi bi-eye" id="toggleIcon"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="remember">
                                        <label class="form-check-label" for="remember">
                                            Recordarme
                                        </label>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 mb-3">
                                        <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                                    </button>

                                    <div class="text-center">
                                        <a href="#" class="text-decoration-none" onclick="showForgotPassword()">
                                            ¿Olvidaste tu contraseña?
                                        </a>
                                    </div>

                                    <hr>

                                    <div class="text-center">
                                        <p class="mb-2">¿Necesitas un mecánico?</p>
                                        <a href="../views/solicitud/form.php" class="btn btn-outline-primary">
                                            <i class="bi bi-plus-circle"></i> Solicitar Servicio
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Credentials Modal -->
    <div class="modal fade" id="credentialsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Credenciales de Prueba</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>Administrador:</h6>
                    <p><strong>Email:</strong> admin@mechanicalfix.com<br>
                    <strong>Contraseña:</strong> password</p>
                    
                    <h6>Mecánicos:</h6>
                    <p><strong>Email:</strong> juan.perez@mechanicalfix.com<br>
                    <strong>Contraseña:</strong> password</p>
                    
                    <h6>Clientes:</h6>
                    <p><strong>Email:</strong> ana.martinez@email.com<br>
                    <strong>Contraseña:</strong> password</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                password.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        function showForgotPassword() {
            alert('Para recuperar tu contraseña, contacta al administrador del sistema.\n\nPara pruebas, puedes usar las credenciales de demostración.');
            new bootstrap.Modal(document.getElementById('credentialsModal')).show();
        }

        // User type selector
        document.querySelectorAll('.user-type-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                document.querySelectorAll('.user-type-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const type = this.dataset.type;
                const emailInput = document.getElementById('email_or_phone');
                
                // Pre-fill credentials based on user type for demo
                switch(type) {
                    case 'admin':
                        emailInput.value = 'admin@mechanicalfix.com';
                        break;
                    case 'cliente':
                        emailInput.value = 'ana.martinez@email.com';
                        break;
                    case 'mecanico':
                        emailInput.value = 'juan.perez@mechanicalfix.com';
                        break;
                    default:
                        emailInput.value = '';
                }
            });
        });

        // Show test credentials on page load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                new bootstrap.Modal(document.getElementById('credentialsModal')).show();
            }, 1000);
        });
    </script>
</body>
</html>