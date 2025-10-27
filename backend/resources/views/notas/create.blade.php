@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Nota</h1>
    <form action="{{ route('notas.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="_idElemento">Elemento ID</label>
            <input type="number" name="_idElemento" id="_idElemento" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="_fecha">Fecha</label>
            <input type="date" name="_fecha" id="_fecha" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="_nombre">Nombre</label>
            <input type="text" name="_nombre" id="_nombre" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="_informacion">Información</label>
            <textarea name="_informacion" id="_informacion" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label for="_contenido">Contenido (JSON)</label>
            <textarea name="_contenido" id="_contenido" class="form-control" required></textarea>
        </div>
        <div class="form-group">
            <label for="_status">Estado</label>
            <input type="text" name="_status" id="_status" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection

