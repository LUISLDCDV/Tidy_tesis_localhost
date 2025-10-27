@extends('layouts.app')

@section('title', 'Editar Perfil - Tidy')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-user-edit"></i> {{ __('Editar Perfil') }}</span>
                    <a href="{{ route('perfil.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver al Perfil
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('perfil.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">{{ __('Nombre') }}</label>
                                    <input id="name" type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           name="name"
                                           value="{{ old('name', $user->name) }}"
                                           required autofocus>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name">{{ __('Apellido') }}</label>
                                    <input id="last_name" type="text"
                                           class="form-control @error('last_name') is-invalid @enderror"
                                           name="last_name"
                                           value="{{ old('last_name', $user->last_name) }}"
                                           required>
                                    @error('last_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">{{ __('Correo Electrónico') }}</label>
                            <input id="email" type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   name="email"
                                   value="{{ old('email', $user->email) }}"
                                   required>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone">{{ __('Teléfono') }} <small class="text-muted">(opcional)</small></label>
                            <input id="phone" type="text"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   name="phone"
                                   value="{{ old('phone', $user->phone) }}"
                                   placeholder="+1234567890">
                            @error('phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        @if($user->usuarioCuenta)
                            <hr>
                            <h6 class="text-muted">{{ __('Información de la Cuenta') }}</h6>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ __('Nivel Actual') }}</label>
                                        <input type="text" class="form-control"
                                               value="Nivel {{ $user->usuarioCuenta->current_level ?? 1 }}"
                                               readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ __('Experiencia Total') }}</label>
                                        <input type="text" class="form-control"
                                               value="{{ number_format($user->usuarioCuenta->total_xp ?? 0) }} XP"
                                               readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ __('Estado Premium') }}</label>
                                        <input type="text" class="form-control"
                                               value="{{ $user->usuarioCuenta->is_premium ? 'Premium' : 'Gratuito' }}"
                                               readonly>
                                    </div>
                                </div>
                            </div>

                            @if($user->usuarioCuenta->is_premium && $user->usuarioCuenta->premium_expires_at)
                                <div class="form-group">
                                    <label>{{ __('Premium Válido Hasta') }}</label>
                                    <input type="text" class="form-control"
                                           value="{{ $user->usuarioCuenta->premium_expires_at->format('d/m/Y H:i') }}"
                                           readonly>
                                </div>
                            @endif
                        @endif

                        <hr>
                        <div class="form-group mb-0">
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('Guardar Cambios') }}
                                </button>

                                <a href="{{ route('perfil.index') }}" class="btn btn-outline-secondary">
                                    {{ __('Cancelar') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(Auth::user()->hasRole('admin'))
                <div class="card mt-4">
                    <div class="card-header bg-warning">
                        <h6 class="mb-0 text-dark">
                            <i class="fas fa-user-shield"></i> {{ __('Configuración de Administrador') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>{{ __('Gestión de Usuarios') }}</h6>
                                <a href="{{ route('usuarios.index', Auth::user()->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-users"></i> {{ __('Ver Todos los Usuarios') }}
                                </a>
                            </div>
                            <div class="col-md-6">
                                <h6>{{ __('Configuración del Sistema') }}</h6>
                                <a href="{{ route('home') }}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-tachometer-alt"></i> {{ __('Dashboard Admin') }}
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