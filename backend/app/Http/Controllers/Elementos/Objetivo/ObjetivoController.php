<?php

namespace App\Http\Controllers\Elementos\Objetivo;
use App\Http\Controllers\Controller;

use  App\Models\UsuarioCuenta;
use Illuminate\Support\Facades\DB;


use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

use App\Models\Elementos\Meta;
use App\Models\Elementos\Objetivo;

class ObjetivoController extends Controller
{
    public function index()
    {
        $objetivos = Objetivo::all();
        return response()->json($objetivos);
    }

    // Guarda un nuevo objetivo en la base de datos
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

        $objetivo = Objetivo::create($validatedData);

        return response()->json([
            'message' => 'Objetivo creado con éxito.',
            'objetivo' => $objetivo,
        ], 201);
    }

    // Muestra un objetivo específico junto con sus metas
    public function show($id)
    {
        $objetivo = Objetivo::findOrFail($id);
        $metas = Meta::where('_idObjetivo', $id)->get();

        return response()->json([
            'objetivo' => $objetivo,
            'metas' => $metas,
        ]);
    }

    // Actualiza un objetivo en la base de datos
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

        $objetivo = Objetivo::findOrFail($id);
        $objetivo->update($validatedData);

        return response()->json([
            'message' => 'Objetivo actualizado con éxito.',
            'objetivo' => $objetivo,
        ]);
    }

    // Elimina un objetivo de la base de datos
    public function eliminar($id)
    {
        $objetivo = Objetivo::findOrFail($id);
        $objetivo->delete();

        return response()->json([
            'message' => 'Objetivo eliminado con éxito.'
        ]);
    }


    /**API**/
    public function obtenerObjetivosPorUsuario()
    {
        // Log detallado de peticiones entrantes
        $timestamp = now()->format('Y-m-d H:i:s.u');
        $userAgent = request()->header('User-Agent', 'Unknown');
        $ip = request()->ip();

        \Log::info("🎯 [OBJECTIVES REQUEST] Iniciando obtenerObjetivosPorUsuario", [
            'timestamp' => $timestamp,
            'user_ip' => $ip,
            'user_agent' => substr($userAgent, 0, 100),
            'referer' => request()->header('Referer'),
            'request_id' => request()->header('X-Request-ID', 'no-id')
        ]);

        $usuario = auth()->user();

        if (!$usuario) {
            \Log::warning("🎯 [OBJECTIVES REQUEST] Usuario no autenticado");
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        \Log::info("🎯 [OBJECTIVES REQUEST] Usuario autenticado", [
            'user_id' => $usuario->id,
            'email' => $usuario->email
        ]);
    
        // Obtener la cuenta del usuario
        $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
    
        if (!$cuenta) {
            return response()->json(['error' => 'Cuenta no encontrada'], 404);
        }
    
        // Obtener todos los objetivos relacionados a esta cuenta
        $objetivos = DB::table('objetivos')
            ->join('elementos', 'objetivos.elemento_id', '=', 'elementos.id')
            ->where('elementos.cuenta_id', $cuenta->id)
            ->select('objetivos.*')
            ->whereNull('objetivos.deleted_at') // Asegurarse de que no se obtengan objetivos eliminados
            ->whereNull('elementos.deleted_at') // Asegurarse de que no se obtengan elementos eliminados
            ->get();
    
        // Convertir a colección para usar métodos de Laravel
        $objetivos = collect($objetivos);
    
        // Para cada objetivo, obtener sus metas y agregarlas al objeto
        $objetivosConMetas = $objetivos->map(function ($objetivo) {
            $metas = DB::table('metas')
                ->select('metas.*')
                ->where('objetivo_id', $objetivo->id)
                ->whereNull('metas.deleted_at') // Asegurarse de que no se obtengan metas eliminadas
                ->whereNull('metas.deleted_at') // Asegurarse de que no se obtengan elementos eliminados
                ->get();
    
            // Convertir a array si prefieres que sea un arreglo en lugar de una colección
            $objetivo->metas = $metas; // O $metas->toArray() si quieres formato plano
    
            return $objetivo;
        });
    
        return response()->json([
            'objetivos' => $objetivosConMetas,
        ]);
    }


}

