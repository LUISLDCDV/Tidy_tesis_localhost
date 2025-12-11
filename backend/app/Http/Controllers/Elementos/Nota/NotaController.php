<?php

// namespace App\Http\Controllers\Elementos\Nota\NotaController;
namespace App\Http\Controllers\Elementos\Nota;
use  App\Models\Elementos\Nota;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use  App\Models\Elementos\Elemento;
use  App\Models\UsuarioCuenta;
use  App\Models\TipoNota;


class NotaController extends Controller
{

    // Muestra una lista de notas
    public function index()
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
        if (!$cuenta) {
            return response()->json(['data' => []], 200);
        }

        $elementoIds = Elemento::where('cuenta_id', $cuenta->id)->pluck('id');
        $query = Nota::whereIn('elemento_id', $elementoIds)->with('elemento');

        // Filtro por búsqueda
        if (request()->has('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('informacion', 'like', "%{$search}%");
            });
        }

        // Filtro por rango de fechas
        if (request()->has('fecha_inicio')) {
            $query->where('fecha', '>=', request('fecha_inicio'));
        }
        if (request()->has('fecha_fin')) {
            $query->where('fecha', '<=', request('fecha_fin'));
        }

        // Filtro por tags
        if (request()->has('tags')) {
            $tags = request('tags');
            $query->whereJsonContains('contenido->tags', $tags);
        }

        // Ordenamiento
        if (request()->has('sort_by')) {
            $sortBy = request('sort_by');
            $sortOrder = request('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);
        }

        $notas = $query->get();

        return response()->json(['data' => $notas], 200);
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
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
        if (!$cuenta) {
            return response()->json(['error' => 'Cuenta no encontrada'], 404);
        }

        $nota = Nota::with('elemento')->findOrFail($id);

        // Verificar que la nota pertenece al usuario
        if ($nota->elemento->cuenta_id !== $cuenta->id) {
            return response()->json(['error' => 'No autorizado'], 404);
        }

        return response()->json(['data' => $nota], 200);
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

    /**
     * Almacenar una nota nueva (API REST)
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
            'fecha' => 'nullable|date',
            'informacion' => 'nullable|string',
            'contenido' => 'nullable',
            'tipo_nota_id' => 'nullable|exists:tipos_notas,id',
            'clave' => 'nullable|string',
        ]);

        // Validar si el tipo de nota es premium
        $tipoNotaId = $validatedData['tipo_nota_id'] ?? 1;
        $tipoNota = TipoNota::find($tipoNotaId);

        if ($tipoNota && $tipoNota->is_premium && !$cuenta->is_premium) {
            return response()->json([
                'error' => 'Este tipo de nota requiere una cuenta premium',
                'tipo_nota_id' => $tipoNotaId
            ], 403);
        }

        // Crear elemento primero
        $elemento = Elemento::create([
            'tipo' => 'nota',
            'descripcion' => $validatedData['nombre'],
            'cuenta_id' => $cuenta->id,
            'estado' => 'activo',
        ]);

        // Crear la nota
        $nota = Nota::create([
            'elemento_id' => $elemento->id,
            'nombre' => $validatedData['nombre'],
            'fecha' => $validatedData['fecha'] ?? now(),
            'informacion' => $validatedData['informacion'] ?? '',
            'contenido' => is_array($request->contenido) ? $request->contenido : ['text' => $request->contenido ?? ''],
            'tipo_nota_id' => $validatedData['tipo_nota_id'] ?? 1,
            'clave' => $validatedData['clave'] ?? null,
        ]);

        // Recargar con relaciones
        $nota->load('elemento');

        return response()->json(['data' => $nota], 201);
    }

    /**
     * Actualizar una nota (API REST)
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

        $nota = Nota::findOrFail($id);

        // Verificar que la nota pertenece al usuario
        $elemento = $nota->elemento;
        if ($elemento->cuenta_id !== $cuenta->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validatedData = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'fecha' => 'sometimes|date',
            'informacion' => 'sometimes|string',
            'contenido' => 'sometimes',
            'tipo_nota_id' => 'sometimes|exists:tipos_notas,id',
            'clave' => 'sometimes|string',
        ]);

        $nota->update($validatedData);

        return response()->json(['data' => $nota], 200);
    }

    /**
     * Eliminar una nota (API REST)
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

        $nota = Nota::findOrFail($id);

        // Verificar que la nota pertenece al usuario
        $elemento = $nota->elemento;
        if ($elemento->cuenta_id !== $cuenta->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Soft delete de la nota y su elemento
        $nota->delete();
        $elemento->delete();

        return response()->json(['message' => 'Nota eliminada exitosamente'], 200);
    }

    /**
     * Duplicar una nota
     */
    public function duplicate($id)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
        if (!$cuenta) {
            return response()->json(['error' => 'Cuenta no encontrada'], 404);
        }

        $notaOriginal = Nota::with('elemento')->findOrFail($id);

        // Verificar que la nota pertenece al usuario
        if ($notaOriginal->elemento->cuenta_id !== $cuenta->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Crear nuevo elemento
        $nuevoElemento = Elemento::create([
            'tipo' => 'nota',
            'descripcion' => $notaOriginal->nombre . ' (Copy)',
            'cuenta_id' => $cuenta->id,
            'estado' => 'activo',
        ]);

        // Duplicar la nota
        $notaDuplicada = Nota::create([
            'elemento_id' => $nuevoElemento->id,
            'nombre' => $notaOriginal->nombre . ' (Copy)',
            'fecha' => now(),
            'informacion' => $notaOriginal->informacion,
            'contenido' => $notaOriginal->contenido,
            'tipo_nota_id' => $notaOriginal->tipo_nota_id,
            'clave' => $notaOriginal->clave,
        ]);

        $notaDuplicada->load('elemento');

        return response()->json(['data' => $notaDuplicada], 201);
    }

    /**
     * Archivar/desarchivar una nota
     */
    public function archive($id)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
        if (!$cuenta) {
            return response()->json(['error' => 'Cuenta no encontrada'], 404);
        }

        $nota = Nota::with('elemento')->findOrFail($id);

        // Verificar que la nota pertenece al usuario
        if ($nota->elemento->cuenta_id !== $cuenta->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Cambiar estado del elemento
        $nuevoEstado = $nota->elemento->estado === 'archivado' ? 'activo' : 'archivado';
        $nota->elemento->update(['estado' => $nuevoEstado]);

        return response()->json([
            'message' => $nuevoEstado === 'archivado' ? 'Nota archivada' : 'Nota desarchivada',
            'estado' => $nuevoEstado
        ], 200);
    }

    /**
     * Desarchivar una nota
     */
    public function unarchive($id)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
        if (!$cuenta) {
            return response()->json(['error' => 'Cuenta no encontrada'], 404);
        }

        $nota = Nota::with('elemento')->findOrFail($id);

        // Verificar que la nota pertenece al usuario
        if ($nota->elemento->cuenta_id !== $cuenta->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Cambiar estado del elemento a activo
        $nota->elemento->update(['estado' => 'activo']);

        return response()->json([
            'message' => 'Nota desarchivada',
            'estado' => 'activo'
        ], 200);
    }

    /**
     * Eliminar múltiples notas
     */
    public function bulkDelete(Request $request)
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
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:notas,id'
        ]);

        $deletedCount = 0;
        foreach ($validatedData['ids'] as $id) {
            $nota = Nota::with('elemento')->find($id);

            if ($nota && $nota->elemento->cuenta_id === $cuenta->id) {
                $nota->delete();
                $nota->elemento->delete();
                $deletedCount++;
            }
        }

        return response()->json([
            'message' => "Se eliminaron {$deletedCount} notas",
            'deleted_count' => $deletedCount
        ], 200);
    }

}
