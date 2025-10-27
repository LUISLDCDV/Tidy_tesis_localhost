@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles del Puntaje</h1>
    <p><strong>ID:</strong> {{ $puntaje->_idPuntaje }}</p>
    <p><strong>Cantidad:</strong> {{ $puntaje->_cantidad }}</p>
    <p><strong>Rango:</strong> {{ $puntaje->_rango }}</p>
    <p><strong>ID Usuario:</strong> {{ $puntaje->_id_usuario }}</p>
    <a href="{{ route('puntajes.index') }}" class="btn btn-primary">Volver</a>
</div>
@endsection
