import { ref, watch, onMounted, onUnmounted } from 'vue'
import { useQuasar } from 'quasar'

/**
 * Composable para detectar y manejar el estado de conexión a internet
 * Proporciona notificaciones y eventos cuando cambia el estado de la red
 */

export function useNetworkStatus() {
  const $q = useQuasar()
  
  // Estado reactivo
  const isOnline = ref(navigator.onLine)
  const connectionType = ref(null)
  const effectiveType = ref(null)
  const downlink = ref(null)
  const rtt = ref(null)
  
  // Eventos
  let onlineHandler = null
  let offlineHandler = null
  let connectionHandler = null
  
  // Configuración de notificaciones
  const notifications = {
    offline: {
      type: 'negative',
      message: 'Sin conexión a internet',
      caption: 'Los datos se guardarán localmente',
      icon: 'cloud_off',
      position: 'top',
      timeout: 0,
      actions: [{ icon: 'close', color: 'white' }]
    },
    online: {
      type: 'positive',
      message: 'Conexión restaurada',
      caption: 'Sincronizando datos...',
      icon: 'cloud_done',
      position: 'top',
      timeout: 3000
    },
    slowConnection: {
      type: 'warning',
      message: 'Conexión lenta detectada',
      caption: 'Algunas funciones pueden tardar más',
      icon: 'network_check',
      position: 'top',
      timeout: 5000
    }
  }
  
  let currentNotification = null

  function setupNetworkListeners() {
    // Listener para cambios online/offline
    onlineHandler = () => {
      isOnline.value = true
      updateConnectionInfo()
      showOnlineNotification()
    }
    
    offlineHandler = () => {
      isOnline.value = false
      clearConnectionInfo()
      showOfflineNotification()
    }
    
    // Listener para cambios en la conexión (si está disponible)
    if ('connection' in navigator) {
      connectionHandler = () => {
        updateConnectionInfo()
        checkConnectionQuality()
      }
      
      navigator.connection.addEventListener('change', connectionHandler)
    }
    
    window.addEventListener('online', onlineHandler)
    window.addEventListener('offline', offlineHandler)
  }

  function removeNetworkListeners() {
    if (onlineHandler) {
      window.removeEventListener('online', onlineHandler)
    }
    
    if (offlineHandler) {
      window.removeEventListener('offline', offlineHandler)
    }
    
    if (connectionHandler && 'connection' in navigator) {
      navigator.connection.removeEventListener('change', connectionHandler)
    }
  }

  function updateConnectionInfo() {
    if ('connection' in navigator) {
      const conn = navigator.connection
      connectionType.value = conn.type || null
      effectiveType.value = conn.effectiveType || null
      downlink.value = conn.downlink || null
      rtt.value = conn.rtt || null
    }
  }

  function clearConnectionInfo() {
    connectionType.value = null
    effectiveType.value = null
    downlink.value = null
    rtt.value = null
  }

  function checkConnectionQuality() {
    if (!isOnline.value) return
    
    // Detectar conexión lenta
    if (effectiveType.value === 'slow-2g' || effectiveType.value === '2g') {
      showSlowConnectionNotification()
    } else if (downlink.value && downlink.value < 0.5) {
      showSlowConnectionNotification()
    }
  }

  function showOfflineNotification() {
    // Cerrar notificación anterior si existe
    if (currentNotification) {
      currentNotification()
    }
    
    currentNotification = $q.notify(notifications.offline)
  }

  function showOnlineNotification() {
    // Cerrar notificación de offline si existe
    if (currentNotification) {
      currentNotification()
      currentNotification = null
    }
    
    $q.notify(notifications.online)
  }

  function showSlowConnectionNotification() {
    $q.notify(notifications.slowConnection)
  }

  // Test de conectividad real (ping al servidor)
  async function testConnectivity(url = '/api/ping', timeout = 5000) {
    if (!isOnline.value) return false
    
    try {
      const controller = new AbortController()
      const timeoutId = setTimeout(() => controller.abort(), timeout)
      
      const response = await fetch(url, {
        method: 'HEAD',
        signal: controller.signal,
        cache: 'no-cache'
      })
      
      clearTimeout(timeoutId)
      return response.ok
    } catch (error) {
      console.warn('Connectivity test failed:', error)
      return false
    }
  }

  // Ejecutar acción cuando vuelva la conexión
  function whenOnline(callback) {
    if (isOnline.value) {
      callback()
    } else {
      const unwatch = watch(isOnline, (newValue) => {
        if (newValue) {
          callback()
          unwatch()
        }
      })
    }
  }

  // Información detallada de la conexión
  function getConnectionInfo() {
    return {
      isOnline: isOnline.value,
      type: connectionType.value,
      effectiveType: effectiveType.value,
      downlink: downlink.value,
      rtt: rtt.value,
      quality: getConnectionQuality()
    }
  }

  function getConnectionQuality() {
    if (!isOnline.value) return 'offline'
    
    if (effectiveType.value === '4g' || (downlink.value && downlink.value > 2)) {
      return 'excellent'
    } else if (effectiveType.value === '3g' || (downlink.value && downlink.value > 1)) {
      return 'good'
    } else if (effectiveType.value === '2g' || (downlink.value && downlink.value > 0.5)) {
      return 'fair'
    } else {
      return 'poor'
    }
  }

  // Configurar notificaciones personalizadas
  function setNotificationConfig(type, config) {
    if (notifications[type]) {
      notifications[type] = { ...notifications[type], ...config }
    }
  }

  // Lifecycle hooks
  onMounted(() => {
    setupNetworkListeners()
    updateConnectionInfo()
    
    // Test inicial de conectividad después de un breve delay
    setTimeout(async () => {
      const actuallyOnline = await testConnectivity()
      if (isOnline.value !== actuallyOnline) {
        isOnline.value = actuallyOnline
      }
    }, 1000)
  })

  onUnmounted(() => {
    removeNetworkListeners()
    
    // Cerrar notificación si existe
    if (currentNotification) {
      currentNotification()
    }
  })

  return {
    // Estado reactivo
    isOnline,
    connectionType,
    effectiveType,
    downlink,
    rtt,
    
    // Métodos de utilidad
    testConnectivity,
    whenOnline,
    getConnectionInfo,
    getConnectionQuality,
    setNotificationConfig,
    
    // Métodos de notificación manuales
    showOfflineNotification,
    showOnlineNotification,
    showSlowConnectionNotification
  }
}

export default useNetworkStatus