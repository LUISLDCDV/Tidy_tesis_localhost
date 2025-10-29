<template class="calendar-header">
  <div >
    <div class="calendar-header q-mb-md">
      <div class="row items-center justify-end">
        <SyncStatusIndicator 
          :always-show="false"
          class="sync-indicator-calendar"
        />
      </div>
    </div>
    <!-- My Calendars -->
    <q-card class="calendars-section q-mb-md">
      <q-card-section class="q-pa-md">
        <CalendarCarousel 
          :calendarios="calendarios"
          @select="seleccionarCalendario"
          @refresh="fetchCalendarios"
          @save-calendar="handleCalendarSave"
          @delete-calendar="handleCalendarDelete"
          class="carousel-container"
          ref="calendarCarousel"
        />
      </q-card-section>
    </q-card>

    <!-- Main View: Calendar and Events -->
    <div class=" q-gutter-sm">
      <!-- VueCal Calendar -->
      <div class="" v-if="calendarioSeleccionado">
        <q-card class="calendar-card">
          <q-card-section class="q-pa-md">
            <!-- Calendar Controls -->
            <div class="row items-center justify-between q-mb-md">
              <!-- View Controls -->
              <div class="row q-gutter-xs">
                <q-btn-group>
                  <q-btn 
                    @click="changeCalendarView('month')" 
                    icon="calendar_view_month"
                    :flat="currentView !== 'month'"
                    :unelevated="currentView === 'month'"
                    :color="currentView === 'month' ? 'primary' : 'grey-7'"
                    size="sm"
                    :label="$q.screen.gt.xs ? $t('calendar.month') : ''"
                  />
                  <q-btn 
                    @click="changeCalendarView('week')" 
                    icon="calendar_view_week"
                    :flat="currentView !== 'week'"
                    :unelevated="currentView === 'week'"
                    :color="currentView === 'week' ? 'primary' : 'grey-7'"
                    size="sm"
                    :label="$q.screen.gt.xs ? $t('calendar.week') : ''"
                  />
                  <q-btn 
                    @click="changeCalendarView('day')" 
                    icon="today"
                    :flat="currentView !== 'day'"
                    :unelevated="currentView === 'day'"
                    :color="currentView === 'day' ? 'primary' : 'grey-7'"
                    size="sm"
                    :label="$q.screen.gt.xs ? $t('calendar.day') : ''"
                  />
                </q-btn-group>
                
                <!-- Navigation Controls -->
                <q-btn-group v-if="$q.screen.gt.sm">
                  <q-btn 
                    @click="previousPeriod" 
                    icon="chevron_left"
                    flat
                    size="sm"
                  />
                  <q-btn 
                    @click="goToToday" 
                    :label="$t('calendar.today')"
                    flat
                    size="sm"
                  />
                  <q-btn 
                    @click="nextPeriod" 
                    icon="chevron_right"
                    flat
                    size="sm"
                  />
                </q-btn-group>
                
                <!-- Date Selector -->
                <q-btn 
                  icon="date_range"
                  flat
                  size="sm"
                  :label="$q.screen.gt.xs ? formatCurrentPeriod() : ''"
                  :round="$q.screen.lt.sm"
                  @click="showDatePicker = true"
                />
                
                <!-- Date Picker Dialog -->
                <q-dialog v-model="showDatePicker">
                  <q-card>
                    <q-card-section>
                      <div class="text-h6">{{ $t('calendar.selectDate') }}</div>
                    </q-card-section>
                    <q-card-section>
                      <q-date
                        v-model="selectedDate"
                        :locale="$q.lang.date"
                        @update:model-value="navigateToDate"
                        minimal
                      />
                    </q-card-section>
                    <q-card-actions align="right">
                      <q-btn 
                        flat 
                        :label="$t('common.cancel')" 
                        @click="showDatePicker = false" 
                      />
                    </q-card-actions>
                  </q-card>
                </q-dialog>
              </div>

              <!-- Actions -->
              <div class="row q-gutter-xs">
                <q-btn
                  @click="refreshCalendar"
                  icon="refresh"
                  flat
                  size="sm"
                  :loading="loading"
                />
              </div>
            </div>

            <!-- Calendar Display -->
            <vue-cal
              :key="calendarioSeleccionado?.id"
              :events="formattedEvents"
              :time="true"
              :active-view="currentView"
              :views="['month', 'week', 'day']"
              locale="es"
              :disable-views="[]"
              :editable-events="true"
              :drag-to-create-event="true"
              :min-cell-width="50"
              :snap-to-time="15"
              :time-from="6 * 60"
              :time-to="22 * 60"
              :time-step="60"
              @event-click="onEventClick"
              @event-drop="onEventDrop"
              @event-duration-change="onEventDurationChange"
              @cell-click="onCellClick"
              @cell-double-click="onCellDoubleClick"
              @ready="onCalendarReady"
              @view-change="onViewChange"
              :class="['custom-vuecal', 'calendar-display', { 'dark-theme': $q.dark.isActive }]"
              ref="vuecal"
            >
              <!-- Custom event template -->
              <template #event="{ event, view }">
                <div class="event-content">
                  <div class="event-header">
                    <q-icon
                      :name="getEventIcon(event)"
                      size="14px"
                      class="event-icon"
                    />
                    <div class="event-title">{{ event.title }}</div>
                  </div>
                  <div v-if="event.clima && formatClima(event.clima)" class="event-weather">
                    <img
                      :src="'https:' + formatClima(event.clima).icon"
                      :alt="formatClima(event.clima).condition"
                      class="weather-icon-small"
                    />
                    <span class="weather-temp">{{ formatClima(event.clima).temp }}°C</span>
                  </div>
                </div>
              </template>
            </vue-cal>
          </q-card-section>
        </q-card>
      </div>

      <!-- Side Panel: Upcoming Events List -->
      <div class="">
        <div class="">
          <q-card v-if="formattedEvents.length" class="">
            <q-card-section class="">
              <div class="row items-center justify-between q-mb-md">
                <h3 class="text-h6 text-weight-medium">{{ $t('calendar.upcomingEvents') }}</h3>
                <q-chip 
                  color="grey-3" 
                  text-color="grey-8"
                  :label="formattedEvents.length"
                  dense
                />
              </div>
              <q-list class="events-list">
                <q-item
                  v-for="event in formattedEvents" 
                  :key="event.id" 
                  @click="editEvent(event)" 
                  clickable
                  class="event-item q-mb-sm"
                >
                  <q-item-section avatar>
                    <q-avatar
                      :color="event.color || 'primary'"
                      size="40px"
                      class="event-avatar"
                    >
                      <q-icon
                        :name="getEventIcon(event)"
                        size="20px"
                      />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="text-weight-medium">
                      {{ event.title }}
                    </q-item-label>
                    <q-item-label v-if="event.content" caption class="text-grey-7 text-caption">
                      {{ event.content }}
                    </q-item-label>
                    <q-item-label caption class="row items-center q-gutter-xs q-mt-xs">
                      <q-icon name="schedule" size="12px" class="text-grey-7" />
                      <span class="text-grey-7 text-caption">
                        {{ formatDate(event.start) }} - {{ formatTime(event.start) }}
                      </span>
                    </q-item-label>
                    <!-- Location Information -->
                    <q-item-label v-if="event.gps" caption class="row items-center q-gutter-xs q-mt-xs">
                      <q-icon name="location_on" size="10px" class="text-grey-7" />
                      <span class="text-grey-7" style="font-size: 10px;">
                        {{ formatGps(event.gps) }}
                      </span>
                    </q-item-label>
                    <!-- Weather Information -->
                    <div v-if="event.clima && formatClima(event.clima)" class="row items-center q-gutter-xs q-mt-xs">
                      <img 
                        :src="formatClima(event.clima).icon" 
                        :alt="formatClima(event.clima).condition"
                        style="width: 64px; height: 64px;"
                      />
                      <span class="text-grey-7 text-weight-medium text-caption">
                        {{ formatClima(event.clima).temp }}°C
                      </span>
                    </div>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
          <q-card v-else class="empty-events-card">
            <q-card-section class="text-center q-pa-lg">
              <q-icon name="event" size="48px" class="text-grey-4 q-mb-md" />
              <p class="text-grey-6">No scheduled events</p>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Event Form Modal -->
    <EventFormModal
      v-if="modalVisible"
      :event="currentEvent"
      :calendarios="calendarios"
      :editing="isEditing"
      :available-colors="availableColors" 
      @save="handleSave"
      @delete="handleDelete"
      @close="modalVisible = false"
    />

  </div>
