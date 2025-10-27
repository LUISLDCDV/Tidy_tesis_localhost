@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Detalles de la Nota</h1>
        <dl class="row">
            <dt class="col-sm-3">ID</dt>
            <dd class="col-sm-9">{{ $nota->_idNota }}</dd>

            <dt class="col-sm-3">Elemento ID</dt>
            <dd class="col-sm-9">{{ $nota->_idElemento }}</dd>

            <dt class="col-sm-3">Fecha</dt>
            <dd class="col-sm-9">{{ $nota->_fecha }}</dd>

            <dt class="col-sm-3">Nombre</dt>
            <dd class="col-sm-9">{{ $nota->_nombre }}</dd>

            <dt class="col-sm-3">Información</dt>
            <dd class="col-sm-9">{{ $nota->_informacion }}</dd>

            <dt class="col-sm-3">Contenido</dt>
            <dd class="col-sm-9">{{ json_encode($nota->_contenido) }}</dd>

            <dt class="col-sm-3">Estado</dt>
            <dd class="col-sm-9">{{ $nota->_status }}</dd>
        </dl>
        <a href="{{ route('notas.index') }}" class="btn btn-primary">Volver</a>
    </div>
@endsection

