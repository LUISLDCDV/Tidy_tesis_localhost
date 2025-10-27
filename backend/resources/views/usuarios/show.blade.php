@extends('layouts.app')


@section('content')
<div class="container">
    <h1>Detalles del Usuario</h1>
    <p><strong>Nombre:</strong> {{ $usuario->name }}</p>
    <p><strong>Apellido:</strong> {{ $usuario->last_name }}</p>
    <p><strong>Mail:</strong> {{ $usuario->email }}</p>
</div>
<br>
<div class="container">
    <p style="color:darkcyan;"><strong>Rol:</strong> {{ $usuario->roles->pluck('name')->first() }}</p>

</div>
@endsection
