@extends('layouts.app')

@section('title', 'Configuración de Niveles - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-trophy"></i> Configuración de Niveles</h1>
                <a href="{{ route('home') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>

            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Sistema de Niveles y Experiencia</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">🚧 En Desarrollo</h6>
                        <p class="mb-0">Esta sección está en desarrollo. Aquí podrás configurar:</p>
                        <ul class="mt-2 mb-0">
                            <li>Requisitos de XP para cada nivel</li>
                            <li>Recompensas por nivel</li>
                            <li>Configuración de progresión</li>
                        </ul>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Niveles Actuales</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Nivel</th>
                                            <th>XP Requerida</th>
                                            <th>Usuarios</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>0</td>
                                            <td>{{ \App\Models\User::count() }}</td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>1,000</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>5,000</td>
                                            <td>0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Configuración Rápida</h6>
                            <div class="list-group">
                                <a href="#" class="list-group-item list-group-item-action disabled">
                                    <i class="fas fa-cog"></i> Configurar Niveles
                                </a>
                                <a href="#" class="list-group-item list-group-item-action disabled">
                                    <i class="fas fa-gift"></i> Configurar Recompensas
                                </a>
                                <a href="#" class="list-group-item list-group-item-action disabled">
                                    <i class="fas fa-chart-line"></i> Ver Progresión
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection