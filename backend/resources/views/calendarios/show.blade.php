@extends('layouts.app')


@section('content')
<div class="container">
    <h1>Detalles del Calendario</h1>
    <p><strong>ID:</strong> {{ $calendario->_idCalendario }}</p>
    <p><strong>Elemento ID:</strong> {{ $calendario->_idElemento }}</p>
    <p><strong>Nombre:</strong> {{ $calendario->_nombre }}</p>
    <p><strong>ID Color:</strong> {{ $calendario->_idColor }}</p>
    <p><strong>Información:</strong> {{ $calendario->_informacion }}</p>
    <a href="{{ route('calendarios.index') }}" class="btn btn-primary">Volver</a>
</div>
@endsection
