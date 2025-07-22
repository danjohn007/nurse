# Foreign Key Constraint Fix Documentation

## Issue Description
The application was experiencing foreign key constraint failures when inserting data into the database:

1. **Error #1452** for `solicitudes` table: Cannot add or update a child row: a foreign key constraint fails (`fix360_mechanical`.`solicitudes`, CONSTRAINT `solicitudes_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE)

2. **Error #1452** for `mecanicos` table: Cannot add or update a child row: a foreign key constraint fails (`fix360_mechanical`.`mecanicos`, CONSTRAINT `mecanicos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE)

## Root Causes Identified

### 1. SolicitudController.php - cliente_id Validation Issue
**Problem**: Line 52 in `createSolicitud()` function was not validating if the database query returned a valid result before accessing the array key.

```php
// BEFORE (problematic code)
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$clienteId = $result['id']; // Could be null if no result found
```

**Solution**: Added proper validation:
```php
// AFTER (fixed code)
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Error: No se pudo encontrar el usuario existente']);
    return;
}
$clienteId = $result['id'];
```

### 2. Mechanic Assignment Validation
**Problem**: The `assignMechanic()` function was not validating if the mechanic exists before assignment.

**Solution**: Added validation to ensure mechanic exists and is active:
```php
$query = "SELECT id FROM usuarios WHERE id = :mecanico_id AND tipo_usuario = 'mecanico' AND estatus = 'activo'";
$stmt = $solicitud->getConnection()->prepare($query);
$stmt->bindParam(':mecanico_id', $data['mecanico_id']);
$stmt->execute();
$mechanic = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mechanic) {
    echo json_encode(['success' => false, 'message' => 'Error: El mecánico especificado no existe o no está activo']);
    return;
}
```

### 3. Database Connection Access Issue
**Problem**: SolicitudController was trying to access `$solicitud->conn` but the connection property was private.

**Solution**: Added public getter method in Solicitud model:
```php
public function getConnection() {
    return $this->conn;
}
```

## New Features Added

### 1. Mechanic Management API
Created `api/manage_mechanics.php` with full CRUD operations:

#### Create Mechanic
```bash
POST /api/manage_mechanics.php
Content-Type: application/json

{
    "action": "create",
    "nombre": "Juan Pérez",
    "email": "juan@example.com",
    "telefono": "5551234567",
    "especialidades": ["Motor", "Transmisión"],
    "experiencia_anos": 5,
    "certificaciones": ["ASE Certified"],
    "tarifa_base": 500.00,
    "direccion": "Calle Principal 123"
}
```

#### List Mechanics
```bash
POST /api/manage_mechanics.php
Content-Type: application/json

{
    "action": "list"
}
```

#### Update Mechanic
```bash
POST /api/manage_mechanics.php
Content-Type: application/json

{
    "action": "update",
    "id": 2,
    "nombre": "Juan Carlos Pérez",
    "experiencia_anos": 6,
    "disponible": true
}
```

#### Delete Mechanic
```bash
POST /api/manage_mechanics.php
Content-Type: application/json

{
    "action": "delete",
    "id": 2
}
```

### 2. Enhanced Usuario Model
Added `userExists()` method for better validation:
```php
public function userExists($id, $tipo_usuario = null) {
    $query = "SELECT id FROM " . $this->table_name . " WHERE id = :id";
    if ($tipo_usuario) {
        $query .= " AND tipo_usuario = :tipo_usuario";
    }
    $query .= " AND estatus = 'activo'";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":id", $id);
    if ($tipo_usuario) {
        $stmt->bindParam(":tipo_usuario", $tipo_usuario);
    }
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}
```

## Testing the Fixes

### 1. Database Setup
Ensure your database is properly initialized with the schema:
```sql
-- Run the complete schema.sql file to create tables and sample data
mysql -u your_user -p your_database < schema.sql
```

### 2. Test Client Creation and Solicitud
```bash
# Test creating a solicitud (should now work without foreign key errors)
curl -X POST http://your-domain/controllers/SolicitudController.php \
  -H "Content-Type: application/json" \
  -d '{
    "action": "create",
    "nombre": "Test Cliente",
    "email": "test@example.com",
    "telefono": "5559999999",
    "empresa": "Test Company",
    "tipo_servicio": "Cambio de Aceite",
    "descripcion": "Test description",
    "direccion": "Test Address",
    "fecha_programada": "2024-01-20 10:00:00",
    "costo_estimado": 800.00,
    "metodo_pago": "efectivo",
    "notas": "Test notes",
    "prioridad": "media"
  }'
```

### 3. Test Mechanic Creation
```bash
# Test creating a mechanic (new functionality)
curl -X POST http://your-domain/api/manage_mechanics.php \
  -H "Content-Type: application/json" \
  -d '{
    "action": "create",
    "nombre": "Test Mechanic",
    "email": "mechanic@example.com",
    "telefono": "5558888888",
    "especialidades": ["Motor", "Frenos"],
    "experiencia_anos": 5,
    "tarifa_base": 500.00
  }'
```

## Prevention Measures

The fixes include several validation layers to prevent foreign key constraint violations:

1. **Client Validation**: Always verify client exists or create one before creating solicitudes
2. **Mechanic Validation**: Verify mechanic exists and is active before assignment
3. **Transaction Safety**: Use database transactions where appropriate
4. **Error Handling**: Comprehensive error messages for debugging

## Deployment Notes

1. Backup your database before deploying these changes
2. Run syntax checks: `php -l filename.php` on all modified files
3. Test with a small dataset first
4. Monitor error logs for any remaining issues
5. Ensure proper database permissions for foreign key operations

The foreign key constraint errors should now be resolved with these comprehensive fixes and validations.