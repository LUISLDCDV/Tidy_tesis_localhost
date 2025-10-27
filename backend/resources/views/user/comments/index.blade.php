@extends('layouts.app')

@section('title', 'Mis Comentarios')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Mis Comentarios y Solicitudes</h2>
                <a href="{{ route('user.comments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Comentario
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

            @if($comments->count() > 0)
                <div class="row">
                    @foreach($comments as $comment)
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $comment->created_at->format('d/m/Y H:i') }}</small>
                                    <div>
                                        <span class="badge badge-{{ $comment->status_color }}">{{ $comment->status_text }}</span>
                                        <span class="badge badge-{{ $comment->priority_color }}">{{ $comment->priority_text }}</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title">{{ $comment->subject }}</h6>
                                    <p class="card-text">{{ Str::limit($comment->comment, 100) }}</p>
                                    <div class="mb-2">
                                        <span class="badge badge-secondary">{{ $comment->type_text }}</span>
                                        @if($comment->isOverdue())
                                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Vencido</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-footer">
                                    @if($comment->admin_response)
                                        <div class="alert alert-success mb-2 p-2">
                                            <small><strong>Respuesta:</strong> {{ Str::limit($comment->admin_response, 80) }}</small>
                                        </div>
                                    @else
                                        <small class="text-muted">Sin respuesta aún</small>
                                    @endif
                                    <div class="text-right">
                                        <a href="{{ route('user.comments.show', $comment->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> Ver Detalles
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-center">
                    {{ $comments->links() }}
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-comments fa-3x text-gray-300 mb-3"></i>
                        <h5 class="text-gray-500">No tienes comentarios aún</h5>
                        <p class="text-gray-400 mb-4">¿Necesitas ayuda o tienes alguna sugerencia? ¡Estamos aquí para ayudarte!</p>
                        <a href="{{ route('user.comments.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Enviar tu primer comentario
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection