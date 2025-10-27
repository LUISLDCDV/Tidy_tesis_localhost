<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UsuarioCuenta;
use App\Models\Elementos\Elemento;
use App\Models\Nivel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RankingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Obtener ranking de usuarios
     */
    public function index(Request $request)
    {
        try {
            $type = $request->get('type', 'experience');
            $limit = $request->get('limit', 50);

            $ranking = $this->getRankingByType($type, $limit);
            $stats = $this->getGeneralStats();
            $levelDistribution = $this->getLevelDistribution();

            return response()->json([
                'success' => true,
                'data' => [
                    'ranking' => $ranking,
                    'stats' => $stats,
                    'level_distribution' => $levelDistribution,
                    'type' => $type
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo ranking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ranking según el tipo
     */
    private function getRankingByType($type, $limit)
    {
        $cacheKey = "ranking_{$type}_{$limit}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($type, $limit) {
            switch ($type) {
                case 'experience':
                    return $this->getExperienceRanking($limit);

                case 'elements':
                    return $this->getElementsRanking($limit);

                case 'activity':
                    return $this->getActivityRanking($limit);

                default:
                    return $this->getExperienceRanking($limit);
            }
        });
    }

    /**
     * Ranking por experiencia
     */
    private function getExperienceRanking($limit)
    {
        return UsuarioCuenta::with([
            'user:id,name,last_name,email',
            'nivel:id,nombre,color'
        ])
        ->orderBy('experiencia_total', 'desc')
        ->limit($limit)
        ->get()
        ->map(function ($cuenta, $index) {
            return [
                'id' => $cuenta->user_id,
                'name' => $cuenta->user->name . ' ' . $cuenta->user->last_name,
                'email' => $cuenta->user->email,
                'experience' => $cuenta->experiencia_total,
                'level' => $cuenta->nivel_id,
                'level_name' => $cuenta->nivel->nombre ?? 'Sin nivel',
                'level_color' => $cuenta->nivel->color ?? '#grey',
                'premium' => $cuenta->premium,
                'position' => $index + 1,
                'last_activity' => $cuenta->fecha_ultimo_acceso?->diffForHumans()
            ];
        });
    }

    /**
     * Ranking por elementos creados
     */
    private function getElementsRanking($limit)
    {
        return User::withCount('elementos')
            ->with('usuarioCuenta.nivel:id,nombre,color')
            ->orderBy('elementos_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($user, $index) {
                return [
                    'id' => $user->id,
                    'name' => $user->name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'elements_count' => $user->elementos_count,
                    'level' => $user->usuarioCuenta->nivel_id ?? 1,
                    'level_name' => $user->usuarioCuenta->nivel->nombre ?? 'Sin nivel',
                    'level_color' => $user->usuarioCuenta->nivel->color ?? '#grey',
                    'premium' => $user->usuarioCuenta->premium ?? false,
                    'position' => $index + 1,
                    'last_activity' => $user->usuarioCuenta->fecha_ultimo_acceso?->diffForHumans()
                ];
            });
    }

    /**
     * Ranking por actividad
     */
    private function getActivityRanking($limit)
    {
        // Calcular score de actividad basado en múltiples factores
        $users = UsuarioCuenta::with([
            'user:id,name,last_name,email',
            'nivel:id,nombre,color'
        ])
        ->get()
        ->map(function ($cuenta) {
            $activityScore = $this->calculateActivityScore($cuenta);

            return [
                'id' => $cuenta->user_id,
                'name' => $cuenta->user->name . ' ' . $cuenta->user->last_name,
                'email' => $cuenta->user->email,
                'activity_score' => $activityScore,
                'level' => $cuenta->nivel_id,
                'level_name' => $cuenta->nivel->nombre ?? 'Sin nivel',
                'level_color' => $cuenta->nivel->color ?? '#grey',
                'premium' => $cuenta->premium,
                'last_activity' => $cuenta->fecha_ultimo_acceso?->diffForHumans()
            ];
        })
        ->sortByDesc('activity_score')
        ->values()
        ->take($limit)
        ->map(function ($user, $index) {
            $user['position'] = $index + 1;
            return $user;
        });

        return $users;
    }

    /**
     * Calcular score de actividad
     */
    private function calculateActivityScore($cuenta)
    {
        $score = 0;

        // Puntos por experiencia (1 punto por cada 10 XP)
        $score += ($cuenta->experiencia_total / 10);

        // Puntos por actividad reciente
        if ($cuenta->fecha_ultimo_acceso) {
            $daysAgo = $cuenta->fecha_ultimo_acceso->diffInDays(now());
            $activityBonus = max(0, 100 - ($daysAgo * 5)); // Menos puntos por inactividad
            $score += $activityBonus;
        }

        // Puntos por elementos creados
        $elementCount = Elemento::where('usuario_id', $cuenta->user_id)->count();
        $score += ($elementCount * 5);

        // Bonus por ser premium
        if ($cuenta->premium) {
            $score += 50;
        }

        // Puntos por longevidad en la plataforma
        $accountAge = $cuenta->created_at->diffInDays(now());
        $score += min($accountAge, 365); // Máximo 365 puntos por antigüedad

        return round($score);
    }

    /**
     * Estadísticas generales
     */
    private function getGeneralStats()
    {
        $totalUsers = UsuarioCuenta::count();
        $premiumUsers = UsuarioCuenta::where('premium', true)->count();
        $activeUsers = UsuarioCuenta::where('fecha_ultimo_acceso', '>=', Carbon::now()->subDays(7))->count();
        $avgExperience = UsuarioCuenta::avg('experiencia_total');
        $avgLevel = UsuarioCuenta::avg('nivel_id');

        return [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'premiumPercentage' => $totalUsers > 0 ? round(($premiumUsers / $totalUsers) * 100, 2) : 0,
            'averageExperience' => round($avgExperience, 0),
            'averageLevel' => round($avgLevel, 1)
        ];
    }

    /**
     * Distribución por niveles
     */
    private function getLevelDistribution()
    {
        return Nivel::withCount('usuariosCuenta')
            ->orderBy('orden')
            ->get()
            ->map(function ($nivel) {
                $totalUsers = UsuarioCuenta::count();
                $percentage = $totalUsers > 0 ? round(($nivel->usuarios_cuenta_count / $totalUsers) * 100, 2) : 0;

                return [
                    'id' => $nivel->id,
                    'name' => $nivel->nombre,
                    'color' => $nivel->color,
                    'icon' => $nivel->icono,
                    'count' => $nivel->usuarios_cuenta_count,
                    'percentage' => $percentage,
                    'order' => $nivel->orden
                ];
            });
    }

    /**
     * Obtener estadísticas detalladas de un usuario
     */
    public function userStats($userId)
    {
        try {
            $user = User::with(['usuarioCuenta.nivel', 'elementos'])->findOrFail($userId);

            $stats = [
                'user_info' => [
                    'id' => $user->id,
                    'name' => $user->name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'last_login' => $user->last_login_at,
                ],
                'level_info' => [
                    'current_level' => $user->usuarioCuenta->nivel_id,
                    'level_name' => $user->usuarioCuenta->nivel->nombre ?? 'Sin nivel',
                    'experience' => $user->usuarioCuenta->experiencia_total,
                    'premium' => $user->usuarioCuenta->premium,
                ],
                'activity_stats' => [
                    'total_elements' => $user->elementos->count(),
                    'elements_by_type' => $user->elementos->groupBy('tipo')->map->count(),
                    'activity_score' => $this->calculateActivityScore($user->usuarioCuenta),
                    'last_activity' => $user->usuarioCuenta->fecha_ultimo_acceso,
                ],
                'rankings' => [
                    'experience_position' => $this->getUserPosition($userId, 'experience'),
                    'elements_position' => $this->getUserPosition($userId, 'elements'),
                    'activity_position' => $this->getUserPosition($userId, 'activity'),
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo estadísticas del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener posición de un usuario en un ranking específico
     */
    private function getUserPosition($userId, $type)
    {
        switch ($type) {
            case 'experience':
                $userExp = UsuarioCuenta::where('user_id', $userId)->value('experiencia_total');
                return UsuarioCuenta::where('experiencia_total', '>', $userExp)->count() + 1;

            case 'elements':
                $userElements = Elemento::where('usuario_id', $userId)->count();
                return User::withCount('elementos')
                    ->having('elementos_count', '>', $userElements)
                    ->count() + 1;

            case 'activity':
                $userScore = $this->calculateActivityScore(
                    UsuarioCuenta::where('user_id', $userId)->first()
                );

                // Calcular posición comparando con otros usuarios
                $betterUsers = UsuarioCuenta::get()
                    ->filter(function ($cuenta) use ($userScore) {
                        return $this->calculateActivityScore($cuenta) > $userScore;
                    })
                    ->count();

                return $betterUsers + 1;

            default:
                return null;
        }
    }

    /**
     * Obtener historial de cambios de nivel
     */
    public function levelHistory(Request $request)
    {
        try {
            $limit = $request->get('limit', 20);

            // Esta funcionalidad requeriría una tabla de historial de niveles
            // Por ahora, simulamos con los datos actuales
            $recentLevelChanges = UsuarioCuenta::with(['user:id,name,last_name', 'nivel:id,nombre,color'])
                ->orderBy('updated_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($cuenta) {
                    return [
                        'user_id' => $cuenta->user_id,
                        'user_name' => $cuenta->user->name . ' ' . $cuenta->user->last_name,
                        'current_level' => $cuenta->nivel_id,
                        'level_name' => $cuenta->nivel->nombre,
                        'level_color' => $cuenta->nivel->color,
                        'experience' => $cuenta->experiencia_total,
                        'updated_at' => $cuenta->updated_at->diffForHumans()
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $recentLevelChanges
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo historial de niveles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpiar cache de rankings
     */
    public function clearCache()
    {
        try {
            $patterns = ['ranking_*'];

            foreach ($patterns as $pattern) {
                Cache::flush(); // En producción, usar un método más específico
            }

            return response()->json([
                'success' => true,
                'message' => 'Cache de rankings limpiado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error limpiando cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}