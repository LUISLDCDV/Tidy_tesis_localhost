@extends('layouts.app')

@section('title', 'Rankings - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">🏅 Rankings de Usuarios</h1>
                <a href="{{ route('home') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-trophy"></i> Top por Experiencia</h6>
                        </div>
                        <div class="card-body">
                            @php
                                $topUsers = \App\Models\User::with('usuarioCuenta')
                                    ->join('cuentas', 'users.id', '=', 'cuentas.user_id')
                                    ->orderBy('cuentas.total_xp', 'desc')
                                    ->limit(10)
                                    ->select('users.*', 'cuentas.total_xp', 'cuentas.current_level')
                                    ->get();
                            @endphp

                            @if($topUsers->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($topUsers as $index => $user)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>#{{ $index + 1 }}</strong>
                                                {{ $user->name }} {{ $user->last_name }}
                                                <br>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                            <div class="text-right">
                                                <span class="badge badge-primary">Nivel {{ $user->current_level ?? 1 }}</span>
                                                <br>
                                                <small>{{ number_format($user->total_xp ?? 0) }} XP</small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-muted">
                                    <p>No hay datos de experiencia disponibles</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-clipboard"></i> Top por Elementos</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">🚧 En Desarrollo</h6>
                                <p class="mb-0">Ranking por cantidad de elementos creados próximamente.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-chart-bar"></i> Estadísticas Generales</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary">{{ \App\Models\User::count() }}</h4>
                                <p class="text-muted">Total Usuarios</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                @php
                                    $premiumCount = 0;
                                    try {
                                        $premiumCount = \App\Models\UsuarioCuenta::where('is_premium', true)->count();
                                    } catch (\Exception $e) {
                                        // Si falla, mantener 0
                                    }
                                @endphp
                                <h4 class="text-success">{{ $premiumCount }}</h4>
                                <p class="text-muted">Usuarios Premium</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                @php
                                    $avgXp = 0;
                                    try {
                                        $avgXp = \App\Models\UsuarioCuenta::avg('total_xp');
                                        $avgXp = $avgXp ? number_format($avgXp) : 0;
                                    } catch (\Exception $e) {
                                        $avgXp = 0;
                                    }
                                @endphp
                                <h4 class="text-warning">{{ $avgXp }}</h4>
                                <p class="text-muted">XP Promedio</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                @php
                                    $maxLevel = 1;
                                    try {
                                        $maxLevel = \App\Models\UsuarioCuenta::max('current_level') ?? 1;
                                    } catch (\Exception $e) {
                                        $maxLevel = 1;
                                    }
                                @endphp
                                <h4 class="text-info">{{ $maxLevel }}</h4>
                                <p class="text-muted">Nivel Máximo</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection