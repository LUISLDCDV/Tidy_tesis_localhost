<!-- App.vue -->
<template >
  <q-page class="full-height">
    <div class="q-pa-lg" >
      <!-- Header -->
      <div class="row justify-center q-mb-lg calendar-header">
        <h1 class="text-h4 text-weight-bold" style="border-bottom: 1px solid #e5e7eb;   padding-bottom: 16px;"
        >{{ $t('calendar.title') }}</h1>
      </div>
      <q-card >
        <q-card-section class="q-pa-lg">
          <CalendarView
            ref="calendarView"
            class="calendar-container"
            :show-calendar-modal="showCalendarModal"
            @calendar-modal-opened="showCalendarModal = false"
          />
        </q-card-section>
      </q-card>
    </div>
    
    <!-- Floating Plus Button -->
    <PlusModal />
  </q-page>
</template>
  
<script>
import CalendarView from '@/components/Elements/Calendary/componentsCalendar/CalendarView.vue';
import PlusModal from '@/components/Elements/PlusModal.vue';

export default {
  name: 'CalendarList',
  components: {
    CalendarView,
    PlusModal
  },
  data() {
    return {
      showCalendarModal: false
    };
  },
  methods: {
    addCalendar() {
      // Abrir modal para crear nuevo calendario
      this.showCalendarModal = true;
    }
  },
  
  created() {
    // Verificar si se debe abrir el modal automáticamente desde PlusModal
    if (this.$route.query.modo === 'crear' && this.$route.query.openModal === 'true') {
      this.$nextTick(() => {
        this.showCalendarModal = true;
        console.log('Abriendo modal de calendario por query desde PlusModal');
      });
    }
  }
};
</script>

<style scoped>
.full-height {
  min-height: 100vh;
}

.calendar-header {
  padding-top: 24px;
}

.calendar-header h1 {
  text-align: center;
  width: 100%;
}


.calendar-container {
  /* background: white; */
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Mobile responsive */
@media (max-width: 768px) {
  .calendar-header {
    padding-top: 20px;
  }

}

/* Mobile small screens */
@media (max-width: 480px) {
  .calendar-header {
    padding-top: 16px;
  }
}

/* Estilos adicionales para animaciones de transición */
.transition-colors {
  transition-property: background-color, border-color, color, fill, stroke;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>

  