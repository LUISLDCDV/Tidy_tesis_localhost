<template>
  <q-page class="elements-container">
    <PlusModal/>

    <!-- Header con búsqueda e indicador de sincronización -->
    <div class="header-container q-mb-lg q-pt-xl">
      <!-- Barra de búsqueda -->
      <div class="search-container">
        <q-input
          v-model="searchQuery"
          filled
          placeholder="Buscar elemento..."
          class="search-input"
          style="max-width: 400px;"
        >
          <template v-slot:prepend>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>

      <!-- Indicador de sincronización -->
      <SyncStatusIndicator 
        :always-show="false"
        class="sync-indicator-container"
      />
    </div>

    <!-- Estado de carga inicial -->
    <div v-if="isInitialLoading" class="loading-container">
      <q-spinner-dots size="xl" color="primary" />
      <p class="text-body1 text-grey-6 q-mt-md">Cargando elementos...</p>
    </div>

    <!-- Lista de elementos -->
    <div v-else class="elements-list">
      <q-card
        v-for="(elemento, index) in filteredElementos"
        :key="elemento.id"
        class="element-card q-mb-sm"
        :class="{
          'dragging': draggingIndex === index,
          'hovered': hoveredIndex === index,
          'updating': updatingElements.has(elemento.id)
        }"
        :data-index="index"
        draggable="true"
        @dragstart="handleDragStart(index, $event)"
        @dragover.prevent="handleDragOver(index)"
        @dragleave="handleDragLeave"
        @drop="handleDrop(index, $event)"
        @click="handleElementClick(elemento, $event)"
        flat
        bordered
      >
        <q-card-section class="element-content row items-center no-wrap q-pa-md">
          <!-- Drag Handle -->
          <q-btn 
            class="drag-btn"
            @mousedown.stop
            flat
            dense
            icon="drag_indicator"
            size="sm"
            color="grey-6"
          />

          <!-- Index Number -->
          <div class="q-mx-sm text-caption text-grey-6">
            {{ index + 1 }}
          </div>

          <!-- Element Content -->
          <div class="row items-center no-wrap q-gutter-sm col">
            <div class="text-caption text-grey-6">
              {{ elemento.id }}
            </div>
            
            <div 
              class="text-grey-8"
              v-html="getIconPath(elemento.tipo)"
            />
            
            <div class="text-weight-medium">
              {{ elemento.descripcion || elemento.nombre || 'Sin título' }}
            </div>
          </div>

          <!-- Edit Button (appears on hover) -->
          <q-btn 
            class="edit-btn"
            @click.stop="handleElementClick(elemento, $event)"
            flat
            dense
            round
            icon="edit"
            size="sm"
            color="grey-6"
          />
        </q-card-section>
      </q-card>
    </div>

    <!-- Mensaje de error -->
    <q-banner 
      v-if="error" 
      class="q-mt-md text-negative"
      rounded
      dense
    >
      {{ error }}
    </q-banner>
  </q-page>
</template>

<script>
import PlusModal from './PlusModal.vue';
import SyncStatusIndicator from '@/components/SyncStatusIndicator.vue';
import { monitorForElements } from '@atlaskit/pragmatic-drag-and-drop/element/adapter';
import { useElementsStore } from '@/stores/elements';
import { useNotesStore } from '@/stores/notes';
import { obfuscateId } from '@/utils/obfuscateId';


