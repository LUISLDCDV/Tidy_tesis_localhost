@extends('layouts.app')


@section('content')
<div class="container">
    <h1>Crear Usuario</h1>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('usuarios.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="last_name">Apellido</label>
            <input type="text" name="last_name" id="last_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="phone">telefono</label>
            <input type="text" name="phone" id="phone" class="form-control" 
            required pattern="\d{10,15}" minlength="10" maxlength="15" 
            placeholder="El número de teléfono debe tener entre 10 y 15 dígitos" 
            oninput="this.value = this.value.replace(/[^0-9]/g, '');">
        </div>
        <div class="form-group">
            <label for="email">Mail</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection

