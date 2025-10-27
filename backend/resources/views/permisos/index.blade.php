

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Permisos</h1>
    <a href="{{ route('permisos.create') }}" class="btn btn-primary">Crear Permiso</a>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Entorno</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($permisos as $permiso)
                <tr>
                    <td>{{ $permiso->id }}</td>
                    <td>{{ $permiso->name }}</td>
                    <td>{{ $permiso->guard_name }}</td>
                    <td>
                        <a href="{{ route('permisos.show', $permiso->id) }}" class="btn btn-info btn-sm" >Ver</a>
                        <a href="{{ route('permisos.edit', $permiso->id) }}" class="btn btn-warning btn-sm"  style="margin-left: 1.5%;">Editar</a>
                        <form action="{{ route('permisos.destroy', $permiso->id) }}" method="POST" style="display:inline;">
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
