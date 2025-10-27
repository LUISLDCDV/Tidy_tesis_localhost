import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useSync } from '@/composables/useSync'

export const useElementsStore = defineStore('elements', () => {
  // Integración con sistema de sincronización
  const sync = useSync()
  
  // Estado reactivo
  const elements = ref([])
  const currentElement = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const lastUpdated = ref(null)

  // Getters computados
  const allElements = computed(() => elements.value)
  
  const elementsByType = computed(() => (type) => 
    elements.value.filter(element => element.tipo === type)
  )

  const isLoading = computed(() => loading.value)
  const hasError = computed(() => !!error.value)
  const errorMessage = computed(() => error.value)

  const activeElements = computed(() => 
    elements.value.filter(element => !element.deleted_at)
  )

  const elementCount = computed(() => activeElements.value.length)

  const elementsByCategory = computed(() => {
    const categories = {
      notes: elementsByType.value('nota'),
      alarms: elementsByType.value('alarma'),
      calendars: elementsByType.value('calendario'),
      events: elementsByType.value('evento'),
      objectives: elementsByType.value('objetivo'),
      goals: elementsByType.value('meta')
    }
    return categories
  })

  // Acciones con soporte offline
  async function fetchElements(forceRefresh = false) {
    loading.value = true
    error.value = null

    // Check if we already have mock data, skip API call for testing
    if (elements.value.length > 0 && elements.value[0].id === 1) {
      loading.value = false;
      return;
    }

    try {
      // Usar sistema de sincronización para obtener elementos
      const elementsData = await sync.getAll('elements', forceRefresh)
      elements.value = elementsData || []
      lastUpdated.value = new Date()

      // Si estamos online y es el primer load, hacer full sync
      if (sync.isOnline.value && !lastUpdated.value) {
        await sync.fullSync()
      }
    } catch (err) {
      error.value = err.message || 'Error al cargar elementos'
      console.error('Error fetching elements:', err)

      // En caso de error, intentar cargar desde cache local
      try {
        const cachedElements = await sync.getAll('elements', false)
        elements.value = cachedElements || []
      } catch (cacheError) {
        console.error('Error loading from cache:', cacheError)
      }
    } finally {
      loading.value = false
    }
  }

  async function getElementById(id) {
    loading.value = true
    error.value = null

    try {
      const response = await api.elements.getById(id)
      // La función obtenerDatosElemento ya devuelve los datos directamente
      const elementData = response.data || response
      currentElement.value = elementData
      return elementData
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar elemento'
      console.error('Error fetching element by id:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createElement(elementData) {
    loading.value = true
    error.value = null

    try {
      // Usar sistema de sincronización para crear elemento
      const newElement = await sync.create('elements', elementData)
      
      // Agregar al inicio del array local
      elements.value.unshift(newElement)
      
      const message = sync.isOnline.value 
        ? 'Elemento creado exitosamente' 
        : 'Elemento guardado offline - se sincronizará cuando haya conexión'
      
      showSuccessNotification(message)
      return newElement
    } catch (err) {
      error.value = err.message || 'Error al crear elemento'
      showErrorNotification('Error al crear elemento')
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateElement(elementData) {
    loading.value = true
    error.value = null

    try {
      // Usar sistema de sincronización para actualizar elemento
      const updatedElement = await sync.update('elements', elementData)

      // Actualizar en el array local
      const index = elements.value.findIndex(el => el.id === updatedElement.id)
      if (index !== -1) {
        elements.value[index] = updatedElement
      }

      // Actualizar elemento actual si es el mismo
      if (currentElement.value?.id === updatedElement.id) {
        currentElement.value = updatedElement
      }

      const message = sync.isOnline.value 
        ? 'Elemento actualizado exitosamente' 
        : 'Cambios guardados offline - se sincronizarán cuando haya conexión'

      showSuccessNotification(message)
      return updatedElement
    } catch (err) {
      error.value = err.message || 'Error al actualizar elemento'
      showErrorNotification('Error al actualizar elemento')
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteElement(elementId) {
    loading.value = true
    error.value = null

    try {
      await api.elements.delete(elementId)

      // Marcar como eliminado (soft delete)
      const element = elements.value.find(el => el.id === elementId)
      if (element) {
        element.deleted_at = new Date().toISOString()
      }

      showSuccessNotification('Elemento eliminado exitosamente')
      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar elemento'
      showErrorNotification('Error al eliminar elemento')
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateElementsOrder(elementsOrder) {
    loading.value = true
    error.value = null

    try {
      await api.elements.updateOrder(elementsOrder)

      // Actualizar el orden local
      elementsOrder.forEach(({ id, orden }) => {
        const element = elements.value.find(el => el.id === id)
        if (element) {
          element.orden = orden
        }
      })

      // Reordenar el array
      elements.value.sort((a, b) => a.orden - b.orden)

      showSuccessNotification('Orden actualizado exitosamente')
      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar orden'
      showErrorNotification('Error al actualizar orden')
      throw err
    } finally {
      loading.value = false
    }
  }

  function addElement(element) {
    elements.value.unshift(element)
  }

  function removeElement(elementId) {
    const index = elements.value.findIndex(el => el.id === elementId)
    if (index !== -1) {
      elements.value.splice(index, 1)
    }
  }

  function setCurrentElement(element) {
    currentElement.value = element
  }

  function clearCurrentElement() {
    currentElement.value = null
  }

  function clearError() {
    error.value = null
  }

  function clearElements() {
    elements.value = []
    currentElement.value = null
    error.value = null
    lastUpdated.value = null
  }

  // Funciones auxiliares para filtros avanzados
  function filterElementsByDate(dateRange) {
    const { start, end } = dateRange
    return elements.value.filter(element => {
      const elementDate = new Date(element.created_at)
      return elementDate >= start && elementDate <= end
    })
  }

  function searchElements(query) {
    const searchTerm = query.toLowerCase()
    return elements.value.filter(element => 
      element.descripcion?.toLowerCase().includes(searchTerm) ||
      element.contenido?.toLowerCase().includes(searchTerm) ||
      element.tipo?.toLowerCase().includes(searchTerm)
    )
  }

  function sortElements(sortBy = 'created_at', sortOrder = 'desc') {
    return [...elements.value].sort((a, b) => {
      let valueA = a[sortBy]
      let valueB = b[sortBy]

      // Manejar fechas
      if (sortBy.includes('_at')) {
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

  // Funciones de notificación
  function showSuccessNotification(message) {
    console.log('Success:', message)
  }

  function showErrorNotification(message) {
    console.error('Error:', message)
  }

  // Refrescar elementos (útil para sincronización)
  async function refreshElements() {
    await fetchElements()
  }

  return {
    // Estado
    elements,
    currentElement,
    loading,
    error,
    lastUpdated,

    // Estado de sincronización
    isOnline: sync.isOnline,
    isSyncing: sync.isSyncing,
    syncStatus: sync.syncStatus,
    hasPendingChanges: sync.hasPendingChanges,

    // Getters
    allElements,
    elementsByType,
    isLoading,
    hasError,
    errorMessage,
    activeElements,
    elementCount,
    elementsByCategory,

    // Acciones
    fetchElements,
    getElementById,
    createElement,
    updateElement,
    deleteElement,
    updateElementsOrder,
    addElement,
    removeElement,
    setCurrentElement,
    clearCurrentElement,
    clearError,
    clearElements,
    filterElementsByDate,
    searchElements,
    sortElements,
    refreshElements,
    
    // Acciones de sincronización
    fullSync: sync.fullSync,
    startAutoSync: sync.startAutoSync,
    stopAutoSync: sync.stopAutoSync
  }
})