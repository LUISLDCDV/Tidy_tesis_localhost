@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Puntaje</h1>
    <form action="{{ route('puntajes.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="_cantidad">Cantidad</label>
            <input type="number" name="_cantidad" id="_cantidad" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="_rango">Rango</label>
            <input type="text" name="_rango" id="_rango" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="_id_usuario">ID Usuario</label>
            <input type="number" name="_id_usuario" id="_id_usuario" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection
