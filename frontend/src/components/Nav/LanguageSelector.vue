<template>
  <q-item
    clickable
    v-ripple
    @click="showLanguageMenu = true"
    class="language-selector-item"
  >
    <q-item-section avatar>
      <q-icon
        name="language"
        color="primary"
        size="24px"
        class="language-icon"
      />
    </q-item-section>

    <q-item-section>
      <q-item-label class="language-label">
        {{ t('nav.language') }}
      </q-item-label>
      <q-item-label caption class="language-description">
        <span class="current-language-flag">{{ currentLanguageFlag }}</span>
        {{ currentLanguageLabel }}
      </q-item-label>
    </q-item-section>

    <q-item-section side>
      <q-icon name="chevron_right" color="grey-5" size="20px" />
    </q-item-section>

    <!-- Menu de selección de idioma -->
    <q-menu
      v-model="showLanguageMenu"
      anchor="top right"
      self="top left"
      class="language-menu"
      transition-show="scale"
      transition-hide="scale"
    >
      <q-list class="language-list">
        <q-item-label header class="menu-header">
          {{ t('nav.selectLanguage') }}
        </q-item-label>

        <q-separator />

        <q-item
          v-for="language in languageOptions"
          :key="language.value"
          clickable
          v-ripple
          @click="selectLanguage(language)"
          :class="{ 'active-language': currentLang === language.value }"
          class="language-option"
        >
          <q-item-section avatar class="flag-section">
            <span class="language-flag">{{ language.flag }}</span>
          </q-item-section>

          <q-item-section>
            <q-item-label class="language-name">
              {{ language.label }}
            </q-item-label>
            <q-item-label caption class="country-name">
              {{ language.country }}
            </q-item-label>
          </q-item-section>

          <q-item-section side v-if="currentLang === language.value">
            <q-icon name="check" color="positive" size="20px" />
          </q-item-section>
        </q-item>
      </q-list>
    </q-menu>
  </q-item>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useQuasar } from 'quasar';

export default {
  name: 'LanguageSelector',

  emits: ['language-changed'],

  setup(props, { emit }) {
    const { t, locale } = useI18n();
    const $q = useQuasar();

    // Estado reactivo
    const showLanguageMenu = ref(false);
    const currentLang = ref('es');

    // Opciones de idiomas disponibles
    const languageOptions = ref([
      {
        label: 'Español',
        value: 'es',
        flag: '🇪🇸',
        country: 'España'
      },
      {
        label: 'English',
        value: 'en',
        flag: '🇺🇸',
        country: 'United States'
      },
      {
        label: 'Português',
        value: 'pt',
        flag: '🇧🇷',
        country: 'Brasil'
      }
    ]);

    // Computed para obtener la información del idioma actual
    const currentLanguageData = computed(() => {
      return languageOptions.value.find(lang => lang.value === currentLang.value);
    });

    const currentLanguageLabel = computed(() => {
      return currentLanguageData.value?.label || 'Español';
    });

    const currentLanguageFlag = computed(() => {
      return currentLanguageData.value?.flag || '🇪🇸';
    });

    /**
     * Inicializar el idioma actual
     */
    onMounted(() => {
      // Obtener idioma del localStorage o usar el actual
      const savedLanguage = localStorage.getItem('language') ||
                           localStorage.getItem('app_language') ||
                           locale.value;
      currentLang.value = savedLanguage;
    });

    /**
     * Seleccionar un nuevo idioma
     * @param {Object} language - Objeto del idioma seleccionado
     */
    const selectLanguage = async (language) => {
      try {
        // Actualizar el idioma local
        currentLang.value = language.value;

        // Cambiar el idioma en vue-i18n
        locale.value = language.value;

        // Guardar en localStorage
        localStorage.setItem('language', language.value);
        localStorage.setItem('app_language', language.value);

        // Cerrar el menú
        showLanguageMenu.value = false;

        // Emitir evento de cambio
        emit('language-changed', language);

        // Mostrar notificación de éxito
        showLanguageChangeNotification(language);

        // Actualizar el idioma en la aplicación si hay un método global
        if (window.changeAppLanguage) {
          await window.changeAppLanguage(language.value);
        }

      } catch (error) {
        console.error('Error al cambiar idioma:', error);
        showErrorNotification();
      }
    };

    /**
     * Mostrar notificación de cambio exitoso
     * @param {Object} language - Idioma seleccionado
     */
    const showLanguageChangeNotification = (language) => {
      $q.notify({
        type: 'positive',
        message: `${language.flag} ${t('nav.languageChanged')} ${language.label}`,
        icon: 'language',
        position: 'top',
        timeout: 2500,
        actions: [
          {
            icon: 'close',
            color: 'white',
            round: true,
            size: 'sm'
          }
        ]
      });
    };

    /**
     * Mostrar notificación de error
     */
    const showErrorNotification = () => {
      $q.notify({
        type: 'negative',
        message: t('nav.languageChangeError'),
        icon: 'error',
        position: 'top',
        timeout: 3000
      });
    };

    /**
     * Detectar idioma del navegador como sugerencia
     */
    const getBrowserLanguage = () => {
      const browserLang = navigator.language || navigator.userLanguage;
      const langCode = browserLang.split('-')[0];

      const supportedLanguages = languageOptions.value.map(lang => lang.value);
      return supportedLanguages.includes(langCode) ? langCode : 'es';
    };

    /**
     * Sugerir idioma del navegador si es diferente al actual
     */
    const suggestBrowserLanguage = () => {
      const browserLang = getBrowserLanguage();

      if (browserLang !== currentLang.value) {
        const browserLanguageData = languageOptions.value.find(lang => lang.value === browserLang);

        if (browserLanguageData) {
          $q.dialog({
            title: t('nav.languageSuggestionTitle'),
            message: `${t('nav.languageSuggestionMessage')} ${browserLanguageData.flag} ${browserLanguageData.label}?`,
            cancel: {
              label: t('common.no'),
              color: 'grey',
              flat: true
            },
            ok: {
              label: t('common.yes'),
              color: 'primary'
            },
            persistent: false
          }).onOk(() => {
            selectLanguage(browserLanguageData);
          });
        }
      }
    };

    return {
      t,
      showLanguageMenu,
      currentLang,
      languageOptions,
      currentLanguageLabel,
      currentLanguageFlag,
      selectLanguage,
      suggestBrowserLanguage
    };
  }
};
</script>

