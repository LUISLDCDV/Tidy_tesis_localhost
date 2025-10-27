import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAlarmsStore } from '../alarms'
import axios from 'axios'

// Mock de axios
vi.mock('axios')

describe('useAlarmsStore', () => {
  let alarmsStore

  beforeEach(() => {
    setActivePinia(createPinia())
    alarmsStore = useAlarmsStore()
    vi.clearAllMocks()
  })

  describe('Estado inicial', () => {
    it('tiene el estado inicial correcto', () => {
      expect(alarmsStore.alarms).toEqual([])
      expect(alarmsStore.loading).toBe(false)
      expect(alarmsStore.error).toBeNull()
    })
  })

  describe('Getters', () => {
    it('allAlarms devuelve todas las alarmas', () => {
      alarmsStore.alarms = [
        { id: 1, nombre: 'Alarma 1' },
        { id: 2, nombre: 'Alarma 2' }
      ]

      expect(alarmsStore.allAlarms).toHaveLength(2)
      expect(alarmsStore.allAlarms[0].nombre).toBe('Alarma 1')
    })

    it('getAlarmById devuelve la alarma correcta', () => {
      alarmsStore.alarms = [
        { id: 1, nombre: 'Alarma 1' },
        { id: 2, nombre: 'Alarma 2' }
      ]

      const alarm = alarmsStore.getAlarmById(1)
      expect(alarm.nombre).toBe('Alarma 1')
    })

    it('getAlarmById devuelve undefined para ID inexistente', () => {
      alarmsStore.alarms = [
        { id: 1, nombre: 'Alarma 1' }
      ]

      const alarm = alarmsStore.getAlarmById(999)
      expect(alarm).toBeUndefined()
    })

    it('activeAlarms devuelve solo alarmas activas', () => {
      alarmsStore.alarms = [
        { id: 1, nombre: 'Alarma 1', activa: true },
        { id: 2, nombre: 'Alarma 2', activa: false },
        { id: 3, nombre: 'Alarma 3', activa: true }
      ]

      const activeAlarms = alarmsStore.activeAlarms
      expect(activeAlarms).toHaveLength(2)
      expect(activeAlarms.every(alarm => alarm.activa)).toBe(true)
    })

    it('alarmsCount devuelve el número correcto', () => {
      alarmsStore.alarms = [
        { id: 1, nombre: 'Alarma 1' },
        { id: 2, nombre: 'Alarma 2' }
      ]

      expect(alarmsStore.alarmsCount).toBe(2)
    })
  })

  describe('Actions - fetchAlarms', () => {
    it('obtiene alarmas exitosamente', async () => {
      const mockAlarms = [
        { id: 1, nombre: 'Alarma 1' },
        { id: 2, nombre: 'Alarma 2' }
      ]

      axios.get.mockResolvedValue({ data: mockAlarms })

      await alarmsStore.fetchAlarms()

      expect(alarmsStore.loading).toBe(false)
      expect(alarmsStore.alarms).toEqual(mockAlarms)
      expect(alarmsStore.error).toBeNull()
      expect(axios.get).toHaveBeenCalledWith('/api/alarms')
    })

    it('maneja errores al obtener alarmas', async () => {
      const errorMessage = 'Error de red'
      axios.get.mockRejectedValue(new Error(errorMessage))

      await alarmsStore.fetchAlarms()

      expect(alarmsStore.loading).toBe(false)
      expect(alarmsStore.alarms).toEqual([])
      expect(alarmsStore.error).toBe(errorMessage)
    })

    it('establece loading durante la petición', async () => {
      let resolvePromise
      const promise = new Promise(resolve => {
        resolvePromise = resolve
      })

      axios.get.mockReturnValue(promise)

      const fetchPromise = alarmsStore.fetchAlarms()
      expect(alarmsStore.loading).toBe(true)

      resolvePromise({ data: [] })
      await fetchPromise

      expect(alarmsStore.loading).toBe(false)
    })
  })

  describe('Actions - createAlarm', () => {
    it('crea una nueva alarma exitosamente', async () => {
      const newAlarm = { nombre: 'Nueva Alarma', hora: '08:00' }
      const createdAlarm = { id: 1, ...newAlarm }

      axios.post.mockResolvedValue({ data: createdAlarm })

      const result = await alarmsStore.createAlarm(newAlarm)

      expect(result).toEqual(createdAlarm)
      expect(alarmsStore.alarms).toContain(createdAlarm)
      expect(axios.post).toHaveBeenCalledWith('/api/alarms', newAlarm)
    })

    it('maneja errores al crear alarma', async () => {
      const newAlarm = { nombre: 'Nueva Alarma' }
      const errorMessage = 'Error al crear'

      axios.post.mockRejectedValue(new Error(errorMessage))

      await expect(alarmsStore.createAlarm(newAlarm)).rejects.toThrow(errorMessage)
      expect(alarmsStore.alarms).toHaveLength(0)
    })
  })

  describe('Actions - updateAlarm', () => {
    it('actualiza una alarma existente', async () => {
      const originalAlarm = { id: 1, nombre: 'Alarma Original' }
      const updatedData = { nombre: 'Alarma Actualizada' }
      const updatedAlarm = { ...originalAlarm, ...updatedData }

      alarmsStore.alarms = [originalAlarm]
      axios.put.mockResolvedValue({ data: updatedAlarm })

      const result = await alarmsStore.updateAlarm(updatedAlarm)

      expect(result).toEqual(updatedAlarm)
      expect(alarmsStore.alarms[0]).toEqual(updatedAlarm)
      expect(axios.put).toHaveBeenCalledWith('/api/alarms/1', updatedAlarm)
    })

    it('maneja errores al actualizar alarma', async () => {
      const alarm = { id: 1, nombre: 'Alarma' }
      const errorMessage = 'Error al actualizar'

      axios.put.mockRejectedValue(new Error(errorMessage))

      await expect(alarmsStore.updateAlarm(alarm)).rejects.toThrow(errorMessage)
    })

    it('no actualiza si la alarma no existe en el store', async () => {
      const alarm = { id: 999, nombre: 'Alarma Inexistente' }
      axios.put.mockResolvedValue({ data: alarm })

      await alarmsStore.updateAlarm(alarm)

      // Debería agregar la alarma si no existe
      expect(alarmsStore.alarms).toContain(alarm)
    })
  })

  describe('Actions - deleteAlarm', () => {
    it('elimina una alarma exitosamente', async () => {
      const alarm = { id: 1, nombre: 'Alarma a eliminar' }
      alarmsStore.alarms = [alarm]

      axios.delete.mockResolvedValue({ data: { success: true } })

      await alarmsStore.deleteAlarm(1)

      expect(alarmsStore.alarms).not.toContain(alarm)
      expect(alarmsStore.alarms).toHaveLength(0)
      expect(axios.delete).toHaveBeenCalledWith('/api/alarms/1')
    })

    it('maneja errores al eliminar alarma', async () => {
      const alarm = { id: 1, nombre: 'Alarma' }
      alarmsStore.alarms = [alarm]
      const errorMessage = 'Error al eliminar'

      axios.delete.mockRejectedValue(new Error(errorMessage))

      await expect(alarmsStore.deleteAlarm(1)).rejects.toThrow(errorMessage)
      expect(alarmsStore.alarms).toContain(alarm) // La alarma debe seguir ahí
    })
  })

  describe('Actions - toggleAlarm', () => {
    it('activa/desactiva una alarma', async () => {
      const alarm = { id: 1, nombre: 'Alarma', activa: false }
      alarmsStore.alarms = [alarm]

      axios.patch.mockResolvedValue({
        data: { ...alarm, activa: true }
      })

      await alarmsStore.toggleAlarm(1)

      expect(alarmsStore.alarms[0].activa).toBe(true)
      expect(axios.patch).toHaveBeenCalledWith('/api/alarms/1/toggle')
    })
  })

  describe('Integración con localStorage', () => {
    it('guarda alarmas en localStorage después de fetch', async () => {
      const mockAlarms = [{ id: 1, nombre: 'Alarma 1' }]
      axios.get.mockResolvedValue({ data: mockAlarms })

      await alarmsStore.fetchAlarms()

      expect(localStorage.setItem).toHaveBeenCalledWith(
        'tidy_alarms',
        JSON.stringify(mockAlarms)
      )
    })

    it('carga alarmas desde localStorage si no hay conexión', async () => {
      const cachedAlarms = [{ id: 1, nombre: 'Alarma Cached' }]
      localStorage.getItem.mockReturnValue(JSON.stringify(cachedAlarms))
      axios.get.mockRejectedValue(new Error('Sin conexión'))

      await alarmsStore.fetchAlarms()

      expect(alarmsStore.alarms).toEqual(cachedAlarms)
    })
  })

  describe('Filtros y búsqueda', () => {
    beforeEach(() => {
      alarmsStore.alarms = [
        { id: 1, nombre: 'Alarma Trabajo', tipo: 'trabajo' },
        { id: 2, nombre: 'Alarma Personal', tipo: 'personal' },
        { id: 3, nombre: 'Recordatorio Médico', tipo: 'salud' }
      ]
    })

    it('filtra alarmas por tipo', () => {
      const workAlarms = alarmsStore.getAlarmsByType('trabajo')
      expect(workAlarms).toHaveLength(1)
      expect(workAlarms[0].nombre).toBe('Alarma Trabajo')
    })

    it('busca alarmas por nombre', () => {
      const searchResults = alarmsStore.searchAlarms('Médico')
      expect(searchResults).toHaveLength(1)
      expect(searchResults[0].nombre).toBe('Recordatorio Médico')
    })

    it('búsqueda insensible a mayúsculas', () => {
      const searchResults = alarmsStore.searchAlarms('trabajo')
      expect(searchResults).toHaveLength(1)
    })
  })

  describe('Manejo de estado de carga', () => {
    it('resetea error al iniciar nueva operación', async () => {
      alarmsStore.error = 'Error previo'

      axios.get.mockResolvedValue({ data: [] })
      await alarmsStore.fetchAlarms()

      expect(alarmsStore.error).toBeNull()
    })

    it('mantiene alarms anteriores si fetch falla', async () => {
      const existingAlarms = [{ id: 1, nombre: 'Alarma Existente' }]
      alarmsStore.alarms = existingAlarms

      axios.get.mockRejectedValue(new Error('Error de red'))
      await alarmsStore.fetchAlarms()

      expect(alarmsStore.alarms).toEqual(existingAlarms)
    })
  })
})