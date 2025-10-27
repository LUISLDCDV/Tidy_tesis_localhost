<template>
  <q-dialog
    v-model="showDialog"
    persistent
    :maximized="$q.screen.lt.sm"
    transition-show="slide-up"
    transition-hide="slide-down"
  >
    <q-card class="map-modal" :class="{ 'mobile-modal': $q.screen.lt.sm }">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">{{ $t('maps.selectLocation') }}</div>
        <q-space />
        <q-btn icon="close" flat round dense @click="closeModal" />
      </q-card-section>

      <q-card-section class="map-container">
        <!-- Mapa usando OpenStreetMap con Leaflet -->
        <div
          id="map"
          class="map-display"
          :style="{ height: mapHeight }"
        ></div>

        <!-- Controles de búsqueda -->
        <div class="map-controls">
          <q-input
            v-model="searchQuery"
            :label="$t('maps.searchPlaces')"
            outlined
            dense
            clearable
            @keyup.enter="searchLocation"
          >
            <template v-slot:append>
              <q-btn
                icon="search"
                flat
                round
                @click="searchLocation"
                :loading="searching"
              />
            </template>
          </q-input>

          <!-- Información de ubicación seleccionada -->
          <div v-if="selectedLocation" class="location-info q-mt-sm">
            <q-card flat bordered class="q-pa-sm">
              <div class="text-subtitle2">{{ $t('maps.selectedLocation') }}</div>
              <div class="text-body2">
                <div><strong>{{ $t('maps.coordinates') }}:</strong> {{ selectedLocation.lat.toFixed(6) }}, {{ selectedLocation.lng.toFixed(6) }}</div>
                <div v-if="selectedLocation.address"><strong>{{ $t('maps.address') }}:</strong> {{ selectedLocation.address }}</div>
              </div>
            </q-card>
          </div>
        </div>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          flat
          :label="$t('common.cancel')"
          @click="closeModal"
          color="grey"
        />
        <q-btn
          :label="$t('common.select')"
          @click="confirmSelection"
          color="primary"
          :disable="!selectedLocation"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
