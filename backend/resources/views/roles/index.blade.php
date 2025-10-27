

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Roles</h1>
    <a href="{{ route('roles.create') }}" class="btn btn-primary">Crear Rol</a>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Tipo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $rol)
                <tr>
                    <td>{{ $rol->id }}</td>
                    <td>{{ $rol->name }}</td>
                    <td>{{ $rol->guard_name }}</td>
                    <td>
                        <a href="{{ route('roles.editPermissions', $rol->id) }}" class="btn btn-info">Modificar Permisos</a>
                        <a href="{{ route('roles.edit', $rol->id) }}" class="btn btn-warning btn-sm" style="margin-left: 1.5%;">Editar</a>
                        <form action="{{ route('roles.destroy', $rol->id) }}" method="POST" style="display:inline;">
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
