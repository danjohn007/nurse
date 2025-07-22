# MechanicalFix - Sistema de Servicios Mecánicos a Domicilio

Aplicación web completa para la gestión de servicios mecánicos a domicilio con sistema de administración, clientes y mecánicos.

## Características Principales

### 🔧 Para Clientes
- **Solicitud de servicios**: Formulario completo con mapa interactivo
- **Búsqueda por teléfono**: Carga automática de datos de clientes existentes
- **Dashboard personal**: Historial de servicios y gestión de pagos
- **Sistema de pagos**: Subida de comprobantes y integración con PayPal
- **Sistema de calificaciones**: Evalúa a los mecánicos después del servicio
- **Notificaciones**: Seguimiento en tiempo real del estado del servicio

### 🛠️ Para Mecánicos
- **Dashboard profesional**: Gestión de servicios asignados
- **Control de disponibilidad**: Toggle para marcar disponibilidad
- **Ganancias en tiempo real**: 70% de cada servicio completado
- **4 gráficas analíticas**: Tendencias, eficiencia, calificaciones e ingresos
- **Geolocalización**: Actualización de ubicación para mejores asignaciones
- **Historial completo**: Todos los servicios realizados y calificaciones

### 👨‍💼 Para Administradores
- **Dashboard completo**: Estadísticas generales del negocio
- **Gestión de solicitudes**: Asignación y seguimiento de servicios
- **Catálogo de mecánicos**: Alta, baja y gestión de mecánicos
- **CRM de clientes**: Base de datos completa de clientes
- **Reportes financieros**: Análisis de ingresos con gráficas
- **Sistema de exportación**: Generación de reportes en PDF

## Tecnologías Utilizadas

- **Backend**: PHP 7.4+
- **Base de datos**: MySQL 5.7+
- **Frontend**: Bootstrap 5.3, JavaScript ES6
- **Gráficas**: Chart.js
- **Mapas**: Google Maps API
- **Iconos**: Bootstrap Icons
- **Patrones**: Arquitectura MVC

## Estructura del Proyecto

```
mechanicalfix/
├── config/
│   └── database.php           # Configuración de base de datos
├── controllers/
│   ├── AuthController.php     # Autenticación y sesiones
│   └── SolicitudController.php # Gestión de solicitudes
├── models/
│   ├── Usuario.php           # Modelo de usuarios
│   └── Solicitud.php         # Modelo de solicitudes
├── views/
│   ├── auth/
│   │   └── login.php         # Página de login
│   └── solicitud/
│       └── form.php          # Formulario de solicitud
├── public/
│   ├── index.php             # Página principal
│   ├── dashboard.php         # Dashboard admin
│   ├── dashboard_cliente.php # Dashboard cliente
│   ├── dashboard_mecanico.php# Dashboard mecánico
│   └── logout.php           # Cerrar sesión
├── api/                     # Endpoints API
├── assets/                  # Recursos estáticos
└── schema.sql              # Estructura de base de datos
```

## Instalación

### 1. Requisitos Previos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Extensiones PHP: PDO, PDO_MySQL

### 2. Configuración de Base de Datos
```sql
-- Ejecutar el archivo schema.sql en MySQL
mysql -u root -p < schema.sql
```

### 3. Configuración de Conexión
Editar `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'mechanicalfix_db';
private $username = 'tu_usuario';
private $password = 'tu_contraseña';
```

### 4. Configuración del Servidor Web
Apuntar el document root a la carpeta `public/`

### 5. Google Maps API (Opcional)
Reemplazar `YOUR_API_KEY` en `views/solicitud/form.php` con tu API key de Google Maps.

## Credenciales de Prueba

### Administrador
- **Email**: admin@mechanicalfix.com
- **Contraseña**: password

### Mecánicos
- **Email**: juan.perez@mechanicalfix.com
- **Contraseña**: password

### Clientes
- **Email**: ana.martinez@email.com
- **Contraseña**: password

## Servicios Disponibles

1. **Cambio de Aceite** - $800
2. **Revisión de Frenos** - $1,200
3. **Diagnóstico General** - $600
4. **Reparación de Motor** - $5,000
5. **Cambio de Batería** - $1,500
6. **Alineación y Balanceo** - $900
7. **Reparación de Transmisión** - $8,000
8. **Servicio de Emergencia** - $2,000

## Funcionalidades Implementadas

### ✅ Completadas
- [x] Estructura MVC completa
- [x] Sistema de autenticación multi-rol
- [x] Formulario de solicitud con mapa
- [x] Dashboard administrativo con estadísticas
- [x] Dashboard de cliente con historial
- [x] Dashboard de mecánico con ganancias
- [x] Sistema de calificaciones
- [x] Subida de comprobantes de pago
- [x] Integración básica con PayPal
- [x] Responsive design con Bootstrap
- [x] Base de datos completa con datos de prueba

### 🔄 En Desarrollo
- [ ] Exportación de reportes PDF
- [ ] Notificaciones en tiempo real
- [ ] Sistema de chat cliente-mecánico
- [ ] Pasarela de pagos completa
- [ ] Panel de configuración avanzado

## API Endpoints

### Públicos
- `POST /controllers/SolicitudController.php` - Crear/gestionar solicitudes
- `POST /controllers/AuthController.php` - Autenticación

### Autenticados
- `POST /api/get_service_details.php` - Detalles de servicio
- `POST /api/upload_payment.php` - Subir comprobante
- `POST /api/submit_rating.php` - Enviar calificación
- `POST /api/complete_service.php` - Completar servicio
- `POST /api/add_service_note.php` - Agregar nota
- `POST /api/update_availability.php` - Actualizar disponibilidad

## Personalización

### Cambiar Logo
Reemplazar el SVG en las vistas con tu logo personalizado.

### Modificar Servicios
Editar la tabla `servicios` en la base de datos.

### Ajustar Comisiones
Modificar el porcentaje (70%) en los dashboards y APIs.

## Soporte

Para soporte técnico o preguntas sobre la implementación, contactar al equipo de desarrollo.

## Licencia

Este proyecto está desarrollado para MechanicalFix y contiene código propietario.
