<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UsuarioCuenta;
use App\Models\Notificacion;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Registrar token de dispositivo FCM
     */
    public function registerDeviceToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string',
            'device_type' => 'required|string|in:android,ios,web',
            'device_name' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Datos inválidos',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            $user = auth()->user();

            Log::info('Device token registration simulated (Firebase not available)', [
                'user_id' => $user->id,
                'device_type' => $request->device_type,
                'device_token' => substr($request->device_token, 0, 20) . '...'
            ]);

            return response()->json([
                'message' => 'Token de dispositivo registrado exitosamente',
                'status' => 'simulated'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to register device token: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Eliminar token de dispositivo
     */
    public function unregisterDeviceToken(Request $request)
    {
        try {
            $user = auth()->user();

            Log::info('Device token unregistration simulated (Firebase not available)', [
                'user_id' => $user->id
            ]);

            return response()->json([
                'message' => 'Token de dispositivo desregistrado exitosamente',
                'status' => 'simulated'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to unregister device token: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Enviar notificación de prueba
     */
    public function sendTestNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'user_id' => 'nullable|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Datos inválidos',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            $targetUserId = $request->user_id ?? auth()->id();

            Log::info('Test notification simulated (Firebase not available)', [
                'user_id' => $targetUserId,
                'title' => $request->title,
                'body' => $request->body
            ]);

            return response()->json([
                'message' => 'Notificación de prueba enviada exitosamente',
                'status' => 'simulated'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to send test notification: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al enviar notificación'
            ], 500);
        }
    }

    /**
     * Obtener historial de notificaciones del usuario
     */
    public function getNotificationHistory(Request $request)
    {
        try {
            $user = auth()->user();
            $limit = $request->get('limit', 20);
            $offset = $request->get('offset', 0);

            // Por ahora devolvemos un historial vacío hasta implementar tabla de notificaciones
            $notifications = collect();

            return response()->json([
                'notifications' => $notifications,
                'total' => $notifications->count(),
                'limit' => $limit,
                'offset' => $offset
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get notification history: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener historial'
            ], 500);
        }
    }

    /**
     * Actualizar configuración de notificaciones del usuario
     */
    public function updateNotificationSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'element_created' => 'boolean',
            'element_updated' => 'boolean',
            'alarm_triggered' => 'boolean',
            'goal_completed' => 'boolean',
            'level_up' => 'boolean',
            'achievement_unlocked' => 'boolean',
            'reminders' => 'boolean',
            'system_notifications' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Datos inválidos',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            $user = auth()->user();
            $settings = $request->only([
                'element_created',
                'element_updated',
                'alarm_triggered',
                'goal_completed',
                'level_up',
                'achievement_unlocked',
                'reminders',
                'system_notifications'
            ]);

            $cuenta = UsuarioCuenta::where('user_id', $user->id)->first();
            if (!$cuenta) {
                $cuenta = new UsuarioCuenta();
                $cuenta->user_id = $user->id;
                $cuenta->configuraciones = '{}';
            }

            // Parsear configuraciones existentes
            $config = json_decode($cuenta->configuraciones, true) ?? [];

            // Actualizar configuraciones de notificaciones
            $config['notifications'] = array_merge($config['notifications'] ?? [], $settings);

            // Guardar configuraciones actualizadas
            $cuenta->configuraciones = json_encode($config);
            $cuenta->save();

            Log::info('Notification settings updated', [
                'user_id' => $user->id,
                'settings' => $settings
            ]);

            return response()->json([
                'message' => 'Configuración de notificaciones actualizada',
                'settings' => $config['notifications']
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to update notification settings: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al actualizar configuración'
            ], 500);
        }
    }

    /**
     * Obtener configuración de notificaciones del usuario
     */
    public function getNotificationSettings()
    {
        try {
            $user = auth()->user();

            $cuenta = UsuarioCuenta::where('user_id', $user->id)->first();

            // Configuración por defecto si no existe cuenta
            $defaultSettings = [
                'element_created' => true,
                'element_updated' => true,
                'alarm_triggered' => true,
                'goal_completed' => true,
                'level_up' => true,
                'achievement_unlocked' => true,
                'reminders' => true,
                'system_notifications' => true
            ];

            if (!$cuenta || !$cuenta->configuraciones) {
                return response()->json([
                    'settings' => $defaultSettings
                ], 200);
            }

            // Parsear configuraciones desde JSON
            $config = json_decode($cuenta->configuraciones, true) ?? [];
            $settings = $config['notifications'] ?? $defaultSettings;

            return response()->json([
                'settings' => $settings
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get notification settings: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener configuración'
            ], 500);
        }
    }

    /**
     * Listar notificaciones del usuario
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $cuenta = UsuarioCuenta::where('user_id', $user->id)->first();

            if (!$cuenta) {
                return response()->json(['data' => []], 200);
            }

            $query = Notificacion::where('cuenta_id', $cuenta->id);

            // Filtrar por tipo si se proporciona
            if ($request->has('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            $notificaciones = $query->orderByDesc('created_at')->get();

            return response()->json(['data' => $notificaciones], 200);
        } catch (\Exception $e) {
            Log::error('Failed to get notifications: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener notificaciones'], 500);
        }
    }

    /**
     * Obtener contador de notificaciones no leídas
     */
    public function unreadCount()
    {
        try {
            $user = auth()->user();
            $cuenta = UsuarioCuenta::where('user_id', $user->id)->first();

            if (!$cuenta) {
                return response()->json(['count' => 0], 200);
            }

            $count = Notificacion::where('cuenta_id', $cuenta->id)
                ->where('leido', false)
                ->count();

            return response()->json(['count' => $count], 200);
        } catch (\Exception $e) {
            Log::error('Failed to get unread count: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener contador'], 500);
        }
    }

    /**
     * Marcar notificación como leída
     */
    public function markAsRead($id)
    {
        try {
            $user = auth()->user();
            $cuenta = UsuarioCuenta::where('user_id', $user->id)->first();

            if (!$cuenta) {
                return response()->json(['error' => 'Cuenta no encontrada'], 404);
            }

            $notificacion = Notificacion::where('id', $id)
                ->where('cuenta_id', $cuenta->id)
                ->firstOrFail();

            $notificacion->update(['leido' => true]);

            return response()->json(['message' => 'Notificación marcada como leída'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read: ' . $e->getMessage());
            return response()->json(['error' => 'Error al marcar notificación'], 500);
        }
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead()
    {
        try {
            $user = auth()->user();
            $cuenta = UsuarioCuenta::where('user_id', $user->id)->first();

            if (!$cuenta) {
                return response()->json(['error' => 'Cuenta no encontrada'], 404);
            }

            Notificacion::where('cuenta_id', $cuenta->id)
                ->where('leido', false)
                ->update(['leido' => true]);

            return response()->json(['message' => 'Todas las notificaciones marcadas como leídas'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to mark all as read: ' . $e->getMessage());
            return response()->json(['error' => 'Error al marcar notificaciones'], 500);
        }
    }

    /**
     * Eliminar una notificación
     */
    public function destroy($id)
    {
        try {
            $user = auth()->user();
            $cuenta = UsuarioCuenta::where('user_id', $user->id)->first();

            if (!$cuenta) {
                return response()->json(['error' => 'Cuenta no encontrada'], 404);
            }

            $notificacion = Notificacion::where('id', $id)
                ->where('cuenta_id', $cuenta->id)
                ->firstOrFail();

            $notificacion->delete();

            return response()->json(['message' => 'Notificación eliminada'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete notification: ' . $e->getMessage());
            return response()->json(['error' => 'Error al eliminar notificación'], 500);
        }
    }
}