export default {
  props: {
    id: {
      type: [String, Number],
      required: false, // Set to true if `id` is mandatory
      default: null,   // Default value if not provided
    },
  },
  components: {
    PlusModal,
    SyncStatusIndicator
  },
  data() {
    return {
      searchQuery: '', // Texto ingresado en el buscador
      draggingIndex: null,
      hoveredIndex: null,
      error: null,
      backupElementos: [],
      updatingElements: new Set(), // IDs de elementos que están siendo actualizados
      isInitialLoading: true, // Estado de carga inicial
    };
  },
  setup() {
    const elementsStore = useElementsStore();
    const notesStore = useNotesStore();
    return {
      elementsStore,
      notesStore
    };
  },
  computed: {
    elementos() {
      return this.elementsStore.allElements;
    },
    isOnline() {
      return this.elementsStore.isOnline;
    },
    isSyncing() {
      return this.elementsStore.isSyncing;
    },
    filteredElementos() {
      if (!this.searchQuery) {
        // Si no hay texto de búsqueda, mostrar todos los elementos
        return this.elementos;
      }
      // Filtrar elementos basados en la descripción o el ID
      const query = this.searchQuery.toLowerCase();
      return this.elementos.filter((elemento) => {
        const description = elemento.descripcion || elemento.nombre || '';
        return (
          description.toLowerCase().includes(query) ||
          elemento.id.toString().includes(query)
        );
      });
    },
  },
  async mounted() {
    // Esperar a que termine la carga inicial antes de configurar drag and drop
    if (this.isInitialLoading) {
      await new Promise(resolve => {
        const unwatch = this.$watch('isInitialLoading', (newVal) => {
          if (!newVal) {
            unwatch();
            resolve();
          }
        });
      });
    }
    this.setupDragAndDrop();
  },
  methods: {
    async fetchElementos() {
      await this.elementsStore.fetchElements();
    },
    async fetchElementoId(id) {
      return await this.elementsStore.getElementById(id);
    },
    findObjetivoContenedor(metaData) {
      // Buscar el objetivo contenedor de esta meta
      const objetivos = this.elementsStore.allElements.filter(el => el.tipo === 'objetivo');

      // De los logs del backend vimos que objetivo id=1 tiene elemento_id=4
      // Entonces necesitamos buscar el elemento que corresponde a ese objetivo
      // La meta busca objetivo_id=1, que según el backend tiene elemento_id=4
      // Entonces buscamos elemento con id=4 (que es el objetivo "bajar de peso")

      if (metaData.objetivo_id) {
        // Buscar directamente por objetivo_id
        let objetivoEncontrado = objetivos.find(obj => obj.id === metaData.objetivo_id);

        if (!objetivoEncontrado) {
          // Mapeo basado en la estructura del backend: objetivo_id → elemento_id
          const mapeoObjetivos = { 1: 4, 2: 24 };
          const elementoId = mapeoObjetivos[metaData.objetivo_id];

          if (elementoId) {
            objetivoEncontrado = objetivos.find(obj => obj.id === elementoId);
          }
        }

        if (objetivoEncontrado) {
          return objetivoEncontrado;
        }
      }

      // Buscar por elemento_id de la meta (caso alternativo)
      if (metaData.elemento_id) {
        const objetivoEncontrado = objetivos.find(obj => obj.elemento_id === metaData.elemento_id);
        if (objetivoEncontrado) {
          return objetivoEncontrado;
        }
      }

      return null;
    },
    routeConfig() {
      return {
        nota: {
          1: 'NoteNormal',
          2: 'NoteComidaSemana',
          3: 'NoteClaves',
          4: 'NoteCodigo',
          5: 'NoteComprasSupermercado',
          6: 'NoteGastosMensuales',
          7: 'NotePuntosJuego',
          8: 'NotePresupuesto',
          9: 'NoteTimeboxing',
          10: 'NoteRecetas',
          11: 'NoteMedidasConstruct',
          12: 'NoteRecomentaciones',
          13: 'NotePedidoGrupal',
          14: 'NoteViaje',
          15: 'DibujoNote',
          16: 'DiagramaNote',
          default: 'NotesList'
        },
        alarma: 'AlarmasList',
        evento: 'CalendarList',
        objetivo: (data, elemento) => {
          console.log('🎯 Routing OBJETIVO - data:', data, 'elemento:', elemento);
          const route = {
            name: 'StepsHome',
            params: { id: data.elemento_id || elemento.elemento_id }
          };
          console.log('🚀 Objetivo route:', route);
          return route;
        },
        meta: (data, elemento) => {
          const objetivoPadre = this.findObjetivoContenedor(data);

          if (!objetivoPadre) {
            // Fallback: usar el elemento_id de la meta directamente
            const fallbackId = data.elemento_id || elemento.elemento_id || elemento.id;
            return {
              name: 'StepsHome',
              params: { id: fallbackId }
            };
          }

          // Usar el ID del objetivo encontrado
          const objetivoElementoId = objetivoPadre.elemento_id || objetivoPadre.id;

          return {
            name: 'StepsHome',
            params: { id: objetivoElementoId }
          };
        },
        calendario: (data, elemento) => ({
          name: 'CalendarList',
          params: { id: elemento.id }
        }),
        default: (elemento) => ({
          name: 'ElementoVista',
          params: { id: elemento.id }
        })
      }
    },

    setupDragAndDrop() {
      
      monitorForElements({
        onDrop: ({ source, location }) => {
          if (location) {
            const newOrder = [...this.elementos]; // Desde Pinia
            const [movedItem] = newOrder.splice(source.data.index, 1);
            newOrder.splice(location.data.index, 0, movedItem);
            
            // Actualiza Pinia
            this.elementsStore.elements = newOrder;

            // Envía al backend
            this.updateOrder(newOrder.map((el, i) => ({
              id: el.id,
              orden: i + 1,
            })));
          }
        },
      });
    },
    handleDragStart(index, event) {
      this.draggingIndex = index;
      event.target.style.opacity = '0.2';
      this.backupElementos = [...this.elementos]; // Crear un respaldo del listado
    },

    handleDragOver(index) {
      if (this.draggingIndex !== index) {
        this.hoveredIndex = index;
      }
    },

    handleDragLeave() {
      this.hoveredIndex = null;
    },

    async handleDrop(targetIndex) {
      if (this.draggingIndex === null) return;
      
      const draggedElement = this.elementos[this.draggingIndex];
      
      try {
        const newElementos = [...this.elementos]; // Copia desde Pinia
        const [draggedItem] = newElementos.splice(this.draggingIndex, 1);
        newElementos.splice(targetIndex, 0, draggedItem);

        // Actualiza el store inmediatamente para feedback visual
        this.elementsStore.elements = newElementos;
        
        const elementosOrdenados = newElementos.map((el, index) => ({
          id: el.id,
          orden: index + 1,
        }));

        // Marcar solo los elementos que cambiaron de orden como "actualizándose"
        const start = Math.min(this.draggingIndex, targetIndex);
        const end = Math.max(this.draggingIndex, targetIndex);
        
        for (let i = start; i <= end; i++) {
          if (newElementos[i]) {
            this.updatingElements.add(newElementos[i].id);
          }
        }

        this.draggingIndex = null;
        this.hoveredIndex = null;
        this.restoreStyles();
        
        // Actualiza el backend 
        await this.elementsStore.updateElementsOrder(elementosOrdenados);
        
        // Remover elementos del estado de actualización después de la operación exitosa
        for (let i = start; i <= end; i++) {
          if (newElementos[i]) {
            this.updatingElements.delete(newElementos[i].id);
          }
        }
        
      } catch(error) {
        console.error('Error al actualizar el orden:', error);
        this.error = 'No se pudo actualizar el orden';
        
        // Restaurar el orden original en caso de error
        this.elementsStore.elements = this.backupElementos;
        
        // Limpiar estado de actualización
        this.updatingElements.clear();
        
        this.draggingIndex = null;
        this.hoveredIndex = null;
        this.restoreStyles();
      }
    },

    restoreStyles() {
      const elementos = document.querySelectorAll('.elemento');
      elementos.forEach((el) => {
        el.style.opacity = '1';
        el.classList.remove('drag-over');
      });
    },
    handleElementClick(elemento, event) {
      // Evita clics durante el arrastre
      if (this.draggingIndex !== null) return;

      // Excluye el botón de arrastre
      const isDragButton = event.target.closest('.drag-btn');
      if (isDragButton) return;

      // Navega a la vista del elemento
      this.irAVistaElemento(elemento);
    },
    getIconPath(icon) {
      const paths = {
        nota: `<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256"><path d="M88,96a8,8,0,0,1,8-8h64a8,8,0,0,1,0,16H96A8,8,0,0,1,88,96Zm8,40h64a8,8,0,0,0,0-16H96a8,8,0,0,0,0,16Zm32,16H96a8,8,0,0,0,0,16h32a8,8,0,0,0,0-16ZM224,48V156.69A15.86,15.86,0,0,1,219.31,168L168,219.31A15.86,15.86,0,0,1,156.69,224H48a16,16,0,0,1-16-16V48A16,16,0,0,1,48,32H208A16,16,0,0,1,224,48ZM48,208H152V160a8,8,0,0,1,8-8h48V48H48Zm120-40v28.7L196.69,168Z"></path></svg>`,
        alarma: `<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256"><path d="M221.8,175.94C216.25,166.38,208,139.33,208,104a80,80,0,1,0-160,0c0,35.34-8.26,62.38-13.81,71.94A16,16,0,0,0,48,200H88.81a40,40,0,0,0,78.38,0H208a16,16,0,0,0,13.8-24.06ZM128,216a24,24,0,0,1-22.62-16h45.24A24,24,0,0,1,128,216ZM48,184c7.7-13.24,16-43.92,16-80a64,64,0,1,1,128,0c0,36.05,8.28,66.73,16,80Z"></path></svg>`,
        calendario: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z"/><path d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg>`, 
        objetivo: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"  viewBox="0 0 16 16"><path d="M2.5.5A.5.5 0 0 1 3 0h10a.5.5 0 0 1 .5.5q0 .807-.034 1.536a3 3 0 1 1-1.133 5.89c-.79 1.865-1.878 2.777-2.833 3.011v2.173l1.425.356c.194.048.377.135.537.255L13.3 15.1a.5.5 0 0 1-.3.9H3a.5.5 0 0 1-.3-.9l1.838-1.379c.16-.12.343-.207.537-.255L6.5 13.11v-2.173c-.955-.234-2.043-1.146-2.833-3.012a3 3 0 1 1-1.132-5.89A33 33 0 0 1 2.5.5m.099 2.54a2 2 0 0 0 .72 3.935c-.333-1.05-.588-2.346-.72-3.935m10.083 3.935a2 2 0 0 0 .72-3.935c-.133 1.59-.388 2.885-.72 3.935"/></svg>`,
        meta:`<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256"><path d="M34.76,42A8,8,0,0,0,32,48V216a8,8,0,0,0,16,0V171.77c26.79-21.16,49.87-9.75,76.45,3.41,16.4,8.11,34.06,16.85,53,16.85,13.93,0,28.54-4.75,43.82-18a8,8,0,0,0,2.76-6V48A8,8,0,0,0,210.76,42c-28,24.23-51.72,12.49-79.21-1.12C103.07,26.76,70.78,10.79,34.76,42ZM208,164.25c-26.79,21.16-49.87,9.74-76.45-3.41-25-12.35-52.81-26.13-83.55-8.4V51.79c26.79-21.16,49.87-9.75,76.45,3.4,25,12.35,52.82,26.13,83.55,8.4Z"></path></svg>`, 
        Elementos: `<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256"><path d="M104,40H56A16,16,0,0,0,40,56v48a16,16,0,0,0,16,16h48a16,16,0,0,0,16-16V56A16,16,0,0,0,104,40Zm0,64H56V56h48v48Zm96-64H152a16,16,0,0,0-16,16v48a16,16,0,0,0,16,16h48a16,16,0,0,0,16-16V56A16,16,0,0,0,200,40Zm0,64H152V56h48v48Zm-96,32H56a16,16,0,0,0-16,16v48a16,16,0,0,0,16,16h48a16,16,0,0,0,16-16V152A16,16,0,0,0,104,136Zm0,64H56V152h48v48Zm96-64H152a16,16,0,0,0-16,16v48a16,16,0,0,0,16,16h48a16,16,0,0,0,16-16V152A16,16,0,0,0,200,136Zm0,64H152V152h48v48Z"></path></svg>`, 
        evento: `<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256"><path d="M208,32H184V24a8,8,0,0,0-16,0v8H88V24a8,8,0,0,0-16,0v8H48A16,16,0,0,0,32,48V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V48A16,16,0,0,0,208,32ZM112,184a8,8,0,0,1-16,0V132.94l-4.42,2.22a8,8,0,0,1-7.16-14.32l16-8A8,8,0,0,1,112,120Zm56-8a8,8,0,0,1,0,16H136a8,8,0,0,1-6.4-12.8l28.78-38.37A8,8,0,1,0,145.07,132a8,8,0,1,1-13.85-8A24,24,0,0,1,176,136a23.76,23.76,0,0,1-4.84,14.45L152,176ZM48,80V48H72v8a8,8,0,0,0,16,0V48h80v8a8,8,0,0,0,16,0V48h24V80Z"></path></svg>`, 
      };
      return paths[icon] || '';
    },
    async irAVistaElemento(elemento) {
      try {
        const data = await this.fetchElementoId(elemento.id);
        if (!data) {
          throw new Error('No se pudo obtener la información del elemento');
        }

        // Manejo especial para notas
        if (elemento.tipo === 'nota') {
          await this.notesStore.saveNotaEnMemoria(data);

          return this.$router.push({
            name: 'DynamicNote',
            params: {
              type: data.tipo_nota_id || 1,
              modo: 'editar',
              id: data.elemento_id || elemento.id
            },
          });
        }

        // Manejo para otros tipos
        const routeFunction = this.routeConfig()[elemento.tipo];
        const route = routeFunction
          ? (typeof routeFunction === 'function'
              ? routeFunction(data, elemento)
              : { name: routeFunction })
          : this.routeConfig().default(elemento);

        this.$router.push(route);
      } catch (error) {
        console.error('Error al cargar el elemento:', error);
        this.error = error.message || 'No se pudo obtener el elemento';
      }
    },
    addMockData() {
      console.log('🧪 Adding mock data for testing...');
      const mockElements = [
        {
          id: 1,
          tipo: 'objetivo',
          descripcion: 'Objetivo de prueba 1',
          nombre: 'Objetivo Test 1',
          orden: 1,
          created_at: new Date().toISOString()
        },
        {
          id: 2,
          tipo: 'nota',
          descripcion: 'Nota de prueba 1',
          nombre: 'Nota Test 1',
          orden: 2,
          created_at: new Date().toISOString()
        },
        {
          id: 3,
          tipo: 'alarma',
          descripcion: 'Alarma de prueba 1',
          nombre: 'Alarma Test 1',
          orden: 3,
          created_at: new Date().toISOString()
        }
      ];

      // Add directly to store for testing
      this.elementsStore.elements = mockElements;
      console.log('🧪 Mock data added:', mockElements);
    },
  },
  async created() {
    // Asegurar que la carga inicial esté en true
    this.isInitialLoading = true;

    try {
      await this.fetchElementos();
    } catch (error) {
      console.error('Error loading elements:', error);
      this.error = error.message;
    } finally {
      // Completar la carga inicial
      this.isInitialLoading = false;
    }
  },
};
</script>

