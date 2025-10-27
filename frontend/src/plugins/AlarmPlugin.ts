import { registerPlugin } from '@capacitor/core';

export interface AlarmPluginInterface {
  /**
   * Programa una alarma usando AlarmManager nativo de Android.
   *
   * @param options - Opciones de la alarma
   * @returns Promise que resuelve con información de la alarma programada
   */
  scheduleAlarm(options: {
    id: number;
    triggerTime: number; // Timestamp en milisegundos
    title: string;
    message: string;
  }): Promise<{ success: boolean; alarmId: number; scheduledFor: string }>;

  /**
   * Cancela una alarma programada.
   *
   * @param options - ID de la alarma a cancelar
   * @returns Promise que resuelve con información de la cancelación
   */
  cancelAlarm(options: { id: number }): Promise<{ success: boolean; alarmId: number }>;

  /**
   * Verifica si la app puede programar alarmas exactas (Android 12+).
   *
   * @returns Promise que resuelve con el estado de los permisos
   */
  canScheduleExactAlarms(): Promise<{ canSchedule: boolean }>;

  /**
   * Abre la configuración de alarmas exactas (Android 12+).
   *
   * @returns Promise que resuelve cuando se abre la configuración
   */
  openExactAlarmSettings(): Promise<{ success: boolean; message?: string }>;
}

const AlarmPlugin = registerPlugin<AlarmPluginInterface>('AlarmPlugin', {
  web: () => {
    // Fallback para web (no implementado)
    return {
      scheduleAlarm: async () => {
        console.warn('AlarmPlugin no está disponible en web');
        return { success: false, alarmId: 0, scheduledFor: '' };
      },
      cancelAlarm: async () => {
        console.warn('AlarmPlugin no está disponible en web');
        return { success: false, alarmId: 0 };
      },
      canScheduleExactAlarms: async () => {
        return { canSchedule: false };
      },
      openExactAlarmSettings: async () => {
        return { success: false, message: 'No disponible en web' };
      }
    };
  }
});

export default AlarmPlugin;
