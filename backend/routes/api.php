<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiUsuarioController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request){
    return $request->user();
});

// Route::middleware(['cors'])->group(function () {
// });

// Las rutas OPTIONS están manejadas por el middleware CORS

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/validate-token', [AuthController::class, 'validateToken']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::get('/sessions', [AuthController::class, 'getUserSessions']);
    Route::delete('/sessions/{tokenId}', [AuthController::class, 'revokeSession']);

    // Rutas de verificación de email para API
    Route::get('/email/verification-status', [App\Http\Controllers\EmailVerificationController::class, 'status']);
    Route::post('/email/verification-notification', [App\Http\Controllers\EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1');
});

// Ruta pública para verificar email con token (no requiere autenticación)
Route::post('/verify-email', [App\Http\Controllers\EmailVerificationController::class, 'verifyToken']);

Route::middleware('auth:sanctum')->group(function () {

    // Rutas de imagen de perfil
    Route::prefix('profile')->group(function () {
        Route::post('/image/upload', [App\Http\Controllers\ProfileImageController::class, 'upload']);
        Route::post('/image/upload-base64', [App\Http\Controllers\ProfileImageController::class, 'uploadBase64']);
        Route::get('/image', [App\Http\Controllers\ProfileImageController::class, 'get']);
        Route::delete('/image', [App\Http\Controllers\ProfileImageController::class, 'delete']);
    });

    // Rutas de sincronización offline - TEMPORALMENTE DESHABILITADAS
    // Route::prefix('sync')->group(function () {
    //     Route::post('/process', [App\Http\Controllers\SyncController::class, 'processSync']);
    //     Route::post('/save', [App\Http\Controllers\SyncController::class, 'saveForSync']);
    //     Route::get('/stats', [App\Http\Controllers\SyncController::class, 'getStats']);
    //     Route::get('/history', [App\Http\Controllers\SyncController::class, 'getHistory']);
    //     Route::post('/retry-failed', [App\Http\Controllers\SyncController::class, 'retryFailed']);
    //     Route::post('/check-connectivity', [App\Http\Controllers\SyncController::class, 'checkConnectivity']);
    // });

    // Rutas de notificaciones móviles - TEMPORALMENTE DESHABILITADAS
    // Route::prefix('mobile')->group(function () {
    //     Route::post('/register-device', [App\Http\Controllers\MobileNotificationController::class, 'registerDeviceToken']);
    //     Route::post('/send-notification', [App\Http\Controllers\MobileNotificationController::class, 'sendImmediateNotification']);
    //     Route::post('/schedule-notification', [App\Http\Controllers\MobileNotificationController::class, 'scheduleNotification']);
    //     Route::get('/scheduled-notifications', [App\Http\Controllers\MobileNotificationController::class, 'getScheduledNotifications']);
    //     Route::delete('/scheduled-notifications/{id}', [App\Http\Controllers\MobileNotificationController::class, 'cancelScheduledNotification']);
    //     Route::put('/notification-settings', [App\Http\Controllers\MobileNotificationController::class, 'updateNotificationSettings']);
    // });
});

Route::middleware('auth:sanctum')->get('/user-data', [ApiUsuarioController::class, 'getUserData']);

// Route::get('/user-data', [AuthController::class, 'getUserData']);



use App\Http\Controllers\Auth\GoogleLoginController;

Route::post('/auth/google-login', [GoogleLoginController::class, 'login']);






use App\Http\Controllers\Elementos\ElementoController;

use App\Http\Controllers\Elementos\Nota\NotaController;

use App\Http\Controllers\Elementos\Alarma\AlarmaController;

use App\Http\Controllers\Elementos\Calendario\CalendarioController;
use App\Http\Controllers\Elementos\Calendario\EventoController;

use App\Http\Controllers\Elementos\Objetivo\ObjetivoController;
use App\Http\Controllers\Elementos\Objetivo\MetaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\MercadoPagoController;

// Route::middleware('auth:sanctum')->get('/usuarios/elementos', [ElementoController::class, 'elementosPorUsuario']);


Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // Endpoints con rate limiting específico para elementos críticos
    Route::middleware('throttle:elements')->group(function () {
        Route::get('/usuarios/elementos', [ElementoController::class, 'elementosPorUsuario']);
        Route::get('/usuarios/elemento/{id}', [ElementoController::class, 'elementoPorId']);
    });

    // Endpoints de actualización con rate limiting más restrictivo
    Route::middleware(['throttle:updates', 'ensure.nota.types'])->group(function () {
        Route::post('/elementos/saveUpdate', [ElementoController::class, 'guardarElemento']);
        Route::post('/elementos/updateOrder', [ElementoController::class, 'actualizarOrden']);
        Route::post('/elementos/eliminarElemento/{id}', [ElementoController::class, 'eliminarElemento']);
    });

    // Endpoints de metas con rate limiting muy restrictivo (los que causan más problemas)
    Route::middleware('throttle:metas')->group(function () {
        Route::get('/elementos/{elementoId}/objetivo-id', [ElementoController::class, 'obtenerObjetivoId']);
        Route::get('/elementos/{elementoId}/metas', [ElementoController::class, 'obtenerMetasDeObjetivo']);
    });

    /**Notas**/
    Route::get('/usuarios/notas', [NotaController::class, 'obtenerNotasPorUsuario']);

    // API RESTful para notas (usado por tests)
    Route::apiResource('notes', NotaController::class);
    Route::post('/notes/{id}/duplicate', [NotaController::class, 'duplicate']);
    Route::patch('/notes/{id}/archive', [NotaController::class, 'archive']);
    Route::patch('/notes/{id}/unarchive', [NotaController::class, 'unarchive']);
    Route::delete('/notes/bulk', [NotaController::class, 'bulkDelete']);
    Route::get('/note-types/available', [App\Http\Controllers\TipoNotaController::class, 'available']);
    
    /**Alarmas**/
    Route::get('/usuarios/alarmas', [AlarmaController::class, 'obtenerAlarmasPorUsuario']);

    // API RESTful para alarmas (usado por tests)
    Route::apiResource('alarms', AlarmaController::class);
    Route::patch('/alarms/{id}/toggle', [AlarmaController::class, 'toggle']);
    Route::get('/alarms/nearby', [AlarmaController::class, 'nearby']);
    Route::post('/alarms/check-location', [AlarmaController::class, 'checkLocation']);

    /**Elementos**/
    Route::get('/elementos', [ElementoController::class, 'index']);

    /**Calendario**/
    Route::get('/usuarios/calendarios', [CalendarioController::class, 'obtenerCalendariosPorUsuario']);
    Route::get('/usuarios/{idCalendario}/eventos', [EventoController::class, 'obtenerEventosPorUsuario']);

    // API RESTful para calendarios y eventos (usado por tests)
    Route::apiResource('calendars', CalendarioController::class);
    Route::apiResource('events', EventoController::class);

    /**Objetivos**/
    Route::get('/usuarios/objetivos', [ObjetivoController::class, 'obtenerObjetivosPorUsuario']);
    Route::get('/usuarios/{idObjetivo}/metas', [MetaController::class, 'obtenerMetasPorUsuario']);
    
    /**Notificaciones**/
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::post('/notifications/register-device', [NotificationController::class, 'registerDeviceToken']);
    Route::post('/notifications/unregister-device', [NotificationController::class, 'unregisterDeviceToken']);
    Route::post('/notifications/test', [NotificationController::class, 'sendTestNotification']);
    Route::get('/notifications/history', [NotificationController::class, 'getNotificationHistory']);
    Route::get('/notifications/settings', [NotificationController::class, 'getNotificationSettings']);
    Route::post('/notifications/settings', [NotificationController::class, 'updateNotificationSettings']);

    /**Configuraciones de Notificaciones (rutas alternativas)**/
    Route::get('/usuarios/notifications/settings', [NotificationController::class, 'getNotificationSettings']);
    Route::put('/usuarios/notifications/settings', [NotificationController::class, 'updateNotificationSettings']);
    Route::get('/usuarios/notifications/history', [NotificationController::class, 'getNotificationHistory']);
    Route::post('/usuarios/notifications/device', [NotificationController::class, 'registerDeviceToken']);
    Route::delete('/usuarios/notifications/device/{token}', [NotificationController::class, 'unregisterDeviceToken']);
    Route::post('/usuarios/notifications/test', [NotificationController::class, 'sendTestNotification']);
    
    /**Perfil de Usuario**/
    Route::get('/usuarios/profile', [ApiUsuarioController::class, 'getProfile']);
    Route::put('/usuarios/profile', [ApiUsuarioController::class, 'updateProfile']);
    Route::get('/usuarios/settings', [ApiUsuarioController::class, 'getSettings']);
    Route::put('/usuarios/settings', [ApiUsuarioController::class, 'updateSettings']);

    /**Sistema de Niveles**/
    Route::get('/user/level', [LevelController::class, 'getUserLevel']);
    Route::get('/level', [LevelController::class, 'getUserLevel']); // Alias para tests
    Route::get('/user/experience', [LevelController::class, 'getUserExperience']);
    Route::get('/user/achievements', [LevelController::class, 'getUserAchievements']);
    Route::get('/leaderboard', [LevelController::class, 'getLeaderboard']);

    /**MercadoPago Premium**/
    Route::get('/mercadopago/subscription-url', function(Request $request) {
        $controller = new MercadoPagoController();
        return response()->json([
            'url' => $controller->createSubscriptionUrl($request->user()->id)
        ]);
    });

});

/**MercadoPago Webhooks (sin autenticación)**/
Route::post('/mercadopago/webhook', [MercadoPagoController::class, 'webhook']);
Route::get('/mercadopago/success', [MercadoPagoController::class, 'subscriptionSuccess']);

/**Informes de Pago**/
use App\Http\Controllers\PaymentReportController;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // Rutas para usuarios
    Route::post('/payment-reports', [PaymentReportController::class, 'store']);
    Route::get('/payment-reports/my', [PaymentReportController::class, 'myReports']);

    // Rutas para administradores
    Route::get('/payment-reports', [PaymentReportController::class, 'index']);
    Route::get('/payment-reports/stats', [PaymentReportController::class, 'stats']);
    Route::get('/payment-reports/{id}', [PaymentReportController::class, 'show']);
    Route::patch('/payment-reports/{id}/status', [PaymentReportController::class, 'updateStatus']);
});

/**
 * Dashboard Administrativo
 */
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\LevelConfigController;
use App\Http\Controllers\Admin\RankingController;

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/users', [DashboardController::class, 'users']);
    Route::get('/charts', [DashboardController::class, 'charts']);

    /**Configuración de Niveles**/
    Route::apiResource('levels', LevelConfigController::class);
    Route::post('/levels/recalcular', [LevelConfigController::class, 'recalcularNiveles']);
    Route::get('/levels/export/config', [LevelConfigController::class, 'exportarConfiguracion']);
    Route::post('/levels/import/config', [LevelConfigController::class, 'importarConfiguracion']);
    Route::get('/levels/preview/recalculo', [LevelConfigController::class, 'previsualizarRecalculo']);

    /**Rankings y Estadísticas**/
    Route::get('/ranking', [RankingController::class, 'index']);
    Route::get('/ranking/user/{userId}', [RankingController::class, 'userStats']);
    Route::get('/ranking/level-history', [RankingController::class, 'levelHistory']);
    Route::post('/ranking/clear-cache', [RankingController::class, 'clearCache']);
});

/**Contadores de Elementos**/
use App\Http\Controllers\ElementCountController;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/usuarios/elements-count', [ElementCountController::class, 'getUserElementsCount']);
    Route::get('/usuarios/elements-stats', [ElementCountController::class, 'getDetailedStats']);
    Route::post('/usuarios/elements-can-create', [ElementCountController::class, 'canCreateElement']);
    Route::get('/usuarios/elements-optimization', [ElementCountController::class, 'getOptimizationSuggestions']);
    Route::post('/usuarios/elements-cleanup', [ElementCountController::class, 'cleanupObsoleteElements']);

    /**Tipos de Notas**/
    Route::get('/tipos-notas', [App\Http\Controllers\TipoNotaController::class, 'index']);
    Route::get('/tipos-notas/verificar/{id}', [App\Http\Controllers\TipoNotaController::class, 'verificarTipo']);
    Route::post('/tipos-notas/inicializar', [App\Http\Controllers\TipoNotaController::class, 'inicializar']);
});

