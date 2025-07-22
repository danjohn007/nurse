<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Servicio - MechanicalFix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: white;
            padding: 4rem 0;
            margin-bottom: 3rem;
        }
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .logo {
            max-width: 200px;
            height: auto;
        }
        .service-form {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: -100px;
            position: relative;
            z-index: 2;
        }
        .btn-primary {
            background: #dc3545;
            border-color: #dc3545;
        }
        .btn-primary:hover {
            background: #c82333;
            border-color: #c82333;
        }
        .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        #map {
            height: 300px;
            border-radius: 10px;
            margin-top: 1rem;
        }
        .loading {
            display: none;
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="container">
            <div class="logo-container">
                <svg width="200" height="100" viewBox="0 0 400 200" xmlns="http://www.w3.org/2000/svg">
                    <!-- Wrench Icon -->
                    <g transform="translate(40, 60)">
                        <rect x="0" y="10" width="60" height="15" fill="#000" stroke="#fff" stroke-width="3"/>
                        <rect x="0" y="25" width="60" height="15" fill="#000" stroke="#fff" stroke-width="3"/>
                        <rect x="55" y="0" width="20" height="55" fill="#000" stroke="#fff" stroke-width="3"/>
                        <rect x="75" y="15" width="15" height="25" fill="#000" stroke="#fff" stroke-width="3"/>
                    </g>
                    
                    <!-- Shield Background -->
                    <g transform="translate(140, 30)">
                        <path d="M0,20 L20,0 L80,0 L100,20 L100,60 L50,90 L0,60 Z" fill="#dc3545" stroke="#000" stroke-width="3"/>
                        <text x="50" y="45" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="24" font-weight="bold">MX</text>
                    </g>
                    
                    <!-- Another Wrench -->
                    <g transform="translate(280, 60) rotate(45)">
                        <rect x="0" y="10" width="60" height="15" fill="#000" stroke="#fff" stroke-width="3"/>
                        <rect x="0" y="25" width="60" height="15" fill="#000" stroke="#fff" stroke-width="3"/>
                        <rect x="55" y="0" width="20" height="55" fill="#000" stroke="#fff" stroke-width="3"/>
                        <rect x="75" y="15" width="15" height="25" fill="#000" stroke="#fff" stroke-width="3"/>
                    </g>
                    
                    <!-- Text -->
                    <text x="200" y="150" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="28" font-weight="bold">MECHANICALFIX</text>
                </svg>
            </div>
            <div class="text-center">
                <h1 class="display-4 fw-bold">Servicio Mecánico a Domicilio</h1>
                <p class="lead">Solicita un mecánico profesional las 24 horas del día</p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="service-form">
                    <h2 class="text-center mb-4">Solicitar Servicio de Mecánico</h2>
                    
                    <!-- Search Phone Section -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <label for="buscar_telefono" class="form-label">Buscar por Teléfono</label>
                            <input type="tel" class="form-control" id="buscar_telefono" placeholder="Ingresa tu teléfono para cargar tus datos">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="buscarTelefono()">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </div>

                    <form id="solicitudForm" onsubmit="return submitForm(event)">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre Completo *</label>
                                <input type="text" class="form-control" id="nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono *</label>
                                <input type="tel" class="form-control" id="telefono" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="empresa" class="form-label">Empresa (Opcional)</label>
                                <input type="text" class="form-control" id="empresa">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="tipo_servicio" class="form-label">Tipo de Servicio *</label>
                            <select class="form-select" id="tipo_servicio" required onchange="updateCosto()">
                                <option value="">Selecciona un servicio</option>
                                <option value="Cambio de Aceite" data-precio="800">Cambio de Aceite - $800</option>
                                <option value="Revisión de Frenos" data-precio="1200">Revisión de Frenos - $1,200</option>
                                <option value="Diagnóstico General" data-precio="600">Diagnóstico General - $600</option>
                                <option value="Reparación de Motor" data-precio="5000">Reparación de Motor - $5,000</option>
                                <option value="Cambio de Batería" data-precio="1500">Cambio de Batería - $1,500</option>
                                <option value="Alineación y Balanceo" data-precio="900">Alineación y Balanceo - $900</option>
                                <option value="Reparación de Transmisión" data-precio="8000">Reparación de Transmisión - $8,000</option>
                                <option value="Servicio de Emergencia" data-precio="2000">Servicio de Emergencia - $2,000</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción del Problema *</label>
                            <textarea class="form-control" id="descripcion" rows="4" required placeholder="Describe detalladamente el problema de tu vehículo"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección Completa *</label>
                            <input type="text" class="form-control" id="direccion" required placeholder="Calle, número, colonia, ciudad">
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="obtenerUbicacion()">
                                <i class="bi bi-geo-alt"></i> Usar mi ubicación actual
                            </button>
                        </div>

                        <div id="map"></div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_programada" class="form-label">Fecha y Hora Preferida *</label>
                                <input type="datetime-local" class="form-control" id="fecha_programada" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="prioridad" class="form-label">Prioridad</label>
                                <select class="form-select" id="prioridad">
                                    <option value="media">Media</option>
                                    <option value="baja">Baja</option>
                                    <option value="alta">Alta</option>
                                    <option value="urgente">Urgente (+50% costo)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="metodo_pago" class="form-label">Método de Pago Preferido</label>
                                <select class="form-select" id="metodo_pago">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="paypal">PayPal</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="costo_estimado" class="form-label">Costo Estimado</label>
                                <input type="text" class="form-control" id="costo_estimado" readonly placeholder="$0.00">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notas" class="form-label">Notas Adicionales</label>
                            <textarea class="form-control" id="notas" rows="3" placeholder="Información adicional que pueda ser útil para el mecánico"></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <span class="loading">
                                    <i class="bi bi-hourglass-split"></i> Procesando...
                                </span>
                                <span class="submit-text">
                                    <i class="bi bi-tools"></i> Solicitar Servicio
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-check-circle-fill text-success"></i> Solicitud Enviada
                    </h5>
                </div>
                <div class="modal-body">
                    <p>¡Solicitud enviada con éxito! Un mecánico será asignado pronto.</p>
                    <p>Te hemos enviado un email con tus credenciales de acceso para que puedas dar seguimiento a tu solicitud.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="redirectToLogin()">
                        Ir al Dashboard
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Missing Data Modal -->
    <div class="modal fade" id="missingDataModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill text-warning"></i> Datos Faltantes
                    </h5>
                </div>
                <div class="modal-body">
                    <p>Por favor completa los siguientes campos requeridos:</p>
                    <ul id="missingFieldsList"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let map;
        let marker;

        // Initialize map
        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 13,
                center: { lat: 19.432608, lng: -99.133209 }, // Mexico City default
            });

            marker = new google.maps.Marker({
                position: { lat: 19.432608, lng: -99.133209 },
                map: map,
                draggable: true
            });

            // Update address when marker is dragged
            marker.addListener('dragend', function() {
                geocodePosition(marker.getPosition());
            });

            // Add click listener to map
            map.addListener('click', function(e) {
                marker.setPosition(e.latLng);
                geocodePosition(e.latLng);
            });
        }

        function geocodePosition(pos) {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({
                latLng: pos
            }, function(responses) {
                if (responses && responses.length > 0) {
                    document.getElementById('direccion').value = responses[0].formatted_address;
                }
            });
        }

        function obtenerUbicacion() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    map.setCenter(pos);
                    marker.setPosition(pos);
                    geocodePosition(pos);
                });
            } else {
                alert("La geolocalización no es soportada por este navegador.");
            }
        }

        function buscarTelefono() {
            const telefono = document.getElementById('buscar_telefono').value;
            if (!telefono) {
                alert('Por favor ingresa un teléfono');
                return;
            }

            fetch('../controllers/SolicitudController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'buscar_telefono',
                    telefono: telefono
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    document.getElementById('nombre').value = data.data.nombre || '';
                    document.getElementById('telefono').value = data.data.telefono || '';
                    document.getElementById('email').value = data.data.email || '';
                    document.getElementById('empresa').value = data.data.empresa || '';
                } else {
                    alert('No se encontraron datos para este teléfono');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al buscar los datos');
            });
        }

        function updateCosto() {
            const select = document.getElementById('tipo_servicio');
            const option = select.options[select.selectedIndex];
            const precio = option.getAttribute('data-precio') || '0';
            const prioridad = document.getElementById('prioridad').value;
            
            let costoFinal = parseInt(precio);
            if (prioridad === 'urgente') {
                costoFinal = costoFinal * 1.5;
            }
            
            document.getElementById('costo_estimado').value = '$' + costoFinal.toLocaleString();
        }

        function validateForm() {
            const required = ['nombre', 'telefono', 'email', 'tipo_servicio', 'descripcion', 'direccion', 'fecha_programada'];
            const missing = [];

            required.forEach(field => {
                const element = document.getElementById(field);
                if (!element.value.trim()) {
                    missing.push(element.previousElementSibling.textContent.replace(' *', ''));
                }
            });

            if (missing.length > 0) {
                const list = document.getElementById('missingFieldsList');
                list.innerHTML = '';
                missing.forEach(field => {
                    const li = document.createElement('li');
                    li.textContent = field;
                    list.appendChild(li);
                });
                
                new bootstrap.Modal(document.getElementById('missingDataModal')).show();
                return false;
            }

            return true;
        }

        function submitForm(event) {
            event.preventDefault();
            
            if (!validateForm()) return false;

            const submitBtn = document.querySelector('button[type="submit"]');
            const loading = submitBtn.querySelector('.loading');
            const submitText = submitBtn.querySelector('.submit-text');
            
            loading.style.display = 'inline';
            submitText.style.display = 'none';
            submitBtn.disabled = true;

            const formData = {
                action: 'create',
                nombre: document.getElementById('nombre').value,
                telefono: document.getElementById('telefono').value,
                email: document.getElementById('email').value,
                empresa: document.getElementById('empresa').value,
                tipo_servicio: document.getElementById('tipo_servicio').value,
                descripcion: document.getElementById('descripcion').value,
                direccion: document.getElementById('direccion').value,
                fecha_programada: document.getElementById('fecha_programada').value,
                prioridad: document.getElementById('prioridad').value,
                metodo_pago: document.getElementById('metodo_pago').value,
                costo_estimado: document.getElementById('costo_estimado').value.replace(/[^0-9.]/g, ''),
                notas: document.getElementById('notas').value,
                ubicacion_lat: marker.getPosition().lat(),
                ubicacion_lng: marker.getPosition().lng()
            };

            fetch('../controllers/SolicitudController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    new bootstrap.Modal(document.getElementById('successModal')).show();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al enviar la solicitud');
            })
            .finally(() => {
                loading.style.display = 'none';
                submitText.style.display = 'inline';
                submitBtn.disabled = false;
            });

            return false;
        }

        function redirectToLogin() {
            window.location.href = '../public/login.php';
        }

        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('fecha_programada').min = now.toISOString().slice(0, 16);

            // Update priority change listener
            document.getElementById('prioridad').addEventListener('change', updateCosto);
        });
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap"></script>
</body>
</html>