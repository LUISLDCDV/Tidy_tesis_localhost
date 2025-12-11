<template>
  <div class="guest-header">
    <div class="header-content">
      <div class="brand">
        <q-img
          :src="currentLogo"
          style="width: 120px; height: auto; max-height: 40px; cursor: pointer; border-radius: 12px;"
          @click="navigateToLogin"
          class="brand-logo"
          fit="contain"
        />
      </div>

      <!-- Desktop navigation -->
      <div class="nav-buttons desktop-nav">
        <!-- Dark Mode Toggle -->
        <q-btn
          flat
          dense
          round
          :icon="isDarkMode ? 'light_mode' : 'dark_mode'"
          :color="isDarkMode ? 'amber' : 'blue-grey-1'"
          class="theme-btn"
          @click="toggleTheme"
        >
          <q-tooltip :delay="300">
            {{ isDarkMode ? $t('nav.switchToLight') : $t('nav.switchToDark') }}
          </q-tooltip>
        </q-btn>

        <!-- Language Selector -->
        <q-btn
          flat
          dense
          round
          icon="language"
          color="blue-grey-1"
          class="theme-btn"
        >
          <q-tooltip :delay="300">{{ $t('nav.language') }}</q-tooltip>
          <q-menu>
            <q-list style="min-width: 150px">
              <q-item
                v-for="lang in languages"
                :key="lang.value"
                clickable
                v-close-popup
                @click="changeLanguage(lang)"
              >
                <q-item-section avatar>
                  <span style="font-size: 1.2rem">{{ lang.flag }}</span>
                </q-item-section>
                <q-item-section>{{ lang.label }}</q-item-section>
                <q-item-section side v-if="currentLanguage === lang.value">
                  <q-icon name="check" color="primary" size="sm" />
                </q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </q-btn>

        <q-btn
          flat
          :to="{ path: '/login' }"
          icon="login"
          class="nav-btn"
        >
          <q-tooltip :delay="300">{{ $t('auth.login') }}</q-tooltip>
        </q-btn>
        <q-btn
          unelevated
          :to="{ path: '/Register' }"
          icon="person_add"
          class="nav-btn"
        >
          <q-tooltip :delay="300">{{ $t('auth.register') }}</q-tooltip>
        </q-btn>
      </div>

      <!-- Mobile navigation -->
      <q-btn
        flat
        round
        icon="menu"
        class="mobile-menu"
        @click="drawer = !drawer"
      />
    </div>

    <!-- Mobile drawer overlay -->
    <div v-if="drawer" class="mobile-overlay" @click="drawer = false">
      <div class="mobile-drawer" @click.stop>
        <div class="drawer-header">{{ $t('nav.toggleMenu') }}</div>

        <!-- Dark Mode Toggle -->
        <div class="drawer-item" @click="toggleTheme">
          <q-icon :name="isDarkMode ? 'light_mode' : 'dark_mode'" :color="isDarkMode ? 'amber' : 'grey-7'" />
          <span>{{ isDarkMode ? $t('nav.switchToLight') : $t('nav.switchToDark') }}</span>
        </div>

        <!-- Language Selector -->
        <div class="drawer-item" @click.stop>
          <q-icon name="language" color="grey-7" />
          <span>{{ $t('nav.language') }}</span>
          <q-menu>
            <q-list style="min-width: 150px">
              <q-item
                v-for="lang in languages"
                :key="lang.value"
                clickable
                v-close-popup
                @click="changeLanguage(lang)"
              >
                <q-item-section avatar>
                  <span style="font-size: 1.2rem">{{ lang.flag }}</span>
                </q-item-section>
                <q-item-section>{{ lang.label }}</q-item-section>
                <q-item-section side v-if="currentLanguage === lang.value">
                  <q-icon name="check" color="primary" size="sm" />
                </q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </div>

        <div class="drawer-separator"></div>

        <div class="drawer-item" @click="navigateAndClose('/login')">
          <q-icon name="login" />
          <span>{{ $t('auth.login') }}</span>
        </div>

        <div class="drawer-item" @click="navigateAndClose('/Register')">
          <q-icon name="person_add" />
          <span>{{ $t('auth.register') }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import logoLight from '@/assets/logo.png'
import logoDark from '@/assets/logo-dark.png'

export default {
  name: 'NavBarGuest',

  setup() {
    const $q = useQuasar()
    const { t, locale } = useI18n()
    const router = useRouter()

    // Language management
    const currentLanguage = ref(locale.value || 'es')
    const languages = ref([
      { label: 'Español', value: 'es', flag: '🇪🇸' },
      { label: 'English', value: 'en', flag: '🇺🇸' },
      { label: 'Português', value: 'pt', flag: '🇧🇷' }
    ])

    const changeLanguage = (language) => {
      currentLanguage.value = language.value
      locale.value = language.value
      localStorage.setItem('language', language.value)
      localStorage.setItem('app_language', language.value)

      $q.notify({
        type: 'positive',
        message: `${language.flag} ${t('nav.languageChanged')} ${language.label}`,
        icon: 'language',
        position: 'top',
        timeout: 2000
      })
    }

    // Dark mode management
    const isDarkMode = computed(() => $q.dark.isActive)

    // Logo selection based on theme
    const currentLogo = computed(() => isDarkMode.value ? logoDark : logoLight)

    const navigateToLogin = () => {
      router.push('/login')
    }

    const toggleTheme = () => {
      $q.dark.toggle()
      saveThemePreference()
      showThemeNotification()
    }

    const saveThemePreference = () => {
      try {
        localStorage.setItem('darkMode', $q.dark.isActive.toString())
        localStorage.setItem('theme', $q.dark.isActive ? 'dark' : 'light')
      } catch (error) {
        console.warn('No se pudo guardar la preferencia de tema:', error)
      }
    }

    const showThemeNotification = () => {
      const themeMessage = $q.dark.isActive
        ? t('nav.darkModeEnabled') || 'Modo oscuro activado'
        : t('nav.lightModeEnabled') || 'Modo claro activado'

      $q.notify({
        type: 'positive',
        message: themeMessage,
        icon: $q.dark.isActive ? 'dark_mode' : 'light_mode',
        position: 'top',
        timeout: 2000
      })
    }

    return {
      isDarkMode,
      currentLogo,
      navigateToLogin,
      toggleTheme,
      currentLanguage,
      languages,
      changeLanguage,
      t
    }
  },

  data() {
    return {
      drawer: false
    }
  },

  methods: {
    navigateAndClose(path) {
      this.$router.push(path);
      this.drawer = false;
    }
  }
}
</script>

<style scoped>
.guest-header {
  background: #176F46;
  color: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  position: relative;
  z-index: 1001;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 24px;
  min-height: 64px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 8px;
}

.brand-logo {
  transition: all 0.3s ease;
  border-radius: 12px;
}

.brand-logo:hover {
  transform: scale(1.05);
}

.nav-buttons {
  display: flex;
  gap: 12px;
  align-items: center;
}

.theme-btn {
  transition: all 0.3s ease;
  min-width: 40px;
  min-height: 40px;
  border-radius: 50%;
  margin-right: 8px;
}

.theme-btn:hover {
  background-color: rgba(255, 255, 255, 0.15);
  transform: scale(1.05);
}

.theme-btn .q-icon {
  transition: all 0.3s ease;
}

.theme-btn:active .q-icon {
  transform: rotate(180deg);
}

.nav-btn {
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.nav-btn:hover {
  transform: translateY(-1px);
}

.mobile-menu {
  display: none;
}

/* Mobile styles */
@media (max-width: 1023px) {
  .desktop-nav {
    display: none;
  }
  
  .mobile-menu {
    display: flex;
  }
  
  .header-content {
    padding: 8px 16px;
    min-height: 56px;
  }
}

.mobile-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 2000;
  display: flex;
  justify-content: flex-end;
  align-items: flex-start;
  padding-top: 56px;
}

.mobile-drawer {
  background: white;
  width: 280px;
  height: calc(100vh - 56px);
  box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
  padding: 16px;
}

.drawer-header {
  font-size: 1rem;
  font-weight: 600;
  color: #666;
  margin-bottom: 16px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.drawer-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  margin: 4px 0;
}

.drawer-item:hover {
  background-color: rgba(var(--q-primary), 0.1);
}

.drawer-item span {
  font-weight: 500;
}

.drawer-separator {
  height: 1px;
  background-color: rgba(0, 0, 0, 0.1);
  margin: 8px 16px;
}

/* Dark mode support */
.body--dark .guest-header {
  background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%);
  color: #e0e0e0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}


.body--dark .nav-btn {
  color: #e0e0e0;
}

.body--dark .nav-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.body--dark .nav-btn[color="white"] {
  background-color: #2d2d2d;
  color: #90caf9;
  border: 1px solid #333333;
}

.body--dark .nav-btn[color="white"]:hover {
  background-color: #3d3d3d;
}

.body--dark .mobile-menu {
  color: #e0e0e0;
}

.body--dark .mobile-menu:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.body--dark .mobile-drawer {
  background: #2d2d2d;
  color: #e0e0e0;
  box-shadow: -2px 0 8px rgba(0, 0, 0, 0.3);
}

.body--dark .drawer-header {
  color: #b0b0b0;
}

.body--dark .drawer-item {
  color: #e0e0e0;
}

.body--dark .drawer-item:hover {
  background-color: rgba(144, 202, 249, 0.16);
}

.body--dark .theme-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.body--dark .theme-btn[color="amber"] {
  color: #ffc107 !important;
}

.body--dark .drawer-separator {
  background-color: rgba(255, 255, 255, 0.1);
}

.body--dark .drawer-item .q-icon {
  color: #b0b0b0;
}
</style>