</template>

<script>
// Componentes importados
import VueCal from 'vue-cal';
import 'vue-cal/dist/vuecal.css';
import '@/assets/styles/vuecal-dark-theme.css';
import CalendarCarousel from './CalendarCarousel.vue';
import GeoClima from './GeoClima.vue';
import SyncStatusIndicator from '@/components/SyncStatusIndicator.vue';
import { useConfirm } from '@/services/useConfirm';
import { formatEvents } from '@/utils/eventsFormats';
import EventFormModal from './EventFormModal.vue';
import { useElementsStore } from '@/stores/elements';
import { storeToRefs } from 'pinia';
import eventNotificationService from '@/services/eventNotifications';

/**
 * @component CalendarView
 * @description Componente principal para la gestión de calendarios y eventos
 * 
 * Pendientes de implementación:
 * 1. Migrar formularios modales a componentes independientes
 * 2. Implementar modal de GeoClima
 * 3. Optimizar la gestión de estado con Vuex
 * 4. Mejorar el manejo de fechas y zonas horarias
 */
export default {
  name: 'CalendarView',
  components: {
    VueCal,
    CalendarCarousel,
    GeoClima,
    EventFormModal,
    SyncStatusIndicator
  },
  props: {
    showCalendarModal: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isEditing: false,
      modalVisible: false,
      showEventForm: false,
      editingEvent: false,
      loading: false,
      currentView: 'month',
      showDatePicker: false,
      selectedDate: null,
      currentEvent: {
        id: null,
        nombre: '',
        informacion: '',
        fechaVencimiento: '',
        horaVencimiento: '',
        calendario_id: null,
        tipo: 'evento',
        gps: null,
        clima: null,
        elemento_id: null,
        color: '#4caf50'
      },
      availableColors: ['#4caf50', '#2196f3', '#9c27b0', '#ff9800', '#f44336', '#607d8b', '#00bcd4', '#8bc34a', '#e91e63'],
      selectedLocation: null,
      calendarios: [], // Lista reactiva de calendarios
      calendarioSeleccionado: null, // Calendario actualmente seleccionado
      eventosParaCalendario: [] // Lista reactiva de eventos
    };
  },
  computed: {
    formattedEvents() {
      return this.eventosParaCalendario ? formatEvents(this.eventosParaCalendario) : [];
    }
  },
  methods: {
    async fetchCalendarios() {
      try {
        // Buscar token en localStorage primero, luego en sessionStorage (consistente con API interceptor)
        const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No hay token de autenticación');
        }

        // Usar la función obtenerCalendarios del api.js
        const { obtenerCalendarios } = await import('@/services/api');
        const response = await obtenerCalendarios(token);
        
        // La respuesta tiene la estructura { calendarios: [...] }
        this.calendarios = response.calendarios || [];
        
      } catch (error) {
        console.error('Error al obtener calendarios:', error);
        this.calendarios = [];
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar calendarios',
          position: 'top'
        });
      }
    },

    async fetchEventos(calendarioId) {
      try {
        const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No hay token de autenticación');
        }

        // Usar la función obtenerEventos del api.js
        const { obtenerEventos } = await import('@/services/api');
        const response = await obtenerEventos(token, calendarioId);
        
        // Actualizar la lista de eventos
        this.eventosParaCalendario = response.eventos || response || [];
        
      } catch (error) {
        console.error('Error al obtener eventos:', error);
        this.eventosParaCalendario = [];
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar eventos',
          position: 'top'
        });
      }
    },
    async guardarEvento(eventoData) {
      console.log('Guardando evento:', eventoData);
      try {
        const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No hay token de autenticación');
        }

        // Usar la función guardarElemento del api.js para crear/actualizar un evento
        const { guardarElemento } = await import('@/services/api');
        
        // Preparar los datos para el backend
        const eventoDataForAPI = {
          elemento_id: eventoData.elemento_id || null, // Para actualizaciones
          calendario_id: eventoData.calendario_id,
          tipo: eventoData.tipo || 'evento',
          nombre: eventoData.nombre,
          informacion: eventoData.informacion || '',
          fechaVencimiento: eventoData.fechaVencimiento,
          horaVencimiento: eventoData.horaVencimiento,
          gps: eventoData.gps || null,
          clima: eventoData.clima || null
        };

        console.log('💾 guardarEvento - Datos finales enviados al backend:', eventoDataForAPI);
        console.log('💾 guardarEvento - GPS final:', eventoDataForAPI.gps, 'Tipo:', typeof eventoDataForAPI.gps);
        console.log('💾 guardarEvento - Clima final:', eventoDataForAPI.clima, 'Tipo:', typeof eventoDataForAPI.clima);
        console.log('🌐 guardarEvento - Iniciando llamada API al backend...');
        console.log('🌐 guardarEvento - Token existe:', !!token);
        console.log('🌐 guardarEvento - URL API:', '/elementos/saveUpdate');
        console.log('🌐 guardarEvento - Método HTTP:', 'POST');

        const response = await guardarElemento(token, 'evento', eventoDataForAPI);

        console.log('✅ guardarEvento - Respuesta recibida del backend:', response);
        console.log('✅ guardarEvento - Status de respuesta:', response?.status);
        console.log('✅ guardarEvento - Datos de respuesta:', response?.data);
        
        // Activar notificaciones según si es creación o actualización
        if (eventoData.elemento_id) {
          // Evento actualizado
          eventNotificationService.notifyEventUpdated(eventoData);
        } else {
          // Evento creado
          eventNotificationService.notifyEventCreated(eventoData);
        }
        
        this.$q.notify({
          type: 'positive',
          message: eventoData.elemento_id ? 'Evento actualizado exitosamente' : 'Evento creado exitosamente',
          position: 'top'
        });

        // Recargar eventos después de guardar
        if (this.calendarioSeleccionado) {
          await this.fetchEventos(this.calendarioSeleccionado.id);
          // Reprogramar notificaciones para todos los eventos
          eventNotificationService.rescheduleAllNotifications(this.eventosParaCalendario);
        }

        return response;
      } catch (error) {
        console.error('❌ guardarEvento - Error completo:', error);
        console.error('❌ guardarEvento - Error message:', error.message);
        console.error('❌ guardarEvento - Error response:', error.response);
        console.error('❌ guardarEvento - Error response data:', error.response?.data);
        console.error('❌ guardarEvento - Error response status:', error.response?.status);
        console.error('❌ guardarEvento - Error stack:', error.stack);

        const errorMessage = error.response?.data?.message ||
                           error.response?.data?.error ||
                           error.message ||
                           'Error desconocido al guardar evento';

        this.$q.notify({
          type: 'negative',
          message: `Error al guardar el evento: ${errorMessage}`,
          position: 'top',
          timeout: 5000
        });
        throw error;
      }
    },
    async eliminar(elementoId) {
      console.log('🗑️ eliminar - Iniciando eliminación con elementoId:', elementoId);
      try {
        const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No hay token de autenticación');
        }

        console.log('🌐 eliminar - Token existe:', !!token);
        console.log('🌐 eliminar - URL API:', `/elementos/eliminarElemento/${elementoId}`);
        console.log('🌐 eliminar - Método HTTP:', 'POST');

        // Usar la función eliminarElemento del api.js
        const { eliminarElemento } = await import('@/services/api');

        const response = await eliminarElemento(token, elementoId);

        console.log('✅ eliminar - Respuesta recibida del backend:', response);
        console.log('✅ eliminar - Status de respuesta:', response?.status);
        console.log('✅ eliminar - Datos de respuesta:', response?.data);
        
        this.$q.notify({
          type: 'positive',
          message: 'Evento eliminado exitosamente',
          position: 'top'
        });

        return response;
      } catch (error) {
        console.error('Error al eliminar evento:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error al eliminar el evento',
          position: 'top'
        });
        throw error;
      }
    },
    async guardarCalendario(calendarData) {
      console.log('Guardando calendario:', calendarData);
      try {
        const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No hay token de autenticación');
        }

        // Usar la función guardarElemento del api.js para crear un calendario
        const { guardarElemento } = await import('@/services/api');
        
        // Preparar los datos para el backend
        const calendarDataForAPI = {
          nombre: calendarData.nombre,
          informacion: calendarData.descripcion || '', // descripcion -> informacion
          color: calendarData.color || '#4caf50' // color por defecto si no se especifica
        };

        const response = await guardarElemento(token, 'calendario', calendarDataForAPI);
        
        this.$q.notify({
          type: 'positive',
          message: 'Calendario creado exitosamente',
          position: 'top'
        });

        return response;
      } catch (error) {
        console.error('Error al guardar calendario:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error al crear el calendario',
          position: 'top'
        });
        throw error;
      }
    },
    async eliminarCalendario(calendarId) {
      console.log('Eliminando calendario:', calendarId);
      try {
        const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No hay token de autenticación');
        }

        // Usar la función eliminarElemento del api.js
        const { eliminarElemento } = await import('@/services/api');
        
        const response = await eliminarElemento(token, calendarId);
        
        this.$q.notify({
          type: 'positive',
          message: 'Calendario eliminado exitosamente',
          position: 'top'
        });

        return response;
      } catch (error) {
        console.error('Error al eliminar calendario:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error al eliminar el calendario',
          position: 'top'
        });
        throw error;
      }
    },
    
    /**
     * Selecciona un calendario y carga sus eventos
     * @param {Object} calendario - Calendario a seleccionar
     */
    async seleccionarCalendario(calendario) {
      if (!calendario) return;
      
      try {
        console.log('Calendario seleccionado:', calendario);
        this.$q.notify({
          type: 'positive',
          message: `Calendario "${calendario.nombre}" seleccionado`,
          position: 'top',
          timeout: 2000
        });
      } catch (error) {
        console.error('Error al seleccionar calendario:', error);
      }
    },

    /**
     * Maneja el guardado de un evento
     * @param {Object} currentEvent - Evento a guardar
     */
    async handleSave(currentEvent) {
      if (!currentEvent || !this.calendarioSeleccionado) {
        console.error('Datos incompletos para guardar el evento');
        return;
      }

      try {
        console.log('🔧 handleSave - Evento recibido:', currentEvent);
        console.log('🔧 handleSave - GPS:', currentEvent.gps, 'Tipo:', typeof currentEvent.gps);
        console.log('🔧 handleSave - Clima:', currentEvent.clima, 'Tipo:', typeof currentEvent.clima);

        // Validar datos requeridos
        if (!currentEvent.nombre || !currentEvent.fechaVencimiento || !currentEvent.horaVencimiento) {
          throw new Error('Faltan campos requeridos');
        }

        // Formatear el evento
        const eventoFormateado = {
          id: currentEvent.id,
          nombre: currentEvent.nombre.trim(),
          informacion: currentEvent.informacion?.trim(),
          fechaVencimiento: currentEvent.fechaVencimiento,
          horaVencimiento: currentEvent.horaVencimiento,
          calendario_id: currentEvent.calendario_id || this.calendarioSeleccionado?.id,
          elemento_id: currentEvent.elemento_id || null,
          tipo: 'evento',
          // Los datos ya vienen como JSON string desde EventFormModal, no convertir de nuevo
          gps: currentEvent.gps || null,
          clima: currentEvent.clima || null
        };

        await this.guardarEvento(eventoFormateado);
        this.modalVisible = false;
        this.resetEventForm();
        
        // Mostrar mensaje de éxito
        this.$emit('evento-guardado', eventoFormateado);
      } catch (error) {
        console.error('Error guardando evento:', error);
        throw new Error('Error al guardar el evento: ' + (error.response?.data?.message || error.message));
      }
    },
    onEventClick(event) {
      this.editEvent(event);
    },

    /**
     * Maneja el arrastre de eventos en el calendario
     * @param {Object} event - Evento arrastrado
     * @param {Date} newDate - Nueva fecha del evento
     */
    async onEventDrop(event, newDate) {
      try {
        // Calcular la nueva fecha y hora
        const newDateStr = newDate.toISOString().split('T')[0];
        const newTimeStr = newDate.toTimeString().split(' ')[0].substring(0, 5);

        // Actualizar el evento
        const updatedEvent = {
          ...event,
          fechaVencimiento: newDateStr,
          horaVencimiento: newTimeStr,
          elemento_id: event.elemento_id
        };

        await this.guardarEvento(updatedEvent);
        
        this.$q.notify({
          type: 'positive',
          message: this.$t('calendar.eventMoved'),
          position: 'top',
          timeout: 2000
        });
      } catch (error) {
        console.error('Error al mover evento:', error);
        this.$q.notify({
          type: 'negative',
          message: this.$t('calendar.errorMovingEvent'),
          position: 'top'
        });
        // Recargar eventos para restaurar estado
        if (this.calendarioSeleccionado) {
          await this.fetchEventos(this.calendarioSeleccionado.id);
        }
      }
    },

    /**
     * Maneja el cambio de duración de eventos
     * @param {Object} event - Evento modificado
     * @param {Date} newEnd - Nueva fecha de fin
     */
    async onEventDurationChange(event, newEnd) {
      try {
        // Para eventos simples, simplemente actualizamos la hora de fin
        const newTimeStr = newEnd.toTimeString().split(' ')[0].substring(0, 5);
        
        const updatedEvent = {
          ...event,
          horaVencimiento: newTimeStr,
          elemento_id: event.elemento_id
        };

        await this.guardarEvento(updatedEvent);
        
        this.$q.notify({
          type: 'positive',
          message: this.$t('calendar.eventDurationChanged'),
          position: 'top',
          timeout: 2000
        });
      } catch (error) {
        console.error('Error al cambiar duración del evento:', error);
        this.$q.notify({
          type: 'negative',
          message: this.$t('calendar.errorChangingDuration'),
          position: 'top'
        });
        // Recargar eventos para restaurar estado
        if (this.calendarioSeleccionado) {
          await this.fetchEventos(this.calendarioSeleccionado.id);
        }
      }
    },

    /**
     * Maneja el clic en una celda del calendario
     * @param {Date} date - Fecha clickeada
     * @param {Object} e - Evento del DOM
     */
    onCellClick(date, e) {
      // Preparar datos para nuevo evento con la fecha seleccionada
      const dateStr = date.toISOString().split('T')[0];
      const timeStr = date.toTimeString().split(' ')[0].substring(0, 5);
      
      this.currentEvent = {
        ...this.currentEvent,
        fechaVencimiento: dateStr,
        horaVencimiento: timeStr || '09:00',
        calendario_id: this.calendarioSeleccionado?.id
      };
    },

    /**
     * Maneja el doble clic en una celda (crear evento rápido)
     * @param {Date} date - Fecha clickeada
     * @param {Object} e - Evento del DOM
     */
    onCellDoubleClick(date, e) {
      const dateStr = date.toISOString().split('T')[0];
      const timeStr = date.toTimeString().split(' ')[0].substring(0, 5);
      
      // Preparar evento con datos por defecto
      this.currentEvent = {
        id: null,
        nombre: '',
        informacion: '',
        fechaVencimiento: dateStr,
        horaVencimiento: timeStr || '09:00',
        calendario_id: this.calendarioSeleccionado?.id,
        tipo: 'evento',
        gps: null,
        clima: null,
        elemento_id: null,
        color: this.availableColors[0]
      };
      
      this.isEditing = false;
      this.modalVisible = true;
    },

    /**
     * Callback cuando el calendario está listo
     */
    onCalendarReady() {
      console.log('Calendario inicializado correctamente');
      // Aquí podemos realizar inicializaciones adicionales si es necesario
    },

    /**
     * Maneja el cambio de vista del calendario
     * @param {Object} event - Evento de cambio de vista
     */
    onViewChange(event) {
      if (event.view && this.currentView !== event.view) {
        this.currentView = event.view;
      }
    },

    /**
     * Navega a una fecha específica en el calendario
     * @param {Date|string} date - Fecha a la que navegar
     */
    goToDate(date) {
      if (this.$refs.vuecal && date) {
        try {
          console.log('Navegando a fecha programáticamente:', date);
          this.$refs.vuecal.updateMutableDate(new Date(date));
        } catch (error) {
          console.error('Error navegando a fecha programáticamente:', error);
        }
      }
    },

    /**
     * Navega a la fecha seleccionada desde el date picker
     * @param {string} dateString - Fecha en formato YYYY-MM-DD
     */
    navigateToDate(dateString) {
      if (dateString && this.$refs.vuecal) {
        try {
          console.log('Navegando a fecha:', dateString);
          // VueCal acepta fecha en formato Date object o string YYYY-MM-DD
          this.$refs.vuecal.updateMutableDate(new Date(dateString));
          this.showDatePicker = false;
        } catch (error) {
          console.error('Error navegando a fecha:', error);
          // Fallback: intentar con formato alternativo
          try {
            const [year, month, day] = dateString.split('-');
            const date = new Date(year, month - 1, day);
            this.$refs.vuecal.updateMutableDate(date);
            this.showDatePicker = false;
          } catch (fallbackError) {
            console.error('Error en fallback navegando a fecha:', fallbackError);
          }
        }
      }
    },

    /**
     * Formatea el período actual visible en el calendario
     * @returns {string} Período formateado
     */
    formatCurrentPeriod() {
      const now = new Date();
      const options = { year: 'numeric', month: 'long' };
      
      if (this.currentView === 'day') {
        options.day = 'numeric';
      } else if (this.currentView === 'week') {
        return `${this.$t('calendar.week')} ${this.getWeekNumber(now)}`;
      }
      
      return new Intl.DateTimeFormat('es-ES', options).format(now);
    },

    /**
     * Obtiene el número de semana del año
     * @param {Date} date - Fecha
     * @returns {number} Número de semana
     */
    getWeekNumber(date) {
      const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
      const dayNum = d.getUTCDay() || 7;
      d.setUTCDate(d.getUTCDate() + 4 - dayNum);
      const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
      return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
    },

    /**
     * Cambia la vista del calendario
     * @param {string} view - Vista a mostrar (month, week, day)
     */
    changeCalendarView(view) {
      if (['month', 'week', 'day'].includes(view)) {
        this.currentView = view;
        this.$nextTick(() => {
          if (this.$refs.vuecal) {
            if (this.$refs.vuecal.switchView) {
              this.$refs.vuecal.switchView(view);
            } else if (this.$refs.vuecal.$refs && this.$refs.vuecal.$refs.vuecal) {
              this.$refs.vuecal.$refs.vuecal.switchView(view);
            }
          }
        });
      }
    },

    /**
     * Navega al período anterior
     */
    previousPeriod() {
      if (this.$refs.vuecal) {
        this.$refs.vuecal.previous();
      }
    },

    /**
     * Navega al período siguiente
     */
    nextPeriod() {
      if (this.$refs.vuecal) {
        this.$refs.vuecal.next();
      }
    },

    /**
     * Navega a hoy
     */
    goToToday() {
      if (this.$refs.vuecal) {
        this.$refs.vuecal.goToToday();
      }
    },

    /**
     * Refresca el calendario actual
     */
    async refreshCalendar() {
      if (this.calendarioSeleccionado) {
        this.loading = true;
        try {
          await this.fetchEventos(this.calendarioSeleccionado.id);
        } finally {
          this.loading = false;
        }
      }
    },
    openAddEvent() {
      this.resetEventForm();
      this.isEditing = false;
      this.modalVisible = true;
    },
    editEvent(event) {
      this.isEditing = true;
      
      // Mapear propiedades del evento al formato esperado por el formulario
      this.currentEvent = {
        id: event.id,
        nombre: event.nombre || event.title, // Asegurar compatibilidad
        informacion: event.informacion || event.content,
        fechaVencimiento: event.fechaVencimiento || this.formatDate(event.start, 'YYYY-MM-DD'),
        horaVencimiento: event.horaVencimiento || this.formatTime(event.start),
        calendario_id: event.calendario_id || this.calendarioSeleccionado?.id,
        gps: event.gps || null,
        clima: event.clima || null,
        elemento_id: event.elemento_id || null,
        color: event.color || '#4caf50'
      };
      
      this.modalVisible = true;
    },
    async deleteEventConfirmed(eventoObject) {
      const confirmed = await useConfirm('¿Estás seguro de eliminar este evento?');
      if (!confirmed) return;
      try {
        await this.eliminar(eventoObject.elemento_id);

        // Recargar eventos después de eliminar
        if (this.calendarioSeleccionado) {
          await this.fetchEventos(this.calendarioSeleccionado.id);
          eventNotificationService.rescheduleAllNotifications(this.eventosParaCalendario);
        }

        this.$q.notify({
          type: 'positive',
          message: 'Evento eliminado correctamente',
          position: 'top'
        });
      } catch (error) {
        console.error('Error al eliminar el evento:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Hubo un error al eliminar el evento.',
          position: 'top'
        });
      }
    },
    async handleDelete(eventId) {
      console.log('🗑️ handleDelete - Iniciando eliminación de evento');
      console.log('🗑️ handleDelete - eventId recibido:', eventId);
      console.log('🗑️ handleDelete - elemento_id:', eventId.elemento_id);

      const confirmed = await useConfirm('¿Estás seguro de eliminar este evento?');
      if (!confirmed) {
        console.log('❌ handleDelete - Usuario canceló eliminación');
        return;
      }

      console.log('✅ handleDelete - Usuario confirmó eliminación');

      try {
        // Buscar el evento antes de eliminarlo para las notificaciones
        const eventoAEliminar = this.eventosParaCalendario.find(e => e.elemento_id === eventId.elemento_id);
        console.log('🔍 handleDelete - Evento encontrado para eliminar:', eventoAEliminar);

        console.log('🌐 handleDelete - Llamando API eliminar con ID:', eventId.elemento_id);
        await this.eliminar(eventId.elemento_id);
        
        // Notificar eliminación del evento
        if (eventoAEliminar) {
          eventNotificationService.notifyEventDeleted(eventoAEliminar);
        }
        
        this.modalVisible = false;
        this.resetEventForm();
        
        // Recargar eventos y reprogramar notificaciones
        if (this.calendarioSeleccionado) {
          await this.fetchEventos(this.calendarioSeleccionado.id);
          eventNotificationService.rescheduleAllNotifications(this.eventosParaCalendario);
        }
      } catch (error) {
        console.error('Error eliminando evento:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Hubo un error al eliminar el evento.',
          position: 'top'
        });
      }
    },
    closeModal() {
      this.showEventForm = false;
      this.editingEvent = false;
      this.resetEventForm();
    },
    resetEventForm() {
      this.currentEvent = {
        id: null,
        nombre: '',
        informacion: '',
        fechaVencimiento: '',
        horaVencimiento: '',
        calendario_id: this.calendarioSeleccionado?.id,
        tipo: 'evento',
        gps: null,
        clima: null,
        elemento_id: null,
        color: '#4caf50'
      };
    },
    /**
     * Formatea una fecha según el formato especificado
     * @param {Date|string} date - Fecha a formatear
     * @param {string} format - Formato deseado ('YYYY-MM-DD' o 'dd/MM/yyyy')
     * @returns {string} Fecha formateada
     */
    formatDate(date, format = 'dd/MM/yyyy') {
      if (!date) return '';
      try {
        const d = new Date(date);
        if (isNaN(d.getTime())) return '';
        
        if (format === 'YYYY-MM-DD') {
          return d.toISOString().split('T')[0];
        }
        
        return new Intl.DateTimeFormat('es-ES', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric'
        }).format(d);
      } catch (error) {
        console.error('Error al formatear fecha:', error);
        return '';
      }
    },

    /**
     * Formatea una hora desde una fecha
     * @param {Date|string} date - Fecha de la cual extraer la hora
     * @returns {string} Hora formateada (HH:mm)
     */
    formatTime(date) {
      if (!date) return '';
      try {
        const d = new Date(date);
        if (isNaN(d.getTime())) return '';
        
        return new Intl.DateTimeFormat('es-ES', {
          hour: '2-digit',
          minute: '2-digit',
          hour12: false
        }).format(d);
      } catch (error) {
        console.error('Error al formatear hora:', error);
        return '';
      }
    },

    handleLocation(location) {
      this.selectedLocation = location;
      this.currentEvent.gps = `${location.nombre}, ${location.provincia.nombre}`;
    },

    /**
     * Formatea datos de geolocalización
     * @param {Object|string} gpsData - Datos de geolocalización
     * @returns {string} Ubicación formateada
     */
    formatGps(gpsData) {
      if (!gpsData) return '';

      console.log('🔧 formatGps - Input data:', gpsData, 'Type:', typeof gpsData);

      try {
        // Si ya es un string simple (formato anterior), devolverlo
        if (typeof gpsData === 'string' && !gpsData.startsWith('{') && !gpsData.startsWith('[') && !gpsData.startsWith('"')) {
          console.log('✅ formatGps - String simple detectado:', gpsData);
          return gpsData;
        }

        let data = gpsData;

        // Manejar doble escapado JSON (string dentro de string)
        if (typeof gpsData === 'string') {
          console.log('🔍 formatGps - Procesando string JSON');

          // Detectar el patrón específico: "\"{ ... }"
          if (gpsData.startsWith('"\\"') || gpsData.includes('\\"{\\')) {
            console.log('🚨 formatGps - Doble escapado complejo detectado');
            // Múltiples niveles de escapado
            let processedData = gpsData;

            // Remover comillas externas
            if (processedData.startsWith('"') && processedData.endsWith('"')) {
              processedData = processedData.slice(1, -1);
            }

            // Decodificar escape sequences
            processedData = processedData.replace(/\\"/g, '"').replace(/\\\\/g, '\\');
            console.log('🔧 formatGps - Primera decodificación:', processedData);

            // Si sigue siendo un string JSON, parsearlo otra vez
            if (typeof processedData === 'string' && processedData.startsWith('{')) {
              data = JSON.parse(processedData);
            } else {
              data = processedData;
            }

            console.log('🔧 formatGps - Resultado final parseado:', data);
          }
          // Patrón normal de doble escapado: "{ ... }"
          else if (gpsData.startsWith('"') && gpsData.endsWith('"')) {
            console.log('🔧 formatGps - Doble escapado simple detectado');
            const unquoted = gpsData.slice(1, -1);
            const unescaped = unquoted.replace(/\\"/g, '"').replace(/\\\\/g, '\\');
            console.log('🔧 formatGps - Decodificado:', unescaped);
            data = JSON.parse(unescaped);
          }
          // JSON normal que empieza con {
          else if (gpsData.startsWith('{')) {
            console.log('🔧 formatGps - JSON normal detectado');
            data = JSON.parse(gpsData);
          }
          // Último intento: podría ser un string con caracteres especiales
          else {
            console.log('🔧 formatGps - Intentando parsear string directo');
            try {
              data = JSON.parse(gpsData);
            } catch (e) {
              console.log('🔧 formatGps - No es JSON válido, usando como string:', gpsData);
              return gpsData; // Si no es JSON, usar el string tal como está
            }
          }
        }

        console.log('🔍 formatGps - Datos parseados:', data);

        // Verificar si tiene la estructura esperada
        if (data && typeof data === 'object') {
          if (data.nombre && data.provincia?.nombre) {
            // Formato: "Ciudad, Departamento, Provincia" o "Ciudad, Provincia"
            const departamento = data.departamento?.nombre ? `, ${data.departamento.nombre}` : '';
            const result = `${data.nombre}${departamento}, ${data.provincia.nombre}`;
            console.log('✅ formatGps - Resultado exitoso:', result);
            return result;
          }

          // Si tiene coordinates pero no nombres, mostrar coordenadas
          if (data.centroide?.lat && data.centroide?.lon) {
            return `${data.centroide.lat.toFixed(4)}, ${data.centroide.lon.toFixed(4)}`;
          }

          // Si tiene coordinates pero no nombres (formato alternativo)
          if (data.lat && data.lng) {
            return `${data.lat.toFixed(4)}, ${data.lng.toFixed(4)}`;
          }

          // Si es un array de coordenadas [lat, lng]
          if (Array.isArray(data) && data.length === 2) {
            return `${data[0].toFixed(4)}, ${data[1].toFixed(4)}`;
          }
        }

        throw new Error('Formato de ubicación no reconocido');

      } catch (error) {
        console.error('❌ formatGps - Error al formatear GPS:', error, 'Data original:', gpsData);

        // Fallback mejorado: intentar extraer nombres con regex más robusto
        if (typeof gpsData === 'string' && (gpsData.includes('nombre') || gpsData.includes('\\\"nombre\\\"'))) {
          console.log('🔧 formatGps - Intentando regex fallback');
          try {
            // Regex que maneja múltiples niveles de escapado incluyendo el caso específico \\\"
            const nombreRegex = /\\*\"*nombre\\*\"*\s*:\s*\\*\"([^\"\\]+)\\*\"*/;
            const provinciaRegex = /\\*\"*provincia\\*\"*\s*:\s*\{[^}]*\\*\"*nombre\\*\"*\s*:\s*\\*\"([^\"\\]+)\\*\"*/;
            const departamentoRegex = /\\*\"*departamento\\*\"*\s*:\s*\{[^}]*\\*\"*nombre\\*\"*\s*:\s*\\*\"([^\"\\]+)\\*\"*/;

            const nombreMatch = gpsData.match(nombreRegex);
            const provinciaMatch = gpsData.match(provinciaRegex);
            const departamentoMatch = gpsData.match(departamentoRegex);

            console.log('🔍 formatGps - Regex results:', { nombreMatch, provinciaMatch, departamentoMatch });

            if (nombreMatch && provinciaMatch) {
              const departamento = departamentoMatch ? `, ${departamentoMatch[1]}` : '';
              const result = `${nombreMatch[1]}${departamento}, ${provinciaMatch[1]}`;
              console.log('✅ formatGps - Fallback exitoso:', result);
              return result;
            }

            // Solo nombre sin provincia
            if (nombreMatch) {
              console.log('✅ formatGps - Solo nombre encontrado:', nombreMatch[1]);
              return nombreMatch[1];
            }
          } catch (e) {
            console.error('❌ formatGps - Error en regex fallback:', e);
          }
        }

        // Último fallback: mostrar primeros caracteres del JSON para debug
        if (typeof gpsData === 'string' && gpsData.length > 50) {
          console.log('⚠️ formatGps - Devolviendo fallback para datos largos');
          const preview = gpsData.substring(0, 100);
          return `Ubicación (debug: ${preview}...)`;
        }

        console.log('⚠️ formatGps - Devolviendo fallback final');
        return 'Ubicación no disponible';
      }
    },

    /**
     * Formatea datos del clima
     * @param {Object|string} climaData - Datos del clima
     * @returns {Object|null} Datos del clima formateados
     */
    formatClima(climaData) {
      if (!climaData) return null;
      try {
        const data = typeof climaData === 'string' ? JSON.parse(climaData) : climaData;
        if (!data.current?.temp_c || !data.current?.condition) {
          throw new Error('Datos del clima incompletos');
        }
        return {
          temp: Math.round(data.current.temp_c),
          icon: data.current.condition.icon,
          condition: data.current.condition.text
        };
      } catch (error) {
        console.error('Error al formatear clima:', error);
        return null;
      }
    },

    getEventIcon(event) {
      // Determinar icono basado en el título y contenido del evento
      const title = (event.title || '').toLowerCase();
      const content = (event.content || '').toLowerCase();
      const text = `${title} ${content}`;

      // Iconos para tipos específicos de eventos
      if (text.includes('reunión') || text.includes('meeting') || text.includes('junta')) {
        return 'group';
      }
      if (text.includes('cumpleaños') || text.includes('birthday') || text.includes('aniversario')) {
        return 'cake';
      }
      if (text.includes('trabajo') || text.includes('work') || text.includes('oficina')) {
        return 'work';
      }
      if (text.includes('doctor') || text.includes('médico') || text.includes('hospital') || text.includes('salud')) {
        return 'local_hospital';
      }
      if (text.includes('viaje') || text.includes('trip') || text.includes('vacaciones') || text.includes('vacation')) {
        return 'flight';
      }
      if (text.includes('comida') || text.includes('restaurante') || text.includes('cena') || text.includes('lunch') || text.includes('dinner')) {
        return 'restaurant';
      }
      if (text.includes('estudio') || text.includes('study') || text.includes('clase') || text.includes('course') || text.includes('universidad')) {
        return 'school';
      }
      if (text.includes('ejercicio') || text.includes('gym') || text.includes('deporte') || text.includes('sport') || text.includes('fitness')) {
        return 'fitness_center';
      }
      if (text.includes('compras') || text.includes('shopping') || text.includes('tienda')) {
        return 'shopping_cart';
      }
      if (text.includes('casa') || text.includes('home') || text.includes('hogar') || text.includes('familia')) {
        return 'home';
      }
      if (text.includes('llamada') || text.includes('call') || text.includes('teléfono') || text.includes('phone')) {
        return 'phone';
      }
      if (text.includes('presentación') || text.includes('presentation') || text.includes('conferencia')) {
        return 'slideshow';
      }
      if (text.includes('entrega') || text.includes('deadline') || text.includes('entregar')) {
        return 'assignment_turned_in';
      }
      if (text.includes('recordatorio') || text.includes('reminder')) {
        return 'notifications';
      }
      if (text.includes('cita') || text.includes('appointment')) {
        return 'schedule';
      }

      // Iconos basados en ubicación (si tiene GPS)
      if (event.gps) {
        const gpsText = (event.gps || '').toLowerCase();
        if (gpsText.includes('hospital') || gpsText.includes('clínica')) {
          return 'local_hospital';
        }
        if (gpsText.includes('restaurante') || gpsText.includes('bar') || gpsText.includes('café')) {
          return 'restaurant';
        }
        if (gpsText.includes('tienda') || gpsText.includes('mall') || gpsText.includes('centro comercial')) {
          return 'shopping_cart';
        }
        if (gpsText.includes('oficina') || gpsText.includes('empresa')) {
          return 'business';
        }
        if (gpsText.includes('universidad') || gpsText.includes('escuela') || gpsText.includes('colegio')) {
          return 'school';
        }
        if (gpsText.includes('gimnasio') || gpsText.includes('deportivo')) {
          return 'fitness_center';
        }
        // Si tiene ubicación pero no coincide con ninguna categoría
        return 'place';
      }

      // Icono por defecto basado en la hora
      if (event.start) {
        const hour = new Date(event.start).getHours();
        if (hour >= 6 && hour < 12) {
          return 'wb_sunny'; // Mañana
        } else if (hour >= 12 && hour < 18) {
          return 'wb_sunny'; // Tarde
        } else {
          return 'brightness_2'; // Noche
        }
      }

      // Icono por defecto
      return 'event';
    },

    openAddCalendar() {
      // Emitir evento al CarouselCalendar para abrir el modal en modo crear
      if (this.$refs.calendarCarousel) {
        // Asegurar que está en modo "crear" (sin calendario existente)
        this.$refs.calendarCarousel.isCreatingNew = true;
        this.$refs.calendarCarousel.showModal = true;
      }
    },

    async seleccionarCalendario(calendario) {
      this.calendarioSeleccionado = calendario;
      if (calendario && calendario.id) {
        await this.fetchEventos(calendario.id);
      }
    },

    async handleCalendarSave(calendarData) {
      try {
        await this.guardarCalendario(calendarData);
        await this.fetchCalendarios();
        if (!this.calendarioSeleccionado && this.calendarios.length > 0) {
          this.seleccionarCalendario(this.calendarios[0]);
        }
      } catch (error) {
        console.error('Error al guardar el calendario:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Hubo un error al guardar el calendario.',
          position: 'top'
        });
      }
    },

    async handleCalendarDelete(calendar) {
      const confirmed = await useConfirm('¿Estás seguro de eliminar este calendario? Se eliminarán todos los eventos asociados.');
      if (!confirmed) return;

      try {
        const wasSelectedCalendar = this.calendarioSeleccionado?.id === calendar.id;
        
        await this.eliminarCalendario(calendar.id);
        await this.fetchCalendarios();
        
        // Si se eliminó el calendario seleccionado o no hay ninguno seleccionado
        if (wasSelectedCalendar || !this.calendarioSeleccionado) {
          if (this.calendarios.length > 0) {
            await this.seleccionarCalendario(this.calendarios[0]);
          } else {
            // No hay calendarios disponibles
            this.calendarioSeleccionado = null;
            this.eventosParaCalendario = [];
          }
        }
      } catch (error) {
        console.error('Error al eliminar el calendario:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Hubo un error al eliminar el calendario.',
          position: 'top'
        });
      }
    },

    /**
     * Maneja el clic en una notificación de evento
     * @param {CustomEvent} event - Evento de notificación
     */
    handleNotificationEventClick(event) {
      const { evento } = event.detail;
      
      if (evento) {
        // Navegar al evento en el calendario
        this.editEvent(evento);
        
        // Si el calendario está asociado, asegurarse de que esté seleccionado
        if (evento.calendario_id && this.calendarios.length > 0) {
          const calendarioDelEvento = this.calendarios.find(c => c.id === evento.calendario_id);
          if (calendarioDelEvento && this.calendarioSeleccionado?.id !== evento.calendario_id) {
            this.seleccionarCalendario(calendarioDelEvento);
          }
        }
        
        // Enfocar el evento en el calendario si es posible
        if (this.$refs.vuecal && evento.fechaVencimiento) {
          try {
            const eventDate = new Date(evento.fechaVencimiento + 'T' + (evento.horaVencimiento || '00:00'));
            this.$refs.vuecal.goToDate(eventDate);
          } catch (error) {
            console.error('Error al navegar a la fecha del evento:', error);
          }
        }
      }
    }
  },
  
  watch: {
    showCalendarModal(newVal) {
      if (newVal) {
        this.openAddCalendar();
        this.$emit('calendar-modal-opened');
      }
    },
    modalVisible(newVal) {
      // Emitir evento global cuando el modal de evento se abre/cierra
      if (newVal) {
        window.dispatchEvent(new CustomEvent('modal-opened', {
          detail: { source: 'event-modal', type: 'event' }
        }));
      } else {
        window.dispatchEvent(new CustomEvent('modal-closed', {
          detail: { source: 'event-modal', type: 'event' }
        }));
      }
    }
  },
  async created() {
    // Inicializar el servicio de notificaciones
    eventNotificationService.initialize();
    
    // Inicializar fecha seleccionada para el date picker
    this.selectedDate = new Date().toISOString().split('T')[0];
    
    await this.fetchCalendarios();
    if (this.calendarios.length > 0 && !this.calendarioSeleccionado) {
      await this.seleccionarCalendario(this.calendarios[0]);
    }
    
    // Programar notificaciones para eventos existentes
    if (this.eventosParaCalendario.length > 0) {
      eventNotificationService.rescheduleAllNotifications(this.eventosParaCalendario);
    }
    
    // Escuchar eventos de notificación para navegar a eventos
    window.addEventListener('notification-event-click', this.handleNotificationEventClick);
    
    // Verificar si se debe abrir el modal de crear calendario
    if (this.$route.query.openModal === 'true') {
      this.$nextTick(() => {
        this.openAddCalendar();
      });
    }
  },

  beforeUnmount() {
    // Limpiar listeners y recursos
    window.removeEventListener('notification-event-click', this.handleNotificationEventClick);
    eventNotificationService.destroy();
  }
};
</script>

