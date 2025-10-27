@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Elemento</h1>
    <form action="{{ route('elementos.update', $elemento->_idElemento) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="_tipoElemento">Tipo de Elemento</label>
            <input type="text" name="_tipoElemento" id="_tipoElemento" class="form-control" value="{{ $elemento->_tipoElemento }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
