@extends('layouts.app')

@section('title', 'Gestión de Usuarios - Admin Tidy')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">👥 Gestión de Usuarios</h1>
                <a href="{{ route('home') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>

            @if(isset($error))
                <div class="alert alert-danger" role="alert">
                    <strong>Error:</strong> {{ $error }}
                </div>
            @endif

            @if($usersData)
                <!-- Filtros de búsqueda -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold" style="color: #1976D2; font-family: 'Google Sans Text', sans-serif;">🔍 Filtros de Búsqueda</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.dashboard.users') }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="search">Buscar:</label>
                                        <input type="text" class="form-control" id="search" name="search"
                                               value="{{ request('search') }}" placeholder="Nombre, apellido o email...">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="level">Nivel:</label>
                                        <select class="form-control" id="level" name="level">
                                            <option value="">Todos</option>
                                            @for($i = 1; $i <= 10; $i++)
                                                <option value="{{ $i }}" {{ request('level') == $i ? 'selected' : '' }}>
                                                    Nivel {{ $i }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="premium">Premium:</label>
                                        <select class="form-control" id="premium" name="premium">
                                            <option value="">Todos</option>
                                            <option value="true" {{ request('premium') === 'true' ? 'selected' : '' }}>Premium</option>
                                            <option value="false" {{ request('premium') === 'false' ? 'selected' : '' }}>Gratuito</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="sort_by">Ordenar por:</label>
                                        <select class="form-control" id="sort_by" name="sort_by">
                                            <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Fecha de registro</option>
                                            <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Nombre</option>
                                            <option value="experience" {{ request('sort_by') === 'experience' ? 'selected' : '' }}>Experiencia</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de usuarios -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold" style="color: #1976D2; font-family: 'Google Sans Text', sans-serif;">
                            📋 Lista de Usuarios ({{ $usersData['total'] ?? 0 }} total)
                        </h6>
                    </div>
                    <div class="card-body">
                        @if(isset($usersData['data']) && count($usersData['data']) > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Usuario</th>
                                            <th>Email</th>
                                            <th>Nivel</th>
                                            <th>XP Total</th>
                                            <th>Premium</th>
                                            <th>Registro</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($usersData['data'] as $user)
                                            <tr>
                                                <td>{{ $user['id'] }}</td>
                                                <td>
                                                    <strong>{{ $user['name'] }} {{ $user['last_name'] }}</strong>
                                                    @if($user['phone'])
                                                        <br><small class="text-muted">{{ $user['phone'] }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $user['email'] }}</td>
                                                                <td>
                                                    @if(isset($user['userLevel']))
                                                        <span class="badge bg-info text-white rounded-pill">
                                                            Nivel {{ $user['userLevel']->level ?? 0 }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary text-white rounded-pill">Sin nivel</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(isset($user['userLevel']))
                                                        {{ number_format($user['userLevel']->total_experience ?? 0) }} XP
                                                    @else
                                                        0 XP
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(isset($user['cuenta']) && $user['cuenta']->is_premium)
                                                        <span class="badge bg-warning text-dark rounded-pill">
                                                            👑 Premium
                                                        </span>
                                                        @if($user['cuenta']->premium_expires_at)
                                                            <br><small class="text-muted">Hasta {{ \Carbon\Carbon::parse($user['cuenta']->premium_expires_at)->format('d/m/Y') }}</small>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-light text-dark rounded-pill">Gratuito</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small>{{ \Carbon\Carbon::parse($user['created_at'])->format('d/m/Y H:i') }}</small>
                                                </td>
                                                <td>
                                                    <div >
                                                                <a class="dropdown-item" href="#"
                                                                   onclick="showUserDetails({{ $user['id'] }})">
                                                                    <i class="fas fa-eye"></i> Ver detalles
                                                                </a>
                                                                <a class="dropdown-item" href="#"
                                                                   onclick="editUserExperience({{ $user['id'] }}, {{ json_encode($user['name']) }}, {{ isset($user['userLevel']) ? $user['userLevel']->total_experience : 0 }})">
                                                                    <i class="fas fa-coins"></i> Editar experiencia
                                                                </a>
                                                                <a class="dropdown-item" href="#"
                                                                   onclick="editUserLevel({{ $user['id'] }}, {{ json_encode($user['name']) }}, {{ isset($user['userLevel']) ? $user['userLevel']->level : 0 }})">
                                                                    <i class="fas fa-star"></i> Editar nivel
                                                                </a>
                                                                <a class="dropdown-item" href="#"
                                                                   onclick="editUserPremium({{ $user['id'] }}, {{ json_encode($user['name']) }}, false)">
                                                                    <i class="fas fa-crown"></i> Gestionar Premium
                                                                </a>
                                                                <a class="dropdown-item" href="#"
                                                                   onclick="sendNotificationToUser({{ $user['id'] }}, {{ json_encode($user['name']) }})">
                                                                    <i class="fas fa-bell"></i> Enviar notificación
                                                                </a>
                                                                <a class="dropdown-item" href="#"
                                                                   onclick="sendTestEmail({{ $user['id'] }}, {{ json_encode($user['email']) }})">
                                                                    <i class="fas fa-envelope"></i> Enviar email prueba
                                                                </a>
                                                                <a class="dropdown-item text-warning" href="#"
                                                                   onclick="softDeleteUser({{ $user['id'] }}, {{ json_encode($user['name']) }})">
                                                                    <i class="fas fa-user-times"></i> Desactivar
                                                                </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            @if(isset($usersData['last_page']) && $usersData['last_page'] > 1)
                                <nav aria-label="Paginación de usuarios">
                                    <ul class="pagination justify-content-center">
                                        @if($usersData['current_page'] > 1)
                                            <li class="page-item">
                                                <a class="page-link" href="?page={{ $usersData['current_page'] - 1 }}{{ http_build_query(request()->except('page')) ? '&' . http_build_query(request()->except('page')) : '' }}">
                                                    Anterior
                                                </a>
                                            </li>
                                        @endif

                                        @for($i = 1; $i <= $usersData['last_page']; $i++)
                                            <li class="page-item {{ $i == $usersData['current_page'] ? 'active' : '' }}">
                                                <a class="page-link" href="?page={{ $i }}{{ http_build_query(request()->except('page')) ? '&' . http_build_query(request()->except('page')) : '' }}">
                                                    {{ $i }}
                                                </a>
                                            </li>
                                        @endfor

                                        @if($usersData['current_page'] < $usersData['last_page'])
                                            <li class="page-item">
                                                <a class="page-link" href="?page={{ $usersData['current_page'] + 1 }}{{ http_build_query(request()->except('page')) ? '&' . http_build_query(request()->except('page')) : '' }}">
                                                    Siguiente
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                            @endif

                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                                <h5>No se encontraron usuarios</h5>
                                <p class="text-muted">Intenta ajustar los filtros de búsqueda.</p>
                            </div>
                        @endif
                    </div>
                </div>

            @else
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-heading">👥 Gestión de usuarios no disponible</h4>
                    <p>No se pudieron cargar los datos de usuarios.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal para editar experiencia -->
<div class="modal fade" id="editExperienceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editExperienceForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">
                        🪙 Editar Experiencia
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="userNameDisplay" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Usuario:</label>
                        <input type="text" id="userNameDisplay" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="experience" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Nueva experiencia (XP):</label>
                        <input type="number" name="experience" id="experience" class="form-control" min="0" max="757500" required>
                        <small class="form-text text-muted">El nivel se calculará automáticamente (máx: 757,500 XP)</small>
                    </div>
                    <div class="form-group">
                        <label for="reason" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Razón del cambio (opcional):</label>
                        <input type="text" name="reason" id="reason" class="form-control" maxlength="255"
                               placeholder="Ej: Corrección por error, bonificación especial...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Experiencia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para enviar notificación -->
<div class="modal fade" id="sendNotificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="sendNotificationForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">
                        🔔 Enviar Notificación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="notifUserNameDisplay" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Usuario:</label>
                        <input type="text" id="notifUserNameDisplay" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="title" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Título:</label>
                        <input type="text" name="title" id="title" class="form-control" maxlength="255" required
                               placeholder="Ej: Mensaje importante del administrador">
                    </div>
                    <div class="form-group">
                        <label for="message" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Mensaje:</label>
                        <textarea name="message" id="message" class="form-control" rows="4" maxlength="1000" required
                                  placeholder="Escribe tu mensaje aquí..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="type" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Tipo:</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="info">Información</option>
                            <option value="success">Éxito</option>
                            <option value="warning">Advertencia</option>
                            <option value="error">Error</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="send_email" id="send_email" class="form-check-input" value="1">
                        <label for="send_email" class="form-check-label" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">
                            También enviar por email
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Enviar Notificación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para enviar email de prueba -->
<div class="modal fade" id="sendTestEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="sendTestEmailForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">
                        📧 Enviar Email de Prueba
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="emailUserDisplay" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Destinatario:</label>
                        <input type="text" id="emailUserDisplay" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="subject" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Asunto:</label>
                        <input type="text" name="subject" id="subject" class="form-control" maxlength="255" required
                               placeholder="Ej: Prueba de conectividad de email">
                    </div>
                    <div class="form-group">
                        <label for="emailMessage" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Mensaje:</label>
                        <textarea name="message" id="emailMessage" class="form-control" rows="5" maxlength="2000" required
                                  placeholder="Escribe el contenido del email..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-envelope"></i> Enviar Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para soft delete -->
<div class="modal fade" id="softDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="softDeleteForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" style="color: #dc3545; font-family: 'Google Sans Text', sans-serif;">
                        ⚠️ Desactivar Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>¡Atención!</strong> Esta acción desactivará la cuenta del usuario.
                        El usuario será notificado por email y no podrá acceder hasta que sea reactivado.
                    </div>
                    <div class="form-group">
                        <label for="deleteUserNameDisplay" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Usuario a desactivar:</label>
                        <input type="text" id="deleteUserNameDisplay" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="deleteReason" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Razón de la desactivación:</label>
                        <input type="text" name="reason" id="deleteReason" class="form-control" maxlength="255" required
                               placeholder="Ej: Violación de términos de uso, solicitud del usuario...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-user-times"></i> Desactivar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar nivel -->
<div class="modal fade" id="editLevelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editLevelForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">
                        ⭐ Editar Nivel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="levelUserNameDisplay" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Usuario:</label>
                        <input type="text" id="levelUserNameDisplay" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="level" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Nuevo nivel:</label>
                        <input type="number" name="level" id="level" class="form-control" min="0" max="100" required>
                        <small class="form-text text-muted">Nivel del usuario (0-100)</small>
                    </div>
                    <div class="form-group">
                        <label for="levelReason" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Razón del cambio (opcional):</label>
                        <input type="text" name="reason" id="levelReason" class="form-control" maxlength="255"
                               placeholder="Ej: Ajuste manual de nivel...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Nivel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para gestionar Premium -->
<div class="modal fade" id="editPremiumModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editPremiumForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">
                        👑 Gestionar Premium
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="premiumUserNameDisplay" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Usuario:</label>
                        <input type="text" id="premiumUserNameDisplay" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="is_premium" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Estado Premium:</label>
                        <select name="is_premium" id="is_premium" class="form-control" required>
                            <option value="0">No Premium</option>
                            <option value="1">Premium</option>
                        </select>
                    </div>
                    <div class="form-group" id="premiumDatesGroup" style="display: none;">
                        <label for="premium_expires_at" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Fecha de expiración:</label>
                        <input type="date" name="premium_expires_at" id="premium_expires_at" class="form-control">
                        <small class="form-text text-muted">Dejar vacío para premium permanente</small>
                    </div>
                    <div class="form-group">
                        <label for="premiumReason" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">Razón del cambio (opcional):</label>
                        <input type="text" name="reason" id="premiumReason" class="form-control" maxlength="255"
                               placeholder="Ej: Promoción especial, premio...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Premium
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Tidy Dashboard - Gestión de Usuarios
// Inicialización limpia y sin logs excesivos

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Bootstrap dropdowns cuando esté disponible
    function initDropdowns() {
        if (typeof bootstrap === 'undefined') {
            setTimeout(initDropdowns, 100);
            return;
        }

        const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        dropdowns.forEach(dropdown => {
            try {
                new bootstrap.Dropdown(dropdown);
            } catch (e) {
                // Silencioso - bootstrap ya maneja los dropdowns automáticamente
            }
        });
    }

    initDropdowns();

    // Configurar confirmaciones de formularios
    const editForm = document.getElementById('editExperienceForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            const newExp = document.getElementById('experience').value;
            if (!confirm(`¿Cambiar experiencia a ${newExp} XP?`)) {
                e.preventDefault();
            }
        });
    }

    const deleteForm = document.getElementById('softDeleteForm');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            const userName = document.getElementById('deleteUserNameDisplay').value;
            if (!confirm(`¿Desactivar usuario ${userName}?`)) {
                e.preventDefault();
            }
        });
    }

    // Toggle fecha de expiración premium
    const isPremiumSelect = document.getElementById('is_premium');
    if (isPremiumSelect) {
        isPremiumSelect.addEventListener('change', function() {
            const premiumDatesGroup = document.getElementById('premiumDatesGroup');
            if (this.value === '1') {
                premiumDatesGroup.style.display = 'block';
            } else {
                premiumDatesGroup.style.display = 'none';
            }
        });
    }
});

