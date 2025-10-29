# 📚 Documentación Backend - Tidy API (Laravel 11)

## 📖 Tabla de Contenidos

1. [Arquitectura General](#arquitectura-general)
2. [Modelos y Base de Datos](#modelos-y-base-de-datos)
3. [Controladores y APIs](#controladores-y-apis)
4. [Autenticación y Seguridad](#autenticación-y-seguridad)
5. [Middleware](#middleware)
6. [Servicios](#servicios)
7. [Observers](#observers)
8. [Testing](#testing)
9. [Deploy y Configuración](#deploy-y-configuración)
10. [Convenciones y Mejores Prácticas](#convenciones-y-mejores-prácticas)

---

## 🏗️ Arquitectura General

### Stack Tecnológico

- **Framework**: Laravel 11
- **PHP**: 8.2+
- **Base de Datos**: MySQL 8.0
- **Cache/Queue**: Redis
- **Autenticación**: Laravel Sanctum
- **Testing**: PHPUnit 10
- **Deploy**: Railway (Docker)

### Estructura de Directorios

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Controladores
│   │   │   ├── Admin/        # Controladores admin
│   │   │   └── Web/          # Controladores web
│   │   ├── Middleware/       # Middleware personalizado
│   │   └── Requests/         # Form Requests
│   ├── Models/               # Modelos Eloquent
│   ├── Services/             # Servicios
│   ├── Observers/            # Observers
│   └── Providers/            # Service Providers
├── database/
│   ├── migrations/           # Migraciones
│   ├── seeders/              # Seeders
│   └── factories/            # Factories para testing
├── routes/
│   ├── api.php              # Rutas API
│   └── web.php              # Rutas web
└── tests/
    ├── Feature/             # Tests de integración
    └── Unit/                # Tests unitarios
```

---

## 💾 Modelos y Base de Datos

### Modelo: Usuario (User)

**Ubicación**: `app/Models/User.php`

**Tabla**: `usuarios`

**Propósito**: Gestión de usuarios del sistema.

**Campos**:

```php
{
  id: bigint,
  name: string,
  last_name: string,
  email: string (unique),
  password: string (hashed),
  phone: string (nullable),
  email_verified_at: timestamp (nullable),
  role: enum('user', 'admin'),
  created_at: timestamp,
  updated_at: timestamp,
  deleted_at: timestamp (soft delete)
}
```

**Relaciones**:

```php
// One to One
hasOne(UsuarioCuenta::class)
hasOne(UsuarioNivel::class)

// One to Many
hasMany(Elemento::class)
hasMany(Payment::class)
hasMany(Notificacion::class)
```

**Scopes**:

```php
// Usuarios activos (no eliminados)
scopeActive($query)

// Usuarios premium
scopePremium($query)

// Usuarios por rol
scopeByRole($query, $role)
```

**Métodos personalizados**:

```php
// Verificar si el usuario tiene un rol específico
hasRole(string $role): bool

// Verificar si el usuario es premium
isPremium(): bool

// Obtener estadísticas del usuario
getStats(): array
```

**Ejemplo de uso**:

```php
// Crear usuario
$user = User::create([
    'name' => 'Juan',
    'last_name' => 'Pérez',
    'email' => 'juan@example.com',
    'password' => Hash::make('password'),
    'role' => 'user'
]);

// Verificar premium
if ($user->isPremium()) {
    // Acceso a funcionalidades premium
}

// Obtener estadísticas
$stats = $user->getStats();
```

---

### Modelo: UsuarioCuenta

**Ubicación**: `app/Models/UsuarioCuenta.php`

**Tabla**: `usuario_cuentas`

**Propósito**: Información adicional de cuenta del usuario (premium, suscripción).

**Campos**:

```php
{
  id: bigint,
  user_id: bigint (foreign key),
  is_premium: boolean,
  premium_expires_at: timestamp (nullable),
  mercadopago_subscription_id: string (nullable),
  subscription_status: enum('active', 'cancelled', 'expired'),
  created_at: timestamp,
  updated_at: timestamp
}
```

**Relaciones**:

```php
belongsTo(User::class)
```

**Ejemplo de uso**:

```php
// Actualizar a premium
$user->usuarioCuenta->update([
    'is_premium' => true,
    'premium_expires_at' => now()->addMonth(),
    'subscription_status' => 'active'
]);
```

---

### Modelo: UsuarioNivel

**Ubicación**: `app/Models/UsuarioNivel.php`

**Tabla**: `usuario_niveles`

**Propósito**: Sistema de niveles y experiencia del usuario.

**Campos**:

```php
{
  id: bigint,
  user_id: bigint (foreign key),
  level: integer,
  total_experience: integer,
  current_level_experience: integer,
  next_level_experience: integer,
  achievements: json,
  created_at: timestamp,
  updated_at: timestamp
}
```

**Relaciones**:

```php
belongsTo(User::class)
```

**Métodos personalizados**:

```php
// Agregar experiencia
addExperience(int $xp): void

// Calcular nivel basado en experiencia
calculateLevel(): int

// Verificar si puede subir de nivel
canLevelUp(): bool

// Desbloquear logro
unlockAchievement(string $achievementKey): void
```

**Ejemplo de uso**:

```php
// Agregar XP
$user->usuarioNivel->addExperience(50);

// Verificar nivel
if ($user->usuarioNivel->canLevelUp()) {
    $newLevel = $user->usuarioNivel->calculateLevel();
    // Disparar evento de subida de nivel
}
```

---

### Modelo: Elemento

**Ubicación**: `app/Models/Elemento.php`

**Tabla**: `elementos`

**Propósito**: Modelo base polimórfico para todos los elementos (notas, alarmas, objetivos, eventos).

**Campos**:

```php
{
  id: bigint,
  user_id: bigint (foreign key),
  title: string,
  type: enum('note', 'alarm', 'objective', 'event', 'calendar'),
  elementable_type: string (polimórfico),
  elementable_id: bigint (polimórfico),
  archived: boolean,
  priority: enum('low', 'medium', 'high'),
  tags: json (nullable),
  created_at: timestamp,
  updated_at: timestamp,
  deleted_at: timestamp (soft delete)
}
```

**Relaciones**:

```php
// Polimórfica
morphTo('elementable') // Nota, Alarma, Objetivo, Evento

// Pertenece a usuario
belongsTo(User::class)
```

**Scopes**:

```php
// Por tipo
scopeByType($query, $type)

// Archivados
scopeArchived($query)

// Activos (no archivados)
scopeActive($query)

// Por usuario
scopeByUser($query, $userId)
```

**Ejemplo de uso**:

```php
// Obtener todos los elementos de tipo nota
$notes = Elemento::byType('note')->get();

// Archivar elemento
$elemento->update(['archived' => true]);

// Obtener el elemento polimórfico
$nota = $elemento->elementable; // Instancia de Nota
```

---

### Modelo: Nota

**Ubicación**: `app/Models/Nota.php`

**Tabla**: `notas`

**Propósito**: Notas de diferentes tipos.

**Campos**:

```php
{
  id: bigint,
  note_type: enum('note_normal', 'note_code', 'gastos_mensuales', ...),
  content: json,
  color: string (hex color),
  attachments: json (nullable),
  created_at: timestamp,
  updated_at: timestamp
}
```

**Relaciones**:

```php
morphOne(Elemento::class, 'elementable')
```

**Tipos de Notas**:

1. `note_normal` - Nota básica
2. `note_code` - Nota con código
3. `gastos_mensuales` - Control de gastos
4. `compras_super` - Lista de compras
5. `puntos_juego` - Puntos de juego
6. `control_presupuesto` - Presupuesto
7. `gestion_tiempo` - Time tracking
8. `receta` - Recetas
9. `medidas_construccion` - Medidas
10. `diagrama` - Diagramas
11. `recomendaciones` - Recomendaciones
12. `pedidos_grupales` - Pedidos
13. `viaje` - Viajes
14. `dibujo` - Dibujos
15. `gestion_claves` - Claves
16. `premium_type_1/2/3` - Notas premium

**Cast**:

```php
protected $casts = [
    'content' => 'array',
    'attachments' => 'array'
];
```

**Ejemplo de uso**:

```php
// Crear nota
$nota = Nota::create([
    'note_type' => 'note_normal',
    'content' => [
        'text' => 'Contenido de la nota',
        'fontSize' => 14
    ],
    'color' => '#ffffff'
]);

// Crear elemento asociado
$elemento = Elemento::create([
    'user_id' => $userId,
    'title' => 'Mi nota',
    'type' => 'note',
    'elementable_id' => $nota->id,
    'elementable_type' => Nota::class
]);
```

---

### Modelo: Alarma

**Ubicación**: `app/Models/Alarma.php`

**Tabla**: `alarmas`

**Propósito**: Alarmas con geolocalización y periodicidad.

**Campos**:

```php
{
  id: bigint,
  time: time,
  repeat_config: json,
  sound: string,
  vibration: boolean,
  snooze_duration: integer (minutos),
  gps_config: json (nullable),
  weather_config: json (nullable),
  is_active: boolean,
  created_at: timestamp,
  updated_at: timestamp
}
```

**Configuración de repetición** (`repeat_config`):

```json
{
  "type": "daily" | "weekly" | "weekdays" | "custom",
  "days": [0, 1, 2, 3, 4, 5, 6], // 0 = domingo
  "skipHolidays": false
}
```

**Configuración GPS** (`gps_config`):

```json
{
  "enabled": true,
  "latitude": -34.603722,
  "longitude": -58.381592,
  "radius": 500, // metros
  "address": "Dirección completa"
}
```

**Relaciones**:

```php
morphOne(Elemento::class, 'elementable')
```

**Ejemplo de uso**:

```php
// Crear alarma con GPS
$alarma = Alarma::create([
    'time' => '08:00:00',
    'repeat_config' => [
        'type' => 'weekdays',
        'days' => [1, 2, 3, 4, 5]
    ],
    'gps_config' => [
        'enabled' => true,
        'latitude' => -34.603722,
        'longitude' => -58.381592,
        'radius' => 500
    ],
    'is_active' => true
]);
```

---

### Modelo: Objetivo

**Ubicación**: `app/Models/Objetivo.php`

**Tabla**: `objetivos`

**Propósito**: Objetivos con metas (pasos) para completar.

**Campos**:

```php
{
  id: bigint,
  description: text,
  deadline: date (nullable),
  priority: enum('low', 'medium', 'high'),
  completed: boolean,
  completed_at: timestamp (nullable),
  created_at: timestamp,
  updated_at: timestamp
}
```

**Relaciones**:

```php
morphOne(Elemento::class, 'elementable')
hasMany(Meta::class)
```

**Métodos personalizados**:

```php
// Calcular progreso
calculateProgress(): int

// Marcar como completado
markAsCompleted(): void

// Verificar si está completado
isCompleted(): bool
```

**Ejemplo de uso**:

```php
// Crear objetivo con metas
$objetivo = Objetivo::create([
    'description' => 'Aprender Vue 3',
    'deadline' => now()->addMonth(),
    'priority' => 'high'
]);

// Agregar metas
$objetivo->metas()->create([
    'description' => 'Completar tutorial oficial',
    'order' => 1,
    'completed' => false
]);

// Verificar progreso
$progress = $objetivo->calculateProgress(); // 0-100
```

---

### Modelo: Meta

**Ubicación**: `app/Models/Meta.php`

**Tabla**: `metas`

**Propósito**: Pasos individuales de un objetivo.

**Campos**:

```php
{
  id: bigint,
  objetivo_id: bigint (foreign key),
  description: string,
  order: integer,
  completed: boolean,
  completed_at: timestamp (nullable),
  created_at: timestamp,
  updated_at: timestamp
}
```

**Relaciones**:

```php
belongsTo(Objetivo::class)
```

**Ejemplo de uso**:

```php
// Marcar meta como completada
$meta->update([
    'completed' => true,
    'completed_at' => now()
]);

// Verificar si el objetivo está completo
$objetivo->refresh();
if ($objetivo->metas()->where('completed', false)->count() === 0) {
    $objetivo->markAsCompleted();
}
```

---

### Modelo: Payment

**Ubicación**: `app/Models/Payment.php`

**Tabla**: `payments`

**Propósito**: Historial de pagos de MercadoPago.

**Campos**:

```php
{
  id: bigint,
  user_id: bigint (foreign key),
  payment_id: string (unique),
  collection_id: string (nullable),
  subscription_id: string (nullable),
  status: enum('approved', 'pending', 'rejected', 'cancelled'),
  payment_type: enum('subscription', 'one_time'),
  payment_method: string (nullable),
  amount: decimal(10,2),
  currency: string(3),
  plan_type: enum('monthly', 'annual') (nullable),
  description: text (nullable),
  metadata: json (nullable),
  paid_at: timestamp (nullable),
  created_at: timestamp,
  updated_at: timestamp
}
```

**Relaciones**:

```php
belongsTo(User::class, 'user_id', 'id', 'usuarios')
```

**Scopes**:

```php
// Pagos aprobados
scopeApproved($query)

// Pagos pendientes
scopePending($query)

// Suscripciones
scopeSubscriptions($query)
```

**Accessors**:

```php
// Monto formateado
getFormattedAmountAttribute(): string // "ARS $1,500.00"

// Label de estado
getStatusLabelAttribute(): string // "Aprobado"

// Label de tipo de plan
getPlanTypeLabelAttribute(): string // "Mensual"
```

**Ejemplo de uso**:

```php
// Crear registro de pago
Payment::create([
    'user_id' => $userId,
    'payment_id' => 'MP-123456',
    'status' => 'approved',
    'payment_type' => 'subscription',
    'amount' => 1500.00,
    'currency' => 'ARS',
    'plan_type' => 'monthly',
    'paid_at' => now()
]);

// Obtener pagos aprobados del mes
$monthlyPayments = Payment::approved()
    ->whereMonth('paid_at', now()->month)
    ->get();
```

---

## 🎮 Controladores y APIs

### AuthController

**Ubicación**: `app/Http/Controllers/Api/AuthController.php`

**Propósito**: Gestión de autenticación (login, registro, logout).

**Rutas**:

```php
POST   /api/register      # Registro de usuario
POST   /api/login         # Inicio de sesión
POST   /api/logout        # Cerrar sesión
GET    /api/user          # Usuario actual
POST   /api/refresh       # Refrescar token
```

**Métodos**:

#### `register(Request $request)`

**Request**:

```json
{
  "name": "Juan",
  "last_name": "Pérez",
  "email": "juan@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+54911234567"
}
```

**Response** (201):

```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "data": {
    "user": {
      "id": 1,
      "name": "Juan",
      "last_name": "Pérez",
      "email": "juan@example.com"
    },
    "token": "1|eyJ0eXAiOiJKV1QiLCJh..."
  }
}
```

**Validaciones**:

- name: requerido, string, min:2
- last_name: requerido, string, min:2
- email: requerido, email, único
- password: requerido, min:8, confirmado
- phone: opcional, string

---

#### `login(Request $request)`

**Request**:

```json
{
  "email": "juan@example.com",
  "password": "password123"
}
```

**Response** (200):

```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "user": {
      "id": 1,
      "name": "Juan",
      "email": "juan@example.com",
      "role": "user",
      "is_premium": false
    },
    "token": "2|eyJ0eXAiOiJKV1QiLCJh..."
  }
}
```

**Response error** (401):

```json
{
  "success": false,
  "message": "Credenciales inválidas"
}
```

---

### ElementoController

**Ubicación**: `app/Http/Controllers/Api/ElementoController.php`

**Propósito**: CRUD de elementos (notas, alarmas, objetivos).

**Rutas**:

```php
GET    /api/elementos                  # Listar elementos
POST   /api/elementos                  # Crear elemento
GET    /api/elementos/{id}             # Ver elemento
PUT    /api/elementos/{id}             # Actualizar elemento
DELETE /api/elementos/{id}             # Eliminar elemento
POST   /api/elementos/{id}/archive     # Archivar
POST   /api/elementos/{id}/restore     # Restaurar
```

**Métodos**:

#### `index(Request $request)`

**Query params**:

```
?type=note              # Filtrar por tipo
&archived=false         # Filtrar archivados
&search=texto          # Búsqueda
&per_page=15           # Paginación
```

**Response**:

```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "title": "Mi nota",
        "type": "note",
        "archived": false,
        "elementable": {
          "note_type": "note_normal",
          "content": {"text": "Contenido"},
          "color": "#ffffff"
        },
        "created_at": "2025-10-05T10:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "total": 50,
    "per_page": 15
  }
}
```

---

#### `store(Request $request)`

**Request** (ejemplo nota):

```json
{
  "title": "Mi primera nota",
  "type": "note",
  "note_type": "note_normal",
  "content": {
    "text": "Contenido de la nota",
    "fontSize": 14
  },
  "color": "#ffffff",
  "priority": "medium",
  "tags": ["importante", "trabajo"]
}
```

**Response** (201):

```json
{
  "success": true,
  "message": "Elemento creado exitosamente",
  "data": {
    "id": 1,
    "title": "Mi primera nota",
    "type": "note",
    "elementable_id": 1,
    "elementable_type": "App\\Models\\Nota"
  }
}
```

---

### MercadoPagoController

**Ubicación**: `app/Http/Controllers/MercadoPagoController.php`

**Propósito**: Gestión de pagos y suscripciones con MercadoPago.

**Rutas**:

```php
GET    /api/mercadopago/success       # Redirect después de pago exitoso
POST   /api/mercadopago/webhook       # Webhook de MercadoPago
GET    /api/mercadopago/subscription  # URL de suscripción
```

**Métodos**:

#### `subscriptionSuccess(Request $request)`

**Query params** (desde MercadoPago):

```
?collection_id=123456
&status=approved
&preapproval_id=SUB-789
&external_reference=1
```

**Funcionalidades**:

- Actualiza usuario a premium
- Guarda registro de pago
- Establece fecha de expiración
- Redirige al frontend con resultado

---

#### `webhook(Request $request)`

**Request** (desde MercadoPago):

```json
{
  "type": "subscription",
  "data": {
    "id": "SUB-789"
  }
}
```

**Funcionalidades**:

- Procesa notificaciones de MercadoPago
- Actualiza estado de suscripción
- Guarda historial de pagos
- Maneja renovaciones y cancelaciones

---

### Admin/DashboardController (Web)

**Ubicación**: `app/Http/Controllers/Web/AdminDashboardController.php`

**Propósito**: Panel de administración web.

**Rutas**:

```php
GET    /admin/dashboard/users          # Gestión de usuarios
GET    /admin/dashboard/payments       # Historial de pagos
GET    /admin/dashboard/charts         # Estadísticas
```

**Métodos**:

#### `payments(Request $request)`

**Query params**:

```
?status=approved
&search=usuario
&page=1
```

**Funcionalidades**:

- Lista de pagos con paginación
- Filtros por estado y usuario
- Estadísticas de ingresos
- Modal de detalles con metadata

**Response** (vista Blade):

- Tarjetas de estadísticas
- Tabla de pagos
- Filtros de búsqueda
- Paginación

---

## 🔐 Autenticación y Seguridad

### Laravel Sanctum

**Configuración**: `config/sanctum.php`

**Funcionamiento**:

1. Usuario hace login → recibe token
2. Token se envía en headers de requests subsiguientes
3. Middleware `auth:sanctum` valida el token
4. Usuario autenticado disponible en request

**Ejemplo de request autenticado**:

```bash
curl -X GET https://api.tidy.com/api/user \
  -H "Authorization: Bearer 2|eyJ0eXAiOiJKV1QiLCJh..."
```

---

### Middleware

#### `auth:sanctum`

**Propósito**: Validar token de autenticación.

**Uso**:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/elementos', [ElementoController::class, 'index']);
});
```

---

#### `role:admin`

**Ubicación**: `app/Http/Middleware/CheckRole.php`

**Propósito**: Verificar rol de usuario.

**Uso**:

```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index']);
});
```

---

## 🛠️ Servicios

### NivelService

**Ubicación**: `app/Services/NivelService.php`

**Propósito**: Lógica de cálculo de niveles y experiencia.

**Métodos**:

```php
// Calcular nivel basado en XP
calculateLevel(int $totalXP): int

// Obtener XP requerida para siguiente nivel
getRequiredXPForNextLevel(int $currentLevel): int

// Verificar si puede subir de nivel
canLevelUp(int $currentXP, int $currentLevel): bool

// Calcular progreso (0-100)
calculateProgress(int $currentXP, int $currentLevel): int
```

**Tabla de niveles**:

```php
protected $levelXPRequirements = [
    1 => 0,
    2 => 100,
    3 => 250,
    4 => 500,
    5 => 1000,
    6 => 2000,
    // ... hasta nivel 100
];
```

**Ejemplo de uso**:

```php
use App\Services\NivelService;

$nivelService = new NivelService();

// Calcular nivel
$level = $nivelService->calculateLevel(1500); // Nivel 5

// XP para siguiente nivel
$nextLevelXP = $nivelService->getRequiredXPForNextLevel(5); // 2000
```

---

## 👁️ Observers

### GamificationObserver

**Ubicación**: `app/Observers/GamificationObserver.php`

**Propósito**: Otorgar XP automáticamente por acciones del usuario.

**Eventos observados**:

```php
// Elementos
Elemento::created   → +10 XP (nota)
Elemento::created   → +5 XP (alarma)
Elemento::created   → +15 XP (objetivo)

// Objetivos
Objetivo::completed → +50 XP

// Metas
Meta::completed     → +20 XP
```

**Registro** (`app/Providers/EventServiceProvider.php`):

```php
protected $observers = [
    Elemento::class => [GamificationObserver::class],
    Objetivo::class => [GamificationObserver::class],
];
```

**Ejemplo**:

```php
// Al crear un objetivo, automáticamente se otorgan 15 XP
$objetivo = Objetivo::create([...]);
// GamificationObserver detecta el evento y agrega XP
```

---

## 🧪 Testing

### Feature Tests

**Ubicación**: `tests/Feature/`

#### AuthControllerTest.php

**Tests incluidos**:

```php
test_user_can_register()
test_user_can_login()
test_user_cannot_login_with_invalid_credentials()
test_authenticated_user_can_logout()
test_can_get_authenticated_user()
```

**Ejemplo**:

```php
public function test_user_can_register()
{
    $response = $this->postJson('/api/register', [
        'name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password'
    ]);

    $response->assertStatus(201)
             ->assertJsonStructure([
                 'success',
                 'data' => ['user', 'token']
             ]);
}
```

---

#### ElementoControllerTest.php

**Tests incluidos**:

```php
test_can_list_elementos()
test_can_create_elemento()
test_can_update_elemento()
test_can_delete_elemento()
test_can_archive_elemento()
test_can_filter_elementos_by_type()
```

---

### Unit Tests

**Ubicación**: `tests/Unit/`

#### NotaTest.php

**Tests incluidos**:

```php
test_content_is_cast_to_array()
test_can_store_complex_content()
test_can_handle_special_characters()
```

---

### Ejecutar Tests

```bash
# Todos los tests
php artisan test

# Con coverage
php artisan test --coverage

# Solo feature tests
php artisan test tests/Feature

# Test específico
php artisan test tests/Feature/AuthControllerTest.php
```

---

## 🚀 Deploy y Configuración

### Railway

**Dockerfile**:

```dockerfile
FROM php:8.2-fpm

# Instalar extensiones
RUN docker-php-ext-install pdo pdo_mysql

# Instalar Redis
RUN pecl install redis && docker-php-ext-enable redis

# Instalar Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

# Copiar código
COPY . /var/www/html

# Instalar dependencias
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Permisos
RUN chown -R www-data:www-data /var/www/html/storage
```

---

### Migraciones en Producción

**Ruta especial**:

```php
// web.php
Route::get('/run-migrations-now-please-' . md5(env('APP_KEY')), function () {
    Artisan::call('migrate', ['--force' => true]);
    return response()->json(['output' => Artisan::output()]);
});
```

**Uso**:

```bash
# Generar URL hash
php artisan tinker
>>> md5(env('APP_KEY'))

# Acceder a:
https://tidyback-production.up.railway.app/run-migrations-now-please-{hash}
```

---

## 📝 Convenciones y Mejores Prácticas

### Nomenclatura

1. **Modelos**: PascalCase singular

   ```php
   User, Elemento, Nota, Alarma
   ```
2. **Controladores**: PascalCase + Controller

   ```php
   AuthController, ElementoController
   ```
3. **Métodos**: camelCase

   ```php
   getUserStats(), calculateLevel()
   ```
4. **Rutas**: kebab-case

   ```php
   /api/usuario-nivel
   /admin/dashboard-users
   ```

---

### Validación

**Form Requests**:

```php
// app/Http/Requests/StoreElementoRequest.php
class StoreElementoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'type' => 'required|in:note,alarm,objective',
            'content' => 'required|array'
        ];
    }
}

// Uso en controlador
public function store(StoreElementoRequest $request)
{
    // Datos ya validados
    $validated = $request->validated();
}
```

---

### Manejo de Errores

**Try-catch en controladores**:

```php
public function store(Request $request)
{
    try {
        $elemento = Elemento::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $elemento
        ], 201);

    } catch (\Exception $e) {
        Log::error('Error creando elemento: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Error al crear elemento'
        ], 500);
    }
}
```

---

### Query Optimization

**Eager loading**:

```php
// Malo (N+1 queries)
$elementos = Elemento::all();
foreach ($elementos as $elemento) {
    echo $elemento->user->name; // Query por cada elemento
}

// Bueno (2 queries)
$elementos = Elemento::with('user')->get();
foreach ($elementos as $elemento) {
    echo $elemento->user->name;
}
```

---

### Transacciones

**Para operaciones múltiples**:

```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    $user = User::create([...]);
    $cuenta = UsuarioCuenta::create(['user_id' => $user->id]);
    $nivel = UsuarioNivel::create(['user_id' => $user->id]);
});
```

---

## 📚 Recursos Adicionales

- **Laravel Docs**: https://laravel.com/docs/11.x
- **Sanctum Docs**: https://laravel.com/docs/11.x/sanctum
- **PHPUnit**: https://phpunit.de/
- **Railway Docs**: https://docs.railway.app/

---

**Última actualización**: Octubre 2025

**Mantenido por**: Equipo Tidy Development
