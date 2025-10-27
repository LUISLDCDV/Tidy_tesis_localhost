@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles de la Meta</h1>
    <p><strong>ID:</strong> {{ $meta->_idMeta }}</p>
    <p><strong>Elemento ID:</strong> {{ $meta->_idElemento }}</p>
    <p><strong>Tipo:</strong> {{ $meta->_tipo }}</p>
    <p><strong>Fecha Creación:</strong> {{ $meta->_fechaCreacion }}</p>
    <p><strong>Fecha Vencimiento:</strong> {{ $meta->_fechaVencimiento }}</p>
    <p><strong>Nombre:</strong> {{ $meta->_nombre }}</p>
    <p><strong>Información:</strong> {{ $meta->_informacion }}</p>
    <a href="{{ route('metas.index') }}" class="btn btn-primary">Volver</a>
</div>
@endsection
