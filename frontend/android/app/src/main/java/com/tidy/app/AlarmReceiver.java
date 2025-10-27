package com.tidy.app;

import android.app.AlarmManager;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.media.AudioAttributes;
import android.media.RingtoneManager;
import android.net.Uri;
import android.os.Build;
import android.os.PowerManager;
import android.os.Vibrator;
import android.util.Log;
import androidx.core.app.NotificationCompat;

/**
 * BroadcastReceiver que recibe las alarmas programadas por AlarmManager
 * y muestra notificaciones con sonido, vibración y pantalla completa.
 */
public class AlarmReceiver extends BroadcastReceiver {
    private static final String TAG = "AlarmReceiver";
    private static final String CHANNEL_ID = "alarms_native";
    private static final int NOTIFICATION_IMPORTANCE = NotificationManager.IMPORTANCE_MAX;

    @Override
    public void onReceive(Context context, Intent intent) {
        Log.d(TAG, "🔔 AlarmReceiver.onReceive() - Alarma recibida");

        // Obtener datos de la alarma
        int alarmId = intent.getIntExtra("alarm_id", 0);
        String title = intent.getStringExtra("title");
        String message = intent.getStringExtra("message");
        long triggerTime = intent.getLongExtra("trigger_time", System.currentTimeMillis());
        boolean isRecurring = intent.getBooleanExtra("is_recurring", false);
        String frequency = intent.getStringExtra("frequency");
        String repeatDays = intent.getStringExtra("repeat_days");

        Log.d(TAG, "📋 Datos de la alarma:");
        Log.d(TAG, "  - ID: " + alarmId);
        Log.d(TAG, "  - Title: " + title);
        Log.d(TAG, "  - Message: " + message);
        Log.d(TAG, "  - Trigger Time: " + new java.util.Date(triggerTime));
        Log.d(TAG, "  - Is Recurring: " + isRecurring);
        Log.d(TAG, "  - Frequency: " + frequency);

        // Despertar dispositivo
        wakeUpDevice(context);

        // Crear y mostrar notificación
        showAlarmNotification(context, alarmId, title, message);

        // IMPORTANTE: Iniciar AlarmActivity en pantalla completa
        // Esto muestra la alarma con sonido y vibración
        Intent alarmIntent = new Intent(context, AlarmActivity.class);
        alarmIntent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        alarmIntent.putExtra("alarmId", alarmId);
        alarmIntent.putExtra("label", title != null ? title : "Alarma Tidy");
        context.startActivity(alarmIntent);

        Log.d(TAG, "✅ AlarmActivity iniciada");

        // 🔄 REPROGRAMAR si es recurrente
        if (isRecurring && frequency != null) {
            rescheduleRecurringAlarm(context, alarmId, title, message, triggerTime, frequency, repeatDays);
        }

        Log.d(TAG, "✅ Alarma procesada correctamente");
    }

    /**
     * Despierta el dispositivo brevemente para mostrar la alarma.
     */
    private void wakeUpDevice(Context context) {
        try {
            PowerManager powerManager = (PowerManager) context.getSystemService(Context.POWER_SERVICE);
            if (powerManager != null) {
                PowerManager.WakeLock wakeLock = powerManager.newWakeLock(
                    PowerManager.PARTIAL_WAKE_LOCK | PowerManager.ACQUIRE_CAUSES_WAKEUP,
                    "Tidy::AlarmWakeLock"
                );
                wakeLock.acquire(10000); // 10 segundos
                Log.d(TAG, "📱 Dispositivo despertado");

                // Liberar después de un delay
                new android.os.Handler().postDelayed(() -> {
                    if (wakeLock.isHeld()) {
                        wakeLock.release();
                        Log.d(TAG, "📱 WakeLock liberado");
                    }
                }, 10000);
            }
        } catch (Exception e) {
            Log.e(TAG, "❌ Error al despertar dispositivo: " + e.getMessage(), e);
        }
    }

    /**
     * Nota: La vibración se maneja ahora en AlarmActivity
     * para tener control sobre cuándo detenerla.
     */

