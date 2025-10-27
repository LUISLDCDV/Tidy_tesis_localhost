import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import alarmService from '@/services/alarmService'

export const useAlarmsStore = defineStore('alarms', () => {
  // Estado reactivo
  const alarms = ref([])
  const currentAlarm = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const lastUpdated = ref(null)

  // Getters computados
  const allAlarms = computed(() => alarms.value)
  
  const activeAlarms = computed(() => 
    alarms.value.filter(alarm => alarm.estado === 'activo' && !alarm.deleted_at)
  )

  const inactiveAlarms = computed(() => 
    alarms.value.filter(alarm => alarm.estado === 'inactivo')
  )

  const upcomingAlarms = computed(() => {
    const now = new Date()
    return activeAlarms.value
      .filter(alarm => new Date(alarm.fecha_hora) > now)
      .sort((a, b) => new Date(a.fecha_hora) - new Date(b.fecha_hora))
  })

  const overdueAlarms = computed(() => {
    const now = new Date()
    return activeAlarms.value
      .filter(alarm => new Date(alarm.fecha_hora) < now && !alarm.completada)
  })

  const recurringAlarms = computed(() => 
    alarms.value.filter(alarm => alarm.es_recurrente)
  )

  const isLoading = computed(() => loading.value)
  const hasError = computed(() => !!error.value)
  const errorMessage = computed(() => error.value)
  const alarmCount = computed(() => activeAlarms.value.length)

  // Acciones
  async function fetchAlarms() {
    loading.value = true
    error.value = null

    try {
      console.log('Fetching alarms from API...')
      const response = await api.get('/usuarios/elementos?tipo=alarma')
      console.log('Alarms response raw:', response.data)
      console.log('Total elements received:', response.data?.length)
      
      // Filtrar solo elementos de tipo alarma
      const allElements = response.data || []
      const alarmElements = allElements.filter(elemento => elemento.tipo === 'alarma')
      console.log('Alarm elements filtered:', alarmElements.length)
      
      const alarmsData = alarmElements
      
      // Mapear elementos a formato de alarmas compatible
      alarms.value = alarmsData.map(elemento => {
        console.log('Mapping alarm element:', elemento)
        console.log('Element fields available:', Object.keys(elemento))
        return {
          id: elemento.id,
          elemento_id: elemento.id,
          nombre: elemento.nombre || elemento.descripcion || 'Sin título',
          descripcion: elemento.descripcion,
          contenido: elemento.contenido || '',
          // Intentar obtener campos desde el nivel superior primero, luego desde configuracion
          fecha: elemento.fecha || elemento.configuracion?.fecha || '',
          hora: (elemento.hora || elemento.configuracion?.hora || '').substring(0, 5), // Solo HH:MM
          fechaVencimiento: elemento.fechaVencimiento || elemento.configuracion?.fechaVencimiento || '',
          horaVencimiento: (elemento.horaVencimiento || elemento.configuracion?.horaVencimiento || '').substring(0, 5), // Solo HH:MM
          intensidad_volumen: elemento.intensidad_volumen || elemento.configuracion?.intensidad_volumen || 50,
          configuraciones: elemento.configuracion || {},
          estado: elemento.estado || 'activo',
          es_recurrente: elemento.configuracion?.es_recurrente || false,
          frecuencia: elemento.configuracion?.frecuencia || null,
          completada: elemento.configuracion?.completada || false,
          created_at: elemento.created_at,
          updated_at: elemento.updated_at,
          deleted_at: elemento.deleted_at
        }
      })
      
      console.log('Alarms mapped to store:', alarms.value)
      lastUpdated.value = new Date()

      // 🔄 Iniciar polling de alarmas después de cargarlas
      startAlarmPolling()
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar alarmas'
      console.error('Error fetching alarms:', err)
    } finally {
      loading.value = false
    }
  }

  async function getAlarmById(id) {
    loading.value = true
    error.value = null

    try {
      const response = await api.get(`/usuarios/elemento/${id}`)
      currentAlarm.value = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar alarma'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createAlarm(alarmData) {
    loading.value = true
    error.value = null

    try {
      const elementData = {
        tipo: 'alarma',
        nombre: alarmData.nombre || 'Alarma sin título',
        descripcion: alarmData.nombre || 'Alarma sin título',
        estado: 'activo',
        contenido: alarmData.descripcion || '',
        fecha: alarmData.fecha || '',
        hora: alarmData.hora ? `${alarmData.hora}:00` : '',
        fechaVencimiento: alarmData.fechaVencimiento || '',
        horaVencimiento: alarmData.horaVencimiento ? `${alarmData.horaVencimiento}:00` : '',
        intensidad_volumen: alarmData.intensidad_volumen || 50,
        configuracion: {
          es_recurrente: alarmData.es_recurrente || false,
          frecuencia: alarmData.frecuencia || null,
          ...(typeof alarmData.configuraciones === 'object' 
            ? alarmData.configuraciones 
            : {})
        }
      }

      console.log('Sending alarm data to backend:', elementData)
      const response = await api.post('/elementos/saveUpdate', elementData)
      console.log('Response from create alarm:', response)

      const newAlarm = response.data
      console.log('New alarm data from backend:', newAlarm)
      console.log('New alarm fields available:', Object.keys(newAlarm))

      // Mapear la respuesta y agregarla al store en lugar de refrescar toda la lista
      const mappedAlarm = {
        id: newAlarm.id,
        elemento_id: newAlarm.id,
        nombre: newAlarm.nombre || newAlarm.descripcion || 'Sin título',
        descripcion: newAlarm.descripcion,
        contenido: newAlarm.contenido || '',
        fecha: newAlarm.fecha || '',
        hora: (newAlarm.hora || '').substring(0, 5), // Solo HH:MM
        fechaVencimiento: newAlarm.fechaVencimiento || '',
        horaVencimiento: (newAlarm.horaVencimiento || '').substring(0, 5), // Solo HH:MM
        intensidad_volumen: newAlarm.intensidad_volumen || 50,
        configuraciones: newAlarm.configuracion || {},
        estado: newAlarm.estado || 'activo',
        es_recurrente: newAlarm.configuracion?.es_recurrente || false,
        frecuencia: newAlarm.configuracion?.frecuencia || null,
        completada: false,
        created_at: newAlarm.created_at,
        updated_at: newAlarm.updated_at,
        deleted_at: null
      }

      alarms.value.unshift(mappedAlarm)

      // Programar notificación local si la alarma está activa
      if (mappedAlarm.estado === 'activo' && mappedAlarm.fecha && mappedAlarm.hora) {
        const fecha_hora = `${mappedAlarm.fecha} ${mappedAlarm.hora}:00`
        await alarmService.scheduleAlarm({
          id: mappedAlarm.id,
          nombre: mappedAlarm.nombre,
          descripcion: mappedAlarm.contenido || mappedAlarm.descripcion,
          fecha_hora: fecha_hora,
          repetir: mappedAlarm.es_recurrente,
          frecuencia: mappedAlarm.frecuencia
        })
      }

      // 🔄 Actualizar alarmas del polling
      alarmService.updateAlarms(alarms.value)

      return newAlarm
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear alarma'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateAlarm(alarmData) {
    loading.value = true
    error.value = null

    try {
      const elementData = {
        elemento_id: alarmData.id,
        tipo: 'alarma',
        nombre: alarmData.nombre || 'Alarma sin título',
        descripcion: alarmData.nombre || 'Alarma sin título',
        estado: alarmData.estado || 'activo',
        contenido: alarmData.descripcion || '',
        fecha: alarmData.fecha || '',
        hora: alarmData.hora ? `${alarmData.hora}:00` : '',
        fechaVencimiento: alarmData.fechaVencimiento || '',
        horaVencimiento: alarmData.horaVencimiento ? `${alarmData.horaVencimiento}:00` : '',
        intensidad_volumen: alarmData.intensidad_volumen || 50,
        configuracion: {
          es_recurrente: alarmData.es_recurrente || false,
          frecuencia: alarmData.frecuencia || null,
          ...(typeof alarmData.configuraciones === 'object' 
            ? alarmData.configuraciones 
            : {})
        }
      }

      console.log('Updating alarm data:', elementData)
      const response = await api.post('/elementos/saveUpdate', elementData)
      const updatedAlarm = response.data
      console.log('Updated alarm response from backend:', updatedAlarm)
      console.log('Updated alarm fields available:', Object.keys(updatedAlarm))

      // Mapear la respuesta actualizada
      const mappedAlarm = {
        id: updatedAlarm.id,
        elemento_id: updatedAlarm.id,
        nombre: updatedAlarm.nombre || updatedAlarm.descripcion || 'Sin título',
        descripcion: updatedAlarm.descripcion,
        contenido: updatedAlarm.contenido || '',
        fecha: updatedAlarm.fecha || '',
        hora: (updatedAlarm.hora || '').substring(0, 5), // Solo HH:MM
        fechaVencimiento: updatedAlarm.fechaVencimiento || '',
        horaVencimiento: (updatedAlarm.horaVencimiento || '').substring(0, 5), // Solo HH:MM
        intensidad_volumen: updatedAlarm.intensidad_volumen || 50,
        configuraciones: updatedAlarm.configuracion || {},
        estado: updatedAlarm.estado || 'activo',
        es_recurrente: updatedAlarm.configuracion?.es_recurrente || false,
        frecuencia: updatedAlarm.configuracion?.frecuencia || null,
        completada: updatedAlarm.configuracion?.completada || false,
        created_at: updatedAlarm.created_at,
        updated_at: updatedAlarm.updated_at,
        deleted_at: updatedAlarm.deleted_at
      }

      // Actualizar en el array
      const index = alarms.value.findIndex(alarm => alarm.id === updatedAlarm.id)
      if (index !== -1) {
        alarms.value[index] = mappedAlarm
      }

      // Actualizar alarma actual si es la misma
      if (currentAlarm.value?.id === updatedAlarm.id) {
        currentAlarm.value = mappedAlarm
      }

      // Cancelar alarma anterior y programar la actualizada si está activa
      if (mappedAlarm.estado === 'activo') {
        // Primero cancelar la alarma anterior
        await alarmService.cancelAlarm(mappedAlarm.id)

        // Luego programar la nueva si tiene fecha y hora
        if (mappedAlarm.fecha && mappedAlarm.hora) {
          const fecha_hora = `${mappedAlarm.fecha} ${mappedAlarm.hora}:00`
          await alarmService.scheduleAlarm({
            id: mappedAlarm.id,
            nombre: mappedAlarm.nombre,
            descripcion: mappedAlarm.contenido || mappedAlarm.descripcion,
            fecha_hora: fecha_hora,
            repetir: mappedAlarm.es_recurrente,
            frecuencia: mappedAlarm.frecuencia
          })
          console.log('✅ Alarma reprogramada:', mappedAlarm.nombre, 'para', fecha_hora)
        }
      } else {
        // Si está inactiva, solo cancelarla
        await alarmService.cancelAlarm(mappedAlarm.id)
        console.log('✅ Alarma cancelada (inactiva):', mappedAlarm.nombre)
      }

      // 🔄 Actualizar alarmas del polling
      alarmService.updateAlarms(alarms.value)

      return mappedAlarm
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar alarma'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteAlarm(alarmId) {
    loading.value = true
    error.value = null

    try {
      // Validar que el ID existe
      if (!alarmId) {
        console.error('❌ deleteAlarm: ID no proporcionado')
        throw new Error('ID de alarma no proporcionado')
      }

      console.log('🗑️ Eliminando alarma con ID:', alarmId)

      // Buscar la alarma antes de eliminar para debugging
      const alarmToDelete = alarms.value.find(a => a.id === alarmId)
      if (!alarmToDelete) {
        console.warn('⚠️ Alarma no encontrada en el store con ID:', alarmId)
        console.log('📋 IDs disponibles:', alarms.value.map(a => a.id))
      } else {
        console.log('✅ Alarma encontrada:', alarmToDelete.nombre)
      }

      await api.post(`/elementos/eliminarElemento/${alarmId}`)
      console.log('✅ Backend confirmó eliminación')

      // Marcar como eliminado (soft delete)
      const alarm = alarms.value.find(a => a.id === alarmId)
      if (alarm) {
        alarm.deleted_at = new Date().toISOString()
        console.log('✅ Alarma marcada como eliminada en el store')
      }

      // Cancelar notificación
      await alarmService.cancelAlarm(alarmId)
      console.log('✅ Notificación cancelada')

      // 🧹 Limpiar alarma del set de disparadas (liberar memoria)
      alarmService.removeFromFiredAlarms(alarmId)

      // 🔄 Actualizar alarmas del polling
      alarmService.updateAlarms(alarms.value)
      console.log('✅ Polling actualizado')

      return true
    } catch (err) {
      console.error('❌ Error completo al eliminar alarma:', err)
      error.value = err.response?.data?.message || err.message || 'Error al eliminar alarma'
      throw err
    } finally {
      loading.value = false
    }
  }

  function toggleAlarmStatus(alarmId) {
    const alarm = alarms.value.find(a => a.id === alarmId)
    if (alarm) {
      alarm.estado = alarm.estado === 'activo' ? 'inactivo' : 'activo'
      // En una implementación real, aquí se haría la llamada al API
      updateAlarm(alarm)
    }
  }

  function snoozeAlarm(alarmId, minutes = 5) {
    const alarm = alarms.value.find(a => a.id === alarmId)
    if (alarm) {
      const newTime = new Date(alarm.fecha_hora)
      newTime.setMinutes(newTime.getMinutes() + minutes)
      alarm.fecha_hora = newTime.toISOString()
      updateAlarm(alarm)
    }
  }

  function markAlarmAsCompleted(alarmId) {
    const alarm = alarms.value.find(a => a.id === alarmId)
    if (alarm) {
      alarm.completada = true
      alarm.fecha_completada = new Date().toISOString()
      
      // Si es recurrente, programar la siguiente
      if (alarm.es_recurrente) {
        scheduleNextRecurrence(alarm)
      }
    }
  }

  function scheduleNextRecurrence(alarm) {
    if (!alarm.es_recurrente || !alarm.frecuencia) return

    const nextDate = new Date(alarm.fecha_hora)
    
    switch (alarm.frecuencia) {
      case 'diaria':
        nextDate.setDate(nextDate.getDate() + 1)
        break
      case 'semanal':
        nextDate.setDate(nextDate.getDate() + 7)
        break
      case 'mensual':
        nextDate.setMonth(nextDate.getMonth() + 1)
        break
      case 'anual':
        nextDate.setFullYear(nextDate.getFullYear() + 1)
        break
    }

    // Crear nueva alarma para la próxima ocurrencia
    const nextAlarm = {
      ...alarm,
      id: null,
      fecha_hora: nextDate.toISOString(),
      completada: false,
      fecha_completada: null
    }

    createAlarm(nextAlarm)
  }

  function getAlarmsForDate(date) {
    const targetDate = new Date(date).toDateString()
    return alarms.value.filter(alarm => {
      const alarmDate = new Date(alarm.fecha_hora).toDateString()
      return alarmDate === targetDate
    })
  }

  function getAlarmsForDateRange(startDate, endDate) {
    const start = new Date(startDate)
    const end = new Date(endDate)
    
    return alarms.value.filter(alarm => {
      const alarmDate = new Date(alarm.fecha_hora)
      return alarmDate >= start && alarmDate <= end
    })
  }

  function searchAlarms(query) {
    const searchTerm = query.toLowerCase()
    return alarms.value.filter(alarm => 
      alarm.descripcion?.toLowerCase().includes(searchTerm) ||
      alarm.mensaje?.toLowerCase().includes(searchTerm)
    )
  }

  function sortAlarms(sortBy = 'fecha_hora', sortOrder = 'asc') {
    return [...alarms.value].sort((a, b) => {
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

  function addAlarm(alarm) {
    alarms.value.unshift(alarm)
  }

  function removeAlarm(alarmId) {
    const index = alarms.value.findIndex(a => a.id === alarmId)
    if (index !== -1) {
      alarms.value.splice(index, 1)
    }
  }

  function setCurrentAlarm(alarm) {
    currentAlarm.value = alarm
  }

  function clearCurrentAlarm() {
    currentAlarm.value = null
  }

  function clearError() {
    error.value = null
  }

  function clearAlarms() {
    alarms.value = []
    currentAlarm.value = null
    error.value = null
    lastUpdated.value = null
  }

  // Refrescar alarmas
  async function refreshAlarms() {
    await fetchAlarms()
  }

  // Re-programar todas las alarmas activas (útil al iniciar la app)
  async function rescheduleAllAlarms() {
    console.log('🔄 Re-programando todas las alarmas activas...')
    const activeAlarmsList = activeAlarms.value

    for (const alarm of activeAlarmsList) {
      if (alarm.fecha && alarm.hora) {
        const fecha_hora = `${alarm.fecha} ${alarm.hora}:00`
        const alarmDate = new Date(fecha_hora)

        // Solo programar si la alarma es futura
        if (alarmDate > new Date()) {
          try {
            await alarmService.scheduleAlarm({
              id: alarm.id,
              nombre: alarm.nombre,
              descripcion: alarm.contenido || alarm.descripcion,
              fecha_hora: fecha_hora,
              repetir: alarm.es_recurrente,
              frecuencia: alarm.frecuencia
            })
            console.log(`✅ Alarma re-programada: ${alarm.nombre}`)
          } catch (error) {
            console.error(`❌ Error re-programando alarma ${alarm.nombre}:`, error)
          }
        }
      }
    }
    console.log('✅ Re-programación de alarmas completada')
  }

  // Iniciar polling de alarmas (fallback mientras app está activa)
  function startAlarmPolling() {
    console.log('🔄 Iniciando polling de alarmas desde store')
    alarmService.startPolling(alarms.value)
  }

  // Detener polling de alarmas
  function stopAlarmPolling() {
    console.log('⏸️ Deteniendo polling de alarmas desde store')
    alarmService.stopPolling()
  }

  return {
    // Estado
    alarms,
    currentAlarm,
    loading,
    error,
    lastUpdated,

    // Getters
    allAlarms,
    activeAlarms,
    inactiveAlarms,
    upcomingAlarms,
    overdueAlarms,
    recurringAlarms,
    isLoading,
    hasError,
    errorMessage,
    alarmCount,

    // Acciones
    fetchAlarms,
    getAlarmById,
    createAlarm,
    updateAlarm,
    deleteAlarm,
    toggleAlarmStatus,
    snoozeAlarm,
    markAlarmAsCompleted,
    scheduleNextRecurrence,
    getAlarmsForDate,
    getAlarmsForDateRange,
    searchAlarms,
    sortAlarms,
    addAlarm,
    removeAlarm,
    setCurrentAlarm,
    clearCurrentAlarm,
    clearError,
    clearAlarms,
    refreshAlarms,
    rescheduleAllAlarms,
    startAlarmPolling,
    stopAlarmPolling
  }
})