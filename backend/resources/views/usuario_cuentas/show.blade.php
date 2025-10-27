@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles del Usuario Cuenta</h1>
    <p><strong>ID Usuario:</strong> {{ $usuarioCuenta->_id_usuario }}</p>
    <p><strong>Puntaje ID:</strong> {{ $usuarioCuenta->_puntaje_id }}</p>
    <p><strong>Medio Pago ID:</strong> {{ $usuarioCuenta->_medio_pago_id }}</p>
    <a href="{{ route('usuario_cuentas.index') }}" class="btn btn-primary">Volver</a>
</div>
@endsection
