import { ref, computed, onMounted, onUnmounted } from 'vue'
import offlineSyncService from '@/services/offlineSync'

export function useOfflineSync() {
  // Estado reactivo
  const isOnline = ref(navigator.onLine)
  const isSyncing = ref(false)
  const stats = ref({
    total: 0,
    pending: 0,
    errors: 0,
    synced: 0,
    isOnline: true,
    isSyncing: false
  })

  // Estado computado
  const hasPendingChanges = computed(() => stats.value.pending > 0)
  const hasErrors = computed(() => stats.value.errors > 0)
  const syncStatus = computed(() => {
    if (!isOnline.value) return 'offline'
    if (isSyncing.value) return 'syncing'
    if (hasErrors.value) return 'error'
    if (hasPendingChanges.value) return 'pending'
    return 'synced'
  })

  const syncStatusText = computed(() => {
    switch (syncStatus.value) {
      case 'offline': return 'Sin conexión'
      case 'syncing': return 'Sincronizando...'
      case 'error': return 'Error en sincronización'
      case 'pending': return 'Cambios pendientes'
      case 'synced': return 'Sincronizado'
      default: return 'Desconocido'
    }
  })

  const syncStatusColor = computed(() => {
    switch (syncStatus.value) {
      case 'offline': return 'orange'
      case 'syncing': return 'blue'
      case 'error': return 'red'
      case 'pending': return 'amber'
      case 'synced': return 'green'
      default: return 'grey'
    }
  })

  const syncStatusIcon = computed(() => {
    switch (syncStatus.value) {
      case 'offline': return 'wifi_off'
      case 'syncing': return 'sync'
      case 'error': return 'sync_problem'
      case 'pending': return 'sync_disabled'
      case 'synced': return 'cloud_done'
      default: return 'help'
    }
  })

  // Actualizar estadísticas
  const updateStats = () => {
    const newStats = offlineSyncService.getSyncStats()
    stats.value = newStats
    isOnline.value = newStats.isOnline
    isSyncing.value = newStats.isSyncing
  }

  // Listeners de eventos
  const handleOnline = () => {
    isOnline.value = true
    updateStats()
  }

  const handleOffline = () => {
    isOnline.value = false
    updateStats()
  }

  // Métodos del servicio
  const queueChange = async (changeData) => {
    const result = await offlineSyncService.queueChange(changeData)
    updateStats()
    return result
  }

  const forceSync = async () => {
    const result = await offlineSyncService.forcSync()
    updateStats()
    return result
  }

  const clearHistory = () => {
    offlineSyncService.clearOfflineHistory()
    updateStats()
  }

  // Helpers para diferentes tipos de cambios
  const queueElementCreate = (elementData, localId = null) => {
    return queueChange({
      type: 'CREATE_ELEMENT',
      data: elementData,
      localId,
      description: `Crear ${elementData.tipo || 'elemento'}: ${elementData.nombre || 'Sin nombre'}`
    })
  }

  const queueElementUpdate = (elementData) => {
    return queueChange({
      type: 'UPDATE_ELEMENT',
      data: elementData,
      description: `Actualizar ${elementData.tipo || 'elemento'}: ${elementData.nombre || 'Sin nombre'}`
    })
  }

  const queueElementDelete = (elementId, elementType = 'elemento') => {
    return queueChange({
      type: 'DELETE_ELEMENT',
      data: { id: elementId },
      description: `Eliminar ${elementType} ID: ${elementId}`
    })
  }

  const queueMessageCreate = (chatId, messageData) => {
    return queueChange({
      type: 'CREATE_MESSAGE',
      data: {
        chatId,
        message: messageData
      },
      description: `Enviar mensaje en chat ${chatId}`
    })
  }

  const queueUserProfileUpdate = (profileData) => {
    return queueChange({
      type: 'UPDATE_USER_PROFILE',
      data: profileData,
      description: 'Actualizar perfil de usuario'
    })
  }

  // Inicialización
  onMounted(() => {
    updateStats()

    // Listeners de conexión
    window.addEventListener('online', handleOnline)
    window.addEventListener('offline', handleOffline)

    // Actualizar estadísticas periódicamente
    const interval = setInterval(updateStats, 5000) // Cada 5 segundos

    // Cleanup en unmount
    onUnmounted(() => {
      window.removeEventListener('online', handleOnline)
      window.removeEventListener('offline', handleOffline)
      clearInterval(interval)
    })
  })

  return {
    // Estado
    isOnline,
    isSyncing,
    stats,

    // Computados
    hasPendingChanges,
    hasErrors,
    syncStatus,
    syncStatusText,
    syncStatusColor,
    syncStatusIcon,

    // Métodos
    queueChange,
    forceSync,
    clearHistory,
    updateStats,

    // Helpers específicos
    queueElementCreate,
    queueElementUpdate,
    queueElementDelete,
    queueMessageCreate,
    queueUserProfileUpdate
  }
}

export default useOfflineSync