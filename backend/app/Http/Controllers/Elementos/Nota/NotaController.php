<?php

// namespace App\Http\Controllers\Elementos\Nota\NotaController;
namespace App\Http\Controllers\Elementos\Nota;
use  App\Models\Elementos\Nota;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use  App\Models\Elementos\Elemento;
use  App\Models\UsuarioCuenta;


class NotaController extends Controller
{

    // Muestra una lista de notas
    public function index()
    {
        $notas = Nota::all();
        return response()->json($notas);
    }

    // Guarda una nueva nota en la base de datos
    public function guardar(Request $request)
    {
        $validatedData = $request->validate([
            'elemento_id' => 'required|exists:elementos,_idElemento',
            'fecha' => 'required|date',
            'nombre' => 'required|string|max:100',
            'informacion' => 'nullable|string',
            'contenido' => 'required|array',
            'status' => 'required|string|max:50',
        ]);

        $nota = Nota::create($validatedData);

        return response()->json([
            'message' => 'Nota creada con éxito.',
            'nota' => $nota,
        ], 201);
    }

    // Muestra una nota específica
    public function show($id)
    {
        $nota = Nota::findOrFail($id);
        return response()->json($nota);
    }

    // Actualiza una nota en la base de datos
    public function actualizar(Request $request, $id)
    {
        $validatedData = $request->validate([
            'elemento_id' => 'required|exists:elementos,_idElemento',
            'fecha' => 'required|date',
            'nombre' => 'required|string|max:100',
            'informacion' => 'nullable|string',
            'contenido' => 'required|array',
            'status' => 'required|string|max:50',
        ]);

        $nota = Nota::findOrFail($id);
        $nota->update($validatedData);

        return response()->json([
            'message' => 'Nota actualizada con éxito.',
            'nota' => $nota,
        ]);
    }

    // Elimina una nota de la base de datos
    public function eliminar($id)
    {
        $nota = Nota::findOrFail($id);
        $nota->delete();

        return response()->json([
            'message' => 'Nota eliminada con éxito.'
        ]);
    }

    /**API**/
    public function obtenerNotasPorUsuario()
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }
    
        $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
    
        if (!$cuenta) {
            return response()->json(['error' => 'Cuenta no encontrada para el usuario'], 404);
        }
    
        // Obtener los IDs de los elementos asociados a la cuenta
        $elementoIds = Elemento::where('cuenta_id', $cuenta->id)->pluck('id')->toArray();
    
        if (empty($elementoIds)) {
            return response()->json(['message' => 'No hay elementos asociados a esta cuenta'], 200);
        }
    
        // Obtener las notas que tengan elemento_id dentro de los IDs obtenidos
        $notas = Nota::whereIn('elemento_id', $elementoIds)->get();
    
        return response()->json(['notas' => $notas]);
    }


}
