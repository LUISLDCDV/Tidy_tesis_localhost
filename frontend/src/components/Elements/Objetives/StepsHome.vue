<template>
  <q-page class="q-pa-md" style="padding-top: 100px;">
    <BackButton />

    <!-- Loading State -->
    <div v-if="loading" class="row justify-center q-my-xl">
      <q-spinner color="primary" size="3em" />
    </div>

    <!-- Header -->
    <template v-else>
    <div class="q-mb-lg">
      <div class="row justify-between items-center q-mb-md">
        <div class="row items-center q-gutter-md">
          <!-- progreso -->
          <q-card flat bordered class="q-pa-md">
            <div class="text-caption text-grey-6">{{ $t('objectives.progress') }}</div>
            <div class="text-h4 text-primary text-weight-bold">{{ completionPercentage }}%</div>
          </q-card>
          <!-- proxima fecha -->
          <q-card v-if="nextDueDate" flat bordered class="q-pa-md">
            <div class="text-caption text-grey-6">{{ $t('objectives.nextDate') }}</div>
            <div class="text-body1 text-weight-medium" :class="isOverdue ? 'text-negative' : 'text-orange-6'">
              {{ formatDate(nextDueDate) }}
            </div>
            <div v-if="isOverdue" class="text-caption text-negative q-mt-xs">
              <q-icon name="warning" size="xs" class="q-mr-xs" />
              {{ $t('objectives.overdue') }}
            </div>
          </q-card>
        </div>

        <div class="row q-gutter-sm">
          <q-btn
            @click="nextStep"
            :disable="currentStep >= steps.length - 1 || completionPercentage === 100"
            color="primary"
            :label="$t('objectives.nextStep')"
            icon-right="chevron_right"
            unelevated
          />

          <!-- Botones separados para agregar pasos -->
          <q-btn
            @click="openAddNextStepModal"
            color="primary"
            icon="add_circle"
            :label="$t('objectives.addNextStep')"
            :size="isOverdue ? 'sm' : 'md'"
            outline
            no-caps
          />

          <q-btn
            @click="openAddFinalStepModal"
            color="orange"
            icon="playlist_add"
            :label="$t('objectives.addFinalStep')"
            :size="isOverdue ? 'sm' : 'md'"
            outline
            no-caps
          />
        </div>
      </div>

      <q-card flat bordered class="q-pa-lg">
        <h1 class="text-h4 text-weight-bold q-mb-sm">{{ objective.nombre || 'Cargando...' }}</h1>
        
        <div v-if="completionPercentage === 100" class="text-center q-mt-md">
          <q-icon
            :name="steps.length === 1 ? 'rocket_launch' : 'check_circle'"
            size="48px"
            :color="steps.length === 1 ? 'primary' : 'positive'"
            class="q-mb-md"
          />
          <h2 class="text-h5 text-weight-medium q-mb-sm">{{ congratulationMessage }}</h2>
          <p class="text-grey-6">{{ congratulationSubMessage }}</p>
        </div>
      </q-card>
    </div>

    <!-- Timeline de pasos -->
    <q-card flat bordered class="q-pa-lg">
      <div ref="stepsContainer" class="row q-col-gutter-md">
        <div
          v-for="(step, index) in steps"
          :key="index"
          class="col-12 col-sm-6 col-md-4 col-lg-3 step-item"
        >
          <q-card 
            class="step-card cursor-pointer"
            @click="editStep(index)"
            :class="step.status === 'completada' ? 'completed' : 'pending'"
            bordered
            flat
          >
            <!-- Fecha -->
            <q-chip 
              v-if="step.fechaVencimiento"
              :label="formatDate(step.fechaVencimiento)"
              color="orange"
              text-color="white"
              size="sm"
              class="absolute-top-right q-ma-xs"
            />

            <q-card-section class="text-center q-pa-md">
              <!-- Círculo indicador -->
              <q-avatar 
                :color="step.status === 'completada' ? 'positive' : 'grey-4'"
                :text-color="step.status === 'completada' ? 'white' : 'grey-8'"
                size="48px"
                class="q-mb-md"
              >
                <q-icon 
                  v-if="step.status === 'completada'"
                  name="check" 
                  size="24px"
                />
                <span v-else class="text-body2 text-weight-bold">{{ index + 1 }}</span>
              </q-avatar>

              <!-- Contenido del paso -->
              <div class="text-subtitle2 text-weight-medium q-mb-xs">
                {{ $t('objectives.step') }} {{ index + 1 }}
              </div>
              <div class="text-body2 text-grey-8">{{ step.nombre }}</div>
              
              <!-- Tipo de paso -->
              <q-chip 
                v-if="step.tipo"
                :label="$t(`objectives.stepTypes.${step.tipo}`)"
                size="sm"
                color="primary"
                outline
                class="q-mt-sm"
              />
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-card>

    <!-- Modal para agregar/editar paso -->
    <q-dialog v-model="mostrarAddStepModal" persistent :no-backdrop-dismiss="savingStep">
      <q-card style="min-width: 400px; max-width: 500px;">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">
            {{ getModalTitle() }}
          </div>
          <q-space />
          <q-btn 
            icon="close" 
            flat 
            round 
            dense 
            @click="closeModals" 
            :disable="savingStep"
          />
        </q-card-section>

        <q-form @submit.prevent="addStep" class="q-pa-md" ref="stepForm">
          <!-- Nombre -->
          <q-input
            v-model="newMeta.nombre"
            :label="$t('objectives.stepName')"
            :placeholder="$t('objectives.stepNamePlaceholder')"
            filled
            required
            class="q-mb-md"
          />

          <!-- Fecha -->
          <q-input
            v-model="newMeta.fechaVencimiento"
            :label="$t('objectives.dueDate')"
            type="date"
            filled
            class="q-mb-md"
          />

          <!-- Descripción -->
          <q-input
            v-model="newMeta.informacion"
            :label="$t('objectives.description')"
            type="textarea"
            rows="3"
            filled
            class="q-mb-md"
          />

          <!-- Tipo -->
          <q-select
            v-model="newMeta.tipo"
            :label="$t('objectives.stepType')"
            :options="tipoOptions"
            option-value="value"
            option-label="label"
            emit-value
            map-options
            filled
            required
            class="q-mb-md"
          />

          <!-- Estado -->
          <q-select
            v-model="newMeta.status"
            :label="$t('objectives.status')"
            :options="statusOptions"
            option-value="value"
            option-label="label"
            emit-value
            map-options
            filled
            required
            class="q-mb-md"
          />

          <!-- Botones -->
          <q-card-actions align="between">
            <q-btn
              v-if="editingIndex !== null"
              @click="deleteStep(null)"
              color="negative"
              :label="$t('common.delete')"
              :loading="deleting"
              :disable="deleting"
              flat
            />
            <q-space v-else />
            <div class="q-gutter-sm">
              <q-btn 
                :label="$t('common.cancel')"
                @click="closeModals"
                flat
                :disable="savingStep"
              />
              <q-btn 
                type="submit"
                :label="editingIndex !== null ? $t('common.update') : $t('common.create')"
                color="primary"
                unelevated
                :loading="savingStep"
                :disable="savingStep"
              />
            </div>
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>
    </template>

  </q-page>
