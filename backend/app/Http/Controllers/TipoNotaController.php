<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoNota;

class TipoNotaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Obtener todos los tipos de notas disponibles
     */
    public function index()
    {
        try {
            $user = auth()->user();
            $isPremium = $user && $user->is_premium;

            // Intentar obtener de la base de datos
            $query = TipoNota::orderBy('id');

            // Si no es premium, excluir los tipos premium
            if (!$isPremium) {
                $query->where('is_premium', false);
            }

            $tipos = $query->get(['id', 'nombre', 'descripcion', 'is_premium']);

            if ($tipos->isEmpty()) {
                // Si no hay tipos en la BD, devolver los tipos por defecto filtrados
                $tipos = $this->getTiposDefault($isPremium);
            }

            return response()->json([
                'success' => true,
                'data' => $tipos,
                'is_premium' => $isPremium
            ]);

        } catch (\Exception $e) {
            // Si hay error con la BD, devolver tipos por defecto
            $user = auth()->user();
            $isPremium = $user && $user->is_premium;

            return response()->json([
                'success' => true,
                'data' => $this->getTiposDefault($isPremium),
                'is_premium' => $isPremium,
                'source' => 'default'
            ]);
        }
    }

    /**
     * Verificar si un tipo de nota existe
     */
    public function verificarTipo($id)
    {
        try {
            $existe = TipoNota::existe($id);

            if (!$existe) {
                // Verificar en los tipos por defecto
                $existe = $id >= 1 && $id <= 16;
            }

            return response()->json([
                'success' => true,
                'exists' => $existe,
                'tipo_id' => $id
            ]);

        } catch (\Exception $e) {
            // Fallback: verificar en rango válido
            $existe = $id >= 1 && $id <= 16;

            return response()->json([
                'success' => true,
                'exists' => $existe,
                'tipo_id' => $id,
                'source' => 'fallback'
            ]);
        }
    }

    /**
     * Inicializar tipos de nota por defecto
     */
    public function inicializar()
    {
        try {
            TipoNota::seedTiposDefault();

            return response()->json([
                'success' => true,
                'message' => 'Tipos de nota inicializados correctamente',
                'data' => TipoNota::getTiposDisponibles()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error inicializando tipos de nota',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tipos por defecto sin acceso a BD
     */
    private function getTiposDefault($isPremium = false)
    {
        $tipos = collect([
            ['id' => 1, 'nombre' => 'Normal', 'descripcion' => 'Nota de texto simple', 'is_premium' => false],
            ['id' => 2, 'nombre' => 'Comida semana', 'descripcion' => 'Planificador de comidas semanal', 'is_premium' => false],
            ['id' => 3, 'nombre' => 'Claves', 'descripcion' => 'Nota con registro de claves', 'is_premium' => false],
            ['id' => 4, 'nombre' => 'Código', 'descripcion' => 'Nota para fragmentos de código', 'is_premium' => false],
            ['id' => 5, 'nombre' => 'Compras super', 'descripcion' => 'Lista de productos supermercado', 'is_premium' => false],
            ['id' => 6, 'nombre' => 'Gastos mensuales', 'descripcion' => 'Registro de pagos pendientes', 'is_premium' => false],
            ['id' => 7, 'nombre' => 'Juegos', 'descripcion' => 'Tabla de puntos juego', 'is_premium' => false],
            ['id' => 8, 'nombre' => 'Presupuesto', 'descripcion' => 'Gestión de presupuesto', 'is_premium' => false],
            ['id' => 9, 'nombre' => 'Timebox', 'descripcion' => 'Gestión de tiempo', 'is_premium' => false],
            ['id' => 10, 'nombre' => 'Recetas', 'descripcion' => 'Registros recetas comidas', 'is_premium' => false],
            ['id' => 11, 'nombre' => 'Medidas construcción', 'descripcion' => 'Guardar medidas de elemento', 'is_premium' => false],
            ['id' => 12, 'nombre' => 'Gustos y recomendaciones', 'descripcion' => 'Guardar recomendaciones', 'is_premium' => false],
            ['id' => 13, 'nombre' => 'Pedido comida grupa', 'descripcion' => 'Organizar pedidos comida grupal', 'is_premium' => false],
            ['id' => 14, 'nombre' => 'Nota viaje', 'descripcion' => 'Organizar recorrido viaje', 'is_premium' => true],
            ['id' => 15, 'nombre' => 'Dibujo Nota', 'descripcion' => 'Tablero un dibujo', 'is_premium' => true],
            ['id' => 16, 'nombre' => 'Diagrama Nota', 'descripcion' => 'Organizar ideas o procesos', 'is_premium' => true],
        ]);

        // Filtrar tipos premium si el usuario no es premium
        if (!$isPremium) {
            $tipos = $tipos->where('is_premium', false)->values();
        }

        return $tipos;
    }
}