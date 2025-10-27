@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Nota</h1>
    <form action="{{ route('notas.update', $nota->_idNota) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="_idElemento">Elemento ID</label>
            <input type="number" name="_idElemento" id="_idElemento" class="form-control" value="{{ $nota->_idElemento }}" required>
        </div>
        <div class="form-group">
            <label for="_fecha">Fecha</label>
            <input type="date" name="_fecha" id="_fecha" class="form-control" value="{{ $nota->_fecha }}" required>
        </div>
        <div class="form-group">
            <label for="_nombre">Nombre</label>
            <input type="text" name="_nombre" id="_nombre" class="form-control" value="{{ $nota->_nombre }}" required>
        </div>
        <div class="form-group">
            <label for="_informacion">Información</label>
            <textarea name="_informacion" id="_informacion" class="form-control">{{ $nota->_informacion }}</textarea>
        </div>
        <div class="form-group">
            <label for="_contenido">Contenido (JSON)</label>
            <textarea name="_contenido" id="_contenido" class="form-control" required>{{ json_encode($nota->_contenido) }}</textarea>
        </div>
        <div class="form-group">
            <label for="_status">Estado</label>
            <input type="text" name="_status" id="_status" class="form-control" value="{{ $nota->_status }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection

