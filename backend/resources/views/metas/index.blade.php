

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-flag-checkered"></i> Gestión de Metas
                    </h5>
                    <a href="{{ route('metas.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nueva Meta
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i> ID</th>
                                <th><i class="fas fa-cube"></i> Elemento ID</th>
                                <th><i class="fas fa-tag"></i> Tipo</th>
                                <th><i class="fas fa-calendar-plus"></i> Fecha Creación</th>
                                <th><i class="fas fa-calendar-check"></i> Fecha Vencimiento</th>
                                <th><i class="fas fa-heading"></i> Nombre</th>
                                <th><i class="fas fa-info-circle"></i> Información</th>
                                <th><i class="fas fa-cog"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($metas as $meta)
                                <tr>
                                    <td>{{ $meta->_idMeta }}</td>
                                    <td>{{ $meta->_idElemento }}</td>
                                    <td>{{ $meta->_tipo }}</td>
                                    <td>{{ $meta->_fechaCreacion }}</td>
                                    <td>{{ $meta->_fechaVencimiento }}</td>
                                    <td>
                                        <i class="fas fa-flag-checkered text-primary"></i>
                                        {{ $meta->_nombre }}
                                    </td>
                                    <td>{{ $meta->_informacion }}</td>
                                    <td>
                                        <a href="{{ route('metas.show', $meta->_idMeta) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        <a href="{{ route('metas.edit', $meta->_idMeta) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <form action="{{ route('metas.destroy', $meta->_idMeta) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta meta?')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
