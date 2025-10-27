@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Objetivo</h1>
    <form action="{{ route('objetivos.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="_idElemento">Elemento ID</label>
            <input type="number" name="_idElemento" id="_idElemento" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="_tipo">Tipo</label>
            <input type="text" name="_tipo" id="_tipo" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="_fechaCreacion">Fecha Creación</label>
            <input type="date" name="_fechaCreacion" id="_fechaCreacion" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="_fechaVencimiento">Fecha Vencimiento</label>
            <input type="date" name="_fechaVencimiento" id="_fechaVencimiento" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="_nombre">Nombre</label>
            <input type="text" name="_nombre" id="_nombre" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="_informacion">Información</label>
            <textarea name="_informacion" id="_informacion" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection
