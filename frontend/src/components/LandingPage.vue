<template>
  <div class="landing-page">
    <!-- Hero Section -->
    <section class="hero-section flex flex-center">
      <div class="hero-content text-center">
        <q-img
          :src="isDarkMode ? rutaLogo : rutaLogoDark"
          style="width: 450px; height: auto; max-width: 90vw;"
          class="q-mb-md logo-landing"
          fit="contain"
        />
        <h2 class="text-h5 text-grey-7 q-mb-xl">{{ $t('landing.hero.subtitle') }}</h2>

        <div class="cta-buttons q-gutter-md q-mb-xl">
          <q-btn
            @click="$router.push('/login')"
            color="primary"
            size="lg"
            :label="$t('auth.login')"
            icon="login"
            unelevated
            no-caps
          />
          <q-btn
            @click="$router.push('/Register')"
            color="primary"
            size="lg"
            :label="$t('auth.register')"
            icon="person_add"
            outline
            no-caps
          />
        </div>

        <!-- Botón de descarga de APK (solo visible en web, no en mobile) -->
        <div v-if="!isMobileApp" class="apk-download-section q-mt-lg">
          <q-btn
            @click="downloadAPK"
            color="positive"
            size="md"
            label="Descargar para Android"
            icon="android"
            outline
            no-caps
            class="apk-download-btn"
          >
            <q-tooltip>Descarga la aplicación para Android (APK)</q-tooltip>
          </q-btn>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features-section q-pa-xl">
      <div class="container">
        <h2 class="text-h3 text-center text-weight-bold q-mb-xl">{{ $t('landing.whatIsTidy') }}</h2>

        <div class="row q-col-gutter-lg">
          <!-- Feature 1 -->
          <div class="col-12 col-md-6 col-lg-3">
            <q-card flat bordered class="feature-card text-center q-pa-md full-height">
              <q-icon name="event_note" size="64px" color="primary" class="q-mb-md" />
              <h3 class="text-h6 text-weight-medium q-mb-sm">{{ $t('landing.features.smartNotes.title') }}</h3>
              <p class="text-grey-7">
                {{ $t('landing.features.smartNotes.description') }}
              </p>
            </q-card>
          </div>

          <!-- Feature 2 -->
          <div class="col-12 col-md-6 col-lg-3">
            <q-card flat bordered class="feature-card text-center q-pa-md full-height">
              <q-icon name="alarm" size="64px" color="orange" class="q-mb-md" />
              <h3 class="text-h6 text-weight-medium q-mb-sm">{{ $t('landing.features.alarmsReminders.title') }}</h3>
              <p class="text-grey-7">
                {{ $t('landing.features.alarmsReminders.description') }}
              </p>
            </q-card>
          </div>

          <!-- Feature 3 -->
          <div class="col-12 col-md-6 col-lg-3">
            <q-card flat bordered class="feature-card text-center q-pa-md full-height">
              <q-icon name="flag" size="64px" color="positive" class="q-mb-md" />
              <h3 class="text-h6 text-weight-medium q-mb-sm">{{ $t('landing.features.objectivesGoals.title') }}</h3>
              <p class="text-grey-7">
                {{ $t('landing.features.objectivesGoals.description') }}
              </p>
            </q-card>
          </div>

          <!-- Feature 4 -->
          <div class="col-12 col-md-6 col-lg-3">
            <q-card flat bordered class="feature-card text-center q-pa-md full-height">
              <q-icon name="calendar_month" size="64px" color="purple" class="q-mb-md" />
              <h3 class="text-h6 text-weight-medium q-mb-sm">{{ $t('landing.features.calendarsEvents.title') }}</h3>
              <p class="text-grey-7">
                {{ $t('landing.features.calendarsEvents.description') }}
              </p>
            </q-card>
          </div>

          <!-- Feature 5 -->
          <div class="col-12 col-md-6 col-lg-3">
            <q-card flat bordered class="feature-card text-center q-pa-md full-height">
              <q-icon name="stars" size="64px" color="yellow-8" class="q-mb-md" />
              <h3 class="text-h6 text-weight-medium q-mb-sm">{{ $t('landing.features.levelSystem.title') }}</h3>
              <p class="text-grey-7">
                {{ $t('landing.features.levelSystem.description') }}
              </p>
            </q-card>
          </div>

          <!-- Feature 6 -->
          <div class="col-12 col-md-6 col-lg-3">
            <q-card flat bordered class="feature-card text-center q-pa-md full-height">
              <q-icon name="cloud_sync" size="64px" color="blue" class="q-mb-md" />
              <h3 class="text-h6 text-weight-medium q-mb-sm">{{ $t('landing.features.synchronization.title') }}</h3>
              <p class="text-grey-7">
                {{ $t('landing.features.synchronization.description') }}
              </p>
            </q-card>
          </div>

          <!-- Feature 7 -->
          <div class="col-12 col-md-6 col-lg-3">
            <q-card flat bordered class="feature-card text-center q-pa-md full-height">
              <q-icon name="workspace_premium" size="64px" color="deep-orange" class="q-mb-md" />
              <h3 class="text-h6 text-weight-medium q-mb-sm">{{ $t('landing.features.premium.title') }}</h3>
              <p class="text-grey-7">
                {{ $t('landing.features.premium.description') }}
              </p>
            </q-card>
          </div>

          <!-- Feature 8 -->
          <div class="col-12 col-md-6 col-lg-3">
            <q-card flat bordered class="feature-card text-center q-pa-md full-height">
              <q-icon name="shield" size="64px" color="teal" class="q-mb-md" />
              <h3 class="text-h6 text-weight-medium q-mb-sm">{{ $t('landing.features.security.title') }}</h3>
              <p class="text-grey-7">
                {{ $t('landing.features.security.description') }}
              </p>
            </q-card>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section flex flex-center q-pa-xl">
      <div class="text-center">
        <h2 class="text-h3 text-weight-bold q-mb-md">{{ $t('landing.cta.title') }}</h2>
        <p class="text-h6 q-mb-lg">{{ $t('landing.cta.subtitle') }}</p>
        <div class="q-gutter-md">
          <q-btn
            @click="$router.push('/Register')"
            color="warning"
            size="xl"
            :label="$t('landing.cta.button')"
            icon-right="arrow_forward"
            unelevated
            no-caps
          />
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { computed } from 'vue';
import { useQuasar } from 'quasar';
import { Capacitor } from '@capacitor/core';
import logoLight from '@/assets/logo.png';
import logoDark from '@/assets/logo-dark.png';

