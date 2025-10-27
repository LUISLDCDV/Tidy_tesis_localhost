<template>
  <q-item
    clickable
    v-ripple
    @click="toggleTheme"
    class="theme-toggle-item"
  >
    <q-item-section avatar>
      <q-icon
        :name="isDarkMode ? 'light_mode' : 'dark_mode'"
        :color="isDarkMode ? 'amber' : 'indigo'"
        size="24px"
        class="theme-icon"
      />
    </q-item-section>

    <q-item-section>
      <q-item-label class="theme-label">
        {{ t('nav.themeToggle') }}
      </q-item-label>
      <q-item-label caption class="theme-description">
        {{ isDarkMode ? t('nav.switchToLight') : t('nav.switchToDark') }}
      </q-item-label>
    </q-item-section>

    <q-item-section side>
      <q-toggle
        v-model="darkModeLocal"
        @update:model-value="handleToggle"
        :color="isDarkMode ? 'amber' : 'primary'"
        :true-value="true"
        :false-value="false"
        size="md"
        class="theme-toggle-switch"
        @click.stop
      />
    </q-item-section>
  </q-item>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import { useI18n } from 'vue-i18n';

export default {
  name: 'ThemeToggle',

  emits: ['theme-changed'],

  setup(props, { emit }) {
    const $q = useQuasar();
    const { t } = useI18n();

    // Estado local del modo oscuro
    const darkModeLocal = ref(false);

    // Computed para verificar si está en modo oscuro
    const isDarkMode = computed(() => $q.dark.isActive);

    // Inicializar el estado del toggle
    onMounted(() => {
      darkModeLocal.value = $q.dark.isActive;
    });

    // Watcher para sincronizar con cambios externos del tema
    watch(() => $q.dark.isActive, (newValue) => {
      darkModeLocal.value = newValue;
    });

    /**
     * Alternar el tema
     */
    const toggleTheme = () => {
      $q.dark.toggle();
      darkModeLocal.value = $q.dark.isActive;
      saveThemePreference();
      showThemeNotification();
      emit('theme-changed', $q.dark.isActive);
    };

    /**
     * Manejar el toggle desde el switch
     */
    const handleToggle = (value) => {
      $q.dark.set(value);
      saveThemePreference();
      showThemeNotification();
      emit('theme-changed', value);
    };

    /**
     * Guardar preferencia de tema en localStorage
     */
    const saveThemePreference = () => {
      try {
        localStorage.setItem('darkMode', $q.dark.isActive.toString());
        localStorage.setItem('theme', $q.dark.isActive ? 'dark' : 'light');
      } catch (error) {
        console.warn('No se pudo guardar la preferencia de tema:', error);
      }
    };

    /**
     * Cambio de tema (notificación deshabilitada)
     */
    const showThemeNotification = () => {
      // Notificación de tema deshabilitada para evitar interrupciones
      const themeMessage = $q.dark.isActive
        ? t('nav.darkModeEnabled')
        : t('nav.lightModeEnabled');

      console.log('🎨 Tema cambiado:', themeMessage);
    };

    return {
      t,
      isDarkMode,
      darkModeLocal,
      toggleTheme,
      handleToggle
    };
  }
};
</script>

<style scoped>
.theme-toggle-item {
  border-radius: 8px;
  margin: 4px 8px;
  transition: all 0.2s ease;
  min-height: 56px;
}

.theme-toggle-item:hover {
  background: rgba(var(--q-primary), 0.08);
}

.theme-icon {
  transition: all 0.3s ease;
}

.theme-label {
  font-weight: 500;
  font-size: 0.95rem;
}

.theme-description {
  font-size: 0.8rem;
  opacity: 0.8;
}

.theme-toggle-switch {
  margin-left: 8px;
}

/* Animaciones para el icono */
.theme-icon {
  animation: iconRotate 0.3s ease-in-out;
}

@keyframes iconRotate {
  0% {
    transform: rotate(0deg);
  }
  50% {
    transform: rotate(180deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Estilos para modo oscuro */
.body--dark .theme-toggle-item:hover {
  background: rgba(255, 255, 255, 0.08);
}

.body--dark .theme-label {
  color: #e0e0e0;
}

.body--dark .theme-description {
  color: #b0b0b0;
}

/* Responsive */
@media (max-width: 768px) {
  .theme-toggle-item {
    min-height: 52px;
    margin: 2px 4px;
  }

  .theme-label {
    font-size: 0.9rem;
  }

  .theme-description {
    font-size: 0.75rem;
  }
}

/* Estados de enfoque para accesibilidad */
.theme-toggle-item:focus {
  outline: 2px solid var(--q-primary);
  outline-offset: 2px;
}

.theme-toggle-switch:focus {
  outline: 2px solid var(--q-primary);
  outline-offset: 2px;
}

/* Animación de entrada */
.theme-toggle-item {
  animation: slideInLeft 0.3s ease-out;
}

@keyframes slideInLeft {
  from {
    transform: translateX(-20px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Efecto de pulse en el toggle */
.theme-toggle-switch .q-toggle__inner {
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.theme-toggle-switch .q-toggle__inner:active {
  transform: scale(1.1);
}
</style>