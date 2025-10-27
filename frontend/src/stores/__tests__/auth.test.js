import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../auth'
import axios from 'axios'

// Mock de axios
vi.mock('axios')

// Mock de router
const mockRouter = {
  push: vi.fn()
}

vi.mock('vue-router', () => ({
  useRouter: () => mockRouter
}))

describe('useAuthStore', () => {
  let authStore

  beforeEach(() => {
    setActivePinia(createPinia())
    authStore = useAuthStore()
    vi.clearAllMocks()
    localStorage.clear()
  })

  describe('Estado inicial', () => {
    it('tiene el estado inicial correcto', () => {
      expect(authStore.currentUser).toBeNull()
      expect(authStore.token).toBeNull()
      expect(authStore.isAuthenticated).toBe(false)
      expect(authStore.loading).toBe(false)
      expect(authStore.error).toBeNull()
    })
  })

  describe('Getters', () => {
    it('isAuthenticated devuelve true cuando hay token y usuario', () => {
      authStore.token = 'valid-token'
      authStore.currentUser = { id: 1, name: 'Test User' }

      expect(authStore.isAuthenticated).toBe(true)
    })

    it('isAuthenticated devuelve false sin token', () => {
      authStore.currentUser = { id: 1, name: 'Test User' }
      authStore.token = null

      expect(authStore.isAuthenticated).toBe(false)
    })

    it('userRole devuelve el rol del usuario', () => {
      authStore.currentUser = { id: 1, role: 'admin' }

      expect(authStore.userRole).toBe('admin')
    })

    it('userLevel devuelve el nivel del usuario', () => {
      authStore.currentUser = { id: 1, level: 5 }

      expect(authStore.userLevel).toBe(5)
    })
  })

  describe('Actions - login', () => {
    it('realiza login exitosamente', async () => {
      const credentials = {
        email: 'test@example.com',
        password: 'password123'
      }

      const mockResponse = {
        data: {
          token: 'jwt-token',
          user: {
            id: 1,
            name: 'Test User',
            email: 'test@example.com'
          }
        }
      }

      axios.post.mockResolvedValue(mockResponse)

      const result = await authStore.login(credentials)

      expect(result).toEqual(mockResponse.data)
      expect(authStore.token).toBe('jwt-token')
      expect(authStore.currentUser).toEqual(mockResponse.data.user)
      expect(authStore.isAuthenticated).toBe(true)
      expect(authStore.loading).toBe(false)
      expect(authStore.error).toBeNull()

      // Verificar que se guardó en localStorage
      expect(localStorage.setItem).toHaveBeenCalledWith('auth_token', 'jwt-token')
      expect(localStorage.setItem).toHaveBeenCalledWith('user_data', JSON.stringify(mockResponse.data.user))
    })

    it('maneja errores de login', async () => {
      const credentials = {
        email: 'test@example.com',
        password: 'wrong-password'
      }

      const errorResponse = {
        response: {
          status: 401,
          data: { message: 'Credenciales inválidas' }
        }
      }

      axios.post.mockRejectedValue(errorResponse)

      await expect(authStore.login(credentials)).rejects.toThrow()

      expect(authStore.token).toBeNull()
      expect(authStore.currentUser).toBeNull()
      expect(authStore.isAuthenticated).toBe(false)
      expect(authStore.error).toBe('Credenciales inválidas')
    })

    it('establece loading durante login', async () => {
      let resolvePromise
      const promise = new Promise(resolve => {
        resolvePromise = resolve
      })

      axios.post.mockReturnValue(promise)

      const loginPromise = authStore.login({
        email: 'test@example.com',
        password: 'password'
      })

      expect(authStore.loading).toBe(true)

      resolvePromise({
        data: {
          token: 'token',
          user: { id: 1, name: 'Test' }
        }
      })

      await loginPromise
      expect(authStore.loading).toBe(false)
    })
  })

  describe('Actions - register', () => {
    it('realiza registro exitosamente', async () => {
      const userData = {
        name: 'New User',
        email: 'new@example.com',
        password: 'password123'
      }

      const mockResponse = {
        data: {
          token: 'new-jwt-token',
          user: {
            id: 2,
            name: 'New User',
            email: 'new@example.com'
          }
        }
      }

      axios.post.mockResolvedValue(mockResponse)

      const result = await authStore.register(userData)

      expect(result).toEqual(mockResponse.data)
      expect(authStore.token).toBe('new-jwt-token')
      expect(authStore.currentUser).toEqual(mockResponse.data.user)
      expect(axios.post).toHaveBeenCalledWith('/api/auth/register', userData)
    })

    it('maneja errores de registro', async () => {
      const userData = {
        name: 'New User',
        email: 'existing@example.com',
        password: 'password123'
      }

      const errorResponse = {
        response: {
          status: 422,
          data: { message: 'Email ya existe' }
        }
      }

      axios.post.mockRejectedValue(errorResponse)

      await expect(authStore.register(userData)).rejects.toThrow()
      expect(authStore.error).toBe('Email ya existe')
    })
  })

  describe('Actions - logout', () => {
    it('realiza logout exitosamente', async () => {
      // Configurar estado autenticado
      authStore.token = 'jwt-token'
      authStore.currentUser = { id: 1, name: 'Test User' }

      axios.post.mockResolvedValue({ data: { success: true } })

      await authStore.logout()

      expect(authStore.token).toBeNull()
      expect(authStore.currentUser).toBeNull()
      expect(authStore.isAuthenticated).toBe(false)

      // Verificar que se limpió localStorage
      expect(localStorage.removeItem).toHaveBeenCalledWith('auth_token')
      expect(localStorage.removeItem).toHaveBeenCalledWith('user_data')

      expect(axios.post).toHaveBeenCalledWith('/api/auth/logout')
    })

    it('limpia estado local incluso si la API falla', async () => {
      authStore.token = 'jwt-token'
      authStore.currentUser = { id: 1, name: 'Test User' }

      axios.post.mockRejectedValue(new Error('Error de red'))

      await authStore.logout()

      expect(authStore.token).toBeNull()
      expect(authStore.currentUser).toBeNull()
      expect(localStorage.removeItem).toHaveBeenCalledWith('auth_token')
    })
  })

  describe('Actions - refreshToken', () => {
    it('actualiza token exitosamente', async () => {
      const newToken = 'new-jwt-token'
      authStore.token = 'old-token'

      axios.post.mockResolvedValue({
        data: { token: newToken }
      })

      await authStore.refreshToken()

      expect(authStore.token).toBe(newToken)
      expect(localStorage.setItem).toHaveBeenCalledWith('auth_token', newToken)
    })

    it('maneja error al refrescar token', async () => {
      authStore.token = 'expired-token'
      axios.post.mockRejectedValue(new Error('Token expirado'))

      await authStore.refreshToken()

      // Debería limpiar el estado si el refresh falla
      expect(authStore.token).toBeNull()
      expect(authStore.currentUser).toBeNull()
    })
  })

  describe('Actions - updateProfile', () => {
    it('actualiza perfil de usuario', async () => {
      const currentUser = { id: 1, name: 'Old Name', email: 'test@example.com' }
      const updates = { name: 'New Name' }
      const updatedUser = { ...currentUser, ...updates }

      authStore.currentUser = currentUser

      axios.put.mockResolvedValue({
        data: { user: updatedUser }
      })

      await authStore.updateProfile(updates)

      expect(authStore.currentUser).toEqual(updatedUser)
      expect(localStorage.setItem).toHaveBeenCalledWith('user_data', JSON.stringify(updatedUser))
    })
  })

  describe('Persistencia de estado', () => {
    it('restaura estado desde localStorage', () => {
      const token = 'stored-token'
      const user = { id: 1, name: 'Stored User' }

      localStorage.getItem.mockImplementation((key) => {
        if (key === 'auth_token') return token
        if (key === 'user_data') return JSON.stringify(user)
        return null
      })

      authStore.restoreAuthState()

      expect(authStore.token).toBe(token)
      expect(authStore.currentUser).toEqual(user)
      expect(authStore.isAuthenticated).toBe(true)
    })

    it('maneja datos corruptos en localStorage', () => {
      localStorage.getItem.mockImplementation((key) => {
        if (key === 'auth_token') return 'valid-token'
        if (key === 'user_data') return 'invalid-json'
        return null
      })

      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})

      authStore.restoreAuthState()

      expect(authStore.token).toBe('valid-token')
      expect(authStore.currentUser).toBeNull() // JSON inválido
      expect(consoleSpy).toHaveBeenCalled()

      consoleSpy.mockRestore()
    })
  })

  describe('Verificación de permisos', () => {
    it('hasPermission verifica permisos correctamente', () => {
      authStore.currentUser = {
        id: 1,
        permissions: ['read:notes', 'write:notes', 'delete:alarms']
      }

      expect(authStore.hasPermission('read:notes')).toBe(true)
      expect(authStore.hasPermission('write:calendars')).toBe(false)
    })

    it('hasRole verifica roles correctamente', () => {
      authStore.currentUser = {
        id: 1,
        role: 'admin'
      }

      expect(authStore.hasRole('admin')).toBe(true)
      expect(authStore.hasRole('user')).toBe(false)
    })

    it('isAdmin identifica administradores', () => {
      authStore.currentUser = { id: 1, role: 'admin' }
      expect(authStore.isAdmin).toBe(true)

      authStore.currentUser = { id: 1, role: 'user' }
      expect(authStore.isAdmin).toBe(false)
    })
  })

  describe('Configuración de axios', () => {
    it('configura header de autorización al hacer login', async () => {
      const mockResponse = {
        data: {
          token: 'jwt-token',
          user: { id: 1, name: 'Test' }
        }
      }

      axios.post.mockResolvedValue(mockResponse)

      await authStore.login({
        email: 'test@example.com',
        password: 'password'
      })

      expect(axios.defaults.headers.common['Authorization']).toBe('Bearer jwt-token')
    })

    it('limpia header de autorización al hacer logout', async () => {
      authStore.token = 'jwt-token'
      axios.defaults.headers.common['Authorization'] = 'Bearer jwt-token'

      axios.post.mockResolvedValue({ data: { success: true } })

      await authStore.logout()

      expect(axios.defaults.headers.common['Authorization']).toBeUndefined()
    })
  })
})