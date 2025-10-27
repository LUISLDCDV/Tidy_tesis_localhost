

@extends('layouts.app')


@section('content')
    <div class="container">
        <h1>Eventos</h1>
        <a href="{{ route('eventos.create') }}" class="btn btn-primary">Crear Evento</a>
        <table class="table mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Fecha de Vencimiento</th>
                    <th>Hora de Vencimiento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($eventos as $evento)
                    <tr>
                        <td>{{ $evento->_idEvento }}</td>
                        <td>{{ $evento->_nombre }}</td>
                        <td>{{ $evento->_tipo }}</td>
                        <td>{{ $evento->_fecha_vencimiento }}</td>
                        <td>{{ $evento->_hora_vencimiento }}</td>
                        <td>
                            <a href="{{ route('eventos.show', $evento->_idEvento) }}" class="btn btn-info btn-sm">Ver</a>
                            <a href="{{ route('eventos.edit', $evento->_idEvento) }}" class="btn btn-warning btn-sm">Editar</a>
                            <form action="{{ route('eventos.destroy', $evento->_idEvento) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection


