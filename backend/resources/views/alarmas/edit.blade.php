@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Editar Alarma</h1>
        <form action="{{ route('alarmas.update', $alarma->_idAlarma) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="_idElemento">Elemento ID</label>
                <input type="number" name="_idElemento" id="_idElemento" class="form-control" value="{{ $alarma->_idElemento }}" required>
            </div>
            <div class="form-group">
                <label for="_fecha">Fecha</label>
                <input type="date" name="_fecha" id="_fecha" class="form-control" value="{{ $alarma->_fecha }}">
            </div>
            <div class="form-group">
                <label for="_hora">Hora</label>
                <input type="time" name="_hora" id="_hora" class="form-control" value="{{ $alarma->_hora }}" required>
            </div>
            <div class="form-group">
                <label for="_fecha_vencimiento">Fecha de Vencimiento</label>
                <input type="date" name="_fecha_vencimiento" id="_fecha_vencimiento" class="form-control" value="{{ $alarma->_fecha_vencimiento }}">
            </div>
            <div class="form-group">
                <label for="_hora_vencimiento">Hora de Vencimiento</label>
                <input type="time" name="_hora_vencimiento" id="_hora_vencimiento" class="form-control" value="{{ $alarma->_hora_vencimiento }}">
            </div>
            <div class="form-group">
                <label for="_nombre">Nombre</label>
                <input type="text" name="_nombre" id="_nombre" class="form-control" value="{{ $alarma->_nombre }}" required>
            </div>
            <div class="form-group">
                <label for="_informacion">Información</label>
                <textarea name="_informacion" id="_informacion" class="form-control">{{ $alarma->_informacion }}</textarea>
            </div>
            <div class="form-group">
                <label for="_configuracion">Configuración</label>
                <textarea name="_configuracion" id="_configuracion" class="form-control">{{ $alarma->_configuracion }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </form>
    </div>
@endsection

