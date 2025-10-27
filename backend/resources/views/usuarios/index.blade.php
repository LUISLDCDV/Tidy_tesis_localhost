@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Panel de Usuarios </h1>
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary">Crear Usuario ADMIN</a>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Mail</th>
                <th>Rol</th>
                <th>| Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->id }}</td>
                    <td>{{ $usuario->name }}</td>
                    <td>{{ $usuario->last_name }}</td>
                    <td>{{ $usuario->email }}</td>
                    <td>{{ $usuario->roles->pluck('name')->first() }}</td>
                    <td>
                        <a href="{{ route('usuarios.editRole', $usuario->id) }}" class="btn btn-info">Modificar Rol</a>
                    </td>
                    <td>
                        <a href="{{ route('usuarios.show', $usuario->id) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-warning btn-sm" style="margin-left:1.5%">Editar</a>

                        <!-- Botón para editar estadísticas -->
                        <button type="button" class="btn btn-success btn-sm" style="margin-left:1.5%" onclick="editStats({{ $usuario->id }}, '{{ $usuario->name }}')">
                            Editar Stats
                        </button>

                        <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" style="display:inline;">
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

<!-- Modal para editar estadísticas -->
<div class="modal fade" id="editStatsModal" tabindex="-1" role="dialog" aria-labelledby="editStatsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStatsModalLabel">Editar Estadísticas del Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editStatsForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Usuario:</strong> <span id="userName"></span>
                    </div>

                    <div class="form-group">
                        <label for="current_level">Nivel Actual</label>
                        <input type="number" class="form-control" id="current_level" name="current_level" min="1" max="100" required>
                    </div>

                    <div class="form-group">
                        <label for="total_xp">Experiencia Total (XP)</label>
                        <input type="number" class="form-control" id="total_xp" name="total_xp" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function editStats(userId, userName) {
    document.getElementById('userName').textContent = userName;

    // Configurar la acción del formulario
    document.getElementById('editStatsForm').action = '/usuarios/' + userId + '/stats';

    // Cargar datos actuales del usuario
    // Aquí puedes hacer una llamada AJAX para obtener los datos actuales
    // Por ahora, poner valores por defecto
    document.getElementById('current_level').value = 1;
    document.getElementById('total_xp').value = 0;

    // Mostrar el modal
    $('#editStatsModal').modal('show');
}
</script>
@endsection