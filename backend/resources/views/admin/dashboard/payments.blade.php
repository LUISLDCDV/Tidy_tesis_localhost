@extends('layouts.app')

@section('title', 'Historial de Pagos - Admin Tidy')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">💳 Historial de Pagos</h1>
                <a href="{{ route('home') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>

            @if(isset($error))
                <div class="alert alert-danger" role="alert">
                    <strong>Error:</strong> {{ $error }}
                </div>
            @endif

            @if($stats)
                <!-- Estadísticas de pagos -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Pagos Aprobados
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $stats['total_payments'] }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Ingresos Totales
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            ARS ${{ number_format($stats['total_amount'], 2) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Pagos Pendientes
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $stats['pending_payments'] }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Ingresos del Mes
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            ARS ${{ number_format($stats['monthly_revenue'], 2) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Filtros de búsqueda -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold" style="color: #1976D2; font-family: 'Google Sans Text', sans-serif;">🔍 Filtros</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.dashboard.payments') }}">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search">Buscar Usuario:</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                           value="{{ request('search') }}" placeholder="Nombre o email del usuario...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Estado:</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Todos</option>
                                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Aprobado</option>
                                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rechazado</option>
                                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <a href="{{ route('admin.dashboard.payments') }}" class="btn btn-secondary btn-block">
                                        <i class="fas fa-redo"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de pagos -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold" style="color: #1976D2; font-family: 'Google Sans Text', sans-serif;">
                        📋 Historial de Pagos ({{ $payments->total() ?? 0 }} total)
                    </h6>
                </div>
                <div class="card-body">
                    @if($payments && count($payments) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Tipo</th>
                                        <th>Método</th>
                                        <th>Plan</th>
                                        <th>Fecha de Pago</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>{{ $payment->id }}</td>
                                            <td>
                                                @if($payment->usuario)
                                                    <strong>{{ $payment->usuario->name }} {{ $payment->usuario->last_name }}</strong>
                                                    <br><small class="text-muted">{{ $payment->usuario->email }}</small>
                                                @else
                                                    <span class="text-muted">Usuario no encontrado</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $payment->formatted_amount }}</strong>
                                            </td>
                                            <td>
                                                @if($payment->status === 'approved')
                                                    <span class="badge bg-success text-white rounded-pill">{{ $payment->status_label }}</span>
                                                @elseif($payment->status === 'pending')
                                                    <span class="badge bg-warning text-dark rounded-pill">{{ $payment->status_label }}</span>
                                                @elseif($payment->status === 'rejected')
                                                    <span class="badge bg-danger text-white rounded-pill">{{ $payment->status_label }}</span>
                                                @else
                                                    <span class="badge bg-secondary text-white rounded-pill">{{ $payment->status_label }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payment->payment_type === 'subscription')
                                                    <span class="badge bg-primary text-white">Suscripción</span>
                                                @else
                                                    <span class="badge bg-info text-white">Único</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payment->payment_method)
                                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payment->plan_type)
                                                    {{ $payment->plan_type_label }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payment->paid_at)
                                                    <small>{{ \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y H:i') }}</small>
                                                @else
                                                    <span class="text-muted">Pendiente</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="showPaymentDetails({{ $payment->id }}, {{ json_encode($payment) }})">
                                                    <i class="fas fa-eye"></i> Ver
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $payments->links() }}
                        </div>

                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-receipt fa-3x text-gray-300 mb-3"></i>
                            <h5>No se encontraron pagos</h5>
                            <p class="text-muted">No hay pagos registrados con los filtros seleccionados.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal para ver detalles del pago -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color: #2c3e50; font-family: 'Google Sans Text', sans-serif;">
                    💳 Detalles del Pago
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="paymentDetailsContent">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicialización
    console.log('Historial de pagos cargado');
});

window.showPaymentDetails = function(paymentId, payment) {
    try {
        const content = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="font-weight-bold mb-3">Información del Pago</h6>
                    <p><strong>ID del Pago:</strong> ${payment.payment_id}</p>
                    <p><strong>ID de Colección:</strong> ${payment.collection_id || 'N/A'}</p>
                    <p><strong>ID de Suscripción:</strong> ${payment.subscription_id || 'N/A'}</p>
                    <p><strong>Estado:</strong> <span class="badge bg-${payment.status === 'approved' ? 'success' : payment.status === 'pending' ? 'warning' : 'danger'}">${payment.status_label}</span></p>
                    <p><strong>Tipo de Pago:</strong> ${payment.payment_type === 'subscription' ? 'Suscripción' : 'Único'}</p>
                    <p><strong>Método de Pago:</strong> ${payment.payment_method || 'N/A'}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-bold mb-3">Información Adicional</h6>
                    <p><strong>Monto:</strong> ${payment.formatted_amount}</p>
                    <p><strong>Moneda:</strong> ${payment.currency}</p>
                    <p><strong>Plan:</strong> ${payment.plan_type_label || 'N/A'}</p>
                    <p><strong>Descripción:</strong> ${payment.description || 'N/A'}</p>
                    <p><strong>Fecha de Pago:</strong> ${payment.paid_at ? new Date(payment.paid_at).toLocaleString('es-AR') : 'Pendiente'}</p>
                    <p><strong>Creado:</strong> ${new Date(payment.created_at).toLocaleString('es-AR')}</p>
                </div>
            </div>
            ${payment.metadata ? `
                <hr>
                <h6 class="font-weight-bold mb-3">Metadata de MercadoPago</h6>
                <pre class="bg-light p-3" style="max-height: 200px; overflow-y: auto;">${JSON.stringify(JSON.parse(payment.metadata), null, 2)}</pre>
            ` : ''}
        `;

        document.getElementById('paymentDetailsContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('paymentDetailsModal')).show();
    } catch (e) {
        alert('Error mostrando detalles del pago: ' + e.message);
    }
};
</script>

<style>
/* Cards de estadísticas */
.border-left-success {
    border-left: 4px solid #1cc88a !important;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.border-left-primary {
    border-left: 4px solid #4e73df !important;
}

.text-xs {
    font-size: 0.7rem;
    font-family: 'Google Sans Text', sans-serif;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

/* Mejorar la apariencia de los modales */
.modal-content {
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px 8px 0 0;
}

.modal-header .modal-title {
    color: white !important;
    font-weight: 500;
}

.modal-header .btn-close {
    color: white;
    opacity: 0.8;
    filter: brightness(0) invert(1);
}

.modal-header .btn-close:hover {
    opacity: 1;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
}

/* Badges más modernos */
.badge {
    font-size: 0.75rem;
    padding: 0.5em 0.8em;
    font-weight: 500;
    font-family: 'Google Sans Text', sans-serif;
}

.badge.bg-success {
    background: linear-gradient(135deg, #1cc88a, #17a673) !important;
}

.badge.bg-warning {
    background: linear-gradient(135deg, #f6c23e, #dda20a) !important;
}

.badge.bg-danger {
    background: linear-gradient(135deg, #e74a3b, #be2617) !important;
}

.badge.bg-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268) !important;
}

.badge.bg-primary {
    background: linear-gradient(135deg, #4e73df, #2e59d9) !important;
}

.badge.bg-info {
    background: linear-gradient(135deg, #36b9cc, #2c9faf) !important;
}

.badge.text-white {
    color: #fff !important;
}

.badge.text-dark {
    color: #212529 !important;
}

.badge.rounded-pill {
    border-radius: 50rem !important;
}
</style>
@endsection
