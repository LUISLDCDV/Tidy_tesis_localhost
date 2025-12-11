import { LocalNotifications } from '@capacitor/local-notifications';
import { Capacitor } from '@capacitor/core';
import AlarmPlugin from '@/plugins/AlarmPlugin';
import PermissionsPlugin from '@/plugins/PermissionsPlugin';

class AlarmService {
  constructor() {
    this.isNative = Capacitor.isNativePlatform();
    this.isAndroid = Capacitor.getPlatform() === 'android';
    this.initialized = false;
    this.pollingInterval = null;
    this.alarmsToCheck = [];
    this.firedAlarms = new Set(); // Para no disparar la misma alarma múltiples veces
    this.useNativeAlarms = false; // Flag para usar AlarmManager nativo
  }

  async initialize() {
    if (!this.isNative || this.initialized) {
      return;
    }

    try {
      // En Android, intentar usar el plugin nativo
      if (this.isAndroid) {
        try {
          // Verificar si podemos programar alarmas exactas
          const canSchedule = await AlarmPlugin.canScheduleExactAlarms();
          console.log('🔍 Puede programar alarmas exactas:', canSchedule.canSchedule);

          if (canSchedule.canSchedule) {
            this.useNativeAlarms = true;
            console.log('✅ Usando AlarmManager nativo de Android');
          } else {
            console.warn('⚠️ No hay permiso para alarmas exactas. Usando LocalNotifications como fallback');
            // Intentar abrir configuración
            console.log('💡 Abriendo configuración de alarmas exactas...');
            await AlarmPlugin.openExactAlarmSettings();
          }
        } catch (pluginError) {
          console.warn('⚠️ Plugin nativo no disponible, usando LocalNotifications:', pluginError);
          this.useNativeAlarms = false;
        }
      }

      // Solicitar permisos de notificaciones
      const permission = await LocalNotifications.requestPermissions();

      if (permission.display === 'granted') {
        console.log('✅ Permisos de notificaciones otorgados');
        this.initialized = true;
      } else {
        console.warn('⚠️ Permisos de notificaciones denegados');
      }

      // En Android, verificar permisos adicionales
      if (this.isAndroid) {
        try {
          // Verificar optimización de batería
          const batteryStatus = await PermissionsPlugin.checkBatteryOptimization();
          console.log('🔋 Estado de optimización de batería:', batteryStatus);

          if (!batteryStatus.isIgnoring) {
            console.warn('⚠️ La app está sujeta a optimización de batería. Las alarmas pueden no funcionar correctamente.');
            console.log('💡 Solicita al usuario desactivar la optimización de batería');
          }

          // Obtener info del dispositivo
          const deviceInfo = await PermissionsPlugin.getDeviceInfo();
          console.log('📱 Info del dispositivo:', deviceInfo);

          if (deviceInfo.hasAggressiveBatterySaving) {
            console.warn('⚠️ ' + deviceInfo.warning);
          }
        } catch (permError) {
          console.warn('⚠️ No se pudieron verificar permisos adicionales:', permError);
        }
      }
    } catch (error) {
      console.error('❌ Error al inicializar alarmas:', error);
    }
  }

  /**
   * Programa una alarma/notificación
   * @param {Object} alarm - Datos de la alarma
   * @param {number} alarm.id - ID único de la alarma
   * @param {string} alarm.nombre - Título de la alarma
   * @param {string} alarm.descripcion - Descripción de la alarma
   * @param {string} alarm.fecha_hora - Fecha y hora en formato ISO o MySQL
   * @param {boolean} alarm.repetir - Si la alarma se repite
   * @param {string} alarm.frecuencia - Frecuencia de repetición (diaria, semanal, etc)
   */
  async scheduleAlarm(alarm) {
    if (!this.isNative) {
      console.log('📱 No estamos en plataforma nativa, usando alarma web');
      return this.scheduleWebAlarm(alarm);
    }

    if (!this.initialized) {
      await this.initialize();
    }

    if (!this.initialized) {
      console.error('❌ No se pudo inicializar el servicio de alarmas');
      return false;
    }

    try {
      const alarmDate = new Date(alarm.fecha_hora);
      const now = new Date();

      // Verificar que la fecha es futura
      if (alarmDate <= now) {
        console.warn('⚠️ La alarma está en el pasado, no se programará');
        return false;
      }

      // Verificar que la fecha es válida
      if (isNaN(alarmDate.getTime())) {
        console.error('❌ Fecha de alarma inválida:', alarm.fecha_hora);
        return false;
      }

      // ⚡ USAR PLUGIN NATIVO EN ANDROID
      if (this.isAndroid && this.useNativeAlarms) {
        return await this.scheduleNativeAlarm(alarm, alarmDate);
      }

      const testTime = new Date()
      testTime.setSeconds(testTime.getSeconds() + 10)
      
      const result = await AlarmPlugin.scheduleAlarm({
        id: 99999, // ID de prueba
        triggerTime: testTime.getTime(),
        title: 'Prueba de Alarma',
        message: '¡La alarma nativa funciona! 🎉'
      })

      const notification = {
        id: alarm.id,
        title: alarm.nombre || 'Alarma Tidy',
        body: alarm.descripcion || 'Es hora de tu alarma',
        schedule: {
          at: alarmDate,
          allowWhileIdle: true,  // Permite alarmas cuando el dispositivo está en reposo
          exact: true  // Fuerza alarmas exactas (Android 12+)
        },
        sound: 'alarm.mp3',  // Usar sonido de alarma (puedes usar 'default' o un archivo personalizado)
        channelId: 'alarms',  // Usar el canal de alarmas con máxima prioridad
        smallIcon: 'ic_stat_alarm',  // Icono pequeño en barra de estado
        largeIcon: 'ic_launcher',  // Icono grande en notificación expandida
        iconColor: '#FF0000',  // Color rojo para alarmas
        attachments: null,
        actionTypeId: '',
        // Configuraciones críticas para alarmas nativas
        ongoing: false,  // No mantener notificación permanente
        autoCancel: true,  // Auto-cancelar al tocar
        silent: false,  // NO silenciar (importante)
        vibrate: true,
        // Prioridad máxima
        importance: 5,  // IMPORTANCE_HIGH
        visibility: 1,  // VISIBILITY_PUBLIC - mostrar en pantalla bloqueada
        // Acción de pantalla completa (para mostrar sobre pantalla bloqueada)
        fullScreenIntent: true,
        // Datos extra
        extra: {
          alarmId: alarm.id,
          type: 'alarm',
          timestamp: alarmDate.getTime(),
          showFullScreen: true  // Flag para mostrar pantalla completa
        }
      };

      // Si es repetitiva, agregar configuración de repetición
      if (alarm.repetir && alarm.frecuencia) {
        notification.schedule.every = this.getRepeatInterval(alarm.frecuencia);
      }

      console.log(`📅 Programando alarma:`, {
        id: alarm.id,
        nombre: alarm.nombre,
        fechaHora: alarm.fecha_hora,
        fechaParsed: alarmDate.toISOString(),
        ahora: new Date().toISOString(),
        diffMs: alarmDate - now,
        diffMin: Math.floor((alarmDate - now) / 1000 / 60)
      });

      try {
        await LocalNotifications.schedule({
          notifications: [notification]
        });
        console.log(`✅ Alarma ${alarm.id} programada para ${alarmDate.toLocaleString()}`);
      } catch (scheduleError) {
        console.error('❌ Error al llamar LocalNotifications.schedule():', scheduleError);
        console.error('Detalles del error:', {
          name: scheduleError.name,
          message: scheduleError.message,
          stack: scheduleError.stack
        });
        throw scheduleError; // Re-lanzar para que sea capturado por el catch externo
      }

      // VERIFICAR que la alarma quedó programada
      try {
        const pending = await LocalNotifications.getPending();
        const alarmaProgramada = pending.notifications.find(n => n.id === alarm.id);

        if (alarmaProgramada) {
          console.log(`✅ CONFIRMADO: Alarma ${alarm.id} encontrada en pending:`, alarmaProgramada);
        } else {
          console.error(`❌ ERROR: Alarma ${alarm.id} NO aparece en pending. Total pendientes: ${pending.notifications.length}`);
          console.log('📋 Todas las alarmas pendientes:', pending.notifications);
          console.warn('⚠️ La alarma fue programada pero no aparece en la lista de pendientes. Esto puede ser normal en algunas versiones de Android.');
          // No retornamos false porque la alarma SÍ fue programada, solo no aparece en pending
        }
      } catch (pendingError) {
        console.warn('⚠️ No se pudo verificar alarmas pendientes:', pendingError);
        // No es crítico, la alarma ya fue programada
      }

      return true;

    } catch (error) {
      console.error('❌ Error al programar alarma:', error);
      console.error('Detalles de la alarma:', alarm);
      console.error('Stack trace completo:', error.stack);

      // Proveer información útil al usuario
      if (error.message && error.message.includes('permission')) {
        console.error('💡 Solución: Verifica que la app tiene permisos de notificaciones y alarmas exactas');
      } else if (error.message && error.message.includes('PendingIntent')) {
        console.error('💡 Solución: Puede ser un problema con Android 12+. Verifica los permisos SCHEDULE_EXACT_ALARM');
      }

      return false;
    }
  }

  /**
   * Programa una alarma usando el plugin nativo de Android (AlarmManager).
   * @param {Object} alarm - Datos de la alarma
   * @param {Date} alarmDate - Fecha de la alarma
   */
  async scheduleNativeAlarm(alarm, alarmDate) {
    try {
      console.log('⚡ Programando alarma con AlarmManager nativo');
      console.log('📋 Datos de alarma recibidos:', JSON.stringify(alarm, null, 2));
      console.log('📅 Fecha programada:', alarmDate.toISOString());
      console.log('⏰ Timestamp:', alarmDate.getTime());

      // SIMPLIFICADO: Igual que testAlarmNow que SÍ FUNCIONA
      // Solo pasar los 4 parámetros esenciales
      const baseAlarmData = {
        id: alarm.id,
        triggerTime: alarmDate.getTime(),
        title: alarm.nombre || 'Alarma Tidy',
        message: alarm.descripcion || 'Es hora de tu alarma'
      };

      console.log('📤 Datos enviados al plugin (SIMPLIFICADOS):', JSON.stringify(baseAlarmData, null, 2));

      const result = await AlarmPlugin.scheduleAlarm(baseAlarmData);

      console.log('📥 Respuesta del plugin:', JSON.stringify(result, null, 2));

      if (result.success) {
        console.log(`✅ Alarma nativa ${alarm.id} programada para ${result.scheduledFor}`);

        // TODO: Implementar recurrencia en el futuro si es necesario
        // Por ahora, solo programamos alarmas simples que funcionan

        return true;
      } else {
        console.error('❌ No se pudo programar la alarma nativa');
        return false;
      }
    } catch (error) {
      console.error('❌ Error al programar alarma nativa:', error);
      // Fallback a LocalNotifications
      console.log('⚠️ Intentando con LocalNotifications como fallback...');
      this.useNativeAlarms = false;
      return false;
    }
  }

  /**
   * Cancela una alarma programada
   * @param {number} alarmId - ID de la alarma a cancelar
   */
  async cancelAlarm(alarmId) {
    if (!this.isNative) {
      return this.cancelWebAlarm(alarmId);
    }

    try {
      // Si estamos usando alarmas nativas, cancelar con el plugin
      if (this.isAndroid && this.useNativeAlarms) {
        try {
          const result = await AlarmPlugin.cancelAlarm({ id: alarmId });
          if (result.success) {
            console.log(`✅ Alarma nativa ${alarmId} cancelada`);
            return true;
          }
        } catch (nativeError) {
          console.warn('⚠️ Error al cancelar alarma nativa, intentando con LocalNotifications:', nativeError);
        }
      }

      // Fallback o iOS: usar LocalNotifications
      await LocalNotifications.cancel({
        notifications: [{ id: alarmId }]
      });
      console.log(`✅ Alarma ${alarmId} cancelada`);
      return true;
    } catch (error) {
      console.error('❌ Error al cancelar alarma:', error);
      return false;
    }
  }

