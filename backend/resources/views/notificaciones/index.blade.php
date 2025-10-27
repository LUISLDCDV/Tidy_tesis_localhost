@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Notificaciones</h1>
    <a href="{{ route('notificaciones.create') }}" class="btn btn-primary">Crear Notificación</a>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>ID Usuario</th>
                <th>ID Elemento</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notificaciones as $notificacion)
                <tr>
                    <td>{{ $notificacion->_idNotificacion }}</td>
                    <td>{{ $notificacion->_id_usuario }}</td>
                    <td>{{ $notificacion->_id_elemento }}</td>
                    <td>{{ $notificacion->_tipo }}</td>
                    <td>{{ $notificacion->_descripcion }}</td>
                    <td>
                        <a href="{{ route('notificaciones.show', $notificacion->_idNotificacion) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('notificaciones.edit', $notificacion->_idNotificacion) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('notificaciones.destroy', $notificacion->_idNotificacion) }}" method="POST" style="display:inline;">
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
