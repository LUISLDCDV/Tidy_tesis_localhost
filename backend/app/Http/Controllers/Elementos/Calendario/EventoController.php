<?php

namespace App\Http\Controllers\Elementos\Calendario;
use App\Http\Controllers\Controller;

use App\Models\Elementos\Evento;
use App\Models\UsuarioCuenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;



class EventoController extends Controller
{
    public function index(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
        if (!$cuenta) {
            return response()->json(['error' => 'Cuenta no encontrada'], 404);
        }

        // Obtener IDs de elementos del usuario
        $elementoIds = \App\Models\Elementos\Elemento::where('cuenta_id', $cuenta->id)->pluck('id');

        $query = Evento::whereIn('elemento_id', $elementoIds);

        // Filtro por rango de fechas
        if ($request->has('start_date')) {
            $query->where('fechaVencimiento', '>=', $request->input('start_date'));
        }
        if ($request->has('end_date')) {
            $query->where('fechaVencimiento', '<=', $request->input('end_date'));
        }

        $eventos = $query->get();

        return response()->json(['data' => $eventos], 200);
    }

    // Guarda un nuevo evento en la base de datos
    public function guardar(Request $request)
    {
        $validatedData = $request->validate([
            'elemento_id' => 'required|exists:elementos,id',
            'tipo' => 'required|string|max:50',
            'fecha_vencimiento' => 'nullable|date',
            'hora_vencimiento' => 'nullable|date_format:H:i',
            'nombre' => 'required|string|max:100',
            'informacion' => 'nullable|string',
        ]);

        $evento = Evento::create($validatedData);

        return response()->json([
            'message' => 'Evento creado con éxito.',
            'evento' => $evento,
        ], 201);
    }

    // Muestra un evento específico
    public function show($id)
    {
        $evento = Evento::findOrFail($id);
        return response()->json($evento);
    }

    // Actualiza un evento en la base de datos
    public function actualizar(Request $request, $id)
    {
        $validatedData = $request->validate([
            'elemento_id' => 'required|exists:elementos,id',
            'tipo' => 'required|string|max:50',
            'fecha_vencimiento' => 'nullable|date',
            'hora_vencimiento' => 'nullable|date_format:H:i',
            'nombre' => 'required|string|max:100',
            'informacion' => 'nullable|string',
        ]);

        $evento = Evento::findOrFail($id);
        $evento->update($validatedData);

        return response()->json([
            'message' => 'Evento actualizado con éxito.',
            'evento' => $evento,
        ]);
    }

    // Elimina un evento de la base de datos
    public function eliminar($id)
    {
        $evento = Evento::findOrFail($id);
        $evento->delete();

        return response()->json([
            'message' => 'Evento eliminado con éxito.'
        ]);
    }

    /**API**/
    public function obtenerEventosPorUsuario($id_calendario)
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // $usuarioCuenta = UsuarioCuenta::find($usuario->id); // Cambia 1 por un id válido
        $Cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first(); // Cambia 1 por un id válido


        $eventos = DB::table('eventos')
            ->select('id','elemento_id', 'nombre','informacion', 'horaVencimiento', 'fechaVencimiento', 'gps', 'clima', 'calendario_id')
            // ->where('elementos.cuenta_id', $Cuenta->id)
            ->where('calendario_id', $id_calendario)
            ->get();



        return response()->json([
            'eventos' => $eventos,
        ]);

    }

    /**
     * Almacenar un nuevo evento (API REST)
     */
    public function store(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
        if (!$cuenta) {
            return response()->json(['error' => 'Cuenta no encontrada'], 404);
        }

        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'ubicacion' => 'nullable|string',
            'calendario_id' => 'required|exists:calendarios,id',
            'recurrencia' => 'nullable|array',
        ]);

        // Crear elemento primero
        $elemento = \App\Models\Elementos\Elemento::create([
            'tipo' => 'evento',
            'cuenta_id' => $cuenta->id,
            'estado' => 'activo',
        ]);

        // Crear el evento usando el mutator
        $evento = new Evento([
            'elemento_id' => $elemento->id,
            'nombre' => $validatedData['nombre'],
            'informacion' => $validatedData['descripcion'] ?? '',
            'calendario_id' => $validatedData['calendario_id'],
            'gps' => $validatedData['ubicacion'] ?? null,
            'recurrencia' => $validatedData['recurrencia'] ?? null,
        ]);

        // Usar el mutator para setear las fechas
        $evento->fecha_inicio = $validatedData['fecha_inicio'];
        $evento->fecha_fin = $validatedData['fecha_fin'];
        $evento->save();

        // Recargar para obtener los accessors
        $evento->load('elemento');

        return response()->json(['data' => $evento], 201);
    }

    /**
     * Actualizar un evento (API REST)
     */
    public function update(Request $request, $id)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
        if (!$cuenta) {
            return response()->json(['error' => 'Cuenta no encontrada'], 404);
        }

        $evento = Evento::findOrFail($id);

        // Verificar que el evento pertenece al usuario
        $elemento = $evento->elemento;
        if ($elemento->cuenta_id !== $cuenta->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validatedData = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|string',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'sometimes|date',
            'ubicacion' => 'sometimes|string',
            'recurrencia' => 'sometimes|array',
        ]);

        // Actualizar campos normales
        if (isset($validatedData['nombre'])) {
            $evento->nombre = $validatedData['nombre'];
        }
        if (isset($validatedData['descripcion'])) {
            $evento->informacion = $validatedData['descripcion'];
        }
        if (isset($validatedData['ubicacion'])) {
            $evento->gps = $validatedData['ubicacion'];
        }
        if (isset($validatedData['recurrencia'])) {
            $evento->recurrencia = $validatedData['recurrencia'];
        }

        // Usar mutators para las fechas
        if (isset($validatedData['fecha_inicio'])) {
            $evento->fecha_inicio = $validatedData['fecha_inicio'];
        }
        if (isset($validatedData['fecha_fin'])) {
            $evento->fecha_fin = $validatedData['fecha_fin'];
        }

        $evento->save();
        $evento->refresh(); // Refresh to get accessors

        return response()->json(['data' => $evento], 200);
    }

    /**
     * Eliminar un evento (API REST)
     */
    public function destroy($id)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
        if (!$cuenta) {
            return response()->json(['error' => 'Cuenta no encontrada'], 404);
        }

        $evento = Evento::findOrFail($id);

        // Verificar que el evento pertenece al usuario
        $elemento = $evento->elemento;
        if ($elemento->cuenta_id !== $cuenta->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Soft delete del evento y su elemento
        $evento->delete();
        $elemento->delete();

        return response()->json(['message' => 'Evento eliminado exitosamente'], 200);
    }

}
