

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Objetivos</h1>
    <a href="{{ route('objetivos.create') }}" class="btn btn-primary">Crear Objetivo</a>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Elemento ID</th>
                <th>Tipo</th>
                <th>Fecha Creación</th>
                <th>Fecha Vencimiento</th>
                <th>Nombre</th>
                <th>Información</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($objetivos as $objetivo)
                <tr>
                    <td>{{ $objetivo->_idObjetivo }}</td>
                    <td>{{ $objetivo->_idElemento }}</td>
                    <td>{{ $objetivo->_tipo }}</td>
                    <td>{{ $objetivo->_fechaCreacion }}</td>
                    <td>{{ $objetivo->_fechaVencimiento }}</td>
                    <td>{{ $objetivo->_nombre }}</td>
                    <td>{{ $objetivo->_informacion }}</td>
                    <td>
                        <a href="{{ route('objetivos.show', $objetivo->_idObjetivo) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('objetivos.edit', $objetivo->_idObjetivo) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('objetivos.destroy', $objetivo->_idObjetivo) }}" method="POST" style="display:inline;">
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
