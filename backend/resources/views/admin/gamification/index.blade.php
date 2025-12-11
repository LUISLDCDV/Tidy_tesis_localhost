@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0"><i class="fas fa-cog"></i> Configuración de Experiencia (XP)</h1>
            <p class="text-muted">Configura cuánta experiencia ganan los usuarios por cada acción</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.gamification.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- XP por Crear Elementos -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus-circle"></i> XP por Crear Elementos</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($configs['crear']))
                            @foreach($configs['crear'] as $config)
                                <div class="mb-3">
                                    <label for="config_{{ $config->id }}" class="form-label">
                                        {{ $config->description }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">✨</span>
                                        <input
                                            type="number"
                                            class="form-control"
                                            id="config_{{ $config->id }}"
                                            name="configs[{{ $loop->index }}][value]"
                                            value="{{ $config->value }}"
                                            min="0"
                                            max="1000"
                                            required
                                        >
                                        <span class="input-group-text">XP</span>
                                        <input type="hidden" name="configs[{{ $loop->index }}][id]" value="{{ $config->id }}">
                                    </div>
                                    <small class="text-muted">Clave: <code>{{ $config->key }}</code></small>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- XP por Completar Elementos -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-check-circle"></i> XP por Completar Elementos</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($configs['completar']))
                            @php
                                $offset = isset($configs['crear']) ? count($configs['crear']) : 0;
                            @endphp
                            @foreach($configs['completar'] as $index => $config)
                                <div class="mb-3">
                                    <label for="config_{{ $config->id }}" class="form-label">
                                        {{ $config->description }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-trophy"></i></span>
                                        <input
                                            type="number"
                                            class="form-control"
                                            id="config_{{ $config->id }}"
                                            name="configs[{{ $offset + $index }}][value]"
                                            value="{{ $config->value }}"
                                            min="0"
                                            max="1000"
                                            required
                                        >
                                        <span class="input-group-text">XP</span>
                                        <input type="hidden" name="configs[{{ $offset + $index }}][id]" value="{{ $config->id }}">
                                    </div>
                                    <small class="text-muted">Clave: <code>{{ $config->key }}</code></small>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><i class="fas fa-lightbulb"></i> Recomendaciones</h6>
                                <small class="text-muted">
                                    • Completar tareas debe dar más XP que crearlas<br>
                                    • Los objetivos son más valiosos que las notas<br>
                                    • Metas intermedias entre objetivos y tareas simples
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Guardar Configuración
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .card {
        border: none;
        border-radius: 8px;
    }
    .card-header {
        border-radius: 8px 8px 0 0 !important;
    }
    .input-group-text {
        background-color: #f8f9fa;
    }
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
</style>
@endsection
