<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Rutas para landing page
Route::get('/sobre-el-proyecto', function () {
    return view('landing.about-project');
})->name('about.project');

Route::get('/tesis-docs', function () {
    return view('landing.tesis-docs');
})->name('tesis.docs');

Auth::routes(['verify' => true]);

// Rutas de verificación de email
Route::middleware('auth')->group(function () {
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');
});

Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware(['auth']);

// Rutas de Perfil de Usuario
Route::middleware('auth')->group(function () {
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::get('/perfil/editar', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
});

// Rutas de Usuarios
Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
Route::get('/usuarios/create', [UsuarioController::class, 'create'])->name('usuarios.create');
Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
Route::get('/usuarios/{id}', [UsuarioController::class, 'show'])->name('usuarios.show');
Route::get('/usuarios/{id}/edit', [UsuarioController::class, 'edit'])->name('usuarios.edit');
Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
Route::patch('/usuarios/{id}/restore', [UsuarioController::class, 'restore'])->name('usuarios.restore');
Route::patch('/usuarios/{id}/stats', [UsuarioController::class, 'updateStats'])->name('usuarios.updateStats');
Route::post('/usuarios/{id}/assign-permission', [UsuarioController::class, 'assignPermission'])->name('usuarios.assignPermission');
Route::get('/usuarios/{id}/edit-role', [UsuarioController::class, 'editRole'])->name('usuarios.editRole');
Route::put('/usuarios/{id}/update-role', [UsuarioController::class, 'updateRole'])->name('usuarios.updateRole');

// Rutas de Permisos
Route::get('/permisos', [PermisoController::class, 'index'])->name('permisos.index');
Route::get('/permisos/create', [PermisoController::class, 'create'])->name('permisos.create');
Route::post('/permisos', [PermisoController::class, 'store'])->name('permisos.store');
Route::get('/permisos/{id}', [PermisoController::class, 'show'])->name('permisos.show');
Route::get('/permisos/{id}/edit', [PermisoController::class, 'edit'])->name('permisos.edit');
Route::put('/permisos/{id}', [PermisoController::class, 'update'])->name('permisos.update');
Route::delete('/permisos/{id}', [PermisoController::class, 'destroy'])->name('permisos.destroy');
Route::patch('/permisos/{id}/restore', [PermisoController::class, 'restore'])->name('permisos.restore');

// Rutas de Roles
Route::get('/roles/create', [RolController::class, 'create'])->name('roles.create');
Route::post('/roles', [RolController::class, 'store'])->name('roles.store');
Route::get('/roles', [RolController::class, 'index'])->name('roles.index');
Route::get('/roles/{id}/edit', [RolController::class, 'edit'])->name('roles.edit');
Route::put('/roles/{id}', [RolController::class, 'update'])->name('roles.update');
Route::delete('/roles/{id}', [RolController::class, 'destroy'])->name('roles.destroy');
Route::get('/roles/{id}/edit-permissions', [RolController::class, 'editPermissions'])->name('roles.editPermissions');
Route::put('/roles/{id}/update-permissions', [RolController::class, 'updatePermissions'])->name('roles.updatePermissions');
Route::post('/roles/{id}/add-permission/{permissionId}', [RolController::class, 'addPermission'])->name('roles.addPermission');
Route::delete('/roles/{id}/remove-permission/{permissionId}', [RolController::class, 'removePermission'])->name('roles.removePermission');

// Dashboard Administrativo
Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', function() {
        return redirect()->route('admin.dashboard.users');
    })->name('dashboard.index');

    Route::get('/dashboard/users', [UserManagementController::class, 'index'])->name('dashboard.users');

    Route::get('/dashboard/charts', [AdminDashboardController::class, 'charts'])->name('dashboard.charts');

    Route::get('/dashboard/payments', [AdminDashboardController::class, 'payments'])->name('dashboard.payments');

    // Rutas de gestión de usuarios
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/edit-experience', [UserManagementController::class, 'editExperience'])->name('users.editExperience');
    Route::post('/users/{user}/edit-level', [UserManagementController::class, 'editLevel'])->name('users.editLevel');
    Route::post('/users/{user}/edit-premium', [UserManagementController::class, 'editPremium'])->name('users.editPremium');
    Route::post('/users/{user}/soft-delete', [UserManagementController::class, 'softDelete'])->name('users.softDelete');
    Route::post('/users/{user}/restore', [UserManagementController::class, 'restore'])->name('users.restore');
    Route::post('/users/{user}/send-notification', [UserManagementController::class, 'sendNotification'])->name('users.sendNotification');
    Route::post('/users/{user}/send-test-email', [UserManagementController::class, 'sendTestEmail'])->name('users.sendTestEmail');

    Route::get('/levels', function() {
        return view('admin.levels.index');
    })->name('levels.index');

    Route::get('/ranking', function() {
        return view('admin.ranking.index');
    })->name('ranking.index');

    // Configuración de gamificación
    Route::get('/gamification', [\App\Http\Controllers\Admin\GamificationConfigController::class, 'index'])->name('gamification.index');
    Route::put('/gamification', [\App\Http\Controllers\Admin\GamificationConfigController::class, 'update'])->name('gamification.update');

    // Rutas de administración de comentarios
    Route::prefix('comments')->name('comments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CommentAdminController::class, 'index'])->name('index');
        Route::get('/dashboard', [\App\Http\Controllers\Admin\CommentAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/{id}', [\App\Http\Controllers\Admin\CommentAdminController::class, 'show'])->name('show');
        Route::post('/{id}/respond', [\App\Http\Controllers\Admin\CommentAdminController::class, 'respond'])->name('respond');
        Route::patch('/{id}/status', [\App\Http\Controllers\Admin\CommentAdminController::class, 'updateStatus'])->name('updateStatus');
        Route::patch('/{id}/priority', [\App\Http\Controllers\Admin\CommentAdminController::class, 'updatePriority'])->name('updatePriority');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\CommentAdminController::class, 'destroy'])->name('destroy');
    });

    // Logs
    Route::get('/logs', function() {
        try {
            $logs = [];
            $logInfo = [
                'file_exists' => false,
                'file_size' => 0,
                'file_path' => 'Railway usa stderr - Ver railway logs'
            ];

            $logDir = storage_path('logs');
            $logFiles = glob($logDir . '/laravel*.log');

            if ($logFiles && count($logFiles) > 0) {
                usort($logFiles, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });

                $logFile = $logFiles[0];
                $logInfo = [
                    'file_exists' => true,
                    'file_size' => filesize($logFile),
                    'file_path' => $logFile
                ];

                $lines = [];
                $handle = fopen($logFile, 'r');
                if ($handle) {
                    fseek($handle, max(0, filesize($logFile) - 8192));
                    $content = fread($handle, 8192);
                    fclose($handle);

                    $lines = array_filter(explode("\n", $content));
                    $lines = array_slice($lines, -50);
                }

                foreach ($lines as $line) {
                    if (preg_match('/^\[([^\]]+)\]\s+(\w+)\.(\w+):\s*(.*)/', $line, $matches)) {
                        $logs[] = [
                            'date' => $matches[1],
                            'level' => strtoupper($matches[3]),
                            'message' => $matches[4]
                        ];
                    }
                }
            }

            if (empty($logs)) {
                $logs = [
                    [
                        'date' => now()->format('Y-m-d H:i:s'),
                        'level' => 'INFO',
                        'message' => 'Sistema funcionando en Railway - Los logs se envían a stderr'
                    ],
                    [
                        'date' => now()->subMinutes(5)->format('Y-m-d H:i:s'),
                        'level' => 'INFO',
                        'message' => 'Para ver logs completos usar: railway logs'
                    ]
                ];
            }

            return view('admin.logs.index', compact('logs', 'logInfo'));
        } catch (\Exception $e) {
            $logs = [
                [
                    'date' => now()->format('Y-m-d H:i:s'),
                    'level' => 'ERROR',
                    'message' => 'Error leyendo logs: ' . $e->getMessage()
                ]
            ];

            $logInfo = [
                'file_exists' => false,
                'file_size' => 0,
                'file_path' => 'Error de acceso'
            ];

            return view('admin.logs.index', compact('logs', 'logInfo'));
        }
    })->name('logs.index');

    // Test email
    Route::get('/test-email', function() {
        return view('admin.test-email');
    })->name('test-email');

    Route::post('/test-email', function(\Illuminate\Http\Request $request) {
        $request->validate([
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000'
        ]);

        try {
            \Mail::send('emails.test-admin', [
                'messageContent' => $request->message,
                'emailSubject' => $request->subject,
                'timestamp' => now()
            ], function ($mail) use ($request) {
                $mail->to($request->email)
                     ->subject($request->subject)
                     ->from(config('mail.from.address'), config('mail.from.name'));
            });

            return back()->with('success', 'Email enviado correctamente a ' . $request->email);
        } catch (\Exception $e) {
            return back()->with('error', 'Error enviando email: ' . $e->getMessage());
        }
    })->name('test-email.send');
});

// Rutas de comentarios para usuarios normales
Route::prefix('comments')->middleware('auth')->name('user.comments.')->group(function () {
    Route::get('/', [\App\Http\Controllers\UserCommentViewController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\UserCommentViewController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\UserCommentViewController::class, 'store'])->name('store');
    Route::get('/{comment}', [\App\Http\Controllers\UserCommentViewController::class, 'show'])->name('show');
});

// Ruta de acceso directo al dashboard admin
Route::get('/admin', function () {
    return redirect()->route('home');
})->middleware(['auth', 'role:admin']);
