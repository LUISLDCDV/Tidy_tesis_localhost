import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useCalendarsStore = defineStore('calendars', () => {
  // Estado reactivo
  const calendars = ref([])
  const currentCalendar = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const lastUpdated = ref(null)

  // Vista actual del calendario
  const currentView = ref('month') // 'month', 'week', 'day'
  const selectedDate = ref(new Date())
  const viewSettings = ref({
    showWeekends: true,
    showHours: false,
    timeFormat: '24h',
    startWeek: 'monday',
    defaultView: 'month'
  })

  // Getters computados
  const allCalendars = computed(() => calendars.value)
  
  const activeCalendars = computed(() => 
    calendars.value.filter(calendar => calendar.estado === 'activo' && !calendar.deleted_at)
  )

  const calendarsByType = computed(() => (type) => 
    calendars.value.filter(calendar => calendar.tipo_calendario === type)
  )

  const isLoading = computed(() => loading.value)
  const hasError = computed(() => !!error.value)
  const errorMessage = computed(() => error.value)
  const calendarCount = computed(() => activeCalendars.value.length)

  const getCurrentView = computed(() => currentView.value)
  const getSelectedDate = computed(() => selectedDate.value)
  const getViewSettings = computed(() => viewSettings.value)

  const eventsForDate = computed(() => (date) => {
    const targetDate = new Date(date).toDateString()
    return calendars.value.reduce((events, calendar) => {
      if (calendar.eventos) {
        const calendarEvents = calendar.eventos.filter(event => {
          const eventDate = new Date(event.fecha_inicio).toDateString()
          return eventDate === targetDate
        })
        return [...events, ...calendarEvents]
      }
      return events
    }, [])
  })

  const eventsForDateRange = computed(() => (startDate, endDate) => {
    const start = new Date(startDate)
    const end = new Date(endDate)
    
    return calendars.value.reduce((events, calendar) => {
      if (calendar.eventos) {
        const calendarEvents = calendar.eventos.filter(event => {
          const eventDate = new Date(event.fecha_inicio)
          return eventDate >= start && eventDate <= end
        })
        return [...events, ...calendarEvents]
      }
      return events
    }, [])
  })

  // Acciones
  async function fetchCalendars() {
    loading.value = true
    error.value = null

    try {
      const response = await api.calendars.getAll()
      calendars.value = response.data || []
      lastUpdated.value = new Date()
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar calendarios'
      console.error('Error fetching calendars:', err)
    } finally {
      loading.value = false
    }
  }

  async function getCalendarById(id) {
    loading.value = true
    error.value = null

    try {
      const response = await api.get(`/usuarios/elemento/${id}`)
      currentCalendar.value = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar calendario'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createCalendar(calendarData) {
    loading.value = true
    error.value = null

    try {
      const elementData = {
        tipo: 'calendario',
        descripcion: calendarData.titulo || calendarData.descripcion,
        estado: 'activo',
        configuracion: {
          color: calendarData.color || '#3498db',
          tipo_calendario: calendarData.tipo_calendario || 'personal',
          vista_predeterminada: calendarData.vista_predeterminada || 'month',
          zona_horaria: calendarData.zona_horaria || 'America/Lima',
          notificaciones: calendarData.notificaciones || true
        },
        eventos: []
      }

      const response = await api.post('/elementos/saveUpdate', elementData)
      const newCalendar = response.data

      calendars.value.unshift(newCalendar)
      
      return newCalendar
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear calendario'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateCalendar(calendarData) {
    loading.value = true
    error.value = null

    try {
      const elementData = {
        elemento_id: calendarData.id,
        tipo: 'calendario',
        descripcion: calendarData.titulo || calendarData.descripcion,
        estado: calendarData.estado || 'activo',
        configuracion: calendarData.configuracion,
        eventos: calendarData.eventos || []
      }

      const response = await api.post('/elementos/saveUpdate', elementData)
      const updatedCalendar = response.data

      const index = calendars.value.findIndex(calendar => calendar.id === updatedCalendar.id)
      if (index !== -1) {
        calendars.value[index] = updatedCalendar
      }

      if (currentCalendar.value?.id === updatedCalendar.id) {
        currentCalendar.value = updatedCalendar
      }

      return updatedCalendar
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar calendario'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteCalendar(calendarId) {
    loading.value = true
    error.value = null

    try {
      await api.post(`/elementos/eliminarElemento/${calendarId}`)

      const calendar = calendars.value.find(c => c.id === calendarId)
      if (calendar) {
        calendar.deleted_at = new Date().toISOString()
      }

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar calendario'
      throw err
    } finally {
      loading.value = false
    }
  }

  function addCalendar(calendar) {
    calendars.value.unshift(calendar)
  }

  function removeCalendar(calendarId) {
    const index = calendars.value.findIndex(c => c.id === calendarId)
    if (index !== -1) {
      calendars.value.splice(index, 1)
    }
  }

  function setCurrentCalendar(calendar) {
    currentCalendar.value = calendar
  }

  function clearCurrentCalendar() {
    currentCalendar.value = null
  }

  function setCurrentView(view) {
    currentView.value = view
  }

  function setSelectedDate(date) {
    selectedDate.value = new Date(date)
  }

  function updateViewSettings(settings) {
    viewSettings.value = { ...viewSettings.value, ...settings }
  }

  function addEventToCalendar(calendarId, event) {
    const calendar = calendars.value.find(c => c.id === calendarId)
    if (calendar) {
      if (!calendar.eventos) calendar.eventos = []
      calendar.eventos.push(event)
      updateCalendar(calendar)
    }
  }

  function removeEventFromCalendar(calendarId, eventId) {
    const calendar = calendars.value.find(c => c.id === calendarId)
    if (calendar && calendar.eventos) {
      calendar.eventos = calendar.eventos.filter(e => e.id !== eventId)
      updateCalendar(calendar)
    }
  }

  function searchCalendars(query) {
    const searchTerm = query.toLowerCase()
    return calendars.value.filter(calendar => 
      calendar.descripcion?.toLowerCase().includes(searchTerm) ||
      calendar.configuracion?.tipo_calendario?.toLowerCase().includes(searchTerm)
    )
  }

  function getCalendarsByDateRange(startDate, endDate) {
    const start = new Date(startDate)
    const end = new Date(endDate)
    
    return calendars.value.filter(calendar => {
      if (!calendar.eventos || calendar.eventos.length === 0) return false
      
      return calendar.eventos.some(event => {
        const eventDate = new Date(event.fecha_inicio)
        return eventDate >= start && eventDate <= end
      })
    })
  }

  function clearError() {
    error.value = null
  }

  function clearCalendars() {
    calendars.value = []
    currentCalendar.value = null
    error.value = null
    lastUpdated.value = null
  }

  async function refreshCalendars() {
    await fetchCalendars()
  }

  return {
    // Estado
    calendars,
    currentCalendar,
    loading,
    error,
    lastUpdated,
    currentView,
    selectedDate,
    viewSettings,

    // Getters
    allCalendars,
    activeCalendars,
    calendarsByType,
    isLoading,
    hasError,
    errorMessage,
    calendarCount,
    getCurrentView,
    getSelectedDate,
    getViewSettings,
    eventsForDate,
    eventsForDateRange,

    // Acciones
    fetchCalendars,
    getCalendarById,
    createCalendar,
    updateCalendar,
    deleteCalendar,
    addCalendar,
    removeCalendar,
    setCurrentCalendar,
    clearCurrentCalendar,
    setCurrentView,
    setSelectedDate,
    updateViewSettings,
    addEventToCalendar,
    removeEventFromCalendar,
    searchCalendars,
    getCalendarsByDateRange,
    clearError,
    clearCalendars,
    refreshCalendars
  }
})