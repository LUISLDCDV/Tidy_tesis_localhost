<template>
  <div>
    <q-card 
      class="calendar-carousel-container" 
      flat
      bordered
      @click="showModal = true"
      clickable
    >
      <q-card-section class="q-pa-md">
        <div v-if="calendarios.length" class="carousel-container">
          <!-- Flechas de navegación (aparecen arriba en mobile) -->
          <div class="nav-buttons-container">
            <q-btn
              @click.stop="prevCalendar"
              :disable="currentIndex === 0"
              round
              flat
              color="grey-8"
              icon="chevron_left"
              size="sm"
              class="nav-button nav-button-prev"
            />

            <q-btn
              @click.stop="nextCalendar"
              :disable="currentIndex === calendarios.length - 1"
              round
              flat
              color="grey-8"
              icon="chevron_right"
              size="sm"
              class="nav-button nav-button-next"
            />
          </div>

          <!-- Tarjeta del calendario -->
          <q-card class="calendar-display" flat bordered>
            <q-card-section class="text-center q-pa-md calendar-content">
              <div class="row items-center justify-center q-gutter-xs q-mb-xs">
                <div
                  class="calendar-color-indicator"
                  :style="{ backgroundColor: currentCalendar?.color || '#4caf50' }"
                ></div>
                <span class="text-body1 text-weight-medium ">
                  {{ currentCalendar?.nombre || 'Sin nombre' }}
                </span>
              </div>
              <div v-if="currentCalendar?.descripcion" class="text-caption text-grey-6 ellipsis">
                {{ currentCalendar.descripcion }}
              </div>
            </q-card-section>
          </q-card>
        </div>
        
        <div v-else class="text-center q-py-md">
          <q-icon name="event" size="48px" color="grey-4" class="q-mb-sm" />
          <div class="text-body2 text-grey-7">No hay calendarios</div>
          <div class="text-caption text-grey-5">Haz clic para crear uno</div>
        </div>
      </q-card-section>
    </q-card>

    <CalendaryFormModa
      v-model="showModal"
      :calendar="calendarForModal"
      @save="handleSave"
      @delete="handleDelete"
    />
  </div>
</template>

<script>
import CalendaryFormModa from './CalendaryFormModa.vue'

export default {
  components: {
    CalendaryFormModa
  },
  emits: ['select', 'refresh', 'save-calendar', 'delete-calendar'],
  props: {
    calendarios: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      currentIndex: 0,
      showModal: false,
      isCreatingNew: false
    }
  },
  computed: {
    currentCalendar() {
      return this.calendarios[this.currentIndex] || null
    },
    calendarForModal() {
      // Si estamos creando uno nuevo, pasar null para forzar modo crear
      return this.isCreatingNew ? null : this.currentCalendar
    }
  },
  watch: {
    calendarios: {
      immediate: true,
      handler(newCalendarios) {
        if (!newCalendarios || newCalendarios.length === 0) {
          this.currentIndex = 0
        } else if (this.currentIndex >= newCalendarios.length) {
          this.currentIndex = Math.max(0, newCalendarios.length - 1)
        }
      }
    },
    showModal(newValue) {
      // Si el modal se cierra, restablecer el flag de creación
      if (!newValue) {
        this.isCreatingNew = false
      }
    }
  },
  methods: {
    prevCalendar(e) {
      e.stopPropagation()
      if (this.calendarios && this.currentIndex > 0) {
        this.currentIndex--
        this.$emit('select', this.currentCalendar)
      }
    },
    nextCalendar(e) {
      e.stopPropagation()
      if (this.calendarios && this.currentIndex < this.calendarios.length - 1) {
        this.currentIndex++
        this.$emit('select', this.currentCalendar)
      }
    },
    handleSave(calendarData) {
      this.$emit('save-calendar', calendarData)
      this.showModal = false
      this.isCreatingNew = false // Restablecer el flag
    },
    handleDelete(calendar) {
      this.$emit('delete-calendar', calendar)
      this.showModal = false
      if (this.currentIndex >= this.calendarios.length - 1) {
        this.currentIndex = Math.max(0, this.calendarios.length - 2)
      }
    }
  }
}
</script>

