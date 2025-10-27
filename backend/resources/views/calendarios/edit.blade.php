@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Calendario</h1>
    <form action="{{ route('calendarios.update', $calendario->_idCalendario) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="_idElemento">Elemento ID</label>
            <input type="number" name="_idElemento" id="_idElemento" class="form-control" value="{{ $calendario->_idElemento }}" required>
        </div>
        <div class="form-group">
            <label for="_nombre">Nombre</label>
            <input type="text" name="_nombre" id="_nombre" class="form-control" value="{{ $calendario->_nombre }}" required>
        </div>
        <div class="form-group">
            <label for="_idColor">ID Color</label>
            <input type="number" name="_idColor" id="_idColor" class="form-control" value="{{ $calendario->_idColor }}" required>
        </div>
        <div class="form-group">
            <label for="_informacion">Información</label>
            <textarea name="_informacion" id="_informacion" class="form-control">{{ $calendario->_informacion }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