// Funciones de modal con manejo de errores
window.editUserExperience = function(userId, userName, currentExp) {
    try {
        document.getElementById('userNameDisplay').value = userName || '';
        document.getElementById('experience').value = currentExp || 0;
        document.getElementById('editExperienceForm').action = `/admin/users/${userId}/edit-experience`;
        new bootstrap.Modal(document.getElementById('editExperienceModal')).show();
    } catch (e) {
        alert('Error abriendo formulario. Recarga la página e intenta nuevamente.');
    }
};

window.sendNotificationToUser = function(userId, userName) {
    try {
        document.getElementById('notifUserNameDisplay').value = userName || '';
        document.getElementById('sendNotificationForm').action = `/admin/users/${userId}/send-notification`;
        new bootstrap.Modal(document.getElementById('sendNotificationModal')).show();
    } catch (e) {
        alert('Error abriendo formulario. Recarga la página e intenta nuevamente.');
    }
};

window.sendTestEmail = function(userId, userEmail) {
    try {
        document.getElementById('emailUserDisplay').value = userEmail || '';
        document.getElementById('sendTestEmailForm').action = `/admin/users/${userId}/send-test-email`;
        new bootstrap.Modal(document.getElementById('sendTestEmailModal')).show();
    } catch (e) {
        alert('Error abriendo formulario. Recarga la página e intenta nuevamente.');
    }
};

