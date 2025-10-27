/**
 * Servicio para gestionar notificaciones de eventos
 * Maneja recordatorios, alertas y notificaciones push para eventos del calendario
 */

import { useNotificationsStore } from '@/stores/notifications'

class EventNotificationService {
  constructor() {
    this.notificationStore = null // Se inicializará cuando se use por primera vez
    this.scheduledNotifications = new Map() // Para almacenar notificaciones programadas
    this.checkInterval = null
  }

  /**
   * Obtiene o inicializa el store de notificaciones
   */
  getNotificationStore() {
    if (!this.notificationStore) {
      try {
        this.notificationStore = useNotificationsStore()
      } catch (error) {
        console.warn('No se pudo acceder al store de notificaciones:', error)
        return null
      }
    }
    return this.notificationStore
  }

  /**
   * Inicializa el servicio de notificaciones de eventos
   */
  initialize() {
    // Solicitar permisos de notificación si no los tenemos
    this.requestNotificationPermission()
    
    // Iniciar el chequeo periódico de eventos próximos
    this.startPeriodicCheck()
    
    console.log('Servicio de notificaciones de eventos inicializado')
  }

  /**
   * Solicita permisos para mostrar notificaciones del navegador
   */
  async requestNotificationPermission() {
    if (!('Notification' in window)) {
      console.warn('Este navegador no soporta notificaciones')
      return false
    }

    const store = this.getNotificationStore()

    if (Notification.permission === 'granted') {
      if (store) store.updateNotificationPermission('granted')
      return true
    }

    if (Notification.permission !== 'denied') {
      const permission = await Notification.requestPermission()
      if (store) store.updateNotificationPermission(permission)
      return permission === 'granted'
    }

    if (store) store.updateNotificationPermission('denied')
    return false
  }

  /**
   * Programa una notificación para un evento
   * @param {Object} evento - Objeto del evento
   * @param {number} minutosAntes - Minutos antes del evento para mostrar la notificación
   */
  scheduleEventNotification(evento, minutosAntes = 15) {
    if (!evento.fechaVencimiento || !evento.horaVencimiento) {
      console.warn('Evento sin fecha/hora válida:', evento)
      return
    }

    try {
      // Construir la fecha y hora del evento
      const eventDateTime = new Date(`${evento.fechaVencimiento}T${evento.horaVencimiento}`)
      
      // Calcular cuándo mostrar la notificación
      const notificationTime = new Date(eventDateTime.getTime() - (minutosAntes * 60 * 1000))
      const now = new Date()

      // Solo programar si la notificación es en el futuro
      if (notificationTime > now) {
        const timeoutMs = notificationTime.getTime() - now.getTime()
        
        const timeoutId = setTimeout(() => {
          this.showEventNotification(evento, minutosAntes)
          this.scheduledNotifications.delete(evento.id)
        }, timeoutMs)

        // Guardar referencia para poder cancelar después
        this.scheduledNotifications.set(evento.id, {
          timeoutId,
          evento,
          minutosAntes,
          notificationTime
        })

        console.log(`Notificación programada para evento "${evento.nombre}" en ${minutosAntes} minutos antes`)
      }
    } catch (error) {
      console.error('Error al programar notificación del evento:', error)
    }
  }

  /**
   * Cancela una notificación programada
   * @param {string|number} eventoId - ID del evento
   */
  cancelEventNotification(eventoId) {
    const scheduledNotification = this.scheduledNotifications.get(eventoId)
    if (scheduledNotification) {
      clearTimeout(scheduledNotification.timeoutId)
      this.scheduledNotifications.delete(eventoId)
      console.log(`Notificación cancelada para evento ID: ${eventoId}`)
    }
  }

