<?php
session_start();
require_once '../controllers/AuthController.php';
requireAuth(['admin']);

require_once '../config/database.php';
require_once '../models/Solicitud.php';
require_once '../models/Usuario.php';

$database = new Database();
$db = $database->getConnection();
$solicitud = new Solicitud($db);
$usuario = new Usuario($db);

// Get statistics
$stats = $solicitud->getEstadisticas();
$clientStats = $usuario->getEstadisticasClientes();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - MechanicalFix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #dc3545 0%, #fd7e14 100%);
            color: white;
        }
        .sidebar .nav-link {
            color: white;
            border-radius: 8px;
            margin: 2px 0;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .stat-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                z-index: 1000;
                transition: left 0.3s;
                width: 280px;
            }
            .sidebar.show {
                left: 0;
            }
            .overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 999;
                display: none;
            }
            .overlay.show {
                display: block;
            }
        }
        .navbar-mobile {
            background: #dc3545 !important;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
    <!-- Mobile navbar -->
    <nav class="navbar navbar-mobile d-md-none">
        <div class="container-fluid">
            <button class="btn btn-outline-light" type="button" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <span class="navbar-brand text-white mb-0 h1">MechanicalFix Admin</span>
            <a href="../controllers/AuthController.php?action=logout" class="btn btn-outline-light">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </nav>

    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-3" id="sidebar">
                <div class="d-flex align-items-center mb-4">
                    <div class="me-3">
                        <svg width="40" height="30" viewBox="0 0 100 60" xmlns="http://www.w3.org/2000/svg">
                            <g transform="translate(10, 10)">
                                <path d="M0,5 L5,0 L20,0 L25,5 L25,15 L12.5,22.5 L0,15 Z" fill="white"/>
                                <text x="12.5" y="12" text-anchor="middle" fill="#dc3545" font-family="Arial" font-size="8" font-weight="bold">MX</text>
                            </g>
                        </svg>
                    </div>
                    <h5 class="mb-0">MechanicalFix</h5>
                </div>

                <nav class="nav flex-column">
                    <a class="nav-link active" href="#" onclick="showSection('dashboard')">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="#" onclick="showSection('solicitudes')">
                        <i class="bi bi-clipboard-check me-2"></i> Solicitudes
                    </a>
                    <a class="nav-link" href="#" onclick="showSection('mecanicos')">
                        <i class="bi bi-people me-2"></i> Mecánicos
                    </a>
                    <a class="nav-link" href="#" onclick="showSection('clientes')">
                        <i class="bi bi-person-lines-fill me-2"></i> Clientes/CRM
                    </a>
                    <a class="nav-link" href="#" onclick="showSection('reportes')">
                        <i class="bi bi-graph-up me-2"></i> Reportes
                    </a>
                    <a class="nav-link" href="#" onclick="showSection('finanzas')">
                        <i class="bi bi-cash-stack me-2"></i> Finanzas
                    </a>
                    <a class="nav-link" href="#" onclick="showSection('servicios')">
                        <i class="bi bi-tools me-2"></i> Catálogo Servicios
                    </a>
                    <a class="nav-link" href="#" onclick="showSection('configuracion')">
                        <i class="bi bi-gear me-2"></i> Configuración
                    </a>
                </nav>

                <hr class="text-white">

                <div class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-2"></i> <?php echo $_SESSION['user_name']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="showProfile()">
                            <i class="bi bi-person"></i> Perfil
                        </a></li>
                        <li><a class="dropdown-item" href="../controllers/AuthController.php?action=logout">
                            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                        </a></li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <div class="col-md-9 col-lg-10 main-content p-4">
                <!-- Dashboard Section -->
                <div id="dashboard-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Dashboard Administrativo</h2>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" onclick="refreshData()">
                                <i class="bi bi-arrow-clockwise"></i> Actualizar
                            </button>
                            <button class="btn btn-success" onclick="exportData()">
                                <i class="bi bi-download"></i> Exportar
                            </button>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-primary text-white me-3">
                                            <i class="bi bi-clipboard-check"></i>
                                        </div>
                                        <div>
                                            <h6 class="card-title text-muted mb-0">Total Solicitudes</h6>
                                            <h3 class="mb-0"><?php echo number_format($stats['total']); ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-warning text-white me-3">
                                            <i class="bi bi-clock-history"></i>
                                        </div>
                                        <div>
                                            <h6 class="card-title text-muted mb-0">Pendientes</h6>
                                            <h3 class="mb-0"><?php echo number_format($stats['pendientes']); ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-success text-white me-3">
                                            <i class="bi bi-check-circle"></i>
                                        </div>
                                        <div>
                                            <h6 class="card-title text-muted mb-0">Completados</h6>
                                            <h3 class="mb-0"><?php echo number_format($stats['completados']); ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-info text-white me-3">
                                            <i class="bi bi-cash-stack"></i>
                                        </div>
                                        <div>
                                            <h6 class="card-title text-muted mb-0">Ingresos Totales</h6>
                                            <h3 class="mb-0">$<?php echo number_format($stats['ingresos_totales'] ?? 0, 2); ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Solicitudes por Estatus</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="statusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Ingresos por Mes</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="incomeChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Requests -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Solicitudes Recientes</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="recentRequestsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Servicio</th>
                                            <th>Fecha</th>
                                            <th>Estatus</th>
                                            <th>Mecánico</th>
                                            <th>Costo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="requestsTableBody">
                                        <!-- Data loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Other sections will be loaded dynamically -->
                <div id="solicitudes-section" style="display: none;">
                    <h2>Gestión de Solicitudes</h2>
                    <!-- Solicitudes content -->
                </div>

                <div id="mecanicos-section" style="display: none;">
                    <h2>Gestión de Mecánicos</h2>
                    <!-- Mecánicos content -->
                </div>

                <div id="clientes-section" style="display: none;">
                    <h2>CRM - Gestión de Clientes</h2>
                    <!-- Clientes content -->
                </div>

                <div id="reportes-section" style="display: none;">
                    <h2>Reportes y Análisis</h2>
                    <!-- Reportes content -->
                </div>

                <div id="finanzas-section" style="display: none;">
                    <h2>Gestión Financiera</h2>
                    <!-- Finanzas content -->
                </div>

                <div id="servicios-section" style="display: none;">
                    <h2>Catálogo de Servicios</h2>
                    <!-- Servicios content -->
                </div>

                <div id="configuracion-section" style="display: none;">
                    <h2>Configuración del Sistema</h2>
                    <!-- Configuración content -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let statusChart, incomeChart;

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        function showSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('[id$="-section"]').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show selected section
            document.getElementById(sectionName + '-section').style.display = 'block';
            
            // Update active nav link
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            event.target.closest('.nav-link').classList.add('active');
            
            // Close sidebar on mobile
            if (window.innerWidth < 768) {
                toggleSidebar();
            }
        }

        function initCharts() {
            // Status Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Pendientes', 'Asignados', 'En Progreso', 'Completados', 'Cancelados'],
                    datasets: [{
                        data: [
                            <?php echo $stats['pendientes']; ?>,
                            <?php echo $stats['asignados']; ?>,
                            <?php echo $stats['en_progreso']; ?>,
                            <?php echo $stats['completados']; ?>,
                            <?php echo $stats['cancelados']; ?>
                        ],
                        backgroundColor: [
                            '#ffc107',
                            '#17a2b8',
                            '#fd7e14',
                            '#28a745',
                            '#dc3545'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Income Chart
            const incomeCtx = document.getElementById('incomeChart').getContext('2d');
            incomeChart = new Chart(incomeCtx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Ingresos',
                        data: [50000, 75000, 60000, 90000, 85000, 100000],
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        function loadRecentRequests() {
            fetch('../controllers/SolicitudController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'list' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const tbody = document.getElementById('requestsTableBody');
                    tbody.innerHTML = '';
                    
                    data.data.slice(0, 10).forEach(request => {
                        const row = tbody.insertRow();
                        row.innerHTML = `
                            <td>${request.id}</td>
                            <td>${request.cliente_nombre || 'N/A'}</td>
                            <td>${request.tipo_servicio}</td>
                            <td>${new Date(request.fecha_solicitud).toLocaleDateString()}</td>
                            <td><span class="badge bg-${getStatusColor(request.estatus)}">${request.estatus}</span></td>
                            <td>${request.mecanico_nombre || 'Sin asignar'}</td>
                            <td>$${parseFloat(request.costo_estimado || 0).toLocaleString()}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewRequest(${request.id})">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="editRequest(${request.id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </td>
                        `;
                    });
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function getStatusColor(status) {
            const colors = {
                'pendiente': 'warning',
                'asignado': 'info',
                'en_progreso': 'primary',
                'completado': 'success',
                'cancelado': 'danger'
            };
            return colors[status] || 'secondary';
        }

        function refreshData() {
            location.reload();
        }

        function exportData() {
            alert('Función de exportación en desarrollo');
        }

        function viewRequest(id) {
            alert('Ver solicitud #' + id);
        }

        function editRequest(id) {
            alert('Editar solicitud #' + id);
        }

        function showProfile() {
            alert('Configuración de perfil en desarrollo');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
            loadRecentRequests();
        });
    </script>
</body>
</html>