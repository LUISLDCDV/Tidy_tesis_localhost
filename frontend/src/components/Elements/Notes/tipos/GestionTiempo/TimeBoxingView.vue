<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row justify-center">
      <div class="col-12">
        <!-- Lista de tareas -->
        <q-card flat bordered class="q-mb-lg">
          <q-card-section>
            <div class="text-h4 text-weight-bold text-negative q-mb-md">LISTA DE TAREAS</div>
            <q-separator class="q-mb-md" />
            <div class="text-h6 text-center q-mb-md">TO DO</div>
            
            <q-list separator>
              <q-item
                v-for="(task, index) in tasks"
                :key="index"
                class="q-pa-sm"
              >
                <q-item-section side>
                  <q-checkbox
                    v-model="task.checked"
                    @update:model-value="saveChanges"
                    color="negative"
                  />
                </q-item-section>
                <q-item-section>
                  <q-item-label
                    :class="{ 'text-strike text-grey': task.checked, 'text-negative': !task.checked }"
                  >
                    {{ task.name }}
                  </q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>

        <!-- Tabla de horarios -->
        <q-card flat bordered class="q-mb-lg">
          <q-card-section class="q-pa-none">
            <q-markup-table flat bordered>
              <thead>
                <tr class="bg-grey-3">
                  <th v-for="day in daysOfWeek" :key="day" class="text-left">
                    {{ day }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(slot, slotIndex) in timeSlots" :key="slotIndex">
                  <td v-for="(event, dayIndex) in slot.events" :key="dayIndex" class="q-pa-xs">
                    <q-chip
                      :color="event.activity ? 'primary' : 'grey-4'"
                      :text-color="event.activity ? 'white' : 'grey-7'"
                      :label="event.activity || slot.time"
                      class="full-width"
                      clickable
                      @click="clearEvent(slotIndex, dayIndex)"
                    />
                  </td>
                </tr>
              </tbody>
            </q-markup-table>
          </q-card-section>
        </q-card>

        <!-- Controles -->
        <div class="q-mb-lg">
          <q-btn
            @click="showEventCreator = true"
            color="positive"
            label="Agregar Evento"
            icon="add"
          />
        </div>

      </div>
    </div>

    <!-- Modal de Creación de Evento -->
    <q-dialog v-model="showEventCreator" persistent>
      <q-card style="min-width: 400px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Nuevo Evento</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="showEventCreator = false" />
        </q-card-section>

        <q-card-section>
          <q-form class="q-gutter-md">
            <q-input
              v-model="newEvent.name"
              label="Nombre del Evento"
              outlined
              dense
            />

            <div>
              <div class="text-subtitle2 q-mb-sm">Días</div>
              <div class="q-gutter-sm">
                <q-checkbox
                  v-for="day in daysOfWeek"
                  :key="day"
                  v-model="newEvent.selectedDays"
                  :val="day"
                  :label="day"
                  color="positive"
                />
              </div>
            </div>

            <q-select
              v-model="newEvent.selectedSlot"
              :options="timeSlotOptions"
              label="Horario"
              outlined
              dense
              emit-value
              map-options
            />

            <q-input
              v-model.number="newEvent.duration"
              type="number"
              label="Duración (horas)"
              min="1"
              outlined
              dense
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="q-gutter-sm">
          <q-btn
            @click="showEventCreator = false"
            label="Cancelar"
            color="grey"
            outline
          />
          <q-btn
            @click="addNewEvent"
            label="Guardar"
            color="positive"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'TimeBoxingView',
  props: {
    content: {
      type: String,
      default: '{}'
    }
  },
  data() {
    return {
      tasks: [],
      daysOfWeek: ['Focus', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
      timeSlots: [],
      showSlotEditor: false,
      showEventCreator: false,
      newEvent: {
        name: '',
        selectedDays: [],
        selectedSlot: 0,
        duration: 1
      }
    };
  },
  computed: {
    timeSlotOptions() {
      return this.timeSlots.map((slot, index) => ({
        label: slot.time,
        value: index
      }));
    }
  },
  methods: {
    eventClass(activity) {
      return {
        'bg-red-200': activity === 'Cita',
        'bg-red-300': activity === 'Reunión',
        'bg-red-400': activity === 'Proyecto',
        'bg-red-500': activity === 'Llamada',
        'bg-red-100': !activity
      };
    },
    addTimeSlot() {
      this.timeSlots.push({
        time: `${this.timeSlots.length + 9}-${this.timeSlots.length + 10}`,
        events: Array(this.daysOfWeek.length).fill().map(() => ({ activity: '' }))
      });
    },
    removeTimeSlot(index) {
      this.timeSlots.splice(index, 1);
    },
    addNewEvent() {
      const duration = parseInt(this.newEvent.duration) || 1;
      const startSlot = parseInt(this.newEvent.selectedSlot);
      
      this.newEvent.selectedDays.forEach(day => {
        const dayIndex = this.daysOfWeek.indexOf(day);
        if (dayIndex === -1) return;

        for (let i = startSlot; i < startSlot + duration; i++) {
          if (i < this.timeSlots.length) {
            this.timeSlots[i].events[dayIndex] = {
              activity: this.newEvent.name,
              colspan: 1
            };
          }
        }
      });

      this.saveChanges();
      this.showEventCreator = false;
      this.resetNewEvent();
    },
    clearEvent(slotIndex, eventIndex) {
      this.timeSlots[slotIndex].events[eventIndex].activity = '';
      this.saveChanges();
    },
    resetNewEvent() {
      this.newEvent = {
        name: '',
        selectedDays: [],
        selectedSlot: 0,
        duration: 1
      };
    },
    saveChanges() {
      const newData = {
        tasks: this.tasks,
        timeSlots: this.timeSlots
      };
      this.$emit('update:content', JSON.stringify(newData));
    }
  },
  watch: {
    content: {
      immediate: true,
      handler(newContent) {
        try {
          const parsed = newContent ? JSON.parse(newContent) : {};
          this.tasks = parsed.tasks || [];
          this.timeSlots = parsed.timeSlots || [
            { time: '9-10', events: Array(6).fill().map(() => ({ activity: '' })) },
            { time: '10-11', events: Array(6).fill().map(() => ({ activity: '' })) },
            { time: '11-12', events: Array(6).fill().map(() => ({ activity: '' })) },
            { time: '12-13', events: Array(6).fill().map(() => ({ activity: '' })) }
          ];
        } catch (e) {
          console.error('Error parsing content:', e);
          this.tasks = [];
          this.timeSlots = [];
        }
      }
    }
  }
};
</script>

<style scoped>
/* Estilos específicos si son necesarios */
</style>