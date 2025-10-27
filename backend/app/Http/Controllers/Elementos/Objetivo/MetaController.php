<?php

namespace App\Http\Controllers\Elementos\Objetivo;
use App\Http\Controllers\Controller;
use App\Models\UsuarioCuenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Elementos\Meta;
use App\Models\Elementos\Objetivo;
use  App\Models\Elementos\Elemento;

class MetaController extends Controller
{
    // Muestra una lista de metas
    public function index()
    {
        $metas = Meta::all();
        return response()->json($metas);
    }

    // Guarda una nueva meta en la base de datos
    public function guardar(Request $request)
    {
        $validatedData = $request->validate([
            '_idElemento' => 'required|exists:elementos,_idElemento',
            '_tipo' => 'required|string|max:50',
            '_fechaCreacion' => 'required|date',
            '_fechaVencimiento' => 'required|date',
            '_nombre' => 'required|string|max:100',
            '_informacion' => 'nullable|string',
        ]);

        $meta = Meta::create($validatedData);

        return response()->json([
            'message' => 'Meta creada con éxito.',
            'meta' => $meta,
        ], 201);
    }

    // Muestra una meta específica
    public function show($id)
    {
        $meta = Meta::findOrFail($id);
        return response()->json($meta);
    }

    // Actualiza una meta en la base de datos
    public function actualizar(Request $request, $id)
    {
        $validatedData = $request->validate([
            '_idElemento' => 'required|exists:elementos,_idElemento',
            '_tipo' => 'required|string|max:50',
            '_fechaCreacion' => 'required|date',
            '_fechaVencimiento' => 'required|date',
            '_nombre' => 'required|string|max:100',
            '_informacion' => 'nullable|string',
        ]);

        $meta = Meta::findOrFail($id);
        $meta->update($validatedData);

        return response()->json([
            'message' => 'Meta actualizada con éxito.',
            'meta' => $meta,
        ]);
    }

    // Elimina una meta de la base de datos
    public function eliminar($id)
    {
        $meta = Meta::findOrFail($id);
        $meta->delete();

        return response()->json([
            'message' => 'Meta eliminada con éxito.'
        ]);
    }


    /**API**/
    public function obtenerMetasPorUsuario($id_objetivo)
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
    
        $metasObjetivo = Meta::where('objetivo_id', $id_objetivo)
            ->whereIn('elemento_id', $elementoIds)
            ->get();
    

        return response()->json(['metas' => $metasObjetivo]);
    }
}
