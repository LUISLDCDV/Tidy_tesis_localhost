@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles del Medio de Pago</h1>
    <p><strong>Nombre:</strong> {{ $medioDePago->nombre }}</p>
    <p><strong>Identificador:</strong> {{ $medioDePago->identificador }}</p>
    <p><strong>Tipo:</strong> {{ $medioDePago->tipo }}</p>
    <a href="{{ route('medio_de_pagos.index') }}" class="btn btn-primary">Volver</a>
</div>
@endsection
