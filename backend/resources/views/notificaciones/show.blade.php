@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles de la Notificación</h1>
    <p><strong>ID:</strong> {{ $notificacion->_idNotificacion }}</p>
    <p><strong>ID Usuario:</strong> {{ $notificacion->_id_usuario }}</p>
    <p><strong>ID Elemento:</strong> {{ $notificacion->_id_elemento }}</p>
    <p><strong>Tipo:</strong> {{ $notificacion->_tipo }}</p>
    <p><strong>Descripción:</strong> {{ $notificacion->_descripcion }}</p>
    <a href="{{ route('notificaciones.index') }}" class="btn btn-primary">Volver</a>
</div>
@endsection
