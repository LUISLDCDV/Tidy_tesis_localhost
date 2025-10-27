<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\DashboardController as ApiDashboardController;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    protected $apiDashboard;

    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
        $this->apiDashboard = new ApiDashboardController();
    }

    /**
     * Dashboard principal del administrador (Vista Web)
     */
    public function index()
    {
        try {
            // Obtener datos del dashboard usando el controlador API
            $request = request();
            $response = $this->apiDashboard->index();
            $data = json_decode($response->getContent(), true);

            if ($data['success']) {
                return view('admin.dashboard.index', [
                    'dashboardData' => $data['data']
                ]);
            } else {
                return view('admin.dashboard.index', [
                    'dashboardData' => null,
                    'error' => $data['message']
                ]);
            }

        } catch (\Exception $e) {
            return view('admin.dashboard.index', [
                'dashboardData' => null,
                'error' => 'Error cargando el dashboard: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Gestión de usuarios (Vista Web)
     */
    public function users()
    {
        try {
            $request = request();
            $response = $this->apiDashboard->users($request);
            $data = json_decode($response->getContent(), true);

            if ($data['success']) {
                return view('admin.dashboard.users', [
                    'usersData' => $data['data']
                ]);
            } else {
                return view('admin.dashboard.users', [
                    'usersData' => null,
                    'error' => $data['message']
                ]);
            }

        } catch (\Exception $e) {
            return view('admin.dashboard.users', [
                'usersData' => null,
                'error' => 'Error cargando usuarios: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Estadísticas y gráficos (Vista Web)
     */
    public function charts()
    {
        try {
            $request = request();
            $response = $this->apiDashboard->charts($request);
            $data = json_decode($response->getContent(), true);

            if ($data['success']) {
                return view('admin.dashboard.charts', [
                    'chartsData' => $data['data']
                ]);
            } else {
                return view('admin.dashboard.charts', [
                    'chartsData' => null,
                    'error' => $data['message']
                ]);
            }

        } catch (\Exception $e) {
            return view('admin.dashboard.charts', [
                'chartsData' => null,
                'error' => 'Error cargando gráficos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Historial de pagos (Vista Web)
     */
    public function payments(Request $request)
    {
        try {
            $query = Payment::with('usuario');

            // Filtros
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->whereHas('usuario', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Ordenar por fecha de pago más reciente
            $payments = $query->orderBy('paid_at', 'desc')->paginate(20);

            // Estadísticas
            $stats = [
                'total_payments' => Payment::approved()->count(),
                'total_amount' => Payment::approved()->sum('amount'),
                'pending_payments' => Payment::pending()->count(),
                'monthly_revenue' => Payment::approved()
                    ->whereMonth('paid_at', now()->month)
                    ->whereYear('paid_at', now()->year)
                    ->sum('amount')
            ];

            return view('admin.dashboard.payments', [
                'payments' => $payments,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return view('admin.dashboard.payments', [
                'payments' => [],
                'stats' => null,
                'error' => 'Error cargando historial de pagos: ' . $e->getMessage()
            ]);
        }
    }
}