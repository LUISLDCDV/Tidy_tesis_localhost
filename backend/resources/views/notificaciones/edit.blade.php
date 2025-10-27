@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Notificación</h1>
    <form action="{{ route('notificaciones.update', $notificacion->_idNotificacion) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="_id_usuario">ID Usuario</label>
            <input type="number" name="_id_usuario" id="_id_usuario" class="form-control" value="{{ $notificacion->_id_usuario }}" required>
        </div>
        <div class="form-group">
            <label for="_id_elemento">ID Elemento (opcional)</label>
            <input type="number" name="_id_elemento" id="_id_elemento" class="form-control" value="{{ $notificacion->_id_elemento }}">
        </div>
        <div class="form-group">
            <label for="_tipo">Tipo</label>
            <input type="text" name="_tipo" id="_tipo" class="form-control" value="{{ $notificacion->_tipo }}" required>
        </div>
        <div class="form-group">
            <label for="_descripcion">Descripción</label>
            <input type="text" name="_descripcion" id="_descripcion" class="form-control" value="{{ $notificacion->_descripcion }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
