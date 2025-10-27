@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Puntajes</h1>
    <a href="{{ route('puntajes.create') }}" class="btn btn-primary">Crear Puntaje</a>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cantidad</th>
                <th>Rango</th>
                <th>ID Usuario</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($puntajes as $puntaje)
                <tr>
                    <td>{{ $puntaje->_idPuntaje }}</td>
                    <td>{{ $puntaje->_cantidad }}</td>
                    <td>{{ $puntaje->_rango }}</td>
                    <td>{{ $puntaje->_id_usuario }}</td>
                    <td>
                        <a href="{{ route('puntajes.show', $puntaje->_idPuntaje) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('puntajes.edit', $puntaje->_idPuntaje) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('puntajes.destroy', $puntaje->_idPuntaje) }}" method="POST" style="display:inline;">
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
