@extends('layouts.app')

@section('title', 'Dashboard de Comentarios')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard de Comentarios</h1>
        <a href="{{ route('admin.comments.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-comments fa-sm text-white-50"></i> Ver Todos los Comentarios
        </a>
    </div>

    <!-- Estadísticas Generales -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Comentarios</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-comments fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pendientes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Alta Prioridad</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['high_priority'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Vencidos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['overdue'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Comentarios de Alta Prioridad -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-danger">Comentarios de Alta Prioridad</h6>
                    <span class="badge badge-danger">{{ $urgentComments->count() }}</span>
                </div>
                <div class="card-body">
                    @if($urgentComments->count() > 0)
                        @foreach($urgentComments as $comment)
                            <div class="d-flex align-items-center mb-3 p-2 border-left border-danger">
                                <div class="flex-grow-1">
                                    <div class="small text-gray-500 mb-1">
                                        <strong>{{ $comment->getUserDisplayName() }}</strong> •
                                        {{ $comment->created_at->diffForHumans() }}
                                    </div>
                                    <div class="h6 mb-1">{{ $comment->subject }}</div>
                                    <div class="small">
                                        <span class="badge badge-{{ $comment->priority_color }}">{{ $comment->priority_text }}</span>
                                        <span class="badge badge-secondary">{{ $comment->type_text }}</span>
                                    </div>
                                </div>
                                <div class="ml-2">
                                    <a href="{{ route('admin.comments.show', $comment->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No hay comentarios de alta prioridad pendientes.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Comentarios Vencidos -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-dark">Comentarios Vencidos (>7 días)</h6>
                    <span class="badge badge-dark">{{ $overdueComments->count() }}</span>
                </div>
                <div class="card-body">
                    @if($overdueComments->count() > 0)
                        @foreach($overdueComments as $comment)
                            <div class="d-flex align-items-center mb-3 p-2 border-left border-dark">
                                <div class="flex-grow-1">
                                    <div class="small text-gray-500 mb-1">
                                        <strong>{{ $comment->getUserDisplayName() }}</strong> •
                                        {{ $comment->created_at->diffForHumans() }}
                                    </div>
                                    <div class="h6 mb-1">{{ $comment->subject }}</div>
                                    <div class="small">
                                        <span class="badge badge-{{ $comment->priority_color }}">{{ $comment->priority_text }}</span>
                                        <span class="badge badge-secondary">{{ $comment->type_text }}</span>
                                    </div>
                                </div>
                                <div class="ml-2">
                                    <a href="{{ route('admin.comments.show', $comment->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No hay comentarios vencidos.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Comentarios Recientes -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Comentarios Recientes</h6>
        </div>
        <div class="card-body">
            @if($recentComments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Asunto</th>
                                <th>Tipo</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentComments as $comment)
                                <tr>
                                    <td>{{ $comment->getUserDisplayName() }}</td>
                                    <td>{{ Str::limit($comment->subject, 40) }}</td>
                                    <td><span class="badge badge-secondary">{{ $comment->type_text }}</span></td>
                                    <td><span class="badge badge-{{ $comment->priority_color }}">{{ $comment->priority_text }}</span></td>
                                    <td><span class="badge badge-{{ $comment->status_color }}">{{ $comment->status_text }}</span></td>
                                    <td>{{ $comment->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.comments.show', $comment->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center">No hay comentarios recientes.</p>
            @endif
        </div>
    </div>

    <!-- Estadísticas por Tipo -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Comentarios por Tipo</h6>
                </div>
                <div class="card-body">
                    @if(isset($stats['by_type']) && count($stats['by_type']) > 0)
                        @foreach($stats['by_type'] as $type => $count)
                            @php
                                $typeTexts = [
                                    'help_request' => 'Solicitudes de Ayuda',
                                    'suggestion' => 'Sugerencias',
                                    'bug_report' => 'Reportes de Bugs',
                                    'feedback' => 'Comentarios',
                                    'other' => 'Otros'
                                ];
                                $typeText = $typeTexts[$type] ?? $type;
                                $percentage = $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0;
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>{{ $typeText }}</span>
                                    <span>{{ $count }} ({{ number_format($percentage, 1) }}%)</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No hay datos disponibles.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Comentarios por Estado</h6>
                </div>
                <div class="card-body">
                    @if(isset($stats['by_status']) && count($stats['by_status']) > 0)
                        @foreach($stats['by_status'] as $status => $count)
                            @php
                                $statusTexts = [
                                    'pending' => 'Pendientes',
                                    'in_progress' => 'En Progreso',
                                    'resolved' => 'Resueltos',
                                    'closed' => 'Cerrados'
                                ];
                                $statusText = $statusTexts[$status] ?? $status;
                                $percentage = $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0;
                                $colors = [
                                    'pending' => 'warning',
                                    'in_progress' => 'info',
                                    'resolved' => 'success',
                                    'closed' => 'secondary'
                                ];
                                $color = $colors[$status] ?? 'primary';
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>{{ $statusText }}</span>
                                    <span>{{ $count }} ({{ number_format($percentage, 1) }}%)</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No hay datos disponibles.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection