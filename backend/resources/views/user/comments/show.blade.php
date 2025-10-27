@extends('layouts.app')

@section('title', 'Comentario #' . $comment->id)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    Comentario #{{ $comment->id }}
                    <span class="badge badge-{{ $comment->status_color }} ml-2">{{ $comment->status_text }}</span>
                </h2>
                <div>
                    <a href="{{ route('user.comments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Mis Comentarios
                    </a>
                    <a href="{{ route('user.comments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Comentario
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <!-- Información del Comentario -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Detalles del Comentario</h5>
                            <div>
                                <span class="badge badge-{{ $comment->priority_color }}">{{ $comment->priority_text }}</span>
                                <span class="badge badge-secondary">{{ $comment->type_text }}</span>
                                @if($comment->isOverdue())
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> Vencido</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Asunto:</strong></div>
                                <div class="col-sm-9">{{ $comment->subject }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Tipo:</strong></div>
                                <div class="col-sm-9">
                                    @switch($comment->type)
                                        @case('help_request')
                                            <i class="fas fa-life-ring text-info"></i> Solicitud de ayuda
                                            @break
                                        @case('suggestion')
                                            <i class="fas fa-lightbulb text-warning"></i> Sugerencia
                                            @break
                                        @case('bug_report')
                                            <i class="fas fa-bug text-danger"></i> Reporte de error
                                            @break
                                        @case('feedback')
                                            <i class="fas fa-comment text-success"></i> Comentario general
                                            @break
                                        @default
                                            <i class="fas fa-question text-secondary"></i> Otro
                                    @endswitch
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Prioridad:</strong></div>
                                <div class="col-sm-9">
                                    <span class="badge badge-{{ $comment->priority_color }}">{{ $comment->priority_text }}</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Estado:</strong></div>
                                <div class="col-sm-9">
                                    <span class="badge badge-{{ $comment->status_color }}">{{ $comment->status_text }}</span>
                                    @if($comment->status == 'resolved')
                                        <small class="text-success ml-2">¡Tu comentario ha sido resuelto!</small>
                                    @elseif($comment->status == 'in_progress')
                                        <small class="text-info ml-2">Estamos trabajando en tu solicitud</small>
                                    @elseif($comment->status == 'pending')
                                        <small class="text-warning ml-2">Tu comentario está pendiente de revisión</small>
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Comentario:</strong></div>
                                <div class="col-sm-9">
                                    <div class="bg-light p-3 rounded">
                                        {{ $comment->comment }}
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Fecha de envío:</strong></div>
                                <div class="col-sm-9">
                                    {{ $comment->created_at->format('d/m/Y H:i:s') }}
                                    <small class="text-muted">({{ $comment->created_at->diffForHumans() }})</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Respuesta del Administrador -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0 text-success">
                                <i class="fas fa-reply"></i> Respuesta del Equipo de Soporte
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($comment->admin_response)
                                <div class="alert alert-success">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Respuesta:</strong>
                                        <small class="text-muted">
                                            @if($comment->respondedBy)
                                                por {{ $comment->respondedBy->name }}
                                            @endif
                                            {{ $comment->responded_at ? $comment->responded_at->format('d/m/Y H:i') : '' }}
                                        </small>
                                    </div>
                                    <div>{{ $comment->admin_response }}</div>
                                </div>

                                @if($comment->status == 'resolved')
                                    <div class="alert alert-info">
                                        <i class="fas fa-check-circle"></i>
                                        <strong>¡Resuelto!</strong> Si tienes más preguntas o el problema persiste,
                                        no dudes en <a href="{{ route('user.comments.create') }}">enviar un nuevo comentario</a>.
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-clock"></i>
                                    <strong>Pendiente de respuesta</strong>
                                    <p class="mb-0">Nuestro equipo de soporte revisará tu comentario pronto.
                                    Te notificaremos cuando tengamos una respuesta.</p>
                                </div>

                                @if($comment->isOverdue())
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Atención:</strong> Tu comentario ha estado pendiente por más de 7 días.
                                        Si es urgente, considera
                                        <a href="{{ route('user.comments.create') }}">enviar un nuevo comentario</a>
                                        con prioridad alta.
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Panel Lateral -->
                <div class="col-lg-4 mb-4">
                    <!-- Estado del Comentario -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Estado del Comentario</h6>
                        </div>
                        <div class="card-body">
                            <div class="progress mb-3" style="height: 20px;">
                                @php
                                    $progress = 25;
                                    $progressClass = 'bg-warning';
                                    if($comment->status == 'in_progress') {
                                        $progress = 50;
                                        $progressClass = 'bg-info';
                                    } elseif($comment->status == 'resolved') {
                                        $progress = 100;
                                        $progressClass = 'bg-success';
                                    } elseif($comment->status == 'closed') {
                                        $progress = 100;
                                        $progressClass = 'bg-secondary';
                                    }
                                @endphp
                                <div class="progress-bar {{ $progressClass }}" style="width: {{ $progress }}%">
                                    {{ $progress }}%
                                </div>
                            </div>

                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Enviado</h6>
                                        <p class="timeline-date">{{ $comment->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>

                                @if($comment->status != 'pending')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">En revisión</h6>
                                            <p class="timeline-date">{{ $comment->updated_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($comment->admin_response)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-primary"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">Respondido</h6>
                                            <p class="timeline-date">{{ $comment->responded_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($comment->status == 'resolved')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">Resuelto</h6>
                                            <p class="timeline-date">{{ $comment->updated_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Información de Contacto -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">¿Necesitas más ayuda?</h6>
                        </div>
                        <div class="card-body">
                            <p class="card-text">Si tienes más preguntas o necesitas asistencia adicional:</p>
                            <div class="d-grid gap-2">
                                <a href="{{ route('user.comments.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Nuevo Comentario
                                </a>
                                <a href="{{ route('user.comments.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-list"></i> Ver Mis Comentarios
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-size: 14px;
}

.timeline-date {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 0;
}
</style>
@endsection