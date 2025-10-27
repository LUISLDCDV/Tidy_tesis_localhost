@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Usuario Cuenta</h1>
    <form action="{{ route('usuario_cuentas.update', $usuarioCuenta->_id_usuario) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="_id_usuario">ID Usuario</label>
            <input type="number" name="_id_usuario" id="_id_usuario" class="form-control" value="{{ $usuarioCuenta->_id_usuario }}" required>
        </div>
        <div class="form-group">
            <label for="_puntaje_id">Puntaje ID</label>
            <input type="number" name="_puntaje_id" id="_puntaje_id" class="form-control" value="{{ $usuarioCuenta->_puntaje_id }}" required>
        </div>
        <div class="form-group">
            <label for="_medio_pago_id">Medio Pago ID (opcional)</label>
            <input type="number" name="_medio_pago_id" id="_medio_pago_id" class="form-control" value="{{ $usuarioCuenta->_medio_pago_id }}">
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
