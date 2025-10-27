import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useConfigStore = defineStore('config', () => {
  // Estado reactivo
  const theme = ref(localStorage.getItem('app_theme') || 'light')
  const language = ref(localStorage.getItem('app_language') || 'es')
  const sidebarCollapsed = ref(JSON.parse(localStorage.getItem('sidebar_collapsed') || 'false'))
  const notificationsEnabled = ref(JSON.parse(localStorage.getItem('notifications_enabled') || 'true'))
  const soundEnabled = ref(JSON.parse(localStorage.getItem('sound_enabled') || 'true'))
  const autoSave = ref(JSON.parse(localStorage.getItem('auto_save') || 'true'))
  const compactMode = ref(JSON.parse(localStorage.getItem('compact_mode') || 'false'))
  
  // Configuraciones de usuario
  const userSettings = ref({
    timezone: 'America/Lima',
    dateFormat: 'dd/MM/yyyy',
    timeFormat: '24h',
    firstDayOfWeek: 1, // 0: Domingo, 1: Lunes
    defaultView: 'dashboard',
    itemsPerPage: 10,
    autoRefresh: true,
    refreshInterval: 30000 // 30 segundos
  })

  // Configuraciones de la aplicación
  const appConfig = ref({
    version: '1.0.0',
    apiUrl: import.meta.env.VITE_API_URL || 'https://tidyback-production.up.railway.app',
    appName: 'Tidy',
    supportEmail: 'support@tidy.app',
    maxFileSize: 10 * 1024 * 1024, // 10MB
    allowedFileTypes: ['image/*', 'application/pdf', 'text/*'],
    features: {
      darkMode: true,
      notifications: true,
      offlineMode: false,
      analytics: false,
      multiLanguage: true
    }
  })

  // Estados de la UI
  const uiState = ref({
    loading: false,
    activeModal: null,
    activeTab: null,
    searchQuery: '',
    selectedItems: [],
    bulkActions: false
  })

  // Getters computados
  const isDarkMode = computed(() => theme.value === 'dark')
  const isLightMode = computed(() => theme.value === 'light')
  const currentLanguage = computed(() => language.value)
  const isSidebarCollapsed = computed(() => sidebarCollapsed.value)
  
  const getTheme = computed(() => theme.value)
  const getLanguage = computed(() => language.value)
  const getUserSettings = computed(() => userSettings.value)
  const getAppConfig = computed(() => appConfig.value)
  const getUIState = computed(() => uiState.value)

  const isFeatureEnabled = computed(() => (feature) => {
    return appConfig.value.features[feature] || false
  })

  const getFormattedDate = computed(() => (date) => {
    const options = {
      year: 'numeric',
      month: 'numeric',
      day: 'numeric',
      hour12: userSettings.value.timeFormat === '12h'
    }
    
    if (userSettings.value.dateFormat === 'MM/dd/yyyy') {
      options.month = 'numeric'
      options.day = 'numeric'
    } else if (userSettings.value.dateFormat === 'yyyy-MM-dd') {
      // ISO format
    }

    return new Intl.DateTimeFormat(language.value, options).format(new Date(date))
  })

  // Acciones
  function setTheme(newTheme) {
    theme.value = newTheme
    localStorage.setItem('app_theme', newTheme)
    
    // Aplicar tema al documento
    document.documentElement.setAttribute('data-theme', newTheme)
    
    if (newTheme === 'dark') {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }

  function toggleTheme() {
    const newTheme = theme.value === 'light' ? 'dark' : 'light'
    setTheme(newTheme)
  }

  function setLanguage(newLanguage) {
    language.value = newLanguage
    localStorage.setItem('app_language', newLanguage)
  }

  function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value
    localStorage.setItem('sidebar_collapsed', JSON.stringify(sidebarCollapsed.value))
  }

  function setSidebarState(collapsed) {
    sidebarCollapsed.value = collapsed
    localStorage.setItem('sidebar_collapsed', JSON.stringify(collapsed))
  }

  function setNotificationsEnabled(enabled) {
    notificationsEnabled.value = enabled
    localStorage.setItem('notifications_enabled', JSON.stringify(enabled))
  }

  function setSoundEnabled(enabled) {
    soundEnabled.value = enabled
    localStorage.setItem('sound_enabled', JSON.stringify(enabled))
  }

  function setAutoSave(enabled) {
    autoSave.value = enabled
    localStorage.setItem('auto_save', JSON.stringify(enabled))
  }

  function setCompactMode(enabled) {
    compactMode.value = enabled
    localStorage.setItem('compact_mode', JSON.stringify(enabled))
  }

  function updateUserSettings(newSettings) {
    userSettings.value = { ...userSettings.value, ...newSettings }
    localStorage.setItem('user_settings', JSON.stringify(userSettings.value))
  }

  function updateUserSetting(key, value) {
    userSettings.value[key] = value
    localStorage.setItem('user_settings', JSON.stringify(userSettings.value))
  }

  function setUILoading(loading) {
    uiState.value.loading = loading
  }

  function setActiveModal(modal) {
    uiState.value.activeModal = modal
  }

  function setActiveTab(tab) {
    uiState.value.activeTab = tab
  }

  function setSearchQuery(query) {
    uiState.value.searchQuery = query
  }

  function setSelectedItems(items) {
    uiState.value.selectedItems = items
  }

  function toggleBulkActions() {
    uiState.value.bulkActions = !uiState.value.bulkActions
  }

  function clearSelectedItems() {
    uiState.value.selectedItems = []
    uiState.value.bulkActions = false
  }

  function resetUIState() {
    uiState.value = {
      loading: false,
      activeModal: null,
      activeTab: null,
      searchQuery: '',
      selectedItems: [],
      bulkActions: false
    }
  }

  // Inicializar configuraciones
  function initTheme() {
    const savedTheme = localStorage.getItem('app_theme') || 'light'
    setTheme(savedTheme)
  }

  function loadStorageSettings() {
    // Cargar configuraciones del usuario desde localStorage
    const savedUserSettings = localStorage.getItem('user_settings')
    if (savedUserSettings) {
      try {
        const parsedSettings = JSON.parse(savedUserSettings)
        userSettings.value = { ...userSettings.value, ...parsedSettings }
      } catch (error) {
        console.warn('Error al cargar configuraciones del usuario:', error)
      }
    }

    // Cargar otras configuraciones
    const savedNotifications = localStorage.getItem('notifications_enabled')
    if (savedNotifications) {
      notificationsEnabled.value = JSON.parse(savedNotifications)
    }

    const savedSound = localStorage.getItem('sound_enabled')
    if (savedSound) {
      soundEnabled.value = JSON.parse(savedSound)
    }

    const savedAutoSave = localStorage.getItem('auto_save')
    if (savedAutoSave) {
      autoSave.value = JSON.parse(savedAutoSave)
    }

    const savedCompactMode = localStorage.getItem('compact_mode')
    if (savedCompactMode) {
      compactMode.value = JSON.parse(savedCompactMode)
    }

    const savedSidebar = localStorage.getItem('sidebar_collapsed')
    if (savedSidebar) {
      sidebarCollapsed.value = JSON.parse(savedSidebar)
    }
  }

  function resetAllSettings() {
    // Limpiar localStorage
    const keysToRemove = [
      'app_theme',
      'app_language',
      'sidebar_collapsed',
      'notifications_enabled',
      'sound_enabled',
      'auto_save',
      'compact_mode',
      'user_settings'
    ]

    keysToRemove.forEach(key => localStorage.removeItem(key))

    // Resetear valores por defecto
    theme.value = 'light'
    language.value = 'es'
    sidebarCollapsed.value = false
    notificationsEnabled.value = true
    soundEnabled.value = true
    autoSave.value = true
    compactMode.value = false

    userSettings.value = {
      timezone: 'America/Lima',
      dateFormat: 'dd/MM/yyyy',
      timeFormat: '24h',
      firstDayOfWeek: 1,
      defaultView: 'dashboard',
      itemsPerPage: 10,
      autoRefresh: true,
      refreshInterval: 30000
    }

    resetUIState()

    // Reinicializar tema
    initTheme()
  }

  function exportSettings() {
    const settings = {
      theme: theme.value,
      language: language.value,
      sidebarCollapsed: sidebarCollapsed.value,
      notificationsEnabled: notificationsEnabled.value,
      soundEnabled: soundEnabled.value,
      autoSave: autoSave.value,
      compactMode: compactMode.value,
      userSettings: userSettings.value
    }

    return JSON.stringify(settings, null, 2)
  }

  function importSettings(settingsJSON) {
    try {
      const settings = JSON.parse(settingsJSON)
      
      if (settings.theme) setTheme(settings.theme)
      if (settings.language) setLanguage(settings.language)
      if (typeof settings.sidebarCollapsed === 'boolean') setSidebarState(settings.sidebarCollapsed)
      if (typeof settings.notificationsEnabled === 'boolean') setNotificationsEnabled(settings.notificationsEnabled)
      if (typeof settings.soundEnabled === 'boolean') setSoundEnabled(settings.soundEnabled)
      if (typeof settings.autoSave === 'boolean') setAutoSave(settings.autoSave)
      if (typeof settings.compactMode === 'boolean') setCompactMode(settings.compactMode)
      if (settings.userSettings) updateUserSettings(settings.userSettings)

      return true
    } catch (error) {
      console.error('Error al importar configuraciones:', error)
      return false
    }
  }

  return {
    // Estado
    theme,
    language,
    sidebarCollapsed,
    notificationsEnabled,
    soundEnabled,
    autoSave,
    compactMode,
    userSettings,
    appConfig,
    uiState,

    // Getters
    isDarkMode,
    isLightMode,
    currentLanguage,
    isSidebarCollapsed,
    getTheme,
    getLanguage,
    getUserSettings,
    getAppConfig,
    getUIState,
    isFeatureEnabled,
    getFormattedDate,

    // Acciones
    setTheme,
    toggleTheme,
    setLanguage,
    toggleSidebar,
    setSidebarState,
    setNotificationsEnabled,
    setSoundEnabled,
    setAutoSave,
    setCompactMode,
    updateUserSettings,
    updateUserSetting,
    setUILoading,
    setActiveModal,
    setActiveTab,
    setSearchQuery,
    setSelectedItems,
    toggleBulkActions,
    clearSelectedItems,
    resetUIState,
    initTheme,
    loadStorageSettings,
    resetAllSettings,
    exportSettings,
    importSettings
  }
})