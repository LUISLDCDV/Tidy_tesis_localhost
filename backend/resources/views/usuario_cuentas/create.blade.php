@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Crear Notificación</h1>
        <form action="{{ route('notificaciones.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="_idElemento">ID Elemento</label>
                <input type="number" name="_idElemento" id="_idElemento" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="_tipo">Tipo</label>
                <input type="text" name="_tipo" id="_tipo" class="form-control" required maxlength="50">
            </div>
            <div class="form-group">
                <label for="_descripcion">Descripción</label>
                <input type="text" name="_descripcion" id="_descripcion" class="form-control" required maxlength="50">
            </div>
            <div class="form-group">
                <label for="_id_usuario">ID Usuario</label>
                <input type="number" name="_id_usuario" id="_id_usuario" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
    </div>
@endsection
