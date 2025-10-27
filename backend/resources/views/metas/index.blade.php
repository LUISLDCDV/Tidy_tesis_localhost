

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Metas</h1>
    <a href="{{ route('metas.create') }}" class="btn btn-primary">Crear Meta</a>
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
            @foreach($metas as $meta)
                <tr>
                    <td>{{ $meta->_idMeta }}</td>
                    <td>{{ $meta->_idElemento }}</td>
                    <td>{{ $meta->_tipo }}</td>
                    <td>{{ $meta->_fechaCreacion }}</td>
                    <td>{{ $meta->_fechaVencimiento }}</td>
                    <td>{{ $meta->_nombre }}</td>
                    <td>{{ $meta->_informacion }}</td>
                    <td>
                        <a href="{{ route('metas.show', $meta->_idMeta) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('metas.edit', $meta->_idMeta) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('metas.destroy', $meta->_idMeta) }}" method="POST" style="display:inline;">
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