<style scoped>
.elements-container {
  min-height: 100vh;
  background-color: #f9fafb;
  padding: 16px 32px;
  width: 100%;
  transition: all 0.3s ease;
}

/* El layout se maneja a nivel de App.vue - asegurar que se estire */
.elements-container {
  margin-left: 0 !important;
  width: 100% !important;
  max-width: none !important;
  box-sizing: border-box;
}

.header-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.search-container {
  flex: 1;
  max-width: 400px;
  min-width: 250px;
}

.search-input {
  width: 100%;
}

.sync-indicator-container {
  flex-shrink: 0;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  padding: 40px 20px;
}

.elements-list {
  max-width: 100%;
}

.element-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  transition: all 0.2s ease;
  cursor: pointer;
}

.element-card:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transform: scale(1.01);
}

.element-card.hovered {
  transform: scale(1.02);
  border: 2px solid #176F46;
}

.element-card.dragging {
  opacity: 0.5;
}

.element-card.updating {
  opacity: 0.6;
  background-color: #f5f5f5 !important;
  color: #9ca3af !important;
  transition: all 0.3s ease;
}

.element-card.updating .text-grey-8,
.element-card.updating .text-weight-medium,
.element-card.updating .text-caption {
  color: #9ca3af !important;
}

.element-content {
  padding: 16px !important;
}

