@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-bell"></i> Gestión de Alarmas
                    </h5>
                    <a href="{{ route('alarmas.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nueva Alarma
                    </a>
                </div>

                <div class="card-body">
                    <p class="text-muted mb-4">
                        Administra las alarmas del sistema Tidy. Programa recordatorios y notificaciones para tus tareas importantes.
                    </p>

                    @if($alarmas->isEmpty())
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i>
                            No hay alarmas registradas. Crea tu primera alarma haciendo clic en "Nueva Alarma".
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre de la Alarma</th>
                                        <th>Fecha Programada</th>
                                        <th>Hora</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($alarmas as $alarma)
                                        <tr>
                                            <td><strong>#{{ $alarma->_idAlarma }}</strong></td>
                                            <td>
                                                <i class="fas fa-clock text-primary"></i>
                                                {{ $alarma->_nombre }}
                                            </td>
                                            <td>
                                                <i class="fas fa-calendar-day"></i>
                                                {{ \Carbon\Carbon::parse($alarma->_fecha)->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                <i class="fas fa-clock"></i>
                                                {{ \Carbon\Carbon::parse($alarma->_hora)->format('H:i') }}
                                            </td>
                                            <td>
                                                @if($alarma->_status == 'activa' || $alarma->_status == 1)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle"></i> Activa
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-times-circle"></i> Inactiva
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('alarmas.show', $alarma->_idAlarma) }}"
                                                       class="btn btn-info"
                                                       title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('alarmas.edit', $alarma->_idAlarma) }}"
                                                       class="btn btn-warning"
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('alarmas.destroy', $alarma->_idAlarma) }}"
                                                          method="POST"
                                                          class="d-inline"
                                                          onsubmit="return confirm('¿Estás seguro de eliminar esta alarma?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="btn btn-danger"
                                                                title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
