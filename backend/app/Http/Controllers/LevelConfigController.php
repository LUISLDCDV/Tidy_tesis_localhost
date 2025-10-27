<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nivel;
use App\Models\UsuarioCuenta;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class LevelConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Obtener configuración de niveles
     */
    public function index()
    {
        try {
            $niveles = Nivel::orderBy('orden')->get();
            $stats = $this->getLevelStats();

            return response()->json([
                'success' => true,
                'data' => [
                    'niveles' => $niveles,
                    'estadisticas' => $stats
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo configuración de niveles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo nivel
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'required|string|max:500',
            'experiencia_requerida' => 'required|integer|min:0',
            'experiencia_maxima' => 'required|integer|min:0',
            'color' => 'required|string|max:7',
            'icono' => 'required|string|max:50',
            'orden' => 'required|integer|min:1',
            'activo' => 'boolean'
        ]);

        try {
            $nivel = Nivel::create($request->all());

            // Limpiar cache de niveles
            Cache::forget('niveles_config');

            return response()->json([
                'success' => true,
                'data' => $nivel,
                'message' => 'Nivel creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creando nivel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar nivel específico
     */
    public function show($id)
    {
        try {
            $nivel = Nivel::findOrFail($id);
            $usuariosEnNivel = UsuarioCuenta::where('nivel_id', $id)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'nivel' => $nivel,
                    'usuarios_en_nivel' => $usuariosEnNivel
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo nivel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar nivel
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'required|string|max:500',
            'experiencia_requerida' => 'required|integer|min:0',
            'experiencia_maxima' => 'required|integer|min:0',
            'color' => 'required|string|max:7',
            'icono' => 'required|string|max:50',
            'orden' => 'required|integer|min:1',
            'activo' => 'boolean'
        ]);

        try {
            $nivel = Nivel::findOrFail($id);
            $nivel->update($request->all());

            // Limpiar cache
            Cache::forget('niveles_config');

            return response()->json([
                'success' => true,
                'data' => $nivel,
                'message' => 'Nivel actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error actualizando nivel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar nivel
     */
    public function destroy($id)
    {
        try {
            $nivel = Nivel::findOrFail($id);

            // Verificar si hay usuarios en este nivel
            $usuariosEnNivel = UsuarioCuenta::where('nivel_id', $id)->count();

            if ($usuariosEnNivel > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede eliminar el nivel. Hay {$usuariosEnNivel} usuarios asignados a este nivel."
                ], 422);
            }

            $nivel->delete();
            Cache::forget('niveles_config');

            return response()->json([
                'success' => true,
                'message' => 'Nivel eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error eliminando nivel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalcular niveles de todos los usuarios
     */
    public function recalcularNiveles()
    {
        try {
            $niveles = Nivel::where('activo', true)->orderBy('orden')->get();
            $usuariosActualizados = 0;

            $usuarios = UsuarioCuenta::with('user')->get();

            foreach ($usuarios as $usuario) {
                $nivelAnterior = $usuario->nivel_id;
                $experiencia = $usuario->experiencia_total;

                // Encontrar el nivel correspondiente
                $nuevoNivel = $this->determinarNivel($experiencia, $niveles);

                if ($nuevoNivel && $nuevoNivel->id !== $nivelAnterior) {
                    $usuario->update(['nivel_id' => $nuevoNivel->id]);
                    $usuariosActualizados++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Niveles recalculados exitosamente. {$usuariosActualizados} usuarios actualizados.",
                'usuarios_actualizados' => $usuariosActualizados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error recalculando niveles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar configuración de niveles
     */
    public function exportarConfiguracion()
    {
        try {
            $niveles = Nivel::orderBy('orden')->get();
            $configuracion = [
                'version' => '1.0',
                'exported_at' => now()->toISOString(),
                'niveles' => $niveles->toArray()
            ];

            return response()->json([
                'success' => true,
                'data' => $configuracion,
                'filename' => 'niveles_config_' . now()->format('Y-m-d_H-i-s') . '.json'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exportando configuración',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Importar configuración de niveles
     */
    public function importarConfiguracion(Request $request)
    {
        $request->validate([
            'configuracion' => 'required|array',
            'configuracion.niveles' => 'required|array|min:1',
            'sobrescribir' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $sobrescribir = $request->input('sobrescribir', false);
            $nivelesImportados = $request->input('configuracion.niveles');

            if ($sobrescribir) {
                // Verificar que no hay usuarios asignados antes de eliminar
                $usuariosConNiveles = UsuarioCuenta::whereNotNull('nivel_id')->count();
                if ($usuariosConNiveles > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede sobrescribir. Hay usuarios asignados a niveles existentes.'
                    ], 422);
                }

                Nivel::truncate();
            }

            $nivelesCreados = 0;

            foreach ($nivelesImportados as $nivelData) {
                $nivel = Nivel::updateOrCreate(
                    ['orden' => $nivelData['orden']],
                    [
                        'nombre' => $nivelData['nombre'],
                        'descripcion' => $nivelData['descripcion'],
                        'experiencia_requerida' => $nivelData['experiencia_requerida'],
                        'experiencia_maxima' => $nivelData['experiencia_maxima'],
                        'color' => $nivelData['color'],
                        'icono' => $nivelData['icono'],
                        'activo' => $nivelData['activo'] ?? true
                    ]
                );
                $nivelesCreados++;
            }

            DB::commit();
            Cache::forget('niveles_config');

            return response()->json([
                'success' => true,
                'message' => "Configuración importada exitosamente. {$nivelesCreados} niveles procesados.",
                'niveles_procesados' => $nivelesCreados
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error importando configuración',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de distribución de niveles
     */
    private function getLevelStats()
    {
        $stats = UsuarioCuenta::select('nivel_id', DB::raw('count(*) as usuarios'))
            ->groupBy('nivel_id')
            ->with('nivel:id,nombre,color')
            ->get();

        $totalUsuarios = UsuarioCuenta::count();

        return [
            'distribucion_niveles' => $stats->map(function ($stat) use ($totalUsuarios) {
                return [
                    'nivel_id' => $stat->nivel_id,
                    'nivel_nombre' => $stat->nivel->nombre ?? 'Sin nivel',
                    'color' => $stat->nivel->color ?? '#gray',
                    'usuarios' => $stat->usuarios,
                    'porcentaje' => $totalUsuarios > 0 ? round(($stat->usuarios / $totalUsuarios) * 100, 2) : 0
                ];
            }),
            'total_usuarios' => $totalUsuarios,
            'usuarios_sin_nivel' => UsuarioCuenta::whereNull('nivel_id')->count(),
            'nivel_mas_popular' => $stats->sortByDesc('usuarios')->first()?->nivel->nombre ?? 'N/A'
        ];
    }

    /**
     * Determinar el nivel correcto según la experiencia
     */
    private function determinarNivel($experiencia, $niveles)
    {
        foreach ($niveles as $nivel) {
            if ($experiencia >= $nivel->experiencia_requerida &&
                $experiencia <= $nivel->experiencia_maxima) {
                return $nivel;
            }
        }

        // Si no encuentra nivel exacto, devolver el primer nivel
        return $niveles->first();
    }

    /**
     * Previsualizar cambios de recálculo
     */
    public function previsualizarRecalculo()
    {
        try {
            $niveles = Nivel::where('activo', true)->orderBy('orden')->get();
            $cambios = [];

            $usuarios = UsuarioCuenta::with(['user:id,name,last_name', 'nivel:id,nombre'])
                ->limit(100) // Limitar para performance
                ->get();

            foreach ($usuarios as $usuario) {
                $nivelActual = $usuario->nivel;
                $experiencia = $usuario->experiencia_total;
                $nuevoNivel = $this->determinarNivel($experiencia, $niveles);

                if ($nuevoNivel && $nuevoNivel->id !== $usuario->nivel_id) {
                    $cambios[] = [
                        'usuario_id' => $usuario->user_id,
                        'usuario_nombre' => $usuario->user->name . ' ' . $usuario->user->last_name,
                        'experiencia' => $experiencia,
                        'nivel_actual' => $nivelActual->nombre ?? 'Sin nivel',
                        'nivel_nuevo' => $nuevoNivel->nombre,
                        'cambio_tipo' => $nuevoNivel->orden > ($nivelActual->orden ?? 0) ? 'subida' : 'bajada'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'cambios_previstos' => $cambios,
                    'total_cambios' => count($cambios),
                    'muestra_limitada' => count($usuarios) >= 100
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error previsualizando recálculo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}