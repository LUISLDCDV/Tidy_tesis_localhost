<template>
  <q-page class="dinamic-note-wrapper">
    <!-- Header with back button -->
    <div class="">
      <div class="">
        <BackButton class="back-btn" />
        <q-btn
          v-if="modo === 'editar'"
          @click="confirmDelete"
          flat
          round
          color="negative"
          icon="delete"
          class="delete-btn"
        >
          <q-tooltip>Eliminar nota</q-tooltip>
        </q-btn>
      </div>
    </div>

    <div class="dinamic-note-container">
      <!-- Title section -->
      <div class="note-title-section">
        <q-card class="title-card">
          <q-card-section class="title-card-content">
            <q-input 
              v-model="localNombre" 
              borderless
              class="note-title-input"
              :input-style="titleInputStyle"
              :placeholder="$t('notes.titlePlaceholder') || 'Sin título'"
            />
          </q-card-section>
        </q-card>
      </div>

      <!-- Note content -->
      <div class="note-content-section">
        <Suspense>
          <component 
            :is="currentComponent" 
            :content="localContent"
            @update:content="localContent = $event"
          />
          <template #fallback>
            <div class="loading-container">
              <q-spinner-dots
                :size="$q.screen.lt.sm ? '40px' : '48px'"
                color="grey-6"
                class="loading-spinner"
              />
              <div class="loading-text">{{ $t('notes.loading') || 'Cargando componente...' }}</div>
            </div>
          </template>
        </Suspense>
      </div>
    </div>

    <!-- Floating action button -->
    <q-btn
      @click="saveChanges"
      class="save-button-fab"
      fab
      color="primary"
      icon="save"
    />
  </q-page>
</template>

<script>
import { obfuscateId, deobfuscateId } from '@/utils/obfuscateId';
import { useNotesStore } from '@/stores/notes';
import { defineAsyncComponent } from 'vue';
import LoadingSpinner from '@/components/LoadingSpinner.vue';
import ErrorComponent from '@/components/ErrorComponent.vue';
import { formatDateForMySQL } from '@/utils/dateFormat';
import BackButton from '@/components/Nav/BackButton.vue';

// Función para generar componentes de forma dinámica
const createAsyncComponent = (loader) => defineAsyncComponent({
  loader,
  loadingComponent: LoadingSpinner,
  errorComponent: ErrorComponent,
  timeout: 3000
});

