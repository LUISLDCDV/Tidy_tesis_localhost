

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Notas</h1>
    <a href="{{ route('notas.create') }}" class="btn btn-primary">Crear Nota</a>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notas as $nota)
                <tr>
                    <td>{{ $nota->_idNota }}</td>
                    <td>{{ $nota->_nombre }}</td>
                    <td>{{ $nota->_fecha }}</td>
                    <td>{{ $nota->_status }}</td>
                    <td>
                        <a href="{{ route('notas.show', $nota->_idNota) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('notas.edit', $nota->_idNota) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('notas.destroy', $nota->_idNota) }}" method="POST" style="display:inline;">
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

