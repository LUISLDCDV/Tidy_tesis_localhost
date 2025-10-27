<template>
  <q-header elevated class="text-white" style="background-color: #176F46;">
    <q-toolbar>
      <q-btn
        flat
        dense
        round
        icon="menu"
        aria-label="Menu"
        @click="toggleDrawer"
      />

      <q-toolbar-title class="text-weight-bold">
        <q-img
          :src="currentLogo"
          style="width: 120px; height: auto; max-height: 40px;"
          class="q-mr-sm logo-header"
          no-spinner
          no-transition
          fit="contain"
        />
      </q-toolbar-title>

      <!-- User info and menu for desktop -->
      <div class="row items-center q-gutter-sm">
        <!-- Connection Status -->
        <ConnectionStatus />

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

        <!-- Language Selector with Flag -->
        <q-btn-dropdown
          flat
          dense
          class="language-btn"
          dropdown-icon="none"
        >
          <template v-slot:label>
            <div class="row items-center q-gutter-xs">
              <span class="language-flag">{{ getCurrentLanguageFlag() }}</span>
              <span class="text-body2">{{ currentLocale.toUpperCase() }}</span>
            </div>
          </template>
          <q-list>
            <q-item
              v-for="lang in availableLanguages"
              :key="lang.code"
              clickable
              v-close-popup
              @click="changeLanguage(lang.code)"
              :active="currentLocale === lang.code"
            >
              <q-item-section avatar>
                <span class="language-flag">{{ lang.flag }}</span>
              </q-item-section>
              <q-item-section>{{ lang.name }}</q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>

        <!-- User Level Display -->
        <q-btn
          flat
          dense
          class="level-btn"
          @click="goToLevels"
        >
          <div class="row items-center  level-display">
            <q-icon :name="getLevelIcon()" :color="getLevelColor()" size="24px" />
            <div class="level-info">
              <div class="text-body2 text-weight-bold">
                {{ $t('level.level') }} {{ currentLevel }}
              </div>
              <q-linear-progress
                :value="levelProgress / 100"
                :color="getLevelColor()"
                size="4px"
                class="level-progress"
              />
            </div>
          </div>
          <q-tooltip :delay="300">
            {{ userRank.name }} - {{ currentExperience }}/{{ experienceToNextLevel }} XP
          </q-tooltip>
        </q-btn>


      </div>
    </q-toolbar>
  </q-header>
</template>

