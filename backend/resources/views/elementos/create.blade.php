@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Elemento</h1>
    <form action="{{ route('elementos.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="_tipoElemento">Tipo de Elemento</label>
            <input type="text" name="_tipoElemento" id="_tipoElemento" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection
