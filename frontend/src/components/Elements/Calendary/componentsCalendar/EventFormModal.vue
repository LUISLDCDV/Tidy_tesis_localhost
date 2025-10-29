<template>
  <q-dialog 
    v-model="showModal" 
    persistent 
    :maximized="$q.screen.lt.sm"
    transition-show="slide-up"
    transition-hide="slide-down"
  >
    <q-card class="event-modal-card" :class="{ 'mobile-modal': $q.screen.lt.sm }">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6 text-weight-medium">
          {{ editing ? $t('calendar.editEvent') : $t('calendar.newEvent') }}
        </div>
        <q-space />
        <q-btn icon="close" flat round dense @click="closeModal" />
      </q-card-section>

      <q-card-section class="q-pt-none">
        <div class="q-gutter-md">
          <!-- Título -->
          <q-input
            v-model="currentEvent.nombre" 
            :label="$t('calendar.eventTitle') + ' *'"
            :placeholder="$t('calendar.eventTitlePlaceholder')"
            filled
            required
            maxlength="100"
            counter
            :rules="titleRules"
            :error="titleError"
            :error-message="titleErrorMessage"
          />

          <!-- Descripción -->
          <q-input
            v-model="currentEvent.informacion" 
            :label="$t('common.description')"
            :placeholder="$t('calendar.eventDescriptionPlaceholder')"
            type="textarea"
            filled
            rows="3"
            maxlength="500"
            counter
            :rules="descriptionRules"
          />

          <!-- Fecha y Hora -->
          <div class="row q-gutter-md">
            <q-input
              v-model="currentEvent.fechaVencimiento" 
              :label="$t('calendar.date') + ' *'"
              type="date"
              filled
              required
              class="col"
              :rules="dateRules"
              :error="dateError"
              :error-message="dateErrorMessage"
            />
            <q-input
              v-model="currentEvent.horaVencimiento" 
              :label="$t('calendar.time') + ' *'"
              type="time"
              filled
              required
              class="col"
              :rules="timeRules"
              :error="timeError"
              :error-message="timeErrorMessage"
            />
          </div>

          <!-- Calendario -->
          <q-select
            v-if="calendarios.length > 1"
            v-model="currentEvent.calendario_id" 
            :options="calendarOptions"
            :label="$t('calendar.calendar')"
            option-value="id"
            option-label="nombre"
            emit-value
            map-options
            filled
          />          

              <!-- GeoClima -->
              <GeoClima
                :fechaVencimiento="currentEvent.fechaVencimiento"
                :horaVencimiento="currentEvent.horaVencimiento"
                :initialLocation="locationData"
                @update:location="handleLocationUpdate"
                @update:clima="handleClimaUpdate"
              />

          <!-- Color -->
          <div>
            <div class="text-body2 text-weight-medium q-mb-sm">{{ $t('calendar.color') }}</div>
            <div class="row q-gutter-xs">
              <div 
                v-for="color in availableColors" 
                :key="color" 
                :style="{backgroundColor: color}" 
                class="color-picker-option"
                :class="{
                  'color-selected': currentEvent.color === color
                }"
                @click="currentEvent.color = color"
              ></div>
            </div>
          </div>
        </div>
      </q-card-section>

      <!-- Footer del modal -->
      <q-card-actions align="between">
        <q-btn 
          flat
          :label="$t('common.cancel')"
          @click="closeModal" 
          color="grey-8"
        />
        <div class="q-gutter-sm">
          <q-btn
            v-if="editing"
            flat
            :label="$t('common.delete')"
            @click="handleDelete"
            color="negative"
            icon="delete"
          />
          <q-btn 
            unelevated
            :label="editing ? $t('common.update') : $t('common.save')"
            @click="handleSave" 
            color="positive"
            :icon="editing ? 'edit' : 'save'"
            :disable="!isFormValid"
            :loading="false"
          />
        </div>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
  import GeoClima from "./GeoClima.vue";
export default {
  props: {
    selectedLocation: {
      type: Object,
      default: () => null
    },
    event: {
      type: Object,
      default: () => ({
        id: null,
        elemento_id: null,
        nombre: '',
        informacion: '',
        fechaVencimiento: '',
        horaVencimiento: '',
        calendario_id: null,
        color: '#4caf50',
        gps: null,
        clima: null,
      })
    },
    calendarios: {
      type: Array,
      default: () => []
    },
    editing: Boolean,
    availableColors: {
      type: Array,
      default: () => ['#4caf50'] // Valor por defecto
    },
  },
  components: {
    GeoClima
  },
  data() {
    return {
      defaultEvent: {
        id: null,
        nombre: '',
        informacion: '',
        fechaVencimiento: '',
        horaVencimiento: '',
        calendario_id: null,
        color: '#4caf50',
        gps: null,
        clima: null,
      },
      currentEvent: null,
      locationData: null,
      climaData: null,
      formErrors: {
        title: false,
        date: false,
        time: false
      }
    };
  },
  watch: {
    event: {
      handler(newVal) {
        this.currentEvent = { ...this.defaultEvent, ...newVal };
      },
      deep: true
    }
  },
  computed: {
    showModal: {
      get() {
        return true // El modal se maneja desde el padre
      },
      set(value) {
        if (!value) {
          this.closeModal()
        }
      }
    },
    calendarOptions() {
      return this.calendarios.map(cal => ({
        id: cal.id,
        nombre: cal.nombre
      }))
    },
    titleRules() {
      return [
        val => !!val || this.$t('validation.required'),
        val => (val && val.trim().length >= 2) || this.$t('validation.minLength', { min: 2 }),
        val => (val && val.trim().length <= 100) || this.$t('validation.maxLength', { max: 100 }),
        val => !/^\s+$/.test(val) || this.$t('validation.notOnlySpaces')
      ]
    },
    descriptionRules() {
      return [
        val => !val || val.length <= 500 || this.$t('validation.maxLength', { max: 500 })
      ]
    },
    dateRules() {
      return [
        val => !!val || this.$t('validation.required'),
        val => this.isValidDate(val) || this.$t('validation.invalidDate'),
        val => this.isNotInPast(val) || this.$t('validation.pastDate')
      ]
    },
    timeRules() {
      return [
        val => !!val || this.$t('validation.required'),
        val => this.isValidTime(val) || this.$t('validation.invalidTime'),
        val => this.isNotInPastTime(val) || this.$t('validation.pastTime')
      ]
    },
    titleError() {
      return this.formErrors.title
    },
    titleErrorMessage() {
      return this.formErrors.titleMessage || ''
    },
    dateError() {
      return this.formErrors.date
    },
    dateErrorMessage() {
      return this.formErrors.dateMessage || ''
    },
    timeError() {
      return this.formErrors.time
    },
    timeErrorMessage() {
      return this.formErrors.timeMessage || ''
    },
    isFormValid() {
      return this.currentEvent?.nombre?.trim() && 
             this.currentEvent?.fechaVencimiento &&
             this.currentEvent?.horaVencimiento &&
             this.isValidDate(this.currentEvent.fechaVencimiento) &&
             this.isValidTime(this.currentEvent.horaVencimiento) &&
             !this.isInPast(this.currentEvent.fechaVencimiento, this.currentEvent.horaVencimiento)
    }
  },
  methods: {
    closeModal() {
      this.currentEvent = { ...this.defaultEvent };
      this.resetFormErrors();
      this.$emit('close');
    },

    resetFormErrors() {
      this.formErrors = {
        title: false,
        date: false,
        time: false,
        titleMessage: '',
        dateMessage: '',
        timeMessage: ''
      };
    },

    isValidDate(date) {
      if (!date) return false;
      const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
      if (!dateRegex.test(date)) return false;
      
      const dateObj = new Date(date + 'T00:00:00');
      return !isNaN(dateObj.getTime());
    },

    isValidTime(time) {
      if (!time) return false;
      const timeRegex = /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/;
      return timeRegex.test(time);
    },

    isNotInPast(date) {
      if (!date) return true;
      const selectedDate = new Date(date + 'T00:00:00');
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      return selectedDate >= today;
    },

    isNotInPastTime(time) {
      if (!time || !this.currentEvent?.fechaVencimiento) return true;
      
      const today = new Date();
      const selectedDate = new Date(this.currentEvent.fechaVencimiento + 'T00:00:00');
      
      // Si es fecha futura, la hora es válida
      if (selectedDate > today) return true;
      
      // Si es hoy, verificar que la hora no esté en el pasado
      const todayDateOnly = new Date();
      todayDateOnly.setHours(0, 0, 0, 0);
      
      if (selectedDate.getTime() === todayDateOnly.getTime()) {
        const [hours, minutes] = time.split(':').map(Number);
        const selectedTime = hours * 60 + minutes;
        const currentTime = today.getHours() * 60 + today.getMinutes();
        return selectedTime > currentTime;
      }
      
      return true;
    },

    isInPast(date, time) {
      if (!date || !time) return false;
      
      const eventDateTime = new Date(date + 'T' + time + ':00');
      const now = new Date();
      
      return eventDateTime <= now;
    },

    validateForm() {
      this.resetFormErrors();
      let isValid = true;

      // Validar título
      if (!this.currentEvent.nombre?.trim()) {
        this.formErrors.title = true;
        this.formErrors.titleMessage = this.$t('validation.required');
        isValid = false;
      } else if (this.currentEvent.nombre.trim().length < 2) {
        this.formErrors.title = true;
        this.formErrors.titleMessage = this.$t('validation.minLength', { min: 2 });
        isValid = false;
      } else if (/^\s+$/.test(this.currentEvent.nombre)) {
        this.formErrors.title = true;
        this.formErrors.titleMessage = this.$t('validation.notOnlySpaces');
        isValid = false;
      }

      // Validar fecha
      if (!this.currentEvent.fechaVencimiento) {
        this.formErrors.date = true;
        this.formErrors.dateMessage = this.$t('validation.required');
        isValid = false;
      } else if (!this.isValidDate(this.currentEvent.fechaVencimiento)) {
        this.formErrors.date = true;
        this.formErrors.dateMessage = this.$t('validation.invalidDate');
        isValid = false;
      } else if (!this.isNotInPast(this.currentEvent.fechaVencimiento)) {
        this.formErrors.date = true;
        this.formErrors.dateMessage = this.$t('validation.pastDate');
        isValid = false;
      }

      // Validar hora
      if (!this.currentEvent.horaVencimiento) {
        this.formErrors.time = true;
        this.formErrors.timeMessage = this.$t('validation.required');
        isValid = false;
      } else if (!this.isValidTime(this.currentEvent.horaVencimiento)) {
        this.formErrors.time = true;
        this.formErrors.timeMessage = this.$t('validation.invalidTime');
        isValid = false;
      } else if (!this.isNotInPastTime(this.currentEvent.horaVencimiento)) {
        this.formErrors.time = true;
        this.formErrors.timeMessage = this.$t('validation.pastTime');
        isValid = false;
      }

      // Validar que la fecha y hora combinadas no estén en el pasado
      if (isValid && this.isInPast(this.currentEvent.fechaVencimiento, this.currentEvent.horaVencimiento)) {
        this.formErrors.time = true;
        this.formErrors.timeMessage = this.$t('validation.eventInPast');
        isValid = false;
      }

      return isValid;
    },

    handleLocationUpdate(location) {
      console.log('📍 EventFormModal - Ubicación actualizada:', location);
      this.locationData = location;
      this.currentEvent.gps = location;
    },

    handleClimaUpdate(clima) {
      console.log('🌤️ EventFormModal - Clima actualizado:', clima);
      console.log('🌤️ EventFormModal - Tipo de clima:', typeof clima);
      this.climaData = clima;
      this.currentEvent.clima = clima;
      console.log('🌤️ EventFormModal - climaData guardado:', this.climaData);
    },

    async handleDelete() {
      console.log('🗑️ EventFormModal - Iniciando eliminación de evento');
      console.log('🗑️ EventFormModal - Evento a eliminar:', this.currentEvent);
      console.log('🗑️ EventFormModal - ID del evento:', this.currentEvent.id);
      console.log('🗑️ EventFormModal - elemento_id:', this.currentEvent.elemento_id);

      // Mostrar diálogo de confirmación
      this.$q.dialog({
        title: this.$t('common.confirm'),
        message: `¿Estás seguro de que deseas eliminar el evento "${this.currentEvent.nombre}"?`,
        cancel: {
          label: this.$t('common.cancel'),
          color: 'grey-7',
          flat: true
        },
        ok: {
          label: this.$t('common.delete'),
          color: 'negative',
          unelevated: true
        },
        persistent: true
      }).onOk(() => {
        console.log('✅ EventFormModal - Usuario confirmó eliminación');
        console.log('🗑️ EventFormModal - Emitiendo evento delete con datos:', this.currentEvent);

        // Emitir evento de eliminación
        this.$emit('delete', this.currentEvent);

        // Mostrar notificación de eliminación
        this.$q.notify({
          type: 'info',
          message: this.$t('calendar.deleting'),
          position: 'top',
          timeout: 1000
        });

        this.closeModal();
      }).onCancel(() => {
        console.log('❌ EventFormModal - Usuario canceló eliminación');
      });
    },

    async handleSave() {
      // Ejecutar validación completa del formulario
      if (!this.validateForm()) {
        this.$q.notify({
          type: 'negative',
          message: this.$t('validation.formErrors'),
          position: 'top',
          timeout: 3000
        });
        return;
      }

      try {
        // Preparar datos del evento con sanitización
        console.log('💾 EventFormModal - Preparando guardado...');
        console.log('💾 EventFormModal - this.climaData:', this.climaData);
        console.log('💾 EventFormModal - this.locationData:', this.locationData);

        const eventoData = {
          id: this.currentEvent.id,
          elemento_id: this.currentEvent.elemento_id,
          nombre: this.currentEvent.nombre.trim(),
          informacion: this.currentEvent.informacion?.trim() || '',
          fechaVencimiento: this.currentEvent.fechaVencimiento,
          horaVencimiento: this.currentEvent.horaVencimiento + ":00",
          calendario_id: this.currentEvent.calendario_id || this.calendarios[0]?.id,
          // Enviar objetos directos, no JSON strings. Laravel los convertirá automáticamente
          gps: this.locationData || null,
          clima: this.climaData || null,
          color: this.currentEvent.color || '#4caf50'
        };

        console.log('💾 EventFormModal - Datos finales a enviar:', eventoData);

        // Mostrar feedback de guardado
        this.$q.notify({
          type: 'info',
          message: this.$t('calendar.saving'),
          position: 'top',
          timeout: 1000
        });

        console.log('Enviando datos del evento:', eventoData);
        this.$emit('save', eventoData);

        // Mostrar confirmación de éxito
        this.$q.notify({
          type: 'positive',
          message: this.editing ? this.$t('calendar.eventUpdated') : this.$t('calendar.eventCreated'),
          position: 'top',
          timeout: 2000
        });

        this.closeModal();
      } catch (error) {
        console.error('Error al guardar:', error);
        this.$q.notify({
          type: 'negative',
          message: this.$t('calendar.saveError') + ': ' + (error.message || error),
          position: 'top',
          timeout: 5000
        });
      }
    }
  },
  created() {
    this.currentEvent = { ...this.defaultEvent, ...this.event };

    // Inicializar datos de clima y ubicación si existen en el evento
    if (this.event.clima) {
      try {
        this.climaData = typeof this.event.clima === 'string'
          ? JSON.parse(this.event.clima)
          : this.event.clima;
        console.log('🌤️ EventFormModal - Clima inicializado desde evento existente:', this.climaData);
      } catch (error) {
        console.error('Error al parsear clima del evento:', error);
        this.climaData = null;
      }
    }

    if (this.event.gps) {
      try {
        this.locationData = typeof this.event.gps === 'string'
          ? JSON.parse(this.event.gps)
          : this.event.gps;
        console.log('📍 EventFormModal - Ubicación inicializada desde evento existente:', this.locationData);
      } catch (error) {
        console.error('Error al parsear GPS del evento:', error);
        this.locationData = null;
      }
    }
  }
};
</script>

<style scoped>
/* Asegurar que el modal de eventos esté por encima del botón + */
:deep(.q-dialog) {
  z-index: 10000 !important;
}

:deep(.q-dialog__backdrop) {
  z-index: 9999 !important;
}
.event-modal-card {
  background: white;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  min-width: 400px;
  max-width: 500px;
}

.mobile-modal {
  min-width: 100vw !important;
  max-width: 100vw !important;
  min-height: 100vh !important;
  border-radius: 0 !important;
}

.color-picker-option {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  cursor: pointer;
  border: 2px solid transparent;
  transition: all 0.2s ease;
}

.color-picker-option:hover {
  transform: scale(1.1);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.color-selected {
  border-color: #374151 !important;
  transform: scale(1.2);
  box-shadow: 0 0 0 2px rgba(55, 65, 81, 0.2);
}

/* Dark mode support */
.body--dark .event-modal-card {
  background: #1f2937;
  color: white;
}

.body--dark .color-selected {
  border-color: white !important;
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.2);
}

/* Mobile responsive optimizations */
@media (max-width: 768px) {
  .event-modal-card {
    min-width: 280px !important;
    max-width: 95vw !important;
    margin: 8px;
  }

  /* Header más compacto */
  .q-card-section.row.items-center.q-pb-none {
    padding: 12px 16px 0 16px !important;
  }

  .text-h6 {
    font-size: 1.1rem;
  }

  /* Contenido principal */
  .q-card-section.q-pt-none {
    padding: 12px 16px !important;
  }

  .q-gutter-md {
    gap: 12px;
  }

  /* Inputs más compactos */
  .q-input .q-field__control {
    min-height: 48px;
  }

  .q-input .q-field__label {
    font-size: 0.9rem;
  }

  /* Fecha y hora en mobile */
  .row.q-gutter-md .col {
    margin-bottom: 8px;
  }

  /* Selector de color */
  .color-picker-option {
    width: 32px;
    height: 32px;
    margin: 2px;
  }

  .color-picker-option:hover {
    transform: scale(1.05);
  }

  /* Footer del modal */
  .q-card-actions {
    padding: 12px 16px 16px 16px !important;
    flex-direction: column;
    align-items: stretch;
  }

  .q-card-actions > div {
    width: 100%;
    justify-content: space-between;
    margin-top: 8px;
  }

  /* Botones en mobile */
  .q-btn {
    min-height: 44px;
    padding: 8px 16px;
  }

  .q-btn .q-btn__content {
    font-size: 0.9rem;
  }
}

/* Mobile small screens */
@media (max-width: 480px) {
  .event-modal-card {
    min-width: 260px !important;
    max-width: 98vw !important;
    margin: 4px;
  }

  .q-card-section.row.items-center.q-pb-none {
    padding: 8px 12px 0 12px !important;
  }

  .q-card-section.q-pt-none {
    padding: 8px 12px !important;
  }

  .text-h6 {
    font-size: 1rem;
  }

  /* Inputs aún más compactos */
  .q-input .q-field__control {
    min-height: 44px;
  }

  .q-input .q-field__label {
    font-size: 0.85rem;
  }

  /* Textarea más pequeño */
  .q-input[type="textarea"] .q-field__control {
    min-height: 80px;
  }

  /* Fecha y hora stack vertical */
  .row.q-gutter-md {
    flex-direction: column;
    gap: 8px;
  }

  .row.q-gutter-md .col {
    flex: none;
    margin-bottom: 0;
  }

  /* Selector de color más compacto */
  .color-picker-option {
    width: 28px;
    height: 28px;
    margin: 1px;
  }

  /* Footer simplificado */
  .q-card-actions {
    padding: 8px 12px 12px 12px !important;
    gap: 8px;
  }

  .q-card-actions > .q-btn:first-child {
    order: 2;
    margin-top: 8px;
  }

  .q-card-actions > div {
    order: 1;
    display: flex;
    gap: 8px;
  }

  /* Botones stack en pantallas muy pequeñas */
  .q-card-actions > div {
    flex-direction: column;
  }

  .q-card-actions .q-btn {
    min-height: 40px;
    width: 100%;
  }
}

