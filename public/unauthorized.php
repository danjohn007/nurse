<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso No Autorizado - MechanicalFix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: white;
        }
        .error-container {
            text-align: center;
        }
        .error-icon {
            font-size: 8rem;
            margin-bottom: 2rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="error-icon">
                <i class="bi bi-shield-exclamation"></i>
            </div>
            <h1 class="display-4 mb-4">Acceso No Autorizado</h1>
            <p class="lead mb-4">No tienes permisos para acceder a esta página.</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="../views/auth/login.php" class="btn btn-light btn-lg">
                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                </a>
                <a href="../views/solicitud/form.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-house"></i> Ir al Inicio
                </a>
            </div>
        </div>
    </div>
</body>
</html>