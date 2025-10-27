// Sistema de cola de peticiones para evitar spam al backend
class RequestQueue {
  constructor() {
    this.queue = new Map() // Usar Map para evitar peticiones duplicadas
    this.isProcessing = false
    this.processingDelay = 50 // Delay entre peticiones reducido para mejor UX
    this.gracePeriod = 0 // Deshabilitado temporalmente para debug
    this.lastProcessedTime = null
    this.processingTimeout = 10000 // Timeout de 10 segundos para evitar cuelgues
    this.currentProcessingTimer = null
  }

  // Añadir petición a la cola (evita duplicados por clave)
  async addRequest(key, requestFn) {
    const timestamp = new Date().toISOString()
    console.log(`🔍 [${timestamp}] Intentando añadir petición: ${key}`)

    return new Promise((resolve, reject) => {
      // Si ya existe una petición con esta clave, esperar a que termine
      if (this.queue.has(key)) {
        const existingCallbacks = this.queue.get(key).callbacks.length
        console.log(`🔄 [${timestamp}] Petición ya en cola: ${key} (${existingCallbacks} callbacks existentes)`)
        this.queue.get(key).callbacks.push({ resolve, reject })
        console.log(`📎 [${timestamp}] Callback agregado a petición existente: ${key} (total: ${existingCallbacks + 1})`)
        return
      }

      // Añadir nueva petición a la cola
      this.queue.set(key, {
        requestFn,
        callbacks: [{ resolve, reject }],
        addedAt: timestamp
      })

      console.log(`➕ [${timestamp}] Nueva petición añadida a cola: ${key}`)
      console.log(`📊 [${timestamp}] Cola actual: ${Array.from(this.queue.keys()).join(', ')}`)
      this.processQueue()
    })
  }

  async processQueue() {
    const timestamp = new Date().toISOString()

    if (this.queue.size === 0) {
      console.log(`📋 [${timestamp}] Cola vacía, nada que procesar`)
      return
    }

    // Detectar si estamos stuck - isProcessing = true pero no hay timer activo
    if (this.isProcessing && !this.currentProcessingTimer) {
      const timeStuck = this.lastProcessedTime ? Date.now() - this.lastProcessedTime : Date.now()
      if (timeStuck > 5000) { // Si llevamos más de 5 segundos stuck
        console.error(`🚨 STUCK DETECTION: Procesamiento stuck por ${timeStuck}ms sin timer activo`)
        console.error(`🚨 Ejecutando recuperación automática...`)
        this.manualRecovery()
        return
      }
    }

    if (this.isProcessing) {
      console.log(`⏸️ [${timestamp}] Procesamiento ya en curso, esperando... (Cola: ${this.queue.size})`)
      console.log(`🔍 [${timestamp}] Estado actual:`, this.getStatus())
      return
    }

    const startTime = Date.now()
    this.isProcessing = true
    console.log(`🏃‍♂️ [${new Date().toISOString()}] Iniciando procesamiento de cola (${this.queue.size} peticiones)`)

    // Configurar timeout de seguridad
    this.currentProcessingTimer = setTimeout(() => {
      console.error(`🚨 TIMEOUT: Cola se quedó colgada después de ${this.processingTimeout}ms, reseteando...`)
      console.error('🚨 Estado antes del timeout:', this.getStatus())
      this.forceReset()
      // Intentar procesar de nuevo después del reset
      if (this.queue.size > 0) {
        console.log('🚨 Reintentando procesamiento después del timeout...')
        setTimeout(() => this.processQueue(), 500)
      }
    }, this.processingTimeout)

    // Esperar un poco por si llegan más peticiones relacionadas
    // Solo si acabamos de procesar algo hace poco
    if (this.lastProcessedTime && (Date.now() - this.lastProcessedTime) < this.gracePeriod && this.queue.size === 1) {
      console.log(`⏳ Esperando ${this.gracePeriod}ms por peticiones relacionadas...`)
      await new Promise(resolve => setTimeout(resolve, this.gracePeriod))
    }

    while (this.queue.size > 0) {
      const [key, { requestFn, callbacks, addedAt }] = this.queue.entries().next().value
      this.queue.delete(key)

      const processingTime = Date.now() - new Date(addedAt).getTime()
      console.log(`📡 [${new Date().toISOString()}] Ejecutando: ${key} (${callbacks.length} callbacks, esperó ${processingTime}ms)`)

      try {
        const requestStart = Date.now()

        // Crear promesa con timeout individual para cada petición
        const timeoutPromise = new Promise((_, reject) => {
          setTimeout(() => reject(new Error(`Request timeout after 8000ms: ${key}`)), 8000)
        })

        const result = await Promise.race([requestFn(), timeoutPromise])
        const requestDuration = Date.now() - requestStart

        // Resolver todas las callbacks con el mismo resultado
        callbacks.forEach(({ resolve }) => resolve(result))
        console.log(`✅ [${new Date().toISOString()}] Completada: ${key} (${requestDuration}ms, ${callbacks.length} callbacks resueltos)`)
      } catch (error) {
        console.error(`❌ [${new Date().toISOString()}] Error en: ${key}`, error)
        console.error(`   Tipo de error: ${error.constructor.name}`)
        console.error(`   Status: ${error.response?.status}`)
        console.error(`   Message: ${error.message}`)
        // Rechazar todas las callbacks con el mismo error
        callbacks.forEach(({ reject }) => reject(error))
      }

      // Delay entre peticiones para no saturar
      if (this.queue.size > 0) {
        console.log(`⏳ Esperando ${this.processingDelay}ms antes de la siguiente petición (${this.queue.size} restantes)`)
        await new Promise(resolve => setTimeout(resolve, this.processingDelay))
      }
    }

    const totalDuration = Date.now() - startTime
    this.lastProcessedTime = Date.now() // Actualizar timestamp de última procesación

    // Limpiar timeout
    if (this.currentProcessingTimer) {
      clearTimeout(this.currentProcessingTimer)
      this.currentProcessingTimer = null
    }

    this.isProcessing = false
    console.log(`🏁 [${new Date().toISOString()}] Cola procesada completamente (${totalDuration}ms total)`)
  }

  // Limpiar cola (útil para resetear estado)
  clear() {
    this.queue.clear()
    this.isProcessing = false
    console.log('🧹 Cola limpiada')
  }

  // Debug: Forzar reset si la cola se queda colgada
  forceReset() {
    console.log('🚨 FORCE RESET - Estado antes:', {
      isProcessing: this.isProcessing,
      queueSize: this.queue.size,
      queueKeys: Array.from(this.queue.keys())
    })
    this.clear()
    console.log('🚨 FORCE RESET - Completado')
  }

  // Debug: Mostrar estado actual
  getStatus() {
    return {
      isProcessing: this.isProcessing,
      queueSize: this.queue.size,
      queueKeys: Array.from(this.queue.keys()),
      lastProcessedTime: this.lastProcessedTime,
      hasTimeout: !!this.currentProcessingTimer
    }
  }

  // Debug: Manual recovery para casos de emergency
  manualRecovery() {
    console.log('🛠️ MANUAL RECOVERY - Ejecutando recuperación manual...')
    console.log('🛠️ Estado antes:', this.getStatus())

    // Limpiar timeout si existe
    if (this.currentProcessingTimer) {
      clearTimeout(this.currentProcessingTimer)
      this.currentProcessingTimer = null
      console.log('🛠️ Timeout limpiado')
    }

    // Resetear estado
    this.isProcessing = false
    console.log('🛠️ Estado de procesamiento reseteado')

    // Intentar procesar cola si hay elementos
    if (this.queue.size > 0) {
      console.log('🛠️ Reintentando procesamiento de cola...')
      setTimeout(() => this.processQueue(), 100)
    } else {
      console.log('🛠️ Cola vacía, no hay nada que procesar')
    }

    console.log('🛠️ MANUAL RECOVERY - Completado')
    return this.getStatus()
  }
}

// Instancia global
export const requestQueue = new RequestQueue()

// Debug: Exponer globalmente para debug en consola
if (typeof window !== 'undefined') {
  window.debugRequestQueue = requestQueue
}

// Helper functions para tipos comunes de peticiones
export const queueRequest = {
  // Para peticiones de objetivos
  objectives: (fn) => requestQueue.addRequest('fetch-objectives', fn),

  // Para peticiones de experiencia/niveles
  levels: (fn) => requestQueue.addRequest('fetch-levels', fn),

  // Para peticiones de metas específicas
  metas: (objetivoId, fn) => requestQueue.addRequest(`metas-${objetivoId}`, fn),

  // Para peticiones de actualización de meta específica
  updateMeta: (metaId, fn) => requestQueue.addRequest(`update-meta-${metaId}`, fn)
}