<script>
import { computed, onMounted, nextTick, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { useAuthStore } from '@/stores/auth'
import { useLevelsStore } from '@/stores/levels'
import { useUserSettingsStore } from '@/stores/userSettings'
import ConnectionStatus from './ConnectionStatus.vue'
import logoLight from '@/assets/logo.png'
import logoDark from '@/assets/logo-dark.png'

export default {
  name: 'DesktopHeader',
  emits: ['toggle-drawer'],

  components: {
    ConnectionStatus
  },

  setup(props, { emit }) {
    const { t, locale } = useI18n()
    const router = useRouter()
    const $q = useQuasar()
    const authStore = useAuthStore()
    const levelsStore = useLevelsStore()
    const userSettingsStore = useUserSettingsStore()

    // Language management
    const availableLanguages = [
      { code: 'es', name: 'Español', flag: '🇪🇸' },
      { code: 'en', name: 'English', flag: '🇺🇸' },
      { code: 'pt', name: 'Português', flag: '🇧🇷' }
    ]

    const currentLocale = computed(() => locale.value)

    const changeLanguage = async (langCode) => {
      console.log('Cambiando idioma a:', langCode)
      console.log('Idioma actual:', locale.value)

      try {
        // Cambiar idioma en vue-i18n
        locale.value = langCode
        localStorage.setItem('app_language', langCode)

        // Usar nextTick para asegurar que los componentes se actualicen correctamente
        await nextTick()

        // Guardar en configuraciones del usuario si está autenticado
        if (authStore.isAuthenticated) {
          await userSettingsStore.updateSingleSetting('appearance', 'language', langCode)
        }

        console.log('Nuevo idioma establecido:', locale.value)
        console.log('Idioma guardado en localStorage:', localStorage.getItem('app_language'))

        // Actualizar atributo de idioma del documento
        document.documentElement.setAttribute('lang', langCode)

        console.log('Idioma cambiado exitosamente a:', langCode)
      } catch (error) {
        console.error('Error al cambiar idioma:', error)
        // Revertir cambio en caso de error
        locale.value = localStorage.getItem('app_language') || 'es'
      }
    }

    const getCurrentLanguageFlag = () => {
      const lang = availableLanguages.find(l => l.code === currentLocale.value)
      return lang ? lang.flag : '🌐'
    }

    // Dark mode management
    const isDarkMode = computed(() => $q.dark.isActive)

    // Logo selection based on theme
    const currentLogo = computed(() => isDarkMode.value ? logoDark : logoLight)

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

    const getLevelIcon = () => {
      const level = currentLevel.value
      if (level <= 1) return 'emoji_events' // Novato
      if (level <= 2) return 'star' // Principiante
      if (level <= 3) return 'stars' // Intermedio
      if (level <= 4) return 'workspace_premium' // Avanzado
      return 'diamond' // Experto
    }

    const getLevelColor = () => {
      const level = currentLevel.value
      if (level <= 1) return 'grey-6' // Novato
      if (level <= 2) return 'green' // Principiante
      if (level <= 3) return 'orange' // Intermedio
      if (level <= 4) return 'purple' // Avanzado
      return 'pink' // Experto
    }

    // Level management
    const currentLevel = computed(() => levelsStore.getCurrentLevel)
    const currentExperience = computed(() => levelsStore.getCurrentExperience)
    const experienceToNextLevel = computed(() => levelsStore.getExperienceToNextLevel)
    const levelProgress = computed(() => levelsStore.getLevelProgress)
    const userRank = computed(() => levelsStore.getUserRank)

    // User info
    const userName = computed(() => {
      return authStore.currentUser?.name ||
             authStore.currentUser?.nombre ||
             authStore.currentUser?.usuario ||
             t('common.user')
    })

    // Navigation methods
    const toggleDrawer = () => {
      emit('toggle-drawer')
    }

    const goToProfile = () => {
      router.push('/profile')
    }

    const goToLevels = () => {
      router.push('/levels')
    }

    const goToSettings = () => {
      router.push('/settings')
    }

    const logout = async () => {
      await authStore.logout(true)
      router.push('/login')
    }

    // Initialize levels data on component mount and watch for changes
    onMounted(async () => {
      if (authStore.isAuthenticated) {
        await levelsStore.initializeLevelData()
      }
    })

    // Watch for level changes from objectives completion
    watch(
      () => levelsStore.getCurrentLevel,
      (newLevel, oldLevel) => {
        if (newLevel !== oldLevel) {
          console.log('🔄 DesktopHeader: Nivel actualizado de', oldLevel, 'a', newLevel)
        }
      },
      { immediate: true }
    )

    return {
      // Language
      availableLanguages,
      currentLocale,
      changeLanguage,
      getCurrentLanguageFlag,

      // Dark Mode
      isDarkMode,
      toggleTheme,
      currentLogo,

      // Levels
      currentLevel,
      currentExperience,
      experienceToNextLevel,
      levelProgress,
      userRank,
      getLevelIcon,
      getLevelColor,

      // User
      userName,

      // Navigation
      toggleDrawer,
      goToProfile,
      goToLevels,
      goToSettings,
      logout,

      // i18n
      t
    }
  }
}
</script>

<style scoped>
/* Logo header */
.logo-header {
  object-fit: contain;
  border-radius: 12px;
}

/* Theme toggle button */
.theme-btn {
  transition: all 0.3s ease;
  min-width: 40px;
  min-height: 40px;
  border-radius: 50%;
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

/* Language selector */
.language-btn {
  transition: all 0.2s ease;
  min-width: 80px;
  min-height: 40px;
  border-radius: 8px;
}

.language-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.language-flag {
  font-size: 18px;
  line-height: 1;
}

/* Level display */
.level-btn {
  transition: all 0.2s ease;
  border-radius: 12px;
  padding: 6px 12px;
}

.level-btn:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

/* .level-display {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 8px 12px;
  transition: all 0.2s ease;
  min-width: 120px;
} */

/* .level-display:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: scale(1.02);
} */

.level-info {
  min-width: 80px;
}

.level-progress {
  width: 80px;
  margin-top: 2px;
}

/* User menu styling */
.user-menu {
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Dark mode support */
.body--dark .q-header {
  background: var(--q-dark) !important;
  border-bottom: 1px solid #333333;
}

.body--dark .q-toolbar-title {
  color: #e0e0e0;
}

.body--dark .text-body2 {
  color: #e0e0e0;
}

.body--dark .q-btn[color="white"] {
  color: #e0e0e0;
}

.body--dark .q-btn[color="white"]:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.body--dark .theme-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.body--dark .theme-btn[color="amber"] {
  color: #ffc107 !important;
}

.body--dark .language-btn:hover {
  background-color: rgba(255, 255, 255, 0.08);
}

.body--dark .language-flag {
  filter: brightness(1.1);
}

.body--dark .level-btn:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

/* .body--dark .level-display {
  background: rgba(255, 255, 255, 0.15);
  color: #e0e0e0;
} */

.body--dark .level-display:hover {
  /* background: rgba(255, 255, 255, 0.2); */
}

.body--dark .user-menu {
  background-color: #2d2d2d;
  border: 1px solid #333333;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.body--dark .user-menu .q-item {
  color: #e0e0e0;
}

.body--dark .user-menu .q-item:hover {
  background-color: rgba(255, 255, 255, 0.08);
}

.body--dark .user-menu .q-icon {
  color: #b0b0b0;
}

.body--dark .user-menu .q-separator {
  background-color: #424242;
}

/* Dark mode icon color in header */
.body--dark .q-toolbar .q-icon[name="task_alt"] {
  color: #90caf9;
}

/* Accessibility improvements */
.theme-btn:focus-visible,
.language-btn:focus-visible,
.level-btn:focus-visible {
  outline: 2px solid rgba(255, 255, 255, 0.5);
  outline-offset: 2px;
}

.theme-btn:focus-visible {
  border-radius: 50%;
}

.q-btn[color="white"]:focus-visible {
  outline: 2px solid rgba(255, 255, 255, 0.5);
  outline-offset: 2px;
  border-radius: 50%;
}
</style>