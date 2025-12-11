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
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Dashboard principal del administrador
     */
    public function index()
    {
        try {
            Log::info('📊 Iniciando carga de dashboard');

            $stats = $this->getSystemStats();
            Log::info('✅ System stats cargado');

            $userStats = $this->getUserStats();
            Log::info('✅ User stats cargado');

            $elementStats = $this->getElementStats();
            Log::info('✅ Element stats cargado');

            $recentActivity = $this->getRecentActivity();
            Log::info('✅ Recent activity cargado');

            $topUsers = $this->getTopUsers();
            Log::info('✅ Top users cargado');

            return response()->json([
                'success' => true,
                'data' => [
                    'system_stats' => $stats,
                    'user_stats' => $userStats,
                    'element_stats' => $elementStats,
                    'recent_activity' => $recentActivity,
                    'top_users' => $topUsers,
                    'generated_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error en dashboard: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo datos del dashboard',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Estadísticas del sistema
     */
    private function getSystemStats()
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'total_users' => User::count(),
            'active_users_today' => User::whereDate('last_login_at', $today)->count(),
            'active_users_week' => User::where('last_login_at', '>=', $thisWeek)->count(),
            'active_users_month' => User::where('last_login_at', '>=', $thisMonth)->count(),
            'new_users_today' => User::whereDate('created_at', $today)->count(),
            'new_users_week' => User::where('created_at', '>=', $thisWeek)->count(),
            'new_users_month' => User::where('created_at', '>=', $thisMonth)->count(),
            'premium_users' => UsuarioCuenta::where('is_premium', true)->count(),
            'premium_percentage' => round((UsuarioCuenta::where('is_premium', true)->count() / max(UsuarioCuenta::count(), 1)) * 100, 2),
        ];
    }

    /**
     * Estadísticas de usuarios
     */
    private function getUserStats()
    {
        $levelStats = UsuarioCuenta::select('current_level', DB::raw('count(*) as count'))
            ->groupBy('current_level')
            ->orderBy('current_level')
            ->get()
            ->mapWithKeys(function ($item) {
                return ['nivel_' . $item->current_level => $item->count];
            });

        $registrationTrend = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $avgSessionsPerUser = UsuarioCuenta::avg(DB::raw('DATEDIFF(updated_at, created_at)'));

        return [
            'level_distribution' => $levelStats,
            'registration_trend' => $registrationTrend,
            'avg_sessions_per_user' => round($avgSessionsPerUser, 2),
            'user_retention_week' => $this->calculateRetention(7),
            'user_retention_month' => $this->calculateRetention(30),
        ];
    }

    /**
     * Estadísticas de elementos
     */
    private function getElementStats()
    {
        $elementsByType = Elemento::select('tipo', DB::raw('count(*) as count'))
            ->groupBy('tipo')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->tipo => $item->count];
            });

        $elementsToday = Elemento::whereDate('created_at', Carbon::today())->count();
        $elementsThisWeek = Elemento::where('created_at', '>=', Carbon::now()->startOfWeek())->count();
        $elementsThisMonth = Elemento::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        $elementsTrend = Elemento::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'total_elements' => Elemento::count(),
            'elements_by_type' => $elementsByType,
            'elements_today' => $elementsToday,
            'elements_this_week' => $elementsThisWeek,
            'elements_this_month' => $elementsThisMonth,
            'elements_trend' => $elementsTrend,
            'avg_elements_per_user' => round(Elemento::count() / max(User::count(), 1), 2),
        ];
    }

    /**
     * Actividad reciente
     */
    private function getRecentActivity()
    {
        $recentUsers = User::with(['usuarioCuenta', 'roles'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->filter(function ($user) {
                return $user && $user->name;
            })
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => ($user->name ?? '') . ' ' . ($user->last_name ?? ''),
                    'email' => $user->email ?? '',
                    'level' => $user->usuarioCuenta->current_level ?? 1,
                    'premium' => $user->usuarioCuenta->is_premium ?? false,
                    'premium_expires' => $user->usuarioCuenta && $user->usuarioCuenta->premium_expires_at
                        ? $user->usuarioCuenta->premium_expires_at->format('d/m/Y')
                        : null,
                    'total_xp' => $user->usuarioCuenta->total_xp ?? 0,
                    'is_admin' => $user->roles && $user->roles->contains('name', 'admin'),
                    'created_at' => $user->created_at ? $user->created_at->diffForHumans() : 'Desconocido',
                ];
            })
            ->values();

        $recentElements = Elemento::orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($element) {
                return [
                    'id' => $element->id,
                    'name' => $element->tipo ?? 'Sin tipo',
                    'type' => $element->tipo,
                    'user' => 'Sistema',
                    'created_at' => $element->created_at->diffForHumans(),
                ];
            });

        return [
            'recent_users' => $recentUsers,
            'recent_elements' => $recentElements,
        ];
    }

    /**
     * Top usuarios por actividad
     */
    private function getTopUsers()
    {
        $topByExperience = UsuarioCuenta::with('user:id,name,last_name,email')
            ->orderBy('total_xp', 'desc')
            ->limit(10)
            ->get()
            ->filter(function ($cuenta) {
                return $cuenta->user && $cuenta->user->name;
            })
            ->map(function ($cuenta) {
                return [
                    'id' => $cuenta->user_id,
                    'name' => ($cuenta->user->name ?? '') . ' ' . ($cuenta->user->last_name ?? ''),
                    'email' => $cuenta->user->email ?? '',
                    'experience' => $cuenta->total_xp ?? 0,
                    'level' => $cuenta->current_level ?? 1,
                    'premium' => $cuenta->is_premium ?? false,
                ];
            })
            ->values();

        $topByElements = User::withCount('elementos')
            ->orderBy('elementos_count', 'desc')
            ->limit(10)
            ->get()
            ->filter(function ($user) {
                return $user && $user->name;
            })
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => ($user->name ?? '') . ' ' . ($user->last_name ?? ''),
                    'email' => $user->email ?? '',
                    'elements_count' => $user->elementos_count ?? 0,
                ];
            })
            ->values();

        return [
            'top_by_experience' => $topByExperience,
            'top_by_elements' => $topByElements,
        ];
    }

    /**
     * Calcular retención de usuarios
     */
    private function calculateRetention($days)
    {
        $startDate = Carbon::now()->subDays($days);
        $newUsers = User::where('created_at', '>=', $startDate)->count();

        if ($newUsers === 0) {
            return 0;
        }

        // Usar last_login_at de la tabla users en lugar de fecha_ultimo_acceso
        $activeUsers = User::where('created_at', '>=', $startDate)
            ->where('last_login_at', '>=', $startDate)
            ->count();

        return round(($activeUsers / $newUsers) * 100, 2);
    }

    /**
     * Gestión de usuarios
     */
    public function users(Request $request)
    {
        try {
            // Solo mostrar usuarios que NO son admin
            $query = User::with(['usuarioCuenta'])
                ->whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'admin');
                });

            Log::info("📋 Cargando gestión de usuarios - solo usuarios no admin");

            // Filtros
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->filled('level')) {
                $query->whereHas('usuarioCuenta', function ($q) use ($request) {
                    $q->where('current_level', $request->level);
                });
            }

            if ($request->filled('premium')) {
                $query->whereHas('usuarioCuenta', function ($q) use ($request) {
                    $q->where('is_premium', $request->premium === 'true');
                });
            }

            if ($request->filled('active')) {
                $query->whereHas('usuarioCuenta', function ($q) use ($request) {
                    $q->where('activo', $request->active === 'true');
                });
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');

            if ($sortBy === 'experience') {
                $query->join('cuentas', 'users.id', '=', 'cuentas.user_id')
                      ->orderBy('cuentas.total_xp', $sortDirection)
                      ->select('users.*');
            } else {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Paginación
            $perPage = $request->get('per_page', 15);
            $users = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas del sistema para gráficos
     */
    public function charts(Request $request)
    {
        try {
            $period = $request->get('period', '7d'); // 7d, 30d, 90d, 1y

            $chartData = [
                'user_registrations' => $this->getUserRegistrationChart($period),
                'element_creation' => $this->getElementCreationChart($period),
                'user_activity' => $this->getUserActivityChart($period),
                'revenue' => $this->getRevenueChart($period),
            ];

            return response()->json([
                'success' => true,
                'data' => $chartData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo datos de gráficos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getUserRegistrationChart($period)
    {
        $days = $this->getPeriodDays($period);
        $startDate = Carbon::now()->subDays($days);

        return User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getElementCreationChart($period)
    {
        $days = $this->getPeriodDays($period);
        $startDate = Carbon::now()->subDays($days);

        return Elemento::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('tipo'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy(['date', 'tipo'])
            ->orderBy('date')
            ->get();
    }

    private function getUserActivityChart($period)
    {
        $days = $this->getPeriodDays($period);
        $startDate = Carbon::now()->subDays($days);

        // Usar last_login_at de la tabla users
        return User::select(
                DB::raw('DATE(last_login_at) as date'),
                DB::raw('count(*) as count')
            )
            ->whereNotNull('last_login_at')
            ->where('last_login_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getRevenueChart($period)
    {
        $days = $this->getPeriodDays($period);
        $startDate = Carbon::now()->subDays($days);

        // Simular datos de ingresos basados en usuarios premium creados
        return UsuarioCuenta::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) * 9.99 as revenue') // Asumiendo precio de $9.99
            )
            ->where('is_premium', true)
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getPeriodDays($period)
    {
        return match($period) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            '1y' => 365,
            default => 30
        };
    }
}