export default {
  name: 'MapSelector',

  emits: ['location-selected', 'update:modelValue'],

  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    initialLocation: {
      type: Object,
      default: () => ({ lat: -34.6037, lng: -58.3816 }) // Buenos Aires por defecto
    },
    allowMultiple: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      map: null,
      marker: null,
      selectedLocation: null,
      searchQuery: '',
      searching: false,
      leafletLoaded: false
    };
  },

  computed: {
    showDialog: {
      get() {
        return this.modelValue;
      },
      set(value) {
        this.$emit('update:modelValue', value);
      }
    },

    mapHeight() {
      return this.$q.screen.lt.sm ? '50vh' : '400px';
    }
  },

  watch: {
    showDialog(newVal) {
      if (newVal) {
        this.$nextTick(() => {
          this.initializeMap();
        });
      }
    }
  },

  methods: {
    async loadLeaflet() {
      if (this.leafletLoaded) return;

      // Cargar CSS de Leaflet
      const link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
      document.head.appendChild(link);

      // Cargar JS de Leaflet
      const script = document.createElement('script');
      script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';

      return new Promise((resolve, reject) => {
        script.onload = () => {
          this.leafletLoaded = true;
          resolve();
        };
        script.onerror = reject;
        document.head.appendChild(script);
      });
    },

    async initializeMap() {
      try {
        await this.loadLeaflet();

        if (this.map) {
          this.map.remove();
        }

        // Inicializar mapa
        this.map = window.L.map('map').setView(
          [this.initialLocation.lat, this.initialLocation.lng],
          13
        );

        // Agregar capa de tiles de OpenStreetMap
        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 18,
          attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);

        // Crear marcador inicial
        this.marker = window.L.marker([this.initialLocation.lat, this.initialLocation.lng], {
          draggable: true
        }).addTo(this.map);

        // Evento de click en el mapa
        this.map.on('click', (e) => {
          this.setLocation(e.latlng.lat, e.latlng.lng);
        });

        // Evento de arrastre del marcador
        this.marker.on('dragend', (e) => {
          const position = e.target.getLatLng();
          this.setLocation(position.lat, position.lng);
        });

        // Configurar ubicación inicial
        this.setLocation(this.initialLocation.lat, this.initialLocation.lng);

      } catch (error) {
        console.error('Error inicializando mapa:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar el mapa. Verifica tu conexión a internet.',
          position: 'top'
        });
      }
    },

    setLocation(lat, lng, address = null) {
      this.selectedLocation = {
        lat: parseFloat(lat),
        lng: parseFloat(lng),
        address
      };

      if (this.marker) {
        this.marker.setLatLng([lat, lng]);
      }

      // Obtener dirección si no se proporcionó
      if (!address) {
        this.reverseGeocode(lat, lng);
      }
    },

    async reverseGeocode(lat, lng) {
      try {
        const response = await fetch(
          `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
        );

        if (response.ok) {
          const data = await response.json();
          if (data.display_name) {
            this.selectedLocation.address = data.display_name;
          }
        }
      } catch (error) {
        console.warn('Error en geocodificación inversa:', error);
      }
    },

    async searchLocation() {
      if (!this.searchQuery.trim()) return;

      this.searching = true;

      try {
        const response = await fetch(
          `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchQuery)}&limit=1&addressdetails=1`
        );

        if (response.ok) {
          const data = await response.json();

          if (data.length > 0) {
            const result = data[0];
            const lat = parseFloat(result.lat);
            const lng = parseFloat(result.lon);

            this.setLocation(lat, lng, result.display_name);

            // Centrar mapa en la ubicación encontrada
            if (this.map) {
              this.map.setView([lat, lng], 15);
            }

            this.$q.notify({
              type: 'positive',
              message: 'Ubicación encontrada',
              position: 'top'
            });
          } else {
            this.$q.notify({
              type: 'warning',
              message: 'No se encontraron resultados',
              position: 'top'
            });
          }
        }
      } catch (error) {
        console.error('Error en búsqueda:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error al buscar la ubicación',
          position: 'top'
        });
      } finally {
        this.searching = false;
      }
    },

    getCurrentLocation() {
      if (!navigator.geolocation) {
        this.$q.notify({
          type: 'negative',
          message: 'Geolocalización no soportada',
          position: 'top'
        });
        return;
      }

      this.$q.loading.show({
        message: 'Obteniendo ubicación actual...'
      });

      navigator.geolocation.getCurrentPosition(
        (position) => {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;

          this.setLocation(lat, lng);

          if (this.map) {
            this.map.setView([lat, lng], 15);
          }

          this.$q.loading.hide();
          this.$q.notify({
            type: 'positive',
            message: 'Ubicación actual obtenida',
            position: 'top'
          });
        },
        (error) => {
          this.$q.loading.hide();
          console.error('Error obteniendo ubicación:', error);
          this.$q.notify({
            type: 'negative',
            message: 'Error al obtener ubicación actual',
            position: 'top'
          });
        },
        {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 0
        }
      );
    },

    confirmSelection() {
      if (this.selectedLocation) {
        this.$emit('location-selected', {
          ...this.selectedLocation,
          coordinates: `${this.selectedLocation.lat.toFixed(6)}, ${this.selectedLocation.lng.toFixed(6)}`
        });
        this.closeModal();
      }
    },

    closeModal() {
      this.showDialog = false;
      this.searchQuery = '';
      this.selectedLocation = null;

      if (this.map) {
        setTimeout(() => {
          this.map.remove();
          this.map = null;
          this.marker = null;
        }, 300);
      }
    }
  },

  beforeUnmount() {
    if (this.map) {
      this.map.remove();
    }
  }
};
</script>

<style scoped>
.map-modal {
  min-width: 600px;
  max-width: 800px;
}

.mobile-modal {
  min-width: 100vw !important;
  max-width: 100vw !important;
  min-height: 100vh !important;
  border-radius: 0 !important;
}

.map-container {
  position: relative;
  padding: 0 !important;
}

.map-display {
  width: 100%;
  min-height: 400px;
  border-radius: 4px;
}

.map-controls {
  position: absolute;
  top: 10px;
  left: 10px;
  right: 10px;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.95);
  padding: 16px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.location-info {
  margin-top: 8px;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .map-modal {
    min-width: 100vw !important;
    max-width: 100vw !important;
    min-height: 100vh !important;
    margin: 0 !important;
    border-radius: 0 !important;
  }

  .map-display {
    min-height: 50vh;
  }

  .map-controls {
    position: relative;
    top: 0;
    left: 0;
    right: 0;
    margin: 16px;
    background: white;
    border: 1px solid #e0e0e0;
  }

  .map-container {
    display: flex;
    flex-direction: column;
  }
}

/* Leaflet override for better mobile support */
:deep(.leaflet-control-container) {
  pointer-events: auto;
}

:deep(.leaflet-control-zoom) {
  margin-left: 10px;
  margin-top: 80px;
}

:deep(.leaflet-popup-content-wrapper) {
  border-radius: 8px;
}

/* Dark mode support */
.body--dark .map-controls {
  background: rgba(31, 41, 55, 0.95);
  border: 1px solid #374151;
}

.body--dark .location-info .q-card {
  background: #374151;
  color: white;
}
</style>