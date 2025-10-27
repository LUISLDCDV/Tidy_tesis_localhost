<template>
  <q-dialog 
    v-model="showModal" 
    persistent
    transition-show="scale"
    transition-hide="scale"
  >
    <q-card class="calendar-modal-card">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">
          <q-icon name="event" class="q-mr-sm" />
          {{ isEditing ? 'Editar Calendario' : 'Nuevo Calendario' }}
        </div>
        <q-space />
        <q-btn 
          icon="close" 
          flat 
          round 
          dense 
          v-close-popup 
        />
      </q-card-section>

      <q-card-section class="q-pt-none">
        <q-form @submit="handleSubmit" class="q-gutter-md">
          <q-input
            v-model="formData.nombre"
            :label="$t('common.name')"
            outlined
            required
            :rules="[val => !!val || 'El nombre es requerido']"
          />
          
          <q-input
            v-model="formData.descripcion"
            :label="$t('common.description')"
            type="textarea"
            outlined
            rows="3"
          />

          <div>
            <div class="text-body2 q-mb-sm">{{ $t('calendar.color') }}</div>
            <div class="row q-gutter-sm">
              <q-btn
                v-for="color in availableColors"
                :key="color"
                round
                size="md"
                :style="{ backgroundColor: color }"
                :outline="formData.color !== color"
                :class="{ 'ring-2 ring-primary': formData.color === color }"
                @click="formData.color = color"
                :icon="formData.color === color ? 'check' : ''"
              />
            </div>
          </div>
        </q-form>
      </q-card-section>

      <q-card-actions class="row justify-between q-pa-md">
        <div>
          <q-btn
            v-if="isEditing"
            @click="handleDelete"
            color="negative"
            icon="delete"
            label="Eliminar"
            outline
          />
        </div>
        <div class="row q-gutter-sm">
          <q-btn
            @click="$emit('update:modelValue', false)"
            color="grey"
            label="Cancelar"
            flat
          />
          <q-btn
            @click="handleSubmit"
            color="primary"
            :icon="isEditing ? 'save' : 'add'"
            :label="isEditing ? 'Guardar' : 'Crear Calendario'"
          />
        </div>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
export default {
  name: 'CalendaryFormModa',
  props: {
    modelValue: {
      type: Boolean,
      required: true
    },
    calendar: {
      type: [Object, null],
      default: null
    }
  },
  data() {
    return {
      formData: {
        nombre: '',
        descripcion: '',
        color: '#4caf50'
      },
      availableColors: ['#4caf50', '#2196f3', '#9c27b0', '#ff9800', '#f44336', '#607d8b', '#00bcd4', '#8bc34a', '#e91e63']
    }
  },
  computed: {
    isEditing() {
      const hasValidCalendar = this.calendar && 
                              typeof this.calendar === 'object' && 
                              this.calendar !== null &&
                              this.calendar.id;
      
      console.log('isEditing check:', {
        calendar: this.calendar,
        hasValidCalendar,
        calendarId: this.calendar?.id,
        calendarNombre: this.calendar?.nombre
      });
      
      return hasValidCalendar;
    },
    showModal: {
      get() {
        return this.modelValue
      },
      set(val) {
        this.$emit('update:modelValue', val)
      }
    }
  },
  watch: {
    calendar: {
      immediate: true,
      handler(newVal) {
        if (newVal && typeof newVal === 'object') {
          this.formData = {
            nombre: '',
            descripcion: '',
            color: '#4caf50',
            ...newVal
          }
        } else {
          // Reset form data when calendar is null
          this.formData = {
            nombre: '',
            descripcion: '',
            color: '#4caf50'
          }
        }
      }
    },
    showModal(newVal) {
      // Emitir evento global cuando el modal de calendario se abre/cierra
      if (newVal) {
        window.dispatchEvent(new CustomEvent('modal-opened', {
          detail: { source: 'calendar-modal', type: 'calendar' }
        }));
      } else {
        window.dispatchEvent(new CustomEvent('modal-closed', {
          detail: { source: 'calendar-modal', type: 'calendar' }
        }));
      }
    }
  },
  methods: {
    handleSubmit() {
      this.$emit('save', { ...this.formData })
      this.$emit('update:modelValue', false)
    },
    handleDelete() {
      if (this.calendar && confirm(this.$t('calendar.confirmDeleteCalendar'))) {
        this.$emit('delete', this.calendar)
        this.$emit('update:modelValue', false)
      }
    }
  }
}
</script>

<style scoped>
.calendar-modal-card {
  width: 100%;
  max-width: 500px;
  min-width: 400px;
}

@media (max-width: 480px) {
  .calendar-modal-card {
    min-width: 300px;
  }
}

.ring-2 {
  box-shadow: 0 0 0 2px var(--q-primary);
}

.ring-primary {
  border: 2px solid var(--q-primary);
}

/* Asegurar que el modal de calendario esté por encima del botón + */
:deep(.q-dialog) {
  z-index: 10000 !important;
}

:deep(.q-dialog__backdrop) {
  z-index: 9999 !important;
}
</style>
