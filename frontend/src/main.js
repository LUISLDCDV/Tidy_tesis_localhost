import { createApp } from 'vue';
import { createI18n } from 'vue-i18n';
import { Quasar, Notify, Dialog, Loading, LoadingBar, LocalStorage, SessionStorage } from 'quasar';
import axios from 'axios';

// Importar estilos de Quasar
import 'quasar/dist/quasar.css';
import '@quasar/extras/material-icons/material-icons.css';
import '@quasar/extras/fontawesome-v6/fontawesome-v6.css';

import App from './App.vue';
import router from './router';
import pinia from './stores';
import './assets/styles/variables.css';
import imageOptimizationPlugin from './plugins/imageOptimization';
import { applyCustomColorsGlobally } from './composables/useThemeColors';

// Importar traducciones
import es from './locales/es';
import en from './locales/en';
import pt from './locales/pt';

// Función para detectar idioma del navegador
function getBrowserLanguage() {
  const browserLang = navigator.language || navigator.userLanguage;
  const langCode = browserLang.split('-')[0]; // Obtener solo el código de idioma (ej: 'es' de 'es-ES')

  // Idiomas soportados
  const supportedLanguages = ['es', 'en', 'pt'];

  return supportedLanguages.includes(langCode) ? langCode : 'es';
}

// Configurar i18n
const i18n = createI18n({
  legacy: false,
  locale: localStorage.getItem('app_language') || getBrowserLanguage(),
  fallbackLocale: 'es',
  globalInjection: true,
  messages: {
    es,
    en,
    pt
  }
});

// Configurar axios globalmente
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['Content-Type'] = 'application/json';
axios.defaults.withCredentials = false;

// Crear la aplicación
const app = createApp(App);

// Usar Quasar
app.use(Quasar, {
  plugins: {
    Notify,
    Dialog,
    Loading,
    LoadingBar,
    LocalStorage,
    SessionStorage
  },
  config: {
    notify: {
      position: 'top',
      timeout: 2500,
      textColor: 'white',
      actions: [{ icon: 'close', color: 'white' }]
    },
    loading: {}
  }
});

// Usar plugins
app.use(pinia);
app.use(router);
app.use(i18n);
app.use(imageOptimizationPlugin);

// Aplicar colores personalizados guardados (funcionalidad Premium)
applyCustomColorsGlobally();

// NOTA: Con Quasar CLI, los boot files se ejecutan automáticamente
// El boot file de Capacitor (src/boot/capacitor.js) se ejecuta antes de montar la app
// No es necesario importarlo ni llamarlo manualmente

// Inicializar configuraciones después de registrar Pinia pero antes de montar
// import { useConfigStore } from './stores/config';
// const configStore = useConfigStore();
// configStore.initTheme();
// configStore.loadStorageSettings();

// Registrar Service Worker para cache
if ('serviceWorker' in navigator && import.meta.env.PROD) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then((registration) => {
        console.log('SW: Service Worker registrado exitosamente:', registration);

        // Escuchar actualizaciones del SW
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing;

          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              // Nueva versión disponible
              console.log('SW: Nueva versión disponible');

              // Aquí se puede mostrar una notificación al usuario
              if (window.Quasar) {
                window.Quasar.Notify.create({
                  message: 'Nueva versión disponible. Recarga la página para actualizar.',
                  color: 'primary',
                  actions: [
                    {
                      label: 'Recargar',
                      color: 'white',
                      handler: () => {
                        window.location.reload();
                      }
                    }
                  ],
                  timeout: 0
                });
              }
            }
          });
        });
      })
      .catch((error) => {
        console.log('SW: Error al registrar Service Worker:', error);
      });

    // Escuchar mensajes del Service Worker
    navigator.serviceWorker.addEventListener('message', (event) => {
      if (event.data.type === 'SW_UPDATE_AVAILABLE') {
        console.log('SW: Actualización disponible');
      }

      if (event.data.type === 'BACKGROUND_SYNC_COMPLETE') {
        console.log('SW: Sincronización en background completada');
      }
    });
  });
}

// Montar la aplicación
app.mount('#app');