</template>

<script>
import { useObjectivesStore } from '@/stores/objectives';
import BackButton from '../../Nav/BackButton.vue';
import api from '@/services/api';
import { queueRequest } from '@/utils/requestQueue';

export default {
  name: 'StepsHome',
  components: {
    BackButton,
  },
  props: {
    id: {
      type: [Number, String],
      required: true,
    },
  },
  data() {
    return {
      mostrarAddStepModal: false,
      nuevoPaso: "",
      currentStep: 0,
      stepInsertType: 'next', // 'next' o 'final'
      steps: [],
      objective: {
        nombre: '',
        informacion: '',
        fechaVencimiento: null
      },
      loading: true,
      deleting: false,
      newMeta: {
        id: null,
        nombre: '',
        fechaVencimiento: '',
        informacion: '',
        tipo: 'preparacion',
        status: 'pendiente'
      },
      tiposMeta: {
        'preparacion': 'Preparation',
        'accion': 'Action',
        'seguimiento': 'Follow-up',
        'verificacion': 'Verification',
        'cierre': 'Closure'
      },
      tiposStatus: {
        'pendiente': 'Pending',
        'en_progreso': 'In Progress',
        'completada': 'Completed'
      },
      editingIndex: null,
      stepsContainerWidth: '100%',
      stepItemWidth: 180,
      nextDueDate: null,
      savingStep: false
    };
  },
  setup() {
    const objectivesStore = useObjectivesStore();
    
    return {
      objectivesStore
    };
  },
  
  computed: {
    objetivoActual() {
      return this.objectivesStore.currentObjective;
    },
    completionPercentage() {
      const completed = this.steps.filter(step => step.status === 'completada').length;
      // Si solo hay un paso, mostrar 0% inicialmente
      if (this.steps.length === 1 && completed === 0) {
        return 0;
      }
      return this.steps.length ? Math.round((completed / this.steps.length) * 100) : 0;
    },
    isOverdue() {
      if (!this.nextDueDate) return false;
      const now = new Date();
      now.setHours(0, 0, 0, 0); // Resetear horas para comparar solo fechas
      const dueDate = new Date(this.nextDueDate);
      dueDate.setHours(0, 0, 0, 0);
      return dueDate < now;
    },
    congratulationMessage() {
      // Si solo hay un paso completado, felicitar por empezar
      if (this.steps.length === 1 && this.completionPercentage === 100) {
        return this.$t('objectives.congratulationsStart');
      }
      // Si tiene múltiples pasos completados, felicitar por terminar
      return this.$t('objectives.congratulations');
    },
    congratulationSubMessage() {
      if (this.steps.length === 1 && this.completionPercentage === 100) {
        return this.$t('objectives.startedNewObjective');
      }
      return this.$t('objectives.completedAllSteps');
    },
    tipoOptions() {
      return Object.keys(this.tiposMeta).map(key => ({
        value: key,
        label: this.$t(`objectives.stepTypes.${key}`)
      }));
    },
    statusOptions() {
      return Object.keys(this.tiposStatus).map(key => ({
        value: key,
        label: this.$t(`objectives.statusTypes.${key}`)
      }));
    }
  },
  watch: {
    steps: {
      handler(newSteps) {
        this.calculateNextDueDate();
        this.$nextTick(() => {
          this.calculateStepsWidth();
        });
        
        // Marcar objetivo como completado si todas las metas están completadas
        if (this.completionPercentage === 100) {
          this.markGoalAsCompleted();
        }
      },
      deep: true
    },
    completionPercentage(newVal) {
      if (newVal === 100) {
        setTimeout(() => {
          const celebration = document.querySelector('.celebration');
          if (celebration) {
            celebration.style.display = 'block';
          }
        }, 500);
      }
    }
  },
  methods: {
    async fetchObjetivoActual(id) {
      return await this.objectivesStore.getObjectiveById(id);
    },
    async guardarMeta(metaData) {
      return await this.objectivesStore.createGoal(metaData);
    },
    async eliminarMeta(metaId, elementoId) {
      return await this.objectivesStore.deleteGoal(metaId, elementoId);
    },
    async actualizarObjetivo(objectiveData) {
      return await this.objectivesStore.updateObjective(objectiveData);
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const options = { year: 'numeric', month: 'short', day: 'numeric' };
      return new Date(dateString).toLocaleDateString('es-ES', options);
    },
    calculateNextDueDate() {
      const now = new Date();
      const pendingSteps = this.steps.filter(step => 
        step.status !== 'completada' && 
        step.fechaVencimiento
      );
      
      if (pendingSteps.length > 0) {
        // Ordenar por fecha más próxima
        pendingSteps.sort((a, b) => new Date(a.fechaVencimiento) - new Date(b.fechaVencimiento));
        this.nextDueDate = pendingSteps[0].fechaVencimiento;
      } else {
        this.nextDueDate = null;
      }
    },
    async markGoalAsCompleted() {
      try {
        const routeIde = this.$route.params.ide;
        const idToUse = routeIde || this.ide || this.id;
        console.log('🎯 Marcando objetivo como completado, ID:', idToUse);
        await this.objectivesStore.completeObjective(idToUse);
      } catch (error) {
        console.error('Error al marcar objetivo como completado:', error);
      }
    },
    calculateStepsWidth() {
      if (this.steps.length > 5) {
        this.stepsContainerWidth = `${this.steps.length * this.stepItemWidth}px`;
      } else {
        this.stepsContainerWidth = '100%';
      }
    },
    scrollToCurrentStep() {
      if (this.steps.length > 5) {
        this.$nextTick(() => {
          const container = this.$refs.stepsContainer;
          if (!container) {
            console.warn('stepsContainer ref no encontrado, saltando scroll');
            return;
          }
          const stepElements = container.querySelectorAll('.step-item');
          const stepElement = stepElements[this.currentStep];
          if (stepElement) {
            container.scrollTo({
              left: stepElement.offsetLeft - container.offsetWidth / 2 + stepElement.offsetWidth / 2,
              behavior: 'smooth'
            });
          }
        });
      }
    },
    async fetchObjetivo() {
      try {
        const routeId = this.$route.params.id;
        const routeIde = this.$route.params.ide;
        const idToUse = parseInt(routeIde || this.ide || routeId || this.id);
        console.log('Fetching objetivo con ID:', idToUse, 'de ruta:', { routeId, routeIde });

        // Obtener objetivo básico
        const objetivo = await this.fetchObjetivoActual(idToUse);
        this.objectivesStore.setCurrentObjective(objetivo);

        // Obtener metas específicas del objetivo usando el nuevo endpoint
        const metasResponse = await api.get(`/elementos/${idToUse}/metas`);
        const metas = metasResponse.data || [];

        console.log('Metas obtenidas del backend:', metas);

        this.steps = [{
          nombre: 'Inicio',
          status: 'completada',
          fechaVencimiento: '',
          informacion: '',
          tipo: 'preparacion'
        }, ...metas.map(meta => ({
          id: meta.id,
          elemento_id: meta.elemento_id,
          nombre: meta.nombre,
          status: meta.status || 'pendiente',
          fechaVencimiento: meta.fechaVencimiento || '',
          informacion: meta.informacion || '',
          tipo: meta.tipo || 'preparacion',
          objetivo_id: meta.objetivo_id
        }))];

        console.log('Steps finales:', this.steps);

        this.calculateNextDueDate();
        this.$nextTick(() => {
          this.calculateStepsWidth();
          this.scrollToCurrentStep();
        });
      } catch (error) {
        console.error('Error al obtener el objetivo:', error);
      }
    },
    async refreshMetas() {
      try {
        const timestamp = new Date().toISOString()
        const routeId = this.$route.params.id;
        const routeIde = this.$route.params.ide;
        const idToUse = parseInt(routeIde || this.ide || routeId || this.id);

        // Log detallado de quién llama a refreshMetas
        const stack = new Error().stack
        const caller = stack.split('\n')[2]?.trim() || 'Unknown caller'
        console.log(`🔄 [${timestamp}] StepsHome.refreshMetas() llamado`)
        console.log(`   📍 Llamado desde: ${caller}`)
        console.log(`   🆔 ID a usar: ${idToUse}`)

        // Validar que tenemos un ID válido
        if (!idToUse || isNaN(idToUse)) {
          console.error('❌ [refreshMetas] ID inválido:', idToUse);
          throw new Error('ID de objetivo inválido');
        }

        // Obtener metas actualizadas del objetivo usando cola de requests
        const metas = await queueRequest.metas(idToUse, async () => {
          console.log(`🔗 [${timestamp}] refreshMetas - Ejecutando petición API para elemento ${idToUse}`)
          const metasResponse = await api.get(`/elementos/${idToUse}/metas`);
          console.log(`✅ [${timestamp}] refreshMetas - Respuesta API recibida`);
          return metasResponse.data || [];
        });

        console.log('✅ Metas refrescadas (orden del backend):', metas);
        console.log('Detalle de cada meta:');
        metas.forEach((meta, index) => {
          console.log(`  ${index}: ${meta.nombre} | Status: ${meta.status} | ID: ${meta.id}`);
        });

        // Mantener el paso "Inicio" y actualizar el resto
        this.steps = [{
          nombre: 'Inicio',
          status: 'completada',
          fechaVencimiento: '',
          informacion: '',
          tipo: 'preparacion'
        }, ...metas.map(meta => ({
          id: meta.id,
          elemento_id: meta.elemento_id,
          nombre: meta.nombre,
          status: meta.status || 'pendiente',
          fechaVencimiento: meta.fechaVencimiento || '',
          informacion: meta.informacion || '',
          tipo: meta.tipo || 'preparacion',
          objetivo_id: meta.objetivo_id
        }))];

        console.log(`✅ [${timestamp}] Steps actualizados: ${this.steps.length} pasos`);

        this.calculateNextDueDate();
        this.$nextTick(() => {
          this.calculateStepsWidth();
          this.scrollToCurrentStep();
        });
      } catch (error) {
        console.error('❌ [refreshMetas] Error al refrescar metas:', error);
        console.error('   Tipo de error:', error.constructor.name);
        console.error('   Mensaje:', error.message);
        console.error('   Stack:', error.stack);

        // Re-throw para que el error se propague
        throw error;
      }
    },
    async nextStep() {
      if (this.currentStep < this.steps.length - 1) {
        const nextStepIndex = this.currentStep + 1;
        const nextStepToComplete = this.steps[nextStepIndex];

        console.log('🔄 Completando siguiente paso:', nextStepToComplete);

        try {
          // No modificar el array local directamente
          // En su lugar, hacer la actualización en el servidor y luego refrescar
          if (nextStepToComplete.id) {
            const elementoId = parseInt(this.id);
            const objetivoIdResponse = await api.get(`/elementos/${elementoId}/objetivo-id`);
            const realObjetivoId = objetivoIdResponse.data.objetivo_id;

            await this.objectivesStore.updateGoal({
              ...nextStepToComplete,
              id: nextStepToComplete.id,
              objetivo_id: realObjetivoId,
              status: 'completada'
            });

            // Actualizar el estado local inmediatamente
            this.currentStep = nextStepIndex;
            this.steps[nextStepIndex].status = 'completada';

            // NO refrescar aquí - el store ya refrescó el nivel
            // Solo hacer scroll
            this.scrollToCurrentStep();

            console.log('✅ Siguiente paso completado exitosamente');
          } else {
            console.warn('⚠️ Paso sin ID, no se puede actualizar');
          }
        } catch (error) {
          console.error('❌ Error al completar siguiente paso:', error);
          this.$q.notify({
            type: 'negative',
            message: 'Error al completar el paso',
            position: 'top'
          });
        }
      }
    },
    openAddStepModal() {
      this.resetForm();
      this.stepInsertType = 'final'; // Por defecto agregar al final
      this.mostrarAddStepModal = true;
    },

    openAddNextStepModal() {
      this.resetForm();
      this.stepInsertType = 'next';
      this.mostrarAddStepModal = true;
    },

    openAddFinalStepModal() {
      this.resetForm();
      this.stepInsertType = 'final';
      this.mostrarAddStepModal = true;
    },

    calculateStepPosition() {
      if (this.stepInsertType === 'next') {
        // Insertar después del paso actual (progreso)
        const completedSteps = this.steps.filter(step => step.status === 'completada').length;
        return completedSteps + 1; // Posición después del último completado
      } else {
        // Insertar al final
        return this.steps.length + 1; // Al final de todos los pasos
      }
    },

    getModalTitle() {
      if (this.editingIndex !== null) {
        return this.$t('objectives.editStep');
      } else if (this.stepInsertType === 'next') {
        return this.$t('objectives.addNextStep');
      } else {
        return this.$t('objectives.addFinalStep');
      }
    },
    editStep(index) {
      const stepToEdit = this.steps[index];
      console.log('Editando paso:', stepToEdit);
      this.newMeta = {
        id: stepToEdit.id,
        elemento_id: stepToEdit.elemento_id, // ✅ Agregar elemento_id
        objetivo_id: stepToEdit.objetivo_id, // ✅ Agregar objetivo_id
        nombre: stepToEdit.nombre || '',
        fechaVencimiento: stepToEdit.fechaVencimiento || '',
        informacion: stepToEdit.informacion || '',
        tipo: stepToEdit.tipo || 'preparacion',
        status: stepToEdit.status || 'pendiente'
      };
      this.editingIndex = index;
      this.mostrarAddStepModal = true;
      console.log('Datos del formulario de paso:', this.newMeta);
    },
    async deleteStep(index) {
      // Prevenir ejecuciones múltiples
      if (this.deleting) {
        console.log('deleteStep ya está en ejecución, ignorando...');
        return;
      }

      this.deleting = true;
      console.log('=== INICIANDO DELETESTEP ===');
      console.log('deleteStep llamado con index:', index);
      console.log('editingIndex actual:', this.editingIndex);
      console.log('steps disponibles:', this.steps);

      // Usar diálogo nativo de Quasar en lugar de useConfirm
      const confirmed = await new Promise((resolve) => {
        this.$q.dialog({
          title: 'Confirmar eliminación',
          message: this.$t('objectives.confirmDelete'),
          cancel: true,
          persistent: true
        }).onOk(() => {
          resolve(true);
        }).onCancel(() => {
          resolve(false);
        });
      });

      if (!confirmed) {
        console.log('Usuario canceló la eliminación');
        this.deleting = false;
        return;
      }

      try {
        // Si index es null, usar el paso que se está editando
        let stepToDelete;
        let stepIndex;

        if (index === null && this.editingIndex !== null) {
          stepToDelete = this.steps[this.editingIndex];
          stepIndex = this.editingIndex;
        } else if (index !== null && index >= 0 && index < this.steps.length) {
          stepToDelete = this.steps[index];
          stepIndex = index;
        } else {
          console.error('Índice inválido para eliminar paso:', index);
          this.$q.notify({
            type: 'negative',
            message: 'Índice de paso inválido',
            position: 'top'
          });
          return;
        }

        console.log('Step a eliminar:', stepToDelete);
        console.log('Índice del step:', stepIndex);

        // No permitir eliminar el paso "Inicio" (que no tiene ID)
        if (stepToDelete.nombre === 'Inicio') {
          this.$q.notify({
            type: 'warning',
            message: 'No se puede eliminar el paso inicial',
            position: 'top'
          });
          return;
        }

        // Verificar que tiene ID
        if (!stepToDelete.id) {
          console.warn('Step no tiene ID, no se puede eliminar del backend:', stepToDelete);
          this.$q.notify({
            type: 'warning',
            message: 'No se puede eliminar este paso (sin ID)',
            position: 'top'
          });
          return;
        }

        console.log('Eliminando meta con ID:', stepToDelete.id);

        // Eliminar la meta del backend, pasando también el elemento_id
        console.log('Datos completos del step a eliminar:', stepToDelete);
        const result = await this.eliminarMeta(stepToDelete.id, stepToDelete.elemento_id);
        console.log('Resultado eliminación:', result);

        // Refrescar las metas después de eliminar
        console.log('Refrescando metas...');
        await this.refreshMetas();

        console.log('Eliminación completada exitosamente');

        // Cerrar modales DESPUÉS de todo el procesamiento
        this.closeModals();

        // Mostrar mensaje de éxito
        this.$q.notify({
          type: 'positive',
          message: 'Paso eliminado exitosamente',
          position: 'top'
        });

      } catch (error) {
        console.error('Error al eliminar el paso:', error);
        console.error('Error completo:', error);
        this.$q.notify({
          type: 'negative',
          message: this.$t('objectives.deleteError') + ': ' + (error.message || 'Error desconocido'),
          position: 'top'
        });
      } finally {
        this.deleting = false;
      }
    },
    async addStep() {
      if (!this.newMeta.nombre.trim()) {
        this.$q.notify({
          type: 'warning',
          message: this.$t('objectives.stepNameRequired'),
          position: 'top'
        });
        return;
      }

      this.savingStep = true;
      try {
        // Obtener IDs directamente de la ruta
        const routeId = this.$route.params.id;
        const routeIde = this.$route.params.ide;

        const objectiveId = this.objetivoActual?.id || routeIde || this.ide || routeId || this.id;
        const elementoId = routeId || this.id;

        console.log('IDs disponibles:', {
          objetivoActualId: this.objetivoActual?.id,
          routeId: routeId,
          routeIde: routeIde,
          ideParam: this.ide,
          idParam: this.id,
          objectiveIdSeleccionado: objectiveId,
          elementoIdSeleccionado: elementoId
        });

        let response;

        if (this.editingIndex !== null) {
          // Editar - usar updateGoal con el ID de la meta
          // Necesitamos obtener el objetivo_id real desde el elemento_id
          const objetivoIdResponse = await api.get(`/elementos/${elementoId}/objetivo-id`);
          const realObjetivoId = objetivoIdResponse.data.objetivo_id;

          const stepData = {
            id: this.newMeta.id, // ID de la meta que se está editando
            objetivo_id: realObjetivoId, // ID del objetivo real al que pertenece
            ...this.newMeta
          };
          console.log('Datos para actualizar paso (con objetivo_id correcto):', stepData);
          response = await this.objectivesStore.updateGoal(stepData);
        } else {
          // Crear nuevo - usar createGoal
          const stepPosition = this.calculateStepPosition();
          const stepData = {
            elemento_id: elementoId, // ID del objetivo al que pertenece
            position: stepPosition, // Posición calculada según el tipo
            stepInsertType: this.stepInsertType, // Tipo de inserción (next/final)
            ...this.newMeta
          };
          console.log('Datos para crear paso:', stepData, 'Posición:', stepPosition, 'Tipo:', this.stepInsertType);
          response = await this.guardarMeta(stepData);
        }

        // Refrescar las metas después de crear o actualizar
        await this.refreshMetas();

        this.$q.notify({
          type: 'positive',
          message: this.editingIndex !== null ? 
            this.$t('objectives.stepUpdated') : 
            this.$t('objectives.stepCreated'),
          position: 'top'
        });

        // Cerrar modal solo si todo fue exitoso
        this.mostrarAddStepModal = false;
        this.resetForm();
      } catch (error) {
        console.error('Error al guardar paso:', error);
        this.$q.notify({
          type: 'negative',
          message: this.$t('objectives.stepSaveError') + ': ' + (error.message || 'Error desconocido'),
          position: 'top'
        });
        // No cerrar el modal si hay error para que el usuario pueda corregir
      } finally {
        this.savingStep = false;
      }
    },
    closeModals() {
      console.log('=== CERRANDO MODALES ===');
      console.log('savingStep:', this.savingStep);
      console.log('mostrarAddStepModal antes:', this.mostrarAddStepModal);
      console.log('editingIndex antes:', this.editingIndex);

      if (this.savingStep) {
        // No permitir cerrar mientras se está guardando
        this.$q.notify({
          type: 'info',
          message: this.$t('objectives.pleaseWait'),
          position: 'top'
        });
        return;
      }
      this.mostrarAddStepModal = false;
      this.resetForm();

      console.log('mostrarAddStepModal después:', this.mostrarAddStepModal);
      console.log('editingIndex después:', this.editingIndex);
    },
    resetForm() {
      this.newMeta = {
        id: null,
        nombre: '',
        fechaVencimiento: '',
        informacion: '',
        tipo: 'preparacion',
        status: 'pendiente'
      };
      this.editingIndex = null;
      this.savingStep = false;
      // Limpiar cualquier estado de validación
      this.$nextTick(() => {
        if (this.$refs.stepForm) {
          this.$refs.stepForm.resetValidation();
        }
      });
    },
    async updateStatus(step) {
      console.log('updateStatus llamado con step:', step);

      try {
        // Si el step tiene ID, es una actualización, sino es creación
        if (step.id) {
          // Actualizar meta existente
          // Necesitamos obtener el objetivo_id real desde el elemento_id
          const elementoId = parseInt(this.id);
          const objetivoIdResponse = await api.get(`/elementos/${elementoId}/objetivo-id`);
          const realObjetivoId = objetivoIdResponse.data.objetivo_id;

          await this.objectivesStore.updateGoal({
            id: step.id,
            objetivo_id: realObjetivoId,
            ...step
          });
        } else {
          // Crear nueva meta
          await this.guardarMeta({
            elemento_id: parseInt(this.id),
            ...step
          });
        }

        // Refrescar las metas después de la actualización
        await this.refreshMetas();
      } catch (err) {
        console.error("Error al actualizar/crear meta:", err);
        this.$q.notify({
          type: 'negative',
          message: "Hubo un error al crear la meta",
          position: 'top'
        });
      }
    },
    async loadObjectiveData() {
      console.log('🔄 [loadObjectiveData] Iniciando carga de datos del objetivo', this.id);
      try {
        this.loading = true;

        // Cargar datos del objetivo con timeout
        console.log('📡 [loadObjectiveData] Obteniendo datos del objetivo...');
        const objectiveData = await Promise.race([
          this.objectivesStore.getObjectiveById(this.id),
          new Promise((_, reject) =>
            setTimeout(() => reject(new Error('Timeout obteniendo objetivo')), 10000)
          )
        ]);

        if (objectiveData) {
          this.objective = {
            nombre: objectiveData.nombre || 'Sin nombre',
            informacion: objectiveData.informacion || '',
            fechaVencimiento: objectiveData.fechaVencimiento || null
          };
          console.log('✅ [loadObjectiveData] Datos del objetivo cargados:', this.objective.nombre);
        }

        // Cargar steps/metas del objetivo con timeout
        console.log('📡 [loadObjectiveData] Refrescando metas...');
        await Promise.race([
          this.refreshMetas(),
          new Promise((_, reject) =>
            setTimeout(() => reject(new Error('Timeout refrescando metas')), 12000)
          )
        ]);

        console.log('✅ [loadObjectiveData] Metas cargadas exitosamente');

      } catch (error) {
        console.error('❌ [loadObjectiveData] Error cargando datos del objetivo:', error);
        this.$q.notify({
          type: 'negative',
          message: error.message || 'Error cargando datos del objetivo',
          position: 'top'
        });

        // Redirigir a objetivos si no se puede cargar
        setTimeout(() => {
          this.$router.push('/Objectives');
        }, 2000);
      } finally {
        console.log('🏁 [loadObjectiveData] Finalizando carga, setting loading = false');
        this.loading = false;
      }
    }
  },
  async created() {
    // Debug de parámetros de ruta
    console.log('=== PARAMETROS DE RUTA EN STEPSHOME ===');
    console.log('$route.params:', this.$route.params);
    console.log('Props recibidas:', {
      id: this.id
    });
    console.log('================================');

    // Cargar datos del objetivo usando solo el ID
    await this.loadObjectiveData();
  }
};
</script>

<style scoped>
.step-card {
  transition: all 0.3s ease;
  position: relative;
}

.step-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.step-card.completed {
  border-left: 4px solid #21ba45;
}

.step-card.pending {
  border-left: 4px solid #f2c037;
}
</style>