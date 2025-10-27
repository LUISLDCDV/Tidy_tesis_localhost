@extends('layouts.app')

@section('title', 'Gestión de Comentarios')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestión de Comentarios y Solicitudes</h1>
        <a href="{{ route('admin.comments.dashboard') }}" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm">
            <i class="fas fa-chart-area fa-sm text-white-50"></i> Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Estadísticas Rápidas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-comments fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
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

        <div class="col-xl-3 col-md-6 mb-3">
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

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Sin Respuesta</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['unresponded'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-question-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.comments.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="status">Estado:</label>
                        <select name="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendientes</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resueltos</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Cerrados</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="type">Tipo:</label>
                        <select name="type" class="form-control">
                            <option value="">Todos</option>
                            <option value="help_request" {{ request('type') == 'help_request' ? 'selected' : '' }}>Solicitudes de Ayuda</option>
                            <option value="suggestion" {{ request('type') == 'suggestion' ? 'selected' : '' }}>Sugerencias</option>
                            <option value="bug_report" {{ request('type') == 'bug_report' ? 'selected' : '' }}>Reportes de Bugs</option>
                            <option value="feedback" {{ request('type') == 'feedback' ? 'selected' : '' }}>Comentarios</option>
                            <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Otros</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="priority">Prioridad:</label>
                        <select name="priority" class="form-control">
                            <option value="">Todas</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgente</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Media</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Baja</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="search">Buscar:</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Asunto, comentario, usuario..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Comentarios -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Comentarios ({{ $comments->total() }} total)
            </h6>
        </div>
        <div class="card-body">
            @if($comments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Asunto</th>
                                <th>Tipo</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Respondido por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comments as $comment)
                                <tr class="{{ $comment->isOverdue() ? 'table-warning' : '' }}">
                                    <td>{{ $comment->id }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $comment->getUserDisplayName() }}</strong>
                                            @if($comment->getUserEmail())
                                                <br><small class="text-muted">{{ $comment->getUserEmail() }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            {{ Str::limit($comment->subject, 40) }}
                                            @if($comment->isOverdue())
                                                <br><small class="text-warning"><i class="fas fa-clock"></i> Vencido</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $comment->type_text }}</span>
                                    </td>
                                    <td>
                                        <select class="form-control form-control-sm priority-select" data-comment-id="{{ $comment->id }}">
                                            <option value="low" {{ $comment->priority == 'low' ? 'selected' : '' }}>Baja</option>
                                            <option value="medium" {{ $comment->priority == 'medium' ? 'selected' : '' }}>Media</option>
                                            <option value="high" {{ $comment->priority == 'high' ? 'selected' : '' }}>Alta</option>
                                            <option value="urgent" {{ $comment->priority == 'urgent' ? 'selected' : '' }}>Urgente</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control form-control-sm status-select" data-comment-id="{{ $comment->id }}">
                                            <option value="pending" {{ $comment->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                            <option value="in_progress" {{ $comment->status == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                                            <option value="resolved" {{ $comment->status == 'resolved' ? 'selected' : '' }}>Resuelto</option>
                                            <option value="closed" {{ $comment->status == 'closed' ? 'selected' : '' }}>Cerrado</option>
                                        </select>
                                    </td>
                                    <td>{{ $comment->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($comment->respondedBy)
                                            <small>{{ $comment->respondedBy->name }}</small>
                                        @else
                                            <span class="text-muted">Sin responder</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.comments.show', $comment->id) }}" class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-comment-id="{{ $comment->id }}" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-center">
                    {{ $comments->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">No hay comentarios</h5>
                    <p class="text-gray-400">No se encontraron comentarios que coincidan con los filtros aplicados.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar este comentario? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Actualizar prioridad
    $('.priority-select').change(function() {
        var commentId = $(this).data('comment-id');
        var priority = $(this).val();

        $.ajax({
            url: '/admin/comments/' + commentId + '/priority',
            method: 'PATCH',
            data: {
                priority: priority,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Prioridad actualizada');
                } else {
                    toastr.error('Error actualizando prioridad');
                }
            },
            error: function() {
                toastr.error('Error actualizando prioridad');
            }
        });
    });

    // Actualizar estado
    $('.status-select').change(function() {
        var commentId = $(this).data('comment-id');
        var status = $(this).val();

        $.ajax({
            url: '/admin/comments/' + commentId + '/status',
            method: 'PATCH',
            data: {
                status: status,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Estado actualizado');
                    // Recargar la página para actualizar estadísticas
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error('Error actualizando estado');
                }
            },
            error: function() {
                toastr.error('Error actualizando estado');
            }
        });
    });

    // Eliminar comentario
    $('.delete-btn').click(function() {
        var commentId = $(this).data('comment-id');
        $('#deleteForm').attr('action', '/admin/comments/' + commentId);
        $('#deleteModal').modal('show');
    });
});
</script>
@endsection