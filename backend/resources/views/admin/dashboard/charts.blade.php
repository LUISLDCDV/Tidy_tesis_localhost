@extends('layouts.app')

@section('title', 'Estadísticas y Gráficos - Admin Tidy')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-chart-bar"></i> Estadísticas y Gráficos</h1>
                <a href="{{ route('home') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>

            @if(isset($error))
                <div class="alert alert-danger" role="alert">
                    <strong>Error:</strong> {{ $error }}
                </div>
            @endif

            @if($chartsData)
                <!-- Filtros de período -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3" style="background: linear-gradient(135deg,rgb(16, 116, 25) 0%,rgb(45, 158, 149) 100%);">
                        <h6 class="m-0 font-weight-bold text-white">
                            <i class="fas fa-calendar-alt"></i> Período de Análisis
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.dashboard.charts') }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="period">Seleccionar período:</label>
                                        <select class="form-control" id="period" name="period" onchange="this.form.submit()">
                                            <option value="7d" {{ request('period') === '7d' ? 'selected' : '' }}>Últimos 7 días</option>
                                            <option value="30d" {{ request('period') === '30d' || !request('period') ? 'selected' : '' }}>Últimos 30 días</option>
                                            <option value="90d" {{ request('period') === '90d' ? 'selected' : '' }}>Últimos 90 días</option>
                                            <option value="1y" {{ request('period') === '1y' ? 'selected' : '' }}>Último año</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <!-- Registros de usuarios -->
                    <div class="col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3" style="background: linear-gradient(135deg,rgb(16, 116, 25) 0%,rgb(45, 158, 149) 100%);">
                                <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-chart-line"></i> Registros de Usuarios</h6>
                            </div>
                            <div class="card-body">
                                @if(isset($chartsData['user_registrations']) && count($chartsData['user_registrations']) > 0)
                                    <div style="height: 300px;">
                                        <canvas id="userRegistrationsChart"></canvas>
                                    </div>
                                    <div class="mt-3">
                                        <h6 class="text-primary">Datos del período:</h6>
                                        <ul class="list-unstyled">
                                            <li><strong>Total registros:</strong> {{ collect($chartsData['user_registrations'])->sum('count') }}</li>
                                            <li><strong>Promedio diario:</strong> {{ round(collect($chartsData['user_registrations'])->avg('count'), 1) }}</li>
                                            <li><strong>Día con más registros:</strong> {{ collect($chartsData['user_registrations'])->max('count') }}</li>
                                        </ul>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
                                        <p class="text-muted">No hay datos de registros para este período</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actividad de usuarios -->
                    <div class="col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3" style="background: linear-gradient(135deg,rgb(16, 116, 25) 0%,rgb(45, 158, 149) 100%);">
                                <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-fire"></i> Actividad de Usuarios</h6>
                            </div>
                            <div class="card-body">
                                @if(isset($chartsData['user_activity']) && count($chartsData['user_activity']) > 0)
                                    <div style="height: 300px;">
                                        <canvas id="userActivityChart"></canvas>
                                    </div>
                                    <div class="mt-3">
                                        <h6 class="text-success">Datos del período:</h6>
                                        <ul class="list-unstyled">
                                            <li><strong>Total accesos:</strong> {{ collect($chartsData['user_activity'])->sum('count') }}</li>
                                            <li><strong>Promedio diario:</strong> {{ round(collect($chartsData['user_activity'])->avg('count'), 1) }}</li>
                                            <li><strong>Día más activo:</strong> {{ collect($chartsData['user_activity'])->max('count') }} usuarios</li>
                                        </ul>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                                        <p class="text-muted">No hay datos de actividad para este período</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Creación de elementos -->
                    <div class="col-lg-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3" style="background: linear-gradient(135deg,rgb(16, 116, 25) 0%,rgb(45, 158, 149) 100%);">
                                <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-clipboard-list"></i> Creación de Elementos</h6>
                            </div>
                            <div class="card-body">
                                @if(isset($chartsData['element_creation']) && count($chartsData['element_creation']) > 0)
                                    <div style="height: 300px;">
                                        <canvas id="elementCreationChart"></canvas>
                                    </div>
                                    <div class="mt-3">
                                        <h6 class="text-warning">Elementos por tipo:</h6>
                                        <div class="row">
                                            @php
                                                $elementsByType = collect($chartsData['element_creation'])->groupBy('tipo')->map(function($items) {
                                                    return $items->sum('count');
                                                });
                                            @endphp
                                            @foreach($elementsByType as $type => $count)
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <strong>{{ ucfirst($type) }}</strong><br>
                                                        <span class="badge badge-warning">{{ $count }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-boxes fa-3x text-gray-300 mb-3"></i>
                                        <p class="text-muted">No hay datos de elementos para este período</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Ingresos -->
                    <div class="col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3" style="background: linear-gradient(135deg,rgb(16, 116, 25) 0%,rgb(45, 158, 149) 100%);">
                                <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-dollar-sign"></i> Ingresos</h6>
                            </div>
                            <div class="card-body">
                                @if(isset($chartsData['revenue']) && count($chartsData['revenue']) > 0)
                                    <div style="height: 300px;">
                                        <canvas id="revenueChart"></canvas>
                                    </div>
                                    <div class="mt-3">
                                        <h6 class="text-info">Resumen financiero:</h6>
                                        <ul class="list-unstyled">
                                            <li><strong>Total ingresos:</strong> ${{ number_format(collect($chartsData['revenue'])->sum('revenue'), 2) }}</li>
                                            <li><strong>Promedio diario:</strong> ${{ number_format(collect($chartsData['revenue'])->avg('revenue'), 2) }}</li>
                                        </ul>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-dollar-sign fa-3x text-gray-300 mb-3"></i>
                                        <p class="text-muted">No hay datos de ingresos para este período</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            @else
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-heading"><i class="fas fa-chart-bar"></i> Estadísticas no disponibles</h4>
                    <p>No se pudieron cargar los datos de estadísticas.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($chartsData)
        // Registros de usuarios
        @if(isset($chartsData['user_registrations']) && count($chartsData['user_registrations']) > 0)
        const userRegData = @json($chartsData['user_registrations']);
        new Chart(document.getElementById('userRegistrationsChart'), {
            type: 'line',
            data: {
                labels: userRegData.map(item => item.date),
                datasets: [{
                    label: 'Registros',
                    data: userRegData.map(item => item.count),
                    borderColor: 'rgb(16, 116, 25)',
                    backgroundColor: 'rgba(16, 116, 25, 0.1)',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        @endif

        // Actividad de usuarios
        @if(isset($chartsData['user_activity']) && count($chartsData['user_activity']) > 0)
        const userActivityData = @json($chartsData['user_activity']);
        new Chart(document.getElementById('userActivityChart'), {
            type: 'bar',
            data: {
                labels: userActivityData.map(item => item.date),
                datasets: [{
                    label: 'Usuarios Activos',
                    data: userActivityData.map(item => item.count),
                    backgroundColor: 'rgba(45, 158, 149, 0.8)',
                    borderColor: 'rgb(45, 158, 149)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        @endif

        // Creación de elementos
        @if(isset($chartsData['element_creation']) && count($chartsData['element_creation']) > 0)
        const elementData = @json($chartsData['element_creation']);
        const elementsByDate = {};
        const types = [...new Set(elementData.map(item => item.tipo))];

        elementData.forEach(item => {
            if (!elementsByDate[item.date]) {
                elementsByDate[item.date] = {};
            }
            elementsByDate[item.date][item.tipo] = item.count;
        });

        const dates = Object.keys(elementsByDate).sort();
        const colors = [
            'rgb(16, 116, 25)',
            'rgb(45, 158, 149)',
            'rgb(76, 175, 80)',
            'rgb(33, 150, 243)',
            'rgb(156, 39, 176)'
        ];

        new Chart(document.getElementById('elementCreationChart'), {
            type: 'line',
            data: {
                labels: dates,
                datasets: types.map((type, index) => ({
                    label: type.charAt(0).toUpperCase() + type.slice(1),
                    data: dates.map(date => elementsByDate[date][type] || 0),
                    borderColor: colors[index % colors.length],
                    backgroundColor: colors[index % colors.length].replace('rgb', 'rgba').replace(')', ', 0.1)'),
                    tension: 0.3
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        @endif

        // Ingresos
        @if(isset($chartsData['revenue']) && count($chartsData['revenue']) > 0)
        const revenueData = @json($chartsData['revenue']);
        new Chart(document.getElementById('revenueChart'), {
            type: 'doughnut',
            data: {
                labels: revenueData.map(item => item.date),
                datasets: [{
                    data: revenueData.map(item => item.revenue),
                    backgroundColor: [
                        'rgba(16, 116, 25, 0.8)',
                        'rgba(45, 158, 149, 0.8)',
                        'rgba(76, 175, 80, 0.8)',
                        'rgba(33, 150, 243, 0.8)',
                        'rgba(156, 39, 176, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });
        @endif
    @endif
});
</script>
@endsection