  /**
   * Cancela todas las alarmas
   */
  async cancelAllAlarms() {
    if (!this.isNative) {
      return this.cancelAllWebAlarms();
    }

    try {
      const pending = await LocalNotifications.getPending();
      if (pending.notifications.length > 0) {
        await LocalNotifications.cancel(pending);
        console.log(`✅ ${pending.notifications.length} alarmas canceladas`);
      }
      return true;
    } catch (error) {
      console.error('❌ Error al cancelar todas las alarmas:', error);
      return false;
    }
  }

  /**
   * Obtiene las alarmas pendientes
   */
  async getPendingAlarms() {
    if (!this.isNative) {
      return [];
    }

    try {
      const result = await LocalNotifications.getPending();
      return result.notifications;
    } catch (error) {
      console.error('❌ Error al obtener alarmas pendientes:', error);
      return [];
    }
  }

  /**
   * Convierte la frecuencia a intervalo de Capacitor
   */
  getRepeatInterval(frecuencia) {
    const intervals = {
      'diaria': 'day',
      'semanal': 'week',
      'mensual': 'month',
      'anual': 'year',
      'cada_hora': 'hour',
      'cada_minuto': 'minute'
    };
    return intervals[frecuencia] || 'day';
  }

  // ========== Métodos para Web (fallback) ==========

  scheduleWebAlarm(alarm) {
    // Para web, podemos usar una combinación de setTimeout y Web Notifications API
    console.log('🌐 Programando alarma web:', alarm);

    const alarmDate = new Date(alarm.fecha_hora);
    const now = new Date();
    const delay = alarmDate - now;

    if (delay > 0) {
      const timeoutId = setTimeout(() => {
        this.showWebNotification(alarm);
      }, delay);

      // Guardar el timeout ID para poder cancelarlo después
      const webAlarms = JSON.parse(localStorage.getItem('web_alarms') || '{}');
      webAlarms[alarm.id] = {
        timeoutId,
        alarm
      };
      localStorage.setItem('web_alarms', JSON.stringify(webAlarms));

      return true;
    }

    return false;
  }

  showWebNotification(alarm) {
    if ('Notification' in window && Notification.permission === 'granted') {
      new Notification(alarm.nombre || 'Alarma Tidy', {
        body: alarm.descripcion || 'Es hora de tu alarma',
        icon: '/icons/favicon-96x96.png',
        badge: '/icons/favicon-32x32.png',
        tag: `alarm-${alarm.id}`,
        requireInteraction: true,
        vibrate: [200, 100, 200]
      });
    } else {
      // Fallback: mostrar alerta
      alert(`⏰ ${alarm.nombre}\n${alarm.descripcion || ''}`);
    }
  }

  cancelWebAlarm(alarmId) {
    const webAlarms = JSON.parse(localStorage.getItem('web_alarms') || '{}');
    if (webAlarms[alarmId]) {
      clearTimeout(webAlarms[alarmId].timeoutId);
      delete webAlarms[alarmId];
      localStorage.setItem('web_alarms', JSON.stringify(webAlarms));
      return true;
    }
    return false;
  }

  cancelAllWebAlarms() {
    const webAlarms = JSON.parse(localStorage.getItem('web_alarms') || '{}');
    Object.values(webAlarms).forEach(({ timeoutId }) => {
      clearTimeout(timeoutId);
    });
    localStorage.removeItem('web_alarms');
    return true;
  }

  // ========== Polling de alarmas (fallback mientras app está activa) ==========

  /**
   * Inicia el polling para verificar alarmas mientras la app está activa
   * @param {Array} alarms - Array de alarmas del store
   */
  startPolling(alarms) {
    console.log('🔄 Iniciando polling de alarmas (cada 15 segundos)');
    this.alarmsToCheck = alarms;

    // Limpiar intervalo anterior si existe
    if (this.pollingInterval) {
      clearInterval(this.pollingInterval);
    }

    // Verificar cada 15 segundos
    this.pollingInterval = setInterval(() => {
      this.checkAlarms();
    }, 15000); // 15 segundos

    // Verificar inmediatamente también
    this.checkAlarms();
  }