window.softDeleteUser = function(userId, userName) {
    try {
        document.getElementById('deleteUserNameDisplay').value = userName || '';
        document.getElementById('softDeleteForm').action = `/admin/users/${userId}/soft-delete`;
        new bootstrap.Modal(document.getElementById('softDeleteModal')).show();
    } catch (e) {
        alert('Error abriendo formulario. Recarga la página e intenta nuevamente.');
    }
};

window.showUserDetails = function(userId) {
    alert('Funcionalidad en desarrollo. ID: ' + userId);
};

window.editUserLevel = function(userId, userName, currentLevel) {
    try {
        document.getElementById('levelUserNameDisplay').value = userName || '';
        document.getElementById('level').value = currentLevel || 0;
        document.getElementById('editLevelForm').action = `/admin/users/${userId}/edit-level`;
        new bootstrap.Modal(document.getElementById('editLevelModal')).show();
    } catch (e) {
        alert('Error abriendo formulario. Recarga la página e intenta nuevamente.');
    }
};

window.editUserPremium = function(userId, userName, isPremium) {
    try {
        document.getElementById('premiumUserNameDisplay').value = userName || '';
        document.getElementById('is_premium').value = isPremium ? '1' : '0';
        document.getElementById('editPremiumForm').action = `/admin/users/${userId}/edit-premium`;
        new bootstrap.Modal(document.getElementById('editPremiumModal')).show();
    } catch (e) {
        alert('Error abriendo formulario. Recarga la página e intenta nuevamente.');
    }
};

