import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useOfflineStorage } from '@/composables/useOfflineStorage'

export const useAuthStore = defineStore('auth', () => {
  // Estado reactivo
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token'))
  const isAuthenticated = ref(!!token.value)
  const loading = ref(false)
  const error = ref(null)
  const rememberMe = ref(localStorage.getItem('remember_me') === 'true')
  const isLoggingOut = ref(false) // Protección contra múltiples logouts
  const tokenExpiresAt = ref(null)
  const tokenExpiresIn = ref(null)

  // Getters computados
  const currentUser = computed(() => user.value)
  const isLoggedIn = computed(() => isAuthenticated.value && !!user.value)
  const authToken = computed(() => token.value)
  const isLoading = computed(() => loading.value)
  const authError = computed(() => error.value)
  const isTokenExpiringSoon = computed(() => {
    if (!tokenExpiresAt.value) return false
    const now = new Date()
    const expiresAt = new Date(tokenExpiresAt.value)
    const timeLeft = expiresAt - now
    return timeLeft < 15 * 60 * 1000 // 15 minutos en milisegundos
  })
  const tokenTimeLeft = computed(() => {
    if (!tokenExpiresAt.value) return null
    const now = new Date()
    const expiresAt = new Date(tokenExpiresAt.value)
    const timeLeft = Math.max(0, expiresAt - now)
    return timeLeft
  })

  // Acciones
  async function login(credentials, remember = false) {
    loading.value = true
    error.value = null

    try {
      const response = await api.auth.login(credentials)
      const {
        user: userData,
        access_token: authToken,
        expires_at,
        expires_in
      } = response.data

      // Guardar datos en el estado
      user.value = userData
      token.value = authToken
      isAuthenticated.value = true
      rememberMe.value = remember
      tokenExpiresAt.value = expires_at
      tokenExpiresIn.value = expires_in

      // Guardar en localStorage o sessionStorage según recordarme
      if (remember) {
        localStorage.setItem('auth_token', authToken)
        localStorage.setItem('user', JSON.stringify(userData))
        localStorage.setItem('remember_me', 'true')
        localStorage.setItem('token_expires_at', expires_at)
      } else {
        sessionStorage.setItem('auth_token', authToken)
        sessionStorage.setItem('user', JSON.stringify(userData))
        sessionStorage.setItem('token_expires_at', expires_at)
        localStorage.setItem('remember_me', 'false')
      }

      // Limpiar datos offline para asegurar que el nuevo usuario vea solo sus datos
      try {
        const { clearAllData } = useOfflineStorage()
        await clearAllData()
        console.info('Datos offline limpiados para nuevo usuario')
      } catch (err) {
        console.warn('Error al limpiar datos offline en login:', err)
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de autenticación'
      isAuthenticated.value = false
      throw err
    } finally {
      loading.value = false
    }
  }

  async function register(userData) {
    loading.value = true
    error.value = null

    try {
      const response = await api.auth.register(userData)
      const { user: newUser, access_token: authToken } = response.data

      // Guardar datos en el estado
      user.value = newUser
      token.value = authToken
      isAuthenticated.value = true

      // Guardar en localStorage
      localStorage.setItem('auth_token', authToken)
      localStorage.setItem('user', JSON.stringify(newUser))

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de registro'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function loginWithGoogle(tokenData) {
    loading.value = true
    error.value = null

    try {
      const response = await api.auth.googleLogin(tokenData)
      const { user: userData, token: authToken } = response.data

      // Guardar datos en el estado
      user.value = userData
      token.value = authToken
      isAuthenticated.value = true

      // Guardar en localStorage
      localStorage.setItem('auth_token', authToken)
      localStorage.setItem('user', JSON.stringify(userData))

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de autenticación con Google'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function logout(isManualLogout = true) {
    // Evitar múltiples logouts simultáneos
    if (isLoggingOut.value) {
      console.info('Logout ya en progreso, ignorando llamada adicional')
      return
    }

    isLoggingOut.value = true
    loading.value = true

    try {
      // Llamar al endpoint de logout si existe y el token está presente
      if (token.value) {
        await api.auth.logout()
        console.info('Logout exitoso en el servidor')
      }
    } catch (err) {
      console.warn('Error inesperado al hacer logout en el servidor:', err.message || err)
    } finally {
      // Limpiar estado local siempre
      await clearAuthData(isManualLogout)
      loading.value = false
      isLoggingOut.value = false
    }
  }

  async function clearAuthData(isManualLogout = true) {
    user.value = null
    token.value = null
    isAuthenticated.value = false
    error.value = null

    // Limpiar datos de sesión
    sessionStorage.removeItem('auth_token')
    sessionStorage.removeItem('user')

    // Si es logout manual, limpiar todo incluyendo localStorage y datos offline
    if (isManualLogout) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      localStorage.removeItem('user_data')
      localStorage.removeItem('remember_me')
      rememberMe.value = false

      // Limpiar todos los datos offline (elementos, notas, etc.)
      try {
        const { clearAllData } = useOfflineStorage()
        await clearAllData()
        console.info('Datos offline limpiados correctamente')
      } catch (err) {
        console.warn('Error al limpiar datos offline:', err)
      }
    }
    // Si no es manual (ej: token expirado), mantener preferencia de recordarme
  }

  async function checkAuthStatus() {
    // Primero revisar localStorage (recordarme activado)
    let storedToken = localStorage.getItem('auth_token')
    let storedUser = localStorage.getItem('user')
    let storedExpiresAt = localStorage.getItem('token_expires_at')
    let isFromLocalStorage = true

    // Si no hay en localStorage, revisar sessionStorage
    if (!storedToken) {
      storedToken = sessionStorage.getItem('auth_token')
      storedUser = sessionStorage.getItem('user')
      storedExpiresAt = sessionStorage.getItem('token_expires_at')
      isFromLocalStorage = false
    }

    // Actualizar estado de recordarme
    rememberMe.value = localStorage.getItem('remember_me') === 'true'

    if (storedToken && storedUser) {
      try {
        // Validar que storedUser sea JSON válido antes de parsearlo
        if (storedUser.trim().startsWith('{') || storedUser.trim().startsWith('[')) {
          token.value = storedToken
          user.value = JSON.parse(storedUser)
          tokenExpiresAt.value = storedExpiresAt
          isAuthenticated.value = true
        } else {
          throw new Error('Datos de usuario no son JSON válido')
        }

        // Intentar verificar si el token sigue siendo válido (solo si hay servidor)
        try {
          const response = await api.auth.getUserData()
          user.value = response.data.user

          // Actualizar en el mismo storage que se obtuvo
          if (isFromLocalStorage) {
            localStorage.setItem('user', JSON.stringify(response.data.user))
          } else {
            sessionStorage.setItem('user', JSON.stringify(response.data.user))
          }
        } catch (serverError) {
          // Si no hay servidor disponible, usar datos locales
          console.warn('Servidor no disponible, usando datos locales:', serverError.message)
        }
      } catch (err) {
        console.warn('Error al parsear datos locales, limpiando autenticación:', err)
        await clearAuthData(false) // No es logout manual
      }
    } else {
      clearAuthData(false) // No es logout manual
    }
  }

  async function refreshUserData() {
    if (!isAuthenticated.value) return

    try {
      const response = await api.auth.getUserData()
      user.value = response.data.user
      localStorage.setItem('user', JSON.stringify(response.data.user))
    } catch (err) {
      console.error('Error al refrescar datos del usuario:', err)
      if (err.response?.status === 401) {
        await clearAuthData()
      }
    }
  }

  function updateUser(userData) {
    user.value = { ...user.value, ...userData }
    localStorage.setItem('user', JSON.stringify(user.value))
  }

  function clearError() {
    error.value = null
  }

  function toggleNotifications() {
    if (user.value) {
      user.value.notificationsEnabled = !user.value.notificationsEnabled
      localStorage.setItem('user', JSON.stringify(user.value))
    }
  }

  // Inicializar el estado al cargar
  if (typeof window !== 'undefined') {
    checkAuthStatus()
  }

  // Función para validar token
  async function validateToken() {
    if (!token.value) return false

    try {
      const response = await api.auth.validateToken()
      return response.data.valid
    } catch (err) {
      console.warn('Token validation failed:', err)
      return false
    }
  }

  // Función para renovar token
  async function refreshToken() {
    if (!token.value) return false

    try {
      const response = await api.auth.refreshToken()
      const {
        access_token: newToken,
        expires_at,
        expires_in,
        user: userData
      } = response.data

      // Actualizar estado
      token.value = newToken
      user.value = userData
      tokenExpiresAt.value = expires_at
      tokenExpiresIn.value = expires_in

      // Actualizar storage
      const isFromLocalStorage = localStorage.getItem('auth_token')
      if (isFromLocalStorage) {
        localStorage.setItem('auth_token', newToken)
        localStorage.setItem('token_expires_at', expires_at)
        localStorage.setItem('user', JSON.stringify(userData))
      } else {
        sessionStorage.setItem('auth_token', newToken)
        sessionStorage.setItem('token_expires_at', expires_at)
        sessionStorage.setItem('user', JSON.stringify(userData))
      }

      console.info('Token renovado exitosamente')
      return true
    } catch (err) {
      console.warn('Error al renovar token:', err)
      await clearAuthData(false) // Logout automático
      return false
    }
  }

  // Función para verificar expiración automáticamente
  async function checkTokenExpiration() {
    if (!isAuthenticated.value || !tokenExpiresAt.value) return

    const now = new Date()
    const expiresAt = new Date(tokenExpiresAt.value)
    const timeLeft = expiresAt - now

    // Si queda menos de 15 minutos, intentar renovar
    if (timeLeft < 15 * 60 * 1000 && timeLeft > 0) {
      console.info('Token expira pronto, intentando renovar...')
      await refreshToken()
    }
    // Si ya expiró, hacer logout
    else if (timeLeft <= 0) {
      console.warn('Token expirado, cerrando sesión...')
      await logout(false) // Logout automático
    }
  }

  // Verificar expiración cada minuto
  if (typeof window !== 'undefined') {
    setInterval(checkTokenExpiration, 60000) // 1 minuto
  }

  return {
    // Estado
    user,
    token,
    isAuthenticated,
    loading,
    error,
    rememberMe,

    // Getters
    currentUser,
    isLoggedIn,
    authToken,
    isLoading,
    authError,

    // Acciones
    login,
    register,
    loginWithGoogle,
    logout,
    clearAuthData,
    checkAuthStatus,
    refreshUserData,
    updateUser,
    clearError,
    toggleNotifications,

    // Gestión de tokens
    tokenExpiresAt,
    tokenExpiresIn,
    isTokenExpiringSoon,
    tokenTimeLeft,
    refreshToken,
    validateToken,
    checkTokenExpiration
  }
})