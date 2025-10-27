package com.tidy.app;

import android.app.AlarmManager;
import android.app.KeyguardManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.media.AudioAttributes;
import android.media.MediaPlayer;
import android.media.RingtoneManager;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.os.VibrationEffect;
import android.os.Vibrator;
import android.util.Log;
import android.view.View;
import android.view.WindowManager;
import android.widget.Button;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;

/**
 * Actividad de pantalla completa que se muestra cuando suena una alarma.
 * Se muestra incluso cuando el teléfono está bloqueado.
 * Reproduce sonido y vibración continuamente hasta que el usuario la detenga.
 */
public class AlarmActivity extends AppCompatActivity {

    private static final String TAG = "AlarmActivity";
    private MediaPlayer mediaPlayer;
    private Vibrator vibrator;
    private int alarmId;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        Log.d(TAG, "🔔 AlarmActivity.onCreate() - Mostrando alarma en pantalla completa");

        // Mostrar alarma incluso cuando el teléfono está bloqueado
        setupLockscreenDisplay();

        setContentView(R.layout.activity_alarm);

        // Obtener datos de la alarma
        alarmId = getIntent().getIntExtra("alarmId", 0);
        String label = getIntent().getStringExtra("label");
        if (label == null || label.isEmpty()) {
            label = "Alarma Tidy";
        }

        Log.d(TAG, "📋 Alarma ID: " + alarmId + ", Label: " + label);

        // Configurar UI
        TextView timeText = findViewById(R.id.alarm_time);
        TextView labelText = findViewById(R.id.alarm_label);
        Button dismissButton = findViewById(R.id.dismiss_button);
        Button snoozeButton = findViewById(R.id.snooze_button);

        // Mostrar hora actual
        SimpleDateFormat sdf = new SimpleDateFormat("HH:mm", Locale.getDefault());
        timeText.setText(sdf.format(new Date()));

        // Mostrar label
        labelText.setText(label);

