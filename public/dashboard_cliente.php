<?php
session_start();
require_once '../controllers/AuthController.php';
requireAuth(['cliente']);

require_once '../config/database.php';
require_once '../models/Solicitud.php';
require_once '../models/Usuario.php';

$database = new Database();
$db = $database->getConnection();
$solicitud = new Solicitud($db);
$usuario = new Usuario($db);

// Get client's requests
$stmt = $solicitud->readByCliente($_SESSION['user_id']);
$solicitudes = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $solicitudes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Dashboard - MechanicalFix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .header {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: white;
            padding: 2rem 0;
        }
        .service-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 1rem;
        }
        .service-card:hover {
            transform: translateY(-2px);
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        .quick-actions {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 24px;
        }
        .payment-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .file-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .file-upload-area:hover {
            border-color: #dc3545;
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0">Bienvenido, <?php echo $_SESSION['user_name']; ?></h1>
                    <p class="mb-0 opacity-75">Gestiona tus servicios mecánicos</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <a href="../views/solicitud/form.php" class="btn btn-light">
                            <i class="bi bi-plus-circle"></i> Nuevo Servicio
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="showProfile()">
                                    <i class="bi bi-person"></i> Mi Perfil
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="changePassword()">
                                    <i class="bi bi-key"></i> Cambiar Contraseña
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../controllers/AuthController.php?action=logout">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-4">
        <!-- Quick Actions -->
        <div class="quick-actions">
            <h5 class="mb-3">Acciones Rápidas</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary text-white">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                        <h3 class="mb-1"><?php echo count($solicitudes); ?></h3>
                        <p class="text-muted mb-0">Total Servicios</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-warning text-white">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h3 class="mb-1"><?php echo count(array_filter($solicitudes, fn($s) => in_array($s['estatus'], ['pendiente', 'asignado', 'en_progreso']))); ?></h3>
                        <p class="text-muted mb-0">En Proceso</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-success text-white">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <h3 class="mb-1"><?php echo count(array_filter($solicitudes, fn($s) => $s['estatus'] === 'completado')); ?></h3>
                        <p class="text-muted mb-0">Completados</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-info text-white">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <h3 class="mb-1">$<?php echo number_format(array_sum(array_column($solicitudes, 'costo_final')) ?: 0, 2); ?></h3>
                        <p class="text-muted mb-0">Total Pagado</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Buscar por servicio, mecánico o estatus">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="">Todos los estatus</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="asignado">Asignado</option>
                    <option value="en_progreso">En Progreso</option>
                    <option value="completado">Completado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="month" class="form-control" id="dateFilter">
            </div>
        </div>

        <!-- Services List -->
        <div class="row" id="servicesContainer">
            <?php foreach ($solicitudes as $servicio): ?>
                <div class="col-md-6 mb-3 service-item" data-status="<?php echo $servicio['estatus']; ?>" data-service="<?php echo strtolower($servicio['tipo_servicio']); ?>" data-mechanic="<?php echo strtolower($servicio['mecanico_nombre'] ?? ''); ?>" data-date="<?php echo date('Y-m', strtotime($servicio['fecha_solicitud'])); ?>">
                    <div class="service-card card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($servicio['tipo_servicio']); ?></h5>
                                <span class="status-badge badge bg-<?php echo getStatusColor($servicio['estatus']); ?>">
                                    <?php echo ucfirst($servicio['estatus']); ?>
                                </span>
                            </div>
                            
                            <p class="card-text text-muted"><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
                            
                            <div class="row text-sm">
                                <div class="col-6">
                                    <strong>Fecha:</strong><br>
                                    <span class="text-muted"><?php echo date('d/m/Y H:i', strtotime($servicio['fecha_programada'])); ?></span>
                                </div>
                                <div class="col-6">
                                    <strong>Mecánico:</strong><br>
                                    <span class="text-muted"><?php echo $servicio['mecanico_nombre'] ?? 'Sin asignar'; ?></span>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <strong>Costo:</strong> $<?php echo number_format($servicio['costo_final'] ?? $servicio['costo_estimado'], 2); ?>
                                    <br>
                                    <small class="payment-status">
                                        <?php if ($servicio['comprobante_pago']): ?>
                                            <i class="bi bi-check-circle-fill text-success"></i> Pagado
                                        <?php else: ?>
                                            <i class="bi bi-exclamation-circle-fill text-warning"></i> Pendiente
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="col-6 text-end">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewService(<?php echo $servicio['id']; ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if (!$servicio['comprobante_pago'] && $servicio['estatus'] !== 'cancelado'): ?>
                                            <button class="btn btn-sm btn-outline-success" onclick="uploadPayment(<?php echo $servicio['id']; ?>)">
                                                <i class="bi bi-upload"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="payWithPayPal(<?php echo $servicio['id']; ?>, <?php echo $servicio['costo_final'] ?? $servicio['costo_estimado']; ?>)">
                                                <i class="bi bi-paypal"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($servicio['estatus'] === 'completado' && !hasRating($servicio['id'])): ?>
                                            <button class="btn btn-sm btn-outline-info" onclick="rateService(<?php echo $servicio['id']; ?>)">
                                                <i class="bi bi-star"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($solicitudes)): ?>
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-clipboard-x display-1 text-muted"></i>
                </div>
                <h3 class="text-muted">No tienes servicios registrados</h3>
                <p class="text-muted mb-4">¡Solicita tu primer servicio mecánico ahora!</p>
                <a href="../views/solicitud/form.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle"></i> Solicitar Servicio
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Service Detail Modal -->
    <div class="modal fade" id="serviceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles del Servicio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="serviceModalBody">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Upload Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Subir Comprobante de Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm" enctype="multipart/form-data">
                        <input type="hidden" id="serviceId">
                        <div class="file-upload-area" onclick="document.getElementById('paymentFile').click()">
                            <i class="bi bi-cloud-upload display-4 text-muted"></i>
                            <h5>Arrastra tu comprobante aquí</h5>
                            <p class="text-muted">o haz clic para seleccionar archivo</p>
                            <input type="file" id="paymentFile" accept="image/*,.pdf" style="display: none;">
                        </div>
                        <div id="filePreview" class="mt-3"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="submitPayment()">Subir Comprobante</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Modal -->
    <div class="modal fade" id="ratingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Calificar Servicio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h6>¿Cómo fue tu experiencia?</h6>
                        <div class="rating-stars">
                            <i class="bi bi-star star" data-rating="1"></i>
                            <i class="bi bi-star star" data-rating="2"></i>
                            <i class="bi bi-star star" data-rating="3"></i>
                            <i class="bi bi-star star" data-rating="4"></i>
                            <i class="bi bi-star star" data-rating="5"></i>
                        </div>
                    </div>
                    <textarea class="form-control" id="ratingComment" rows="3" placeholder="Comparte tu experiencia..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="submitRating()">Enviar Calificación</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentServiceId = null;
        let selectedRating = 0;

        // Search and filter functionality
        document.getElementById('searchInput').addEventListener('input', filterServices);
        document.getElementById('statusFilter').addEventListener('change', filterServices);
        document.getElementById('dateFilter').addEventListener('change', filterServices);

        function filterServices() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const status = document.getElementById('statusFilter').value;
            const date = document.getElementById('dateFilter').value;

            document.querySelectorAll('.service-item').forEach(item => {
                const itemStatus = item.dataset.status;
                const itemService = item.dataset.service;
                const itemMechanic = item.dataset.mechanic;
                const itemDate = item.dataset.date;

                const matchesSearch = !search || 
                    itemService.includes(search) || 
                    itemMechanic.includes(search) || 
                    itemStatus.includes(search);
                
                const matchesStatus = !status || itemStatus === status;
                const matchesDate = !date || itemDate === date;

                item.style.display = matchesSearch && matchesStatus && matchesDate ? 'block' : 'none';
            });
        }

        function viewService(id) {
            // Load service details via AJAX
            fetch('../api/get_service_details.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ service_id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('serviceModalBody').innerHTML = generateServiceDetails(data.service);
                    new bootstrap.Modal(document.getElementById('serviceModal')).show();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function uploadPayment(id) {
            currentServiceId = id;
            document.getElementById('serviceId').value = id;
            new bootstrap.Modal(document.getElementById('paymentModal')).show();
        }

        function payWithPayPal(id, amount) {
            const paypalUrl = `https://paypal.me/ImpactosDigitales/${amount}`;
            window.open(paypalUrl, '_blank');
            
            // Show confirmation
            if (confirm('¿Ya realizaste el pago? Haz clic en OK para confirmar.')) {
                // Update payment status
                alert('Pago registrado. Por favor, sube tu comprobante para completar el proceso.');
                uploadPayment(id);
            }
        }

        function rateService(id) {
            currentServiceId = id;
            selectedRating = 0;
            document.getElementById('ratingComment').value = '';
            updateStars();
            new bootstrap.Modal(document.getElementById('ratingModal')).show();
        }

        function submitPayment() {
            const fileInput = document.getElementById('paymentFile');
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Por favor selecciona un archivo');
                return;
            }

            const formData = new FormData();
            formData.append('service_id', currentServiceId);
            formData.append('payment_proof', file);

            fetch('../api/upload_payment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Comprobante subido exitosamente');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al subir el archivo');
            });
        }

        function submitRating() {
            if (selectedRating === 0) {
                alert('Por favor selecciona una calificación');
                return;
            }

            const comment = document.getElementById('ratingComment').value;

            fetch('../api/submit_rating.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    service_id: currentServiceId,
                    rating: selectedRating,
                    comment: comment
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('¡Gracias por tu calificación!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al enviar la calificación');
            });
        }

        // Star rating functionality
        document.querySelectorAll('.star').forEach(star => {
            star.addEventListener('click', function() {
                selectedRating = parseInt(this.dataset.rating);
                updateStars();
            });
        });

        function updateStars() {
            document.querySelectorAll('.star').forEach((star, index) => {
                if (index < selectedRating) {
                    star.className = 'bi bi-star-fill star text-warning';
                } else {
                    star.className = 'bi bi-star star text-muted';
                }
            });
        }

        function generateServiceDetails(service) {
            return `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Información del Servicio</h6>
                        <p><strong>Tipo:</strong> ${service.tipo_servicio}</p>
                        <p><strong>Estado:</strong> <span class="badge bg-${getStatusColor(service.estatus)}">${service.estatus}</span></p>
                        <p><strong>Fecha Programada:</strong> ${new Date(service.fecha_programada).toLocaleString()}</p>
                        <p><strong>Descripción:</strong> ${service.descripcion}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Información del Mecánico</h6>
                        <p><strong>Nombre:</strong> ${service.mecanico_nombre || 'Sin asignar'}</p>
                        <p><strong>Dirección:</strong> ${service.direccion_completa}</p>
                        <p><strong>Costo:</strong> $${parseFloat(service.costo_final || service.costo_estimado).toLocaleString()}</p>
                    </div>
                </div>
            `;
        }

        function showProfile() {
            alert('Configuración de perfil en desarrollo');
        }

        function changePassword() {
            alert('Cambio de contraseña en desarrollo');
        }

        // File upload preview
        document.getElementById('paymentFile').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('filePreview');
            
            if (file) {
                preview.innerHTML = `
                    <div class="alert alert-info">
                        <i class="bi bi-file-earmark"></i> ${file.name}
                        <small class="d-block">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                    </div>
                `;
            }
        });
    </script>
</body>
</html>

<?php
function getStatusColor($status) {
    $colors = [
        'pendiente' => 'warning',
        'asignado' => 'info',
        'en_progreso' => 'primary',
        'completado' => 'success',
        'cancelado' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}

function hasRating($serviceId) {
    // This would check if the service has been rated
    // For now, return false
    return false;
}
?>