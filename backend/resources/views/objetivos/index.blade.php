

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-bullseye"></i> Gestión de Objetivos
                    </h5>
                    <a href="{{ route('objetivos.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nuevo Objetivo
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
                            @foreach($objetivos as $objetivo)
                                <tr>
                                    <td>{{ $objetivo->_idObjetivo }}</td>
                                    <td>{{ $objetivo->_idElemento }}</td>
                                    <td>{{ $objetivo->_tipo }}</td>
                                    <td>{{ $objetivo->_fechaCreacion }}</td>
                                    <td>{{ $objetivo->_fechaVencimiento }}</td>
                                    <td>
                                        <i class="fas fa-bullseye text-primary"></i>
                                        {{ $objetivo->_nombre }}
                                    </td>
                                    <td>{{ $objetivo->_informacion }}</td>
                                    <td>
                                        <a href="{{ route('objetivos.show', $objetivo->_idObjetivo) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        <a href="{{ route('objetivos.edit', $objetivo->_idObjetivo) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <form action="{{ route('objetivos.destroy', $objetivo->_idObjetivo) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este objetivo?')">
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