// Función toggleDropdown como fallback (aunque Bootstrap maneja los dropdowns automáticamente)
window.toggleDropdown = function(button) {
    // Esta función existe solo como fallback - Bootstrap 5 maneja los dropdowns automáticamente
    if (typeof bootstrap !== 'undefined') {
        const dropdown = bootstrap.Dropdown.getOrCreateInstance(button);
        dropdown.toggle();
    }
};
</script>

<style>
/* Mejorar la apariencia de los modales */
.modal-content {
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px 8px 0 0;
}

.modal-header .modal-title {
    color: white !important;
    font-weight: 500;
}

.modal-header .close {
    color: white;
    opacity: 0.8;
}

.modal-header .close:hover {
    opacity: 1;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
}

.alert-warning {
    border-left: 4px solid #ffc107;
}

/* Mejorar dropdowns */
.dropdown-menu {
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    border: none;
    z-index: 1055;
}

.dropdown-toggle::after {
    margin-left: 0.5em;
}

.dropdown-item {
    padding: 8px 16px;
    transition: all 0.2s ease;
    color: #495057;
    font-family: 'Google Sans Text', sans-serif;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    transform: translateX(2px);
    color: #495057;
}

.dropdown-item:active {
    background-color: #667eea;
    color: #fff;
}

.dropdown-item i {
    width: 16px;
    margin-right: 8px;
}

.dropdown-divider {
    margin: 0.5rem 0;
}

/* Badges más modernos - Bootstrap 5 compatible */
.badge {
    font-size: 0.75rem;
    padding: 0.5em 0.8em;
    font-weight: 500;
    font-family: 'Google Sans Text', sans-serif;
}

.badge.bg-info {
    background: linear-gradient(135deg, #0dcaf0, #0aa2c0) !important;
    color: #fff !important;
    border: none;
}

.badge.bg-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268) !important;
    color: #fff !important;
    border: none;
}

.badge.bg-light {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef) !important;
    color: #495057 !important;
    border: 1px solid #dee2e6;
}

/* Mejorar la visibilidad y estilo */
.badge.rounded-pill {
    border-radius: 50rem !important;
}

/* Asegurar que el texto sea visible */
.badge.text-white {
    color: #fff !important;
}

.badge.text-dark {
    color: #212529 !important;
}
</style>
@endsection