export default {
  components: {
    BackButton,
  },
  props: {
    type: { type: [String, Number], required: true },
    nombre: { type: String, default: 'Sin título' },   
    modo: { type: String, default: 'crear' },
    id: { type: [String, Number], default: null },
  },
  data() {
    return {
      localNombre: '',
      localContent: ''
    }
  },
  async created() {
    console.log('modo:', this.modo);
    console.log('tipo:', this.type);
    console.log('id:', this.id);

    if (this.modo === 'editar') {
      await this.cargarNotaExistente();
    } else {
      this.inicializarNuevaNota();
    }
  },
  setup() {
    const notesStore = useNotesStore();
    
    return {
      notesStore
    };
  },
  
  computed: {
    notaActual() {
      return this.notesStore.currentNote;
    },
    
    titleInputStyle() {
      return {
        'text-align': 'center',
        'font-size': this.$q.screen.lt.sm ? '1.125rem' : '1.5rem',
        'font-weight': '600'
      };
    },
    
    realId() {
      try {
        return deobfuscateId(this.$route.params.oid);
      } catch (error) {
        console.error('Error al desofuscar ID:', error);
        return null;
      }
    },

    currentComponent() {
      const componentMap = {
        1: createAsyncComponent(() => import('@/components/Elements/Notes/tipos/NormalNote/NormalNote.vue')),
        2: createAsyncComponent(() => import('@/components/Elements/Notes/tipos/ComidasSemanales/PlanificacionComidasSemanales.vue')),
        3: createAsyncComponent(() => import('@/components/Elements/Notes/tipos/GestionClaves/GestionClaves.vue')),
        4: createAsyncComponent(() => import('@/components/Elements/Notes/tipos/NormalCodigo/NormalCodigo.vue')),
        5: createAsyncComponent(() => import('@/components/Elements/Notes/tipos/ComprasSuper/NoteCompras.vue')),
        6: createAsyncComponent(() => import('@/components/Elements/Notes/tipos/GastosMensuales/GastosMensualesView.vue')),
        7: createAsyncComponent(() => import('@/components/Elements/Notes/tipos/PuntosJuego/NotePuntos.vue')),
        8: createAsyncComponent(() => import('@/components/Elements/Notes/tipos/ControlDePresupuesto/PresupuestoView.vue')),
        9: createAsyncComponent(() => import('@/components/Elements/Notes/tipos/GestionTiempo/TimeBoxingView.vue')),
        10: createAsyncComponent(() => import('@/components/Elements/Notes/tipos/RecetaNote/RecetaView.vue')),
        11: createAsyncComponent(() => import('@/components/Elements/Notes/tipos/MedidasConstruct/MedidasConstructView.vue')),
        12: createAsyncComponent(() => import('@/components/Elements/Notes/tipos/RecomendacionesNote/RecomendacionesView.vue')),
        13: createAsyncComponent(() => import('@/components/Elements/Notes/tipos/PedidosGrupales/NotePedidos.vue')),
        14: createAsyncComponent(() => import('@/components/Elements/Notes/tipos/ViajeNote/TempleTrip.vue')),
        15: createAsyncComponent(() => import('@/components/Elements/Notes/tipos/DibujoNote/DibujoNote.vue')),
        16: createAsyncComponent(() => import('@/components/Elements/Notes/tipos/DiagramaNote/DiagramaNote.vue'))
      };

      return componentMap[this.type] || createAsyncComponent(() => import('@/components/NotFound.vue'));
    },
  },

  methods: {
    async fetchNota(noteId) {
      return await this.notesStore.getNoteById(noteId);
    },
    async guardarNota(noteData) {
      // Por ahora, usar la API directamente hasta migrar completamente a Pinia
      try {
        const { guardarElemento } = await import('@/services/api');
        const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');

        if (!token) {
          throw new Error('No hay token de autenticación');
        }

        const elementData = {
          elemento_id: noteData.id || null,
          nombre: noteData.nombre || 'Sin título',
          fecha: noteData.fecha || new Date().toISOString().split('T')[0],
          tipo_nota_id: parseInt(noteData.tipo_nota_id || this.type),
          contenido: noteData.contenido,
          informacion: 'Nota creada desde la aplicación',
          tipo: 'nota'
        };

        console.log('guardando en api elemento:', elementData);

        return await guardarElemento(token, 'nota', elementData);
      } catch (error) {
        console.error('Error guardando nota:', error);

        // Si es error de validación de tipo_nota_id, mostrar información útil
        if (error.response?.status === 422 && error.response?.data?.errors?.tipo_nota_id) {
          console.error('Error de tipo de nota. Datos enviados:', {
            tipo_nota_id: elementData.tipo_nota_id,
            tipo: elementData.tipo,
            propType: this.type
          });
        }

        throw error;
      }
    },

    async cargarNotaExistente() {
      const rutaid = this.$route.params.id
      await this.fetchNota(rutaid);
      console.log('Nota cargada:', this.notaActual.contenido);
      this.localNombre = this.notaActual.nombre;
      if (typeof this.notaActual.contenido === 'object') {
        this.localContent = JSON.stringify(this.notaActual.contenido);
      } else {
        this.localContent = this.notaActual.contenido || '{}';
      }
      this.commitNoteToStore();
    },
    
    inicializarNuevaNota() {
      this.localNombre = 'Sin título';
      this.localContent = '{}';
      this.commitNoteToStore();
    },
    
    commitNoteToStore() {
      // TODO: Migrar a Pinia
      console.log('Saving note:', {
        elemento_id: this.id,
        tipo_nota_id: this.type,
        nombre: this.localNombre,
        contenido: this.localContent,
        fecha: new Date().toISOString()
      });
    },
    
    async saveChanges() {
      try {
        const contenido = typeof this.localContent === 'object'
          ? JSON.stringify(this.localContent)
          : this.localContent;

        // Usar el ID de la ruta si estamos en modo editar
        const noteId = this.modo === 'editar' ? this.$route.params.id : (this.id || null);

        await this.guardarNota({
          id: noteId,
          nombre: this.localNombre,
          fecha: formatDateForMySQL(new Date()),
          contenido: contenido,
          tipo_nota_id: this.type
        });
        this.$q.notify({
          type: 'positive',
          message: this.$t('notes.saveSuccess') || 'Guardado exitoso',
          position: 'top'
        });
      } catch (error) {
        console.error('Error al guardar:', error);

        let message = this.$t('notes.saveError') || 'Error al guardar';

        if (error.response?.status === 422 && error.response?.data?.errors?.tipo_nota_id) {
          message = `Error: Tipo de nota inválido (${this.type}). Debe estar entre 1 y 16.`;
        }

        this.$q.notify({
          type: 'negative',
          message: message,
          position: 'top'
        });
      }
    },

    confirmDelete() {
      this.$q.dialog({
        title: 'Confirmar eliminación',
        message: '¿Estás seguro de que deseas eliminar esta nota?',
        cancel: {
          label: 'Cancelar',
          flat: true,
          color: 'grey'
        },
        ok: {
          label: 'Eliminar',
          color: 'negative',
          flat: true
        },
        persistent: true
      }).onOk(async () => {
        await this.deleteNote();
      });
    },

    async deleteNote() {
      try {
        const noteId = this.$route.params.id;

        await this.notesStore.deleteNote(noteId);

        this.$q.notify({
          type: 'positive',
          message: 'Nota eliminada exitosamente',
          position: 'top'
        });

        // Redirigir a la lista de notas
        this.$router.push('/Notes');
      } catch (error) {
        console.error('Error al eliminar:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error al eliminar la nota',
          position: 'top'
        });
      }
    }
  }
}
</script>