  /**
   * Reprograma las notificaciones para una lista de eventos
   * @param {Array} eventos - Lista de eventos
   */
  rescheduleAllNotifications(eventos) {
    // Cancelar todas las notificaciones existentes
    this.cancelAllNotifications()
    
    // Programar notificaciones para eventos próximos
    eventos.forEach(evento => {
      // Programar múltiples recordatorios
      this.scheduleEventNotification(evento, 60)  // 1 hora antes
      this.scheduleEventNotification(evento, 15)  // 15 minutos antes
      this.scheduleEventNotification(evento, 5)   // 5 minutos antes
    })
  }

  /**
   * Cancela todas las notificaciones programadas
   */
  cancelAllNotifications() {
    this.scheduledNotifications.forEach(({ timeoutId }) => {
      clearTimeout(timeoutId)
    })
    this.scheduledNotifications.clear()
    console.log('Todas las notificaciones canceladas')
  }

  /**
   * Muestra una notificación de evento
   * @param {Object} evento - Objeto del evento
   * @param {number} minutosAntes - Minutos antes del evento
   */
  async showEventNotification(evento, minutosAntes) {
    const title = `Recordatorio: ${evento.nombre}`
    let body = `Tu evento comienza`
    
    if (minutosAntes > 0) {
      if (minutosAntes === 60) {
        body += ' en 1 hora'
      } else {
        body += ` en ${minutosAntes} minutos`
      }
    } else {
      body += ' ahora'
    }

    if (evento.informacion) {
      body += `\n${evento.informacion}`
    }

    // Mostrar notificación del navegador si tenemos permisos
    if (Notification.permission === 'granted') {
      const notification = new Notification(title, {
        body,
        icon: '/favicon.ico',
        badge: '/favicon.ico',
        tag: `event-${evento.id}-${minutosAntes}`,
        requireInteraction: minutosAntes <= 5, // Requerir interacción para notificaciones urgentes
        actions: [
          { action: 'view', title: 'Ver evento' },
          { action: 'dismiss', title: 'Descartar' }
        ]
      })

      notification.onclick = () => {
        // Enfocar la ventana y navegar al evento
        window.focus()
        this.handleNotificationClick(evento)
        notification.close()
      }

      // Auto-cerrar después de 10 segundos
      setTimeout(() => notification.close(), 10000)
    }

    // Agregar a la lista de notificaciones internas
    const store = this.getNotificationStore()
    if (store) {
      store.addNotification({
        id: `event-${evento.id}-${minutosAntes}-${Date.now()}`,
        type: 'event_reminder',
        title,
        message: body,
        data: { evento, minutosAntes },
        timestamp: new Date()
      })
    }

    // Mostrar notificación visual en la aplicación
    this.showInAppNotification(title, body, evento)
  }

  /**
   * Muestra una notificación visual dentro de la aplicación
   * @param {string} title - Título de la notificación
   * @param {string} body - Cuerpo de la notificación
   * @param {Object} evento - Objeto del evento
   */
  showInAppNotification(title, body, evento) {
    // Usar Quasar Notify para mostrar la notificación
    if (window.$q) {
      window.$q.notify({
        type: 'info',
        title,
        message: body,
        position: 'top-right',
        timeout: 8000,
        actions: [
          {
            label: 'Ver',
            color: 'white',
            handler: () => this.handleNotificationClick(evento)
          },
          {
            label: 'Cerrar',
            color: 'white',
            handler: () => {}
          }
        ]
      })
    }
  }

  /**
   * Maneja el clic en una notificación
   * @param {Object} evento - Objeto del evento
   */
  handleNotificationClick(evento) {
    // Emitir evento personalizado para que la aplicación pueda reaccionar
    window.dispatchEvent(new CustomEvent('notification-event-click', {
      detail: { evento }
    }))
  }

  /**
   * Inicia el chequeo periódico de eventos próximos
   */
  startPeriodicCheck() {
    // Chequear cada 5 minutos por eventos próximos
    this.checkInterval = setInterval(() => {
      this.checkUpcomingEvents()
    }, 5 * 60 * 1000) // 5 minutos
  }

