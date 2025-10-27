@extends('layouts.app')

@section('title', 'Logs del Sistema - Tidy Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-file-alt"></i> Logs del Sistema
                </h1>
                <div>
                    <a href="{{ route('admin.dashboard.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                    <button onclick="location.reload()" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>

            @if(isset($error))
                <div class="alert alert-warning" role="alert">
                    <strong>Advertencia:</strong> {{ $error }}
                </div>
            @endif

            <!-- Información del archivo de logs -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Información de Logs
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Archivo:</strong><br>
                            <small class="text-muted">{{ $logInfo['file_path'] ?? 'N/A' }}</small>
                        </div>
                        <div class="col-md-4">
                            <strong>Existe:</strong><br>
                            <span class="badge {{ ($logInfo['file_exists'] ?? false) ? 'bg-success' : 'bg-danger' }}">
                                {{ ($logInfo['file_exists'] ?? false) ? 'Sí' : 'No' }}
                            </span>
                        </div>
                        <div class="col-md-4">
                            <strong>Tamaño:</strong><br>
                            <span class="text-muted">{{ number_format($logInfo['file_size'] ?? 0) }} bytes</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-info">
                            <i class="fas fa-lightbulb"></i>
                            Para Railway, los logs también están disponibles usando: <code>railway logs</code>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Logs recientes -->
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> Entradas Recientes
                        @if(count($logs ?? []) > 0)
                            <span class="badge bg-info ms-2">{{ count($logs) }} entradas</span>
                        @endif
                    </h6>
                </div>
                <div class="card-body">
                    @if(empty($logs))
                        <div class="text-center py-4">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay logs disponibles</h5>
                            <p class="text-muted">
                                Esto puede suceder si:
                                <br>• El archivo de logs no existe aún
                                <br>• Los logs se envían a stderr (Railway)
                                <br>• No se han generado logs recientes
                            </p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="150">Fecha/Hora</th>
                                        <th width="100">Nivel</th>
                                        <th>Mensaje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $log)
                                        <tr>
                                            <td>
                                                <small class="text-muted">{{ $log['date'] ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $level = strtoupper($log['level'] ?? 'INFO');
                                                    $badgeClass = match($level) {
                                                        'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY' => 'bg-danger',
                                                        'WARNING' => 'bg-warning',
                                                        'INFO' => 'bg-info',
                                                        'DEBUG' => 'bg-secondary',
                                                        default => 'bg-light text-dark'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ $level }}</span>
                                            </td>
                                            <td>
                                                <small>{{ Str::limit($log['message'] ?? '', 200) }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Comandos útiles para Railway -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fab fa-docker"></i> Comandos Railway (CLI)
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Para ver logs en tiempo real desde la terminal:</p>
                    <div class="bg-dark text-light p-3 rounded">
                        <code>
                            # Ver logs en tiempo real<br>
                            railway logs<br><br>
                            # Ver logs de los últimos N minutos<br>
                            railway logs --tail 100<br><br>
                            # Filtrar logs por nivel<br>
                            railway logs | grep ERROR
                        </code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge {
    font-size: 0.75rem;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

code {
    color: #06d6a0;
}
</style>
@endsection