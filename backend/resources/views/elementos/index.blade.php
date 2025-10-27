

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Elementos</h1>
    <a href="{{ route('elementos.create') }}" class="btn btn-primary">Crear Elemento</a>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tipo de Elemento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($elementos as $elemento)
                <tr>
                    <td>{{ $elemento->_idElemento }}</td>
                    <td>{{ $elemento->_tipoElemento }}</td>
                    <td>
                        <a href="{{ route('elementos.show', $elemento->_idElemento) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('elementos.edit', $elemento->_idElemento) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('elementos.destroy', $elemento->_idElemento) }}" method="POST" style="display:inline;">
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