  /**
   * Detiene el chequeo periódico
   */
  stopPeriodicCheck() {
    if (this.checkInterval) {
      clearInterval(this.checkInterval)
      this.checkInterval = null
    }
  }

  /**
   * Verifica eventos próximos que necesiten notificación
   */
  async checkUpcomingEvents() {
    try {
      // Obtener eventos desde el store o API
      const eventos = await this.getUpcomingEvents()
      
      eventos.forEach(evento => {
        // Verificar si el evento necesita recordatorios
        if (!this.scheduledNotifications.has(evento.id)) {
          this.scheduleEventNotification(evento, 15)
        }
      })
    } catch (error) {
      console.error('Error al verificar eventos próximos:', error)
    }
  }

  /**
   * Obtiene eventos próximos (en las próximas 24 horas)
   * @returns {Array} Lista de eventos próximos
   */
  async getUpcomingEvents() {
    // Esto debería conectarse con el store de eventos o API
    // Por ahora retornamos un array vacío
    return []
  }

  /**
   * Notifica cuando se crea un nuevo evento
   * @param {Object} evento - Evento creado
   */
  notifyEventCreated(evento) {
    const store = this.getNotificationStore()
    if (store) {
      store.addNotification({
        id: `event-created-${evento.id}-${Date.now()}`,
        type: 'element_created',
        title: 'Evento creado',
        message: `El evento "${evento.nombre}" ha sido creado exitosamente`,
        data: { evento },
        timestamp: new Date()
      })
    }

    // Programar notificaciones para el nuevo evento
    this.scheduleEventNotification(evento, 60)  // 1 hora antes
    this.scheduleEventNotification(evento, 15)  // 15 minutos antes
    this.scheduleEventNotification(evento, 5)   // 5 minutos antes
  }

  /**
   * Notifica cuando se actualiza un evento
   * @param {Object} evento - Evento actualizado
   */
  notifyEventUpdated(evento) {
    const store = this.getNotificationStore()
    if (store) {
      store.addNotification({
        id: `event-updated-${evento.id}-${Date.now()}`,
        type: 'element_updated',
        title: 'Evento actualizado',
        message: `El evento "${evento.nombre}" ha sido actualizado`,
        data: { evento },
        timestamp: new Date()
      })
    }

    // Cancelar notificaciones anteriores y reprogramar
    this.cancelEventNotification(evento.id)
    this.scheduleEventNotification(evento, 60)
    this.scheduleEventNotification(evento, 15)
    this.scheduleEventNotification(evento, 5)
  }

  /**
   * Notifica cuando se elimina un evento
   * @param {Object} evento - Evento eliminado
   */
  notifyEventDeleted(evento) {
    const store = this.getNotificationStore()
    if (store) {
      store.addNotification({
        id: `event-deleted-${evento.id}-${Date.now()}`,
        type: 'element_deleted',
        title: 'Evento eliminado',
        message: `El evento "${evento.nombre}" ha sido eliminado`,
        data: { evento },
        timestamp: new Date()
      })
    }

    // Cancelar notificaciones del evento eliminado
    this.cancelEventNotification(evento.id)
  }

  /**
   * Configura recordatorios personalizados para un evento
   * @param {string|number} eventoId - ID del evento
   * @param {Array} recordatorios - Array de minutos antes para recordar [60, 30, 15, 5]
   */
  setCustomReminders(eventoId, recordatorios = [60, 15, 5]) {
    // Cancelar recordatorios existentes
    this.cancelEventNotification(eventoId)
    
    // Programar nuevos recordatorios
    recordatorios.forEach(minutos => {
      this.scheduleEventNotification({ id: eventoId }, minutos)
    })
  }

  /**
   * Limpia todos los recursos del servicio
   */
  destroy() {
    this.stopPeriodicCheck()
    this.cancelAllNotifications()
    console.log('Servicio de notificaciones de eventos destruido')
  }
}

// Crear instancia singleton
const eventNotificationService = new EventNotificationService()

export default eventNotificationService