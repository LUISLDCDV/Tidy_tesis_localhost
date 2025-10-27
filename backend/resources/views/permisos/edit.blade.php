@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Permiso</h1>
    <form action="{{ route('permisos.update', $permiso->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="_tipo">Tipo</label>
            <input type="text" name="tipo" id="tipo" class="form-control" value="{{ $permiso->tipo }}" required>
        </div>
        <div class="form-group">
            <label for="_descripcion">Descripción</label>
            <input type="text" name="descripcion" id="descripcion" class="form-control" value="{{ $permiso->descripcion }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
