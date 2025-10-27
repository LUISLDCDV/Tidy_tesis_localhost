@extends('layouts.app')

@section('title', 'Comentario #' . $comment->id)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Comentario #{{ $comment->id }}
            <span class="badge badge-{{ $comment->status_color }} ml-2">{{ $comment->status_text }}</span>
        </h1>
        <div>
            <a href="{{ route('admin.comments.index') }}" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver a la Lista
            </a>
            <button type="button" class="btn btn-sm btn-danger shadow-sm delete-btn" data-comment-id="{{ $comment->id }}">
                <i class="fas fa-trash fa-sm text-white-50"></i> Eliminar
            </button>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Información del Comentario -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Detalles del Comentario</h6>
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
                        <div class="col-sm-3"><strong>Usuario:</strong></div>
                        <div class="col-sm-9">
                            <div>{{ $comment->getUserDisplayName() }}</div>
                            @if($comment->getUserEmail())
                                <small class="text-muted">{{ $comment->getUserEmail() }}</small>
                            @endif
                            @if($comment->user)
                                <small class="text-info">(Usuario registrado)</small>
                            @else
                                <small class="text-warning">(Usuario anónimo)</small>
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
                        <div class="col-sm-3"><strong>Fecha de Creación:</strong></div>
                        <div class="col-sm-9">
                            {{ $comment->created_at->format('d/m/Y H:i:s') }}
                            <small class="text-muted">({{ $comment->created_at->diffForHumans() }})</small>
                        </div>
                    </div>

                    @if($comment->metadata)
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>Información Técnica:</strong></div>
                            <div class="col-sm-9">
                                <details>
                                    <summary class="text-muted" style="cursor: pointer;">Ver detalles técnicos</summary>
                                    <div class="mt-2">
                                        @if(isset($comment->metadata['ip_address']))
                                            <div><strong>IP:</strong> {{ $comment->metadata['ip_address'] }}</div>
                                        @endif
                                        @if(isset($comment->metadata['user_agent']))
                                            <div><strong>User Agent:</strong> <small>{{ $comment->metadata['user_agent'] }}</small></div>
                                        @endif
                                        @if(isset($comment->metadata['url']))
                                            <div><strong>URL:</strong> {{ $comment->metadata['url'] }}</div>
                                        @endif
                                        @if(isset($comment->metadata['platform']))
                                            <div><strong>Plataforma:</strong> {{ $comment->metadata['platform'] }}</div>
                                        @endif
                                    </div>
                                </details>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Respuesta del Administrador -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Respuesta del Administrador</h6>
                </div>
                <div class="card-body">
                    @if($comment->admin_response)
                        <div class="alert alert-success">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Respuesta:</strong>
                                </div>
                                <small class="text-muted">
                                    @if($comment->respondedBy)
                                        por {{ $comment->respondedBy->name }}
                                    @endif
                                    {{ $comment->responded_at ? $comment->responded_at->format('d/m/Y H:i') : '' }}
                                </small>
                            </div>
                            <div class="mt-2">{{ $comment->admin_response }}</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.comments.respond', $comment->id) }}">
                        @csrf
                        <div class="form-group">
                            <label for="admin_response">{{ $comment->admin_response ? 'Actualizar Respuesta:' : 'Responder:' }}</label>
                            <textarea class="form-control" id="admin_response" name="admin_response" rows="4" placeholder="Escribe tu respuesta aquí...">{{ old('admin_response', $comment->admin_response) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="status">Estado:</label>
                            <select class="form-control" id="status" name="status">
                                <option value="pending" {{ $comment->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="in_progress" {{ $comment->status == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                                <option value="resolved" {{ $comment->status == 'resolved' ? 'selected' : '' }}>Resuelto</option>
                                <option value="closed" {{ $comment->status == 'closed' ? 'selected' : '' }}>Cerrado</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-reply"></i> {{ $comment->admin_response ? 'Actualizar Respuesta' : 'Enviar Respuesta' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4 mb-4">
            <!-- Acciones Rápidas -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="quick-priority">Cambiar Prioridad:</label>
                        <select class="form-control" id="quick-priority" data-comment-id="{{ $comment->id }}">
                            <option value="low" {{ $comment->priority == 'low' ? 'selected' : '' }}>Baja</option>
                            <option value="medium" {{ $comment->priority == 'medium' ? 'selected' : '' }}>Media</option>
                            <option value="high" {{ $comment->priority == 'high' ? 'selected' : '' }}>Alta</option>
                            <option value="urgent" {{ $comment->priority == 'urgent' ? 'selected' : '' }}>Urgente</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="quick-status">Cambiar Estado:</label>
                        <select class="form-control" id="quick-status" data-comment-id="{{ $comment->id }}">
                            <option value="pending" {{ $comment->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="in_progress" {{ $comment->status == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                            <option value="resolved" {{ $comment->status == 'resolved' ? 'selected' : '' }}>Resuelto</option>
                            <option value="closed" {{ $comment->status == 'closed' ? 'selected' : '' }}>Cerrado</option>
                        </select>
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        @if($comment->user)
                            <a href="{{ route('usuarios.show', $comment->user->id) }}" class="btn btn-sm btn-outline-info btn-block">
                                <i class="fas fa-user"></i> Ver Perfil del Usuario
                            </a>
                        @endif

                        @if($comment->getUserEmail())
                            <a href="mailto:{{ $comment->getUserEmail() }}" class="btn btn-sm btn-outline-primary btn-block">
                                <i class="fas fa-envelope"></i> Enviar Email
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Información del Usuario -->
            @if($comment->user)
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">Información del Usuario</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            @if($comment->user->photo)
                                <img src="{{ $comment->user->photo }}" alt="Avatar" class="rounded-circle" width="60" height="60">
                            @else
                                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <span class="text-white font-weight-bold">{{ substr($comment->user->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="text-center mb-3">
                            <h6 class="font-weight-bold">{{ $comment->user->name }}</h6>
                            <p class="text-muted mb-1">{{ $comment->user->email }}</p>
                            @if($comment->user->phone)
                                <p class="text-muted mb-1">{{ $comment->user->phone }}</p>
                            @endif
                        </div>

                        <hr>

                        <div class="row text-center">
                            <div class="col">
                                <div class="h6 mb-0">{{ $comment->user->userComments()->count() }}</div>
                                <small class="text-muted">Comentarios</small>
                            </div>
                            <div class="col">
                                <div class="h6 mb-0">{{ $comment->user->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">Registro</small>
                            </div>
                        </div>
                    </div>
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
    $('#quick-priority').change(function() {
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
                    location.reload();
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
    $('#quick-status').change(function() {
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
                    location.reload();
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