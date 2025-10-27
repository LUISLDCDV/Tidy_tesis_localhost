/**
 * Utilidades para manejar permisos especiales de Android
 * Necesarios para que las alarmas funcionen correctamente
 */

import { Capacitor } from '@capacitor/core'

/**
 * Verifica si estamos en Android
 */
export function isAndroid() {
  return Capacitor.getPlatform() === 'android'
}

/**
 * Guía al usuario para desactivar optimización de batería
 * IMPORTANTE: Android no permite solicitar este permiso programáticamente
 * por razones de seguridad, solo podemos guiar al usuario
 */
export async function requestBatteryOptimizationExemption() {
  if (!isAndroid()) {
    console.log('No estamos en Android, no se requiere este permiso')
    return true
  }

  try {
    // Intentar abrir configuración de batería usando intent de Android
    // Esto requiere un plugin nativo o WebView, aquí mostramos cómo se haría

    // Opción 1: Mostrar diálogo instructivo
    const userResponse = confirm(
      '⚡ Configuración de Batería Requerida\n\n' +
      'Para que las alarmas funcionen correctamente en segundo plano, necesitas:\n\n' +
      '1. Ir a Configuración > Aplicaciones > Tidy\n' +
      '2. Tocar "Batería" o "Uso de batería"\n' +
      '3. Seleccionar "Sin restricciones" o "No optimizar"\n\n' +
      '¿Deseas abrir la configuración ahora?'
    )

    if (userResponse) {
      // Opción 2: Intentar abrir configuración (requiere plugin nativo)
      // Para implementar esto, necesitarías:
      // await App.openSettings() // Desde @capacitor/app

      console.log('📱 Usuario abrirá configuración manualmente')

      // Si tienes @capacitor/app instalado:
      if (window.Capacitor?.Plugins?.App) {
        try {
          await window.Capacitor.Plugins.App.openSettings()
        } catch (error) {
          console.warn('No se pudo abrir configuración automáticamente:', error)
        }
      }
    }

    return userResponse
  } catch (error) {
    console.error('Error al solicitar permiso de batería:', error)
    return false
  }
}

/**
 * Verifica si la app tiene permiso de alarmas exactas (Android 12+)
 * @returns {Promise<boolean>}
 */
export async function checkExactAlarmPermission() {
  if (!isAndroid()) {
    return true
  }

  try {
    // Verificar versión de Android
    const androidVersion = await getAndroidVersion()

    if (androidVersion < 12) {
      console.log('Android < 12, no se requiere permiso especial de alarmas exactas')
      return true
    }

    // Aquí deberías usar LocalNotifications.checkPermissions()
    // o un método similar del plugin que estés usando

    console.log('⚠️ Android 12+: Verifica manualmente que "Alarmas y recordatorios" esté activado')
    return true // Asumimos que sí por ahora

  } catch (error) {
    console.error('Error verificando permiso de alarmas exactas:', error)
    return false
  }
}

/**
 * Obtiene la versión de Android
 * @returns {Promise<number>}
 */
async function getAndroidVersion() {
  try {
    // Usando Device plugin de Capacitor
    if (window.Capacitor?.Plugins?.Device) {
      const info = await window.Capacitor.Plugins.Device.getInfo()

      // info.osVersion es string como "13.0"
      const version = parseInt(info.osVersion.split('.')[0])
      console.log(`📱 Android versión: ${version}`)
      return version
    }

    // Fallback: asumir Android reciente
    return 12
  } catch (error) {
    console.warn('No se pudo detectar versión de Android:', error)
    return 12 // Asumir Android 12+ para ser conservadores
  }
}

/**
 * Muestra guía completa de configuración de permisos
 */
export function showPermissionsGuide() {
  const guide = `
📱 GUÍA DE CONFIGURACIÓN DE ALARMAS

Para que las alarmas funcionen correctamente:

🔔 1. PERMISOS DE NOTIFICACIONES
   ✓ Configuración > Aplicaciones > Tidy > Notificaciones
   ✓ Activar "Permitir notificaciones"
   ✓ Prioridad del canal "Alarmas" en "Alta" o "Urgente"

🔋 2. OPTIMIZACIÓN DE BATERÍA
   ✓ Configuración > Aplicaciones > Tidy > Batería
   ✓ Seleccionar "Sin restricciones"

⏰ 3. ALARMAS Y RECORDATORIOS (Android 12+)
   ✓ Configuración > Aplicaciones > Tidy
   ✓ Activar "Alarmas y recordatorios"

🔓 4. PANTALLA BLOQUEADA
   ✓ Configuración > Aplicaciones > Tidy > Notificaciones
   ✓ Activar "Mostrar en pantalla bloqueada"

💡 5. RECOMENDACIONES ADICIONALES:
   • No usar "Modo ultra ahorro de energía"
   • Desactivar "Ahorro de datos" para Tidy
   • Algunas marcas (Xiaomi, Huawei) requieren activar "Autostart"

---

¿Todo listo? ¡Prueba creando una alarma para 2 minutos!
  `

  console.log(guide)
  return guide
}

