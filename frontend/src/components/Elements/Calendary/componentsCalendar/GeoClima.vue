<template>
  <q-card class="weather-container" flat bordered>
    <q-card-section>
      <GeoAutocomplete
        ref="geoAutocomplete"
        @locationSelected="handleLocation"
      />
      
      <!-- Loading state -->
      <div v-if="loading" class="text-center q-mt-md">
        <q-spinner size="48px" color="primary" />
        <div class="text-body2 text-grey-6 q-mt-sm">
          Obteniendo pronóstico del clima...
        </div>
      </div>
      
      <!-- No location selected or missing date -->
      <div v-else-if="!currentLocation || !fechaVencimiento" class="text-center q-mt-md">
        <q-icon name="info" size="48px" color="grey-4" />
        <div class="text-body2 text-grey-6 q-mt-sm">
          <div v-if="!currentLocation && !fechaVencimiento">
            Selecciona una ubicación y fecha para ver el pronóstico
          </div>
          <div v-else-if="!currentLocation">
            Selecciona una ubicación para ver el pronóstico del clima
          </div>
          <div v-else-if="!fechaVencimiento">
            Selecciona una fecha para ver el pronóstico del clima
          </div>
        </div>
      </div>
      
      <!-- Date too far in future -->
      <div v-else-if="isFutureDateTooFar" class="text-center q-mt-md">
        <q-icon name="schedule" size="48px" color="grey-4" />
        <div class="text-body2 text-grey-6 q-mt-sm">
          El pronóstico no está disponible para fechas más allá de 7 días
        </div>
      </div>
      
      <!-- Error state -->
      <div v-else-if="error" class="text-center q-mt-md">
        <q-icon name="error_outline" size="48px" color="negative" />
        <div class="text-body2 text-negative q-mt-sm">
          {{ error }}
        </div>
        <q-btn 
          v-if="canFetchWeather"
          @click="fetchWeather(currentLocation)" 
          color="primary" 
          flat 
          size="sm" 
          class="q-mt-sm"
        >
          <q-icon name="refresh" class="q-mr-xs" />
          Reintentar
        </q-btn>
      </div>
      
      <!-- Weather display -->
      <div v-else-if="weather" class="q-mt-md">
        <div class="row items-center justify-between">
          <div class="col">
            <div class="text-h6 text-weight-medium text-grey-9">{{ weather.location.name }}</div>
            <div class="text-h3 text-weight-bold text-grey-9">{{ weather.current.temp_c }}°C</div>
            <div class="text-body2 text-grey-7">{{ weather.current.condition.text }}</div>
            <div class="text-caption text-grey-5">{{ formatDateTime }}</div>
            
            <!-- Additional weather details -->
            <div v-if="weather.current.humidity" class="row q-gutter-md q-mt-xs">
              <div class="text-caption text-grey-5">
                <q-icon name="water_drop" size="12px" class="q-mr-xs" />
                Humedad: {{ weather.current.humidity }}%
              </div>
              <div v-if="weather.current.wind_kph" class="text-caption text-grey-5">
                <q-icon name="air" size="12px" class="q-mr-xs" />
                Viento: {{ weather.current.wind_kph }} km/h
              </div>
            </div>
          </div>
          <div class="weather-icon-container">
            <q-img 
              :src="'https:' + weather.current.condition.icon" 
              alt="Clima" 
              style="width: 64px; height: 64px;"
              class="weather-icon"
            />
            <q-btn 
              @click="refreshWeather" 
              icon="refresh" 
              flat 
              round 
              size="sm" 
              class="refresh-btn"
              :loading="loading"
            />
          </div>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script>
import GeoAutocomplete from "./GeoAutocomplete.vue";
import axios from "axios";

