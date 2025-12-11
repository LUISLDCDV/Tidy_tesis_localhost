<?php

namespace App\Http\Controllers\Elementos\Alarma;
use App\Http\Controllers\Controller;
use  App\Models\UsuarioCuenta;
use Illuminate\Support\Facades\DB;


use Illuminate\Support\Facades\Validator;
use App\Models\Elementos\Alarma;
use Illuminate\Http\Request;

class AlarmaController extends Controller
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

        $query = Alarma::whereIn('elemento_id', $elementoIds);

        // Filtro por búsqueda
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('nombre', 'like', "%{$search}%");
        }

        // Filtro por rango de fechas
        if ($request->has('fecha_inicio')) {
            $query->where('fecha', '>=', $request->input('fecha_inicio'));
        }
        if ($request->has('fecha_fin')) {
            $query->where('fecha', '<=', $request->input('fecha_fin'));
        }

        $alarmas = $query->get();

        return response()->json(['data' => $alarmas], 200);
    }

    public function guardar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '_idElemento' => 'required|exists:elementos,_idElemento',
            '_hora' => 'required|date_format:H:i',
            '_nombre' => 'required|string|max:100',
        //     '_idElemento' => 'required|exists:elementos,_idElemento',
        //     '_fecha' => 'nullable|date',
        //     '_hora' => 'required|date_format:H:i',
        //     '_fecha_vencimiento' => 'nullable|date',
        //     '_hora_vencimiento' => 'nullable|date_format:H:i',
        //     '_nombre' => 'required|string|max:100',
        //     '_informacion' => 'nullable|string',
        //     '_configuracion' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        // $elemento = new Elemento();
        // $elemento->tipo = '1';
        // $elemento->nombre = 'nombre';
        // $elemento->save();

        // $alarma = new Alarma();
        // $alarma->tipo = 'Tipo 1';
        // $alarma->elemento()->associate($elemento);
        // $alarma->save();
        

        $alarma = Alarma::create($request->all());

        return response()->json(['message' => 'Alarma creada con éxito.', 'alarma' => $alarma], 201);
    }

    public function show($id)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
        if (!$cuenta) {
            return response()->json(['error' => 'Cuenta no encontrada'], 404);
        }

        $alarma = Alarma::with('elemento')->findOrFail($id);

        // Verificar que la alarma pertenece al usuario
        if ($alarma->elemento->cuenta_id !== $cuenta->id) {
            return response()->json(['error' => 'No autorizado'], 404);
        }

        return response()->json(['data' => $alarma], 200);
    }

    public function actualizar(Request $request, $id)
    {
        $request->validate([
            '_idElemento' => 'required|exists:elementos,_idElemento',
            '_fecha' => 'nullable|date',
            '_hora' => 'required|date_format:H:i',
            '_fecha_vencimiento' => 'nullable|date',
            '_hora_vencimiento' => 'nullable|date_format:H:i',
            '_nombre' => 'required|string|max:100',
            '_informacion' => 'nullable|string',
            '_configuracion' => 'nullable|array',
        ]);

        $alarma = Alarma::findOrFail($id);
        $alarma->update($request->all());

        return response()->json(['message' => 'Alarma actualizada con éxito.', 'alarma' => $alarma]);
    }

    public function eliminar($id)
    {
        $alarma = Alarma::findOrFail($id);
        $alarma->delete();

        return response()->json(['message' => 'Alarma eliminada con éxito.']);
    }

    /**API**/
    public function obtenerAlarmasPorUsuario()
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // $usuarioCuenta = UsuarioCuenta::find($usuario->id); // Cambia 1 por un id válido
        $Cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first(); // Cambia 1 por un id válido

        $alarmas = DB::table('alarmas')
        ->join('elementos', 'alarmas.elemento_id', '=', 'elementos.id')
        ->where('elementos.cuenta_id', $Cuenta->id)
        ->whereNull('elementos.deleted_at')
        ->select('alarmas.*') // Seleccionar solo los campos de las notas
        ->get();


        return response()->json($alarmas);
    }

    /**
     * Almacenar una nueva alarma (API REST)
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
            'informacion' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'fecha' => 'nullable|date',
            'hora' => 'nullable|date_format:H:i:s',
            'fechaVencimiento' => 'nullable|date',
            'horaVencimiento' => 'nullable|date_format:H:i:s',
            'intensidad_volumen' => 'nullable|numeric|min:0|max:100',
            'configuraciones' => 'nullable|array',
            'tipo_alarma' => 'nullable|string|max:50',
            'ubicacion' => 'nullable|array',
            'ubicacion.lat' => 'required_with:ubicacion|numeric|between:-90,90',
            'ubicacion.lng' => 'required_with:ubicacion|numeric|between:-180,180',
            'ubicacion.address' => 'nullable|string',
            'ubicacion.radius' => 'nullable|numeric|min:0',
        ]);

        // Crear elemento primero
        $elemento = \App\Models\Elementos\Elemento::create([
            'tipo' => 'alarma',
            'cuenta_id' => $cuenta->id,
            'estado' => 'activo',
        ]);

        // Crear la alarma
        $alarma = Alarma::create([
            'elemento_id' => $elemento->id,
            'nombre' => $validatedData['nombre'],
            'informacion' => $validatedData['descripcion'] ?? $validatedData['informacion'] ?? '',
            'fecha' => $validatedData['fecha'] ?? null,
            'hora' => $validatedData['hora'] ?? '00:00:00',
            'fechaVencimiento' => $validatedData['fechaVencimiento'] ?? null,
            'horaVencimiento' => $validatedData['horaVencimiento'] ?? null,
            'intensidad_volumen' => $validatedData['intensidad_volumen'] ?? 5,
            'configuraciones' => $validatedData['configuraciones'] ?? null,
            'tipo_alarma' => $validatedData['tipo_alarma'] ?? null,
            'ubicacion' => $validatedData['ubicacion'] ?? null,
        ]);

        // Recargar con relaciones
        $alarma->load('elemento');

        // Agregar descripcion al response si existe
        if (isset($validatedData['descripcion'])) {
            $alarmaData = $alarma->toArray();
            $alarmaData['descripcion'] = $validatedData['descripcion'];
            return response()->json(['data' => $alarmaData], 201);
        }

        return response()->json(['data' => $alarma], 201);
    }

    /**
     * Actualizar una alarma (API REST)
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

        $alarma = Alarma::findOrFail($id);

        // Verificar que la alarma pertenece al usuario
        $elemento = $alarma->elemento;
        if ($elemento->cuenta_id !== $cuenta->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validatedData = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'informacion' => 'sometimes|string',
            'fecha' => 'sometimes|date',
            'hora' => 'sometimes|date_format:H:i:s',
            'fechaVencimiento' => 'sometimes|date',
            'horaVencimiento' => 'sometimes|date_format:H:i:s',
            'intensidad_volumen' => 'sometimes|numeric|min:0|max:100',
            'configuraciones' => 'sometimes|array',
        ]);

        $alarma->update($validatedData);

        return response()->json(['data' => $alarma], 200);
    }

    /**
     * Eliminar una alarma (API REST)
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

        $alarma = Alarma::findOrFail($id);

        // Verificar que la alarma pertenece al usuario
        $elemento = $alarma->elemento;
        if ($elemento->cuenta_id !== $cuenta->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Soft delete de la alarma y su elemento
        $alarma->delete();
        $elemento->delete();

        return response()->json(['message' => 'Alarma eliminada exitosamente'], 200);
    }

    /**
     * Toggle estado de la alarma (activo/inactivo)
     */
    public function toggle($id)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
        if (!$cuenta) {
            return response()->json(['error' => 'Cuenta no encontrada'], 404);
        }

        $alarma = Alarma::findOrFail($id);

        // Verificar que la alarma pertenece al usuario
        $elemento = $alarma->elemento;
        if ($elemento->cuenta_id !== $cuenta->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Toggle estado del elemento
        $nuevoEstado = $elemento->estado === 'activo' ? 'inactivo' : 'activo';
        $elemento->update(['estado' => $nuevoEstado]);

        return response()->json([
            'message' => 'Estado actualizado',
            'estado' => $nuevoEstado
        ], 200);
    }

    /**
     * Buscar alarmas cercanas a una ubicación
     */
    public function nearby(Request $request)
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
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:0'
        ]);

        // Obtener todas las alarmas del usuario con ubicación
        $elementoIds = \App\Models\Elementos\Elemento::where('cuenta_id', $cuenta->id)->pluck('id');
        $alarmas = Alarma::whereIn('elemento_id', $elementoIds)
            ->whereNotNull('ubicacion')
            ->get();

        $nearbyAlarms = $alarmas->filter(function ($alarma) use ($validatedData) {
            if (!$alarma->ubicacion || !isset($alarma->ubicacion['lat']) || !isset($alarma->ubicacion['lng'])) {
                return false;
            }

            $distance = $this->calculateDistance(
                $validatedData['lat'],
                $validatedData['lng'],
                $alarma->ubicacion['lat'],
                $alarma->ubicacion['lng']
            );

            return $distance <= $validatedData['radius'];
        })->values();

        return response()->json(['data' => $nearbyAlarms], 200);
    }

    /**
     * Verificar si la ubicación actual activa alguna alarma
     */
    public function checkLocation(Request $request)
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
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180'
        ]);

        // Obtener todas las alarmas activas del usuario con ubicación
        $elementoIds = \App\Models\Elementos\Elemento::where('cuenta_id', $cuenta->id)
            ->where('estado', 'activo')
            ->pluck('id');

        $alarmas = Alarma::whereIn('elemento_id', $elementoIds)
            ->whereNotNull('ubicacion')
            ->get();

        $triggeredAlarms = $alarmas->filter(function ($alarma) use ($validatedData) {
            if (!$alarma->ubicacion || !isset($alarma->ubicacion['lat']) || !isset($alarma->ubicacion['lng'])) {
                return false;
            }

            $radius = $alarma->ubicacion['radius'] ?? 500; // Radio por defecto 500m

            $distance = $this->calculateDistance(
                $validatedData['lat'],
                $validatedData['lng'],
                $alarma->ubicacion['lat'],
                $alarma->ubicacion['lng']
            );

            return $distance <= $radius;
        })->values();

        return response()->json([
            'triggered_alarms' => $triggeredAlarms,
            'count' => $triggeredAlarms->count()
        ], 200);
    }

    /**
     * Calcular distancia entre dos puntos usando la fórmula de Haversine
     * Retorna la distancia en metros
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radio de la Tierra en metros

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($lat1) * cos($lat2) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

}
