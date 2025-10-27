package com.tidy.app;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.util.Log;

/**
 * BroadcastReceiver que se ejecuta cuando el dispositivo se reinicia
 * para reprogramar las alarmas que se perdieron durante el reinicio.
 */
public class BootReceiver extends BroadcastReceiver {
    private static final String TAG = "BootReceiver";

    @Override
    public void onReceive(Context context, Intent intent) {
        String action = intent.getAction();
        Log.d(TAG, "Received broadcast: " + action);

        if (Intent.ACTION_BOOT_COMPLETED.equals(action) ||
            "android.intent.action.QUICKBOOT_POWERON".equals(action)) {

            Log.d(TAG, "Device booted - Alarms will be rescheduled by Capacitor Local Notifications plugin");

            // El plugin @capacitor/local-notifications v6.x automáticamente
            // reprograma las notificaciones pendientes después del reinicio,
            // por lo que no necesitamos hacer nada manualmente aquí.
            //
            // Si necesitas lógica personalizada, puedes agregar código aquí
            // para lanzar un servicio o actividad que reprograme las alarmas.

            // Opcionalmente, puedes lanzar la app en background para
            // que reprograme las alarmas:
            // Intent launchIntent = new Intent(context, MainActivity.class);
            // launchIntent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
            // context.startActivity(launchIntent);
        }
    }
}
