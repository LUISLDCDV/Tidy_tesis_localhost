<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Elementos\Elemento;
use App\Models\UsuarioCuenta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ElementCountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Obtener conteo de elementos por usuario
     */
    public function getUserElementsCount()
    {
        try {
            $userId = Auth::id();
            $cacheKey = "user_elements_count_{$userId}";

            $counts = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($userId) {
                return $this->calculateElementCounts($userId);
            });

            $userAccount = UsuarioCuenta::where('user_id', $userId)->first();
            $isPremium = $userAccount ? $userAccount->premium : false;

            return response()->json([
                'success' => true,
                'data' => [
                    'counts' => $counts,
                    'limits' => $this->getElementLimits($isPremium),
                    'is_premium' => $isPremium,
                    'total_elements' => array_sum($counts),
                    'remaining_slots' => $this->calculateRemainingSlots($counts, $isPremium)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo conteo de elementos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas detalladas de elementos
     */
    public function getDetailedStats()
    {
        try {
            $userId = Auth::id();

            $stats = [
                'elements_by_type' => $this->getElementsByType($userId),
                'recent_activity' => $this->getRecentActivity($userId),
                'monthly_creation' => $this->getMonthlyCreation($userId),
                'usage_patterns' => $this->getUsagePatterns($userId),
                'space_optimization' => $this->getSpaceOptimization($userId)
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo estadísticas detalladas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si el usuario puede crear un elemento específico
     */
    public function canCreateElement(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:notas,alarmas,calendarios,objetivos'
        ]);

        try {
            $userId = Auth::id();
            $elementType = $request->type;

            $currentCount = $this->getElementCountByType($userId, $elementType);
            $userAccount = UsuarioCuenta::where('user_id', $userId)->first();
            $isPremium = $userAccount ? $userAccount->premium : false;

            $limits = $this->getElementLimits($isPremium);
            $canCreate = $isPremium || $currentCount < $limits[$elementType];

            return response()->json([
                'success' => true,
                'data' => [
                    'can_create' => $canCreate,
                    'current_count' => $currentCount,
                    'limit' => $isPremium ? null : $limits[$elementType],
                    'remaining' => $isPremium ? null : max(0, $limits[$elementType] - $currentCount),
                    'is_premium' => $isPremium,
                    'requires_premium' => !$canCreate && !$isPremium
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error verificando límites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener sugerencias de optimización
     */
    public function getOptimizationSuggestions()
    {
        try {
            $userId = Auth::id();
            $userAccount = UsuarioCuenta::where('user_id', $userId)->first();

            if (!$userAccount || $userAccount->premium) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'suggestions' => [],
                        'message' => 'Usuario premium - sin limitaciones'
                    ]
                ]);
            }

            $suggestions = $this->generateOptimizationSuggestions($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'suggestions' => $suggestions,
                    'total_suggestions' => count($suggestions),
                    'potential_savings' => $this->calculatePotentialSavings($suggestions)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo sugerencias',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpiar elementos obsoletos automáticamente
     */
    public function cleanupObsoleteElements(Request $request)
    {
        $request->validate([
            'types' => 'array',
            'types.*' => 'string|in:notas,alarmas,calendarios,objetivos',
            'older_than_days' => 'integer|min:30|max:365'
        ]);

        try {
            $userId = Auth::id();
            $types = $request->input('types', []);
            $olderThanDays = $request->input('older_than_days', 90);

            $cleanedElements = $this->performCleanup($userId, $types, $olderThanDays);

            // Limpiar cache después de la limpieza
            $this->clearUserCache($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'cleaned_elements' => $cleanedElements,
                    'total_cleaned' => array_sum($cleanedElements),
                    'message' => 'Limpieza completada exitosamente'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la limpieza automática',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Métodos privados auxiliares
     */
    private function calculateElementCounts($userId)
    {
        $elementos = Elemento::where('usuario_id', $userId)
            ->select('tipo', DB::raw('count(*) as count'))
            ->groupBy('tipo')
            ->pluck('count', 'tipo')
            ->toArray();

        return [
            'notas' => $elementos['nota'] ?? 0,
            'alarmas' => $elementos['alarma'] ?? 0,
            'calendarios' => $elementos['calendario'] ?? 0,
            'objetivos' => $elementos['objetivo'] ?? 0
        ];
    }

    private function getElementLimits($isPremium)
    {
        if ($isPremium) {
            return [
                'notas' => null,
                'alarmas' => null,
                'calendarios' => null,
                'objetivos' => null
            ];
        }

        return [
            'notas' => 50,
            'alarmas' => 20,
            'calendarios' => 5,
            'objetivos' => 10
        ];
    }

    private function calculateRemainingSlots($counts, $isPremium)
    {
        if ($isPremium) {
            return null; // Ilimitado
        }

        $limits = $this->getElementLimits(false);
        $totalUsed = array_sum($counts);
        $totalAvailable = array_sum($limits);

        return max(0, $totalAvailable - $totalUsed);
    }

    private function getElementCountByType($userId, $type)
    {
        $typeMapping = [
            'notas' => 'nota',
            'alarmas' => 'alarma',
            'calendarios' => 'calendario',
            'objetivos' => 'objetivo'
        ];

        $elementType = $typeMapping[$type] ?? $type;

        return Elemento::where('usuario_id', $userId)
            ->where('tipo', $elementType)
            ->count();
    }

    private function getElementsByType($userId)
    {
        return Elemento::where('usuario_id', $userId)
            ->select('tipo', 'nombre', 'created_at', 'updated_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('tipo')
            ->map(function ($elements) {
                return [
                    'count' => $elements->count(),
                    'recent' => $elements->take(5),
                    'oldest' => $elements->sortBy('created_at')->first(),
                    'newest' => $elements->sortByDesc('created_at')->first()
                ];
            });
    }

    private function getRecentActivity($userId)
    {
        return Elemento::where('usuario_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->select('tipo', DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('tipo', 'date')
            ->orderBy('date', 'desc')
            ->get();
    }

    private function getMonthlyCreation($userId)
    {
        return Elemento::where('usuario_id', $userId)
            ->where('created_at', '>=', now()->subMonths(12))
            ->select(
                'tipo',
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('count(*) as count')
            )
            ->groupBy('tipo', 'year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    private function getUsagePatterns($userId)
    {
        $totalElements = Elemento::where('usuario_id', $userId)->count();

        if ($totalElements === 0) {
            return [];
        }

        return [
            'most_used_type' => Elemento::where('usuario_id', $userId)
                ->select('tipo', DB::raw('count(*) as count'))
                ->groupBy('tipo')
                ->orderBy('count', 'desc')
                ->first(),
            'creation_frequency' => $this->getCreationFrequency($userId),
            'usage_distribution' => $this->getUsageDistribution($userId)
        ];
    }

    private function getSpaceOptimization($userId)
    {
        $userAccount = UsuarioCuenta::where('user_id', $userId)->first();

        if (!$userAccount || $userAccount->premium) {
            return null;
        }

        $counts = $this->calculateElementCounts($userId);
        $limits = $this->getElementLimits(false);

        $optimization = [];

        foreach ($counts as $type => $count) {
            $limit = $limits[$type];
            $usage = $limit > 0 ? ($count / $limit) * 100 : 0;

            $optimization[$type] = [
                'current' => $count,
                'limit' => $limit,
                'usage_percentage' => round($usage, 2),
                'recommendations' => $this->getTypeRecommendations($type, $usage, $userId)
            ];
        }

        return $optimization;
    }

    private function generateOptimizationSuggestions($userId)
    {
        $suggestions = [];

        // Sugerencias basadas en elementos antiguos
        $oldElements = Elemento::where('usuario_id', $userId)
            ->where('updated_at', '<', now()->subDays(60))
            ->get()
            ->groupBy('tipo');

        foreach ($oldElements as $type => $elements) {
            if ($elements->count() > 0) {
                $suggestions[] = [
                    'type' => 'cleanup',
                    'category' => $type,
                    'title' => "Elementos antiguos en {$type}",
                    'description' => "Tienes {$elements->count()} elementos sin actualizar en más de 60 días",
                    'action' => 'cleanup_old',
                    'potential_savings' => $elements->count(),
                    'priority' => $elements->count() > 10 ? 'high' : 'medium'
                ];
            }
        }

        // Sugerencias basadas en duplicados
        $duplicates = $this->findPotentialDuplicates($userId);
        if (!empty($duplicates)) {
            $suggestions[] = [
                'type' => 'duplicates',
                'category' => 'general',
                'title' => 'Posibles duplicados detectados',
                'description' => "Se encontraron elementos que podrían ser duplicados",
                'action' => 'review_duplicates',
                'potential_savings' => count($duplicates),
                'priority' => 'medium'
            ];
        }

        return $suggestions;
    }

    private function findPotentialDuplicates($userId)
    {
        return Elemento::where('usuario_id', $userId)
            ->select('nombre', 'tipo', DB::raw('count(*) as count'))
            ->groupBy('nombre', 'tipo')
            ->having('count', '>', 1)
            ->get();
    }

    private function getTypeRecommendations($type, $usage, $userId)
    {
        $recommendations = [];

        if ($usage > 90) {
            $recommendations[] = [
                'level' => 'urgent',
                'message' => "Límite casi alcanzado para {$type}",
                'action' => 'consider_premium'
            ];
        } elseif ($usage > 70) {
            $recommendations[] = [
                'level' => 'warning',
                'message' => "Uso elevado de {$type}",
                'action' => 'optimize_usage'
            ];
        }

        return $recommendations;
    }

    private function performCleanup($userId, $types, $olderThanDays)
    {
        $cleaned = [];
        $cutoffDate = now()->subDays($olderThanDays);

        foreach ($types as $type) {
            $typeMapping = [
                'notas' => 'nota',
                'alarmas' => 'alarma',
                'calendarios' => 'calendario',
                'objetivos' => 'objetivo'
            ];

            $elementType = $typeMapping[$type] ?? $type;

            $deletedCount = Elemento::where('usuario_id', $userId)
                ->where('tipo', $elementType)
                ->where('updated_at', '<', $cutoffDate)
                ->delete();

            $cleaned[$type] = $deletedCount;
        }

        return $cleaned;
    }

    private function clearUserCache($userId)
    {
        Cache::forget("user_elements_count_{$userId}");
    }

    private function getCreationFrequency($userId)
    {
        return Elemento::where('usuario_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->count() / 30; // Promedio diario
    }

    private function getUsageDistribution($userId)
    {
        return Elemento::where('usuario_id', $userId)
            ->select('tipo', DB::raw('count(*) as count'))
            ->groupBy('tipo')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->tipo => $item->count];
            });
    }

    private function calculatePotentialSavings($suggestions)
    {
        return array_sum(array_column($suggestions, 'potential_savings'));
    }
}