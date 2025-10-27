package com.tidy.app;

import android.Manifest;
import android.app.NotificationManager;
import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.os.Build;
import android.os.PowerManager;
import android.provider.Settings;
import android.util.Log;
import com.getcapacitor.JSObject;
import com.getcapacitor.Plugin;
import com.getcapacitor.PluginCall;
import com.getcapacitor.PluginMethod;
import com.getcapacitor.annotation.CapacitorPlugin;
import com.getcapacitor.annotation.Permission;

/**
 * Plugin para gestionar permisos críticos de Android relacionados con alarmas.
 *
 * Permisos gestionados:
 * - POST_NOTIFICATIONS (Android 13+)
 * - Optimización de batería
 * - Do Not Disturb (acceso a notificaciones)
 */
@CapacitorPlugin(
    name = "PermissionsPlugin",
    permissions = {
        @Permission(
            strings = { Manifest.permission.POST_NOTIFICATIONS },
            alias = "notifications"
        )
    }
)
public class PermissionsPlugin extends Plugin {
    private static final String TAG = "PermissionsPlugin";

    /**
     * Solicita permisos de notificaciones (Android 13+).
     */
    @PluginMethod
    public void requestNotificationPermission(PluginCall call) {
        Log.d(TAG, "📋 requestNotificationPermission() llamado");

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            // Android 13+ requiere solicitud runtime
            requestPermissionForAlias("notifications", call, "notificationPermissionCallback");
        } else {
            // Android 12 y anteriores: permiso concedido automáticamente
            Log.d(TAG, "✅ Android < 13: Permiso de notificaciones no requerido");
            JSObject result = new JSObject();
            result.put("granted", true);
            result.put("message", "No se requiere en esta versión de Android");
            call.resolve(result);
        }
    }

    /**
     * Callback para el resultado de requestNotificationPermission.
     */
    @PluginMethod
    public void notificationPermissionCallback(PluginCall call) {
        Log.d(TAG, "🔔 notificationPermissionCallback() llamado");

        // Verificar el resultado del permiso
        Context context = getContext();
        NotificationManager notificationManager =
            (NotificationManager) context.getSystemService(Context.NOTIFICATION_SERVICE);

        boolean granted = true;
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.N) {
            if (notificationManager != null) {
                granted = notificationManager.areNotificationsEnabled();
            }
        }

        if (granted) {
            Log.d(TAG, "✅ Permiso de notificaciones otorgado");
            JSObject result = new JSObject();
            result.put("granted", true);
            call.resolve(result);
        } else {
            Log.d(TAG, "❌ Permiso de notificaciones denegado");
            JSObject result = new JSObject();
            result.put("granted", false);
            result.put("message", "El usuario denegó el permiso de notificaciones");
            call.resolve(result);
        }
    }

    /**
     * Verifica si los permisos de notificaciones están otorgados.
     */
    @PluginMethod
    public void checkNotificationPermission(PluginCall call) {
        Log.d(TAG, "🔍 checkNotificationPermission() llamado");

        try {
            Context context = getContext();
            NotificationManager notificationManager =
                (NotificationManager) context.getSystemService(Context.NOTIFICATION_SERVICE);

            boolean granted = true;

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.N) {
                if (notificationManager != null) {
                    granted = notificationManager.areNotificationsEnabled();
                }
            }

            Log.d(TAG, "📊 Notificaciones habilitadas: " + granted);

            JSObject result = new JSObject();
            result.put("granted", granted);
            call.resolve(result);

        } catch (Exception e) {
            Log.e(TAG, "❌ Error al verificar permisos: " + e.getMessage(), e);
            call.reject("Error al verificar permisos: " + e.getMessage());
        }
    }

    /**
     * Verifica si la app está exenta de optimización de batería.
     */
    @PluginMethod
    public void checkBatteryOptimization(PluginCall call) {
        Log.d(TAG, "🔋 checkBatteryOptimization() llamado");

        try {
            boolean isIgnoring = false;

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                Context context = getContext();
                PowerManager powerManager = (PowerManager) context.getSystemService(Context.POWER_SERVICE);
                String packageName = context.getPackageName();

                if (powerManager != null) {
                    isIgnoring = powerManager.isIgnoringBatteryOptimizations(packageName);
                }
            } else {
                // Android 5 y anteriores: no hay optimización de batería
                isIgnoring = true;
            }

            Log.d(TAG, "📊 Ignorando optimización de batería: " + isIgnoring);

            JSObject result = new JSObject();
            result.put("isIgnoring", isIgnoring);
            result.put("message", isIgnoring ?
                "La app está exenta de optimización de batería" :
                "La app está sujeta a optimización de batería");
            call.resolve(result);

        } catch (Exception e) {
            Log.e(TAG, "❌ Error al verificar optimización de batería: " + e.getMessage(), e);
            call.reject("Error al verificar optimización de batería: " + e.getMessage());
        }
    }

    /**
     * Abre la configuración para desactivar la optimización de batería.
     */
    @PluginMethod
    public void requestBatteryOptimizationExemption(PluginCall call) {
        Log.d(TAG, "⚙️ requestBatteryOptimizationExemption() llamado");

        try {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                Context context = getContext();
                String packageName = context.getPackageName();

                Intent intent = new Intent();
                intent.setAction(Settings.ACTION_REQUEST_IGNORE_BATTERY_OPTIMIZATIONS);
                intent.setData(Uri.parse("package:" + packageName));
                intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);

                context.startActivity(intent);

                Log.d(TAG, "✅ Configuración de batería abierta");

                JSObject result = new JSObject();
                result.put("success", true);
                result.put("message", "Configuración de batería abierta");
                call.resolve(result);
            } else {
                Log.d(TAG, "ℹ️ No se requiere en Android < 6");
                JSObject result = new JSObject();
                result.put("success", true);
                result.put("message", "No se requiere en esta versión de Android");
                call.resolve(result);
            }

        } catch (Exception e) {
            Log.e(TAG, "❌ Error al abrir configuración de batería: " + e.getMessage(), e);
            call.reject("Error al abrir configuración de batería: " + e.getMessage());
        }
    }

    /**
     * Abre la configuración de la aplicación.
     */
    @PluginMethod
    public void openAppSettings(PluginCall call) {
        Log.d(TAG, "⚙️ openAppSettings() llamado");

        try {
            Context context = getContext();
            Intent intent = new Intent();
            intent.setAction(Settings.ACTION_APPLICATION_DETAILS_SETTINGS);
            Uri uri = Uri.fromParts("package", context.getPackageName(), null);
            intent.setData(uri);
            intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);

            context.startActivity(intent);

            Log.d(TAG, "✅ Configuración de la app abierta");

            JSObject result = new JSObject();
            result.put("success", true);
            call.resolve(result);

        } catch (Exception e) {
            Log.e(TAG, "❌ Error al abrir configuración: " + e.getMessage(), e);
            call.reject("Error al abrir configuración: " + e.getMessage());
        }
    }

    /**
     * Verifica si la app puede acceder a la política de Do Not Disturb.
     */
    @PluginMethod
    public void checkDoNotDisturbAccess(PluginCall call) {
        Log.d(TAG, "🔕 checkDoNotDisturbAccess() llamado");

        try {
            boolean hasAccess = false;

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                Context context = getContext();
                NotificationManager notificationManager =
                    (NotificationManager) context.getSystemService(Context.NOTIFICATION_SERVICE);

                if (notificationManager != null) {
                    hasAccess = notificationManager.isNotificationPolicyAccessGranted();
                }
            }

            Log.d(TAG, "📊 Acceso a DND: " + hasAccess);

            JSObject result = new JSObject();
            result.put("hasAccess", hasAccess);
            call.resolve(result);

        } catch (Exception e) {
            Log.e(TAG, "❌ Error al verificar acceso DND: " + e.getMessage(), e);
            call.reject("Error al verificar acceso DND: " + e.getMessage());
        }
    }

    /**
     * Obtiene información del fabricante del dispositivo.
     */
    @PluginMethod
    public void getDeviceInfo(PluginCall call) {
        Log.d(TAG, "📱 getDeviceInfo() llamado");

        try {
            String manufacturer = Build.MANUFACTURER.toLowerCase();
            String model = Build.MODEL;
            int sdkVersion = Build.VERSION.SDK_INT;
            String androidVersion = Build.VERSION.RELEASE;

            Log.d(TAG, "📊 Fabricante: " + manufacturer);
            Log.d(TAG, "📊 Modelo: " + model);
            Log.d(TAG, "📊 Android: " + androidVersion + " (SDK " + sdkVersion + ")");

            JSObject result = new JSObject();
            result.put("manufacturer", manufacturer);
            result.put("model", model);
            result.put("sdkVersion", sdkVersion);
            result.put("androidVersion", androidVersion);

            // Detectar fabricantes problemáticos para alarmas
            boolean isProblematic = manufacturer.contains("xiaomi") ||
                                   manufacturer.contains("huawei") ||
                                   manufacturer.contains("oppo") ||
                                   manufacturer.contains("vivo") ||
                                   manufacturer.contains("oneplus");

            result.put("hasAggressiveBatterySaving", isProblematic);

            if (isProblematic) {
                result.put("warning", "Este fabricante tiene optimización de batería agresiva. " +
                    "Asegúrate de desactivar la optimización de batería para esta app.");
            }

            call.resolve(result);

        } catch (Exception e) {
            Log.e(TAG, "❌ Error al obtener info del dispositivo: " + e.getMessage(), e);
            call.reject("Error al obtener info del dispositivo: " + e.getMessage());
        }
    }
}