.drag-btn {
  color: #9ca3af;
  transition: color 0.2s ease;
}

.drag-btn:hover {
  color: #6b7280;
}

.edit-btn {
  opacity: 0;
  transition: opacity 0.2s ease;
}

.element-card:hover .edit-btn {
  opacity: 1;
}

/* Dark mode support - if using Quasar dark mode */
.body--dark .elements-container {
  background-color: #111827;
}

.body--dark .element-card {
  background: #1f2937;
  color: white;
}

.body--dark .element-card:hover {
  background: #374151;
}

.body--dark .element-card.updating {
  background-color: #374151 !important;
  color: #6b7280 !important;
}

.body--dark .element-card.updating .text-grey-8,
.body--dark .element-card.updating .text-weight-medium,
.body--dark .element-card.updating .text-caption {
  color: #6b7280 !important;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .elements-container {
    padding: 12px 16px;
    padding-bottom: 120px; /* Espacio para footer fijo mobile */
  }

  /* Header más compacto en mobile */
  .header-container {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
    margin-bottom: 16px;
  }

  .search-container {
    max-width: 100%;
    min-width: auto;
  }

  .search-input {
    max-width: 100% !important;
  }

  .sync-indicator-container {
    align-self: center;
  }

  /* Cards más compactas en mobile */
  .element-card {
    margin-bottom: 8px;
    border-radius: 8px;
  }

  .element-content {
    padding: 12px !important;
  }

  /* Ocultar números de índice en mobile para ahorrar espacio */
  .element-content > div:nth-child(2) {
    display: none;
  }

  /* Hacer los botones más grandes para touch */
  .drag-btn, .edit-btn {
    min-width: 44px;
    min-height: 44px;
  }

  /* Mejor espaciado del contenido */
  .element-content .row {
    gap: 8px;
  }

  /* ID más pequeño en mobile */
  .text-caption {
    font-size: 0.7rem;
  }

  /* Título más prominente en mobile */
  .text-weight-medium {
    font-size: 0.95rem;
    line-height: 1.3;
  }
}