export default {
  name: 'LandingPage',

  setup() {
    const $q = useQuasar();

    // Detectar modo oscuro
    const isDarkMode = computed(() => $q.dark.isActive);

    // Detectar si estamos en una app móvil (Capacitor)
    const isMobileApp = Capacitor.isNativePlatform();

    // Seleccionar logo según el tema
    const rutaLogo = logoDark;
    const rutaLogoDark = logoLight;

    return {
      isDarkMode,
      isMobileApp,
      rutaLogo,
      rutaLogoDark
    };
  },

  methods: {
    downloadAPK() {
      // URL del APK en Firebase Hosting
      const apkUrl = 'https://tidy-personal.web.app/tidy-app-latest.apk';

      // Crear elemento <a> temporal para forzar la descarga
      const link = document.createElement('a');
      link.href = apkUrl;
      link.download = 'tidy-app.apk';
      link.target = '_blank';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      // Notificar al usuario
      this.$q.notify({
        type: 'positive',
        message: 'Descargando APK...',
        caption: 'La descarga comenzará en breve',
        icon: 'download',
        position: 'top',
        timeout: 3000
      });
    }
  }
};
</script>

<style scoped>
.landing-page {
  min-height: 100vh
}

.hero-section {
  min-height: 100vh;
  padding: 2rem;
}

.hero-content {
  max-width: 800px;
}

.logo-landing {
  border-radius: 12px;
}

.apk-download-section {
  display: flex;
  justify-content: center;
}

.apk-download-btn {
  font-weight: 500;
  transition: all 0.3s ease;
}

.apk-download-btn:hover {
  transform: scale(1.05);
}

.features-section {
  background: white;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
}

.feature-card {
  transition: all 0.3s ease;
  height: 100%;
}

.feature-card:hover {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  transform: translateY(-8px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.cta-section {
  background: linear-gradient(135deg, #11631c 0%, #2b7be4 100%);
  color: white;
  min-height: 400px;
}

.cta-section h2,
.cta-section p {
  color: white;
}

.footer-section {
  background: #2c3e50;
}

/* Dark mode styles */
.body--dark .landing-page {
  background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
}

.body--dark .features-section {
  background: #1e1e1e;
}

.body--dark .feature-card {
  background: #2d2d2d;
  border-color: #404040;
}

.body--dark .feature-card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
}

.body--dark .footer-section {
  background: #121212;
}

@media (max-width: 600px) {
  .hero-section {
    min-height: 80vh;
  }

  .text-h2 {
    font-size: 2.5rem;
  }

  .text-h5 {
    font-size: 1.2rem;
  }
}
</style>
