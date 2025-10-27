<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolController extends Controller
{

    public function index()
    {
        $roles = Role::all();
        // return response()->json($permisos);
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
        ]);

        Role::create(['name' => $request->name]);

        return redirect()->route('roles.create')->with('success', 'Rol creado con éxito.');
    }



    // Muestra el formulario para editar un usuario
    public function edit($id)
    {
        $rol = Role::findOrFail($id);
        return view('roles.edit', compact('rol'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
        ]);

        $rol = Role::findOrFail($id);
        $rol->update(['name' => $request->name]);

        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy($id)
    {
        $rol = Role::findOrFail($id);
        $rol->delete();

        return redirect()->route('roles.index', $rol->id)->with('success', 'Rol eliminado correctamente.');

    }


    public function editPermissions($id)
    {
        $rol = Role::findOrFail($id);
        $permisos = Permission::all();
        return view('roles.edit_permissions', compact('rol', 'permisos'));
    }

    
    public function updatePermissions(Request $request, $id)
    {
        $rol = Role::findOrFail($id);

        // Obtener los permisos actuales y disponibles
        $currentPermissions = Permission::whereIn('id', $request->current_permissions ?? [])->pluck('name');
        $availablePermissions = Permission::whereIn('id', $request->available_permissions ?? [])->pluck('name');

        // Sincronizar los permisos
        $rol->syncPermissions($currentPermissions->merge($availablePermissions));

        return redirect()->route('roles.editPermissions', $rol->id)->with('success', 'Permisos actualizados correctamente.');
    }


    public function addPermission(Request $request, $roleId, $permissionId)
    {
        $rol = Role::findOrFail($roleId);
        $permiso = Permission::findOrFail($permissionId);
        $rol->givePermissionTo($permiso);

        return redirect()->route('roles.editPermissions', $rol->id)->with('success', 'Permiso agregado correctamente.');
    }

    public function removePermission(Request $request, $roleId, $permissionId)
    {
        $rol = Role::findOrFail($roleId);
        $permiso = Permission::findOrFail($permissionId);
        $rol->revokePermissionTo($permiso);

        return redirect()->route('roles.editPermissions', $rol->id)->with('success', 'Permiso quitado correctamente.');
    }
}
