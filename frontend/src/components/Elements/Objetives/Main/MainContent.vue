<template>
  <q-page class="objectives-page">
    <!-- Header -->
    <div class="objectives-header q-mb-lg">
      <div class="row justify-center items-center q-col-gutter-md">
        <h1 class="objectives-title text-weight-bold text-grey-9">{{ $t('nav.objectives') }}</h1>
      </div>
    </div>

    <!-- Objectives List -->
    <div class="objectives-content">
      <!-- Empty State -->
      <q-card 
        v-if="!objetivos || !objetivos.length" 
        class="text-center q-py-xl empty-state-card"
        flat
        bordered
      >
        <q-card-section>
          <q-icon 
            name="checklist" 
            size="64px" 
            color="primary" 
            class="q-mb-md"
          />
          <div class="text-h6 text-grey-7 q-mb-md">{{ $t('objectives.noObjectives') }}</div>
          <q-btn
            @click="addObjective"
            color="primary"
            icon="add"
            :label="$t('objectives.createFirst')"
            unelevated
          />
        </q-card-section>
      </q-card>

      <!-- Objectives Grid -->
      <div 
        v-else 
        class="objectives-grid"
      >
        <div 
          v-for="(objective, index) in objetivos" 
          :key="index"
          class="objective-card-container"
        >
          <q-card 
            class="objective-card full-height"
            bordered
            flat
          >
            <!-- Objective Header -->
            <q-card-section class="q-pb-none">
              <div class="row items-start justify-between q-mb-md">
                <!-- Objective Type -->
                <q-chip
                  :color="getTypeColor(objective.tipo)"
                  text-color="white"
                  class="text-weight-medium"
                  size="sm"
                >
                  <span class="q-mr-xs" v-html="getIconPath(objective.tipo)"></span>
                  {{ objectiveTypes[objective.tipo] }}
                </q-chip>

                <!-- Actions -->
                <div class="row q-gutter-xs">
                  <q-btn 
                    @click.stop="editObjective(objective)"
                    flat
                    round
                    dense
                    size="sm"
                    icon="edit"
                    color="grey-6"
                    class="action-btn"
                  />
                  <q-btn
                    @click.stop="deleteObjective(objective.elemento_id)"
                    flat
                    round
                    dense
                    size="sm"
                    icon="delete"
                    color="negative"
                    class="action-btn"
                  />
                </div>
              </div>

              <div class="text-h6 text-weight-medium text-grey-9 q-mb-md">
                {{ objective.nombre }}
              </div>

              <!-- Progress Bar -->
              <div class="q-mb-md">
                <q-linear-progress 
                  :value="calculateCompletion(objective) / 100"
                  color="primary"
                  size="8px"
                  rounded
                  class="q-mb-xs"
                />
                <div class="text-caption text-grey-6">
                  {{ calculateCompletion(objective) }}% {{ $t('objectives.completed') }}
                </div>
              </div>
            </q-card-section>

            <!-- Objective Details -->
            <q-card-section>
              <div class="row items-center justify-between q-mb-md">
                <div class="row items-center text-caption text-grey-6">
                  <q-icon name="event" size="16px" class="q-mr-xs" />
                  {{ formatDate(objective.fechaVencimiento) }}
                </div>
                <div class="row items-center text-caption text-grey-6">
                  <q-icon name="checklist" size="16px" class="q-mr-xs" />
                  {{ objective.metas?.length || 0 }} {{ $t('objectives.steps') }}
                </div>
              </div>

              <div 
                v-if="objective.informacion" 
                class="text-body2 text-grey-7 q-mb-md objective-description"
              >
                {{ objective.informacion }}
              </div>

              <q-btn
                @click="navigateToSteps(objective)"
                color="grey-4"
                text-color="grey-8"
                class="full-width"
                outline
                :label="$t('objectives.viewDetails')"
                icon-right="arrow_forward"
                no-caps
              />
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <q-dialog 
      v-model="showModal" 
      persistent 
      :maximized="$q.screen.lt.sm"
      transition-show="slide-up"
      transition-hide="slide-down"
    >
      <q-card class="objective-modal-card" :class="{ 'mobile-modal': $q.screen.lt.sm }">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6 text-weight-medium">
            {{ editingIndex !== null ? $t('objectives.editObjective') : $t('objectives.newObjective') }}
          </div>
          <q-space />
          <q-btn icon="close" flat round dense @click="closeModal" />
        </q-card-section>

        <q-form @submit.prevent="saveObjective" class="q-pa-md">
          <!-- Name -->
          <q-input
            v-model="newObjective.nombre" 
            :label="$t('objectives.objectiveName')"
            :placeholder="$t('objectives.objectiveNamePlaceholder')"
            filled
            required
            class="q-mb-md"
            :rules="[val => val && val.length > 0 || $t('objectives.nameRequired')]"
          />

          <!-- Date -->
          <q-input
            v-model="newObjective.fechaVencimiento" 
            :label="$t('objectives.dueDate')"
            type="date"
            filled
            required
            class="q-mb-md"
            :rules="[val => val && val.length > 0 || $t('objectives.dateRequired')]"
          />

          <!-- Description -->
          <q-input
            v-model="newObjective.informacion" 
            :label="$t('objectives.description')"
            :placeholder="$t('objectives.descriptionPlaceholder')"
            type="textarea"
            rows="3"
            filled
            class="q-mb-md"
          />

          <!-- Type -->
          <div class="q-mb-md">
            <div class="text-body2 text-weight-medium q-mb-sm">{{ $t('objectives.objectiveType') }}</div>
            <div class="row q-col-gutter-sm">
              <div 
                v-for="(name, value) in objectiveTypes" 
                :key="value"
                class="col-6 col-sm-4"
              >
                <q-btn 
                  :label="name"
                  :color="newObjective.tipo === value ? 'primary' : 'grey-4'"
                  :text-color="newObjective.tipo === value ? 'white' : 'grey-8'"
                  :outline="newObjective.tipo !== value"
                  class="full-width type-btn"
                  no-caps
                  @click="newObjective.tipo = value"
                >
                  <span class="q-mr-xs" v-html="getIconPath(value)"></span>
                </q-btn>
              </div>
            </div>
          </div>

          <!-- Buttons -->
          <q-card-actions align="right" class="q-pt-lg">
            <q-btn 
              flat
              :label="$t('common.cancel')"
              @click="closeModal" 
              color="grey-8"
            />
            <q-btn 
              type="submit"
              unelevated
              :label="editingIndex !== null ? $t('common.update') : $t('common.create')"
              color="primary"
              :icon="editingIndex !== null ? 'edit' : 'add'"
            />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>

    <!-- Floating Plus Button (hidden when modal is open) -->
    <PlusModal v-if="!showModal" @click="addObjective"/>
  </q-page>
</template>

<script>
import { useConfirm } from '@/services/useConfirm';
import { useObjectivesStore } from '@/stores/objectives';
import { storeToRefs } from 'pinia';
import PlusModal from '@/components/Elements/PlusModal.vue';

export default {
  components: {
    PlusModal
  },
  setup() {
    const objectivesStore = useObjectivesStore();
    const { allObjectives: objetivos } = storeToRefs(objectivesStore);
    
    return {
      objectivesStore,
      objetivos
    };
  },
  data() {
    return {
      showModal: false,
      editingIndex: null,
      newObjective: {
        id: null,
        nombre: '',
        fechaVencimiento: '',
        informacion: '',
        tipo: '',
        metas: []
      },
    };
  },
  computed: {
    objectiveTypes() {
      return {
        '1': this.$t('objectives.types.continuousImprovement'),
        '2': this.$t('objectives.types.acquisition'),
        '3': this.$t('objectives.types.elimination'),
        '4': this.$t('objectives.types.maintenance'),
        '5': this.$t('objectives.types.creation'),
        '6': this.$t('objectives.types.exploration'),
        '7': this.$t('objectives.types.achievement'),
        '8': this.$t('objectives.types.organization'),
        '9': this.$t('objectives.types.relationships'),
        '10': this.$t('objectives.types.selfRealization')
      };
    },
    availabilityText() {
      const limitInfo = this.objectivesStore.objectivesLimitInfo;
      if (limitInfo.isPremium) {
        return 'Ilimitado';
      }
      return `${limitInfo.current}/${limitInfo.max} espacios disponibles`;
    }
  },
  methods: {
    formatDate(dateString) {
      console.log('formatDate input:', dateString, typeof dateString);
      if (!dateString || dateString === 'null' || dateString === '') {
        return new Date().toLocaleDateString('es-ES', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        }); // Mostrar fecha actual formateada
      }

      try {
        const date = new Date(dateString);
        console.log('Date object:', date, 'isValid:', !isNaN(date.getTime()));

        if (isNaN(date.getTime())) {
          return 'Invalid date';
        }

        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        const formatted = date.toLocaleDateString('en-US', options);
        console.log('Formatted date:', formatted);
        return formatted;
      } catch (error) {
        console.error('Error formatting date:', error);
        return 'Error formatting date';
      }
    },
    addObjective() {
      this.editingIndex = null;
      this.newObjective = {
        id: null,
        nombre: '',
        fechaVencimiento: '',
        informacion: '',
        tipo: '',
        metas: []
      };
      this.showModal = true;
    },
    async deleteObjective(id) {
      console.log('🎯 [DELETE DEBUG] Inicio de deleteObjective:', {
        id,
        type: typeof id,
        objectives: this.objectivesStore.objectives.length
      });

      // Usar Quasar dialog en lugar de useConfirm que no está implementado
      const confirmed = await new Promise((resolve) => {
        this.$q.dialog({
          title: 'Confirmar eliminación',
          message: this.$t('objectives.confirmDelete'),
          cancel: true,
          persistent: true
        }).onOk(() => {
          console.log('🎯 [DELETE DEBUG] Usuario confirmó eliminación');
          resolve(true);
        }).onCancel(() => {
          console.log('🎯 [DELETE DEBUG] Usuario canceló eliminación');
          resolve(false);
        });
      });

      console.log('🎯 [DELETE DEBUG] Confirmación del usuario:', confirmed);

      if (!confirmed) {
        console.log('🎯 [DELETE DEBUG] Usuario canceló eliminación');
        return;
      }

      try {
        console.log('🎯 Eliminando objetivo optimísticamente...');

        // Eliminación optimista - el store se encarga de todo
        await this.objectivesStore.deleteObjective(id);

        // Notificación inmediata de éxito
        this.$q.notify({
          type: 'positive',
          message: this.$t('objectives.deleteSuccess'),
          position: 'top'
        });

        console.log('✅ Objetivo eliminado de la vista inmediatamente');

      } catch (error) {
        console.error('❌ Error al eliminar objetivo:', error);

        this.$q.notify({
          type: 'negative',
          message: this.$t('objectives.deleteError'),
          position: 'top'
        });
      }
    },
    handleDeleteError(event) {
      console.log('🔄 Rollback - Error al eliminar objetivo en servidor');

      this.$q.notify({
        type: 'negative',
        message: 'Error: ' + event.detail.message,
        position: 'top'
      });
    },
    editObjective(objective) {
      console.log('Editando objetivo:', objective);
      this.newObjective = {
        id: objective.id,
        nombre: objective.nombre || '',
        informacion: objective.informacion || '',
        tipo: objective.tipo || '1',
        fechaVencimiento: objective.fechaVencimiento ? objective.fechaVencimiento.split('T')[0] : '',
        prioridad: objective.configuracion?.prioridad || 'media',
        descripcion_detallada: objective.informacion || '',
        meta_numerica: objective.configuracion?.meta_numerica || null,
        unidad_medida: objective.configuracion?.unidad_medida || null,
        recordatorios: objective.configuracion?.recordatorios || false,
        frecuencia_recordatorio: objective.configuracion?.frecuencia_recordatorio || null,
        recompensa: objective.configuracion?.recompensa || null
      };
      this.editingIndex = objective.id;
      this.showModal = true;
      console.log('Datos del formulario:', this.newObjective);
    },
    async saveObjective() {
      try {
        if (this.editingIndex !== null && this.editingIndex !== undefined) {
          console.log('Actualizando objetivo existente con ID:', this.editingIndex);
          await this.objectivesStore.updateObjective(this.newObjective);
        } else {
          console.log('Creando nuevo objetivo');
          await this.objectivesStore.createObjective(this.newObjective);
        }
        this.closeModal();
        this.$q.notify({
          type: 'positive',
          message: this.editingIndex !== null && this.editingIndex !== undefined ?
            this.$t('objectives.updateSuccess') :
            this.$t('objectives.createSuccess'),
          position: 'top'
        });
      } catch (error) {
        console.error('Error al guardar objetivo:', error);
        this.$q.notify({
          type: 'negative',
          message: this.$t('objectives.saveError'),
          position: 'top'
        });
      }
    },
    closeModal() {
      this.showModal = false;
      this.editingIndex = null;
      this.newObjective = {
        nombre: '',
        informacion: '',
        tipo: '1',
        fechaVencimiento: '',
        prioridad: 'media',
        descripcion_detallada: '',
        meta_numerica: null,
        unidad_medida: null,
        recordatorios: false,
        frecuencia_recordatorio: null,
        recompensa: null
      };
    },
    calculateCompletion(objective) {
      if (!objective.metas || !objective.metas.length) return 0;

      const completed = objective.metas.filter(meta => meta.status === 'completada').length;
      const total = objective.metas.length;

      if (total === 0) return 0;

      return Math.round((completed / total) * 100);
    },
    getTypeColor(tipo) {
      const colors = {
        '1': 'blue',
        '2': 'green', 
        '3': 'red',
        '4': 'orange',
        '5': 'purple',
        '6': 'teal',
        '7': 'deep-orange',
        '8': 'blue-grey',
        '9': 'pink',
        '10': 'indigo'
      };
      return colors[tipo] || 'primary';
    },
    navigateToSteps(objective) {
      console.log('=== DEBUG OBJECTIVE DATA ===');
      console.log('Objective completo:', objective);
      console.log('elemento_id:', objective.elemento_id);
      console.log('id:', objective.id);

      // Usar el ID principal del objetivo
      const objectiveId = objective.elemento_id || objective.id;

      console.log('Navigating to Steps with ID:', objectiveId);

      // Navegar solo con el ID
      this.$router.push({
        name: 'StepsHome',
        params: {
          id: objectiveId
        }
      });
    },
    getIconPath(tipo) {
      const paths = {
        "1":`<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4" /><path d="M19 22v-6" /><path d="M22 19l-3 -3l-3 3" /></svg>`,
        "2":`<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h3" /><path d="M21 15h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5" /><path d="M19 21v1m0 -8v1" /></svg>`,
        "3":`<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h3.5" /><path d="M19 19m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M17 21l4 -4" /></svg>`,
        "4":`<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h.5" /><path d="M18 22l3.35 -3.284a2.143 2.143 0 0 0 .005 -3.071a2.242 2.242 0 0 0 -3.129 -.006l-.224 .22l-.223 -.22a2.242 2.242 0 0 0 -3.128 -.006a2.143 2.143 0 0 0 -.006 3.071l3.355 3.296z" /></svg>`,
        "5":`<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h1m8 -9v1m8 8h1m-15.4 -6.4l.7 .7m12.1 -.7l-.7 .7" /><path d="M9 16a5 5 0 1 1 6 0a3.5 3.5 0 0 0 -1 3a2 2 0 0 1 -4 0a3.5 3.5 0 0 0 -1 -3" /><path d="M9.7 17l4.6 0" /></svg>`,
        "6": `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h.5" /><path d="M18 22l3.35 -3.284a2.143 2.143 0 0 0 .005 -3.071a2.242 2.242 0 0 0 -3.129 -.006l-.224 .22l-.223 -.20a2.242 2.242 0 0 0 -3.128 -.006a2.143 2.143 0 0 0 -.006 3.071l3.355 3.296z" /></svg>`,
        "7":`<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11v-3c0 -.53 -.211 -1.039 -.586 -1.414c-.375 -.375 -.884 -.586 -1.414 -.586h-6m0 0l3 3m-3 -3l3 -3" /><path d="M3 13.013v3c0 .53 .211 1.039 .586 1.414c.375 .375 .884 .586 1.414 .586h6m0 0l-3 -3m3 3l-3 3" /></svg>`,
        "8": `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4c.348 0 .686 .045 1.008 .128" /><path d="M19 16v3" /><path d="M19 22v.01" /></svg>`,
        "9": `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" /><path d="M12 6l-3.293 3.293a1 1 0 0 0 0 1.414l.543 .543c.69 .69 1.81 .69 2.5 0l1 -1a3.182 3.182 0 0 1 4.5 0l2.25 2.25" /></svg>`,
        "10": `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11.5 21h-2.926a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304h11.339a2 2 0 0 1 1.977 2.304c-.057 .368 -.1 .644 -.127 .828" /><path d="M9 11v-5a3 3 0 0 1 6 0v5" /></svg>`
      };
      return paths[tipo] || '';
    },
    async waitForAuth() {
      // Esperar hasta que haya un token de autenticación disponible
      let attempts = 0;
      const maxAttempts = 20; // 2 segundos máximo

      while (attempts < maxAttempts) {
        const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
        if (token) {
          console.log('Token de autenticación encontrado, procediendo con fetch');
          return;
        }

        // Esperar 100ms antes del próximo intento
        await new Promise(resolve => setTimeout(resolve, 100));
        attempts++;
      }

      console.warn('No se pudo encontrar token de autenticación después de esperar');
    }
  },
  async created() {
    // Esperar a que el token de autenticación esté disponible antes de hacer fetch
    await this.waitForAuth();

    this.objectivesStore.fetchObjectives();

    // Listener para errores de rollback en eliminación
    window.addEventListener('objective-delete-failed', this.handleDeleteError);

    // Debug para verificar los datos de objetivos
    this.$nextTick(() => {
      console.log('DEBUG - Objetivos cargados:', this.objetivos);
      if (this.objetivos && this.objetivos.length > 0) {
        console.log('DEBUG - Primer objetivo fechaVencimiento:', this.objetivos[0].fechaVencimiento);
      }
    });

    // Verificar si se debe abrir el modal automáticamente desde PlusModal
    if (this.$route.query.modo === 'crear') {
      this.$nextTick(() => {
        this.addObjective();
        console.log('Abriendo modal de objetivo por query desde PlusModal');
      });
    }
  },
  beforeUnmount() {
    // Limpiar event listener para evitar memory leaks
    window.removeEventListener('objective-delete-failed', this.handleDeleteError);
  }
};
</script>

<style scoped>
.objectives-page {
  padding: 20px;
}

/* Header responsive */
.objectives-header {
  padding: 0 4px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.objectives-title {
  font-size: 2rem;
  line-height: 1.2;
}

.availability-chip {
  font-weight: 500;
  margin-left: 8px;
}

.body--dark .text-grey-6,
.body--dark .text-grey-7,
.body--dark .text-grey-8,
.body--dark .text-grey-9 {
  color: #e5e7eb !important;
}

.new-objective-btn {
  transition: all 0.2s ease;
}

.objectives-content {
  margin-top: 20px;
}

/* Grid de objetivos */
.objectives-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}

.objective-card-container {
  width: 100%;
}

.empty-state-card {
  background: #f8fafb;
  transition: all 0.2s ease;
}

.empty-state-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.objective-card {
  background: #f8fafb;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  min-height: 200px;
}

.objective-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 12px -2px rgba(0, 0, 0, 0.1), 0 4px 8px -2px rgba(0, 0, 0, 0.06);
}

.action-btn {
  opacity: 0.7;
  transition: opacity 0.2s ease;
}

.objective-card:hover .action-btn {
  opacity: 1;
}

.objective-description {
  line-height: 1.4;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

/* Modal styles */
.objective-modal-card {
  background: white;
  box-shadow: 0 6px 16px -4px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  min-width: 400px;
  max-width: 600px;
}

.mobile-modal {
  min-width: 100vw !important;
  max-width: 100vw !important;
  min-height: 100vh !important;
  border-radius: 0 !important;
}

.type-btn {
  min-height: 36px;
  font-size: 0.75rem;
  transition: all 0.2s ease;
}

.type-btn:hover {
  transform: translateY(-1px);
}

/* Mobile responsive optimizations */
@media (max-width: 768px) {
  .objectives-page {
    padding: 12px 16px;
  }

  .objectives-header {
    margin-bottom: 16px !important;
  }

  .objectives-title {
    font-size: 1.5rem;
  }

  .availability-chip {
    margin-left: 0;
    margin-top: 8px;
    font-size: 0.7rem;
  }

  .objectives-header .row {
    flex-direction: column;
    text-align: center;
  }

  .new-objective-btn {
    min-width: 48px;
    min-height: 48px;
  }

  /* Grid responsive */
  .objectives-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .objective-card {
    min-height: 180px;
  }

  .objective-card .q-card-section {
    padding: 12px 16px !important;
  }

  .objective-card .text-h6 {
    font-size: 1.1rem;
    line-height: 1.3;
  }

  .objective-card .action-btn {
    opacity: 1;
    min-width: 40px;
    min-height: 40px;
  }

  /* Empty state responsive */
  .empty-state-card .q-card-section {
    padding: 24px 16px !important;
  }

  .empty-state-card .q-icon {
    font-size: 48px !important;
  }

  /* Modal responsive */
  .objective-modal-card {
    min-width: 280px !important;
    max-width: 95vw !important;
    margin: 8px;
  }

  .objective-modal-card .q-card-section {
    padding: 12px 16px !important;
  }

  .objective-modal-card .text-h6 {
    font-size: 1.1rem;
  }

  /* Form fields */
  .objective-modal-card .q-input {
    margin-bottom: 12px !important;
  }

  .objective-modal-card .q-input .q-field__control {
    min-height: 48px;
  }

  /* Type buttons responsive */
  .type-btn {
    min-height: 40px;
    font-size: 0.8rem;
  }

  /* Actions responsive */
  .objective-modal-card .q-card-actions {
    padding: 12px 16px 16px 16px !important;
    flex-direction: column;
    align-items: stretch;
  }

  .objective-modal-card .q-card-actions .q-btn {
    width: 100%;
    min-height: 44px;
    margin: 4px 0;
  }
}

/* Mobile small screens */
@media (max-width: 480px) {
  .objectives-page {
    padding: 8px 12px;
  }

  .objectives-title {
    font-size: 1.3rem;
  }

  .new-objective-btn {
    min-width: 44px;
    min-height: 44px;
  }

  .objectives-grid {
    gap: 8px;
  }

  .objective-card {
    min-height: 160px;
  }

  .objective-card .q-card-section {
    padding: 10px 12px !important;
  }

  .objective-card .text-h6 {
    font-size: 1rem;
  }

  .objective-card .action-btn {
    min-width: 36px;
    min-height: 36px;
  }

  /* Chips más compactos */
  .objective-card .q-chip {
    font-size: 0.7rem;
    height: 24px;
  }

  /* Progress bar más delgado */
  .objective-card .q-linear-progress {
    height: 6px;
  }

  /* Modal ultra compacto */
  .objective-modal-card {
    min-width: 260px !important;
    max-width: 98vw !important;
    margin: 4px;
  }

  .objective-modal-card .q-card-section {
    padding: 8px 12px !important;
  }

  .objective-modal-card .text-h6 {
    font-size: 1rem;
  }

  /* Form fields más compactos */
  .objective-modal-card .q-input .q-field__control {
    min-height: 44px;
  }

  .objective-modal-card .q-input .q-field__label {
    font-size: 0.85rem;
  }

  /* Textarea más pequeño */
  .objective-modal-card .q-input[type="textarea"] .q-field__control {
    min-height: 80px;
  }

  /* Type buttons grid */
  .objective-modal-card .row.q-col-gutter-sm .col-6 {
    flex: 0 0 50%;
    max-width: 50%;
  }

  .type-btn {
    min-height: 36px;
    font-size: 0.7rem;
    padding: 4px 8px;
  }

  /* Actions simplificadas */
  .objective-modal-card .q-card-actions {
    padding: 8px 12px 12px 12px !important;
  }

  .objective-modal-card .q-card-actions .q-btn {
    min-height: 40px;
    font-size: 0.85rem;
  }
}

/* Landscape mobile */
@media (max-width: 896px) and (orientation: landscape) {
  .objective-modal-card {
    max-height: 90vh;
    overflow-y: auto;
  }

  .objective-modal-card .q-form {
    max-height: 70vh;
    overflow-y: auto;
  }
}

/* Touch improvements */
@media (pointer: coarse) {
  .objective-card {
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
  }

  .objective-card:active {
    transform: scale(0.98);
    transition: transform 0.1s ease;
  }

  .new-objective-btn:active {
    transform: scale(0.95);
  }

  .action-btn:active {
    transform: scale(0.9);
  }

  /* Eliminar hover effects en dispositivos táctiles */
  .objective-card:hover {
    transform: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .empty-state-card:hover {
    transform: none;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  .type-btn:hover {
    transform: none;
  }

  /* Área de toque más grande para inputs */
  .objective-modal-card .q-input .q-field__control {
    min-height: 48px;
  }

  .objective-modal-card .q-btn {
    min-height: 44px;
  }
}

/* Fullscreen modal for very small screens */
@media (max-width: 360px) {
  .objective-modal-card {
    min-width: 100vw !important;
    max-width: 100vw !important;
    min-height: 100vh !important;
    margin: 0 !important;
    border-radius: 0 !important;
  }

  .objective-modal-card .q-card-section:first-child {
    padding: 16px 16px 0 16px !important;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  }

  .objective-modal-card .q-form {
    padding: 16px !important;
    flex: 1;
    overflow-y: auto;
  }

  .objective-modal-card .q-card-actions {
    padding: 16px !important;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
  }
}

/* Dark mode support */
.body--dark .objectives-page {
  color: white;
}

.body--dark .empty-state-card,
.body--dark .objective-card {
  background: #374151;
  color: white;
}

.body--dark .empty-state-card:hover,
.body--dark .objective-card:hover {
  background: #4b5563;
}

.body--dark .objective-modal-card {
  background: #1f2937;
  color: white;
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
  .objective-card,
  .empty-state-card,
  .new-objective-btn,
  .action-btn,
  .type-btn {
    transition: none;
  }
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .objective-card,
  .empty-state-card {
    border-width: 2px;
  }

  .new-objective-btn,
  .action-btn {
    border: 2px solid currentColor;
  }
}
</style>