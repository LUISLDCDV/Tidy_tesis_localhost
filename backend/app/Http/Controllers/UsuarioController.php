<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UsuarioCuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\Elementos\Elemento;
use Illuminate\Support\Facades\Log;


class UsuarioController extends Controller
{
    // Muestra una lista de usuarios
    public function index()
    {
        $usuarios = User::with(['roles', 'usuarioCuenta'])->get();
        return view('usuarios.index', compact('usuarios'));
    }

    // Muestra el formulario para crear un nuevo usuario
    public function create()
    {
        return view('usuarios.create');
    }

    // Guarda un nuevo usuario en la base de datos
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'phone' => 'required|string|digits_between:10,15|unique:users',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado con éxito.');
    }

    // Muestra un usuario específico
    public function show($id)
    {
        $usuario = User::findOrFail($id);
        return view('usuarios.show', compact('usuario'));
    }

    // Muestra el formulario para editar un usuario
    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'mail' => 'required|email|max:255|unique:users,email,' . $id,
        ]);

        $usuario = User::findOrFail($id);
        $usuario->name = $request->input('nombre');
        $usuario->last_name = $request->input('apellido');
        $usuario->email = $request->input('mail');
        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->input('password'));
        }
        $usuario->save();

        return redirect()->route('usuarios.index', $usuario->id)->with('success', 'Usuario actualizado correctamente.');
    }

    // Elimina un usuario de la base de datos
    public function destroy($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado con éxito.');
    }



    public function restore($id)
    {
        $usuario = User::withTrashed()->findOrFail($id);
        $usuario->restore();

        return redirect()->route('usuarios.index')->with('success', 'Usuario restaurado con éxito.');
    }


    public function assignPermission($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->givePermissionTo('manage users');

        return redirect()->route('usuarios.index')->with('success', 'Permiso asignado con éxito.');
    }

    public function editRole($id)
    {
        $usuario = User::findOrFail($id);
        $roles = Role::all();
        return view('usuarios.edit_roles', compact('usuario', 'roles'));
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $usuario = User::findOrFail($id);
        $usuario->syncRoles([$request->role]);

        return redirect()->route('usuarios.index')->with('success', 'Rol actualizado correctamente.');
    }

    /**
     * Actualiza las estadísticas del usuario (nivel, XP, premium)
     */
    public function updateStats(Request $request, $id)
    {
        $request->validate([
            'current_level' => 'required|integer|min:1|max:100',
            'total_xp' => 'required|integer|min:0',
        ]);

        $usuario = User::findOrFail($id);

        // Obtener o crear cuenta de usuario
        $cuenta = $usuario->usuarioCuenta;
        if (!$cuenta) {
            $cuenta = UsuarioCuenta::create([
                'user_id' => $usuario->id,
                'total_xp' => 0,
                'current_level' => 1,
                'configuraciones' => json_encode([]),
                'is_premium' => false,
            ]);
        }

        // Actualizar nivel y experiencia
        $cuenta->update([
            'current_level' => $request->current_level,
            'total_xp' => $request->total_xp,
        ]);

        return redirect()->route('usuarios.index')->with('success',
            "Estadísticas de {$usuario->name} actualizadas: Nivel {$request->current_level}, XP: " . number_format($request->total_xp)
        );
    }

}
