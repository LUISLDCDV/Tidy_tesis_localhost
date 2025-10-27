@extends('layouts.app')

@section('title', 'Mi Perfil - Tidy')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user"></i> {{ __('Mi Perfil') }}
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-circle fa-5x text-primary"></i>
                            </div>
                            @if($user->usuarioCuenta && $user->usuarioCuenta->is_premium)
                                <span class="badge badge-warning mb-2">
                                    <i class="fas fa-crown"></i> Usuario Premium
                                </span>
                            @else
                                <span class="badge badge-secondary mb-2">Usuario Gratuito</span>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h4>{{ $user->name }} {{ $user->last_name }}</h4>

                            <div class="mb-3">
                                <strong><i class="fas fa-envelope"></i> Email:</strong><br>
                                {{ $user->email }}
                            </div>

                            @if($user->phone)
                                <div class="mb-3">
                                    <strong><i class="fas fa-phone"></i> Teléfono:</strong><br>
                                    {{ $user->phone }}
                                </div>
                            @endif

                            @if($user->usuarioCuenta)
                                <div class="mb-3">
                                    <strong><i class="fas fa-trophy"></i> Nivel:</strong><br>
                                    Nivel {{ $user->usuarioCuenta->current_level ?? 1 }}
                                    <div class="progress mt-1" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar"
                                             style="width: {{ min(($user->usuarioCuenta->total_xp ?? 0) / 1000 * 100, 100) }}%">
                                            {{ number_format($user->usuarioCuenta->total_xp ?? 0) }} XP
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="mb-3">
                                <strong><i class="fas fa-calendar"></i> Miembro desde:</strong><br>
                                {{ $user->created_at->format('d/m/Y') }}
                            </div>

                            @if($user->last_login_at)
                                <div class="mb-3">
                                    <strong><i class="fas fa-clock"></i> Último acceso:</strong><br>
                                    {{ $user->last_login_at->diffForHumans() }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-cog fa-2x text-primary mb-2"></i>
                                    <h6>Configuraciones</h6>
                                    <a href="{{ route('perfil.edit') }}" class="btn btn-outline-primary btn-sm">
                                        Editar Perfil
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-bar fa-2x text-success mb-2"></i>
                                    <h6>Mis Estadísticas</h6>
                                    <a href="#" class="btn btn-outline-success btn-sm">
                                        Ver Actividad
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!($user->usuarioCuenta && $user->usuarioCuenta->is_premium))
                        <div class="mt-4">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-crown fa-2x text-warning mb-2"></i>
                                    <h5>¡Upgradea a Premium!</h5>
                                    <p class="text-muted">Desbloquea características avanzadas y obtén más espacio de almacenamiento.</p>
                                    <a href="#" class="btn btn-warning">
                                        <i class="fas fa-star"></i> Obtener Premium
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection