@extends('layouts.app')

@section('title', 'Enviar Comentario')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Enviar Comentario o Solicitud</h5>
                        <a href="{{ route('user.comments.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a mis comentarios
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('user.comments.store') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="type" class="form-label">Tipo de comentario <span class="text-danger">*</span></label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Selecciona el tipo</option>
                                <option value="help_request" {{ old('type') == 'help_request' ? 'selected' : '' }}>
                                    <i class="fas fa-life-ring"></i> Solicitud de ayuda
                                </option>
                                <option value="suggestion" {{ old('type') == 'suggestion' ? 'selected' : '' }}>
                                    Sugerencia de mejora
                                </option>
                                <option value="bug_report" {{ old('type') == 'bug_report' ? 'selected' : '' }}>
                                    Reporte de error
                                </option>
                                <option value="feedback" {{ old('type') == 'feedback' ? 'selected' : '' }}>
                                    Comentario general
                                </option>
                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>
                                    Otro
                                </option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="priority" class="form-label">Prioridad</label>
                            <select class="form-control @error('priority') is-invalid @enderror" id="priority" name="priority">
                                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Media (recomendado)</option>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Baja</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgente</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Selecciona "Alta" o "Urgente" solo para problemas críticos.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="subject" class="form-label">Asunto <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('subject') is-invalid @enderror"
                                   id="subject"
                                   name="subject"
                                   value="{{ old('subject') }}"
                                   placeholder="Escribe un título descriptivo..."
                                   maxlength="255"
                                   required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="comment" class="form-label">Comentario <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('comment') is-invalid @enderror"
                                      id="comment"
                                      name="comment"
                                      rows="6"
                                      placeholder="Describe tu comentario, solicitud o problema de forma detallada..."
                                      required>{{ old('comment') }}</textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Incluye toda la información relevante: pasos para reproducir el problema,
                                capturas de pantalla (si aplica), navegador utilizado, etc.
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Información:</strong> Tu comentario será revisado por nuestro equipo de soporte.
                            Te responderemos lo antes posible según la prioridad seleccionada.
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('user.comments.index') }}" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Enviar Comentario
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-question-circle"></i> Tipos de comentarios</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="text-info"><i class="fas fa-life-ring"></i> Solicitud de ayuda</h6>
                                <small class="text-muted">Cuando necesitas asistencia con alguna funcionalidad o tienes problemas técnicos.</small>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-warning"><i class="fas fa-lightbulb"></i> Sugerencia</h6>
                                <small class="text-muted">Ideas para mejorar la aplicación o nuevas funcionalidades que te gustaría ver.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="text-danger"><i class="fas fa-bug"></i> Reporte de error</h6>
                                <small class="text-muted">Cuando encuentras un comportamiento inesperado o algún error en la aplicación.</small>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-success"><i class="fas fa-comment"></i> Comentario general</h6>
                                <small class="text-muted">Feedback general, opiniones sobre la aplicación o cualquier otro comentario.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection