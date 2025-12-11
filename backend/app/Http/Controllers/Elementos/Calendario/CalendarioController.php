<?php

namespace App\Http\Controllers\Elementos\Calendario;

use App\Http\Controllers\Controller;
use App\Models\Elementos\Calendario;
use App\Models\UsuarioCuenta; // Importar el modelo correcto
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Importar DB

class CalendarioController extends Controller
{
    // Muestra una lista de todos los calendarios
    public function index()
    {
        $calendarios = Calendario::all();
        return response()->json($calendarios);
    }

    // Guarda un nuevo calendario en la base de datos
    public function guardar(Request $request)
    {
        $validatedData = $request->validate([
            '_idElemento' => 'required|exists:elementos,_idElemento',
            '_nombre' => 'required|string|max:100',
            '_idColor' => 'required|integer',
            '_informacion' => 'nullable|string',
        ]);

        $calendario = Calendario::create($validatedData);

        return response()->json([
            'message' => 'Calendario creado con éxito.',
            'calendario' => $calendario,
        ], 201);
    }

    // Muestra un calendario específico
    public function show($id)
    {
        $calendario = Calendario::findOrFail($id);
        return response()->json($calendario);
    }

    // Actualiza un calendario en la base de datos
    public function actualizar(Request $request, $id)
    {
        $validatedData = $request->validate([
            '_idElemento' => 'required|exists:elementos,_idElemento',
            '_nombre' => 'required|string|max:100',
            '_idColor' => 'required|integer',
            '_informacion' => 'nullable|string',
        ]);

        $calendario = Calendario::findOrFail($id);
        $calendario->update($validatedData);

        return response()->json([
            'message' => 'Calendario actualizado con éxito.',
            'calendario' => $calendario,
        ]);
    }

    // Elimina un calendario de la base de datos
    public function eliminar($id)
    {
        $calendario = Calendario::findOrFail($id);
        $calendario->delete();

        return response()->json([
            'message' => 'Calendario eliminado con éxito.'
        ]);
    }

    public function obtenerCalendariosPorUsuario()
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();

        if (!$cuenta) {
            return response()->json(['error' => 'Cuenta no encontrada'], 404);
        }

        // Obtener los calendarios con eventos
        $calendarios = DB::table('calendarios')
            ->join('elementos', 'calendarios.elemento_id', '=', 'elementos.id')
            ->whereNull('elementos.deleted_at')
            ->where('elementos.cuenta_id', $cuenta->id)
            ->select('calendarios.*')
            ->get();



        $calendariosConEventos = $calendarios->map(function ($calendario) {
            $eventosCalendario = DB::table('eventos')
                ->select('eventos.*')
                ->where('calendario_id', $calendario->id)
                ->whereNull('eventos.deleted_at')
                ->get();

            $calendario->eventos = $eventosCalendario;

            return $calendario;
        });

        return response()->json([
            'calendarios' => $calendariosConEventos,
        ]);
    }

    /**
     * Almacenar un nuevo calendario (API REST)
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
            'color' => 'required|string|max:20',
        ]);

        // Crear elemento primero
        $elemento = \App\Models\Elementos\Elemento::create([
            'tipo' => 'calendario',
            'cuenta_id' => $cuenta->id,
            'estado' => 'activo',
        ]);

        // Crear el calendario
        $calendario = Calendario::create([
            'elemento_id' => $elemento->id,
            'nombre' => $validatedData['nombre'],
            'informacion' => $validatedData['descripcion'] ?? '',
            'color' => $validatedData['color'],
        ]);

        return response()->json(['data' => $calendario], 201);
    }

    /**
     * Actualizar un calendario (API REST)
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

        $calendario = Calendario::findOrFail($id);

        // Verificar que el calendario pertenece al usuario
        $elemento = $calendario->elemento;
        if ($elemento->cuenta_id !== $cuenta->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validatedData = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|string',
            'color' => 'sometimes|string|max:20',
        ]);

        if (isset($validatedData['descripcion'])) {
            $validatedData['informacion'] = $validatedData['descripcion'];
            unset($validatedData['descripcion']);
        }

        $calendario->update($validatedData);

        return response()->json(['data' => $calendario], 200);
    }

    /**
     * Eliminar un calendario (API REST)
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

        $calendario = Calendario::findOrFail($id);

        // Verificar que el calendario pertenece al usuario
        $elemento = $calendario->elemento;
        if ($elemento->cuenta_id !== $cuenta->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Soft delete de eventos asociados
        $eventos = \App\Models\Elementos\Evento::where('calendario_id', $calendario->id)->get();
        foreach ($eventos as $evento) {
            $evento->delete();
            if ($evento->elemento) {
                $evento->elemento->delete();
            }
        }

        // Soft delete del calendario y su elemento
        $calendario->delete();
        $elemento->delete();

        return response()->json(['message' => 'Calendario eliminado exitosamente'], 200);
    }

}