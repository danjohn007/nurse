-- MechanicalFix Database Schema
CREATE DATABASE IF NOT EXISTS mechanicalfix_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mechanicalfix_db;

-- Table for users (admin, clients, mechanics)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('admin', 'cliente', 'mecanico') NOT NULL,
    empresa VARCHAR(100),
    direccion TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estatus ENUM('activo', 'inactivo', 'suspendido') DEFAULT 'activo',
    avatar VARCHAR(255),
    fecha_nacimiento DATE,
    genero ENUM('M', 'F', 'Otro')
);

-- Table for mechanics with additional info
CREATE TABLE mecanicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    especialidades TEXT, -- JSON array of specialties
    experiencia_anos INT DEFAULT 0,
    certificaciones TEXT, -- JSON array of certifications
    calificacion_promedio DECIMAL(3,2) DEFAULT 0.00,
    total_servicios INT DEFAULT 0,
    ingresos_totales DECIMAL(10,2) DEFAULT 0.00,
    disponible BOOLEAN DEFAULT TRUE,
    ubicacion_lat DECIMAL(10, 8),
    ubicacion_lng DECIMAL(11, 8),
    radio_trabajo INT DEFAULT 10, -- km
    tarifa_base DECIMAL(8,2) DEFAULT 0.00,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Table for service requests
CREATE TABLE solicitudes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    mecanico_id INT NULL,
    tipo_servicio VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    ubicacion_lat DECIMAL(10, 8),
    ubicacion_lng DECIMAL(11, 8),
    direccion_completa TEXT,
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_programada DATETIME,
    fecha_completado DATETIME NULL,
    estatus ENUM('pendiente', 'asignado', 'en_progreso', 'completado', 'cancelado') DEFAULT 'pendiente',
    costo_estimado DECIMAL(8,2),
    costo_final DECIMAL(8,2),
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'paypal') DEFAULT 'efectivo',
    comprobante_pago VARCHAR(255),
    notas_cliente TEXT,
    notas_mecanico TEXT,
    prioridad ENUM('baja', 'media', 'alta', 'urgente') DEFAULT 'media',
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (mecanico_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Table for activity comments/logs
CREATE TABLE actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NOT NULL,
    fecha_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo_actividad ENUM('comentario', 'cambio_estatus', 'asignacion', 'pago', 'calificacion') DEFAULT 'comentario',
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Table for ratings
CREATE TABLE calificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT NOT NULL,
    cliente_id INT NOT NULL,
    mecanico_id INT NOT NULL,
    puntuacion INT CHECK (puntuacion >= 1 AND puntuacion <= 5),
    comentario TEXT,
    fecha_calificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (mecanico_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Table for payments
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT NOT NULL,
    monto DECIMAL(8,2) NOT NULL,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'paypal') NOT NULL,
    referencia_pago VARCHAR(100),
    comprobante VARCHAR(255),
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estatus_pago ENUM('pendiente', 'completado', 'fallido', 'reembolsado') DEFAULT 'pendiente',
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id) ON DELETE CASCADE
);

-- Table for services catalog
CREATE TABLE servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio_base DECIMAL(8,2) NOT NULL,
    duracion_estimada INT, -- in minutes
    categoria ENUM('mantenimiento', 'reparacion', 'diagnostico', 'instalacion', 'emergencia') NOT NULL,
    activo BOOLEAN DEFAULT TRUE
);

-- Insert default admin user
INSERT INTO usuarios (nombre, email, telefono, password, tipo_usuario) VALUES 
('Administrador', 'admin@mechanicalfix.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample services
INSERT INTO servicios (nombre, descripcion, precio_base, duracion_estimada, categoria) VALUES
('Cambio de Aceite', 'Cambio de aceite de motor y filtro', 800.00, 60, 'mantenimiento'),
('Revisión de Frenos', 'Inspección y mantenimiento del sistema de frenos', 1200.00, 90, 'mantenimiento'),
('Diagnóstico General', 'Diagnóstico completo del vehículo', 600.00, 120, 'diagnostico'),
('Reparación de Motor', 'Reparación de componentes del motor', 5000.00, 480, 'reparacion'),
('Cambio de Batería', 'Reemplazo de batería del vehículo', 1500.00, 30, 'mantenimiento'),
('Alineación y Balanceo', 'Alineación de llantas y balanceo', 900.00, 75, 'mantenimiento'),
('Reparación de Transmisión', 'Reparación del sistema de transmisión', 8000.00, 720, 'reparacion'),
('Servicio de Emergencia', 'Servicio de mecánico a domicilio de emergencia', 2000.00, 120, 'emergencia');

-- Insert sample mechanics
INSERT INTO usuarios (nombre, email, telefono, password, tipo_usuario, direccion) VALUES 
('Juan Pérez', 'juan.perez@mechanicalfix.com', '5551234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mecanico', 'Av. Principal 123, Ciudad'),
('María González', 'maria.gonzalez@mechanicalfix.com', '5557654321', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mecanico', 'Calle Secundaria 456, Ciudad'),
('Carlos López', 'carlos.lopez@mechanicalfix.com', '5559876543', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mecanico', 'Boulevard Norte 789, Ciudad');

-- Insert sample mechanic details
INSERT INTO mecanicos (usuario_id, especialidades, experiencia_anos, certificaciones, calificacion_promedio, total_servicios, tarifa_base) VALUES
(2, '["Motor", "Transmisión", "Frenos"]', 8, '["ASE Certified", "Automotive Technology"]', 4.5, 150, 500.00),
(3, '["Electricidad Automotriz", "Aire Acondicionado", "Diagnóstico"]', 6, '["Electrical Systems", "A/C Certification"]', 4.7, 120, 450.00),
(4, '["Suspensión", "Dirección", "Llantas"]', 10, '["Suspension Specialist", "Brake Systems"]', 4.8, 200, 550.00);

-- Insert sample clients
INSERT INTO usuarios (nombre, email, telefono, password, tipo_usuario, empresa, direccion) VALUES 
('Ana Martínez', 'ana.martinez@email.com', '5551111111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', 'Empresa ABC', 'Residencial Los Pinos 101'),
('Roberto Silva', 'roberto.silva@email.com', '5552222222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', 'Transportes XYZ', 'Colonia Centro 202'),
('Laura Jiménez', 'laura.jimenez@email.com', '5553333333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 'Fraccionamiento Sur 303');

-- Insert sample service requests
INSERT INTO solicitudes (cliente_id, mecanico_id, tipo_servicio, descripcion, direccion_completa, fecha_programada, estatus, costo_estimado, costo_final) VALUES
(5, 2, 'Cambio de Aceite', 'Cambio de aceite para vehículo Honda Civic 2018', 'Residencial Los Pinos 101, Ciudad', '2024-01-15 10:00:00', 'completado', 800.00, 850.00),
(6, 3, 'Diagnóstico General', 'El vehículo presenta ruidos extraños en el motor', 'Colonia Centro 202, Ciudad', '2024-01-16 14:00:00', 'en_progreso', 600.00, NULL),
(7, NULL, 'Servicio de Emergencia', 'Vehículo no enciende, necesito asistencia urgente', 'Fraccionamiento Sur 303, Ciudad', '2024-01-17 09:00:00', 'pendiente', 2000.00, NULL);