  /**
   * Detiene el polling de alarmas
   */
  stopPolling() {
    if (this.pollingInterval) {
      clearInterval(this.pollingInterval);
      this.pollingInterval = null;
      console.log('⏸️ Polling de alarmas detenido');
    }
  }

  /**
   * Verifica si alguna alarma debe dispararse
   */
  checkAlarms() {
    const now = new Date();

    this.alarmsToCheck.forEach(alarm => {
      // Solo verificar alarmas activas que no hayamos disparado y que no estén eliminadas
      if (alarm.estado !== 'activo' ||
          this.firedAlarms.has(alarm.id) ||
          alarm.deleted_at) {
        return;
      }

      // Construir fecha/hora de la alarma
      if (!alarm.fecha || !alarm.hora) {
        return;
      }

      const fecha_hora = `${alarm.fecha} ${alarm.hora}:00`;
      const alarmDate = new Date(fecha_hora);

      // Verificar que la fecha sea válida
      if (isNaN(alarmDate.getTime())) {
        return;
      }

      // Si la alarma ya pasó (con margen de 1 minuto)
      const diff = now - alarmDate;
      if (diff >= 0 && diff < 60000) { // Entre 0 y 60 segundos después
        console.log(`🔔 ¡DISPARANDO ALARMA! ${alarm.nombre} - ${fecha_hora}`);
        this.fireAlarm(alarm);
        this.firedAlarms.add(alarm.id);
      }
    });
  }

  /**
   * Dispara una alarma (muestra notificación)
   * @param {Object} alarm - La alarma a disparar
   */
  async fireAlarm(alarm) {
    console.log('🔔 Disparando alarma:', alarm.nombre);

    if (this.isNative) {
      try {
        // Mostrar notificación inmediata
        await LocalNotifications.schedule({
          notifications: [{
            id: alarm.id + 100000, // ID diferente para no conflictuar
            title: `⏰ ${alarm.nombre}`,
            body: alarm.descripcion || alarm.contenido || 'Es hora de tu alarma',
            schedule: {
              at: new Date(Date.now() + 1000) // 1 segundo en el futuro
            },
            sound: 'default',
            channelId: 'alarms',
            smallIcon: 'ic_stat_alarm',
            extra: {
              alarmId: alarm.id,
              type: 'alarm',
              fired: true
            }
          }]
        });
        console.log('✅ Notificación de alarma programada');
      } catch (error) {
        console.error('❌ Error al disparar alarma:', error);
      }
    } else {
      // Versión web: mostrar notificación del navegador
      this.showWebNotification({
        nombre: alarm.nombre,
        descripcion: alarm.descripcion || alarm.contenido || 'Es hora de tu alarma'
      });
    }

    // Emitir evento para que el UI pueda reaccionar
    if (typeof window !== 'undefined') {
      window.dispatchEvent(new CustomEvent('alarm-fired', {
        detail: { alarm }
      }));
    }
  }

  /**
   * Actualiza la lista de alarmas a verificar
   * @param {Array} alarms - Nueva lista de alarmas
   */
  updateAlarms(alarms) {
    this.alarmsToCheck = alarms;
  }

  /**
   * Limpia las alarmas disparadas (útil para reset)
   */
  clearFiredAlarms() {
    this.firedAlarms.clear();
    console.log('🧹 Alarmas disparadas limpiadas');
  }

  /**
   * Remueve una alarma específica del set de alarmas disparadas
   * @param {number} alarmId - ID de la alarma a remover
   */
  removeFromFiredAlarms(alarmId) {
    const wasRemoved = this.firedAlarms.delete(alarmId);
    if (wasRemoved) {
      console.log(`🧹 Alarma ${alarmId} removida de firedAlarms`);
    }
    return wasRemoved;
  }
}

// Exportar instancia singleton
export const alarmService = new AlarmService();
export default alarmService;