<style scoped>
.calendar-carousel-container {
  background: #f9fafb;
  transition: all 0.2s ease;
  cursor: pointer;
}

.calendar-carousel-container:hover {
  background: #f3f4f6;
}

.nav-button {
  transition: all 0.2s ease;
  align-self: center;
  flex-shrink: 0;
}

.nav-button:hover {
  transform: scale(1.1);
}

.calendar-display {
  background: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
}

.calendar-content {
  min-width: 200px;
}

/* Layout por defecto: horizontal (desktop) */
.carousel-container {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
}

.nav-buttons-container {
  display: flex;
  gap: 8px;
}

.calendar-display {
  flex: 1;
  max-width: 300px;
}

.calendar-color-indicator {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  flex-shrink: 0;
}

/* Dark mode support */
.body--dark .calendar-carousel-container {
  background: #374151;
}

.body--dark .calendar-carousel-container:hover {
  background: #4b5563;
}

.body--dark .calendar-display {
  background: #1f2937;
  color: white;
}

/* Mobile responsive optimizations */
@media (max-width: 768px) {
  .calendar-carousel-container {
    margin: 0;
  }

  /* Layout vertical: flechas arriba, calendario abajo */
  .carousel-container {
    flex-direction: column;
    gap: 12px;
  }

  .nav-buttons-container {
    order: 1;
    justify-content: center;
    gap: 24px;
  }

  .calendar-display {
    order: 2;
    width: 100%;
    max-width: none;
    min-width: 160px !important;
  }

  .nav-button {
    min-width: 44px;
    min-height: 44px;
    padding: 8px;
  }

  /* Botones de navegación más grandes para touch */
  .nav-button .q-btn__content {
    font-size: 18px;
  }

  /* Contenido más compacto en mobile */
  .calendar-content {
    min-width: 160px !important;
    padding: 16px 12px !important;
  }

  .text-body1 {
    font-size: 0.9rem;
    line-height: 1.2;
  }

  .text-caption {
    font-size: 0.75rem;
    line-height: 1.1;
  }

  .calendar-color-indicator {
    width: 10px;
    height: 10px;
  }

  /* Estado vacío más compacto */
  .text-center.q-py-md {
    padding: 16px 8px !important;
  }

  .text-center.q-py-md .q-icon {
    font-size: 36px !important;
  }
}

/* Mobile small screens */
@media (max-width: 480px) {
  .calendar-carousel-container .q-card-section {
    padding: 8px 12px !important;
  }

  .row.q-gutter-md {
    gap: 8px;
  }

  .calendar-display {
    min-width: 140px !important;
  }

  .calendar-content {
    min-width: 140px !important;
    padding: 12px 8px !important;
  }

  .nav-button {
    min-width: 40px;
    min-height: 40px;
    padding: 6px;
  }

  .nav-buttons-container {
    gap: 20px;
  }

  .text-body1 {
    font-size: 0.8rem;
  }

  .text-caption {
    font-size: 0.7rem;
    max-width: 90px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  /* Ocultar descripción en pantallas muy pequeñas */
  .calendar-display .text-caption.ellipsis {
    display: none;
  }
}

/* Landscape mobile */
@media (max-width: 896px) and (orientation: landscape) {
  .calendar-carousel-container {
    margin: 0;
  }

  .calendar-display .q-card-section {
    padding: 8px 12px !important;
  }

  .text-center.q-py-md {
    padding: 12px 8px !important;
  }
}

/* Touch improvements */
@media (pointer: coarse) {
  .calendar-carousel-container {
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
  }

  .nav-button {
    /* Área de toque más grande */
    margin: 4px;
  }

  /* Eliminar hover effects en dispositivos táctiles */
  .calendar-carousel-container:hover {
    background: inherit;
    transform: none;
  }

  .nav-button:hover {
    transform: none;
  }

  /* Añadir feedback táctil */
  .calendar-carousel-container:active {
    transform: scale(0.98);
    transition: transform 0.1s ease;
  }

  .nav-button:active {
    background-color: rgba(0, 0, 0, 0.1);
  }
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
  .calendar-carousel-container,
  .nav-button,
  .calendar-display {
    transition: none;
  }
}
</style>