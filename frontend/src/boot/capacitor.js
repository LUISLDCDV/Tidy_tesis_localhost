import { boot } from 'quasar/wrappers'
import { Capacitor } from '@capacitor/core'
import { LocalNotifications } from '@capacitor/local-notifications'
import alarmService from '@/services/alarmService'
import androidPermissions from '@/utils/androidPermissions'

/**
 * Boot file para inicializar Capacitor y sus plugins
 * Este archivo se ejecuta ANTES de montar la app Vue
 *
 * NOTA: Ahora usamos boot() wrapper de Quasar porque el proyecto
 * migró a Quasar CLI. El wrapper se ejecuta automáticamente.
 */
export default boot(async ({ app, router, store }) => {
  console.log('🚀 Inicializando Capacitor...')

  // Solo ejecutar en plataforma nativa
  if (Capacitor.isNativePlatform()) {
    console.log('📱 Plataforma nativa detectada:', Capacitor.getPlatform())

    try {
      // 1. Solicitar permisos de notificaciones
      const permissionStatus = await LocalNotifications.requestPermissions()
      console.log('🔔 Permisos de notificaciones:', permissionStatus.display)

      // 1.1 En Android 12+ necesitamos verificar permisos críticos para alarmas
      if (Capacitor.getPlatform() === 'android') {
        try {
          // 1.1.1 Verificar alarmas exactas
          if (typeof LocalNotifications.checkExactNotificationSetting === 'function') {
            const exactAlarmStatus = await LocalNotifications.checkExactNotificationSetting()
            console.log('⏰ Estado de alarmas exactas:', exactAlarmStatus)

            if (exactAlarmStatus.exact_alarm !== 'granted') {
              console.warn('⚠️ NO hay permiso para alarmas exactas.')
              console.warn('⚠️ Solicitando al usuario que active alarmas exactas...')

              // Dirigir al usuario a la configuración de alarmas exactas
              if (typeof LocalNotifications.changeExactNotificationSetting === 'function') {
                const result = await LocalNotifications.changeExactNotificationSetting()
                console.log('📱 Usuario dirigido a configuración. Resultado:', result)

                if (result.exact_alarm === 'granted') {
                  console.log('✅ Usuario otorgó permiso de alarmas exactas')
                } else {
                  console.warn('⚠️ Usuario NO otorgó permiso de alarmas exactas. Las alarmas pueden no dispararse a tiempo.')
                }
              } else {
                console.warn('⚠️ changeExactNotificationSetting() no disponible en esta versión del plugin')
                console.warn('⚠️ Por favor, activa manualmente las alarmas exactas en Configuración > Aplicaciones > Tidy > Alarmas y recordatorios')
              }
            } else {
              console.log('✅ Permiso de alarmas exactas ya está otorgado')
            }
          } else {
            console.warn('⚠️ checkExactNotificationSetting() no disponible en esta versión del plugin')
            console.warn('ℹ️ Asumiendo que los permisos están otorgados. Si las alarmas no funcionan, verifica los permisos manualmente.')
          }

          // 1.1.2 Verificar todos los permisos necesarios
          console.log('🔍 Verificando todos los permisos necesarios...')
          const permissionsStatus = await androidPermissions.checkAllPermissions()
          console.log('📊 Estado de permisos:', permissionsStatus)

          if (!permissionsStatus.allGranted) {
            console.warn('⚠️ Algunos permisos no están otorgados')

            // Mostrar guía de permisos específica del fabricante
            const manufacturerGuide = androidPermissions.getManufacturerSpecificInstructions()
            console.log(manufacturerGuide)

            // Opcional: mostrar diálogo en UI (requiere store o evento)
            if (typeof window !== 'undefined') {
              window.dispatchEvent(new CustomEvent('show-permissions-guide', {
                detail: {
                  guide: manufacturerGuide,
                  permissions: permissionsStatus.permissions
                }
              }))
            }
          } else {
            console.log('✅ Todos los permisos verificados correctamente')
          }

        } catch (error) {
          console.warn('⚠️ Error al verificar permisos de Android:', error)
          console.warn('ℹ️ Esto es normal en versiones anteriores de Android o del plugin')
        }
      }

      if (permissionStatus.display === 'granted') {
        // 2. Inicializar servicio de alarmas
        await alarmService.initialize()
        console.log('✅ Servicio de alarmas inicializado correctamente')

        // 3. Crear canales de notificación (Android 8+)
        if (Capacitor.getPlatform() === 'android') {
          // Canal principal de alarmas con máxima prioridad
          await LocalNotifications.createChannel({
            id: 'alarms',
            name: 'Alarmas',
            description: 'Alarmas programadas con sonido y vibración',
            importance: 5, // IMPORTANCE_HIGH - máxima prioridad
            visibility: 1, // VISIBILITY_PUBLIC - mostrar en pantalla bloqueada
            sound: 'alarm.mp3', // Puedes usar un sonido personalizado
            vibration: true,
            lights: true,
            lightColor: '#FF0000', // Rojo para alarmas
            enableVibration: true,
            enableLights: true
          })
          console.log('📢 Canal de alarmas creado con máxima prioridad')

          // Canal secundario para recordatorios suaves
          await LocalNotifications.createChannel({
            id: 'reminders',
            name: 'Recordatorios',
            description: 'Recordatorios suaves sin interrumpir',
            importance: 4, // IMPORTANCE_DEFAULT
            visibility: 1,
            sound: 'default',
            vibration: true,
            lights: true,
            lightColor: '#176F46'
          })
          console.log('📢 Canal de recordatorios creado')
        }

        // 4. Listener para cuando se toca una notificación
        LocalNotifications.addListener('localNotificationActionPerformed', (notification) => {
          console.log('🔔 Notificación tocada:', notification)
          // Aquí puedes navegar a la página de alarmas
          router.push('/Alarms')
        })

        // 5. Obtener alarmas pendientes para debugging
        const pending = await LocalNotifications.getPending()
        console.log(`📋 Alarmas pendientes: ${pending.notifications.length}`)

        // 6. Iniciar polling de alarmas (fallback mientras app está activa)
        // El store pasará las alarmas cuando esté listo
        console.log('🔄 Sistema de polling de alarmas activado (fallback)')

      } else {
        console.warn('⚠️ Permisos de notificaciones denegados')
      }

    } catch (error) {
      console.error('❌ Error al inicializar Capacitor:', error)
    }

  } else {
    console.log('🌐 Ejecutando en navegador web')
    // Inicializar versión web del servicio de alarmas
    await alarmService.initialize()
  }
})