    /**
     * Crea y muestra una notificación de alarma con máxima prioridad.
     */
    private void showAlarmNotification(Context context, int alarmId, String title, String message) {
        try {
            NotificationManager notificationManager =
                (NotificationManager) context.getSystemService(Context.NOTIFICATION_SERVICE);

            if (notificationManager == null) {
                Log.e(TAG, "❌ NotificationManager no disponible");
                return;
            }

            // Crear canal de notificación (Android 8+)
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                createNotificationChannel(context, notificationManager);
            }

            // URI del sonido de alarma
            Uri alarmSoundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_ALARM);
            if (alarmSoundUri == null) {
                alarmSoundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);
            }
            Log.d(TAG, "🔊 Sonido de alarma: " + alarmSoundUri);

            // Intent para abrir la app al tocar la notificación
            Intent contentIntent = new Intent(context, MainActivity.class);
            contentIntent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
            contentIntent.putExtra("alarm_id", alarmId);
            contentIntent.putExtra("from_notification", true);

            int pendingIntentFlags = PendingIntent.FLAG_UPDATE_CURRENT;
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
                pendingIntentFlags |= PendingIntent.FLAG_IMMUTABLE;
            }

            PendingIntent pendingIntent = PendingIntent.getActivity(
                context,
                alarmId,
                contentIntent,
                pendingIntentFlags
            );

            // Construir notificación
            NotificationCompat.Builder builder = new NotificationCompat.Builder(context, CHANNEL_ID)
                .setSmallIcon(getNotificationIcon(context))
                .setContentTitle(title != null ? title : "Alarma Tidy")
                .setContentText(message != null ? message : "Es hora de tu alarma")
                .setPriority(NotificationCompat.PRIORITY_MAX)
                .setCategory(NotificationCompat.CATEGORY_ALARM)
                .setSound(alarmSoundUri)
                .setVibrate(new long[]{0, 500, 200, 500})
                .setLights(0xFFFF0000, 1000, 500) // Luz roja intermitente
                .setAutoCancel(true)
                .setContentIntent(pendingIntent)
                .setVisibility(NotificationCompat.VISIBILITY_PUBLIC) // Mostrar en pantalla bloqueada
                .setOngoing(false); // No mantener permanentemente

            // Full-screen intent para pantalla bloqueada (Android 10+)
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
                Intent fullScreenIntent = new Intent(context, MainActivity.class);
                fullScreenIntent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                fullScreenIntent.putExtra("alarm_id", alarmId);
                fullScreenIntent.putExtra("full_screen", true);

                PendingIntent fullScreenPendingIntent = PendingIntent.getActivity(
                    context,
                    alarmId + 10000, // ID diferente para evitar conflictos
                    fullScreenIntent,
                    pendingIntentFlags
                );

                builder.setFullScreenIntent(fullScreenPendingIntent, true);
                Log.d(TAG, "📱 Full-screen intent configurado");
            }

            // Mostrar notificación
            notificationManager.notify(alarmId, builder.build());
            Log.d(TAG, "✅ Notificación mostrada con ID: " + alarmId);

        } catch (Exception e) {
            Log.e(TAG, "❌ Error al mostrar notificación: " + e.getMessage(), e);
        }
    }

    /**
     * Crea el canal de notificaciones para alarmas (Android 8+).
     */
    private void createNotificationChannel(Context context, NotificationManager notificationManager) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            try {
                // Verificar si el canal ya existe
                NotificationChannel existingChannel = notificationManager.getNotificationChannel(CHANNEL_ID);
                if (existingChannel != null) {
                    Log.d(TAG, "ℹ️ Canal de notificación ya existe");
                    return;
                }

                // Crear nuevo canal
                NotificationChannel channel = new NotificationChannel(
                    CHANNEL_ID,
                    "Alarmas Nativas",
                    NOTIFICATION_IMPORTANCE
                );

                channel.setDescription("Alarmas programadas con máxima prioridad");
                channel.enableVibration(true);
                channel.setVibrationPattern(new long[]{0, 500, 200, 500});
                channel.enableLights(true);
                channel.setLightColor(0xFFFF0000); // Rojo
                channel.setLockscreenVisibility(NotificationCompat.VISIBILITY_PUBLIC);
                channel.setShowBadge(true);
                channel.setBypassDnd(true); // Omitir "No molestar" para alarmas
                channel.setImportance(NotificationManager.IMPORTANCE_MAX); // Máxima prioridad

                // Configurar sonido
                Uri alarmSoundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_ALARM);
                if (alarmSoundUri == null) {
                    alarmSoundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);
                }

                AudioAttributes audioAttributes = new AudioAttributes.Builder()
                    .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                    .setUsage(AudioAttributes.USAGE_ALARM)
                    .build();

                channel.setSound(alarmSoundUri, audioAttributes);

                notificationManager.createNotificationChannel(channel);
                Log.d(TAG, "✅ Canal de notificación creado: " + CHANNEL_ID);

            } catch (Exception e) {
                Log.e(TAG, "❌ Error al crear canal de notificación: " + e.getMessage(), e);
            }
        }
    }

    /**
     * Obtiene el icono de notificación correcto.
     */
    private int getNotificationIcon(Context context) {
        // Intentar usar el icono personalizado de alarma
        int iconId = context.getResources().getIdentifier("ic_stat_alarm", "drawable", context.getPackageName());

        // Si no existe, usar el icono del launcher
        if (iconId == 0) {
            iconId = context.getApplicationInfo().icon;
        }

        return iconId;
    }

    /**
     * Reprograma una alarma recurrente para la próxima ocurrencia.
     *
     * @param context Contexto de la aplicación
     * @param alarmId ID de la alarma
     * @param title Título de la alarma
     * @param message Mensaje de la alarma
     * @param originalTriggerTime Hora del disparo original
     * @param frequency Frecuencia (diaria, semanal, mensual, anual)
     * @param repeatDays Días de repetición (solo para semanal, JSON array)
     */
    private void rescheduleRecurringAlarm(Context context, int alarmId, String title, String message,
                                          long originalTriggerTime, String frequency, String repeatDays) {
        try {
            Log.d(TAG, "🔄 Reprogramando alarma recurrente ID: " + alarmId);

            java.util.Calendar calendar = java.util.Calendar.getInstance();
            calendar.setTimeInMillis(originalTriggerTime);

            // Calcular próxima ocurrencia según frecuencia
            switch (frequency) {
                case "diaria":
                    // Agregar 1 día
                    calendar.add(java.util.Calendar.DAY_OF_MONTH, 1);
                    Log.d(TAG, "  📅 Próxima ocurrencia (diaria): " + calendar.getTime());
                    break;

                case "semanal":
                    // Agregar 7 días
                    // TODO: Si hay repeatDays específicos, calcular próximo día válido
                    calendar.add(java.util.Calendar.DAY_OF_MONTH, 7);
                    Log.d(TAG, "  📅 Próxima ocurrencia (semanal): " + calendar.getTime());
                    break;

                case "mensual":
                    // Agregar 1 mes
                    calendar.add(java.util.Calendar.MONTH, 1);
                    Log.d(TAG, "  📅 Próxima ocurrencia (mensual): " + calendar.getTime());
                    break;

                case "anual":
                    // Agregar 1 año
                    calendar.add(java.util.Calendar.YEAR, 1);
                    Log.d(TAG, "  📅 Próxima ocurrencia (anual): " + calendar.getTime());
                    break;

                default:
                    Log.w(TAG, "⚠️ Frecuencia desconocida: " + frequency);
                    return;
            }

            long nextTriggerTime = calendar.getTimeInMillis();

            // Crear intent para la próxima alarma
            Intent intent = new Intent(context, AlarmReceiver.class);
            intent.putExtra("alarm_id", alarmId);
            intent.putExtra("title", title);
            intent.putExtra("message", message);
            intent.putExtra("trigger_time", nextTriggerTime);
            intent.putExtra("is_recurring", true);
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

            // Obtener AlarmManager
            AlarmManager alarmManager = (AlarmManager) context.getSystemService(Context.ALARM_SERVICE);

            if (alarmManager == null) {
                Log.e(TAG, "❌ AlarmManager no disponible");
                return;
            }

            // Programar próxima alarma
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                alarmManager.setExactAndAllowWhileIdle(
                    AlarmManager.RTC_WAKEUP,
                    nextTriggerTime,
                    pendingIntent
                );
            } else {
                alarmManager.setExact(
                    AlarmManager.RTC_WAKEUP,
                    nextTriggerTime,
                    pendingIntent
                );
            }

            Log.d(TAG, "✅ Alarma recurrente reprogramada para: " + calendar.getTime());

        } catch (Exception e) {
            Log.e(TAG, "❌ Error al reprogramar alarma recurrente: " + e.getMessage(), e);
        }
    }
}
