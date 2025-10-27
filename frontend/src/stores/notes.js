import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useElementsStore } from '@/stores/elements'

export const useNotesStore = defineStore('notes', () => {
  // Estado reactivo
  const notes = ref([])
  const currentNote = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const lastUpdated = ref(null)

  // Tipos de notas disponibles - mapeados por ID según backend
  const noteTypes = ref([
    { id: 1, value: 'normal', label: 'Nota Normal', icon: 'note', description: 'Nota de texto rico con WYSIWYG', is_premium: false },
    { id: 2, value: 'comidas', label: 'Nota Comida Semana', icon: 'restaurant_menu', description: 'Planificador de menús semanales', is_premium: false },
    { id: 3, value: 'claves', label: 'Nota Claves', icon: 'vpn_key', description: 'Administrador de contraseñas', is_premium: false },
    { id: 4, value: 'codigo', label: 'Nota Código', icon: 'code', description: 'Editor de código con sintaxis', is_premium: false },
    { id: 5, value: 'compras', label: 'Nota Compras Supermercado', icon: 'shopping_cart', description: 'Lista de compras inteligente', is_premium: false },
    { id: 6, value: 'gastos', label: 'Nota Gastos Mensuales', icon: 'account_balance_wallet', description: 'Control de gastos mensuales', is_premium: false },
    { id: 7, value: 'puntos', label: 'Nota Puntos Juego', icon: 'stars', description: 'Gestión de puntos y recompensas', is_premium: false },
    { id: 8, value: 'presupuesto', label: 'Nota Presupuesto', icon: 'account_balance_wallet', description: 'Gestión financiera personal', is_premium: false },
    { id: 9, value: 'tiempo', label: 'Nota Timeboxing', icon: 'schedule', description: 'Time boxing y planificación', is_premium: false },
    { id: 10, value: 'recetas', label: 'Nota Recetas', icon: 'local_dining', description: 'Gestor de recetas culinarias', is_premium: false },
    { id: 11, value: 'construccion', label: 'Nota Medidas Construct', icon: 'architecture', description: 'Calculadora de construcción', is_premium: false },
    { id: 12, value: 'recomendaciones', label: 'Nota Recomendaciones', icon: 'thumb_up', description: 'Lista de recomendaciones', is_premium: false },
    { id: 13, value: 'pedidos', label: 'Nota Pedido Grupal', icon: 'group', description: 'Organización de pedidos grupales', is_premium: false },
    { id: 14, value: 'viaje', label: 'Nota Viaje', icon: 'flight', description: 'Organizador de viajes', is_premium: true },
    { id: 15, value: 'dibujo', label: 'Dibujo Nota', icon: 'brush', description: 'Canvas para dibujos y bocetos', is_premium: true },
    { id: 16, value: 'diagramas', label: 'Diagrama Nota', icon: 'timeline', description: 'Editor de diagramas y esquemas', is_premium: true }
  ])

  // Getters computados
  const allNotes = computed(() => notes.value)
  
  const notesByType = computed(() => (type) => 
    notes.value.filter(note => note.tipo_nota === type)
  )

  const isLoading = computed(() => loading.value)
  const hasError = computed(() => !!error.value)
  const errorMessage = computed(() => error.value)

  const activeNotes = computed(() => 
    notes.value.filter(note => !note.deleted_at)
  )

  const noteCount = computed(() => activeNotes.value.length)

  const favoriteNotes = computed(() => 
    notes.value.filter(note => note.is_favorite)
  )

  const recentNotes = computed(() => {
    return [...notes.value]
      .sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at))
      .slice(0, 10)
  })

  const getNoteTypes = computed(() => noteTypes.value)

  const getNoteTypeInfo = computed(() => (type) => {
    // Si es un número, buscar por ID, si es string, buscar por value
    if (typeof type === 'number') {
      return noteTypes.value.find(nt => nt.id === type) || noteTypes.value[0]
    }
    return noteTypes.value.find(nt => nt.value === type) || noteTypes.value[0]
  })

  // Acciones
  async function fetchNotes() {
    loading.value = true
    error.value = null

    try {
      console.log('Fetching notes from API...')
      const response = await api.notes.getAll()
      
      const notesData = response?.notas || response?.data?.notas || response?.data || []
      
      // Mapear notas del backend al formato del frontend
      notes.value = notesData.map(nota => {
        const tipoNota = noteTypes.value.find(type => type.id === nota.tipo_nota_id) || noteTypes.value[0]

        return {
          id: nota.id,
          elemento_id: nota.elemento_id,
          nombre: nota.nombre || 'Sin título',
          descripcion: nota.informacion || nota.nombre || 'Sin descripción',
          contenido: nota.contenido || '',
          tipo_nota: tipoNota.value, // Mapear correctamente según tipo_nota_id
          tipo_nota_id: nota.tipo_nota_id || 1, // El backend envía este campo correctamente
          fecha: nota.fecha,
          informacion: nota.informacion,
          clave: nota.clave,
          configuracion: {},
          etiquetas: [],
          estado: 'activo',
          is_favorite: false,
          created_at: nota.created_at,
          updated_at: nota.updated_at,
          deleted_at: nota.deleted_at
        }
      })
      
      console.log('Notes mapped to store:', notes.value)
      lastUpdated.value = new Date()
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar notas'
      console.error('Error fetching notes:', err)
    } finally {
      loading.value = false
    }
  }

  async function getNoteById(id) {
    loading.value = true
    error.value = null

    try {
      const response = await api.elements.getById(id)
      // La respuesta viene directamente como datos o con estructura .data
      const noteData = response.data || response
      currentNote.value = noteData
      return noteData
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar nota'
      console.error('Error fetching note by id:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createNote(noteData) {
    loading.value = true
    error.value = null

    console.log('🔍 === DEBUG CREATE NOTE ===')
    console.log('📝 Datos de entrada:', noteData)

    try {
      const elementData = {
        tipo: 'nota',
        descripcion: noteData.titulo || noteData.descripcion,
        estado: 'activo',
        contenido: noteData.contenido || '',
        tipo_nota: noteData.tipo_nota || 'normal',
        configuracion: noteData.configuracion || null,
        etiquetas: noteData.etiquetas || []
      }

      console.log('📤 Enviando al backend:', elementData)
      const response = await api.post('/elementos/saveUpdate', elementData)
      const newNote = response.data
      console.log('📥 Respuesta del backend:', newNote)

      // Sincronizar con elementsStore para que aparezca en Home
      try {
        console.log('🔄 Iniciando sincronización con elementsStore...')
        const elementsStore = useElementsStore()
        console.log('📊 ElementsStore antes - Total elementos:', elementsStore.allElements.length)
        console.log('📊 ElementsStore antes - Loading state:', elementsStore.loading)
        console.log('📊 ElementsStore antes - Error state:', elementsStore.error)

        // Forzar refrescar elementos desde el servidor (esto ya incluirá la nueva nota)
        await elementsStore.fetchElements(true) // Force refresh

        console.log('📊 ElementsStore después - Total elementos:', elementsStore.allElements.length)

        // Refrescar también la lista de notas para mantener sincronizado
        await fetchNotes()
        console.log('📋 Notas refrescadas. Total notas:', notes.value.length)

        console.log('✅ Sincronización con Home completada')
      } catch (syncError) {
        console.error('⚠️ Error al sincronizar nota con Home:', syncError)
        console.error('Stack trace:', syncError.stack)
      }

      console.log('🎉 Proceso de creación completado')
      return newNote
    } catch (err) {
      console.error('❌ Error en createNote:', err)
      error.value = err.response?.data?.message || 'Error al crear nota'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateNote(noteData) {
    loading.value = true
    error.value = null

    try {
      const elementData = {
        elemento_id: noteData.id,
        tipo: 'nota',
        descripcion: noteData.titulo || noteData.descripcion,
        estado: noteData.estado || 'activo',
        contenido: noteData.contenido || '',
        tipo_nota: noteData.tipo_nota || 'normal',
        configuracion: noteData.configuracion || null,
        etiquetas: noteData.etiquetas || []
      }

      const response = await api.post('/elementos/saveUpdate', elementData)
      const updatedNote = response.data

      // Sincronizar con elementsStore para actualizar Home
      try {
        const elementsStore = useElementsStore()
        await elementsStore.fetchElements() // Refrescar elementos para mostrar cambios

        // Refrescar también la lista de notas para mantener sincronizado
        await fetchNotes()
        console.log('✅ Nota actualizada sincronizada con Home')
      } catch (syncError) {
        console.error('⚠️ Error al sincronizar actualización con Home:', syncError)
      }

      return updatedNote
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar nota'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteNote(noteId) {
    loading.value = true
    error.value = null

    try {
      await api.post(`/elementos/eliminarElemento/${noteId}`)

      // Marcar como eliminado (soft delete)
      const note = notes.value.find(n => n.id === noteId)
      if (note) {
        note.deleted_at = new Date().toISOString()
      }

      // Sincronizar con elementsStore para actualizar Home
      try {
        const elementsStore = useElementsStore()
        await elementsStore.fetchElements() // Refrescar elementos para ocultar la nota eliminada
        console.log('✅ Nota eliminada sincronizada con Home')
      } catch (syncError) {
        console.error('⚠️ Error al sincronizar eliminación con Home:', syncError)
      }

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar nota'
      throw err
    } finally {
      loading.value = false
    }
  }

  function addNote(note) {
    notes.value.unshift(note)
  }

  function removeNote(noteId) {
    const index = notes.value.findIndex(n => n.id === noteId)
    if (index !== -1) {
      notes.value.splice(index, 1)
    }
  }

  function setCurrentNote(note) {
    currentNote.value = note
  }

  function clearCurrentNote() {
    currentNote.value = null
  }

  function toggleNoteFavorite(noteId) {
    const note = notes.value.find(n => n.id === noteId)
    if (note) {
      note.is_favorite = !note.is_favorite
      // En una implementación real, aquí se haría la llamada al API
    }
  }

  function addNoteTag(noteId, tag) {
    const note = notes.value.find(n => n.id === noteId)
    if (note && !note.etiquetas?.includes(tag)) {
      note.etiquetas = [...(note.etiquetas || []), tag]
    }
  }

  function removeNoteTag(noteId, tag) {
    const note = notes.value.find(n => n.id === noteId)
    if (note && note.etiquetas) {
      note.etiquetas = note.etiquetas.filter(t => t !== tag)
    }
  }

  function searchNotes(query) {
    const searchTerm = query.toLowerCase()
    return notes.value.filter(note => 
      note.descripcion?.toLowerCase().includes(searchTerm) ||
      note.contenido?.toLowerCase().includes(searchTerm) ||
      note.etiquetas?.some(tag => tag.toLowerCase().includes(searchTerm))
    )
  }

  function sortNotes(sortBy = 'updated_at', sortOrder = 'desc') {
    return [...notes.value].sort((a, b) => {
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

  function clearError() {
    error.value = null
  }

  function clearNotes() {
    notes.value = []
    currentNote.value = null
    error.value = null
    lastUpdated.value = null
  }

  // Refrescar notas
  async function refreshNotes() {
    await fetchNotes()
  }

  // Función para guardar nota en memoria (compatibilidad con Vuex)
  function saveNotaEnMemoria(noteData) {
    currentNote.value = noteData
    return noteData
  }

  // Actualizar orden de notas
  async function updateNotesOrder(notesOrder) {
    loading.value = true
    error.value = null

    try {
      // Actualizar orden local
      notesOrder.forEach(({ id, orden }) => {
        const note = notes.value.find(n => n.id === id)
        if (note) {
          note.orden = orden
        }
      })

      // Reordenar el array
      notes.value.sort((a, b) => a.orden - b.orden)

      return true
    } catch (err) {
      error.value = err.message || 'Error al actualizar orden'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    // Estado
    notes,
    currentNote,
    loading,
    error,
    lastUpdated,
    noteTypes,

    // Getters
    allNotes,
    notesByType,
    isLoading,
    hasError,
    errorMessage,
    activeNotes,
    noteCount,
    favoriteNotes,
    recentNotes,
    getNoteTypes,
    getNoteTypeInfo,

    // Acciones
    fetchNotes,
    getNoteById,
    createNote,
    updateNote,
    deleteNote,
    addNote,
    removeNote,
    setCurrentNote,
    clearCurrentNote,
    toggleNoteFavorite,
    addNoteTag,
    removeNoteTag,
    searchNotes,
    sortNotes,
    clearError,
    clearNotes,
    refreshNotes,
    saveNotaEnMemoria,
    updateNotesOrder
  }
})