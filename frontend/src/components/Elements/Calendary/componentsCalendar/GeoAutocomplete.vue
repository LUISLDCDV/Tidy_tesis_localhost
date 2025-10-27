<template>
  <div class="geo-autocomplete">
    <q-input
      v-model="query"
      @update:model-value="debouncedFetchLocations"
      @focus="showDropdown = true"
      @blur="hideDropdownDelayed"
      @keydown.escape="hideDropdown"
      @keydown.down="navigateDown"
      @keydown.up="navigateUp"
      @keydown.enter="selectHighlighted"
      placeholder="Escribe una ubicación (ej: Buenos Aires, Córdoba)"
      label="Ubicación"
      filled
      dense
      :loading="loading"
      :error="!!error"
      :error-message="error"
      clearable
    >
      <template v-slot:prepend>
        <q-icon name="location_on" />
      </template>
      <template v-slot:append v-if="loading">
        <q-spinner size="20px" />
      </template>
    </q-input>
    
    <q-list 
      v-if="showDropdown && (suggestions.length || loading || error)" 
      class="geo-dropdown" 
      bordered
      separator
    >
      <!-- Loading state -->
      <q-item v-if="loading" class="text-center">
        <q-item-section>
          <q-spinner size="24px" class="q-mx-auto" />
          <div class="text-caption q-mt-sm">Buscando ubicaciones...</div>
        </q-item-section>
      </q-item>
      
      <!-- Error state -->
      <q-item v-else-if="error" class="text-center">
        <q-item-section>
          <q-icon name="error_outline" color="negative" size="24px" />
          <div class="text-caption q-mt-sm text-negative">{{ error }}</div>
        </q-item-section>
      </q-item>
      
      <!-- No results state -->
      <q-item v-else-if="!suggestions.length && query.length >= 2" class="text-center">
        <q-item-section>
          <q-icon name="search_off" color="grey-5" size="24px" />
          <div class="text-caption q-mt-sm text-grey-6">No se encontraron ubicaciones</div>
        </q-item-section>
      </q-item>
      
      <!-- Location suggestions -->
      <q-item
        v-else
        v-for="(location, index) in suggestions"
        :key="index"
        clickable
        v-ripple
        @click="selectLocation(location)"
        @mousedown.prevent
        :class="{ 
          'geo-item': true, 
          'highlighted': index === highlightedIndex 
        }"
      >
        <q-item-section avatar>
          <q-icon name="place" color="primary" />
        </q-item-section>
        <q-item-section>
          <q-item-label>
            {{ location.nombre }}{{ location.departamento ? `, ${location.departamento.nombre}` : "" }}, {{ location.provincia.nombre }}
          </q-item-label>
          <q-item-label v-if="location.centroide" caption class="text-grey-5">
            {{ formatCoordinates(location.centroide) }}
          </q-item-label>
        </q-item-section>
      </q-item>
    </q-list>
  </div>