<style scoped>
/* Main wrapper */
.dinamic-note-wrapper {
  position: relative;
  min-height: 100vh;
  background-color: #f9fafb;
  padding-top: 0; /* Asegurar que no hay padding superior */
}

/* Header section - CORREGIDO para evitar conflicto con nav */
.note-header {
  background-color: white;
  border-bottom: 1px solid rgb(134, 10, 103);
  position: sticky;
  top: 0; /* Si hay un nav fijo arriba, cambiar esto */
  z-index: 1000;
  padding: 12px 16px;
  margin-top: 0; /* Asegurar que no hay margen superior */
}

/* Si tienes un nav fijo en la parte superior, ajusta el top */
:deep(.q-layout__header) ~ .note-header {
  top: 56px; /* Ajustar según la altura de tu nav */
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  min-height: 44px; /* Altura mínima para mejor touch */
}

.back-btn {
  flex-shrink: 0;
}

.delete-btn {
  flex-shrink: 0;
}

/* Container */
.dinamic-note-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Title section */
.note-title-section {
  width: 100%;
}

.title-card {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.title-card-content {
  padding: 20px;
}

.note-title-input {
  text-align: center;
  font-size: 1.5rem;
  font-weight: 600;
  color: #111827;
  width: 100%;
}

/* Content section */
.note-content-section {
  flex: 1;
  min-height: 500px;
}

/* Loading states */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
  color: #6b7280;
}

.loading-spinner {
  margin-bottom: 16px;
}

.loading-text {
  font-size: 1rem;
  font-weight: 500;
}

/* Floating save button for mobile */
.save-button-fab {
  position: fixed;
  bottom: 80px;
  right: 24px;
  z-index: 9999;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.save-button-fab:active {
  transform: scale(0.95);
}

/* Mobile responsive design */
@media (max-width: 768px) {
  .note-header {
    padding: 10px 12px;
    top: 0; /* Ajustar según tu nav móvil */
  }
  
  .dinamic-note-container {
    padding: 12px;
    gap: 16px;
  }
  
  .title-card-content {
    padding: 16px;
  }
  
  .note-title-input {
    font-size: 1.25rem;
  }
  
  .note-content-section {
    min-height: 400px;
  }
  
  .loading-container {
    padding: 40px 16px;
  }
  
  .save-button-fab {
    bottom: 76px;
    right: 20px;
  }
}

@media (max-width: 480px) {
  .note-header {
    padding: 8px 10px;
  }
  
  .header-content {
    min-height: 40px;
  }
  
  .dinamic-note-container {
    padding: 8px;
    gap: 12px;
  }
  
  .title-card-content {
    padding: 12px;
  }
  
  .note-title-input {
    font-size: 1.125rem;
  }
  
  .note-content-section {
    min-height: 350px;
  }
  
  .save-button-fab {
    bottom: 72px;
    right: 16px;
    width: 56px;
    height: 56px;
  }
  
  .loading-container {
    padding: 32px 12px;
  }
}

/* SOLUCIÓN ALTERNATIVA: Si el problema persiste, usar margin-top en el container */
.dinamic-note-container.has-sticky-header {
  margin-top: 60px; /* Ajustar según la altura del header */
}

/* Dark mode support */
.body--dark .dinamic-note-wrapper {
  background-color: #111827;
}

.body--dark .note-header {
  background-color: #1f2937;
  border-color: #374151;
}

.body--dark .title-card {
  background-color: #1f2937;
  border-color: #374151;
}

.body--dark .note-title-input {
  color: #f9fafb;
}

.body--dark .loading-container {
  color: #d1d5db;
}

/* Touch improvements */
@media (hover: none) and (pointer: coarse) {
  .save-button-fab {
    min-width: 44px;
    min-height: 44px;
    touch-action: manipulation;
  }
  
  .save-button-fab:active {
    transform: scale(0.95);
  }
}

/* Print styles */
@media print {
  .note-header,
  .save-button-fab {
    display: none;
  }
  
  .dinamic-note-wrapper {
    background-color: white;
  }
  
  .title-card {
    box-shadow: none;
    border: 1px solid #e5e7eb;
  }
}

.note-header.reset-position {
  position: relative !important;
  top: auto !important;
  z-index: auto !important;
}

.dinamic-note-container.offset-for-header {
  padding-top: 70px; /* Espacio para el header */
}
</style>