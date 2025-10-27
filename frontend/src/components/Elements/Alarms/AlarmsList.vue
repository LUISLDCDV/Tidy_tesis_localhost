<template>
  <q-page class="row full-height">
    <div class="col q-pa-md">
      <!-- Header con título -->
      <div class="alarms-header q-mb-lg">
        <div class="row justify-center">
          <h1 class="alarms-title text-weight-bold">
            {{ $t('alarms.title') }}
          </h1>
        </div>
      </div>

      <!-- Lista de alarmas -->
      <div class="alarms-grid">
        <div 
          v-for="alarm in alarmas" 
          :key="alarm.id" 
          class="alarm-card-container"
        >
          <q-card 
            class="alarm-card cursor-pointer"
            @click="editAlarm(alarm)"
            bordered
            flat
          >
            <q-card-section>
              <div class="row justify-between items-start q-mb-sm">
                <h3 class="text-h6 text-weight-medium">{{ alarm.nombre }}</h3>
              </div>
              <div class="column q-gutter-xs text-body2 text-grey-7">
                <div class="row items-center q-gutter-xs" v-if="alarm.fecha || alarm.hora">
                  <q-icon name="event" size="16px" />
                  <span>{{ alarm.fecha || 'Sin fecha' }} {{ alarm.hora || '' }}</span>
                </div>
                <div class="row items-center q-gutter-xs" v-if="alarm.fechaVencimiento || alarm.horaVencimiento">
                  <q-icon name="event" size="16px" />
                  <span>Vence: {{ alarm.fechaVencimiento || 'Sin fecha' }} {{ alarm.horaVencimiento || '' }}</span>
                </div>
                <div class="row items-center q-gutter-xs">
                  <q-icon name="volume_up" size="16px" />
                  <span>{{ alarm.intensidad_volumen }}%</span>
                </div>
                <div v-if="alarm.gps_enabled" class="row items-center q-gutter-xs">
                  <q-icon name="location_on" size="16px" color="green" />
                  <span class="text-green">{{ alarm.gps_location || 'Ubicación GPS' }}</span>
                </div>
                <div v-if="alarm.repeat_enabled && alarm.repeat_frequency !== 'none'" class="row items-center q-gutter-xs">
                  <q-icon name="repeat" size="16px" color="blue" />
                  <span class="text-blue">{{ getRepeatText(alarm) }}</span>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Modal para crear/editar alarma -->
      <q-dialog 
        v-model="showModal" 
        persistent 
        :maximized="$q.screen.lt.sm"
        transition-show="slide-up"
        transition-hide="slide-down"
      >
        <q-card class="alarm-modal" :class="{ 'mobile-modal': $q.screen.lt.sm }">
          <q-card-section class="row items-center q-pb-none">
            <div class="text-h6">{{ isEditing ? $t('alarms.editAlarm') : $t('alarms.newAlarm') }}</div>
            <q-space />
            <q-btn icon="close" flat round dense v-close-popup />
          </q-card-section>

          <q-form @submit="handleSubmit" class="q-gutter-md">
            <q-card-section>
              <q-input
                v-model="newAlarm.nombre"
                :label="$t('alarms.name')"
                outlined
                dense
                required
              />

              <div class="row q-gutter-sm q-mb-md">
                <div class="col">
                  <q-input
                    v-model="newAlarm.fecha"
                    :label="$t('alarms.date')"
                    type="date"
                    outlined
                    dense
                    required
                  />
                </div>
                <div class="col">
                  <q-input
                    v-model="newAlarm.hora"
                    :label="$t('alarms.time')"
                    type="time"
                    outlined
                    dense
                    required
                  />
                </div>
              </div>

              <!-- Switch para vencimiento -->
              <q-toggle
                v-model="newAlarm.has_vencimiento"
                label="Tiene fecha de vencimiento"
                color="primary"
                class="q-mb-md"
              />

              <!-- Campos de vencimiento (solo si está activado) -->
              <div v-if="newAlarm.has_vencimiento" class="row q-gutter-sm q-mb-md">
                <div class="col">
                  <q-input
                    v-model="newAlarm.fechaVencimiento"
                    :label="$t('alarms.dueDate')"
                    type="date"
                    outlined
                    dense
                  />
                </div>
                <div class="col">
                  <q-input
                    v-model="newAlarm.horaVencimiento"
                    :label="$t('alarms.dueTime')"
                    type="time"
                    outlined
                    dense
                  />
                </div>
              </div>

              <div>
                <div class="text-body2 q-mb-sm">{{ $t('alarms.volume') }}: {{ newAlarm.intensidad_volumen }}%</div>
                <q-slider
                  v-model="newAlarm.intensidad_volumen"
                  :min="0"
                  :max="100"
                  label-always
                  color="primary"
                />
              </div>


              <!-- Configuración GPS -->
              <q-expansion-item
                :label="`Configuración GPS ${isPremium ? '(Opcional)' : '(Premium)'}`"
                icon="location_on"
                class="q-mb-md"
                :disable="!isPremium"
              >
                <template #header-icon>
                  <q-icon
                    v-if="!isPremium"
                    name="lock"
                    color="orange"
                    size="20px"
                    class="q-mr-xs"
                  />
                  <q-icon name="location_on" />
                </template>

                <q-card flat bordered class="q-pa-md">
                  <!-- Mensaje de premium si no tiene acceso -->
                  <div v-if="!isPremium" class="text-center q-pa-md">
                    <q-icon name="lock" color="orange" size="48px" class="q-mb-sm" />
                    <div class="text-h6 text-orange q-mb-sm">Funcionalidad Premium</div>
                    <p class="text-body2 text-grey-7 q-mb-md">
                      La configuración de recordatorios por ubicación GPS está disponible
                      únicamente para usuarios Premium.
                    </p>
                    <q-btn
                      color="primary"
                      label="Actualizar a Premium"
                      @click="showPremiumModal"
                      icon="star"
                      outline
                    />
                  </div>

                  <!-- Configuración GPS para usuarios premium -->
                  <div v-else>
                    <q-toggle
                      v-model="newAlarm.gps_enabled"
                      label="Activar recordatorio por ubicación"
                      color="primary"
                      class="q-mb-md"
                    />
                  
                  <div v-if="newAlarm.gps_enabled">
                    <q-input
                      v-model="newAlarm.gps_location"
                      label="Ubicación"
                      placeholder="Ej: Casa, Trabajo, Universidad"
                      outlined
                      dense
                      class="q-mb-sm"
                    />
                    
                    <q-input
                      v-model="newAlarm.gps_coordinates"
                      label="Coordenadas (Opcional)"
                      placeholder="Latitud, Longitud"
                      outlined
                      dense
                      class="q-mb-sm"
                    />
                    
                    <div class="row q-gutter-sm">
                      <q-btn
                        @click="getCurrentLocation"
                        color="primary"
                        icon="my_location"
                        label="Usar ubicación actual"
                        size="sm"
                        outline
                      />
                      <q-btn
                        @click="openMapModal"
                        color="secondary"
                        icon="map"
                        label="Seleccionar en mapa"
                        size="sm"
                        outline
                      />
                    </div>
                    
                    <div class="q-mt-sm">
                      <div class="text-body2 q-mb-sm">Radio de activación: {{ newAlarm.gps_radius }}m</div>
                      <q-slider
                        v-model="newAlarm.gps_radius"
                        :min="50"
                        :max="1000"
                        :step="50"
                        label-always
                        color="secondary"
                      />
                    </div>
                  </div>
                  </div>
                </q-card>
              </q-expansion-item>

              <!-- Periodicidad -->
              <q-expansion-item
                label="Repetir alarma"
                icon="repeat"
                class="q-mb-md"
              >
                <q-card>
                  <q-card-section>
                    <q-toggle
                      v-model="newAlarm.repeat_enabled"
                      label="Activar repetición"
                      color="primary"
                      class="q-mb-md"
                    />
                    
                    <div v-if="newAlarm.repeat_enabled">
                      <q-select
                        v-model="newAlarm.repeat_frequency"
                        :options="frequencyOptions"
                        label="Frecuencia"
                        outlined
                        dense
                        class="q-mb-md"
                      />
                      
                      <!-- Días de la semana (para semanal y días específicos) -->
                      <div v-if="newAlarm.repeat_frequency === 'weekly' || newAlarm.repeat_frequency === 'specific_day'" class="q-mb-md">
                        <div class="text-body2 q-mb-sm">
                          {{ newAlarm.repeat_frequency === 'weekly' ? 'Días de la semana:' : 'Selecciona los días:' }}
                        </div>
                        <div class="row q-gutter-xs">
                          <q-chip
                            v-for="day in daysOfWeek"
                            :key="day.value"
                            :model-value="newAlarm.repeat_days.includes(day.value)"
                            @click="toggleDay(day.value)"
                            clickable
                            :color="newAlarm.repeat_days.includes(day.value) ? 'primary' : 'grey-4'"
                            :text-color="newAlarm.repeat_days.includes(day.value) ? 'white' : 'grey-8'"
                            size="sm"
                          >
                            {{ day.label }}
                          </q-chip>
                        </div>
                      </div>
                      
                      <!-- Fecha de fin -->
                      <q-input
                        v-model="newAlarm.repeat_end_date"
                        label="Terminar repetición (opcional)"
                        type="date"
                        outlined
                        dense
                        class="q-mb-sm"
                      />
                    </div>
                  </q-card-section>
                </q-card>
              </q-expansion-item>
            </q-card-section>

            <q-card-actions class="row justify-between">
              <q-btn
                v-if="isEditing"
                @click="removeAlarm({ id: currentAlarmId })"
                color="negative"
                icon="delete"
                :label="$t('common.delete')"
              />
              <q-space />
              <div class="row q-gutter-sm">
                <q-btn 
                  @click="closeModal"
                  color="grey"
                  :label="$t('common.cancel')"
                  outline
                />
                <q-btn 
                  type="submit"
                  color="primary"
                  :label="isEditing ? $t('common.update') : $t('common.create')"
                />
              </div>
            </q-card-actions>
          </q-form>
        </q-card>
      </q-dialog>
    </div>

    <!-- Floating Plus Button -->
    <PlusModal />

    <!-- Map Selector Modal -->
    <MapSelector
      v-model="showMapModal"
      @location-selected="onLocationSelected"
    />
  </q-page>
