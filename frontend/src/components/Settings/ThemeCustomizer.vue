<template>
  <div class="theme-customizer">
    <!-- Header con badge Premium -->
    <div class="customizer-header q-mb-md">
      <div class="row items-center justify-between">
        <div>
          <h4 class="text-h6 text-weight-medium q-mb-xs">
            <q-icon name="palette" class="q-mr-sm" />
            Personalización de Tema
          </h4>
          <p class="text-caption text-grey-6">Personaliza los colores principales de la aplicación</p>
        </div>
        <q-chip color="purple" text-color="white" icon="workspace_premium">
          Premium
        </q-chip>
      </div>
    </div>

    <!-- Color Pickers -->
    <div class="color-pickers-container">
      <!-- Color Primario -->
      <div class="color-picker-item">
        <q-item>
          <q-item-section avatar>
            <div
              class="color-preview"
              :style="{ backgroundColor: localColors.primary }"
            >
              <q-icon name="water_drop" color="white" />
            </div>
          </q-item-section>

          <q-item-section>
            <q-item-label>Color Primario</q-item-label>
            <q-item-label caption>Color principal de la aplicación</q-item-label>
          </q-item-section>

          <q-item-section side>
            <q-btn
              flat
              round
              icon="colorize"
              @click="openColorPicker('primary')"
              :disable="!isPremium"
            >
              <q-popup-proxy v-if="isPremium">
                <q-color
                  v-model="localColors.primary"
                  @change="updateColor('primary', $event)"
                  default-view="palette"
                />
              </q-popup-proxy>
              <q-tooltip v-if="!isPremium">Esta función requiere Premium</q-tooltip>
            </q-btn>
          </q-item-section>
        </q-item>
      </div>

      <!-- Color Secundario -->
      <div class="color-picker-item">
        <q-item>
          <q-item-section avatar>
            <div
              class="color-preview"
              :style="{ backgroundColor: localColors.secondary }"
            >
              <q-icon name="water_drop" color="white" />
            </div>
          </q-item-section>

          <q-item-section>
            <q-item-label>Color Secundario</q-item-label>
            <q-item-label caption>Color de acentos y detalles</q-item-label>
          </q-item-section>

          <q-item-section side>
            <q-btn
              flat
              round
              icon="colorize"
              @click="openColorPicker('secondary')"
              :disable="!isPremium"
            >
              <q-popup-proxy v-if="isPremium">
                <q-color
                  v-model="localColors.secondary"
                  @change="updateColor('secondary', $event)"
                  default-view="palette"
                />
              </q-popup-proxy>
              <q-tooltip v-if="!isPremium">Esta función requiere Premium</q-tooltip>
            </q-btn>
          </q-item-section>
        </q-item>
      </div>

      <!-- Color de Acento -->
      <div class="color-picker-item">
        <q-item>
          <q-item-section avatar>
            <div
              class="color-preview"
              :style="{ backgroundColor: localColors.accent }"
            >
              <q-icon name="water_drop" color="white" />
            </div>
          </q-item-section>

          <q-item-section>
            <q-item-label>Color de Acento</q-item-label>
            <q-item-label caption>Color para destacar elementos importantes</q-item-label>
          </q-item-section>

          <q-item-section side>
            <q-btn
              flat
              round
              icon="colorize"
              @click="openColorPicker('accent')"
              :disable="!isPremium"
            >
              <q-popup-proxy v-if="isPremium">
                <q-color
                  v-model="localColors.accent"
                  @change="updateColor('accent', $event)"
                  default-view="palette"
                />
              </q-popup-proxy>
              <q-tooltip v-if="!isPremium">Esta función requiere Premium</q-tooltip>
            </q-btn>
          </q-item-section>
        </q-item>
      </div>

      <!-- Color Positivo (Success) -->
      <div class="color-picker-item">
        <q-item>
          <q-item-section avatar>
            <div
              class="color-preview"
              :style="{ backgroundColor: localColors.positive }"
            >
              <q-icon name="water_drop" color="white" />
            </div>
          </q-item-section>

          <q-item-section>
            <q-item-label>Color de Éxito</q-item-label>
            <q-item-label caption>Color para mensajes y estados positivos</q-item-label>
          </q-item-section>

          <q-item-section side>
            <q-btn
              flat
              round
              icon="colorize"
              @click="openColorPicker('positive')"
              :disable="!isPremium"
            >
              <q-popup-proxy v-if="isPremium">
                <q-color
                  v-model="localColors.positive"
                  @change="updateColor('positive', $event)"
                  default-view="palette"
                />
              </q-popup-proxy>
              <q-tooltip v-if="!isPremium">Esta función requiere Premium</q-tooltip>
            </q-btn>
          </q-item-section>
        </q-item>
      </div>

      <!-- Color Negativo (Error) -->
      <div class="color-picker-item">
        <q-item>
          <q-item-section avatar>
            <div
              class="color-preview"
              :style="{ backgroundColor: localColors.negative }"
            >
              <q-icon name="water_drop" color="white" />
            </div>
          </q-item-section>

          <q-item-section>
            <q-item-label>Color de Error</q-item-label>
            <q-item-label caption>Color para mensajes y estados de error</q-item-label>
          </q-item-section>

          <q-item-section side>
            <q-btn
              flat
              round
              icon="colorize"
              @click="openColorPicker('negative')"
              :disable="!isPremium"
            >
              <q-popup-proxy v-if="isPremium">
                <q-color
                  v-model="localColors.negative"
                  @change="updateColor('negative', $event)"
                  default-view="palette"
                />
              </q-popup-proxy>
              <q-tooltip v-if="!isPremium">Esta función requiere Premium</q-tooltip>
            </q-btn>
          </q-item-section>
        </q-item>
      </div>
    </div>

    <!-- Temas Predefinidos -->
    <div class="preset-themes q-mt-lg">
      <h5 class="text-subtitle1 text-weight-medium q-mb-md">Temas Predefinidos</h5>
      <div class="row q-gutter-md">
        <q-btn
          v-for="preset in presetThemes"
          :key="preset.name"
          @click="applyPreset(preset)"
          outline
          class="preset-btn"
          :disable="!isPremium"
        >
          <div class="preset-preview">
            <div
              v-for="(color, index) in [preset.colors.primary, preset.colors.secondary, preset.colors.accent]"
              :key="index"
              class="preset-color"
              :style="{ backgroundColor: color }"
            />
          </div>
          <span class="q-ml-sm">{{ preset.name }}</span>
        </q-btn>
      </div>
    </div>

    <!-- Botones de Acción -->
    <div class="actions-container q-mt-lg">
      <div class="row justify-end q-gutter-sm">
        <q-btn
          flat
          label="Restablecer"
          icon="restore"
          @click="resetToDefault"
          color="grey"
        />
        <q-btn
          unelevated
          label="Aplicar Cambios"
          icon="check"
          @click="saveColors"
          color="primary"
          :loading="saving"
        />
      </div>
    </div>

    <!-- Vista Previa -->
    <div class="preview-section q-mt-lg">
      <h5 class="text-subtitle1 text-weight-medium q-mb-md">Vista Previa</h5>
      <div class="preview-card" :style="getPreviewStyles()">
        <q-btn color="primary" label="Primario" class="q-ma-sm" />
        <q-btn color="secondary" label="Secundario" class="q-ma-sm" />
        <q-btn color="accent" label="Acento" class="q-ma-sm" />
        <q-btn color="positive" label="Éxito" class="q-ma-sm" />
        <q-btn color="negative" label="Error" class="q-ma-sm" />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue';
