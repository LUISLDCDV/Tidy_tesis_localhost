@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Medio de Pago</h1>
    <form action="{{ route('medio_de_pagos.update', $medioDePago->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="form-control" value="{{ $medioDePago->nombre }}" required>
        </div>
        <div class="form-group">
            <label for="identificador">Identificador</label>
            <input type="text" name="identificador" id="identificador" class="form-control" value="{{ $medioDePago->identificador }}" required>
        </div>
        <div class="form-group">
            <label for="tipo">Tipo</label>
            <input type="text" name="tipo" id="tipo" class="form-control" value="{{ $medioDePago->tipo }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
