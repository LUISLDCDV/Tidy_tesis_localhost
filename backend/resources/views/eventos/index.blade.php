

@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-check"></i> Gestión de Eventos
                        </h5>
                        <a href="{{ route('eventos.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nuevo Evento
                        </a>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i> ID</th>
                                    <th><i class="fas fa-heading"></i> Nombre</th>
                                    <th><i class="fas fa-tag"></i> Tipo</th>
                                    <th><i class="fas fa-calendar-day"></i> Fecha de Vencimiento</th>
                                    <th><i class="fas fa-clock"></i> Hora de Vencimiento</th>
                                    <th><i class="fas fa-cog"></i> Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($eventos as $evento)
                                    <tr>
                                        <td>{{ $evento->_idEvento }}</td>
                                        <td>
                                            <i class="fas fa-calendar-check text-primary"></i>
                                            {{ $evento->_nombre }}
                                        </td>
                                        <td>{{ $evento->_tipo }}</td>
                                        <td>{{ $evento->_fecha_vencimiento }}</td>
                                        <td>{{ $evento->_hora_vencimiento }}</td>
                                        <td>
                                            <a href="{{ route('eventos.show', $evento->_idEvento) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                            <a href="{{ route('eventos.edit', $evento->_idEvento) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <form action="{{ route('eventos.destroy', $evento->_idEvento) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este evento?')">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


