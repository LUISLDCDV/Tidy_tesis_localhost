import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useLevelsStore } from '@/stores/levels'
import { queueRequest } from '@/utils/requestQueue'

export const useObjectivesStore = defineStore('objectives', () => {
  // Estado reactivo
  const objectives = ref([])
  const currentObjective = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const lastUpdated = ref(null)

  // Configuraciones de objetivos
  const objectiveTypes = ref([
    { value: 'personal', label: 'Personal', icon: 'person', color: '#3498db' },
    { value: 'profesional', label: 'Profesional', icon: 'work', color: '#2ecc71' },
    { value: 'academico', label: 'Académico', icon: 'school', color: '#f39c12' },
    { value: 'salud', label: 'Salud y Fitness', icon: 'fitness_center', color: '#e74c3c' },
    { value: 'financiero', label: 'Financiero', icon: 'attach_money', color: '#9b59b6' },
    { value: 'social', label: 'Social', icon: 'people', color: '#1abc9c' },
    { value: 'habito', label: 'Hábito', icon: 'repeat', color: '#34495e' },
    { value: 'proyecto', label: 'Proyecto', icon: 'assignment', color: '#e67e22' }
  ])

  const priorityLevels = ref([
    { value: 'baja', label: 'Baja', color: '#95a5a6', weight: 1 },
    { value: 'media', label: 'Media', color: '#f39c12', weight: 2 },
    { value: 'alta', label: 'Alta', color: '#e74c3c', weight: 3 },
    { value: 'critica', label: 'Crítica', color: '#8e44ad', weight: 4 }
  ])

  // Sistema de límites de metas por nivel
  const getMaxObjectivesAllowed = (userLevel, isPremium = false) => {
    // Usuario premium tiene objetivos ilimitados
    if (isPremium) return Infinity

    // Sistema: 3 objetivos base hasta máximo 10
    const baseLimit = 3
    const maxLimit = 10

    // Progresión gradual: +1 objetivo cada 2 niveles aproximadamente
    const extraObjectives = Math.floor((userLevel - 1) / 2)
    const calculatedLimit = baseLimit + extraObjectives

    return Math.min(calculatedLimit, maxLimit)
  }

  // Getters computados
  const allObjectives = computed(() => objectives.value)
  
  const activeObjectives = computed(() => 
    objectives.value.filter(objective => objective.estado === 'activo' && !objective.deleted_at)
  )

  const completedObjectives = computed(() => 
    objectives.value.filter(objective => objective.estado === 'completado')
  )

  const pausedObjectives = computed(() => 
    objectives.value.filter(objective => objective.estado === 'pausado')
  )

  const objectivesByType = computed(() => (type) => 
    objectives.value.filter(objective => objective.tipo_objetivo === type)
  )

  const objectivesByPriority = computed(() => (priority) => 
    objectives.value.filter(objective => objective.prioridad === priority)
  )

  const upcomingDeadlines = computed(() => {
    const now = new Date()
    const nextWeek = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000)
    
    return activeObjectives.value
      .filter(objective => objective.fecha_limite && new Date(objective.fecha_limite) <= nextWeek)
      .sort((a, b) => new Date(a.fecha_limite) - new Date(b.fecha_limite))
  })

  const overallProgress = computed(() => {
    if (objectives.value.length === 0) return 0
    const totalProgress = objectives.value.reduce((sum, obj) => sum + (obj.progreso || 0), 0)
    return Math.round(totalProgress / objectives.value.length)
  })

  const isLoading = computed(() => loading.value)
  const hasError = computed(() => !!error.value)
  const errorMessage = computed(() => error.value)
  const objectiveCount = computed(() => activeObjectives.value.length)

  // Límites de objetivos computados
  const maxObjectivesAllowed = computed(() => {
    const levelsStore = useLevelsStore()
    return getMaxObjectivesAllowed(levelsStore.getCurrentLevel, levelsStore.isPremium)
  })

  const currentObjectivesCount = computed(() => {
    return activeObjectives.value.length
  })

  const canCreateMoreObjectives = computed(() => {
    const levelsStore = useLevelsStore()
    if (levelsStore.isPremium) return true
    return currentObjectivesCount.value < maxObjectivesAllowed.value
  })

  const objectivesLimitInfo = computed(() => {
    const levelsStore = useLevelsStore()
    return {
      current: currentObjectivesCount.value,
      max: maxObjectivesAllowed.value,
      isPremium: levelsStore.isPremium,
      canCreate: canCreateMoreObjectives.value,
      level: levelsStore.getCurrentLevel
    }
  })

  const getObjectiveTypes = computed(() => objectiveTypes.value)
  const getPriorityLevels = computed(() => priorityLevels.value)

  const getObjectiveTypeInfo = computed(() => (type) => 
    objectiveTypes.value.find(ot => ot.value === type) || objectiveTypes.value[0]
  )

  const getPriorityInfo = computed(() => (priority) => 
    priorityLevels.value.find(pl => pl.value === priority) || priorityLevels.value[0]
  )

  // Acciones
  async function fetchObjectives() {
    loading.value = true
    error.value = null

    try {
      console.log('Fetching objectives...')
      // Usar endpoint optimizado que ya incluye metas en una sola petición
      const response = await api.get('/usuarios/objetivos')
      console.log('Response de fetchObjectives:', response.data)

      // El backend ya retorna objetivos con metas incluidas
      const objetivosData = response.data.objetivos || []
      console.log('Datos crudos de objetivos:', objetivosData)

      // Mapear directamente sin necesidad de peticiones adicionales
      const processedObjectives = objetivosData.map(objetivo => ({
        id: objetivo.id, // ID del objetivo
        elemento_id: objetivo.elemento_id,
        nombre: objetivo.nombre || 'Sin título',
        descripcion: objetivo.nombre, // El backend usa 'nombre' como descripción
        informacion: objetivo.informacion || '',
        tipo: objetivo.tipo || '1',
        estado: objetivo.status || 'activo',
        metas: objetivo.metas || [], // Metas ya vienen incluidas desde el backend
        fechaCreacion: objetivo.fechaCreacion,
        fechaVencimiento: objetivo.fechaVencimiento,
        configuracion: {}
      }))

      objectives.value = processedObjectives
      console.log('Objetivos en store después de mapear:', objectives.value)
      lastUpdated.value = new Date()
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar objetivos'
      console.error('Error fetching objectives:', err)
    } finally {
      loading.value = false
    }
  }

  async function getObjectiveById(id) {
    loading.value = true
    error.value = null

    try {
      const response = await api.get(`/usuarios/elemento/${id}`)
      currentObjective.value = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar objetivo'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createObjective(objectiveData) {
    loading.value = true
    error.value = null

    try {
      // Validar límite de objetivos antes de crear
      if (!canCreateMoreObjectives.value) {
        const limitInfo = objectivesLimitInfo.value
        const message = limitInfo.isPremium
          ? 'Error inesperado: Usuario premium no puede crear más objetivos'
          : `Has alcanzado el límite de ${limitInfo.max} objetivos para tu nivel ${limitInfo.level}. Completa algunos objetivos o mejora a Premium para crear más.`

        error.value = message
        throw new Error(message)
      }
      console.log('Datos recibidos en createObjective:', objectiveData)
      
      // Crear fecha en formato MySQL
      const mysqlDate = new Date().toISOString().slice(0, 19).replace('T', ' ')
      console.log('Fecha MySQL generada:', mysqlDate)
      
      const elementData = {
        nombre: objectiveData.nombre || objectiveData.titulo || objectiveData.descripcion || 'Objetivo sin título',
        tipo: 'objetivo',
        descripcion: objectiveData.informacion || objectiveData.descripcion || objectiveData.nombre || '',
        estado: 'activo',
        contenido: objectiveData.descripcion_detallada || objectiveData.informacion || '',
        fechaCreacion: mysqlDate, // Formato MySQL: YYYY-MM-DD HH:mm:ss
        fechaVencimiento: objectiveData.fechaVencimiento && objectiveData.fechaVencimiento.trim()
          ? objectiveData.fechaVencimiento
          : new Date().toISOString().slice(0, 10), // Usar fecha actual por defecto
        tipo_objetivo: objectiveData.tipo || objectiveData.tipo_objetivo || '1',
        status: 'activo',
        configuracion: {
          tipo_objetivo: objectiveData.tipo || objectiveData.tipo_objetivo || '1',
          prioridad: objectiveData.prioridad || 'media',
          fecha_inicio: new Date().toISOString(),
          fecha_limite: objectiveData.fechaVencimiento ? 
            (objectiveData.fechaVencimiento + ' 00:00:00') : 
            (objectiveData.fecha_limite || null),
          progreso: 0,
          meta_numerica: objectiveData.meta_numerica || null,
          unidad_medida: objectiveData.unidad_medida || null,
          hitos: objectiveData.hitos || [],
          metas: objectiveData.metas || [],
          recordatorios: objectiveData.recordatorios || false,
          frecuencia_recordatorio: objectiveData.frecuencia_recordatorio || null,
          recompensa: objectiveData.recompensa || null
        }
      }
      
      console.log('Datos a enviar al backend:', elementData)
      console.log('📅 fechaVencimiento que se envía:', elementData.fechaVencimiento)
      
      // Verificar que los campos requeridos no estén vacíos
      if (!elementData.nombre || elementData.nombre.trim() === '') {
        console.error('ERROR: nombre está vacío')
        throw new Error('El nombre del objetivo es requerido')
      }
      if (!elementData.fechaCreacion) {
        console.error('ERROR: fechaCreacion está vacía')
      }
      if (!elementData.tipo_objetivo) {
        console.error('ERROR: tipo_objetivo está vacío')
      }
      if (!elementData.status) {
        console.error('ERROR: status está vacío')
      }

      const response = await api.post('/elementos/saveUpdate', elementData)
      const newObjective = response.data
      console.log('Objetivo guardado, respuesta del backend:', newObjective)

      // Refrescar la lista completa en lugar de solo agregar
      await fetchObjectives()
      console.log('Lista actualizada después de crear objetivo')
      
      return newObjective
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear objetivo'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateObjective(objectiveData) {
    loading.value = true
    error.value = null

    try {
      console.log('Datos recibidos en updateObjective:', objectiveData)
      
      const elementData = {
        elemento_id: objectiveData.id,
        nombre: objectiveData.nombre || objectiveData.titulo || objectiveData.descripcion || 'Objetivo sin título',
        tipo: 'objetivo',
        descripcion: objectiveData.informacion || objectiveData.descripcion || objectiveData.nombre || '',
        estado: objectiveData.estado || 'activo',
        contenido: objectiveData.descripcion_detallada || objectiveData.informacion || '',
        fechaCreacion: objectiveData.fechaCreacion ||
          new Date().toISOString().slice(0, 19).replace('T', ' '),
        fechaVencimiento: objectiveData.fechaVencimiento && objectiveData.fechaVencimiento.trim()
          ? objectiveData.fechaVencimiento
          : new Date().toISOString().slice(0, 10), // Usar fecha actual por defecto
        tipo_objetivo: objectiveData.tipo || objectiveData.tipo_objetivo || objectiveData.configuracion?.tipo_objetivo || '1',
        status: objectiveData.status || objectiveData.estado || 'activo',
        configuracion: {
          // Preservar configuración existente primero
          ...objectiveData.configuracion,
          // Luego aplicar nuevas propiedades específicas
          tipo_objetivo: objectiveData.tipo || objectiveData.tipo_objetivo || objectiveData.configuracion?.tipo_objetivo || '1',
          prioridad: objectiveData.prioridad || objectiveData.configuracion?.prioridad || 'media',
          fecha_limite: objectiveData.fechaVencimiento ? 
            (objectiveData.fechaVencimiento + ' 00:00:00') : 
            (objectiveData.fecha_limite || objectiveData.configuracion?.fecha_limite || null),
          progreso: objectiveData.progreso !== undefined ? objectiveData.progreso : (objectiveData.configuracion?.progreso || 0),
          meta_numerica: objectiveData.meta_numerica !== undefined ? objectiveData.meta_numerica : objectiveData.configuracion?.meta_numerica,
          unidad_medida: objectiveData.unidad_medida !== undefined ? objectiveData.unidad_medida : objectiveData.configuracion?.unidad_medida,
          metas: objectiveData.metas !== undefined ? objectiveData.metas : (objectiveData.configuracion?.metas || [])
        }
      }

      console.log('Datos a enviar al backend para update:', elementData)
      console.log('📅 fechaVencimiento que se envía:', elementData.fechaVencimiento)
      
      const response = await api.post('/elementos/saveUpdate', elementData)
      const updatedObjective = response.data
      console.log('Objetivo actualizado, respuesta del backend:', updatedObjective)

      // Refrescar la lista completa para asegurar consistencia
      await fetchObjectives()
      console.log('Lista actualizada después de editar objetivo')

      if (currentObjective.value?.id === objectiveData.id) {
        currentObjective.value = updatedObjective
      }

      return updatedObjective
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar objetivo'
      console.error('Error al actualizar objetivo:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteObjective(objectiveId) {
    console.log('🗑️ Eliminación optimista del objetivo ID:', objectiveId)

    // 1. ELIMINACIÓN OPTIMISTA - Quitar inmediatamente de la vista
    const originalObjectives = [...objectives.value]
    objectives.value = objectives.value.filter(obj => obj.elemento_id !== objectiveId)

    // 2. PERSISTIR EN LOCALSTORAGE inmediatamente
    persistObjectivesToStorage()

    console.log('✅ Objetivo eliminado de la vista y localStorage')

    // 3. API CALL EN BACKGROUND (no bloqueante)
    try {
      // API call asíncrono sin esperar
      api.post(`/elementos/eliminarElemento/${objectiveId}`)
        .then(response => {
          console.log('✅ Confirmación del servidor:', response.data)
          // Todo bien, no hacer nada más
        })
        .catch(async (err) => {
          console.error('❌ Error en API, haciendo rollback:', err)

          // ROLLBACK - restaurar objetivo eliminado
          objectives.value = originalObjectives
          persistObjectivesToStorage()

          // Mostrar error al usuario
          error.value = err.response?.data?.message || 'Error al eliminar objetivo'

          // Disparar evento para mostrar notificación de error
          window.dispatchEvent(new CustomEvent('objective-delete-failed', {
            detail: { message: error.value }
          }))
        })

      return true
    } catch (err) {
      // Este catch es solo para errores síncronos inmediatos
      console.error('❌ Error inmediato al llamar API:', err)

      // Rollback inmediato
      objectives.value = originalObjectives
      persistObjectivesToStorage()

      throw err
    }
  }

  function updateProgress(objectiveId, progress, milestone = null) {
    const objective = objectives.value.find(o => o.id === objectiveId)
    if (objective) {
      objective.configuracion = objective.configuracion || {}
      objective.configuracion.progreso = Math.min(Math.max(progress, 0), 100)
      
      if (milestone) {
        objective.configuracion.hitos = objective.configuracion.hitos || []
        objective.configuracion.hitos.push({
          descripcion: milestone,
          fecha: new Date().toISOString(),
          progreso: progress
        })
      }

      // Marcar como completado si llega al 100%
      if (progress >= 100) {
        objective.estado = 'completado'
        objective.fecha_completado = new Date().toISOString()
      }

      updateObjective(objective)
    }
  }

  function pauseObjective(objectiveId) {
    const objective = objectives.value.find(o => o.id === objectiveId)
    if (objective) {
      objective.estado = 'pausado'
      updateObjective(objective)
    }
  }

  function resumeObjective(objectiveId) {
    const objective = objectives.value.find(o => o.id === objectiveId)
    if (objective) {
      objective.estado = 'activo'
      updateObjective(objective)
    }
  }

  async function completeObjective(objectiveId) {
    const objective = objectives.value.find(o => o.id === objectiveId)
    if (objective) {
      objective.estado = 'completado'
      objective.fecha_completado = new Date().toISOString()
      objective.configuracion = objective.configuracion || {}
      objective.configuracion.progreso = 100

      // Actualizar el objetivo en el backend
      await updateObjective(objective)

      // Refrescar datos de experiencia y nivel después de completar el objetivo
      try {
        const levelsStore = useLevelsStore()
        const currentLevelBefore = levelsStore.getCurrentLevel

        console.log('🎯 Nivel antes de completar objetivo:', currentLevelBefore)

        await levelsStore.fetchUserLevel()

        const currentLevelAfter = levelsStore.getCurrentLevel
        console.log('🎯 Nivel después de completar objetivo:', currentLevelAfter)

        // Si subió de nivel, disparar notificación manualmente
        if (currentLevelAfter > currentLevelBefore) {
          console.log('🎉 ¡SUBIDA DE NIVEL DETECTADA!', currentLevelBefore, '->', currentLevelAfter)

          // Establecer la notificación de nivel directamente ya que fetchUserLevel ya actualizó el nivel
          levelsStore.levelUpNotification = {
            newLevel: currentLevelAfter,
            timestamp: new Date(),
            message: `¡Felicitaciones! Has alcanzado el nivel ${currentLevelAfter}`
          }

          // Auto-limpiar notificación después de 5 segundos
          setTimeout(() => {
            levelsStore.levelUpNotification = null
          }, 5000)
        }

        console.log('✅ Objetivo completado - Experiencia actualizada')
      } catch (error) {
        console.error('❌ Error al actualizar experiencia después de completar objetivo:', error)
      }
    }
  }

  function addMilestone(objectiveId, milestone) {
    const objective = objectives.value.find(o => o.id === objectiveId)
    if (objective) {
      objective.configuracion = objective.configuracion || {}
      objective.configuracion.hitos = objective.configuracion.hitos || []
      objective.configuracion.hitos.push({
        ...milestone,
        fecha: new Date().toISOString(),
        id: Date.now()
      })
      updateObjective(objective)
    }
  }

  function removeMilestone(objectiveId, milestoneId) {
    const objective = objectives.value.find(o => o.id === objectiveId)
    if (objective && objective.configuracion?.hitos) {
      objective.configuracion.hitos = objective.configuracion.hitos
        .filter(h => h.id !== milestoneId)
      updateObjective(objective)
    }
  }

  function addObjective(objective) {
    objectives.value.unshift(objective)
  }

  function removeObjective(objectiveId) {
    const index = objectives.value.findIndex(o => o.id === objectiveId)
    if (index !== -1) {
      objectives.value.splice(index, 1)
    }
  }

  function setCurrentObjective(objective) {
    currentObjective.value = objective
  }

  function clearCurrentObjective() {
    currentObjective.value = null
  }

  function searchObjectives(query) {
    const searchTerm = query.toLowerCase()
    return objectives.value.filter(objective => 
      objective.descripcion?.toLowerCase().includes(searchTerm) ||
      objective.contenido?.toLowerCase().includes(searchTerm) ||
      objective.configuracion?.tipo_objetivo?.toLowerCase().includes(searchTerm)
    )
  }

  function getObjectivesByDateRange(startDate, endDate) {
    const start = new Date(startDate)
    const end = new Date(endDate)
    
    return objectives.value.filter(objective => {
      if (!objective.configuracion?.fecha_limite) return false
      const deadline = new Date(objective.configuracion.fecha_limite)
      return deadline >= start && deadline <= end
    })
  }

  function sortObjectives(sortBy = 'created_at', sortOrder = 'desc') {
    return [...objectives.value].sort((a, b) => {
      let valueA = a[sortBy]
      let valueB = b[sortBy]

      // Manejar configuración anidada
      if (sortBy.includes('.')) {
        const keys = sortBy.split('.')
        valueA = keys.reduce((obj, key) => obj?.[key], a)
        valueB = keys.reduce((obj, key) => obj?.[key], b)
      }

      // Manejar fechas
      if (sortBy.includes('fecha_') || sortBy.includes('_at')) {
        valueA = new Date(valueA).getTime()
        valueB = new Date(valueB).getTime()
      }

      // Manejar prioridad
      if (sortBy === 'prioridad' || sortBy === 'configuracion.prioridad') {
        const priorityA = getPriorityInfo.value(valueA)
        const priorityB = getPriorityInfo.value(valueB)
        valueA = priorityA.weight
        valueB = priorityB.weight
      }

      if (sortOrder === 'asc') {
        return valueA > valueB ? 1 : -1
      } else {
        return valueA < valueB ? 1 : -1
      }
    })
  }

  function clearError() {
    error.value = null
  }

  function clearObjectives() {
    objectives.value = []
    currentObjective.value = null
    error.value = null
    lastUpdated.value = null
  }

  async function refreshObjectives() {
    await fetchObjectives()
  }

  // Funciones para manejo de metas/pasos
  async function createGoal(goalData) {
    loading.value = true
    error.value = null

    try {
      console.log('=== CREATEGOAL DEBUG ===')
      console.log('goalData completo recibido:', goalData)
      console.log('goalData.elemento_id:', goalData.elemento_id)

      const elementoId = goalData.elemento_id

      if (!elementoId) {
        console.error('ERROR: No hay elemento_id válido')
        throw new Error('ID del elemento es requerido para crear una meta')
      }

      // Obtener el objetivo_id del elemento_id
      const objetivoResponse = await api.get(`/elementos/${elementoId}/objetivo-id`)
      const objetivo_id = objetivoResponse.data.objetivo_id

      // SOLUCIÓN MEJORADA: Usar stepInsertType para posicionamiento natural
      // Backend ahora ordena por: completadas -> en_progreso -> pendientes -> created_at

      let statusAsignado = goalData.status || 'pendiente';

      // Usar el stepInsertType que viene del componente
      if (goalData.stepInsertType === 'next') {
        console.log('🎯 PASO SIGUIENTE detectado');
        // Marcar como "en_progreso" para que aparezca después de completadas pero antes de pendientes
        statusAsignado = 'en_progreso';
      } else if (goalData.stepInsertType === 'final') {
        console.log('🏁 PASO FINAL detectado');
        // Mantener como "pendiente" para que aparezca al final
        statusAsignado = 'pendiente';
      } else {
        console.log('🤔 TIPO NO ESPECIFICADO - usando pendiente por defecto');
        statusAsignado = 'pendiente';
      }

      // Crear como elemento tipo 'meta' usando la tabla metas real
      const stepData = {
        nombre: goalData.nombre,
        tipo: 'meta',
        objetivo_id: objetivo_id, // FK para la tabla metas
        fechaCreacion: new Date().toISOString().slice(0, 10), // Fecha actual
        fechaVencimiento: goalData.fechaVencimiento || null,
        status: statusAsignado, // Status calculado según tipo de inserción
        informacion: goalData.informacion || ''
      }

      console.log('📊 Posición:', goalData.position, '| Tipo:', goalData.stepInsertType, '| Status asignado:', statusAsignado)

      console.log('Datos de meta a enviar al backend:', stepData)

      // Crear la meta usando la tabla metas real
      const response = await api.post('/elementos/saveUpdate', stepData)

      if (response && response.data) {
        console.log('Meta creada exitosamente:', response.data)

        // Refrescar los objetivos para actualizar el progreso
        await fetchObjectives()

        return { data: response.data, success: true }
      } else {
        throw new Error('No se recibió respuesta del servidor')
      }
    } catch (err) {
      console.error('Error completo al crear meta:', {
        message: err.message,
        response: err.response?.data,
        status: err.response?.status,
        config: err.config
      })

      const errorMessage = err.response?.data?.message ||
                          err.response?.data?.error ||
                          err.message ||
                          'Error desconocido al crear meta'

      error.value = errorMessage
      throw new Error(errorMessage)
    } finally {
      loading.value = false
    }
  }

  async function updateGoal(metaData) {
    loading.value = true
    error.value = null

    try {
      console.log('=== UPDATEGOAL DEBUG ===')
      console.log('metaData para actualizar:', metaData)
      console.log('IDs disponibles:')
      console.log('  - metaData.id (meta_id):', metaData.id)
      console.log('  - metaData.elemento_id:', metaData.elemento_id)
      console.log('  - metaData.objetivo_id:', metaData.objetivo_id)
      console.log('TIPO DEBUG:')
      console.log('  - metaData.tipo (tipo específico de meta):', metaData.tipo)
      console.log('  - tipo_meta que se enviará:', metaData.tipo || 'preparacion')
      console.log('  - tipo elemento (siempre "meta"):', 'meta')

      if (!metaData.id && !metaData.elemento_id) {
        throw new Error('ID de la meta es requerido para actualizar')
      }

      // Verificar que tenemos los IDs necesarios
      if (!metaData.elemento_id) {
        throw new Error('elemento_id es requerido para actualizar una meta')
      }

      const updateData = {
        elemento_id: metaData.elemento_id, // ID del elemento de la meta (no el meta_id)
        nombre: metaData.nombre,
        tipo: 'meta', // Tipo de elemento (siempre "meta" para identificar que es una meta)
        tipo_meta: metaData.tipo || 'preparacion', // Tipo específico de la meta (preparacion, accion, seguimiento, etc.)
        objetivo_id: metaData.objetivo_id, // ID del objetivo al que pertenece
        fechaCreacion: metaData.fechaCreacion || new Date().toISOString().slice(0, 10),
        fechaVencimiento: metaData.fechaVencimiento || null,
        status: metaData.status || 'pendiente',
        informacion: metaData.informacion || ''
      }

      console.log('Datos de actualización a enviar:', updateData)
      console.log('URL del endpoint:', '/elementos/saveUpdate')
      console.log('Método HTTP:', 'POST')

      const response = await api.post('/elementos/saveUpdate', updateData)
      console.log('Respuesta cruda del servidor (update):', response)

      if (response && response.data) {
        console.log('🎉 Meta actualizada exitosamente:', response.data)
        console.log('📋 Estructura de la respuesta:')
        console.log('  - ID recibido:', response.data.id)
        console.log('  - Tipo recibido:', response.data.tipo || response.data.type)
        console.log('  - Status recibido:', response.data.status || response.data.estado)
        console.log('  - Elemento_id recibido:', response.data.elemento_id)
        console.log('  - Objetivo_id recibido:', response.data.objetivo_id)

        // Si la meta fue completada, actualizar nivel y mostrar notificación si subió de nivel
        // El backend ya otorgó la experiencia, solo necesitamos refrescar el nivel
        if (metaData.status === 'completada') {
          console.log('📊 Meta completada - refrescando nivel del usuario...')
          try {
            const levelsStore = useLevelsStore()
            const currentLevelBefore = levelsStore.getCurrentLevel

            console.log('🎯 Nivel antes de completar meta:', currentLevelBefore)

            // Refrescar datos de nivel y experiencia
            await levelsStore.fetchUserLevel()

            const currentLevelAfter = levelsStore.getCurrentLevel
            console.log('🎯 Nivel después de completar meta:', currentLevelAfter)

            // Si subió de nivel, disparar notificación manualmente
            if (currentLevelAfter > currentLevelBefore) {
              console.log('🎉 ¡SUBIDA DE NIVEL DETECTADA!', currentLevelBefore, '->', currentLevelAfter)

              // Establecer la notificación de nivel directamente ya que fetchUserLevel ya actualizó el nivel
              levelsStore.levelUpNotification = {
                newLevel: currentLevelAfter,
                timestamp: new Date(),
                message: `¡Felicitaciones! Has alcanzado el nivel ${currentLevelAfter}`
              }

              // Auto-limpiar notificación después de 5 segundos
              setTimeout(() => {
                levelsStore.levelUpNotification = null
              }, 5000)
            }

            console.log('✅ Nivel actualizado después de completar meta')
          } catch (levelError) {
            console.error('❌ Error al actualizar nivel:', levelError)
          }
        }

        // NO refrescar objetivos aquí - el componente ya lo hace después de updateGoal

        return { data: response.data, success: true }
      } else {
        throw new Error('No se recibió respuesta del servidor')
      }
    } catch (err) {
      console.error('Error completo al actualizar meta:', err)
      const errorMessage = err.response?.data?.message ||
                          err.response?.data?.error ||
                          err.message ||
                          'Error desconocido al actualizar meta'
      error.value = errorMessage
      throw new Error(errorMessage)
    } finally {
      loading.value = false
    }
  }

  async function deleteGoal(metaId, elementoId) {
    loading.value = true
    error.value = null

    try {
      console.log('=== DELETEGOAL DEBUG ===')
      console.log('Eliminando meta ID:', metaId)
      console.log('elemento_id recibido:', elementoId)
      console.log('Tipo de metaId:', typeof metaId)

      // Usar el elemento_id pasado como parámetro
      if (!elementoId) {
        throw new Error('elemento_id es requerido para eliminar meta');
      }

      console.log('URL del endpoint:', `/elementos/eliminarElemento/${elementoId}`)
      console.log('Método HTTP:', 'POST')

      // Eliminar usando el elemento_id, no el meta_id
      const response = await api.post(`/elementos/eliminarElemento/${elementoId}`)
      console.log('Respuesta cruda del servidor (delete):', response)

      if (response && response.data) {
        console.log('Meta eliminada exitosamente:', response.data)

        // Refrescar los objetivos desde el servidor para actualizar el progreso
        await fetchObjectives()

        return { success: true }
      } else {
        throw new Error('No se recibió respuesta del servidor')
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Error al eliminar meta'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Función para persistir objetivos en localStorage
  function persistObjectivesToStorage() {
    try {
      localStorage.setItem('objectives', JSON.stringify(objectives.value))
      console.log('✅ Objetivos guardados en localStorage')
    } catch (err) {
      console.error('Error al guardar objetivos en localStorage:', err)
    }
  }

  // Función para cargar objetivos desde localStorage
  function loadObjectivesFromStorage() {
    try {
      const storedObjectives = localStorage.getItem('objectives')
      if (storedObjectives) {
        objectives.value = JSON.parse(storedObjectives)
        console.log('✅ Objetivos cargados desde localStorage')
      }
    } catch (err) {
      console.error('Error al cargar objetivos desde localStorage:', err)
    }
  }

  return {
    // Estado
    objectives,
    currentObjective,
    loading,
    error,
    lastUpdated,
    objectiveTypes,
    priorityLevels,

    // Getters
    allObjectives,
    activeObjectives,
    completedObjectives,
    pausedObjectives,
    objectivesByType,
    objectivesByPriority,
    upcomingDeadlines,
    overallProgress,
    isLoading,
    hasError,
    errorMessage,
    objectiveCount,
    getObjectiveTypes,
    getPriorityLevels,
    getObjectiveTypeInfo,
    getPriorityInfo,

    // Límites de objetivos
    maxObjectivesAllowed,
    currentObjectivesCount,
    canCreateMoreObjectives,
    objectivesLimitInfo,
    getMaxObjectivesAllowed,

    // Acciones
    fetchObjectives,
    getObjectiveById,
    createObjective,
    updateObjective,
    deleteObjective,
    updateProgress,
    pauseObjective,
    resumeObjective,
    completeObjective,
    addMilestone,
    removeMilestone,
    addObjective,
    removeObjective,
    setCurrentObjective,
    clearCurrentObjective,
    searchObjectives,
    getObjectivesByDateRange,
    sortObjectives,
    clearError,
    clearObjectives,
    refreshObjectives,
    createGoal,
    updateGoal,
    deleteGoal
  }
})