/**Alarmas GPS Premium - TEMPORALMENTE DESHABILITADAS**/
// use App\Http\Controllers\GPSAlarmController;

// Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
//     Route::apiResource('gps-alarms', GPSAlarmController::class);
//     Route::patch('/gps-alarms/{id}/toggle', [GPSAlarmController::class, 'toggle']);
//     Route::post('/gps-alarms/trigger', [GPSAlarmController::class, 'trigger']);
//     Route::get('/gps-alarms-stats', [GPSAlarmController::class, 'stats']);
//     Route::get('/gps-alarms-history', [GPSAlarmController::class, 'triggerHistory']);
//     Route::get('/gps-alarms-nearby', [GPSAlarmController::class, 'nearby']);
// });

/**Alarmas Móviles - TEMPORALMENTE DESHABILITADAS**/
// use App\Http\Controllers\MobileAlarmController;

// Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
//     Route::post('/mobile-alarms', [MobileAlarmController::class, 'createAlarm']);
//     Route::get('/mobile-alarms', [MobileAlarmController::class, 'getUserAlarms']);
//     Route::put('/mobile-alarms/{id}', [MobileAlarmController::class, 'updateAlarm']);
//     Route::delete('/mobile-alarms/{id}', [MobileAlarmController::class, 'deleteAlarm']);
//     Route::patch('/mobile-alarms/{id}/toggle', [MobileAlarmController::class, 'toggleAlarm']);
//     Route::post('/mobile-alarms/{id}/snooze', [MobileAlarmController::class, 'snoozeAlarm']);
//     Route::post('/mobile-alarms/test', [MobileAlarmController::class, 'sendTestNotification']);
//     Route::get('/mobile-alarms/stats', [MobileAlarmController::class, 'getAlarmStats']);
// });

/**Sistema de Logs Frontend**/
use App\Http\Controllers\LogController;

Route::middleware(['throttle:logs'])->group(function () {
    // Logs sin autenticación para errores críticos
    Route::post('/logs/error', [LogController::class, 'logError']);
    Route::post('/logs/batch', [LogController::class, 'logBatch']);
    Route::post('/logs/event', [LogController::class, 'logEvent']);
    Route::post('/logs/performance', [LogController::class, 'logPerformance']);
});

// Logs con autenticación para datos más detallados
Route::middleware(['auth:sanctum', 'throttle:logs'])->group(function () {
    Route::post('/logs/authenticated/error', [LogController::class, 'logError']);
    Route::post('/logs/authenticated/batch', [LogController::class, 'logBatch']);
    Route::post('/logs/authenticated/event', [LogController::class, 'logEvent']);
    Route::post('/logs/authenticated/performance', [LogController::class, 'logPerformance']);
});

/**Sistema de Comentarios/Solicitudes de Ayuda**/
use App\Http\Controllers\UserCommentController;

// Rutas públicas para comentarios (usuarios anónimos)
Route::middleware(['throttle:api'])->group(function () {
    Route::post('/comments', [UserCommentController::class, 'store']);
    Route::get('/comments/types', [UserCommentController::class, 'getTypes']);
});

// Rutas autenticadas para comentarios
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/comments/my', [UserCommentController::class, 'getUserComments']);
    Route::get('/comments/my/stats', [UserCommentController::class, 'getUserStats']);
    Route::get('/comments/{id}', [UserCommentController::class, 'show']);
    Route::post('/comments/authenticated', [UserCommentController::class, 'store']);
});

//use App\Http\Controllers\Elementos\ElementoController;
// Route::post('/elementos/update-order/{id}/{orden}', [ElementoController::class, 'updateOrder']);

