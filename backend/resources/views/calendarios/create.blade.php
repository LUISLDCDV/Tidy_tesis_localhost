@extends('layouts.app')


@section('content')
<div class="container">
    <h1>Crear Calendario</h1>
    <form action="{{ route('calendarios.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="_idElemento">Elemento ID</label>
            <input type="number" name="_idElemento" id="_idElemento" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="_nombre">Nombre</label>
            <input type="text" name="_nombre" id="_nombre" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="_idColor">ID Color</label>
            <input type="number" name="_idColor" id="_idColor" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="_informacion">Información</label>
            <textarea name="_informacion" id="_informacion" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection
