<template>
  <q-page class="notes-page">
    <!-- Mobile/Desktop Responsive Layout -->
    <div class="notes-container">
      <!-- Header with title and plus button -->
      <div class="notes-header q-mb-lg">
        <div class="row justify-center">
          <h1 class="notes-title text-weight-bold">{{ $t('notes.title') }}</h1>
          <div class="notes-actions">
            <PlusModal :show-only-notes="true" />
          </div>
        </div>
      </div>

      <!-- Filters Section -->
      <div class="notes-filters q-mb-lg">
        <div class="filters-grid">
          <!-- Campo de Búsqueda -->
          <div class="filter-item">
            <q-input
              v-model="searchQuery"
              :placeholder="$t('notes.search')"
              filled
              dense
              color="primary"
              class="search-input"
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
            </q-input>
          </div>

          <!-- Filtro por tipo -->
          <div class="filter-item">
            <q-select
              v-model="filterType"
              :options="noteTypesSelectOptions"
              :label="$t('notes.filterByType')"
              filled
              dense
              emit-value
              map-options
              clearable
              color="primary"
              class="type-filter"
            />
          </div>

          <!-- Ordenar por -->
          <div class="filter-item">
            <q-select
              v-model="sortBy"
              :options="sortOptions"
              :label="$t('notes.sortBy')"
              filled
              dense
              emit-value
              map-options
              color="primary"
              class="sort-filter"
            />
          </div>
        </div>
      </div>

      <!-- Notes List -->
      <div class="notes-content">
        <div class="notes-grid">
          <q-card
            v-for="(nota, index) in filteredElementos"
            :key="nota.id"
            class="nota-item cursor-pointer"
            :class="{
              'dragging': draggingIndex === index,
              'drag-over': hoveredIndex === index
            }"
            draggable="true"
            @dragstart="handleDragStart(index, $event)"
            @dragover.prevent="handleDragOver(index)"
            @dragleave="handleDragLeave"
            @drop="handleDrop(index, $event)"
            @click="irANota(nota)"
            bordered
            flat
          >
            <q-card-section class="nota-card-content">
              <!-- Mobile: drag handle in top-right -->
              <div class="nota-header">
                <div class="nota-icon">
                  <span v-html="getIconPath(nota.tipo_nota_id)" class="note-type-icon"></span>
                </div>
                <q-btn
                  class="drag-btn"
                  flat
                  dense
                  icon="drag_handle"
                  color="grey-6"
                  :size="$q.screen.lt.sm ? 'sm' : 'md'"
                />
              </div>
              
              <!-- Note content -->
              <div class="nota-body">
                <div class="nota-type text-body2 text-grey-7">{{ getTipo(nota.tipo_nota_id) }}</div>
                <div class="nota-title text-h6 text-weight-medium q-mt-xs">{{ nota.nombre }}</div>
              </div>
              
              <!-- Note footer -->
              <div class="nota-footer">
                <div class="text-caption text-grey-5">{{ formatSimpleDate(nota.created_at || nota.updated_at) }}</div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import { useNotesStore } from '@/stores/notes';
import { useAuthStore } from '@/stores/auth';
import { usePaymentsStore } from '@/stores/payments';
import PlusModal from '../PlusModal.vue';

export default {
  data() {
    return {
      searchQuery: '',
      filterType: '',
      sortBy: 'fecha',
      draggingIndex: null,
      hoveredIndex: null,
    };
  },
  components: {
    PlusModal,
  },
  setup() {
    const notesStore = useNotesStore();
    const authStore = useAuthStore();
    const paymentsStore = usePaymentsStore();

    return {
      notesStore,
      authStore,
      paymentsStore
    };
  },
  
  computed: {
    notas() {
      return this.notesStore.allNotes;
    },
    noteTypesOptions() {
      const types = {
        1: this.$t('notes.types.1'),
        2: this.$t('notes.types.2'),
        3: this.$t('notes.types.3'),
        4: this.$t('notes.types.4'),
        5: this.$t('notes.types.5'),
        6: this.$t('notes.types.6'),
        7: this.$t('notes.types.7'),
        8: this.$t('notes.types.8'),
        9: this.$t('notes.types.9'),
        10: this.$t('notes.types.10'),
        11: this.$t('notes.types.11'),
        12: this.$t('notes.types.12'),
        13: this.$t('notes.types.13'),
        14: this.$t('notes.types.14'),
        15: this.$t('notes.types.15'),
        16: this.$t('notes.types.16')
      };
      return types;
    },
    noteTypesSelectOptions() {
      const noteTypes = this.notesStore.getNoteTypes || [];
      const userIsPremium = this.paymentsStore.hasActivePremium;

      return Object.entries(this.noteTypesOptions).map(([value, label]) => {
        const typeInfo = noteTypes.find(t => t.id === parseInt(value));
        const isPremium = typeInfo?.is_premium || false;
        return {
          label: isPremium ? `${label} 👑` : label,
          value: parseInt(value),
          disable: isPremium && !userIsPremium
        };
      });
    },
    sortOptions() {
      return [
        { label: this.$t('notes.sortOptions.name'), value: 'nombre' },
        { label: this.$t('notes.sortOptions.type'), value: 'tipo' },
        { label: this.$t('notes.sortOptions.date'), value: 'fecha' }
      ];
    },
    filteredElementos() {
      let result = [...this.notas];

      // Filtrar por texto
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        result = result.filter(nota =>
          nota.nombre.toLowerCase().includes(query) ||
          nota.id.toString().includes(query)
        );
      }

      // Filtrar por tipo
      if (this.filterType) {
        result = result.filter(nota => nota.tipo_nota_id == this.filterType);
      }

      // Ordenar
      result.sort((a, b) => {
        const fieldA = this.getSortValue(a);
        const fieldB = this.getSortValue(b);

        if (fieldA < fieldB) return this.sortDirection === 'asc' ? -1 : 1;
        if (fieldA > fieldB) return this.sortDirection === 'asc' ? 1 : -1;
        return 0;
      });

      return result;
    }
  },
  methods: {
    async fetchNotas() {
      await this.notesStore.fetchNotes();
    },
    async updateNotaOrder(notesOrder) {
      return await this.notesStore.updateNotesOrder(notesOrder);
    },
    getSortValue(nota) {
      switch (this.sortBy) {
        case 'nombre':
          return nota.nombre.toLowerCase();
        case 'tipo':
          return this.getTipo(nota.tipo_nota_id).toLowerCase();
        case 'fecha':
        default:
          return new Date(nota.updated_at);
      }
    },
    handleDragStart(index, event) {
      this.draggingIndex = index;
      event.target.style.opacity = '0.2';
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

      try {
        const newNotas = [...this.notas];
        const [draggedNota] = newNotas.splice(this.draggingIndex, 1);
        newNotas.splice(targetIndex, 0, draggedNota);

        // Actualiza el backend
        await this.updateNotaOrder(newNotas.map((nota, i) => ({
          id: nota.id,
          orden: i + 1,
        })));

        this.draggingIndex = null;
        this.hoveredIndex = null;
        this.restoreStyles();
      } catch (error) {
        console.error('Error al actualizar el orden:', error);
      }
    },
    restoreStyles() {
      const notas = document.querySelectorAll('.nota-item');
      notas.forEach((nota) => {
        nota.style.opacity = '1';
        nota.classList.remove('drag-over');
      });
    },
    irANota(nota) {
      try {
        this.notesStore.saveNotaEnMemoria(nota);
        
        return this.$router.push({
          name: 'DynamicNote',
          params: { 
            type: nota.tipo_nota_id,
            modo: 'editar',
            id: nota.elemento_id 
          }
        });
      } catch (error) {
        console.error('Error al navegar a la nota:', error);
      }
    },
    getTipo(ID) {
      return this.$t(`notes.types.${ID}`);
    },
    getIconPath(icon) {
      const paths = {
         1 : '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-note"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 20l7 -7" /><path d="M13 20v-6a1 1 0 0 1 1 -1h6v-7a2 2 0 0 0 -2 -2h-12a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7" /></svg>' ,
         2 : '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-bowl-chopsticks"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 11h16a1 1 0 0 1 1 1v.5c0 1.5 -2.517 5.573 -4 6.5v1a1 1 0 0 1 -1 1h-8a1 1 0 0 1 -1 -1v-1c-1.687 -1.054 -4 -5 -4 -6.5v-.5a1 1 0 0 1 1 -1z" /><path d="M19 7l-14 1" /><path d="M19 2l-14 3" /></svg>' ,
         3 : '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-password"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 10v4" /><path d="M10 13l4 -2" /><path d="M10 11l4 2" /><path d="M5 10v4" /><path d="M3 13l4 -2" /><path d="M3 11l4 2" /><path d="M19 10v4" /><path d="M17 13l4 -2" /><path d="M17 11l4 2" /></svg>' ,
         4 : '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-source-code"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14.5 4h2.5a3 3 0 0 1 3 3v10a3 3 0 0 1 -3 3h-10a3 3 0 0 1 -3 -3v-5" /><path d="M6 5l-2 2l2 2" /><path d="M10 9l2 -2l-2 -2" /></svg>' ,
         5 : '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-shopping-cart-dollar"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 19a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M13 17h-7v-14h-2" /><path d="M6 5l14 1l-.575 4.022m-4.925 2.978h-8.5" /><path d="M21 15h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5" /><path d="M19 21v1m0 -8v1" /></svg>' ,
         6 : '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-dollar"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 21h-7a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v3" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h12.5" /><path d="M21 15h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5" /><path d="M19 21v1m0 -8v1" /></svg>' ,
         7 : '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-scoreboard"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 5m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><path d="M12 5v2" /><path d="M12 10v1" /><path d="M12 14v1" /><path d="M12 18v1" /><path d="M7 3v2" /><path d="M17 3v2" /><path d="M15 10.5v3a1.5 1.5 0 0 0 3 0v-3a1.5 1.5 0 0 0 -3 0z" /><path d="M6 9h1.5a1.5 1.5 0 0 1 0 3h-.5h.5a1.5 1.5 0 0 1 0 3h-1.5" /></svg>' ,
         8 : '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-report-money"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M14 11h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5" /><path d="M12 17v1m0 -8v1" /></svg>' ,
         9 : '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-report"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h5.697" /><path d="M18 14v4h4" /><path d="M18 11v-4a2 2 0 0 0 -2 -2h-2" /><path d="M8 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M8 11h4" /><path d="M8 15h3" /></svg>' ,
         10 : '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-chef-hat"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3c1.918 0 3.52 1.35 3.91 3.151a4 4 0 0 1 2.09 7.723l0 7.126h-12v-7.126a4 4 0 1 1 2.092 -7.723a4 4 0 0 1 3.908 -3.151z" /><path d="M6.161 17.009l11.839 -.009" /></svg>' ,
         11 : '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-ruler-measure-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 19.875c0 .621 -.512 1.125 -1.143 1.125h-5.714a1.134 1.134 0 0 1 -1.143 -1.125v-15.875a1 1 0 0 1 1 -1h5.857c.631 0 1.143 .504 1.143 1.125z" /><path d="M12 9h-2" /><path d="M12 6h-3" /><path d="M12 12h-3" /><path d="M12 18h-3" /><path d="M12 15h-2" /><path d="M21 3h-4" /><path d="M19 3v18" /><path d="M21 21h-4" /></svg>' ,
         12 : '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-message-circle-star"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.517 19.869a9.757 9.757 0 0 1 -2.817 -.869l-4.7 1l1.3 -3.9c-2.324 -3.437 -1.426 -7.872 2.1 -10.374c3.526 -2.501 8.59 -2.296 11.845 .48c1.666 1.421 2.594 3.29 2.747 5.21" /><path d="M17.8 20.817l-2.172 1.138a.392 .392 0 0 1 -.568 -.41l.415 -2.411l-1.757 -1.707a.389 .389 0 0 1 .217 -.665l2.428 -.352l1.086 -2.193a.392 .392 0 0 1 .702 0l1.086 2.193l2.428 .352a.39 .39 0 0 1 .217 .665l-1.757 1.707l.414 2.41a.39 .39 0 0 1 -.567 .411l-2.172 -1.138z" /></svg>' ,
         13 : '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-users-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4c.96 0 1.84 .338 2.53 .901" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M16 19h6" /><path d="M19 16v6" /></svg>' ,
         14 : '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-plane-departure"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14.639 10.258l4.83 -1.294a2 2 0 1 1 1.035 3.863l-14.489 3.883l-4.45 -5.02l2.897 -.776l2.45 1.414l2.897 -.776l-3.743 -6.244l2.898 -.777l5.675 5.727z" /><path d="M3 21h18" /></svg>' ,
         15 : '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-writing"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 17v-12c0 -1.121 -.879 -2 -2 -2s-2 .879 -2 2v12l2 2l2 -2z" /><path d="M16 7h4" /><path d="M18 19h-13a2 2 0 1 1 0 -4h4a2 2 0 1 0 0 -4h-3" /></svg>' ,
         16 : '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-chart-dots-3"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 2a4 4 0 1 1 -3.843 5.114l-6.295 .786a3 3 0 0 1 -.094 .257l6.446 4.431a3 3 0 1 1 -.695 4.099l-3.527 1.058q .008 .127 .008 .255a4 4 0 1 1 -8 0l.005 -.2a4 4 0 0 1 7.366 -1.954l3.64 -1.094l.01 -.102q .023 -.204 .074 -.4l-6.688 -4.6a3 3 0 0 1 -4.407 -2.65l.005 -.176a3 3 0 0 1 5.784 -.931l6.312 -.79a4 4 0 0 1 3.899 -3.103" /></svg>'
      };
      return paths[icon] || '';
    },
    formatSimpleDate(dateString) {
      if (!dateString) return '';

      try {
        const date = new Date(dateString);
        return date.toLocaleDateString(this.$i18n.locale, {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      } catch (error) {
        console.error('Error formatting date:', error);
        return dateString;
      }
    },
  },
  created() {
    this.fetchNotas();
  },
};
</script>

<style scoped>
/* Main page layout */
.notes-page {
  min-height: 100vh;
  background-color: #f9fafb;
}

.notes-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 20px 16px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

/* Header styles */
.notes-header {
  border-bottom: 1px solid #e5e7eb;
  /* padding-bottom: 16px; */
}

.notes-title {
  font-size: 2rem;
  color: #111827;
  margin: 0;
}

.notes-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

/* Filters section */
.notes-filters {
  background-color: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
}

.filter-item {
  min-width: 0;
}

/* Notes grid */
.notes-content {
  flex: 1;
}

.notes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}

