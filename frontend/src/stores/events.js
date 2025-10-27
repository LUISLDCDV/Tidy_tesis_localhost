import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useEventsStore = defineStore('events', () => {
  // Estado reactivo
  const events = ref([])
  const currentEvent = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const lastUpdated = ref(null)

  // Tipos de eventos
  const eventTypes = ref([
    { value: 'reunion', label: 'Reunión', icon: 'meeting_room', color: '#3498db' },
    { value: 'cita', label: 'Cita', icon: 'event', color: '#2ecc71' },
    { value: 'tarea', label: 'Tarea', icon: 'assignment', color: '#f39c12' },
    { value: 'recordatorio', label: 'Recordatorio', icon: 'notification_important', color: '#e74c3c' },
    { value: 'cumpleanos', label: 'Cumpleaños', icon: 'cake', color: '#e91e63' },
    { value: 'evento_social', label: 'Evento Social', icon: 'people', color: '#9c27b0' },
    { value: 'viaje', label: 'Viaje', icon: 'flight', color: '#00bcd4' },
    { value: 'actividad', label: 'Actividad', icon: 'local_activity', color: '#ff9800' },
    { value: 'conferencia', label: 'Conferencia', icon: 'cast_for_education', color: '#795548' },
    { value: 'otro', label: 'Otro', icon: 'event_note', color: '#607d8b' }
  ])

  // Estados de eventos
  const eventStates = ref([
    { value: 'programado', label: 'Programado', color: '#3498db' },
    { value: 'en_curso', label: 'En Curso', color: '#f39c12' },
    { value: 'completado', label: 'Completado', color: '#2ecc71' },
    { value: 'cancelado', label: 'Cancelado', color: '#e74c3c' },
    { value: 'pospuesto', label: 'Pospuesto', color: '#9b59b6' }
  ])

  // Configuración de vistas
  const viewConfig = ref({
    currentView: 'month', // 'month', 'week', 'day', 'agenda'
    selectedDate: new Date(),
    showWeekends: true,
    startHour: 8,
    endHour: 22,
    timeSlotDuration: 30,
    showAllDay: true
  })

  // Getters computados
  const allEvents = computed(() => events.value)
  
  const activeEvents = computed(() => 
    events.value.filter(event => event.estado !== 'cancelado' && !event.deleted_at)
  )

  const upcomingEvents = computed(() => {
    const now = new Date()
    return activeEvents.value
      .filter(event => new Date(event.fecha_inicio) > now)
      .sort((a, b) => new Date(a.fecha_inicio) - new Date(b.fecha_inicio))
      .slice(0, 10)
  })

  const todayEvents = computed(() => {
    const today = new Date().toDateString()
    return activeEvents.value
      .filter(event => new Date(event.fecha_inicio).toDateString() === today)
      .sort((a, b) => new Date(a.fecha_inicio) - new Date(b.fecha_inicio))
  })

  const eventsByType = computed(() => (type) => 
    events.value.filter(event => event.tipo_evento === type)
  )

  const eventsByState = computed(() => (state) => 
    events.value.filter(event => event.estado === state)
  )

  const recurringEvents = computed(() => 
    events.value.filter(event => event.es_recurrente)
  )

  const conflictingEvents = computed(() => {
    const conflicts = []
    const sortedEvents = [...activeEvents.value].sort((a, b) => 
      new Date(a.fecha_inicio) - new Date(b.fecha_inicio)
    )

    for (let i = 0; i < sortedEvents.length - 1; i++) {
      const current = sortedEvents[i]
      const next = sortedEvents[i + 1]
      
      const currentEnd = new Date(current.fecha_fin || current.fecha_inicio)
      const nextStart = new Date(next.fecha_inicio)
      
      if (currentEnd > nextStart) {
        if (!conflicts.find(c => c.id === current.id)) conflicts.push(current)
        if (!conflicts.find(c => c.id === next.id)) conflicts.push(next)
      }
    }

    return conflicts
  })

  const isLoading = computed(() => loading.value)
  const hasError = computed(() => !!error.value)
  const errorMessage = computed(() => error.value)
  const eventCount = computed(() => activeEvents.value.length)

  const getEventTypes = computed(() => eventTypes.value)
  const getEventStates = computed(() => eventStates.value)
  const getViewConfig = computed(() => viewConfig.value)

  const getEventTypeInfo = computed(() => (type) => 
    eventTypes.value.find(et => et.value === type) || eventTypes.value[0]
  )

  const getEventStateInfo = computed(() => (state) => 
    eventStates.value.find(es => es.value === state) || eventStates.value[0]
  )

  // Acciones
  async function fetchEvents() {
    loading.value = true
    error.value = null

    try {
      const response = await api.events.getAll()
      events.value = response.data || []
      lastUpdated.value = new Date()
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar eventos'
      console.error('Error fetching events:', err)
    } finally {
      loading.value = false
    }
  }

  async function getEventById(id) {
    loading.value = true
    error.value = null

    try {
      const response = await api.get(`/usuarios/elemento/${id}`)
      currentEvent.value = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar evento'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createEvent(eventData) {
    loading.value = true
    error.value = null

    try {
      const elementData = {
        tipo: 'evento',
        descripcion: eventData.titulo || eventData.descripcion,
        estado: 'programado',
        fecha_inicio: eventData.fecha_inicio,
        fecha_fin: eventData.fecha_fin || null,
        configuracion: {
          tipo_evento: eventData.tipo_evento || 'otro',
          ubicacion: eventData.ubicacion || '',
          descripcion_detallada: eventData.descripcion_detallada || '',
          es_todo_el_dia: eventData.es_todo_el_dia || false,
          es_recurrente: eventData.es_recurrente || false,
          patron_recurrencia: eventData.patron_recurrencia || null,
          recordatorio: eventData.recordatorio || null,
          invitados: eventData.invitados || [],
          color: eventData.color || getEventTypeInfo.value(eventData.tipo_evento).color,
          privacidad: eventData.privacidad || 'privado',
          enlace_videollamada: eventData.enlace_videollamada || null,
          notas: eventData.notas || ''
        }
      }

      const response = await api.post('/elementos/saveUpdate', elementData)
      const newEvent = response.data

      events.value.unshift(newEvent)
      
      return newEvent
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear evento'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateEvent(eventData) {
    loading.value = true
    error.value = null

    try {
      const elementData = {
        elemento_id: eventData.id,
        tipo: 'evento',
        descripcion: eventData.titulo || eventData.descripcion,
        estado: eventData.estado || 'programado',
        fecha_inicio: eventData.fecha_inicio,
        fecha_fin: eventData.fecha_fin,
        configuracion: eventData.configuracion || {}
      }

      const response = await api.post('/elementos/saveUpdate', elementData)
      const updatedEvent = response.data

      const index = events.value.findIndex(event => event.id === updatedEvent.id)
      if (index !== -1) {
        events.value[index] = updatedEvent
      }

      if (currentEvent.value?.id === updatedEvent.id) {
        currentEvent.value = updatedEvent
      }

      return updatedEvent
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar evento'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteEvent(eventId) {
    loading.value = true
    error.value = null

    try {
      await api.post(`/elementos/eliminarElemento/${eventId}`)

      const event = events.value.find(e => e.id === eventId)
      if (event) {
        event.deleted_at = new Date().toISOString()
      }

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar evento'
      throw err
    } finally {
      loading.value = false
    }
  }

  function startEvent(eventId) {
    const event = events.value.find(e => e.id === eventId)
    if (event) {
      event.estado = 'en_curso'
      event.fecha_inicio_real = new Date().toISOString()
      updateEvent(event)
    }
  }

  function completeEvent(eventId) {
    const event = events.value.find(e => e.id === eventId)
    if (event) {
      event.estado = 'completado'
      event.fecha_fin_real = new Date().toISOString()
      updateEvent(event)
    }
  }

  function cancelEvent(eventId, reason = null) {
    const event = events.value.find(e => e.id === eventId)
    if (event) {
      event.estado = 'cancelado'
      if (reason) {
        event.configuracion = event.configuracion || {}
        event.configuracion.razon_cancelacion = reason
      }
      updateEvent(event)
    }
  }

  function postponeEvent(eventId, newDateTime) {
    const event = events.value.find(e => e.id === eventId)
    if (event) {
      event.estado = 'pospuesto'
      event.configuracion = event.configuracion || {}
      event.configuracion.fecha_original = event.fecha_inicio
      event.fecha_inicio = newDateTime
      updateEvent(event)
    }
  }

  function duplicateEvent(eventId, newDateTime) {
    const originalEvent = events.value.find(e => e.id === eventId)
    if (originalEvent) {
      const duplicatedEvent = {
        ...originalEvent,
        id: null,
        fecha_inicio: newDateTime,
        estado: 'programado'
      }
      createEvent(duplicatedEvent)
    }
  }

  function addGuest(eventId, guest) {
    const event = events.value.find(e => e.id === eventId)
    if (event) {
      event.configuracion = event.configuracion || {}
      event.configuracion.invitados = event.configuracion.invitados || []
      event.configuracion.invitados.push({
        ...guest,
        id: Date.now(),
        estado_invitacion: 'pendiente'
      })
      updateEvent(event)
    }
  }

  function removeGuest(eventId, guestId) {
    const event = events.value.find(e => e.id === eventId)
    if (event && event.configuracion?.invitados) {
      event.configuracion.invitados = event.configuracion.invitados
        .filter(g => g.id !== guestId)
      updateEvent(event)
    }
  }

  function updateGuestResponse(eventId, guestId, response) {
    const event = events.value.find(e => e.id === eventId)
    if (event && event.configuracion?.invitados) {
      const guest = event.configuracion.invitados.find(g => g.id === guestId)
      if (guest) {
        guest.estado_invitacion = response
        updateEvent(event)
      }
    }
  }

  function setReminder(eventId, reminder) {
    const event = events.value.find(e => e.id === eventId)
    if (event) {
      event.configuracion = event.configuracion || {}
      event.configuracion.recordatorio = reminder
      updateEvent(event)
    }
  }

  function getEventsForDate(date) {
    const targetDate = new Date(date).toDateString()
    return events.value.filter(event => {
      const eventDate = new Date(event.fecha_inicio).toDateString()
      return eventDate === targetDate
    })
  }

  function getEventsForDateRange(startDate, endDate) {
    const start = new Date(startDate)
    const end = new Date(endDate)
    
    return events.value.filter(event => {
      const eventDate = new Date(event.fecha_inicio)
      return eventDate >= start && eventDate <= end
    })
  }

  function searchEvents(query) {
    const searchTerm = query.toLowerCase()
    return events.value.filter(event => 
      event.descripcion?.toLowerCase().includes(searchTerm) ||
      event.configuracion?.descripcion_detallada?.toLowerCase().includes(searchTerm) ||
      event.configuracion?.ubicacion?.toLowerCase().includes(searchTerm) ||
      event.configuracion?.invitados?.some(guest => 
        guest.nombre?.toLowerCase().includes(searchTerm) ||
        guest.email?.toLowerCase().includes(searchTerm)
      )
    )
  }

  function sortEvents(sortBy = 'fecha_inicio', sortOrder = 'asc') {
    return [...events.value].sort((a, b) => {
      let valueA = a[sortBy]
      let valueB = b[sortBy]

      // Manejar fechas
      if (sortBy.includes('fecha_')) {
        valueA = new Date(valueA).getTime()
        valueB = new Date(valueB).getTime()
      }

      if (sortOrder === 'asc') {
        return valueA > valueB ? 1 : -1
      } else {
        return valueA < valueB ? 1 : -1
      }
    })
  }

  function updateViewConfig(config) {
    viewConfig.value = { ...viewConfig.value, ...config }
  }

  function addEvent(event) {
    events.value.unshift(event)
  }

  function removeEvent(eventId) {
    const index = events.value.findIndex(e => e.id === eventId)
    if (index !== -1) {
      events.value.splice(index, 1)
    }
  }

  function setCurrentEvent(event) {
    currentEvent.value = event
  }

  function clearCurrentEvent() {
    currentEvent.value = null
  }

  function clearError() {
    error.value = null
  }

  function clearEvents() {
    events.value = []
    currentEvent.value = null
    error.value = null
    lastUpdated.value = null
  }

  async function refreshEvents() {
    await fetchEvents()
  }

  return {
    // Estado
    events,
    currentEvent,
    loading,
    error,
    lastUpdated,
    eventTypes,
    eventStates,
    viewConfig,

    // Getters
    allEvents,
    activeEvents,
    upcomingEvents,
    todayEvents,
    eventsByType,
    eventsByState,
    recurringEvents,
    conflictingEvents,
    isLoading,
    hasError,
    errorMessage,
    eventCount,
    getEventTypes,
    getEventStates,
    getViewConfig,
    getEventTypeInfo,
    getEventStateInfo,

    // Acciones
    fetchEvents,
    getEventById,
    createEvent,
    updateEvent,
    deleteEvent,
    startEvent,
    completeEvent,
    cancelEvent,
    postponeEvent,
    duplicateEvent,
    addGuest,
    removeGuest,
    updateGuestResponse,
    setReminder,
    getEventsForDate,
    getEventsForDateRange,
    searchEvents,
    sortEvents,
    updateViewConfig,
    addEvent,
    removeEvent,
    setCurrentEvent,
    clearCurrentEvent,
    clearError,
    clearEvents,
    refreshEvents
  }
})