import { useQuasar, colors } from 'quasar';
import { useUserSettingsStore } from '@/stores/userSettings';

export default {
  name: 'ThemeCustomizer',
  props: {
    isPremium: {
      type: Boolean,
      default: false
    }
  },
  emits: ['colors-updated'],
  setup(props, { emit }) {
    const $q = useQuasar();
    const userSettingsStore = useUserSettingsStore();
    const saving = ref(false);

    // Colores por defecto de Quasar
    const defaultColors = {
      primary: '#1976D2',
      secondary: '#26A69A',
      accent: '#9C27B0',
      positive: '#21BA45',
      negative: '#C10015',
      info: '#31CCEC',
      warning: '#F2C037'
    };

    // Colores locales
    const localColors = ref({
      primary: '#1976D2',
      secondary: '#26A69A',
      accent: '#9C27B0',
      positive: '#21BA45',
      negative: '#C10015'
    });

    // Temas predefinidos
    const presetThemes = ref([
      {
        name: 'Oceánico',
        colors: {
          primary: '#0288D1',
          secondary: '#00ACC1',
          accent: '#00BCD4',
          positive: '#00C853',
          negative: '#D32F2F'
        }
      },
      {
        name: 'Bosque',
        colors: {
          primary: '#388E3C',
          secondary: '#689F38',
          accent: '#AFB42B',
          positive: '#66BB6A',
          negative: '#EF5350'
        }
      },
      {
        name: 'Atardecer',
        colors: {
          primary: '#F57C00',
          secondary: '#FF6F00',
          accent: '#FF9800',
          positive: '#66BB6A',
          negative: '#EF5350'
        }
      },
      {
        name: 'Morado Oscuro',
        colors: {
          primary: '#7B1FA2',
          secondary: '#9C27B0',
          accent: '#E91E63',
          positive: '#66BB6A',
          negative: '#EF5350'
        }
      },
      {
        name: 'Noche',
        colors: {
          primary: '#1E88E5',
          secondary: '#5E35B1',
          accent: '#3949AB',
          positive: '#43A047',
          negative: '#E53935'
        }
      },
      {
        name: 'Coral',
        colors: {
          primary: '#FF7043',
          secondary: '#FF8A65',
          accent: '#FFAB91',
          positive: '#66BB6A',
          negative: '#EF5350'
        }
      }
    ]);

    // Cargar colores guardados
    const loadSavedColors = () => {
      const savedColors = localStorage.getItem('customThemeColors');
      if (savedColors) {
        try {
          localColors.value = JSON.parse(savedColors);
          applyColorsToQuasar(localColors.value);
        } catch (error) {
          console.error('Error loading saved colors:', error);
        }
      }
    };

    // Aplicar colores a Quasar
    const applyColorsToQuasar = (colorSet) => {
      Object.keys(colorSet).forEach(colorName => {
        colors.setBrand(colorName, colorSet[colorName]);
      });
    };

    // Actualizar color individual
    const updateColor = (colorName, newColor) => {
      localColors.value[colorName] = newColor;
      // Aplicar inmediatamente para vista previa en tiempo real
      colors.setBrand(colorName, newColor);
    };

    // Abrir selector de color
    const openColorPicker = (colorName) => {
      console.log('Opening color picker for:', colorName);
    };

    // Aplicar tema predefinido
    const applyPreset = (preset) => {
      if (!props.isPremium) {
        $q.notify({
          type: 'warning',
          message: 'Esta función requiere una suscripción Premium',
          position: 'top',
          icon: 'workspace_premium'
        });
        return;
      }

      localColors.value = { ...preset.colors };
      applyColorsToQuasar(preset.colors);

      $q.notify({
        type: 'positive',
        message: `Tema "${preset.name}" aplicado`,
        position: 'top',
        timeout: 2000
      });
    };

    // Restablecer a colores por defecto
    const resetToDefault = () => {
      $q.dialog({
        title: 'Restablecer colores',
        message: '¿Estás seguro de que quieres restablecer los colores a los valores por defecto?',
        cancel: true,
        persistent: false
      }).onOk(() => {
        localColors.value = { ...defaultColors };
        applyColorsToQuasar(defaultColors);
        localStorage.removeItem('customThemeColors');

        $q.notify({
          type: 'info',
          message: 'Colores restablecidos a valores por defecto',
          position: 'top',
          timeout: 2000
        });
      });
    };

    // Guardar colores
    const saveColors = async () => {
      if (!props.isPremium) {
        $q.notify({
          type: 'warning',
          message: 'Esta función requiere una suscripción Premium',
          position: 'top',
          icon: 'workspace_premium'
        });
        return;
      }

      saving.value = true;

      try {
        // Guardar en localStorage
        localStorage.setItem('customThemeColors', JSON.stringify(localColors.value));

        // Guardar en el servidor (si está disponible)
        try {
          await userSettingsStore.updateSingleSetting('appearance', 'customColors', localColors.value);
        } catch (error) {
          console.log('Could not save to server, saved locally only');
        }

        // Aplicar colores
        applyColorsToQuasar(localColors.value);

        // Emitir evento
        emit('colors-updated', localColors.value);

        $q.notify({
          type: 'positive',
          message: 'Colores personalizados guardados correctamente',
          icon: 'palette',
          position: 'top',
          timeout: 2000
        });
      } catch (error) {
        console.error('Error saving colors:', error);
        $q.notify({
          type: 'negative',
          message: 'Error al guardar los colores personalizados',
          position: 'top'
        });
      } finally {
        saving.value = false;
      }
    };

    // Obtener estilos para vista previa
    const getPreviewStyles = () => {
      return {
        '--q-primary': localColors.value.primary,
        '--q-secondary': localColors.value.secondary,
        '--q-accent': localColors.value.accent,
        '--q-positive': localColors.value.positive,
        '--q-negative': localColors.value.negative
      };
    };

    // Cargar colores al montar
    onMounted(() => {
      loadSavedColors();
    });

    return {
      localColors,
      presetThemes,
      saving,
      updateColor,
      openColorPicker,
      applyPreset,
      resetToDefault,
      saveColors,
      getPreviewStyles
    };
  }
};
</script>

