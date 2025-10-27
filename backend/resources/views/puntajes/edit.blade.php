@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Puntaje</h1>
    <form action="{{ route('puntajes.update', $puntaje->_idPuntaje) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="_cantidad">Cantidad</label>
            <input type="number" name="_cantidad" id="_cantidad" class="form-control" value="{{ $puntaje->_cantidad }}" required>
        </div>
        <div class="form-group">
            <label for="_rango">Rango</label>
            <input type="text" name="_rango" id="_rango" class="form-control" value="{{ $puntaje->_rango }}" required>
        </div>
        <div class="form-group">
            <label for="_id_usuario">ID Usuario</label>
            <input type="number" name="_id_usuario" id="_id_usuario" class="form-control" value="{{ $puntaje->_id_usuario }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