/* Landscape mobile */
@media (max-width: 896px) and (orientation: landscape) {
  .event-modal-card {
    max-height: 90vh;
    overflow-y: auto;
  }

  .q-card-section.q-pt-none {
    max-height: 60vh;
    overflow-y: auto;
  }

  /* Volver fecha y hora a layout horizontal en landscape */
  .row.q-gutter-md {
    flex-direction: row;
  }

  .row.q-gutter-md .col {
    flex: 1;
  }
}

/* Touch improvements */
@media (pointer: coarse) {
  /* Mejor feedback táctil */
  .color-picker-option {
    -webkit-tap-highlight-color: transparent;
  }

  .color-picker-option:active {
    transform: scale(0.95);
    transition: transform 0.1s ease;
  }

  .q-btn:active {
    transform: scale(0.98);
  }

  /* Eliminar hover effects en dispositivos táctiles */
  .color-picker-option:hover {
    transform: none;
    box-shadow: none;
  }

  /* Área de toque más grande para inputs */
  .q-input .q-field__control {
    min-height: 48px;
  }
}

/* Fullscreen modal for very small screens */
@media (max-width: 360px) {
  .event-modal-card {
    min-width: 100vw !important;
    max-width: 100vw !important;
    min-height: 100vh !important;
    margin: 0 !important;
    border-radius: 0 !important;
  }

  .q-card-section.row.items-center.q-pb-none {
    padding: 16px 16px 0 16px !important;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  }

  .q-card-section.q-pt-none {
    padding: 16px !important;
    flex: 1;
    overflow-y: auto;
  }

  .q-card-actions {
    padding: 16px !important;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
  }
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
  .color-picker-option,
  .q-btn {
    transition: none;
  }
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .color-picker-option {
    border: 2px solid currentColor;
  }

  .color-selected {
    border-width: 3px;
  }
}
</style>