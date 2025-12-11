@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt"></i> Gestión de Calendarios
                    </h5>
                    <a href="{{ route('calendarios.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nuevo Calendario
                    </a>
                </div>

                <div class="card-body">
                    <p class="text-muted mb-4">
                        Administra los calendarios del sistema Tidy. Aquí puedes crear, editar y eliminar calendarios.
                    </p>

                    @if($calendarios->isEmpty())
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i>
                            No hay calendarios registrados. Crea tu primer calendario haciendo clic en "Nuevo Calendario".
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre del Calendario</th>
                                        <th>Elemento</th>
                                        <th>Color</th>
                                        <th>Información</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($calendarios as $calendario)
                                        <tr>
                                            <td><strong>#{{ $calendario->_idCalendario }}</strong></td>
                                            <td>
                                                <i class="fas fa-calendar text-primary"></i>
                                                {{ $calendario->_nombre }}
                                            </td>
                                            <td>{{ $calendario->_idElemento ?? 'N/A' }}</td>
                                            <td>
                                                @if($calendario->_idColor)
                                                    <span class="badge" style="background-color: {{ $calendario->_idColor }}; color: white;">
                                                        {{ $calendario->_idColor }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Sin color</span>
                                                @endif
                                            </td>
                                            <td>{{ Str::limit($calendario->_informacion ?? 'Sin información', 50) }}</td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('calendarios.show', $calendario->_idCalendario) }}"
                                                       class="btn btn-info"
                                                       title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('calendarios.edit', $calendario->_idCalendario) }}"
                                                       class="btn btn-warning"
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('calendarios.destroy', $calendario->_idCalendario) }}"
                                                          method="POST"
                                                          class="d-inline"
                                                          onsubmit="return confirm('¿Estás seguro de eliminar este calendario?');">
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
