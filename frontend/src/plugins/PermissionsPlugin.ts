import { registerPlugin } from '@capacitor/core';

export interface PermissionsPluginInterface {
  /**
   * Solicita permisos de notificaciones (Android 13+).
   */
  requestNotificationPermission(): Promise<{ granted: boolean; message?: string }>;

  /**
   * Verifica si los permisos de notificaciones están otorgados.
   */
  checkNotificationPermission(): Promise<{ granted: boolean }>;

  /**
   * Verifica si la app está exenta de optimización de batería.
   */
  checkBatteryOptimization(): Promise<{ isIgnoring: boolean; message: string }>;

  /**
   * Abre la configuración para desactivar la optimización de batería.
   */
  requestBatteryOptimizationExemption(): Promise<{ success: boolean; message: string }>;

  /**
   * Abre la configuración de la aplicación.
   */
  openAppSettings(): Promise<{ success: boolean }>;

  /**
   * Verifica si la app puede acceder a la política de Do Not Disturb.
   */
  checkDoNotDisturbAccess(): Promise<{ hasAccess: boolean }>;

  /**
   * Obtiene información del fabricante del dispositivo.
   */
  getDeviceInfo(): Promise<{
    manufacturer: string;
    model: string;
    sdkVersion: number;
    androidVersion: string;
    hasAggressiveBatterySaving: boolean;
    warning?: string;
  }>;
}

const PermissionsPlugin = registerPlugin<PermissionsPluginInterface>('PermissionsPlugin', {
  web: () => {
    // Fallback para web (no implementado)
    return {
      requestNotificationPermission: async () => ({ granted: false }),
      checkNotificationPermission: async () => ({ granted: false }),
      checkBatteryOptimization: async () => ({ isIgnoring: false, message: 'No disponible en web' }),
      requestBatteryOptimizationExemption: async () => ({ success: false, message: 'No disponible en web' }),
      openAppSettings: async () => ({ success: false }),
      checkDoNotDisturbAccess: async () => ({ hasAccess: false }),
      getDeviceInfo: async () => ({
        manufacturer: 'web',
        model: 'web',
        sdkVersion: 0,
        androidVersion: '0',
        hasAggressiveBatterySaving: false
      })
    };
  }
});

export default PermissionsPlugin;