</template>
  
  <script>
  import axios from "axios";
  
  export default {
    name: "GeoAutocomplete",
    data() {
      return {
        query: "",
        suggestions: [],
        showDropdown: false,
        loading: false,
        error: null,
        debounceTimer: null,
        highlightedIndex: -1,
        abortController: null
      };
    },
    methods: {
      /**
       * Método con debounce para buscar ubicaciones
       */
      debouncedFetchLocations() {
        console.log('🔍 debouncedFetchLocations called with query:', this.query);

        // Cancelar búsqueda anterior
        if (this.debounceTimer) {
          clearTimeout(this.debounceTimer);
        }

        // Resetear estado de navegación
        this.highlightedIndex = -1;

        // Si la query es muy corta, limpiar sugerencias
        if (this.query.length < 2) {
          this.suggestions = [];
          this.error = null;
          this.showDropdown = false;
          return;
        }

        // Configurar debounce con menor delay para mejor UX
        this.debounceTimer = setTimeout(() => {
          this.fetchLocations();
        }, 150);
      },

      /**
       * Busca ubicaciones usando la API de georef
       */
      async fetchLocations() {
        // Cancelar petición anterior si existe
        if (this.abortController) {
          this.abortController.abort();
        }

        this.abortController = new AbortController();
        this.loading = true;
        this.error = null;

        console.log('🔍 Buscando ubicaciones para:', this.query);

        try {
          const response = await axios.get(
            "https://apis.datos.gob.ar/georef/api/localidades",
            {
              params: {
                nombre: this.query,
                max: 15,
                campos: "nombre,provincia,departamento,centroide"
              },
              signal: this.abortController.signal,
              timeout: 5000 // 5 segundos de timeout
            }
          );

          console.log('✅ Respuesta de georef API:', response.data);
          
          const localidades = response.data.localidades || [];
          
          if (localidades.length === 0) {
            this.suggestions = [];
            this.showDropdown = true;
          } else {
            this.suggestions = this.getUniqueLocations(localidades);
            this.showDropdown = true;
          }
          
        } catch (error) {
          if (error.name === 'AbortError') {
            // Petición cancelada, no hacer nada
            return;
          }

          console.error("❌ Error al buscar ubicaciones:", error);

          // Intentar con API de respaldo usando datos estáticos comunes
          console.log("🔄 Intentando con datos de respaldo...");
          await this.tryFallbackLocations();

          if (this.suggestions.length === 0) {
            if (error.code === 'ECONNABORTED' || error.message.includes('timeout')) {
              this.error = 'Tiempo de espera agotado. Intenta de nuevo.';
            } else if (error.response?.status === 404) {
              this.error = 'Servicio de ubicaciones no disponible';
            } else if (!navigator.onLine) {
              this.error = 'Sin conexión a internet';
            } else {
              this.error = 'Error al buscar ubicaciones. Intenta de nuevo.';
            }
            this.showDropdown = true;
          }
        } finally {
          this.loading = false;
          this.abortController = null;
        }
      },

      /**
       * API de respaldo con ubicaciones comunes cuando falla georef
       */
      async tryFallbackLocations() {
        const commonLocations = [
          {
            nombre: "Buenos Aires",
            provincia: { nombre: "Ciudad Autónoma de Buenos Aires" },
            departamento: { nombre: "Comuna 1" },
            centroide: { lat: -34.6037, lon: -58.3816 }
          },
          {
            nombre: "Córdoba",
            provincia: { nombre: "Córdoba" },
            departamento: { nombre: "Capital" },
            centroide: { lat: -31.4201, lon: -64.1888 }
          },
          {
            nombre: "Rosario",
            provincia: { nombre: "Santa Fe" },
            departamento: { nombre: "Rosario" },
            centroide: { lat: -32.9442, lon: -60.6505 }
          },
          {
            nombre: "Mendoza",
            provincia: { nombre: "Mendoza" },
            departamento: { nombre: "Capital" },
            centroide: { lat: -32.8908, lon: -68.8272 }
          },
          {
            nombre: "La Plata",
            provincia: { nombre: "Buenos Aires" },
            departamento: { nombre: "La Plata" },
            centroide: { lat: -34.9215, lon: -57.9545 }
          },
          {
            nombre: "Tucumán",
            provincia: { nombre: "Tucumán" },
            departamento: { nombre: "Capital" },
            centroide: { lat: -26.8083, lon: -65.2176 }
          },
          {
            nombre: "Salta",
            provincia: { nombre: "Salta" },
            departamento: { nombre: "Capital" },
            centroide: { lat: -24.7821, lon: -65.4232 }
          },
          {
            nombre: "Santa Fe",
            provincia: { nombre: "Santa Fe" },
            departamento: { nombre: "La Capital" },
            centroide: { lat: -31.6333, lon: -60.7 }
          }
        ];

        const filteredLocations = commonLocations.filter(location =>
          location.nombre.toLowerCase().includes(this.query.toLowerCase()) ||
          location.provincia.nombre.toLowerCase().includes(this.query.toLowerCase())
        );

        if (filteredLocations.length > 0) {
          console.log("✅ Usando ubicaciones de respaldo:", filteredLocations);
          this.suggestions = filteredLocations;
          this.showDropdown = true;
          this.error = null;
        }
      },

      /**
       * Filtra ubicaciones únicas
       */
      getUniqueLocations(localidades) {
        const unique = [];
        const seen = new Set();
  
        for (const localidad of localidades) {
          // Crear un identificador único basado en nombre, departamento y provincia
          const id = `${localidad.nombre}-${localidad.departamento?.nombre || ""}-${localidad.provincia.nombre}`;
          if (!seen.has(id)) {
            seen.add(id);
            unique.push(localidad);
          }
        }
        return unique.slice(0, 10); // Limitar a 10 resultados
      },

      /**
       * Selecciona una ubicación
       */
      selectLocation(location) {
        console.log('📍 Ubicación seleccionada:', location);
        const formattedLocation = `${location.nombre}${location.departamento ? `, ${location.departamento.nombre}` : ""}, ${location.provincia.nombre}`;
        this.query = formattedLocation;
        this.showDropdown = false;
        this.highlightedIndex = -1;
        this.error = null;
        console.log('🔄 Emitiendo evento locationSelected con:', location);
        this.$emit("locationSelected", location);
      },

      /**
       * Selecciona la ubicación resaltada con navegación por teclado
       */
      selectHighlighted() {
        if (this.highlightedIndex >= 0 && this.suggestions[this.highlightedIndex]) {
          this.selectLocation(this.suggestions[this.highlightedIndex]);
        }
      },

      /**
       * Navegación hacia abajo con teclado
       */
      navigateDown() {
        if (this.suggestions.length === 0) return;
        
        this.highlightedIndex = Math.min(
          this.highlightedIndex + 1, 
          this.suggestions.length - 1
        );
      },

      /**
       * Navegación hacia arriba con teclado
       */
      navigateUp() {
        if (this.suggestions.length === 0) return;
        
        this.highlightedIndex = Math.max(this.highlightedIndex - 1, -1);
      },

      /**
       * Oculta el dropdown
       */
      hideDropdown() {
        this.showDropdown = false;
        this.highlightedIndex = -1;
      },

      /**
       * Oculta el dropdown con delay para permitir clics
       */
      hideDropdownDelayed() {
        setTimeout(() => {
          this.hideDropdown();
        }, 150);
      },

      /**
       * Formatea coordenadas para mostrar
       */
      formatCoordinates(centroide) {
        if (!centroide || !centroide.lat || !centroide.lon) return '';
        
        return `${centroide.lat.toFixed(4)}, ${centroide.lon.toFixed(4)}`;
      },

      /**
       * Limpia el estado del componente
       */
      clearState() {
        this.query = '';
        this.suggestions = [];
        this.showDropdown = false;
        this.loading = false;
        this.error = null;
        this.highlightedIndex = -1;
        
        if (this.debounceTimer) {
          clearTimeout(this.debounceTimer);
          this.debounceTimer = null;
        }
        
        if (this.abortController) {
          this.abortController.abort();
          this.abortController = null;
        }
      },

      /**
       * Establece la ubicación inicial en el componente
       * @param {Object} location - Objeto de ubicación
       */
      setInitialLocation(location) {
        console.log("📍 GeoAutocomplete - Estableciendo ubicación inicial:", location);
        if (location && location.nombre && location.provincia) {
          // Format the location for display
          const formattedLocation = `${location.nombre}${location.departamento ? `, ${location.departamento.nombre}` : ""}, ${location.provincia.nombre}`;
          this.query = formattedLocation;
          console.log("📍 GeoAutocomplete - Query establecido:", this.query);
        }
      }
    },

    /**
     * Limpieza al destruir el componente
     */
    beforeUnmount() {
      this.clearState();
    }
  };
  </script>
  
<style scoped>
.geo-autocomplete {
  position: relative;
  max-width: 100%;
}

.geo-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border-radius: 4px;
  max-height: 250px;
  overflow-y: auto;
  z-index: 1000;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.geo-item {
  transition: all 0.2s ease;
}

.geo-item:hover,
.geo-item.highlighted {
  background-color: #f3f4f6;
}

.geo-item.highlighted {
  border-left: 3px solid var(--q-primary);
}

/* Dark mode support */
.body--dark .geo-dropdown {
  background: #1f2937;
  color: white;
}

.body--dark .geo-item:hover,
.body--dark .geo-item.highlighted {
  background-color: #374151;
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .geo-dropdown {
    max-height: 200px;
  }
}

/* Scroll styling */
.geo-dropdown::-webkit-scrollbar {
  width: 6px;
}

.geo-dropdown::-webkit-scrollbar-track {
  background: transparent;
}

.geo-dropdown::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.geo-dropdown::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
  