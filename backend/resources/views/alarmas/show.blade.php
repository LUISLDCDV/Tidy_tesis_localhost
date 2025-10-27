@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles de Alarma</h1>
    <p><strong>ID:</strong> {{ $alarma->_idAlarma }}</p>
    <p><strong>Elemento ID:</strong> {{ $alarma->_idElemento }}</p>
    <p><strong>Fecha:</strong> {{ $alarma->_fecha }}</p>
    <p><strong>Hora:</strong> {{ $alarma->_hora }}</p>
    <p><strong>Fecha de Vencimiento:</strong> {{ $alarma->_fecha_vencimiento }}</p>
    <p><strong>Hora de Vencimiento:</strong> {{ $alarma->_hora_vencimiento }}</p>
    <p><strong>Nombre:</strong> {{ $alarma->_nombre }}</p>
    <p><strong>Información:</strong> {{ $alarma->_informacion }}</p>
    <p><strong>Configuración:</strong> {{ $alarma->_configuracion }}</p>
    <a href="{{ route('alarmas.index') }}" class="btn btn-primary">Volver</a>
</div>
@endsection

