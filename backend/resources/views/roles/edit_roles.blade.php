@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modificar Rol para el Usuario: {{ $usuario->name }}</h1>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('usuarios.updateRole', $usuario->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="role">Rol</label>
            <select name="role" id="role" class="form-control">
                @foreach ($roles as $rol)
                    <option value="{{ $rol->name }}" {{ $usuario->roles->contains('name', $rol->name) ? 'selected' : '' }}>
                        {{ $rol->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </form>
</div>
@endsection
