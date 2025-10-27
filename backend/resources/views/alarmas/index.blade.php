@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Alarmas</h1>
    <a href="{{ route('alarmas.create') }}" class="btn btn-primary">Crear Alarma</a>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alarmas as $alarma)
                <tr>
                    <td>{{ $alarma->_idAlarma }}</td>
                    <td>{{ $alarma->_nombre }}</td>
                    <td>{{ $alarma->_fecha }}</td>
                    <td>{{ $alarma->_hora }}</td>
                    <td>{{ $alarma->_status }}</td>
                    <td>
                        <a href="{{ route('alarmas.show', $alarma->_idAlarma) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('alarmas.edit', $alarma->_idAlarma) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('alarmas.destroy', $alarma->_idAlarma) }}" method="POST" style="display:inline;">
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
