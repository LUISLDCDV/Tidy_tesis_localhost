@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles del Permiso</h1>
    <p><strong>ID:</strong> {{ $permiso->id }}</p>
    <p><strong>Tipo:</strong> {{ $permiso->tipo }}</p>
    <p><strong>Descripción:</strong> {{ $permiso->descripcion }}</p>
    <a href="{{ route('permisos.index') }}" class="btn btn-primary">Volver</a>
</div>
@endsection
