@extends('layouts.app')

@section('title', 'Dashboard Administrativo - Tidy')

@section('styles')
<style>
.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.table-borderless td {
    border: none;
    padding: 0.75rem 0.5rem;
}

.table-borderless thead th {
    border: none;
    padding: 0.5rem;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge {
    font-size: 0.7rem;
}

.badge i {
    margin-right: 2px;
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">📊 Dashboard Administrativo</h1>
                <div class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-download fa-sm text-white-50"></i> Generar Reporte
                </div>
            </div>

            @if(isset($error))
                <div class="alert alert-danger" role="alert">
                    <strong>Error:</strong> {{ $error }}
                </div>
            @endif

            @if($dashboardData)
                <!-- Estadísticas Principales -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Usuarios
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ number_format($dashboardData['system_stats']['total_users']) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Usuarios Premium
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ number_format($dashboardData['system_stats']['premium_users']) }}
                                            <small class="text-muted">({{ $dashboardData['system_stats']['premium_percentage'] }}%)</small>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-crown fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Activos Hoy
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ number_format($dashboardData['system_stats']['active_users_today']) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Total Elementos
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ number_format($dashboardData['element_stats']['total_elements']) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navegación del Dashboard -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">🚀 Navegación Rápida</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="{{ route('admin.dashboard.users') }}" class="btn btn-outline-primary btn-block">
                                            <i class="fas fa-users-cog"></i> Gestión de Usuarios
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="{{ route('admin.dashboard.charts') }}" class="btn btn-outline-success btn-block">
                                            <i class="fas fa-chart-line"></i> Estadísticas
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="/admin/levels" class="btn btn-outline-info btn-block">
                                            <i class="fas fa-trophy"></i> Configuración Niveles
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="/admin/ranking" class="btn btn-outline-warning btn-block">
                                            <i class="fas fa-medal"></i> Rankings
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-secondary btn-block">
                                            <i class="fas fa-file-alt"></i> Logs del Sistema
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actividad Reciente -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">👥 Administradores del Sistema</h6>
                                <a href="{{ route('usuarios.index', Auth::user()->id) }}" class="btn btn-sm btn-outline-primary">
                                    Ver Todos
                                </a>
                            </div>
                            <div class="card-body">
                                @if(count($dashboardData['recent_activity']['recent_users']) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-borderless">
                                            <thead class="small text-uppercase text-muted">
                                                <tr>
                                                    <th>Usuario</th>
                                                    <th>Estado</th>
                                                    <th>Actividad</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dashboardData['recent_activity']['recent_users'] as $user)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="mr-3">
                                                                    @if($user['is_admin'])
                                                                        <div class="icon-circle bg-danger">
                                                                            <i class="fas fa-user-shield text-white"></i>
                                                                        </div>
                                                                    @else
                                                                        <div class="icon-circle bg-secondary">
                                                                            <i class="fas fa-user text-white"></i>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div>
                                                                    <div class="font-weight-bold">{{ $user['name'] }}</div>
                                                                    <div class="small text-muted">{{ $user['email'] }}</div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex flex-column">
                                                                @if($user['premium'])
                                                                    <span class="badge badge-warning mb-1">
                                                                        <i class="fas fa-star"></i> Premium
                                                                    </span>
                                                                    @if($user['premium_expires'])
                                                                        <small class="text-muted">Hasta: {{ $user['premium_expires'] }}</small>
                                                                    @endif
                                                                @else
                                                                    <span class="badge badge-light">Gratuito</span>
                                                                @endif
                                                                <div class="mt-1">
                                                                    <span class="badge badge-info">
                                                                        <i class="fas fa-trophy"></i> Nivel {{ $user['level'] }}
                                                                    </span>
                                                                    <small class="text-muted ml-1">{{ number_format($user['total_xp']) }} XP</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="small text-success">
                                                                <i class="fas fa-clock"></i> {{ $user['created_at'] }}
                                                            </div>
                                                            <small class="text-muted">Registrado</small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-3x mb-3"></i>
                                        <p>No hay usuarios administradores registrados</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-success">📝 Estadísticas de Elementos</h6>
                                <a href="{{ route('admin.dashboard.charts') }}" class="btn btn-sm btn-outline-success">
                                    Ver Gráficos
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-6 mb-3">
                                        <div class="border rounded p-3">
                                            <div class="h3 font-weight-bold text-success">
                                                {{ $dashboardData['element_stats']['total_elements'] ?? 0 }}
                                            </div>
                                            <div class="small text-muted">Total Elementos</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="border rounded p-3">
                                            <div class="h3 font-weight-bold text-info">
                                                {{ $dashboardData['element_stats']['elements_today'] ?? 0 }}
                                            </div>
                                            <div class="small text-muted">Creados Hoy</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="border rounded p-3">
                                            <div class="h3 font-weight-bold text-warning">
                                                {{ $dashboardData['element_stats']['elements_this_week'] ?? 0 }}
                                            </div>
                                            <div class="small text-muted">Esta Semana</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="border rounded p-3">
                                            <div class="h3 font-weight-bold text-primary">
                                                {{ $dashboardData['element_stats']['elements_this_month'] ?? 0 }}
                                            </div>
                                            <div class="small text-muted">Este Mes</div>
                                        </div>
                                    </div>
                                </div>

                                @if(($dashboardData['element_stats']['total_elements'] ?? 0) == 0)
                                    <div class="text-center text-muted mt-3">
                                        <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                        <p class="small">No hay elementos creados aún</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            @else
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-heading">📊 Dashboard no disponible</h4>
                    <p>No se pudieron cargar los datos del dashboard. Esto puede deberse a:</p>
                    <ul>
                        <li>Base de datos no disponible</li>
                        <li>Permisos insuficientes</li>
                        <li>Error de configuración</li>
                    </ul>
                    <hr>
                    <p class="mb-0">Contacta al administrador del sistema si el problema persiste.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.icon-circle {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
</style>
@endsection