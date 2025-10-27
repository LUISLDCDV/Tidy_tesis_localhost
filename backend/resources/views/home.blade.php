<!-- resources/views/home.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <h5>Bienvenido, {{ $user->name }} {{ $user->last_name }}!</h5>
                        <p class="text-muted mb-0">{{ $user->email }}</p>
                        @if($user->phone)
                            <p class="text-muted">{{ $user->phone }}</p>
                        @endif
                    </div>

                    <!-- Sección de Ayuda y Comentarios -->
                    <div class="card border-primary mt-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-question-circle"></i> ¿Necesitas ayuda?
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">Si tienes alguna pregunta, sugerencia o necesitas soporte, estamos aquí para ayudarte.</p>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                                <a href="{{ route('user.comments.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Enviar Comentario o Solicitud
                                </a>
                                <a href="{{ route('user.comments.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-list"></i> Mis Comentarios
                                </a>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-life-ring text-info me-2"></i>
                                        <small class="text-muted">Solicitudes de ayuda</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-lightbulb text-warning me-2"></i>
                                        <small class="text-muted">Sugerencias y mejoras</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-bug text-danger me-2"></i>
                                        <small class="text-muted">Reportes de errores</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-comment text-success me-2"></i>
                                        <small class="text-muted">Comentarios generales</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(auth()->user()->hasRole('admin'))
                        <!-- Panel Administrativo para Admins -->
                        <div class="card border-warning mt-4">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="fas fa-cogs"></i> Panel Administrativo
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <a href="{{ route('admin.comments.dashboard') }}" class="btn btn-warning btn-block mb-2">
                                            <i class="fas fa-chart-area"></i> Dashboard de Comentarios
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ route('admin.comments.index') }}" class="btn btn-outline-warning btn-block mb-2">
                                            <i class="fas fa-comments"></i> Gestionar Comentarios
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ route('admin.dashboard.users') }}" class="btn btn-outline-warning btn-block mb-2">
                                            <i class="fas fa-users"></i> Gestión de Usuarios
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ route('admin.dashboard.payments') }}" class="btn btn-outline-warning btn-block mb-2">
                                            <i class="fas fa-receipt"></i> Historial de Pagos
                                        </a>
                                    </div>
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