<style scoped>
.calendar-view {
  padding: 8px 16px;
  max-width: 1200px;
  margin: 0 auto;
  background-color: #f9fafb;
}

.calendar-header h1 {
  color: #111827;
  margin-bottom: 0;
}

.calendars-section,
.calendar-card,
.events-panel {
  background: white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.custom-vuecal {
  border-radius: 8px;
  overflow: hidden;
  height: 500px;
}

.calendar-display {
  height: 500px;
  border-radius: 8px;
}

.custom-vuecal .vuecal__header {
  background-color: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.custom-vuecal .vuecal__nombre {
  font-weight: 600;
  color: #374151;
}

.custom-vuecal .vuecal__event {
  border-left: 2px solid var(--event-color, #4caf50);
  padding: 4px 8px;
  border-radius: 4px;
  margin-bottom: 4px;
  font-size: 14px;
  background-color: rgba(76, 175, 80, 0.1);
}

.custom-vuecal .vuecal__event-nombre {
  font-weight: 500;
}

.custom-vuecal .vuecal__event-time {
  font-size: 12px;
  opacity: 0.8;
}

.event-item {
  border-radius: 8px;
  transition: all 0.2s ease;
}

.event-item:hover {
  background-color: #f3f4f6;
  transform: translateX(4px);
}

.event-avatar {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  transition: all 0.2s ease;
}

.event-avatar:hover {
  transform: scale(1.05);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

/* Event content with weather */
.event-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
  height: 100%;
  overflow: hidden;
}

.event-header {
  display: flex;
  align-items: center;
  gap: 4px;
  margin-bottom: 2px;
}

.event-icon {
  flex-shrink: 0;
  opacity: 0.9;
}

.event-title {
  font-weight: 500;
  font-size: 12px;
  line-height: 1.2;
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.event-weather {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  opacity: 0.8;
}

.weather-icon-small {
  width: 12px;
  height: 12px;
  flex-shrink: 0;
}

.weather-temp {
  font-weight: 500;
  white-space: nowrap;
}

.empty-events-card {
  background: white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Dark mode support */
.body--dark .calendar-view {
  background-color: #111827;
}

.body--dark .calendar-header h1 {
  color: white;
}

.body--dark .calendars-section,
.body--dark .calendar-card,
.body--dark .events-panel {
  background: #1d1d1d;
  color: white;
}

.body--dark .custom-vuecal .vuecal__header {
  background-color: #374151;
  border-bottom-color: #4b5563;
}

.body--dark .custom-vuecal .vuecal__nombre {
  color: #f9fafb;
}

/* Estilos adicionales para VueCal en modo oscuro */
.body--dark .custom-vuecal {
  background-color: #1f2937;
  color: #f9fafb;
}

.body--dark .custom-vuecal .vuecal__body {
  background-color: #1f2937;
}

.body--dark .custom-vuecal .vuecal__cell {
  background-color: #1f2937;
  border-color: #374151;
  color: #f9fafb;
}

.body--dark .custom-vuecal .vuecal__cell.today {
  background-color: #374151;
}

.body--dark .custom-vuecal .vuecal__cell.selected {
  background-color: #4b5563;
}

.body--dark .custom-vuecal .vuecal__event {
  background-color: rgba(59, 130, 246, 0.2);
  border-left-color: #3b82f6;
  color: #f9fafb;
}

.body--dark .custom-vuecal .vuecal__weekdays-headings {
  background-color: #374151;
  color: #f9fafb;
}

.body--dark .custom-vuecal .vuecal__title {
  color: #f9fafb;
}

.body--dark .custom-vuecal .vuecal__arrow {
  color: #f9fafb;
}

.body--dark .custom-vuecal .vuecal__menu {
  background-color: #374151;
  color: #f9fafb;
}

.body--dark .custom-vuecal .vuecal__menu li {
  color: #f9fafb;
}

.body--dark .custom-vuecal .vuecal__menu li:hover {
  background-color: #4b5563;
}

/* Clase específica para dark theme */
.custom-vuecal.dark-theme {
  background-color: #1f2937 !important;
  color: #f9fafb !important;
}

.custom-vuecal.dark-theme .vuecal__header {
  background-color: #374151 !important;
  border-bottom-color: #4b5563 !important;
  color: #f9fafb !important;
}

.custom-vuecal.dark-theme .vuecal__body {
  background-color: #1f2937 !important;
}

.custom-vuecal.dark-theme .vuecal__cell {
  background-color: #1f2937 !important;
  border-color: #374151 !important;
  color: #f9fafb !important;
}

.custom-vuecal.dark-theme .vuecal__cell.today {
  background-color: #374151 !important;
}

.custom-vuecal.dark-theme .vuecal__cell.selected {
  background-color: #4b5563 !important;
}

.custom-vuecal.dark-theme .vuecal__event {
  background-color: rgba(59, 130, 246, 0.3) !important;
  border-left-color: #3b82f6 !important;
  color: #f9fafb !important;
}

.custom-vuecal.dark-theme .vuecal__weekdays-headings {
  background-color: #374151 !important;
  color: #f9fafb !important;
}

.custom-vuecal.dark-theme .vuecal__title {
  color: #f9fafb !important;
}

.custom-vuecal.dark-theme .vuecal__arrow {
  color: #f9fafb !important;
}

.custom-vuecal.dark-theme .vuecal__menu {
  background-color: #374151 !important;
  color: #f9fafb !important;
}

.custom-vuecal.dark-theme .vuecal__menu li {
  color: #f9fafb !important;
}

.custom-vuecal.dark-theme .vuecal__menu li:hover {
  background-color: #4b5563 !important;
}

.custom-vuecal.dark-theme .vuecal__time-column {
  background-color: #1f2937 !important;
  color: #f9fafb !important;
  border-color: #374151 !important;
}

.custom-vuecal.dark-theme .vuecal__time-cell {
  color: #f9fafb !important;
}

.custom-vuecal.dark-theme .vuecal__cell-content {
  color: #f9fafb !important;
}

.custom-vuecal.dark-theme .vuecal__cell-date {
  color: #f9fafb !important;
}

/* Forzar colores en elementos específicos que puedan estar sobrescritos */
.custom-vuecal.dark-theme * {
  border-color: #374151 !important;
}

.custom-vuecal.dark-theme .vuecal__bg {
  background-color: #1f2937 !important;
}

/* Estilos para el template de eventos en modo oscuro */
.body--dark .event-content {
  color: #f9fafb;
}

.body--dark .event-header {
  color: #f9fafb;
}

.body--dark .event-title {
  color: #f9fafb;
}

.body--dark .event-icon {
  color: #e5e7eb;
}

.body--dark .event-weather {
  color: #d1d5db;
}

.body--dark .weather-temp {
  color: #f9fafb;
}

/* Estilos para el template con clase dark-theme */
.custom-vuecal.dark-theme .event-content {
  color: #f9fafb !important;
}

.custom-vuecal.dark-theme .event-header {
  color: #f9fafb !important;
}

.custom-vuecal.dark-theme .event-title {
  color: #f9fafb !important;
}

.custom-vuecal.dark-theme .event-icon {
  color: #e5e7eb !important;
}

.custom-vuecal.dark-theme .event-weather {
  color: #d1d5db !important;
}

.custom-vuecal.dark-theme .weather-temp {
  color: #f9fafb !important;
}

.body--dark .event-item:hover {
  background-color: #374151;
}

.body--dark .empty-events-card {
  background: #1f2937;
  color: white;
}

/* Mobile responsive optimizations */
@media (max-width: 768px) {
  .calendar-view {
    padding: 8px 12px;
    padding-top: 20px; /* Menos padding porque ya hay espacio del header fijo */
    padding-bottom: 100px; /* Espacio para FAB */
  }

  /* Header más compacto en mobile */
  .calendar-header h1 {
    font-size: 1.5rem;
    margin-bottom: 16px;
  }

  /* Layout de calendarios en mobile */
  .calendars-section {
    margin-bottom: 16px;
    border-radius: 12px;
  }

  .calendars-section :deep(.q-card-section) {
    padding: 8px 12px;
  }


  /* Layout principal responsive */
  .row.q-gutter-sm {
    flex-direction: column;
    gap: 16px;
  }

  /* Calendario principal en mobile */
  .calendar-card {
    order: 2;
  }

  .calendar-card :deep(.q-card-section) {
    padding: 12px;
  }

  /* Controles de calendario más compactos */
  .row.items-center.justify-between {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }

  .row.items-center.justify-between > .row {
    justify-content: center;
  }

  /* Botones de vista más pequeños en mobile */
  .q-btn-group .q-btn {
    min-width: 40px;
    padding: 8px;
  }

  /* Calendar display más pequeño */
  .custom-vuecal {
    height: 350px; /* Reducido para mejor vista en mobile */
  }

  .calendar-display {
    height: 350px;
  }

  /* Panel de eventos en mobile */
  .events-panel {
    order: 1;
    max-height: 300px;
  }

  .events-panel :deep(.q-card-section) {
    padding: 12px;
  }

  /* Event items más grandes para touch */
  .event-item {
    padding: 12px;
    margin-bottom: 8px;
    border-radius: 12px;
  }

  .event-item:hover {
    transform: none; /* Eliminar transform en mobile */
  }

  /* Avatar de evento más grande en mobile */
  .event-avatar {
    width: 44px !important;
    height: 44px !important;
    font-size: 22px !important;
  }

  /* Weather icons más grandes en mobile */
  .weather-icon-small {
    width: 14px;
    height: 14px;
  }

  .event-weather {
    font-size: 11px;
  }

  /* Botones más grandes para touch */
  .row.justify-end :deep(.q-btn) {
    min-width: 44px;
    min-height: 44px;
    padding: 8px 16px;
  }
}

@media (max-width: 480px) {
  .calendar-view {
    padding: 4px 8px;
    padding-top: 16px; /* Menos padding para pantallas pequeñas */
    padding-bottom: 90px;
  }

  /* Header aún más compacto */
  .calendar-header h1 {
    font-size: 1.3rem;
    margin-bottom: 12px;
  }

  /* Calendarios section más compacta */
  .calendars-section :deep(.q-card-section) {
    padding: 8px 12px;
  }

  /* Título de sección más pequeño */
  .text-h6 {
    font-size: 1.1rem;
  }

  /* Controles más compactos en pantallas pequeñas */
  .row.items-center.justify-between {
    margin-bottom: 8px;
  }

  .q-btn-group .q-btn {
    min-width: 36px;
    padding: 6px;
    font-size: 0.8rem;
  }

  /* Calendar más pequeño en pantallas tiny */
  .custom-vuecal {
    height: 300px; /* Más pequeño para pantallas pequeñas */
  }

  .calendar-display {
    height: 300px;
  }

  /* Panel de eventos más compacto */
  .events-panel {
    max-height: 250px;
  }

  .event-item {
    padding: 10px;
    margin-bottom: 6px;
  }

  /* Texto más pequeño en eventos */
  .event-item :deep(.q-item-label) {
    font-size: 0.9rem;
  }

  .event-item :deep(.text-caption) {
    font-size: 0.75rem;
  }

  /* Botones más compactos */
  .row.justify-end :deep(.q-btn) {
    min-width: 40px;
    min-height: 40px;
    padding: 6px 12px;
    font-size: 0.85rem;
  }
}

/* Mobile landscape optimizations */
@media (max-width: 896px) and (orientation: landscape) {
  .calendar-view {
    padding: 4px 8px;
    padding-bottom: 70px;
  }

  /* Layout horizontal en landscape */
  .row.q-gutter-sm {
    flex-direction: row;
    gap: 12px;
  }

  .calendar-card {
    order: 1;
    flex: 2;
  }

  .events-panel {
    order: 2;
    flex: 1;
    max-height: none;
  }

  /* Calendar más bajo en landscape */
  .custom-vuecal {
    height: 300px;
  }

  .calendar-display {
    height: 300px;
  }
}

/* Touch device optimizations */
@media (pointer: coarse) {
  /* Eliminar hover effects */
  .event-item:hover {
    background-color: transparent;
    transform: none;
  }

  /* Feedback táctil */
  .event-item:active {
    background-color: #f3f4f6;
    transform: scale(0.98);
  }

  .body--dark .event-item:active {
    background-color: #374151;
  }

  /* Botones más accesibles al toque */
  :deep(.q-btn) {
    min-width: 44px;
    min-height: 44px;
  }

  /* Calendar touch friendly */
  .custom-vuecal :deep(.vuecal__cell) {
    min-height: 44px;
  }
}

/* Accessibility improvements for mobile */
@media (max-width: 768px) {
  /* Focus visible mejorado */
  .event-item:focus-visible {
    outline: 2px solid var(--q-primary);
    outline-offset: 2px;
  }

  /* Contraste mejorado para textos pequeños */
  .event-item :deep(.text-caption) {
    color: #6b7280;
  }

  .body--dark .event-item :deep(.text-caption) {
    color: #9ca3af;
  }

  /* Mejorar legibilidad de títulos */
  .text-h6 {
    font-weight: 600;
    color: #374151;
  }

  .body--dark .text-h6 {
    color: #f9fafb;
  }
}
</style>