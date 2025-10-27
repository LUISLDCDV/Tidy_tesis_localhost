@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles del Elemento</h1>
    <p><strong>ID:</strong> {{ $elemento->_idElemento }}</p>
    <p><strong>Tipo de Elemento:</strong> {{ $elemento->_tipoElemento }}</p>
    <a href="{{ route('elementos.index') }}" class="btn btn-primary">Volver</a>
</div>
@endsection