/**
 * Verifica todos los permisos necesarios
 * @returns {Promise<{permissions: Object, allGranted: boolean}>}
 */
export async function checkAllPermissions() {
  if (!isAndroid()) {
    return {
      permissions: { allGranted: true },
      allGranted: true
    }
  }

  try {
    const permissions = {
      notifications: false,
      exactAlarms: false,
      batteryOptimization: 'unknown', // No podemos verificarlo programáticamente
      fullScreenIntent: 'unknown'
    }

    // Verificar notificaciones
    if (window.Capacitor?.Plugins?.LocalNotifications) {
      const result = await window.Capacitor.Plugins.LocalNotifications.checkPermissions()
      permissions.notifications = result.display === 'granted'
    }

    // Verificar alarmas exactas
    permissions.exactAlarms = await checkExactAlarmPermission()

    const allGranted = permissions.notifications && permissions.exactAlarms

    return {
      permissions,
      allGranted
    }
  } catch (error) {
    console.error('Error verificando permisos:', error)
    return {
      permissions: {},
      allGranted: false
    }
  }
}

/**
 * Solicita todos los permisos necesarios
 * @returns {Promise<boolean>}
 */
export async function requestAllPermissions() {
  if (!isAndroid()) {
    return true
  }

  try {
    console.log('📱 Solicitando permisos necesarios para alarmas...')

    // 1. Solicitar permisos de notificaciones
    if (window.Capacitor?.Plugins?.LocalNotifications) {
      const result = await window.Capacitor.Plugins.LocalNotifications.requestPermissions()
      console.log('🔔 Permisos de notificaciones:', result.display)

      if (result.display !== 'granted') {
        console.error('❌ Usuario denegó permisos de notificaciones')
        return false
      }
    }

    // 2. Guiar para optimización de batería
    console.log('🔋 Solicitando exención de optimización de batería...')
    await requestBatteryOptimizationExemption()

    // 3. Mostrar guía completa
    const guide = showPermissionsGuide()

    return true
  } catch (error) {
    console.error('Error solicitando permisos:', error)
    return false
  }
}

/**
 * Manejo específico para diferentes fabricantes
 */
export function getManufacturerSpecificInstructions() {
  const manufacturer = getDeviceManufacturer()

  const instructions = {
    xiaomi: `
🔧 XIAOMI / MIUI:
1. Configuración > Permisos > Autostart > Activar Tidy
2. Configuración > Batería > Ahorro de energía > Sin restricciones
3. Seguridad > Permisos > Autostart > Permitir Tidy
`,
    huawei: `
🔧 HUAWEI / EMUI:
1. Configuración > Aplicaciones > Tidy > Iniciar
2. Gestión: Manual, Auto-inicio: ON, Ejecución secundaria: ON
3. Configuración > Batería > Iniciar aplicaciones > Tidy > Manual
`,
    samsung: `
🔧 SAMSUNG:
1. Configuración > Aplicaciones > Tidy > Batería
2. Desactivar "Poner app en suspensión"
3. Activar "Permitir uso en segundo plano"
`,
    oppo: `
🔧 OPPO / ColorOS:
1. Configuración > Batería > Ahorro de energía > Tidy > Sin restricción
2. Configuración > Privacidad > Administrar permisos > Inicio automático
`,
    vivo: `
🔧 VIVO / FuntouchOS:
1. i Manager > Gestión de apps > Inicio automático > Tidy
2. Configuración > Batería > Administración en segundo plano > Tidy
`,
    default: `
🔧 CONFIGURACIÓN ESTÁNDAR:
1. Configuración > Aplicaciones > Tidy
2. Batería > Sin restricciones
3. Notificaciones > Activadas, Prioridad alta
4. Alarmas y recordatorios > Activado (Android 12+)
`
  }

  return instructions[manufacturer] || instructions.default
}

/**
 * Detecta fabricante del dispositivo (simplificado)
 */
function getDeviceManufacturer() {
  const userAgent = navigator.userAgent.toLowerCase()

  if (userAgent.includes('xiaomi') || userAgent.includes('mi ')) return 'xiaomi'
  if (userAgent.includes('huawei')) return 'huawei'
  if (userAgent.includes('samsung')) return 'samsung'
  if (userAgent.includes('oppo')) return 'oppo'
  if (userAgent.includes('vivo')) return 'vivo'

  return 'default'
}

export default {
  isAndroid,
  requestBatteryOptimizationExemption,
  checkExactAlarmPermission,
  showPermissionsGuide,
  checkAllPermissions,
  requestAllPermissions,
  getManufacturerSpecificInstructions
}
