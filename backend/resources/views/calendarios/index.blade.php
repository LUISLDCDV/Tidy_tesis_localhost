

@extends('layouts.app')


@section('content')
<div class="container">
    <h1>Calendarios</h1>
    <a href="{{ route('calendarios.create') }}" class="btn btn-primary">Crear Calendario</a>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>ID Elemento</th>
                <th>ID Color</th>
                <th>Información</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($calendarios as $calendario)
                <tr>
                    <td>{{ $calendario->_idCalendario }}</td>
                    <td>{{ $calendario->_nombre }}</td>
                    <td>{{ $calendario->_idElemento }}</td>
                    <td>{{ $calendario->_idColor }}</td>
                    <td>{{ $calendario->_informacion }}</td>
                    <td>
                        <a href="{{ route('calendarios.show', $calendario->_idCalendario) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('calendarios.edit', $calendario->_idCalendario) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('calendarios.destroy', $calendario->_idCalendario) }}" method="POST" style="display:inline;">
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