export default {
  components: {
    GeoAutocomplete,
  },
  props: {
    fechaVencimiento: {
      type: String,
      default: null
    },
    horaVencimiento: {
      type: String,
      default: null
    },
    initialLocation: {
      type: [Object, String],
      default: null
    }
  },
  data() {
    return {
      weather: null,
      currentLocation: null,
      error: null,
      loading: false,
      retryCount: 0,
      maxRetries: 3,
      weatherCache: new Map(),
      abortController: null
    };
  },
  computed: {
    isFutureDateTooFar() {
      if (!this.fechaVencimiento) return false;
      const eventDate = new Date(this.fechaVencimiento);
      const today = new Date();
      const diffTime = Math.abs(eventDate - today);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      return diffDays > 7;
    },
    isFutureDate() {
      if (!this.fechaVencimiento) return false;
      const eventDateTime = this.getEventDateTime();
      const now = new Date();
      return eventDateTime > now;
    },
    formatDateTime() {
      if (!this.fechaVencimiento || !this.horaVencimiento) return '';
      const date = new Date(this.fechaVencimiento + 'T' + this.horaVencimiento);
      return date.toLocaleString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    
    canFetchWeather() {
      return this.currentLocation && 
             this.fechaVencimiento && 
             !this.isFutureDateTooFar && 
             !this.loading;
    },
    
    cacheKey() {
      if (!this.currentLocation || !this.fechaVencimiento) return null;
      return `${this.currentLocation.nombre}-${this.currentLocation.provincia.nombre}-${this.fechaVencimiento}-${this.horaVencimiento || '00:00'}`;
    }
  },
  methods: {
    
    getEventDateTime() {
      if (!this.fechaVencimiento || !this.horaVencimiento) return null;
      return new Date(this.fechaVencimiento + 'T' + this.horaVencimiento);
    },

    async fetchWeather(location, forceRefresh = false) {
      // Limpiar error anterior
      this.error = null;

      if (!location) {
        console.log('❌ No se puede obtener clima: ubicación no seleccionada');
        return;
      }

      if (!this.fechaVencimiento) {
        console.log('❌ No se puede obtener clima: fecha no seleccionada');
        return;
      }
      
      if (this.isFutureDateTooFar) {
        this.weather = null;
        this.error = 'El pronóstico no está disponible para fechas más allá de 7 días';
        return;
      }

      // Verificar cache primero
      const cacheKey = this.cacheKey;
      if (!forceRefresh && cacheKey && this.weatherCache.has(cacheKey)) {
        const cachedData = this.weatherCache.get(cacheKey);
        // Verificar si el cache es válido (menos de 30 minutos)
        if (Date.now() - cachedData.timestamp < 30 * 60 * 1000) {
          this.weather = cachedData.data;
          this.error = null;
          this.$emit('update:clima', this.weather);
          return;
        }
      }

      // Cancelar petición anterior si existe
      if (this.abortController) {
        this.abortController.abort();
      }

      this.abortController = new AbortController();
      this.loading = true;
      this.error = null;
      
      const formattedQuery = `${location.nombre}, ${location.provincia.nombre}`;
      
      try {
        const endpoint = this.isFutureDate ? 'forecast.json' : 'current.json';
        const requestParams = {
          key: "3a3b8a045de94910a2c35318242411",
          q: formattedQuery,
          aqi: 'yes', // Incluir calidad del aire
          alerts: 'yes' // Incluir alertas meteorológicas
        };

        // Agregar parámetros específicos para pronósticos
        if (this.isFutureDate) {
          requestParams.dt = this.fechaVencimiento;
          requestParams.days = 7;
        }

        const response = await axios.get(
          `https://api.weatherapi.com/v1/${endpoint}`,
          {
            params: requestParams,
            signal: this.abortController.signal,
            timeout: 8000 // 8 segundos de timeout
          }
        );
        
        let weatherData;
        
        if (this.isFutureDate && response.data.forecast) {
          const forecastDay = response.data.forecast.forecastday.find(
            day => day.date === this.fechaVencimiento
          );
          
          if (forecastDay) {
            // Buscar la hora más cercana en el pronóstico por hora
            let tempData;
            if (this.horaVencimiento && forecastDay.hour) {
              const hour = parseInt(this.horaVencimiento);
              tempData = forecastDay.hour[hour] || forecastDay.day;
            } else {
              tempData = forecastDay.day;
            }

            weatherData = {
              location: response.data.location,
              current: {
                temp_c: tempData.temp_c || tempData.avgtemp_c,
                condition: tempData.condition,
                humidity: tempData.humidity,
                wind_kph: tempData.wind_kph || tempData.maxwind_kph,
                feelslike_c: tempData.feelslike_c,
                time: this.horaVencimiento
              },
              forecast_for: this.formatDateTime,
              alerts: response.data.alerts || []
            };
          } else {
            throw new Error('No se encontró pronóstico para la fecha seleccionada');
          }
        } else {
          weatherData = response.data;
        }
        
        this.weather = weatherData;
        this.retryCount = 0; // Reset retry count on success

        // Guardar en cache
        if (cacheKey) {
          this.weatherCache.set(cacheKey, {
            data: weatherData,
            timestamp: Date.now()
          });
        }

        console.log("🌟 GeoClima - Clima obtenido exitosamente:", this.weather);
        console.log("🔄 GeoClima - Emitiendo update:clima:", this.weather);
        this.$emit('update:clima', this.weather);
        
      } catch (error) {
        if (error.name === 'AbortError') {
          return; // Petición cancelada, no hacer nada
        }
        
        console.error("Error fetching weather:", error);
        
        // Manejo específico de errores
        if (error.code === 'ECONNABORTED' || error.message.includes('timeout')) {
          this.error = 'Tiempo de espera agotado. Verifica tu conexión a internet.';
        } else if (error.response?.status === 400) {
          this.error = 'Ubicación no encontrada para el pronóstico del clima.';
        } else if (error.response?.status === 401) {
          this.error = 'Error de autenticación con el servicio meteorológico.';
        } else if (error.response?.status === 403) {
          this.error = 'Límite de solicitudes excedido. Intenta más tarde.';
        } else if (error.response?.status >= 500) {
          this.error = 'Servicio meteorológico temporalmente no disponible.';
        } else if (!navigator.onLine) {
          this.error = 'Sin conexión a internet. Verifica tu conexión.';
        } else {
          this.error = error.response?.data?.error?.message || 'Error al obtener el pronóstico del clima.';
        }
        
        this.weather = null;
        
      } finally {
        this.loading = false;
        this.abortController = null;
      }
    },

    async handleLocation(location) {
      console.log("🌍 GeoClima - Ubicación recibida desde GeoAutocomplete:", location);
      this.currentLocation = location;
      console.log("🔄 GeoClima - Emitiendo update:location:", location);
      this.$emit('update:location', location);
      console.log("🌤️ GeoClima - Iniciando búsqueda del clima...");
      await this.fetchWeather(location);
    },

    /**
     * Refresca el clima actual
     */
    async refreshWeather() {
      if (this.currentLocation) {
        await this.fetchWeather(this.currentLocation, true);
      }
    },

    /**
     * Limpia el cache de clima
     */
    clearCache() {
      this.weatherCache.clear();
    },

    /**
     * Limpia todos los datos de clima
     */
    clearWeather() {
      this.weather = null;
      this.error = null;
      this.currentLocation = null;
      this.loading = false;
      this.retryCount = 0;
      
      if (this.abortController) {
        this.abortController.abort();
        this.abortController = null;
      }
    },

    /**
     * Intenta obtener el clima automáticamente si hay ubicación
     */
    async tryAutoFetchWeather() {
      if (this.canFetchWeather) {
        await this.fetchWeather(this.currentLocation);
      }
    }
  },
  watch: {
    async fechaVencimiento(newDate, oldDate) {
      console.log("Fecha cambiada:", newDate, "Anterior:", oldDate);
      if (newDate !== oldDate) {
        this.error = null;
        await this.tryAutoFetchWeather();
      }
    },
    
    async horaVencimiento(newTime, oldTime) {
      console.log("Hora cambiada:", newTime, "Anterior:", oldTime);
      if (newTime !== oldTime) {
        this.error = null;
        await this.tryAutoFetchWeather();
      }
    },

    async initialLocation(newLocation) {
      console.log("📍 GeoClima - Ubicación inicial recibida:", newLocation);
      if (newLocation) {
        try {
          // Parse the location if it's a string
          let locationData = newLocation;
          if (typeof newLocation === 'string') {
            locationData = JSON.parse(newLocation);
          }

          console.log("📍 GeoClima - Ubicación parseada:", locationData);
          this.currentLocation = locationData;

          // Update the GeoAutocomplete component with the location
          if (this.$refs.geoAutocomplete) {
            this.$refs.geoAutocomplete.setInitialLocation(locationData);
          }

          // Fetch weather for this location
          if (this.fechaVencimiento) {
            await this.fetchWeather(locationData);
          }
        } catch (error) {
          console.error("Error al procesar ubicación inicial:", error);
        }
      }
    },

    // Limpiar cache cuando cambia la ubicación
    currentLocation(newLocation, oldLocation) {
      if (newLocation !== oldLocation) {
        this.clearCache();
      }
    }
  },
  created() {
    this.clearWeather();
  },

  beforeUnmount() {
    this.clearWeather();
    this.clearCache();
  }
};
</script>

<style scoped>
.weather-container {
  background: #f9fafb;
  transition: all 0.3s ease;
}

.weather-container:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transform: translateY(-2px);
}

.weather-icon {
  border-radius: 8px;
  transition: all 0.2s ease;
}

.weather-icon:hover {
  transform: scale(1.1);
}

/* Dark mode support */
.body--dark .weather-container {
  background: #374151;
  color: white;
}

.body--dark .weather-container:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
}

/* Weather icon container with refresh button */
.weather-icon-container {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.refresh-btn {
  margin-top: 4px;
  opacity: 0.7;
  transition: opacity 0.2s ease;
}

.refresh-btn:hover {
  opacity: 1;
}

/* Loading and error states */
.text-center {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .weather-container {
    margin: 0 -8px;
  }
  
  .weather-icon {
    width: 48px !important;
    height: 48px !important;
  }

  .weather-icon-container {
    flex-direction: row;
    gap: 8px;
  }

  .refresh-btn {
    margin-top: 0;
  }

  .text-center {
    padding: 16px;
  }
}
</style>