/* Note card styles */
.nota-item {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
  border: 1px solid #e5e7eb;
  min-height: 120px;
  display: flex;
  flex-direction: column;
}

.nota-item:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.nota-card-content {
  padding: 16px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.nota-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.nota-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f3f4f6;
  border-radius: 8px;
}

.note-type-icon {
  color: #6b7280;
  width: 20px;
  height: 20px;
}

.nota-body {
  flex: 1;
  margin-bottom: 12px;
}

.nota-type {
  color: #6b7280;
  font-weight: 500;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.5px;
}

.nota-title {
  color: #111827;
  line-height: 1.4;
  word-break: break-word;
}

.nota-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-top: auto;
}

/* Drag and drop styles */
.nota-item.dragging {
  opacity: 0.5;
  transform: rotate(5deg);
}

.nota-item.drag-over {
  border-color: #10b981 !important;
  background-color: #f0fdf4;
}

.drag-btn {
  transition: all 0.2s ease;
  border-radius: 6px;
}

.drag-btn:hover {
  background-color: rgba(107, 114, 128, 0.1);
}

/* Mobile responsive design */
@media (max-width: 768px) {
  .notes-container {
    padding: 12px 8px;
  }
  
  .notes-title {
    font-size: 1.75rem;
  }
  
  .notes-filters {
    padding: 16px;
    margin-bottom: 16px;
  }
  
  .filters-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .notes-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 12px;
  }
  
  .nota-card-content {
    padding: 14px;
  }
  
  .nota-title {
    font-size: 1.1rem;
  }
}

@media (max-width: 480px) {
  .notes-container {
    padding: 8px 4px;
  }
  
  .notes-title {
    font-size: 1.5rem;
  }
  
  .notes-grid {
    grid-template-columns: 1fr;
    gap: 8px;
  }
  
  .nota-card-content {
    padding: 12px;
  }
  
  .nota-header {
    margin-bottom: 8px;
  }
  
  .nota-body {
    margin-bottom: 8px;
  }
}

@media (max-width: 360px) {
  .notes-container {
    padding: 8px;
  }
  
  .filters-grid {
    gap: 8px;
  }
  
  .nota-card-content {
    padding: 10px;
  }
}

/* Larger screens */
@media (min-width: 640px) {
  .notes-container {
    padding: 24px 20px;
  }
  
  .notes-filters {
    padding: 24px;
  }
}

@media (min-width: 1024px) {
  .notes-container {
    padding: 32px;
  }
  
  .notes-grid {
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
  }
  
  .filters-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }
}

@media (min-width: 1280px) {
  .notes-grid {
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 24px;
  }
}

/* Dark mode support */
.body--dark .notes-page {
  background-color: #121212;
}

.body--dark .notes-filters {
  background-color: #1f2937;
  border: 1px solid #374151;
}

.body--dark .nota-item {
  background-color: #1f2937;
  border-color: #374151;
}

.body--dark .nota-item:hover {
  background-color: #374151;
}

.body--dark .notes-title {
  color: #f9fafb;
}

.body--dark .nota-title {
  color: #f9fafb;
}

.body--dark .nota-icon {
  background-color: #374151;
}

.body--dark .note-type-icon {
  color: #d1d5db;
}

/* Touch improvements */
@media (hover: none) and (pointer: coarse) {
  .nota-item {
    min-height: 44px;
    touch-action: manipulation;
  }
  
  .drag-btn {
    min-width: 44px;
    min-height: 44px;
  }
  
  .nota-item:active {
    transform: scale(0.98);
  }
}

/* Loading and empty states */
.notes-grid:empty::after {
  content: "No notes found";
  grid-column: 1 / -1;
  text-align: center;
  padding: 60px 20px;
  color: #6b7280;
  font-size: 1.125rem;
}

/* Accessibility improvements */
.nota-item:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

.drag-btn:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Animation for new notes */
.nota-item {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Print styles */
@media print {
  .notes-filters,
  .drag-btn {
    display: none;
  }
  
  .notes-grid {
    grid-template-columns: 1fr;
    gap: 8px;
  }
  
  .nota-item {
    break-inside: avoid;
    box-shadow: none;
    border: 1px solid #e5e7eb;
  }
}
</style>