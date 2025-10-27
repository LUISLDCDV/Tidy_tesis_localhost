@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Evento</h1>
    <form action="{{ route('eventos.store') }}" method="POST">
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
            <label for="_fecha_vencimiento">Fecha de Vencimiento</label>
            <input type="date" name="_fecha_vencimiento" id="_fecha_vencimiento" class="form-control">
        </div>
        <div class="form-group">
            <label for="_hora_vencimiento">Hora de Vencimiento</label>
            <input type="time" name="_hora_vencimiento" id="_hora_vencimiento" class="form-control">
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


