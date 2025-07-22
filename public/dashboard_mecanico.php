<?php
session_start();
require_once '../controllers/AuthController.php';
requireAuth(['mecanico']);

require_once '../config/database.php';
require_once '../models/Solicitud.php';
require_once '../models/Usuario.php';

$database = new Database();
$db = $database->getConnection();
$solicitud = new Solicitud($db);
$usuario = new Usuario($db);

// Get mechanic's data
$mechanicData = $usuario->getMecanicoById($_SESSION['user_id']);

// Get mechanic's assigned services
$stmt = $solicitud->readByMecanico($_SESSION['user_id']);
$servicios = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $servicios[] = $row;
}

// Calculate earnings (70% of service cost)
$totalEarnings = array_sum(array_map(function($s) {
    return ($s['costo_final'] ?? 0) * 0.7;
}, $servicios));

$completedServices = count(array_filter($servicios, fn($s) => $s['estatus'] === 'completado'));
$avgRating = $mechanicData['calificacion_promedio'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mecánico - MechanicalFix</title>
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
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            height: 100%;
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
        .chart-container {
            position: relative;
            height: 300px;
        }
        .rating-display {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .priority-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .availability-toggle {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .availability-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #28a745;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>&background=fff&color=dc3545&size=80" 
                                 class="rounded-circle border border-white border-3" alt="Avatar">
                        </div>
                        <div>
                            <h1 class="mb-0"><?php echo $_SESSION['user_name']; ?></h1>
                            <p class="mb-1 opacity-75">Mecánico Profesional</p>
                            <div class="rating-display">
                                <div class="text-warning">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?php echo $i <= $avgRating ? '-fill' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span><?php echo number_format($avgRating, 1); ?> (<?php echo $mechanicData['total_servicios'] ?? 0; ?> servicios)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="d-flex flex-column align-items-md-end gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span>Disponible:</span>
                            <label class="availability-toggle">
                                <input type="checkbox" <?php echo ($mechanicData['disponible'] ?? true) ? 'checked' : ''; ?> onchange="toggleAvailability()">
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> Menú
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="showProfile()">
                                    <i class="bi bi-person"></i> Mi Perfil
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="updateLocation()">
                                    <i class="bi bi-geo-alt"></i> Actualizar Ubicación
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
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary text-white">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <h3 class="mb-1"><?php echo count($servicios); ?></h3>
                    <p class="text-muted mb-0">Servicios Asignados</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success text-white">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $completedServices; ?></h3>
                    <p class="text-muted mb-0">Completados</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-info text-white">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <h3 class="mb-1">$<?php echo number_format($totalEarnings, 2); ?></h3>
                    <p class="text-muted mb-0">Ganancias (70%)</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning text-white">
                        <i class="bi bi-star"></i>
                    </div>
                    <h3 class="mb-1"><?php echo number_format($avgRating, 1); ?></h3>
                    <p class="text-muted mb-0">Calificación Promedio</p>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Ganancias por Mes</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="earningsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Servicios por Tipo</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="servicesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Tendencia de Calificaciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="ratingsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Eficiencia Semanal</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="efficiencyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Section -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Mis Servicios Asignados</h3>
            <div class="d-flex gap-2">
                <select class="form-select" id="statusFilter" style="width: auto;">
                    <option value="">Todos los estatus</option>
                    <option value="asignado">Asignado</option>
                    <option value="en_progreso">En Progreso</option>
                    <option value="completado">Completado</option>
                </select>
                <button class="btn btn-primary" onclick="refreshServices()">
                    <i class="bi bi-arrow-clockwise"></i> Actualizar
                </button>
            </div>
        </div>

        <div class="row" id="servicesContainer">
            <?php foreach ($servicios as $servicio): ?>
                <div class="col-md-6 mb-3 service-item" data-status="<?php echo $servicio['estatus']; ?>">
                    <div class="service-card card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($servicio['tipo_servicio']); ?></h5>
                                <div class="d-flex gap-1 align-items-center">
                                    <span class="badge bg-<?php echo getStatusColor($servicio['estatus']); ?>">
                                        <?php echo ucfirst($servicio['estatus']); ?>
                                    </span>
                                    <?php if ($servicio['prioridad'] === 'urgente'): ?>
                                        <span class="badge bg-danger priority-badge">URGENTE</span>
                                    <?php elseif ($servicio['prioridad'] === 'alta'): ?>
                                        <span class="badge bg-warning priority-badge">ALTA</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <p class="card-text text-muted small"><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
                            
                            <div class="row text-sm mb-3">
                                <div class="col-6">
                                    <strong>Cliente:</strong><br>
                                    <span class="text-muted"><?php echo htmlspecialchars($servicio['cliente_nombre']); ?></span><br>
                                    <small><i class="bi bi-telephone"></i> <?php echo $servicio['cliente_telefono']; ?></small>
                                </div>
                                <div class="col-6">
                                    <strong>Programado:</strong><br>
                                    <span class="text-muted"><?php echo date('d/m/Y H:i', strtotime($servicio['fecha_programada'])); ?></span>
                                </div>
                            </div>

                            <div class="row text-sm mb-3">
                                <div class="col-12">
                                    <strong>Ubicación:</strong><br>
                                    <span class="text-muted"><?php echo htmlspecialchars($servicio['direccion_completa']); ?></span>
                                    <a href="https://maps.google.com/?q=<?php echo $servicio['ubicacion_lat']; ?>,<?php echo $servicio['ubicacion_lng']; ?>" 
                                       target="_blank" class="btn btn-sm btn-outline-info ms-2">
                                        <i class="bi bi-map"></i> Ver Mapa
                                    </a>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <strong>Costo Total:</strong> $<?php echo number_format($servicio['costo_final'] ?? $servicio['costo_estimado'], 2); ?><br>
                                    <small class="text-success">Tu ganancia: $<?php echo number_format(($servicio['costo_final'] ?? $servicio['costo_estimado']) * 0.7, 2); ?> (70%)</small>
                                </div>
                                <div class="col-6 text-end">
                                    <?php if ($servicio['estatus'] === 'asignado'): ?>
                                        <button class="btn btn-success btn-sm" onclick="startService(<?php echo $servicio['id']; ?>)">
                                            <i class="bi bi-play-circle"></i> Iniciar
                                        </button>
                                    <?php elseif ($servicio['estatus'] === 'en_progreso'): ?>
                                        <button class="btn btn-primary btn-sm" onclick="completeService(<?php echo $servicio['id']; ?>)">
                                            <i class="bi bi-check-circle"></i> Completar
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="addNote(<?php echo $servicio['id']; ?>)">
                                        <i class="bi bi-chat-left-text"></i> Nota
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($servicios)): ?>
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-tools display-1 text-muted"></i>
                </div>
                <h3 class="text-muted">No tienes servicios asignados</h3>
                <p class="text-muted">Los servicios aparecerán aquí cuando sean asignados por el administrador.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Note Modal -->
    <div class="modal fade" id="noteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Nota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" id="serviceNote" rows="4" placeholder="Agrega una nota sobre el servicio..."></textarea>
                    <input type="hidden" id="noteServiceId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="submitNote()">Guardar Nota</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Service Modal -->
    <div class="modal fade" id="completeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Completar Servicio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="finalCost" class="form-label">Costo Final</label>
                        <input type="number" class="form-control" id="finalCost" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label for="completionNote" class="form-label">Notas de Completado</label>
                        <textarea class="form-control" id="completionNote" rows="3" placeholder="Describe el trabajo realizado..."></textarea>
                    </div>
                    <input type="hidden" id="completeServiceId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="submitCompletion()">Marcar como Completado</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', function() {
            const status = this.value;
            document.querySelectorAll('.service-item').forEach(item => {
                const itemStatus = item.dataset.status;
                item.style.display = !status || itemStatus === status ? 'block' : 'none';
            });
        });

        function toggleAvailability() {
            // Update availability status via AJAX
            fetch('../api/update_availability.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ available: event.target.checked })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Error al actualizar disponibilidad');
                    event.target.checked = !event.target.checked;
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function startService(id) {
            if (confirm('¿Deseas iniciar este servicio?')) {
                updateServiceStatus(id, 'en_progreso');
            }
        }

        function completeService(id) {
            document.getElementById('completeServiceId').value = id;
            // Load current estimated cost
            document.getElementById('finalCost').value = '';
            new bootstrap.Modal(document.getElementById('completeModal')).show();
        }

        function addNote(id) {
            document.getElementById('noteServiceId').value = id;
            document.getElementById('serviceNote').value = '';
            new bootstrap.Modal(document.getElementById('noteModal')).show();
        }

        function submitNote() {
            const serviceId = document.getElementById('noteServiceId').value;
            const note = document.getElementById('serviceNote').value;

            if (!note.trim()) {
                alert('Por favor ingresa una nota');
                return;
            }

            fetch('../api/add_service_note.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ service_id: serviceId, note: note })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Nota agregada exitosamente');
                    bootstrap.Modal.getInstance(document.getElementById('noteModal')).hide();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function submitCompletion() {
            const serviceId = document.getElementById('completeServiceId').value;
            const finalCost = document.getElementById('finalCost').value;
            const note = document.getElementById('completionNote').value;

            if (!finalCost) {
                alert('Por favor ingresa el costo final');
                return;
            }

            fetch('../api/complete_service.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    service_id: serviceId, 
                    final_cost: finalCost,
                    note: note
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Servicio completado exitosamente');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function updateServiceStatus(id, status) {
            fetch('../controllers/SolicitudController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    action: 'update_status',
                    id: id, 
                    estatus: status,
                    usuario_id: <?php echo $_SESSION['user_id']; ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function refreshServices() {
            location.reload();
        }

        function showProfile() {
            alert('Configuración de perfil en desarrollo');
        }

        function updateLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    fetch('../api/update_location.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Ubicación actualizada exitosamente');
                        } else {
                            alert('Error al actualizar ubicación');
                        }
                    });
                });
            } else {
                alert('Geolocalización no soportada');
            }
        }

        function changePassword() {
            alert('Cambio de contraseña en desarrollo');
        }

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
        });

        function initCharts() {
            // Earnings Chart
            const earningsCtx = document.getElementById('earningsChart').getContext('2d');
            new Chart(earningsCtx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Ganancias',
                        data: [5000, 7500, 6000, 9000, 8500, 10000],
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Services Chart
            const servicesCtx = document.getElementById('servicesChart').getContext('2d');
            new Chart(servicesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Cambio de Aceite', 'Frenos', 'Diagnóstico', 'Reparaciones', 'Otros'],
                    datasets: [{
                        data: [25, 20, 15, 30, 10],
                        backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6c757d']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Ratings Chart
            const ratingsCtx = document.getElementById('ratingsChart').getContext('2d');
            new Chart(ratingsCtx, {
                type: 'bar',
                data: {
                    labels: ['1★', '2★', '3★', '4★', '5★'],
                    datasets: [{
                        label: 'Calificaciones',
                        data: [0, 1, 3, 8, 12],
                        backgroundColor: '#ffc107'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Efficiency Chart
            const efficiencyCtx = document.getElementById('efficiencyChart').getContext('2d');
            new Chart(efficiencyCtx, {
                type: 'radar',
                data: {
                    labels: ['Puntualidad', 'Calidad', 'Comunicación', 'Eficiencia', 'Satisfacción'],
                    datasets: [{
                        label: 'Tu Performance',
                        data: [4.5, 4.8, 4.2, 4.6, 4.7],
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.2)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 5
                        }
                    }
                }
            });
        }
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
?>