import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useUserSettingsStore = defineStore('userSettings', () => {
  // Estado reactivo
  const settings = ref({
    // Configuraciones de perfil
    profile: {
      name: '',
      email: '',
      phone: '',
      bio: '',
      imageUrl: '',
      emailVerified: false
    },

    // Configuraciones de privacidad
    privacy: {
      publicProfile: true,
      showStats: true,
      shareLocation: false,
      allowAnalytics: true
    },

    // Configuraciones de notificaciones
    notifications: {
      pushNotifications: true,
      eventReminders: true,
      goalNotifications: true,
      systemNotifications: true,
      reminderTime: 15, // minutos antes
      soundEnabled: true,
      vibrationEnabled: true
    },

    // Configuraciones de apariencia
    appearance: {
      theme: 'auto', // 'light', 'dark', 'auto'
      language: 'es',
      fontSize: 'medium', // 'small', 'medium', 'large'
      colorScheme: 'default',
      compactMode: false
    },

    // Configuraciones de funcionalidad
    functionality: {
      autoSave: true,
      autoSync: true,
      offlineMode: false,
      betaFeatures: false,
      advancedMode: false
    }
  })

  const loading = ref({
    profile: false,
    settings: false,
    password: false,
    verification: false,
    sync: false
  })

  const errors = ref({
    profile: null,
    settings: null,
    password: null,
    verification: null,
    sync: null
  })

  const lastUpdated = ref({
    profile: null,
    settings: null
  })

  // Getters computados
  const getUserProfile = computed(() => settings.value.profile)
  const getPrivacySettings = computed(() => settings.value.privacy)
  const getNotificationSettings = computed(() => settings.value.notifications)
  const getAppearanceSettings = computed(() => settings.value.appearance)
  const getFunctionalitySettings = computed(() => settings.value.functionality)
  
  const getAllSettings = computed(() => settings.value)
  
  const isEmailVerified = computed(() => settings.value.profile.emailVerified)
  const isDarkMode = computed(() => {
    if (settings.value.appearance.theme === 'auto') {
      return window.matchMedia('(prefers-color-scheme: dark)').matches
    }
    return settings.value.appearance.theme === 'dark'
  })

  // Estados de carga
  const isLoadingProfile = computed(() => loading.value.profile)
  const isLoadingSettings = computed(() => loading.value.settings)
  const isChangingPassword = computed(() => loading.value.password)
  const isSendingVerification = computed(() => loading.value.verification)
  const isSyncing = computed(() => loading.value.sync)

  // Errores
  const getProfileError = computed(() => errors.value.profile)
  const getSettingsError = computed(() => errors.value.settings)
  const getPasswordError = computed(() => errors.value.password)
  const getVerificationError = computed(() => errors.value.verification)
  const getSyncError = computed(() => errors.value.sync)

  // Acciones para el perfil
  async function fetchUserProfile() {
    loading.value.profile = true
    errors.value.profile = null

    try {
      // Verificar que api.user existe antes de llamarlo
      if (!api.user || !api.user.getProfile) {
        console.warn('API user.getProfile no disponible')
        return
      }

      const response = await api.user.getProfile()
      settings.value.profile = { ...settings.value.profile, ...response.data }

      // También cargar estado de verificación de email
      await fetchEmailVerificationStatus()

      lastUpdated.value.profile = new Date()
    } catch (error) {
      console.error('Error al cargar perfil:', error)
      errors.value.profile = error.response?.data?.message || 'Error al cargar perfil'
    } finally {
      loading.value.profile = false
    }
  }

  async function fetchEmailVerificationStatus() {
    try {
      if (!api.user || !api.user.getVerificationStatus) {
        console.warn('API user.getVerificationStatus no disponible')
        return
      }

      const response = await api.user.getVerificationStatus()
      settings.value.profile.emailVerified = response.verified
    } catch (error) {
      console.error('Error al cargar estado de verificación:', error)
      // No lanzar error para no interrumpir el flujo principal
    }
  }

  async function updateUserProfile(profileData) {
    loading.value.profile = true
    errors.value.profile = null
    
    try {
      const response = await api.user.updateProfile(profileData)
      settings.value.profile = { ...settings.value.profile, ...response.data }
      
      // Guardar en localStorage como backup
      saveToLocalStorage()
      
      return response.data
    } catch (error) {
      console.error('Error al actualizar perfil:', error)
      errors.value.profile = error.response?.data?.message || 'Error al actualizar perfil'
      throw error
    } finally {
      loading.value.profile = false
    }
  }

  async function changePassword(passwordData) {
    loading.value.password = true
    errors.value.password = null
    
    try {
      const response = await api.user.changePassword(passwordData)
      return response.data
    } catch (error) {
      console.error('Error al cambiar contraseña:', error)
      errors.value.password = error.response?.data?.message || 'Error al cambiar contraseña'
      throw error
    } finally {
      loading.value.password = false
    }
  }

  async function sendEmailVerification() {
    loading.value.verification = true
    errors.value.verification = null
    
    try {
      const response = await api.user.sendVerification()
      return response.data
    } catch (error) {
      console.error('Error al enviar verificación:', error)
      errors.value.verification = error.response?.data?.message || 'Error al enviar verificación'
      throw error
    } finally {
      loading.value.verification = false
    }
  }

  // Acciones para configuraciones
  async function fetchUserSettings() {
    loading.value.settings = true
    errors.value.settings = null

    try {
      // Verificar que api.user existe antes de llamarlo
      if (!api.user || !api.user.getSettings) {
        console.warn('API user.getSettings no disponible, usando configuraciones por defecto')
        loadSettingsFromStorage()
        return
      }

      const response = await api.user.getSettings()
      settings.value = { ...settings.value, ...response.data }
      lastUpdated.value.settings = new Date()

      // Aplicar configuraciones cargadas
      applySettings()
    } catch (error) {
      console.error('Error al cargar configuraciones:', error)
      errors.value.settings = error.response?.data?.message || 'Error al cargar configuraciones'

      // Cargar desde localStorage como fallback
      loadFromLocalStorage()
    } finally {
      loading.value.settings = false
    }
  }

  async function updateSettings(newSettings, shouldApplySettings = true) {
    loading.value.settings = true
    errors.value.settings = null

    try {
      // Merge con configuraciones existentes
      const updatedSettings = mergeSettings(settings.value, newSettings)

      const response = await api.user.updateSettings(updatedSettings)
      settings.value = { ...settings.value, ...response.data }

      // Solo aplicar configuraciones si se indica explícitamente
      if (shouldApplySettings) {
        applySettings()
      }

      // Guardar en localStorage
      saveToLocalStorage()

      return response.data
    } catch (error) {
      console.error('Error al actualizar configuraciones:', error)
      errors.value.settings = error.response?.data?.message || 'Error al actualizar configuraciones'
      throw error
    } finally {
      loading.value.settings = false
    }
  }

  async function updateSingleSetting(category, key, value) {
    const currentValue = settings.value[category]?.[key]

    // Actualizar optimísticamente
    if (settings.value[category]) {
      settings.value[category][key] = value
    }

    // Solo aplicar configuraciones si es una configuración que afecta la apariencia visual
    // pero no si solo es el idioma
    const shouldApplySettings = !(category === 'appearance' && key === 'language')

    try {
      await updateSettings({
        [category]: {
          [key]: value
        }
      }, shouldApplySettings)
    } catch (error) {
      // Revertir en caso de error
      if (settings.value[category]) {
        settings.value[category][key] = currentValue
      }
      throw error
    }
  }

  // Acciones de sincronización
  async function syncData() {
    loading.value.sync = true
    errors.value.sync = null
    
    try {
      await Promise.all([
        fetchUserProfile(),
        fetchUserSettings()
      ])
      
      return true
    } catch (error) {
      console.error('Error en sincronización:', error)
      errors.value.sync = error.response?.data?.message || 'Error en sincronización'
      throw error
    } finally {
      loading.value.sync = false
    }
  }

  // Utilidades
  function mergeSettings(current, updates) {
    const merged = { ...current }
    
    Object.keys(updates).forEach(category => {
      if (merged[category]) {
        merged[category] = { ...merged[category], ...updates[category] }
      } else {
        merged[category] = updates[category]
      }
    })
    
    return merged
  }

  function applySettings(specificSettings = null) {
    if (typeof window !== 'undefined') {
      // Si se especifican configuraciones específicas, solo aplicar esas
      if (specificSettings) {
        if (specificSettings.includes('theme')) {
          applyThemeSettings()
        }
        if (specificSettings.includes('fontSize')) {
          applyFontSizeSettings()
        }
        if (specificSettings.includes('compactMode')) {
          applyCompactModeSettings()
        }
      } else {
        // Aplicar todas las configuraciones
        applyThemeSettings()
        applyFontSizeSettings()
        applyCompactModeSettings()
      }
    }
  }

  function applyThemeSettings() {
    if (typeof window !== 'undefined') {
      const theme = settings.value.appearance.theme
      // Solo aplicar el tema si no se está preservando el tema actual
      const currentQuasarTheme = document.body.classList.contains('body--dark')

      if (theme === 'dark' && !currentQuasarTheme) {
        document.body.classList.add('body--dark')
      } else if (theme === 'light' && currentQuasarTheme) {
        document.body.classList.remove('body--dark')
      } else if (theme === 'auto') {
        // Auto theme
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
        if (prefersDark && !currentQuasarTheme) {
          document.body.classList.add('body--dark')
        } else if (!prefersDark && currentQuasarTheme) {
          document.body.classList.remove('body--dark')
        }
      }
    }
  }

  function applyFontSizeSettings() {
    if (typeof window !== 'undefined') {
      const fontSize = settings.value.appearance.fontSize
      document.documentElement.setAttribute('data-font-size', fontSize)
    }
  }

  function applyCompactModeSettings() {
    if (typeof window !== 'undefined') {
      if (settings.value.appearance.compactMode) {
        document.body.classList.add('compact-mode')
      } else {
        document.body.classList.remove('compact-mode')
      }
    }
  }

  function saveToLocalStorage() {
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('userSettings', JSON.stringify(settings.value))
      localStorage.setItem('userSettingsTimestamp', Date.now().toString())
    }
  }

  function loadFromLocalStorage() {
    if (typeof localStorage !== 'undefined') {
      try {
        const saved = localStorage.getItem('userSettings')
        const timestamp = localStorage.getItem('userSettingsTimestamp')
        
        if (saved && timestamp) {
          const savedTime = parseInt(timestamp)
          const oneWeek = 7 * 24 * 60 * 60 * 1000
          
          // Solo cargar si no es muy antiguo (1 semana)
          if (Date.now() - savedTime < oneWeek) {
            const parsedSettings = JSON.parse(saved)
            settings.value = { ...settings.value, ...parsedSettings }
            applySettings()
          }
        }
      } catch (error) {
        console.error('Error loading from localStorage:', error)
      }
    }
  }

  function exportUserData() {
    const data = {
      profile: settings.value.profile,
      settings: settings.value,
      exportDate: new Date().toISOString(),
      version: '1.0'
    }
    
    const blob = new Blob([JSON.stringify(data, null, 2)], { 
      type: 'application/json' 
    })
    
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `tidy-user-data-${new Date().toISOString().split('T')[0]}.json`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
  }

  function clearAllData() {
    // Limpiar estado
    settings.value = {
      profile: {
        name: '',
        email: '',
        phone: '',
        bio: '',
        imageUrl: '',
        emailVerified: false
      },
      privacy: {
        publicProfile: true,
        showStats: true,
        shareLocation: false,
        allowAnalytics: true
      },
      notifications: {
        pushNotifications: true,
        eventReminders: true,
        goalNotifications: true,
        systemNotifications: true,
        reminderTime: 15,
        soundEnabled: true,
        vibrationEnabled: true
      },
      appearance: {
        theme: 'auto',
        language: 'es',
        fontSize: 'medium',
        colorScheme: 'default',
        compactMode: false
      },
      functionality: {
        autoSave: true,
        autoSync: true,
        offlineMode: false,
        betaFeatures: false,
        advancedMode: false
      }
    }
    
    // Limpiar errores
    errors.value = {
      profile: null,
      settings: null,
      password: null,
      verification: null,
      sync: null
    }
    
    // Limpiar localStorage
    if (typeof localStorage !== 'undefined') {
      localStorage.removeItem('userSettings')
      localStorage.removeItem('userSettingsTimestamp')
    }
  }

  async function initializeSettings() {
    // Cargar desde localStorage primero para aplicación inmediata
    loadFromLocalStorage()
    
    // Luego sincronizar con el servidor
    try {
      await syncData()
    } catch (error) {
      console.error('Error during settings initialization:', error)
    }
  }

  return {
    // Estado
    settings,
    loading,
    errors,
    lastUpdated,

    // Getters
    getUserProfile,
    getPrivacySettings,
    getNotificationSettings,
    getAppearanceSettings,
    getFunctionalitySettings,
    getAllSettings,
    isEmailVerified,
    isDarkMode,
    isLoadingProfile,
    isLoadingSettings,
    isChangingPassword,
    isSendingVerification,
    isSyncing,
    getProfileError,
    getSettingsError,
    getPasswordError,
    getVerificationError,
    getSyncError,

    // Acciones
    fetchUserProfile,
    updateUserProfile,
    changePassword,
    sendEmailVerification,
    fetchEmailVerificationStatus,
    fetchUserSettings,
    updateSettings,
    updateSingleSetting,
    syncData,
    applySettings,
    saveToLocalStorage,
    loadFromLocalStorage,
    exportUserData,
    clearAllData,
    initializeSettings
  }
})