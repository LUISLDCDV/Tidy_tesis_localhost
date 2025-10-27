@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Medio de Pago</h1>
    <form action="{{ route('medio_de_pagos.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="identificador">Identificador</label>
            <input type="text" name="identificador" id="identificador" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="tipo">Tipo</label>
            <input type="text" name="tipo" id="tipo" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection
