@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles del Objetivo</h1>
    <p><strong>ID:</strong> {{ $objetivo->_idObjetivo }}</p>
    <p><strong>Elemento ID:</strong> {{ $objetivo->_idElemento }}</p>
    <p><strong>Tipo:</strong> {{ $objetivo->_tipo }}</p>
    <p><strong>Fecha Creación:</strong> {{ $objetivo->_fechaCreacion }}</p>
    <p><strong>Fecha Vencimiento:</strong> {{ $objetivo->_fechaVencimiento }}</p>
    <p><strong>Nombre:</strong> {{ $objetivo->_nombre }}</p>
    <p><strong>Información:</strong> {{ $objetivo->_informacion }}</p>

    <h3>Metas</h3>
    <a href="{{ route('metas.create') }}" class="btn btn-primary">Agregar Meta</a>
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
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('objetivos.index') }}" class="btn btn-primary">Volver</a>
</div>
@endsection