</template>

<script>
import { useAlarmsStore } from '@/stores/alarms';
import { usePaymentsStore } from '@/stores/payments';
import PlusModal from '@/components/Elements/PlusModal.vue';
import geoAlarmService from '@/services/geoAlarmService';
import MapSelector from '@/components/Elements/Maps/MapSelector.vue';
import { Capacitor } from '@capacitor/core';

export default {
  components: {
    PlusModal,
    MapSelector
  },
  setup() {
    const alarmsStore = useAlarmsStore();
    const paymentsStore = usePaymentsStore();

    return {
      alarmsStore,
      paymentsStore,
      geoAlarmService
    };
  },
  
  data() {
    return {
      newAlarm: this.resetAlarmData(),
      isEditing: false,
      currentAlarmId: null,
      showModal: false,
      showMapModal: false
    };
  },


  computed: {
    alarmas() {
      return this.alarmsStore.allAlarms;
    },

    isPremium() {
      return this.paymentsStore.hasActivePremium;
    },

    isNativePlatform() {
      return Capacitor.isNativePlatform();
    },

    frequencyOptions() {
      return [
        { label: 'No repetir', value: 'none' },
        { label: 'Diario', value: 'daily' },
        { label: 'Semanal', value: 'weekly' },
        { label: 'Cada día específico', value: 'specific_day' }
      ];
    },

    daysOfWeek() {
      return [
        { label: 'Lun', value: 'monday' },
        { label: 'Mar', value: 'tuesday' },
        { label: 'Mié', value: 'wednesday' },
        { label: 'Jue', value: 'thursday' },
        { label: 'Vie', value: 'friday' },
        { label: 'Sáb', value: 'saturday' },
        { label: 'Dom', value: 'sunday' }
      ];
    }
  },

  methods: {
    // Método centralizado para abrir el modal de crear
    openCreateModal() {
      console.log('Abriendo modal de crear alarma');
      this.resetForm();
      this.$nextTick(() => {
        this.showModal = true;
      });
    },

    async fetchAlarmas() {
      await this.alarmsStore.fetchAlarms();
    },
    async saveAlarma(alarmData) {
      if (this.isEditing) {
        return await this.alarmsStore.updateAlarm({ ...alarmData, id: this.currentAlarmId });
      } else {
        return await this.alarmsStore.createAlarm(alarmData);
      }
    },
    async deleteAlarma(alarmId) {
      return await this.alarmsStore.deleteAlarm(alarmId);
    },
    
    resetAlarmData() {
      // Por defecto, establecer fecha para hoy
      const today = new Date().toISOString().split('T')[0];
      return {
        nombre: '',
        fecha: today,
        hora: '',
        has_vencimiento: false,
        fechaVencimiento: '',
        horaVencimiento: '',
        intensidad_volumen: 50,
        tipo: 'alarma',
        gps_enabled: false,
        gps_location: '',
        gps_coordinates: '',
        gps_radius: 200,
        repeat_enabled: false,
        repeat_frequency: 'none',
        repeat_days: [],
        repeat_day_of_month: 1,
        repeat_end_date: ''
      };
    },

    async handleSubmit() {
      try {
        const alarmData = {
          ...this.newAlarm
        };

        // Guardar alarma normal
        const savedAlarm = await this.saveAlarma({
          ...alarmData,
          id: this.currentAlarmId
        });

        // Si tiene GPS habilitado, crear alarma geográfica
        if (alarmData.gps_enabled && alarmData.gps_coordinates) {
          await this.createGeoAlarm(savedAlarm || alarmData);
        }

        // Recargar la lista de alarmas para mostrar los cambios
        await this.fetchAlarmas();

        this.closeModal();
        this.$q.notify({
          type: 'positive',
          message: this.isEditing ? this.$t('alarms.updateSuccess') : this.$t('alarms.createSuccess'),
          position: 'top'
        });
      } catch (error) {
        console.error('Error guardando alarma:', error);
        this.$q.notify({
          type: 'negative',
          message: `Error: ${error.response?.data?.error || error.message}`,
          position: 'top'
        });
      }
    },

    editAlarm(alarm) {
      console.log('Editing alarm:', alarm);
      this.newAlarm = {
        nombre: alarm.nombre || '',
        fecha: alarm.fecha || new Date().toISOString().split('T')[0],
        hora: alarm.hora || '',
        has_vencimiento: !!(alarm.fechaVencimiento || alarm.horaVencimiento),
        fechaVencimiento: alarm.fechaVencimiento || '',
        horaVencimiento: alarm.horaVencimiento || '',
        intensidad_volumen: alarm.intensidad_volumen || 50,
        tipo: 'alarma',
        gps_enabled: alarm.gps_enabled || false,
        gps_location: alarm.gps_location || '',
        gps_coordinates: alarm.gps_coordinates || '',
        gps_radius: alarm.gps_radius || 200,
        repeat_enabled: alarm.repeat_enabled || false,
        repeat_frequency: alarm.repeat_frequency || 'none',
        repeat_days: alarm.repeat_days || [],
        repeat_day_of_month: alarm.repeat_day_of_month || 1,
        repeat_end_date: alarm.repeat_end_date || '',
        specific_day: alarm.specific_day || null
      };
      this.currentAlarmId = alarm.id;
      this.isEditing = true;
      this.showModal = true;
      console.log('Form data set to:', this.newAlarm);
    },

    async removeAlarm(alarm) {
      this.$q.dialog({
        title: this.$t('common.confirm'),
        message: this.$t('alarms.confirmDelete'),
        cancel: true,
        persistent: true
      }).onOk(async () => {
        try {
          // Eliminar geo-alarma asociada primero
          await this.deleteGeoAlarm(alarm.id);

          // Eliminar alarma normal
          await this.deleteAlarma(alarm.id);

          // Recargar la lista de alarmas
          await this.fetchAlarmas();

          this.closeModal();
          this.$q.notify({
            type: 'positive',
            message: this.$t('alarms.deleteSuccess'),
            position: 'top'
          });
        } catch (error) {
          console.error('Error eliminando alarma:', error);
          this.$q.notify({
            type: 'negative',
            message: `Error: ${error.response?.data?.error || error.message}`,
            position: 'top'
          });
        }
      });
    },

    // Métodos de geofencing
    async createGeoAlarm(alarmData) {
      try {
        const coordinates = alarmData.gps_coordinates.split(',');
        const latitude = parseFloat(coordinates[0].trim());
        const longitude = parseFloat(coordinates[1].trim());

        if (isNaN(latitude) || isNaN(longitude)) {
          throw new Error('Coordenadas GPS inválidas');
        }

        const geoAlarmId = this.geoAlarmService.createGeoAlarm({
          name: alarmData.nombre,
          latitude,
          longitude,
          radius: alarmData.gps_radius || 200,
          type: 'enter', // Activar al entrar en la zona
          message: `Recordatorio: ${alarmData.nombre}`,
          color: '#176F46',
          icon: 'alarm'
        });

        console.log('Alarma geográfica creada:', geoAlarmId);

        this.$q.notify({
          type: 'info',
          message: 'Geofencing activado para esta alarma',
          position: 'top'
        });

      } catch (error) {
        console.error('Error creando geo-alarma:', error);
        this.$q.notify({
          type: 'warning',
          message: 'Error configurando geofencing: ' + error.message,
          position: 'top'
        });
      }
    },

    async deleteGeoAlarm(alarmId) {
      // Buscar y eliminar geo-alarma asociada
      const geoAlarms = this.geoAlarmService.getAllGeoAlarms();
      const geoAlarm = geoAlarms.find(ga => ga.name.includes(alarmId) || ga.message.includes(alarmId));

      if (geoAlarm) {
        this.geoAlarmService.deleteGeoAlarm(geoAlarm.id);
        console.log('Geo-alarma eliminada:', geoAlarm.id);
      }
    },

    // Métodos GPS

    // Métodos de periodicidad
    toggleDay(dayValue) {
      const index = this.newAlarm.repeat_days.indexOf(dayValue);
      if (index > -1) {
        this.newAlarm.repeat_days.splice(index, 1);
      } else {
        this.newAlarm.repeat_days.push(dayValue);
      }
    },

    closeModal() {
      this.showModal = false;
      this.resetForm();

      // Limpiar query para evitar que se vuelva a abrir
      if (this.$route.query.modo === 'crear') {
        this.$router.replace({
          name: this.$route.name,
          query: {}
        });
      }
    },

    resetForm() {
      this.newAlarm = this.resetAlarmData();
      this.currentAlarmId = null;
      this.isEditing = false;
    },

    async getCurrentLocation() {
      if (!navigator.geolocation) {
        this.$q.notify({
          type: 'negative',
          message: 'Geolocalización no soportada en este navegador',
          position: 'top'
        });
        return;
      }

      try {
        const position = await new Promise((resolve, reject) => {
          navigator.geolocation.getCurrentPosition(resolve, reject, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
          });
        });

        const { latitude, longitude } = position.coords;
        this.newAlarm.gps_coordinates = `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
        this.newAlarm.gps_location = 'Ubicación actual';

        this.$q.notify({
          type: 'positive',
          message: 'Ubicación obtenida exitosamente',
          position: 'top'
        });
      } catch (error) {
        console.error('Error getting location:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error al obtener la ubicación: ' + error.message,
          position: 'top'
        });
      }
    },

    openMapModal() {
      this.showMapModal = true;
    },

    onLocationSelected(location) {
      if (location && location.coordinates && location.address) {
        this.newAlarm.gps_coordinates = location.coordinates;
        this.newAlarm.gps_location = location.address;
        this.showMapModal = false;

        this.$q.notify({
          type: 'positive',
          message: 'Ubicación seleccionada: ' + location.address,
          position: 'top'
        });
      }
    },

    getRepeatText(alarm) {
      if (!alarm.repeat_enabled || alarm.repeat_frequency === 'none') {
        return '';
      }

      const frequencyMap = {
        'daily': 'Diariamente',
        'weekly': 'Semanalmente',
        'monthly': 'Mensualmente',
        'yearly': 'Anualmente',
        'specific_day': 'Día específico'
      };

      let text = frequencyMap[alarm.repeat_frequency] || '';

      if (alarm.repeat_frequency === 'weekly' && alarm.repeat_days && alarm.repeat_days.length > 0) {
        const dayNames = {
          'monday': 'Lun',
          'tuesday': 'Mar',
          'wednesday': 'Mié',
          'thursday': 'Jue',
          'friday': 'Vie',
          'saturday': 'Sáb',
          'sunday': 'Dom'
        };
        const days = alarm.repeat_days.map(day => dayNames[day]).join(', ');
        text += ` (${days})`;
      }

      if (alarm.repeat_frequency === 'specific_day' && alarm.specific_day) {
        const dayNames = {
          'monday': 'Lunes',
          'tuesday': 'Martes',
          'wednesday': 'Miércoles',
          'thursday': 'Jueves',
          'friday': 'Viernes',
          'saturday': 'Sábado',
          'sunday': 'Domingo'
        };
        text += ` (${dayNames[alarm.specific_day]})`;
      }

      return text;
    },

    showPremiumModal() {
      this.$q.notify({
        type: 'info',
        message: 'Redirigiendo a la página de suscripción Premium...',
        position: 'top'
      });

      // Aquí podrías redirigir a la página de premium
      // this.$router.push('/premium');
    }
  },

  async created() {
    await this.fetchAlarmas();

    // Re-programar todas las alarmas activas después de cargarlas
    try {
      await this.alarmsStore.rescheduleAllAlarms();
    } catch (error) {
      console.warn('Error al re-programar alarmas:', error);
    }

    // Inicializar servicio de geolocalización
    try {
      await this.geoAlarmService.initialize();
      console.log('GeoAlarmService inicializado correctamente');
    } catch (error) {
      console.warn('No se pudo inicializar geolocalización:', error.message);
    }

    // Verificar si se debe abrir el modal automáticamente
    if (this.$route.query.modo === 'crear') {
      this.openCreateModal();
    }
  },
  
  mounted() {
    // También verificar en mounted por si acaso
    if (this.$route.query.modo === 'crear' && !this.showModal) {
      this.openCreateModal();
    }
  },

  watch: {
    '$route.query.modo'(newModo) {
      if (newModo === 'crear' && !this.showModal) {
        this.openCreateModal();
      }
    },
    showModal(newVal) {
      // Emitir evento global cuando el modal de alarma se abre/cierra
      if (newVal) {
        window.dispatchEvent(new CustomEvent('modal-opened', {
          detail: { source: 'alarm-modal', type: 'alarm' }
        }));
      } else {
        window.dispatchEvent(new CustomEvent('modal-closed', {
          detail: { source: 'alarm-modal', type: 'alarm' }
        }));
      }
    }
  }
};
</script>

<style scoped>
.full-height {
  min-height: 100vh;
}

.text-mono {
  font-family: 'Courier New', Courier, monospace;
}

/* Header responsive */
.alarms-header {
  padding: 24px 4px 0 4px;
}

.alarms-title {
  font-size: 2rem;
  line-height: 1.2;
  text-align: center;
  width: 100%;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 16px;
}


/* Grid de alarmas */
.alarms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 16px;
}

.alarm-card-container {
  width: 100%;
}

.alarm-card {
  transition: all 0.2s ease;
  min-height: 120px;
}

.alarm-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Modal styles */
.alarm-modal {
  min-width: 400px;
  max-width: 500px;
}

/* Asegurar que el modal de alarmas esté por encima del botón + */
:deep(.q-dialog) {
  z-index: 10000 !important;
}

:deep(.q-dialog__backdrop) {
  z-index: 9999 !important;
}

.mobile-modal {
  min-width: 100vw !important;
  max-width: 100vw !important;
  min-height: 100vh !important;
  border-radius: 0 !important;
}

/* Mobile responsive optimizations */
@media (max-width: 768px) {
  .q-pa-md {
    padding: 12px 16px !important;
  }

  .alarms-header {
    padding: 20px 4px 0 4px !important;
    margin-bottom: 16px !important;
  }

  .alarms-title {
    font-size: 1.5rem;
  }


  /* Grid responsive */
  .alarms-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .alarm-card {
    min-height: 100px;
  }

  .alarm-card .q-card-section {
    padding: 12px 16px !important;
  }

  .alarm-card .text-h6 {
    font-size: 1.1rem;
    line-height: 1.3;
  }

  .alarm-card .text-body2 {
    font-size: 0.85rem;
  }

  /* Modal responsive */
  .alarm-modal {
    min-width: 280px !important;
    max-width: 95vw !important;
    margin: 8px;
  }

  .alarm-modal .q-card-section {
    padding: 12px 16px !important;
  }

  .alarm-modal .text-h6 {
    font-size: 1.1rem;
  }

  /* Form fields */
  .alarm-modal .q-input {
    margin-bottom: 12px;
  }

  .alarm-modal .q-input .q-field__control {
    min-height: 48px;
  }

  .alarm-modal .q-slider {
    margin: 16px 0;
  }

  /* Buttons responsive */
  .alarm-modal .q-btn {
    min-height: 44px;
    padding: 8px 16px;
  }

  .alarm-modal .q-card-actions {
    padding: 12px 16px 16px 16px !important;
    flex-direction: column;
    align-items: stretch;
  }

  .alarm-modal .q-card-actions .row {
    flex-direction: column;
    gap: 8px;
  }

  .alarm-modal .q-card-actions .q-btn {
    width: 100%;
  }
}

/* Mobile small screens */
@media (max-width: 480px) {
  .q-pa-md {
    padding: 8px 12px !important;
  }

  .alarms-header {
    padding: 16px 4px 0 4px !important;
  }

  .alarms-title {
    font-size: 1.3rem;
  }


  .alarms-grid {
    gap: 8px;
  }

  .alarm-card .q-card-section {
    padding: 10px 12px !important;
  }

  .alarm-card .text-h6 {
    font-size: 1rem;
  }

  .alarm-card .text-body2 {
    font-size: 0.8rem;
  }

  /* Modal ultra compacto */
  .alarm-modal {
    min-width: 260px !important;
    max-width: 98vw !important;
    margin: 4px;
  }

  .alarm-modal .q-card-section {
    padding: 8px 12px !important;
  }

  .alarm-modal .text-h6 {
    font-size: 1rem;
  }

  /* Form fields más compactos */
  .alarm-modal .q-input .q-field__control {
    min-height: 44px;
  }

  .alarm-modal .q-input .q-field__label {
    font-size: 0.85rem;
  }

  /* Textarea más pequeño */
  .alarm-modal .q-input[type="textarea"] .q-field__control {
    min-height: 80px;
  }

  /* Fecha y hora stack vertical */
  .alarm-modal .row.q-gutter-sm {
    flex-direction: column;
    gap: 8px;
  }

  .alarm-modal .row.q-gutter-sm .col {
    flex: none;
  }

  /* Slider más compacto */
  .alarm-modal .q-slider {
    margin: 12px 0;
  }

  /* Footer simplificado */
  .alarm-modal .q-card-actions {
    padding: 8px 12px 12px 12px !important;
  }

  .alarm-modal .q-card-actions .q-btn {
    min-height: 40px;
  }
}

/* Landscape mobile */
@media (max-width: 896px) and (orientation: landscape) {
  .alarm-modal {
    max-height: 90vh;
    overflow-y: auto;
  }

  .alarm-modal .q-card-section:not(:first-child) {
    max-height: 60vh;
    overflow-y: auto;
  }

  /* Volver fecha y hora a layout horizontal en landscape */
  .alarm-modal .row.q-gutter-sm {
    flex-direction: row;
  }

  .alarm-modal .row.q-gutter-sm .col {
    flex: 1;
  }
}

/* Touch improvements */
@media (pointer: coarse) {
  .alarm-card {
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
  }

  .alarm-card:active {
    transform: scale(0.98);
    transition: transform 0.1s ease;
  }

  .new-alarm-btn:active {
    transform: scale(0.95);
  }

  /* Eliminar hover effects en dispositivos táctiles */
  .alarm-card:hover {
    transform: none;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  /* Área de toque más grande para inputs */
  .alarm-modal .q-input .q-field__control {
    min-height: 48px;
  }

  .alarm-modal .q-btn {
    min-height: 44px;
  }
}

/* Fullscreen modal for very small screens */
@media (max-width: 360px) {
  .alarm-modal {
    min-width: 100vw !important;
    max-width: 100vw !important;
    min-height: 100vh !important;
    margin: 0 !important;
    border-radius: 0 !important;
  }

  .alarm-modal .q-card-section:first-child {
    padding: 16px 16px 0 16px !important;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  }

  .alarm-modal .q-card-section:not(:first-child):not(.q-card-actions) {
    padding: 16px !important;
    flex: 1;
    overflow-y: auto;
  }

  .alarm-modal .q-card-actions {
    padding: 16px !important;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
  }
}

/* Dark mode support */
.body--dark .alarm-card {
  /* background: #1f2937; */
  /* border-color: #374151; */
}

.body--dark .alarm-card:hover {
  background: #374151;
}

.body--dark .alarm-modal {
  background: #1f2937;
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
  .alarm-card,
  .new-alarm-btn {
    transition: none;
  }
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .alarm-card {
    border-width: 2px;
  }

  .new-alarm-btn {
    border: 2px solid currentColor;
  }
}
</style>