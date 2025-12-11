<!-- resources/views/home.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Tarjeta de Bienvenida -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-home"></i> Panel de Control - Tidy
                </div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('status') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <h4 class="mb-2">¡Hola, {{ $user->name }} {{ $user->last_name }}!</h4>
                        <p class="text-muted mb-1">
                            <i class="fas fa-envelope"></i> {{ $user->email }}
                        </p>
                        @if($user->phone)
                            <p class="text-muted">
                                <i class="fas fa-phone"></i> {{ $user->phone }}
                            </p>
                        @endif
                        <p class="mt-3" style="color: #1976D2;">
                            Bienvenido al panel de administración de Tidy, tu sistema de gestión personal.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sección de Soporte y Comentarios -->
            <div class="card mb-4" style="border-left: 4px solid #1976D2;">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-headset"></i> Centro de Soporte
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        ¿Tienes preguntas, sugerencias o necesitas ayuda con Tidy?
                        Nuestro equipo está disponible para asistirte.
                    </p>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-start mb-4">
                        <a href="{{ route('user.comments.create') }}" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Enviar Mensaje
                        </a>
                        <a href="{{ route('user.comments.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-inbox"></i> Mis Mensajes
                        </a>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <div class="p-3 bg-light rounded text-center">
                                <i class="fas fa-life-ring fa-2x mb-2" style="color: #17a2b8;"></i>
                                <div class="small fw-semibold">Soporte Técnico</div>
                                <div class="small text-muted">Ayuda con problemas</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="p-3 bg-light rounded text-center">
                                <i class="fas fa-lightbulb fa-2x mb-2" style="color: #ffc107;"></i>
                                <div class="small fw-semibold">Sugerencias</div>
                                <div class="small text-muted">Ideas de mejora</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="p-3 bg-light rounded text-center">
                                <i class="fas fa-bug fa-2x mb-2" style="color: #dc3545;"></i>
                                <div class="small fw-semibold">Reportar Errores</div>
                                <div class="small text-muted">Problemas técnicos</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="p-3 bg-light rounded text-center">
                                <i class="fas fa-comments fa-2x mb-2" style="color: #28a745;"></i>
                                <div class="small fw-semibold">Feedback General</div>
                                <div class="small text-muted">Tu opinión importa</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(auth()->user()->hasRole('admin'))
                <!-- Panel Administrativo -->
                <div class="card" style="border-left: 4px solid #ffc107;">
                    <div class="card-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: #1a1a1a;">
                        <h6 class="mb-0">
                            <i class="fas fa-shield-alt"></i> Panel de Administración
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Herramientas administrativas para gestionar el sistema Tidy.</p>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <a href="{{ route('admin.comments.dashboard') }}" class="btn btn-warning w-100 py-3">
                                    <i class="fas fa-chart-line d-block mb-2" style="font-size: 1.5rem;"></i>
                                    <div class="fw-semibold">Dashboard</div>
                                    <small class="d-block text-muted">Estadísticas</small>
                                </a>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <a href="{{ route('admin.comments.index') }}" class="btn btn-outline-warning w-100 py-3">
                                    <i class="fas fa-envelope-open-text d-block mb-2" style="font-size: 1.5rem;"></i>
                                    <div class="fw-semibold">Mensajes</div>
                                    <small class="d-block text-muted">Gestionar soporte</small>
                                </a>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <a href="{{ route('admin.dashboard.users') }}" class="btn btn-outline-warning w-100 py-3">
                                    <i class="fas fa-users-cog d-block mb-2" style="font-size: 1.5rem;"></i>
                                    <div class="fw-semibold">Usuarios</div>
                                    <small class="d-block text-muted">Administrar usuarios</small>
                                </a>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <a href="{{ route('admin.dashboard.payments') }}" class="btn btn-outline-warning w-100 py-3">
                                    <i class="fas fa-file-invoice-dollar d-block mb-2" style="font-size: 1.5rem;"></i>
                                    <div class="fw-semibold">Pagos</div>
                                    <small class="d-block text-muted">Historial financiero</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
