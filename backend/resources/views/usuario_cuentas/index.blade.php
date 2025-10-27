@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Usuarios Cuenta</h1>
    <a href="{{ route('usuario_cuentas.create') }}" class="btn btn-primary">Crear Usuario Cuenta</a>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>ID Usuario</th>
                <th>Puntaje ID</th>
                <th>Medio Pago ID</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($usuariosCuenta as $usuarioCuenta)
                <tr>
                    <td>{{ $usuarioCuenta->_id_usuario }}</td>
                    <td>{{ $usuarioCuenta->_id_usuario }}</td>
                    <td>{{ $usuarioCuenta->_puntaje_id }}</td>
                    <td>{{ $usuarioCuenta->_medio_pago_id }}</td>
                    <td>
                        <a href="{{ route('usuario_cuentas.show', $usuarioCuenta->_id_usuario) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('usuario_cuentas.edit', $usuarioCuenta->_id_usuario) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('usuario_cuentas.destroy', $usuarioCuenta->_id_usuario) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