/* Mobile pequeño (< 480px) */
@media (max-width: 480px) {
  .elements-container {
    padding: 8px 12px;
    padding-bottom: 120px; /* Espacio para footer fijo mobile */
  }

  .element-card {
    margin-bottom: 6px;
  }

  .element-content {
    padding: 10px !important;
  }

  /* En pantallas muy pequeñas, hacer el layout más vertical */
  .element-content .row {
    flex-wrap: wrap;
    gap: 4px;
  }

  /* Ocultar ID del elemento en pantallas muy pequeñas */
  .element-content .text-caption:first-of-type {
    display: none;
  }

  /* Drag handle más pequeño */
  .drag-btn {
    min-width: 36px;
    min-height: 36px;
  }
}

/* Landscape móvil */
@media (max-width: 896px) and (orientation: landscape) {
  .elements-container {
    padding: 8px 16px;
    padding-bottom: 100px; /* Espacio para footer fijo mobile landscape */
  }

  .element-card {
    margin-bottom: 4px;
  }

  .element-content {
    padding: 8px 12px !important;
  }
}

/* Mejoras para touch */
@media (pointer: coarse) {
  .element-card {
    cursor: pointer;
  }

  /* Aumentar área de toque */
  .drag-btn, .edit-btn {
    padding: 8px;
  }

  /* Eliminar hover effects en dispositivos táctiles */
  .element-card:hover {
    transform: none;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  }

  .element-card:hover .edit-btn {
    opacity: 1; /* Siempre visible en mobile */
  }
}

</style>