@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modificar Permisos para el Rol: {{ $rol->name }}</h1>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-group">
        <label for="current_permissions">Permisos Actuales</label>
        <table class="table">
            <thead>
                <tr>
                    <th>Permiso</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rol->permissions as $permiso)
                    <tr>
                        <td>{{ $permiso->name }}</td>
                        <td>
                            <form action="{{ route('roles.removePermission', [$rol->id, $permiso->id]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Quitar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <br>

    <div class="form-group">
        <label for="available_permissions">Permisos Disponibles</label>
        <table class="table">
            <thead>
                <tr>
                    <th>Permiso</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($permisos as $permiso)
                    @if (!$rol->permissions->contains($permiso->id))
                        <tr>
                            <td>{{ $permiso->name }}</td>
                            <td>
                                <form action="{{ route('roles.addPermission', [$rol->id, $permiso->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">Agregar</button>
                                </form>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
