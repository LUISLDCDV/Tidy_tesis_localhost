import { describe, it, expect, beforeEach, vi } from 'vitest'
import geoAlarmService from '../geoAlarmService'

describe('geoAlarmService', () => {
  beforeEach(() => {
    // Limpiar localStorage mock
    localStorage.clear()
    vi.clearAllMocks()

    // Resetear el servicio
    geoAlarmService.geoAlarms.clear()
    geoAlarmService.currentPosition = null
    geoAlarmService.isWatching = false
    geoAlarmService.watchId = null
  })

  describe('Inicialización', () => {
    it('inicializa correctamente', async () => {
      const mockPosition = {
        coords: {
          latitude: -34.6037,
          longitude: -58.3816,
          accuracy: 10
        },
        timestamp: Date.now()
      }

      navigator.geolocation.getCurrentPosition.mockImplementation((success) => {
        success(mockPosition)
      })

      const position = await geoAlarmService.initialize()

      expect(position).toEqual({
        latitude: -34.6037,
        longitude: -58.3816,
        accuracy: 10,
        timestamp: expect.any(Number)
      })
    })

    it('maneja errores de inicialización', async () => {
      navigator.geolocation.getCurrentPosition.mockImplementation((success, error) => {
        error({ code: 1 })
      })

      await expect(geoAlarmService.initialize()).rejects.toThrow('Acceso a la ubicación denegado')
    })
  })

  describe('Gestión de posición', () => {
    it('obtiene la posición actual', async () => {
      const mockPosition = {
        coords: {
          latitude: -34.6037,
          longitude: -58.3816,
          accuracy: 10
        },
        timestamp: Date.now()
      }

      navigator.geolocation.getCurrentPosition.mockImplementation((success) => {
        success(mockPosition)
      })

      const position = await geoAlarmService.getCurrentPosition()

      expect(position.latitude).toBe(-34.6037)
      expect(position.longitude).toBe(-58.3816)
    })

    it('inicia el monitoreo de ubicación', () => {
      navigator.geolocation.watchPosition.mockReturnValue(123)

      geoAlarmService.startLocationWatching()

      expect(navigator.geolocation.watchPosition).toHaveBeenCalled()
      expect(geoAlarmService.isWatching).toBe(true)
      expect(geoAlarmService.watchId).toBe(123)
    })

    it('detiene el monitoreo de ubicación', () => {
      geoAlarmService.watchId = 123
      geoAlarmService.isWatching = true

      geoAlarmService.stopLocationWatching()

      expect(navigator.geolocation.clearWatch).toHaveBeenCalledWith(123)
      expect(geoAlarmService.isWatching).toBe(false)
      expect(geoAlarmService.watchId).toBeNull()
    })
  })

  describe('Gestión de alarmas geográficas', () => {
    it('crea una nueva alarma geográfica', () => {
      const alarmData = {
        name: 'Casa',
        latitude: -34.6037,
        longitude: -58.3816,
        radius: 100,
        message: 'Has llegado a casa'
      }

      const alarmId = geoAlarmService.createGeoAlarm(alarmData)

      expect(alarmId).toBeDefined()
      expect(typeof alarmId).toBe('string')

      const alarm = geoAlarmService.getGeoAlarm(alarmId)
      expect(alarm.name).toBe('Casa')
      expect(alarm.latitude).toBe(-34.6037)
      expect(alarm.longitude).toBe(-58.3816)
      expect(alarm.radius).toBe(100)
      expect(alarm.isActive).toBe(true)
    })

    it('actualiza una alarma existente', () => {
      const alarmId = geoAlarmService.createGeoAlarm({
        name: 'Test',
        latitude: 0,
        longitude: 0,
        radius: 50
      })

      geoAlarmService.updateGeoAlarm(alarmId, {
        name: 'Test Actualizado',
        radius: 100
      })

      const alarm = geoAlarmService.getGeoAlarm(alarmId)
      expect(alarm.name).toBe('Test Actualizado')
      expect(alarm.radius).toBe(100)
    })

    it('elimina una alarma geográfica', () => {
      const alarmId = geoAlarmService.createGeoAlarm({
        name: 'Test',
        latitude: 0,
        longitude: 0,
        radius: 50
      })

      const deleted = geoAlarmService.deleteGeoAlarm(alarmId)

      expect(deleted).toBe(true)
      expect(geoAlarmService.getGeoAlarm(alarmId)).toBeNull()
    })

    it('obtiene todas las alarmas', () => {
      geoAlarmService.createGeoAlarm({
        name: 'Alarma 1',
        latitude: 0,
        longitude: 0,
        radius: 50
      })

      geoAlarmService.createGeoAlarm({
        name: 'Alarma 2',
        latitude: 1,
        longitude: 1,
        radius: 100
      })

      const alarms = geoAlarmService.getAllGeoAlarms()
      expect(alarms).toHaveLength(2)
    })
  })

  describe('Cálculo de distancia', () => {
    it('calcula la distancia correctamente', () => {
      // Buenos Aires a La Plata (aproximadamente 56km)
      const distance = geoAlarmService.calculateDistance(
        -34.6037, -58.3816, // Buenos Aires
        -34.9214, -57.9544  // La Plata
      )

      expect(distance).toBeGreaterThan(50000) // > 50km
      expect(distance).toBeLessThan(70000)    // < 70km
    })

    it('calcula distancia cero para el mismo punto', () => {
      const distance = geoAlarmService.calculateDistance(
        -34.6037, -58.3816,
        -34.6037, -58.3816
      )

      expect(distance).toBe(0)
    })
  })

  describe('Verificación de alarmas', () => {
    it('activa alarma al entrar en zona', () => {
      const alarmId = geoAlarmService.createGeoAlarm({
        name: 'Test Zone',
        latitude: -34.6037,
        longitude: -58.3816,
        radius: 100,
        type: 'enter'
      })

      const position = {
        latitude: -34.6037,
        longitude: -58.3816,
        accuracy: 10,
        timestamp: Date.now()
      }

      const triggerSpy = vi.spyOn(geoAlarmService, 'triggerGeoAlarm')

      geoAlarmService.checkGeoAlarms(position)

      expect(triggerSpy).toHaveBeenCalled()
    })

    it('no activa alarma si ya fue activada', () => {
      const alarmId = geoAlarmService.createGeoAlarm({
        name: 'Test Zone',
        latitude: -34.6037,
        longitude: -58.3816,
        radius: 100,
        type: 'enter'
      })

      const alarm = geoAlarmService.getGeoAlarm(alarmId)
      alarm.triggered = true

      const position = {
        latitude: -34.6037,
        longitude: -58.3816,
        accuracy: 10,
        timestamp: Date.now()
      }

      const triggerSpy = vi.spyOn(geoAlarmService, 'triggerGeoAlarm')

      geoAlarmService.checkGeoAlarms(position)

      expect(triggerSpy).not.toHaveBeenCalled()
    })
  })

  describe('Notificaciones', () => {
    it('crea notificación correctamente', async () => {
      // Mock de Notification
      global.Notification = vi.fn()
      global.Notification.permission = 'granted'

      const alarm = {
        id: '123',
        name: 'Test Alarm'
      }

      await geoAlarmService.createNotification(alarm, 'Test message', true)

      expect(global.Notification).toHaveBeenCalledWith(
        'Tidy - Test Alarm',
        expect.objectContaining({
          body: 'Test message'
        })
      )
    })

    it('solicita permisos de notificación', async () => {
      // Mock de Notification con requestPermission
      const MockNotificationWithPermission = vi.fn()
      MockNotificationWithPermission.permission = 'default'
      MockNotificationWithPermission.requestPermission = vi.fn().mockResolvedValue('granted')

      global.Notification = MockNotificationWithPermission

      const alarm = { id: '123', name: 'Test' }

      await geoAlarmService.createNotification(alarm, 'Test', true)

      expect(MockNotificationWithPermission.requestPermission).toHaveBeenCalled()
    })
  })

  describe('Persistencia', () => {
    it('guarda alarmas en localStorage', () => {
      geoAlarmService.createGeoAlarm({
        name: 'Test',
        latitude: 0,
        longitude: 0,
        radius: 50
      })

      expect(localStorage.setItem).toHaveBeenCalledWith(
        'geoAlarms',
        expect.any(String)
      )
    })

    it('carga alarmas desde localStorage', () => {
      const alarmData = [[
        'test-id',
        {
          id: 'test-id',
          name: 'Test Alarm',
          latitude: 0,
          longitude: 0,
          radius: 50,
          isActive: true
        }
      ]]

      localStorage.getItem.mockReturnValue(JSON.stringify(alarmData))

      geoAlarmService.loadAlarmsFromStorage()

      expect(geoAlarmService.getAllGeoAlarms()).toHaveLength(1)
    })
  })

  describe('Estadísticas', () => {
    it('proporciona estadísticas correctas', () => {
      // Crear algunas alarmas de prueba
      const id1 = geoAlarmService.createGeoAlarm({
        name: 'Alarm 1',
        latitude: 0,
        longitude: 0,
        radius: 50
      })

      const id2 = geoAlarmService.createGeoAlarm({
        name: 'Alarm 2',
        latitude: 1,
        longitude: 1,
        radius: 100
      })

      // Marcar una como inactiva y otra como activada
      geoAlarmService.toggleAlarm(id2, false)
      const alarm1 = geoAlarmService.getGeoAlarm(id1)
      alarm1.currentCount = 5

      const stats = geoAlarmService.getAlarmStats()

      expect(stats.total).toBe(2)
      expect(stats.active).toBe(1)
      expect(stats.triggered).toBe(1)
      expect(stats.mostTriggered.id).toBe(id1)
    })
  })

  describe('Validación', () => {
    it('valida datos de alarma correctamente', () => {
      const validAlarm = {
        id: 'test-id',
        latitude: -34.6037,
        longitude: -58.3816,
        radius: 100
      }

      const invalidAlarm = {
        id: 'test-id',
        latitude: 'invalid',
        longitude: -58.3816,
        radius: 100
      }

      expect(geoAlarmService.validateAlarmData(validAlarm)).toBe(true)
      expect(geoAlarmService.validateAlarmData(invalidAlarm)).toBe(false)
    })
  })
})