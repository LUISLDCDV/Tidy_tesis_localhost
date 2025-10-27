<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsuarioAdmin;
class UsuarioAdminController extends Controller
{
    public function index()
    {
        $usuariosAdmin = UsuarioAdmin::all();
        return view('usuarios_admin.index', compact('usuariosAdmin'));
    }

    // Muestra el formulario para crear un nuevo usuario administrador
    public function create()
    {
        return view('usuarios_admin.create');
    }

    // Guarda un nuevo usuario administrador en la base de datos
    public function store(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|integer|exists:users,id',
            'rol_id' => 'required|integer',
            'rol_nombre' => 'required|string|max:100',
        ]);

        UsuarioAdmin::create($request->all());

        return redirect()->route('usuarios_admin.index')->with('success', 'Usuario administrador creado con éxito.');
    }

    // Muestra un usuario administrador específico
    public function show($id)
    {
        $usuarioAdmin = UsuarioAdmin::findOrFail($id);
        return view('usuarios_admin.show', compact('usuarioAdmin'));
    }

    // Muestra el formulario para editar un usuario administrador
    public function edit($id)
    {
        $usuarioAdmin = UsuarioAdmin::findOrFail($id);
        return view('usuarios_admin.edit', compact('usuarioAdmin'));
    }

    // Actualiza un usuario administrador en la base de datos
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_usuario' => 'required|integer|exists:users,id',
            'rol_id' => 'required|integer',
            'rol_nombre' => 'required|string|max:100',
        ]);

        $usuarioAdmin = UsuarioAdmin::findOrFail($id);
        $usuarioAdmin->update($request->all());

        return redirect()->route('usuarios_admin.index')->with('success', 'Usuario administrador actualizado con éxito.');
    }

    // Elimina un usuario administrador de la base de datos
    public function destroy($id)
    {
        $usuarioAdmin = UsuarioAdmin::findOrFail($id);
        $usuarioAdmin->delete();

        return redirect()->route('usuarios_admin.index')->with('success', 'Usuario administrador eliminado con éxito.');
    }
}
