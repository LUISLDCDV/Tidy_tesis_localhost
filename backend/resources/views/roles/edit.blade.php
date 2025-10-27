@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar roles</h1>
    <form action="{{ route('roles.update', $rol->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $rol->name }}" required>
        </div>
        <div class="form-group">
            <label for="_descripcion">Tipo</label>
            <input type="text" name="descripcion" id="descripcion" class="form-control" value="{{ $rol->guard_name }}" disabled>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
