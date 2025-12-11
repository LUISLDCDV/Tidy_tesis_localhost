<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UsuarioCuenta;
use App\Models\Elementos\Elemento;
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
            'user:id,name,last_name,email,last_login_at'
        ])
        ->orderBy('total_xp', 'desc')
        ->limit($limit)
        ->get()
        ->filter(function ($cuenta) {
            return $cuenta->user && $cuenta->user->name;
        })
        ->map(function ($cuenta, $index) {
            return [
                'id' => $cuenta->user_id,
                'name' => ($cuenta->user->name ?? '') . ' ' . ($cuenta->user->last_name ?? ''),
                'email' => $cuenta->user->email ?? '',
                'experience' => $cuenta->total_xp ?? 0,
                'level' => $cuenta->current_level ?? 1,
                'level_name' => 'Nivel ' . ($cuenta->current_level ?? 1),
                'level_color' => '#4CAF50',
                'premium' => $cuenta->is_premium ?? false,
                'position' => $index + 1,
                'last_activity' => $cuenta->user->last_login_at?->diffForHumans() ?? 'Nunca'
            ];
        })
        ->values();
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
            ->filter(function ($user) {
                return $user && $user->name;
            })
            ->map(function ($user, $index) {
                return [
                    'id' => $user->id,
                    'name' => ($user->name ?? '') . ' ' . ($user->last_name ?? ''),
                    'email' => $user->email ?? '',
                    'elements_count' => $user->elementos_count ?? 0,
                    'level' => $user->usuarioCuenta->current_level ?? 1,
                    'level_name' => 'Nivel ' . ($user->usuarioCuenta->current_level ?? 1),
                    'level_color' => '#4CAF50',
                    'premium' => $user->usuarioCuenta->is_premium ?? false,
                    'position' => $index + 1,
                    'last_activity' => $user->last_login_at?->diffForHumans() ?? 'Nunca'
                ];
            })
            ->values();
    }

    /**
     * Ranking por actividad
     */
    private function getActivityRanking($limit)
    {
        // Calcular score de actividad basado en múltiples factores
        $users = UsuarioCuenta::with([
            'user:id,name,last_name,email,last_login_at'
        ])
        ->get()
        ->filter(function ($cuenta) {
            return $cuenta->user && $cuenta->user->name;
        })
        ->map(function ($cuenta) {
            $activityScore = $this->calculateActivityScore($cuenta);

            return [
                'id' => $cuenta->user_id,
                'name' => ($cuenta->user->name ?? '') . ' ' . ($cuenta->user->last_name ?? ''),
                'email' => $cuenta->user->email ?? '',
                'activity_score' => $activityScore,
                'level' => $cuenta->current_level ?? 1,
                'level_name' => 'Nivel ' . ($cuenta->current_level ?? 1),
                'level_color' => '#4CAF50',
                'premium' => $cuenta->is_premium ?? false,
                'last_activity' => $cuenta->user->last_login_at?->diffForHumans() ?? 'Nunca'
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
        $score += ($cuenta->total_xp / 10);

        // Puntos por actividad reciente
        if ($cuenta->user && $cuenta->user->last_login_at) {
            $daysAgo = $cuenta->user->last_login_at->diffInDays(now());
            $activityBonus = max(0, 100 - ($daysAgo * 5)); // Menos puntos por inactividad
            $score += $activityBonus;
        }

        // Puntos por elementos creados
        $elementCount = Elemento::where('cuenta_id', $cuenta->id)->count();
        $score += ($elementCount * 5);

        // Bonus por ser premium
        if ($cuenta->is_premium) {
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
        $premiumUsers = UsuarioCuenta::where('is_premium', true)->count();
        $activeUsers = User::where('last_login_at', '>=', Carbon::now()->subDays(7))->count();
        $avgExperience = UsuarioCuenta::avg('total_xp');
        $avgLevel = UsuarioCuenta::avg('current_level');

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
        $totalUsers = UsuarioCuenta::count();

        return UsuarioCuenta::select('current_level', DB::raw('count(*) as count'))
            ->groupBy('current_level')
            ->orderBy('current_level')
            ->get()
            ->map(function ($item) use ($totalUsers) {
                $percentage = $totalUsers > 0 ? round(($item->count / $totalUsers) * 100, 2) : 0;

                return [
                    'id' => $item->current_level,
                    'name' => 'Nivel ' . $item->current_level,
                    'color' => '#4CAF50',
                    'icon' => 'fa-trophy',
                    'count' => $item->count,
                    'percentage' => $percentage,
                    'order' => $item->current_level
                ];
            });
    }

    /**
     * Obtener estadísticas detalladas de un usuario
     */
    public function userStats($userId)
    {
        try {
            $user = User::with(['usuarioCuenta', 'elementos'])->findOrFail($userId);

            $stats = [
                'user_info' => [
                    'id' => $user->id,
                    'name' => ($user->name ?? '') . ' ' . ($user->last_name ?? ''),
                    'email' => $user->email ?? '',
                    'created_at' => $user->created_at,
                    'last_login' => $user->last_login_at,
                ],
                'level_info' => [
                    'current_level' => $user->usuarioCuenta->current_level ?? 1,
                    'level_name' => 'Nivel ' . ($user->usuarioCuenta->current_level ?? 1),
                    'experience' => $user->usuarioCuenta->total_xp ?? 0,
                    'premium' => $user->usuarioCuenta->is_premium ?? false,
                ],
                'activity_stats' => [
                    'total_elements' => $user->elementos->count(),
                    'elements_by_type' => $user->elementos->groupBy('tipo')->map->count(),
                    'activity_score' => $this->calculateActivityScore($user->usuarioCuenta),
                    'last_activity' => $user->last_login_at,
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
                $userExp = UsuarioCuenta::where('user_id', $userId)->value('total_xp');
                return UsuarioCuenta::where('total_xp', '>', $userExp)->count() + 1;

            case 'elements':
                $user = User::withCount('elementos')->find($userId);
                if (!$user) return 0;

                $userElements = $user->elementos_count;
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
            $recentLevelChanges = UsuarioCuenta::with(['user:id,name,last_name'])
                ->orderBy('updated_at', 'desc')
                ->limit($limit)
                ->get()
                ->filter(function ($cuenta) {
                    return $cuenta->user && $cuenta->user->name;
                })
                ->map(function ($cuenta) {
                    return [
                        'user_id' => $cuenta->user_id,
                        'user_name' => ($cuenta->user->name ?? '') . ' ' . ($cuenta->user->last_name ?? ''),
                        'current_level' => $cuenta->current_level ?? 1,
                        'level_name' => 'Nivel ' . ($cuenta->current_level ?? 1),
                        'level_color' => '#4CAF50',
                        'experience' => $cuenta->total_xp ?? 0,
                        'updated_at' => $cuenta->updated_at ? $cuenta->updated_at->diffForHumans() : 'Desconocido'
                    ];
                })
                ->values();

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