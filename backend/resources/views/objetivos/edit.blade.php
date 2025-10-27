@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Objetivo</h1>
    <form action="{{ route('objetivos.update', $objetivo->_idObjetivo) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="_idElemento">Elemento ID</label>
            <input type="number" name="_idElemento" id="_idElemento" class="form-control" value="{{ $objetivo->_idElemento }}" required>
        </div>
        <div class="form-group">
            <label for="_tipo">Tipo</label>
            <input type="text" name="_tipo" id="_tipo" class="form-control" value="{{ $objetivo->_tipo }}" required>
        </div>
        <div class="form-group">
            <label for="_fechaCreacion">Fecha Creación</label>
            <input type="date" name="_fechaCreacion" id="_fechaCreacion" class="form-control" value="{{ $objetivo->_fechaCreacion }}" required>
        </div>
        <div class="form-group">
            <label for="_fechaVencimiento">Fecha Vencimiento</label>
            <input type="date" name="_fechaVencimiento" id="_fechaVencimiento" class="form-control" value="{{ $objetivo->_fechaVencimiento }}" required>
        </div>
        <div class="form-group">
            <label for="_nombre">Nombre</label>
            <input type="text" name="_nombre" id="_nombre" class="form-control" value="{{ $objetivo->_nombre }}" required>
        </div>
        <div class="form-group">
            <label for="_informacion">Información</label>
            <textarea name="_informacion" id="_informacion" class="form-control">{{ $objetivo->_informacion }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
