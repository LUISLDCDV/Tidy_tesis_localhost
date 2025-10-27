@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles del Evento</h1>
    <p><strong>ID:</strong> {{ $evento->_idEvento }}</p>
    <p><strong>Elemento ID:</strong> {{ $evento->_idElemento }}</p>
    <p><strong>Tipo:</strong> {{ $evento->_tipo }}</p>
    <p><strong>Fecha de Vencimiento:</strong> {{ $evento->_fecha_vencimiento }}</p>
    <p><strong>Hora de Vencimiento:</strong> {{ $evento->_hora_vencimiento }}</p>
    <p><strong>Nombre:</strong> {{ $evento->_nombre }}</p>
    <p><strong>Información:</strong> {{ $evento->_informacion }}</p>
    <a href="{{ route('eventos.index') }}" class="btn btn-primary">Volver</a>
</div>
@endsection


