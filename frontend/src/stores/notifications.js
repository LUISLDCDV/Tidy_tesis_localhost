import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useNotificationsStore = defineStore('notifications', () => {
  // Estado reactivo
  const settings = ref({
    element_created: true,
    element_updated: true,
    alarm_triggered: true,
    goal_completed: true,
    level_up: true,
    achievement_unlocked: true,
    reminders: true,
    system_notifications: true
  })

  const notifications = ref([])
  const deviceToken = ref(null)
  const permission = ref('default') // 'default', 'granted', 'denied'

  const loading = ref({
    settings: false,
    history: false,
    registration: false
  })

  const errors = ref({
    settings: null,
    history: null,
    registration: null
  })

  const lastUpdated = ref({
    settings: null,
    history: null
  })

  const activeNotifications = ref([])
  const notificationQueue = ref([])

  // Getters computados
  const getNotificationSettings = computed(() => settings.value)
  
  const isNotificationEnabled = computed(() => (type) => settings.value[type] || false)

  const getAllNotifications = computed(() => notifications.value)
  
  const getRecentNotifications = computed(() => (limit = 10) => 
    notifications.value.slice(0, limit)
  )
  
  const getNotificationsByType = computed(() => (type) => 
    notifications.value.filter(notification => notification.type === type)
  )

  const getDeviceToken = computed(() => deviceToken.value)
  const getNotificationPermission = computed(() => permission.value)
  
  const hasNotificationPermission = computed(() => permission.value === 'granted')
  
  // Estados de carga y errores
  const isLoadingSettings = computed(() => loading.value.settings)
  const isLoadingHistory = computed(() => loading.value.history)
  const isRegistering = computed(() => loading.value.registration)

  const getSettingsError = computed(() => errors.value.settings)
  const getHistoryError = computed(() => errors.value.history)
  const getRegistrationError = computed(() => errors.value.registration)

  // Notificaciones activas
  const getActiveNotifications = computed(() => activeNotifications.value)
  const getNotificationQueue = computed(() => notificationQueue.value)
  
  const getUnreadNotificationsCount = computed(() => 
    notifications.value.filter(n => !n.read).length
  )

  // Acciones
  async function fetchNotificationSettings() {
    loading.value.settings = true
    errors.value.settings = null

    try {
      // Verificar que api.notifications existe antes de llamarlo
      if (!api.notifications || !api.notifications.getSettings) {
        console.warn('API notifications.getSettings no disponible, usando configuraciones por defecto')
        return
      }

      const response = await api.notifications.getSettings()
      settings.value = { ...settings.value, ...response.settings }
      lastUpdated.value.settings = new Date()
    } catch (error) {
      console.error('Error al cargar configuración de notificaciones:', error)
      errors.value.settings = error.response?.data?.message || 'Error al cargar configuración'
    } finally {
      loading.value.settings = false
    }
  }

  async function updateNotificationSettings(newSettings) {
    loading.value.settings = true
    errors.value.settings = null
    
    try {
      const response = await api.notifications.updateSettings(newSettings)
      settings.value = { ...settings.value, ...response.settings }
      return response
    } catch (error) {
      console.error('Error al actualizar configuración de notificaciones:', error)
      errors.value.settings = error.response?.data?.message || 'Error al actualizar configuración'
      throw error
    } finally {
      loading.value.settings = false
    }
  }

  async function updateSingleNotificationSetting(type, enabled) {
    const oldValue = settings.value[type]
    settings.value[type] = enabled
    
    try {
      await updateNotificationSettings({ [type]: enabled })
    } catch (error) {
      // Revertir cambio en caso de error
      settings.value[type] = oldValue
      throw error
    }
  }

  async function fetchNotificationHistory({ limit = 20, offset = 0 } = {}) {
    loading.value.history = true
    errors.value.history = null

    try {
      // Verificar que api.notifications existe antes de llamarlo
      if (!api.notifications || !api.notifications.getHistory) {
        console.warn('API notifications.getHistory no disponible')
        return { notifications: [] }
      }

      const response = await api.notifications.getHistory({ limit, offset })
      
      if (offset === 0) {
        notifications.value = response.notifications || []
      } else {
        // Agregar notificaciones adicionales al final
        notifications.value = [
          ...notifications.value,
          ...(response.notifications || [])
        ]
      }

      lastUpdated.value.history = new Date()
      return response
    } catch (error) {
      console.error('Error al cargar historial de notificaciones:', error)
      errors.value.history = error.response?.data?.message || 'Error al cargar historial'
      throw error
    } finally {
      loading.value.history = false
    }
  }

  async function registerDeviceToken({ token, deviceType = 'web', deviceName }) {
    loading.value.registration = true
    errors.value.registration = null
    
    try {
      const response = await api.notifications.registerDevice({
        device_token: token,
        device_type: deviceType,
        device_name: deviceName
      })
      
      deviceToken.value = token
      return response
    } catch (error) {
      console.error('Error al registrar token de dispositivo:', error)
      errors.value.registration = error.response?.data?.message || 'Error al registrar dispositivo'
      throw error
    } finally {
      loading.value.registration = false
    }
  }

  async function unregisterDeviceToken() {
    if (!deviceToken.value) return
    
    try {
      await api.notifications.unregisterDevice(deviceToken.value)
      deviceToken.value = null
    } catch (error) {
      console.error('Error al desregistrar token de dispositivo:', error)
    }
  }

  async function sendTestNotification({ title, body }) {
    try {
      const response = await api.notifications.sendTest({ title, body })
      return response
    } catch (error) {
      console.error('Error al enviar notificación de prueba:', error)
      throw error
    }
  }

  function handleReceivedNotification(notification) {
    // Verificar si el tipo de notificación está habilitado
    const isEnabled = settings.value[notification.type] !== false
    
    if (isEnabled) {
      // Agregar al historial
      addNotification(notification)
      
      // Agregar a notificaciones activas
      addActiveNotification(notification)
      
      // Auto-remover de notificaciones activas después de 5 segundos
      setTimeout(() => {
        removeActiveNotification(notification.id)
      }, 5000)
    }
  }

  function addNotification(notification) {
    // Agregar al principio del array (más recientes primero)
    notifications.value.unshift({
      ...notification,
      id: notification.id || Date.now(),
      timestamp: notification.timestamp || new Date(),
      read: false
    })

    // Limitar el historial a 100 notificaciones
    if (notifications.value.length > 100) {
      notifications.value = notifications.value.slice(0, 100)
    }
  }

  function markNotificationAsRead(notificationId) {
    const notification = notifications.value.find(n => n.id === notificationId)
    if (notification) {
      notification.read = true
    }
  }

  function markAllNotificationsAsRead() {
    notifications.value.forEach(notification => {
      notification.read = true
    })
  }

  function removeNotification(notificationId) {
    notifications.value = notifications.value.filter(n => n.id !== notificationId)
    removeActiveNotification(notificationId)
  }

  function clearNotificationHistory() {
    notifications.value = []
  }

  function updateNotificationPermission(newPermission) {
    permission.value = newPermission
  }

  // Notificaciones activas (para UI)
  function addActiveNotification(notification) {
    activeNotifications.value.push({
      ...notification,
      id: notification.id || Date.now(),
      timestamp: new Date()
    })
  }

  function removeActiveNotification(notificationId) {
    activeNotifications.value = activeNotifications.value
      .filter(n => n.id !== notificationId)
  }

  function clearActiveNotifications() {
    activeNotifications.value = []
  }

  // Cola de notificaciones
  function addToNotificationQueue(notification) {
    notificationQueue.value.push(notification)
  }

  function removeFromNotificationQueue(notificationId) {
    notificationQueue.value = notificationQueue.value
      .filter(n => n.id !== notificationId)
  }

  function clearNotificationQueue() {
    notificationQueue.value = []
  }

  async function initializeNotifications() {
    await Promise.all([
      fetchNotificationSettings(),
      fetchNotificationHistory({ limit: 10 })
    ])
  }

  function resetNotificationsState() {
    unregisterDeviceToken()
    
    notifications.value = []
    deviceToken.value = null
    permission.value = 'default'
    activeNotifications.value = []
    notificationQueue.value = []
    
    settings.value = {
      element_created: true,
      element_updated: true,
      alarm_triggered: true,
      goal_completed: true,
      level_up: true,
      achievement_unlocked: true,
      reminders: true,
      system_notifications: true
    }
    
    errors.value = {
      settings: null,
      history: null,
      registration: null
    }
    
    lastUpdated.value = {
      settings: null,
      history: null
    }
  }

  return {
    // Estado
    settings,
    notifications,
    deviceToken,
    permission,
    loading,
    errors,
    lastUpdated,
    activeNotifications,
    notificationQueue,

    // Getters
    getNotificationSettings,
    isNotificationEnabled,
    getAllNotifications,
    getRecentNotifications,
    getNotificationsByType,
    getDeviceToken,
    getNotificationPermission,
    hasNotificationPermission,
    isLoadingSettings,
    isLoadingHistory,
    isRegistering,
    getSettingsError,
    getHistoryError,
    getRegistrationError,
    getActiveNotifications,
    getNotificationQueue,
    getUnreadNotificationsCount,

    // Acciones
    fetchNotificationSettings,
    updateNotificationSettings,
    updateSingleNotificationSetting,
    fetchNotificationHistory,
    registerDeviceToken,
    unregisterDeviceToken,
    sendTestNotification,
    handleReceivedNotification,
    addNotification,
    markNotificationAsRead,
    markAllNotificationsAsRead,
    removeNotification,
    clearNotificationHistory,
    updateNotificationPermission,
    addActiveNotification,
    removeActiveNotification,
    clearActiveNotifications,
    addToNotificationQueue,
    removeFromNotificationQueue,
    clearNotificationQueue,
    initializeNotifications,
    resetNotificationsState
  }
})