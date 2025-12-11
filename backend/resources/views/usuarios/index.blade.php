@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users"></i> Panel de Usuarios
                    </h5>
                    <a href="{{ route('usuarios.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus"></i> Crear Usuario ADMIN
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i> ID</th>
                                <th><i class="fas fa-user"></i> Nombre</th>
                                <th><i class="fas fa-user"></i> Apellido</th>
                                <th><i class="fas fa-envelope"></i> Email</th>
                                <th><i class="fas fa-user-tag"></i> Rol</th>
                                <th><i class="fas fa-cog"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usuarios as $usuario)
                                <tr>
                                    <td>{{ $usuario->id }}</td>
                                    <td>
                                        <i class="fas fa-user text-primary"></i>
                                        {{ $usuario->name }}
                                    </td>
                                    <td>{{ $usuario->last_name }}</td>
                                    <td>{{ $usuario->email }}</td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <i class="fas fa-user-tag"></i> {{ $usuario->roles->pluck('name')->first() }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('usuarios.editRole', $usuario->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-user-tag"></i> Modificar Rol
                                        </a>
                                        <a href="{{ route('usuarios.show', $usuario->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <button type="button" class="btn btn-success btn-sm" onclick="editStats({{ $usuario->id }}, '{{ $usuario->name }}')">
                                            <i class="fas fa-chart-bar"></i> Editar Stats
                                        </button>
                                        <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
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