        // Configurar botones
        dismissButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Log.d(TAG, "👆 Usuario presionó DETENER");
                dismissAlarm();
            }
        });

        snoozeButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Log.d(TAG, "👆 Usuario presionó POSPONER");
                snoozeAlarm();
            }
        });

        // Iniciar sonido y vibración
        playAlarmSound();
        startVibration();

        Log.d(TAG, "✅ AlarmActivity configurada correctamente");
    }

    /**
     * Configura la actividad para mostrarse sobre la pantalla de bloqueo.
     */
    private void setupLockscreenDisplay() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O_MR1) {
            // Android 8.1+
            setShowWhenLocked(true);
            setTurnScreenOn(true);
            KeyguardManager keyguardManager = (KeyguardManager) getSystemService(Context.KEYGUARD_SERVICE);
            if (keyguardManager != null) {
                keyguardManager.requestDismissKeyguard(this, null);
            }
            Log.d(TAG, "📱 Configurado para Android 8.1+ (setShowWhenLocked)");
        } else {
            // Android 8.0 y anteriores
            getWindow().addFlags(
                WindowManager.LayoutParams.FLAG_SHOW_WHEN_LOCKED |
                WindowManager.LayoutParams.FLAG_DISMISS_KEYGUARD |
                WindowManager.LayoutParams.FLAG_TURN_SCREEN_ON |
                WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON
            );
            Log.d(TAG, "📱 Configurado para Android 8.0- (window flags)");
        }
    }

    /**
     * Reproduce el sonido de alarma en loop.
     */
    private void playAlarmSound() {
        try {
            Uri alarmUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_ALARM);
            if (alarmUri == null) {
                alarmUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);
            }

            Log.d(TAG, "🔊 Reproduciendo sonido: " + alarmUri);

            mediaPlayer = new MediaPlayer();
            mediaPlayer.setDataSource(this, alarmUri);

            AudioAttributes audioAttributes = new AudioAttributes.Builder()
                .setUsage(AudioAttributes.USAGE_ALARM)
                .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                .build();

            mediaPlayer.setAudioAttributes(audioAttributes);
            mediaPlayer.setLooping(true); // Loop infinito
            mediaPlayer.prepare();
            mediaPlayer.start();

            Log.d(TAG, "✅ Sonido de alarma iniciado");
        } catch (IOException e) {
            Log.e(TAG, "❌ Error al reproducir sonido: " + e.getMessage(), e);
        }
    }

    /**
     * Inicia vibración continua.
     */
    private void startVibration() {
        vibrator = (Vibrator) getSystemService(Context.VIBRATOR_SERVICE);
        if (vibrator != null && vibrator.hasVibrator()) {
            long[] pattern = {0, 1000, 500, 1000}; // Vibrar 1s, pausa 0.5s, repetir

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                vibrator.vibrate(VibrationEffect.createWaveform(pattern, 0)); // 0 = repetir desde inicio
            } else {
                vibrator.vibrate(pattern, 0);
            }

            Log.d(TAG, "📳 Vibración iniciada");
        } else {
            Log.w(TAG, "⚠️ Vibrator no disponible");
        }
    }

    /**
     * Detiene la alarma y cierra la actividad.
     */
    private void dismissAlarm() {
        Log.d(TAG, "🛑 Deteniendo alarma...");
        stopAlarm();
        finish();
    }

    /**
     * Pospone la alarma por 10 minutos.
     * TODO: Implementar lógica de reprogramación.
     */
    private void snoozeAlarm() {
        Log.d(TAG, "⏰ Posponiendo alarma por 10 minutos...");
        stopAlarm();

        try {
            // Calcular tiempo para 10 minutos en el futuro
            long snoozeTime = System.currentTimeMillis() + (10 * 60 * 1000); // 10 minutos

            // Crear intent para AlarmReceiver
            Intent intent = new Intent(this, AlarmReceiver.class);
            intent.putExtra("alarm_id", alarmId + 1000000); // ID temporal diferente
            intent.putExtra("title", "Alarma Pospuesta");
            intent.putExtra("message", "Pospuesta por 10 minutos");
            intent.putExtra("trigger_time", snoozeTime);
            intent.putExtra("is_recurring", false); // No repetir el snooze

            // Crear PendingIntent
            int flags = PendingIntent.FLAG_UPDATE_CURRENT;
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
                flags |= PendingIntent.FLAG_IMMUTABLE;
            }

            PendingIntent pendingIntent = PendingIntent.getBroadcast(
                this,
                alarmId + 1000000,
                intent,
                flags
            );

            // Obtener AlarmManager
            AlarmManager alarmManager = (AlarmManager) getSystemService(ALARM_SERVICE);

            if (alarmManager != null) {
                // Programar alarma pospuesta
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                    alarmManager.setExactAndAllowWhileIdle(
                        AlarmManager.RTC_WAKEUP,
                        snoozeTime,
                        pendingIntent
                    );
                } else {
                    alarmManager.setExact(
                        AlarmManager.RTC_WAKEUP,
                        snoozeTime,
                        pendingIntent
                    );
                }

                Log.d(TAG, "✅ Alarma pospuesta para 10 minutos: " + new java.util.Date(snoozeTime));
            } else {
                Log.e(TAG, "❌ AlarmManager no disponible");
            }

        } catch (Exception e) {
            Log.e(TAG, "❌ Error al posponer alarma: " + e.getMessage(), e);
        }

        finish();
    }

    /**
     * Detiene sonido y vibración.
     */
    private void stopAlarm() {
        if (mediaPlayer != null) {
            try {
                mediaPlayer.stop();
                mediaPlayer.release();
                Log.d(TAG, "🔇 Sonido detenido");
            } catch (Exception e) {
                Log.e(TAG, "❌ Error al detener sonido: " + e.getMessage());
            }
            mediaPlayer = null;
        }

        if (vibrator != null) {
            vibrator.cancel();
            Log.d(TAG, "📴 Vibración detenida");
        }
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        Log.d(TAG, "💀 AlarmActivity.onDestroy()");
        stopAlarm();
    }

    @Override
    public void onBackPressed() {
        // Prevenir que el botón "atrás" cierre la alarma
        // El usuario DEBE presionar "Detener" o "Posponer"
        Log.d(TAG, "⚠️ Botón atrás bloqueado - Usuario debe detener o posponer");
    }
}
