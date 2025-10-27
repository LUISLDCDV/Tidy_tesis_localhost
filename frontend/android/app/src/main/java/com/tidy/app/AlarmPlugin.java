package com.tidy.app;

import android.app.AlarmManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.os.Build;
import android.util.Log;
import com.getcapacitor.Plugin;
import com.getcapacitor.PluginCall;
import com.getcapacitor.PluginMethod;
import com.getcapacitor.annotation.CapacitorPlugin;
import com.getcapacitor.JSObject;
import java.util.Calendar;

/**
 * Plugin personalizado para programar alarmas usando AlarmManager nativo de Android.
 *
 * Este plugin garantiza que las alarmas funcionen incluso cuando la app está cerrada,
 * usando el AlarmManager del sistema operativo en lugar de solo notificaciones.
 */
@CapacitorPlugin(name = "AlarmPlugin")
public class AlarmPlugin extends Plugin {
    private static final String TAG = "AlarmPlugin";

    /**
     * Programa una alarma usando AlarmManager nativo.
     *
     * @param call - Objeto con los datos de la alarma:
     *   - id: ID único de la alarma (int)
     *   - triggerTime: Timestamp en milisegundos (long)
     *   - title: Título de la alarma (String)
     *   - message: Mensaje de la alarma (String)
     *   - repeatDays: Array de días para repetir [0-6] (opcional)
     */
    @PluginMethod
    public void scheduleAlarm(PluginCall call) {
        Log.d(TAG, "📅 scheduleAlarm() llamado");

        // Obtener parámetros
        Integer alarmId = call.getInt("id");
        Long triggerTime = call.getLong("triggerTime");
        String title = call.getString("title", "Alarma Tidy");
        String message = call.getString("message", "Es hora de tu alarma");

        // Validar parámetros obligatorios
        if (alarmId == null || triggerTime == null) {
            Log.e(TAG, "❌ Faltan parámetros: id=" + alarmId + ", triggerTime=" + triggerTime);
            call.reject("Faltan parámetros obligatorios: id y triggerTime");
            return;
        }

        Log.d(TAG, "📋 Parámetros recibidos:");
        Log.d(TAG, "  - ID: " + alarmId);
        Log.d(TAG, "  - TriggerTime: " + triggerTime + " (" + new java.util.Date(triggerTime) + ")");
        Log.d(TAG, "  - Title: " + title);
        Log.d(TAG, "  - Message: " + message);

        try {
            Context context = getContext();
            AlarmManager alarmManager = (AlarmManager) context.getSystemService(Context.ALARM_SERVICE);

            if (alarmManager == null) {
                Log.e(TAG, "❌ AlarmManager no disponible");
                call.reject("AlarmManager no disponible");
                return;
            }

            // Obtener datos de recurrencia
            Boolean isRecurring = call.getBoolean("isRecurring", false);
            String frequency = call.getString("frequency");
            String repeatDays = call.getString("repeatDays");

            // Crear intent para AlarmReceiver
            Intent intent = new Intent(context, AlarmReceiver.class);
            intent.putExtra("alarm_id", alarmId);
            intent.putExtra("title", title);
            intent.putExtra("message", message);
            intent.putExtra("trigger_time", triggerTime);
            intent.putExtra("is_recurring", isRecurring);
            intent.putExtra("frequency", frequency);
            intent.putExtra("repeat_days", repeatDays);

            // Crear PendingIntent
            int flags = PendingIntent.FLAG_UPDATE_CURRENT;
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
                flags |= PendingIntent.FLAG_IMMUTABLE;
            }

            PendingIntent pendingIntent = PendingIntent.getBroadcast(
                context,
                alarmId,
                intent,
                flags
            );

            // Programar alarma según versión de Android
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
                // Android 12+ - Verificar permiso para alarmas exactas
                if (alarmManager.canScheduleExactAlarms()) {
                    alarmManager.setExactAndAllowWhileIdle(
                        AlarmManager.RTC_WAKEUP,
                        triggerTime,
                        pendingIntent
                    );
                    Log.d(TAG, "✅ Alarma programada con setExactAndAllowWhileIdle() [Android 12+]");
                } else {
                    Log.e(TAG, "❌ No hay permiso para alarmas exactas");
                    call.reject("Permiso de alarmas exactas no otorgado. Ve a Configuración > Aplicaciones > Tidy > Alarmas y recordatorios");
                    return;
                }
            } else if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                // Android 6+ - Con soporte para Doze mode
                alarmManager.setExactAndAllowWhileIdle(
                    AlarmManager.RTC_WAKEUP,
                    triggerTime,
                    pendingIntent
                );
                Log.d(TAG, "✅ Alarma programada con setExactAndAllowWhileIdle() [Android 6+]");
            } else {
                // Android 5 y anteriores
                alarmManager.setExact(
                    AlarmManager.RTC_WAKEUP,
                    triggerTime,
                    pendingIntent
                );
                Log.d(TAG, "✅ Alarma programada con setExact() [Android 5]");
            }

            JSObject result = new JSObject();
            result.put("success", true);
            result.put("alarmId", alarmId);
            result.put("scheduledFor", new java.util.Date(triggerTime).toString());
            call.resolve(result);

        } catch (Exception e) {
            Log.e(TAG, "❌ Error al programar alarma: " + e.getMessage(), e);
            call.reject("Error al programar alarma: " + e.getMessage());
        }
    }

    /**
     * Cancela una alarma programada.
     *
     * @param call - Objeto con el ID de la alarma a cancelar
     */
    @PluginMethod
    public void cancelAlarm(PluginCall call) {
        Log.d(TAG, "🗑️ cancelAlarm() llamado");

        Integer alarmId = call.getInt("id");

        if (alarmId == null) {
            Log.e(TAG, "❌ Falta parámetro: id");
            call.reject("Falta parámetro obligatorio: id");
            return;
        }

        Log.d(TAG, "📋 Cancelando alarma ID: " + alarmId);

        try {
            Context context = getContext();
            AlarmManager alarmManager = (AlarmManager) context.getSystemService(Context.ALARM_SERVICE);

            if (alarmManager == null) {
                Log.e(TAG, "❌ AlarmManager no disponible");
                call.reject("AlarmManager no disponible");
                return;
            }

            // Crear intent idéntico al usado para programar
            Intent intent = new Intent(context, AlarmReceiver.class);

            int flags = PendingIntent.FLAG_UPDATE_CURRENT;
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
                flags |= PendingIntent.FLAG_IMMUTABLE;
            }

            PendingIntent pendingIntent = PendingIntent.getBroadcast(
                context,
                alarmId,
                intent,
                flags
            );

            // Cancelar la alarma
            alarmManager.cancel(pendingIntent);
            pendingIntent.cancel();

            Log.d(TAG, "✅ Alarma " + alarmId + " cancelada correctamente");

            JSObject result = new JSObject();
            result.put("success", true);
            result.put("alarmId", alarmId);
            call.resolve(result);

        } catch (Exception e) {
            Log.e(TAG, "❌ Error al cancelar alarma: " + e.getMessage(), e);
            call.reject("Error al cancelar alarma: " + e.getMessage());
        }
    }

    /**
     * Verifica si la app puede programar alarmas exactas (Android 12+).
     */
    @PluginMethod
    public void canScheduleExactAlarms(PluginCall call) {
        Log.d(TAG, "🔍 canScheduleExactAlarms() llamado");

        try {
            boolean canSchedule = true;

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
                Context context = getContext();
                AlarmManager alarmManager = (AlarmManager) context.getSystemService(Context.ALARM_SERVICE);

                if (alarmManager != null) {
                    canSchedule = alarmManager.canScheduleExactAlarms();
                }
            }

            Log.d(TAG, "📊 Puede programar alarmas exactas: " + canSchedule);

            JSObject result = new JSObject();
            result.put("canSchedule", canSchedule);
            call.resolve(result);

        } catch (Exception e) {
            Log.e(TAG, "❌ Error al verificar permisos: " + e.getMessage(), e);
            call.reject("Error al verificar permisos: " + e.getMessage());
        }
    }

    /**
     * Abre la configuración de alarmas exactas (Android 12+).
     */
    @PluginMethod
    public void openExactAlarmSettings(PluginCall call) {
        Log.d(TAG, "⚙️ openExactAlarmSettings() llamado");

        try {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
                Context context = getContext();
                Intent intent = new Intent(android.provider.Settings.ACTION_REQUEST_SCHEDULE_EXACT_ALARM);
                intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                context.startActivity(intent);

                Log.d(TAG, "✅ Configuración de alarmas exactas abierta");

                JSObject result = new JSObject();
                result.put("success", true);
                call.resolve(result);
            } else {
                Log.d(TAG, "ℹ️ No se requiere en Android < 12");
                JSObject result = new JSObject();
                result.put("success", true);
                result.put("message", "No se requiere en esta versión de Android");
                call.resolve(result);
            }

        } catch (Exception e) {
            Log.e(TAG, "❌ Error al abrir configuración: " + e.getMessage(), e);
            call.reject("Error al abrir configuración: " + e.getMessage());
        }
    }
}
