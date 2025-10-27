<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermisoController extends Controller
{
    // Muestra una lista de permisos
    public function index()
    {
        $permisos = Permission::all();

        // dd($permisos);
        // return response()->json($permisos);
        return view('Permisos.index', compact('permisos'));
    }

    public function create()
    {
        return view('Permisos.create');
    }

    // Guarda un nuevo permiso en la base de datos
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions',
        ]);

        Permission::create(['name' => $request->name]);

        return redirect()->route('permisos.index')->with('success', 'Permiso creado con éxito.');
    }


    // Muestra el formulario para editar un usuario
    public function edit($id)
    {
        $permiso = Permission::findOrFail($id);
        return view('permisos.edit', compact('permiso'));
    }

    // Actualiza un permiso en la base de datos
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
        ]);

        $permiso = Permission::findOrFail($id);
        $permiso->update(['name' => $request->name]);

        return redirect()->route('permisos.index')->with('success', 'Permiso actualizado correctamente.');
    }

    // Elimina un permiso de la base de datos
    public function destroy($id)
    {
        $permiso = Permission::findOrFail($id);
        $permiso->delete();

        return redirect()->route('permisos.index')->with('success', 'Permiso eliminado correctamente.');
    }
}