<style scoped>
.language-selector-item {
  border-radius: 8px;
  margin: 4px 8px;
  transition: all 0.2s ease;
  min-height: 56px;
}

.language-selector-item:hover {
  background: rgba(var(--q-primary), 0.08);
}

.language-icon {
  transition: transform 0.2s ease;
}

.language-selector-item:hover .language-icon {
  transform: scale(1.1);
}

.language-label {
  font-weight: 500;
  font-size: 0.95rem;
}

.language-description {
  font-size: 0.8rem;
  opacity: 0.8;
  display: flex;
  align-items: center;
  gap: 6px;
}

.current-language-flag {
  font-size: 1rem;
}

/* Menu de idiomas */
.language-menu {
  min-width: 250px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.language-list {
  padding: 8px 0;
}

.menu-header {
  font-weight: 600;
  color: var(--q-primary);
  padding: 12px 16px 8px;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.language-option {
  padding: 12px 16px;
  min-height: 56px;
  transition: all 0.2s ease;
}

.language-option:hover {
  background: rgba(var(--q-primary), 0.08);
}

.active-language {
  background: rgba(var(--q-primary), 0.12);
  border-left: 3px solid var(--q-primary);
}

.flag-section {
  min-width: 40px;
  display: flex;
  justify-content: center;
}

.language-flag {
  font-size: 1.5rem;
  line-height: 1;
}

.language-name {
  font-weight: 500;
  font-size: 0.95rem;
}

.country-name {
  font-size: 0.8rem;
  opacity: 0.7;
}

/* Modo oscuro */
.body--dark .language-selector-item:hover {
  background: rgba(255, 255, 255, 0.08);
}

.body--dark .language-option:hover {
  background: rgba(255, 255, 255, 0.08);
}

.body--dark .active-language {
  background: rgba(144, 202, 249, 0.16);
}

.body--dark .language-menu {
  background: #1e1e1e;
  border: 1px solid #333;
}

.body--dark .language-label {
  color: #e0e0e0;
}

.body--dark .language-description {
  color: #b0b0b0;
}

.body--dark .menu-header {
  color: #90CAF9;
}

.body--dark .language-name {
  color: #e0e0e0;
}

.body--dark .country-name {
  color: #b0b0b0;
}

/* Responsive */
@media (max-width: 768px) {
  .language-selector-item {
    min-height: 52px;
    margin: 2px 4px;
  }

  .language-label {
    font-size: 0.9rem;
  }

  .language-description {
    font-size: 0.75rem;
  }

  .language-menu {
    min-width: 200px;
  }

  .language-option {
    min-height: 48px;
    padding: 10px 12px;
  }
}

/* Animaciones */
.language-selector-item {
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

/* Efectos de transición para el flag */
.current-language-flag {
  transition: transform 0.2s ease;
}

.language-selector-item:hover .current-language-flag {
  transform: scale(1.2);
}

/* Estados de enfoque para accesibilidad */
.language-selector-item:focus {
  outline: 2px solid var(--q-primary);
  outline-offset: 2px;
}

.language-option:focus {
  outline: 2px solid var(--q-primary);
  outline-offset: -2px;
}

/* Efecto ripple personalizado para opciones de idioma */
.language-option .q-focus-helper {
  background: rgba(var(--q-primary), 0.12);
}

/* Separador personalizado */
.language-list .q-separator {
  margin: 8px 0;
  background: rgba(0, 0, 0, 0.1);
}

.body--dark .language-list .q-separator {
  background: rgba(255, 255, 255, 0.1);
}
</style>