package com.tidy.app;

import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.content.Intent;
import android.media.AudioAttributes;
import android.media.RingtoneManager;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.util.Log;
import androidx.core.app.NotificationCompat;
import com.getcapacitor.BridgeActivity;

/**
 * MainActivity de Tidy.
 *
 * IMPORTANTE: Los plugins personalizados deben registrarse ANTES de super.onCreate()
 * para que Capacitor los reconozca correctamente. Este es un requisito crítico
 * descubierto tras debugging exhaustivo.
 */
public class MainActivity extends BridgeActivity {

    private static final String TAG = "MainActivity";
    private static final String CHANNEL_ID = "alarms_native";

    @Override
    public void onCreate(Bundle savedInstanceState) {
        // ⚠️ CRÍTICO: Registrar plugins ANTES de super.onCreate()
        // Si se registran después, aparecerán en la lista de plugins
        // pero sus métodos devolverán "not implemented on android"
        registerPlugin(AlarmPlugin.class);
        registerPlugin(PermissionsPlugin.class);

        // Ahora sí, llamar al onCreate del padre
        super.onCreate(savedInstanceState);

        // Crear canal de notificaciones para alarmas al iniciar la app
        createAlarmNotificationChannel();

        // Manejar intents de alarmas (SET_ALARM, SHOW_ALARMS)
        handleAlarmIntent(getIntent());
    }

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
        handleAlarmIntent(intent);
    }

    /**
     * Maneja los intents relacionados con alarmas (SET_ALARM, SHOW_ALARMS).
     * Cuando el usuario abre la app desde configuración de alarmas del sistema,
     * redirige a la vista de alarmas de la app.
     */
    private void handleAlarmIntent(Intent intent) {
        if (intent == null) {
            return;
        }

        String action = intent.getAction();
        Log.d(TAG, "📨 Intent recibido: " + action);

        if ("android.intent.action.SET_ALARM".equals(action) ||
            "android.intent.action.SHOW_ALARMS".equals(action)) {
            Log.d(TAG, "🔔 Intent de alarma detectado - Usuario viene desde configuración del sistema");
            // El router de Vue manejará la redirección a /Alarms
            // Solo logueamos para debug
        }
    }

    /**
     * Crea el canal de notificaciones para alarmas con máxima prioridad.
     * Se ejecuta al iniciar la app para asegurar que el canal existe
     * con la configuración correcta antes de que se dispare cualquier alarma.
     */
    private void createAlarmNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            try {
                NotificationManager notificationManager =
                    (NotificationManager) getSystemService(NOTIFICATION_SERVICE);

                if (notificationManager == null) {
                    Log.e(TAG, "❌ NotificationManager no disponible");
                    return;
                }

                // Verificar si el canal ya existe
                NotificationChannel existingChannel = notificationManager.getNotificationChannel(CHANNEL_ID);
                if (existingChannel != null) {
                    Log.d(TAG, "ℹ️ Canal de alarmas ya existe");
                    return;
                }

                // Crear canal de notificación para alarmas
                NotificationChannel channel = new NotificationChannel(
                    CHANNEL_ID,
                    "Alarmas Nativas",
                    NotificationManager.IMPORTANCE_MAX
                );

                channel.setDescription("Alarmas programadas con máxima prioridad");
                channel.enableVibration(true);
                channel.setVibrationPattern(new long[]{0, 500, 200, 500});
                channel.enableLights(true);
                channel.setLightColor(0xFFFF0000); // Rojo
                channel.setLockscreenVisibility(NotificationCompat.VISIBILITY_PUBLIC);
                channel.setShowBadge(true);
                channel.setBypassDnd(true); // Omitir "No molestar"
                channel.setImportance(NotificationManager.IMPORTANCE_MAX);

                // Configurar sonido de alarma
                Uri alarmSoundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_ALARM);
                if (alarmSoundUri == null) {
                    alarmSoundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);
                }

                AudioAttributes audioAttributes = new AudioAttributes.Builder()
                    .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                    .setUsage(AudioAttributes.USAGE_ALARM)
                    .build();

                channel.setSound(alarmSoundUri, audioAttributes);

                // Crear el canal
                notificationManager.createNotificationChannel(channel);
                Log.d(TAG, "✅ Canal de alarmas creado exitosamente al iniciar la app");

            } catch (Exception e) {
                Log.e(TAG, "❌ Error al crear canal de notificación: " + e.getMessage(), e);
            }
        }
    }
}
