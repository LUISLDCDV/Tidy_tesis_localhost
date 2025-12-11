<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UsuarioCuenta;
use App\Models\Elementos\Elemento;
use App\Models\UserComment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Web\AdminDashboardController;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application home page.
     * - Si es admin: muestra el dashboard administrativo
     * - Si es usuario normal: redirige al perfil
     */
    public function index()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return redirect()->route('login')->with('message', 'Debes iniciar sesión para acceder.');
            }

            // Verificar si el usuario es admin (método seguro)
            $isAdmin = false;
            try {
                $user->load('roles');
                $isAdmin = $user->roles->contains('name', 'admin');
            } catch (\Exception $e) {
                // Si falla cargar roles, asumir que no es admin
                $isAdmin = false;
            }

            if ($isAdmin) {
                // Dashboard para admin con datos reales del backend
                try {
                    $adminDashboard = new AdminDashboardController();
                    return $adminDashboard->index();
                } catch (\Exception $e) {
                    // Si falla obtener datos reales, mostrar dashboard vacío
                    return view('admin.dashboard.index', [
                        'dashboardData' => null,
                        'error' => 'No se pudieron cargar los datos del dashboard. Verifica la conexión a la base de datos.'
                    ]);
                }
            } else {
                // Home simple para usuarios normales
                return view('home', ['user' => $user]);
            }

        } catch (\Exception $e) {
            // Si todo falla, mostrar error
            return view('admin.dashboard.index', [
                'dashboardData' => null,
                'error' => 'Error cargando el dashboard: ' . $e->getMessage()
            ]);
        }
    }

}
