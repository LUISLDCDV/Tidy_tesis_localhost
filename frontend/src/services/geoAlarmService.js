/**
 * Servicio para alarmas basadas en geolocalización
 * Maneja geofencing y notificaciones por ubicación
 */

class GeoAlarmService {
  constructor() {
    this.watchId = null;
    this.geoAlarms = new Map(); // almacén local de alarmas geo
    this.currentPosition = null;
    this.isWatching = false;
    this.options = {
      enableHighAccuracy: true,
      timeout: 15000,
      maximumAge: 60000 // 1 minuto
    };
  }

  /**
   * Inicializa el servicio de geolocalización
   */
  async initialize() {
    if (!('geolocation' in navigator)) {
      throw new Error('Geolocalización no soportada en este navegador');
    }

    try {
      // Solicitar posición inicial
      const position = await this.getCurrentPosition();
      this.currentPosition = position;
      
      console.log('GeoAlarmService inicializado correctamente', position);
      return position;
    } catch (error) {
      console.error('Error al inicializar GeoAlarmService:', error);
      throw error;
    }
  }

  /**
   * Obtiene la posición actual del usuario
   */
  getCurrentPosition() {
    return new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          this.currentPosition = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
            timestamp: position.timestamp
          };
          resolve(this.currentPosition);
        },
        (error) => {
          const errorMessages = {
            1: 'Acceso a la ubicación denegado por el usuario',
            2: 'Ubicación no disponible',
            3: 'Tiempo de espera agotado al obtener la ubicación'
          };
          reject(new Error(errorMessages[error.code] || 'Error desconocido de geolocalización'));
        },
        this.options
      );
    });
  }

  /**
   * Inicia el monitoreo de la ubicación
   */
  startLocationWatching() {
    if (this.isWatching) {
      console.log('Ya se está monitoreando la ubicación');
      return;
    }

    this.watchId = navigator.geolocation.watchPosition(
      (position) => {
        const newPosition = {
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
          accuracy: position.coords.accuracy,
          timestamp: position.timestamp
        };

        this.currentPosition = newPosition;
        this.checkGeoAlarms(newPosition);
      },
      (error) => {
        console.error('Error en watchPosition:', error);
      },
      this.options
    );

    this.isWatching = true;
    console.log('Monitoreo de ubicación iniciado');
  }

  /**
   * Detiene el monitoreo de la ubicación
   */
  stopLocationWatching() {
    if (this.watchId !== null) {
      navigator.geolocation.clearWatch(this.watchId);
      this.watchId = null;
      this.isWatching = false;
      console.log('Monitoreo de ubicación detenido');
    }
  }

  /**
   * Crea una nueva alarma geográfica
   * @param {Object} alarmData - Datos de la alarma
   * @returns {string} - ID de la alarma
   */
  createGeoAlarm(alarmData) {
    const alarm = {
      id: Date.now().toString() + Math.random().toString(36).substr(2, 9),
      name: alarmData.name || 'Alarma sin nombre',
      latitude: alarmData.latitude,
      longitude: alarmData.longitude,
      radius: alarmData.radius || 100, // metros
      type: alarmData.type || 'enter', // 'enter', 'exit', 'both'
      message: alarmData.message || 'Has llegado a tu destino',
      isActive: true,
      triggered: false,
      lastTriggered: null,
      createdAt: new Date().toISOString(),
      color: alarmData.color || '#176F46',
      icon: alarmData.icon || 'location_on',
      repeatCount: alarmData.repeatCount || 0, // 0 = infinito
      currentCount: 0
    };

    this.geoAlarms.set(alarm.id, alarm);
    this.saveAlarmsToStorage();

    // Iniciar monitoreo si es la primera alarma
    if (this.geoAlarms.size === 1 && !this.isWatching) {
      this.startLocationWatching();
    }

    console.log('Alarma geográfica creada:', alarm);
    return alarm.id;
  }

  /**
   * Actualiza una alarma existente
   * @param {string} alarmId - ID de la alarma
   * @param {Object} updateData - Datos a actualizar
   */
  updateGeoAlarm(alarmId, updateData) {
    const alarm = this.geoAlarms.get(alarmId);
    if (!alarm) {
      throw new Error('Alarma no encontrada');
    }

    Object.assign(alarm, updateData);
    this.geoAlarms.set(alarmId, alarm);
    this.saveAlarmsToStorage();

    console.log('Alarma geográfica actualizada:', alarm);
  }

  /**
   * Elimina una alarma geográfica
   * @param {string} alarmId - ID de la alarma
   */
  deleteGeoAlarm(alarmId) {
    const deleted = this.geoAlarms.delete(alarmId);
    
    if (deleted) {
      this.saveAlarmsToStorage();
      
      // Detener monitoreo si no hay más alarmas
      if (this.geoAlarms.size === 0) {
        this.stopLocationWatching();
      }
      
      console.log('Alarma geográfica eliminada:', alarmId);
    }
    
    return deleted;
  }

  /**
   * Obtiene todas las alarmas geográficas
   * @returns {Array} - Lista de alarmas
   */
  getAllGeoAlarms() {
    return Array.from(this.geoAlarms.values());
  }

  /**
   * Obtiene una alarma específica
   * @param {string} alarmId - ID de la alarma
   * @returns {Object|null} - Alarma o null si no existe
   */
  getGeoAlarm(alarmId) {
    return this.geoAlarms.get(alarmId) || null;
  }

  /**
   * Verifica si alguna alarma debe activarse
   * @param {Object} position - Posición actual
   */
  checkGeoAlarms(position) {
    for (const alarm of this.geoAlarms.values()) {
      if (!alarm.isActive) continue;

      const distance = this.calculateDistance(
        position.latitude,
        position.longitude,
        alarm.latitude,
        alarm.longitude
      );

      const isInsideRadius = distance <= alarm.radius;
      const wasTriggered = alarm.triggered;

      // Lógica de activación según el tipo
      let shouldTrigger = false;

      if (alarm.type === 'enter' && isInsideRadius && !wasTriggered) {
        shouldTrigger = true;
      } else if (alarm.type === 'exit' && !isInsideRadius && wasTriggered) {
        shouldTrigger = true;
      } else if (alarm.type === 'both') {
        if ((isInsideRadius && !wasTriggered) || (!isInsideRadius && wasTriggered)) {
          shouldTrigger = true;
        }
      }

      if (shouldTrigger) {
        this.triggerGeoAlarm(alarm, position, isInsideRadius);
      }

      // Actualizar estado
      alarm.triggered = isInsideRadius;
    }
  }

  /**
   * Activa una alarma geográfica
   * @param {Object} alarm - Alarma a activar
   * @param {Object} position - Posición actual
   * @param {boolean} entering - Si está entrando o saliendo
   */
  async triggerGeoAlarm(alarm, position, entering) {
    // Verificar límite de repeticiones
    if (alarm.repeatCount > 0 && alarm.currentCount >= alarm.repeatCount) {
      return;
    }

    const triggerTime = new Date();
    alarm.lastTriggered = triggerTime.toISOString();
    alarm.currentCount++;

    const message = entering 
      ? alarm.message || `Has entrado en el área de "${alarm.name}"`
      : `Has salido del área de "${alarm.name}"`;

    // Crear notificación
    try {
      await this.createNotification(alarm, message, entering);
    } catch (error) {
      console.error('Error al crear notificación:', error);
    }

    // Emitir evento personalizado
    window.dispatchEvent(new CustomEvent('geoAlarmTriggered', {
      detail: {
        alarm,
        position,
        entering,
        message,
        timestamp: triggerTime
      }
    }));

    // Guardar cambios
    this.saveAlarmsToStorage();

    console.log(`Alarma geográfica activada: ${alarm.name}`, {
      entering,
      distance: this.calculateDistance(
        position.latitude,
        position.longitude,
        alarm.latitude,
        alarm.longitude
      )
    });
  }

  /**
   * Crea una notificación del navegador
   * @param {Object} alarm - Alarma
   * @param {string} message - Mensaje
   * @param {boolean} entering - Si está entrando
   */
  async createNotification(alarm, message, entering) {
    // Verificar permisos de notificación
    if (Notification.permission !== 'granted') {
      const permission = await Notification.requestPermission();
      if (permission !== 'granted') {
        throw new Error('Permisos de notificación denegados');
      }
    }

    const notification = new Notification(`Tidy - ${alarm.name}`, {
      body: message,
      icon: '/favicon.ico',
      badge: '/favicon.ico',
      tag: `geo-alarm-${alarm.id}`,
      requireInteraction: true,
      data: {
        alarmId: alarm.id,
        entering,
        timestamp: Date.now()
      }
    });

    // Auto-cerrar después de 10 segundos
    setTimeout(() => {
      notification.close();
    }, 10000);

    return notification;
  }

  /**
   * Calcula la distancia entre dos puntos geográficos
   * @param {number} lat1 - Latitud del punto 1
   * @param {number} lon1 - Longitud del punto 1
   * @param {number} lat2 - Latitud del punto 2
   * @param {number} lon2 - Longitud del punto 2
   * @returns {number} - Distancia en metros
   */
  calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // Radio de la Tierra en metros
    const φ1 = lat1 * Math.PI / 180;
    const φ2 = lat2 * Math.PI / 180;
    const Δφ = (lat2 - lat1) * Math.PI / 180;
    const Δλ = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ/2) * Math.sin(Δλ/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

    return R * c; // Distancia en metros
  }

  /**
   * Activa o desactiva una alarma
   * @param {string} alarmId - ID de la alarma
   * @param {boolean} isActive - Estado activo
   */
  toggleAlarm(alarmId, isActive) {
    const alarm = this.geoAlarms.get(alarmId);
    if (alarm) {
      alarm.isActive = isActive;
      this.saveAlarmsToStorage();
    }
  }

  /**
   * Obtiene estadísticas de las alarmas
   * @returns {Object} - Estadísticas
   */
  getAlarmStats() {
    const alarms = this.getAllGeoAlarms();
    
    return {
      total: alarms.length,
      active: alarms.filter(a => a.isActive).length,
      triggered: alarms.filter(a => a.currentCount > 0).length,
      mostTriggered: alarms.reduce((max, alarm) => 
        alarm.currentCount > (max?.currentCount || 0) ? alarm : max, null
      )
    };
  }

  /**
   * Guarda las alarmas en localStorage
   */
  saveAlarmsToStorage() {
    try {
      const alarmsData = Array.from(this.geoAlarms.entries());
      localStorage.setItem('geoAlarms', JSON.stringify(alarmsData));
    } catch (error) {
      console.error('Error al guardar alarmas en localStorage:', error);
    }
  }

  /**
   * Carga las alarmas desde localStorage
   */
  loadAlarmsFromStorage() {
    try {
      const saved = localStorage.getItem('geoAlarms');
      if (saved) {
        const alarmsData = JSON.parse(saved);
        this.geoAlarms = new Map(alarmsData);
        
        // Iniciar monitoreo si hay alarmas activas
        const hasActiveAlarms = Array.from(this.geoAlarms.values())
          .some(alarm => alarm.isActive);
          
        if (hasActiveAlarms && !this.isWatching) {
          this.startLocationWatching();
        }
      }
    } catch (error) {
      console.error('Error al cargar alarmas desde localStorage:', error);
      this.geoAlarms.clear();
    }
  }

  /**
   * Limpia todos los datos y detiene el monitoreo
   */
  destroy() {
    this.stopLocationWatching();
    this.geoAlarms.clear();
    this.currentPosition = null;
  }

  /**
   * Exporta las alarmas para backup
   * @returns {string} - JSON de las alarmas
   */
  exportAlarms() {
    const alarms = this.getAllGeoAlarms();
    return JSON.stringify(alarms, null, 2);
  }

  /**
   * Importa alarmas desde un backup
   * @param {string} jsonData - JSON de las alarmas
   */
  importAlarms(jsonData) {
    try {
      const alarms = JSON.parse(jsonData);
      
      if (!Array.isArray(alarms)) {
        throw new Error('Formato de datos inválido');
      }

      // Validar y agregar alarmas
      for (const alarm of alarms) {
        if (this.validateAlarmData(alarm)) {
          this.geoAlarms.set(alarm.id, alarm);
        }
      }

      this.saveAlarmsToStorage();
      console.log(`${alarms.length} alarmas importadas`);
    } catch (error) {
      console.error('Error al importar alarmas:', error);
      throw error;
    }
  }

  /**
   * Valida los datos de una alarma
   * @param {Object} alarm - Datos de la alarma
   * @returns {boolean} - Es válida
   */
  validateAlarmData(alarm) {
    return alarm &&
           typeof alarm.id === 'string' &&
           typeof alarm.latitude === 'number' &&
           typeof alarm.longitude === 'number' &&
           typeof alarm.radius === 'number' &&
           alarm.radius > 0;
  }
}

// Singleton
const geoAlarmService = new GeoAlarmService();

// Cargar alarmas al inicializar
geoAlarmService.loadAlarmsFromStorage();

export default geoAlarmService;