<style scoped>
.theme-customizer {
  padding: 16px;
}

.customizer-header {
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.12);
}

.color-pickers-container {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.color-picker-item {
  background: rgba(0, 0, 0, 0.02);
  border-radius: 8px;
  transition: all 0.3s ease;
}

.color-picker-item:hover {
  background: rgba(0, 0, 0, 0.04);
}

.color-preview {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
}

.color-preview:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.preset-themes {
  padding-top: 16px;
  border-top: 1px solid rgba(0, 0, 0, 0.12);
}

.preset-btn {
  min-width: 140px;
  padding: 8px 16px;
}

.preset-preview {
  display: flex;
  gap: 4px;
  margin-right: 8px;
}

.preset-color {
  width: 20px;
  height: 20px;
  border-radius: 4px;
  border: 2px solid white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.actions-container {
  padding-top: 16px;
  border-top: 1px solid rgba(0, 0, 0, 0.12);
}

.preview-section {
  padding: 16px;
  background: rgba(0, 0, 0, 0.02);
  border-radius: 8px;
}

.preview-card {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding: 16px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Dark mode support */
.body--dark .customizer-header {
  border-bottom-color: rgba(255, 255, 255, 0.12);
}

.body--dark .color-picker-item {
  background: rgba(255, 255, 255, 0.05);
}

.body--dark .color-picker-item:hover {
  background: rgba(255, 255, 255, 0.08);
}

.body--dark .preset-themes,
.body--dark .actions-container {
  border-top-color: rgba(255, 255, 255, 0.12);
}

.body--dark .preview-section {
  background: rgba(255, 255, 255, 0.05);
}

.body--dark .preview-card {
  background: #1e293b;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .theme-customizer {
    padding: 12px;
  }

  .preset-btn {
    min-width: 100%;
    justify-content: flex-start;
  }

  .row.q-gutter-md {
    flex-direction: column;
    gap: 8px;
  }

  .actions-container .row {
    flex-direction: column;
  }

  .preview-card {
    justify-content: center;
  }
}
</style>
