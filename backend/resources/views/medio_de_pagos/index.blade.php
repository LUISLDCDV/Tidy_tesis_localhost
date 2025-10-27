@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Medios de Pago</h1>
    <a href="{{ route('medio_de_pagos.create') }}" class="btn btn-primary">Crear Medio de Pago</a>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Identificador</th>
                <th>Tipo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mediosDePago as $medioDePago)
                <tr>
                    <td>{{ $medioDePago->id }}</td>
                    <td>{{ $medioDePago->nombre }}</td>
                    <td>{{ $medioDePago->identificador }}</td>
                    <td>{{ $medioDePago->tipo }}</td>
                    <td>
                        <a href="{{ route('medio_de_pagos.show', $medioDePago->id) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('medio_de_pagos.edit', $medioDePago->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('medio_de_pagos.destroy', $medioDePago->id) }}" method="POST" style="display:inline;">
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
