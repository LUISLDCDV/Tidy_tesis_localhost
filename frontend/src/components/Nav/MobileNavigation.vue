<template>
  <!-- Mobile Top Navigation Bar -->
  <div v-if="isMobile" class="mobile-nav-header text-white" style="background-color: #176F46;">
    <div class="nav-title-container">
      <div class="logo-brand-container row items-center justify-center q-py-md">
        <q-img
          :src="currentLogo"
          style="width: 150px; height: auto; max-height: 50px; cursor: pointer; border-radius: 12px;"
          @click="navigateTo('/Home')"
          class="logo-mobile"
          fit="contain"
        />
      </div>

      <div class="nav-sections row justify-center q-pt-xs">
        <q-btn
          v-for="item in mainNavItems"
          :key="item.name"
          flat
          round
          :icon="item.icon"
          :color="isCurrentRoute(item.route) ? 'white' : 'grey-5'"
          :style="isCurrentRoute(item.route) ? 'opacity: 1; background: rgba(255,255,255,0.2);' : 'opacity: 0.6;'"
          class="nav-icon-btn q-mx-xs"
          @click="navigateTo(item.route)"
        >
          <q-tooltip :delay="300">{{ item.label }}</q-tooltip>
        </q-btn>
      </div>
    </div>
  </div>

  <!-- Mobile Bottom Navigation Bar -->
  <div v-if="isMobile && isAuthenticated" class="mobile-footer-container">
    <!-- Footer Navigation Bar -->
    <q-footer
      elevated
      class="mobile-nav-footer text-white"
      style="background-color: #176F46;"
    >
      <q-toolbar class="footer-toolbar justify-around">

        <!-- Theme Toggle Button -->
        <q-btn
          flat
          round
          :icon="isDarkMode ? 'light_mode' : 'dark_mode'"
          :color="isDarkMode ? 'amber' : 'white'"
          class="footer-btn"
          size="md"
          @click="toggleTheme"
        >
          <q-tooltip :delay="300">
            {{ isDarkMode ? $t('nav.switchToLight') : $t('nav.switchToDark') }}
          </q-tooltip>
        </q-btn>

        <!-- Connection Status -->
        <div class="connection-status-mobile">
          <ConnectionStatus />
        </div>

        <!-- User Menu Button -->
        <q-btn
          flat
          round
          icon="account_circle"
          :color="isUserRoute() ? 'primary' : 'white'"
          class="footer-btn"
          size="md"
        >
          <q-tooltip :delay="300">{{ $t('nav.profile') }}</q-tooltip>
          <q-menu class="user-menu">
            <q-list style="min-width: 180px">
              <q-item clickable v-close-popup @click="goToProfile">
                <q-item-section avatar>
                  <q-icon name="person" />
                </q-item-section>
                <q-item-section>{{ $t('nav.profile') || 'Perfil' }}</q-item-section>
              </q-item>
              <q-item clickable v-close-popup @click="goToSettings">
                <q-item-section avatar>
                  <q-icon name="settings" />
                </q-item-section>
                <q-item-section>{{ $t('nav.settings') }}</q-item-section>
              </q-item>
              <q-separator />
              <q-item clickable v-close-popup @click="logout">
                <q-item-section avatar>
                  <q-icon name="logout" />
                </q-item-section>
                <q-item-section>{{ $t('nav.logout') || 'Cerrar Sesión' }}</q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </q-btn>

        <!-- Level Display Button -->
        <q-btn
          flat
          class="footer-btn level-btn-compact"
          @click="goToLevels"
        >
          <div class="level-display-compact">
            <q-icon :name="getLevelIcon()" :color="getLevelColor()" size="20px" />
            <div class="level-text">
              <div class="text-caption text-weight-bold">
                Nv. {{ currentLevel }}
              </div>
              <q-linear-progress
                :value="levelProgress / 100"
                :color="getLevelColor()"
                size="2px"
                class="level-progress-mini"
              />
            </div>
          </div>
          <q-tooltip :delay="300">
            {{ userRank.name }} - {{ currentExperience }}/{{ experienceToNextLevel }} XP
          </q-tooltip>
        </q-btn>

      </q-toolbar>
    </q-footer>
  </div>
</template>

<script>
import { useRouter, useRoute } from 'vue-router';
import { computed, onMounted, watch } from 'vue';
import { useQuasar } from 'quasar';
import { useAuthStore } from '@/stores/auth';
import { useI18n } from 'vue-i18n';
import { useLevelsStore } from '@/stores/levels';
import ConnectionStatus from './ConnectionStatus.vue';
import logoLight from '@/assets/logo.png';
import logoDark from '@/assets/logo-dark.png';


export default {
  name: 'MobileNavigation',

  components: {
    ConnectionStatus
  },

  setup() {
    const router = useRouter();
    const route = useRoute();
    const $q = useQuasar();
    const authStore = useAuthStore();
    const { t } = useI18n();

    const levelsStore = useLevelsStore();

    const currentLevel = computed(() => levelsStore.getCurrentLevel);
    const currentExperience = computed(() => levelsStore.getCurrentExperience);
    const experienceToNextLevel = computed(() => levelsStore.getExperienceToNextLevel);
    const levelProgress = computed(() => levelsStore.getLevelProgress);
    const userRank = computed(() => levelsStore.getUserRank);

    // Mobile detection
    const isMobile = computed(() => $q.screen.lt.md);

    // Main navigation items for header
    const mainNavItems = [
      {
        name: 'alarms',
        icon: 'alarm',
        label: t('nav.alarms'),
        route: '/Alarms'
      },
      {
        name: 'notes',
        icon: 'edit_note',
        label: t('nav.notes'),
        route: '/Notes'
      },
      {
        name: 'calendars',
        icon: 'event',
        label: t('nav.calendars'),
        route: '/Calendars'
      },
      {
        name: 'objectives',
        icon: 'flag',
        label: t('nav.objectives'),
        route: '/Objectives'
      }
    ];

    // Check if current route matches
    const isCurrentRoute = (targetRoute) => {
      return route.path === targetRoute || route.path.startsWith(targetRoute);
    };

    // Check if current route is user-related
    const isUserRoute = () => {
      return route.path.includes('/user') || route.path.includes('/profile') || route.path.includes('/settings');
    };

    // Navigation method
    const navigateTo = (path) => {
      router.push(path);
    };

    // User menu methods
    const openUserMenu = () => {
      // El menú se abre automáticamente con q-menu
    };

    const goToProfile = () => {
      router.push('/profile');
    };

    const goToSettings = () => {
      router.push('/settings');
    };

    const goToLevels = () => {
      router.push('/levels');
    };

    const logout = async () => {
      await authStore.logout(true); // logout manual
      router.push('/login');
    };


    const getLevelIcon = () => {
      const level = currentLevel.value;
        if (level <= 1) return 'emoji_events';
        if (level <= 2) return 'star';
        if (level <= 3) return 'stars';
        if (level <= 4) return 'workspace_premium';
        return 'diamond';
      };

      const getLevelColor = () => {
        const level = currentLevel.value;
        if (level <= 1) return 'grey-6';
        if (level <= 2) return 'green';
        if (level <= 3) return 'orange';
        if (level <= 4) return 'purple';
        return 'pink';
      };

    const isAuthenticated = computed(() => authStore.isAuthenticated);
    
    const currentUserName = computed(() => {
      return authStore.currentUser?.name ||
             authStore.currentUser?.nombre ||
             authStore.currentUser?.usuario ||
             t('nav.profile');
    });

    // Dark mode management
    const isDarkMode = computed(() => $q.dark.isActive);

    // Logo selection based on theme
    const currentLogo = computed(() => isDarkMode.value ? logoDark : logoLight);

    const toggleTheme = () => {
      $q.dark.toggle();
      saveThemePreference();
      showThemeNotification();
    };

    const saveThemePreference = () => {
      try {
        localStorage.setItem('darkMode', $q.dark.isActive.toString());
        localStorage.setItem('theme', $q.dark.isActive ? 'dark' : 'light');
      } catch (error) {
        console.warn('No se pudo guardar la preferencia de tema:', error);
      }
    };

    const showThemeNotification = () => {
      const themeMessage = $q.dark.isActive
        ? t('nav.darkModeEnabled') || 'Modo oscuro activado'
        : t('nav.lightModeEnabled') || 'Modo claro activado';
    };

    // Initialize levels data on component mount
    onMounted(async () => {
      if (authStore.isAuthenticated) {
        await levelsStore.initializeLevelData();
      }
    });

    // Watch for level changes from objectives completion
    watch(
      () => levelsStore.getCurrentLevel,
      (newLevel, oldLevel) => {
        if (newLevel !== oldLevel) {
          console.log('🔄 MobileNavigation: Nivel actualizado de', oldLevel, 'a', newLevel);
        }
      },
      { immediate: true }
    );

    return {
      mainNavItems,
      isCurrentRoute,
      isUserRoute,
      navigateTo,
      openUserMenu,
      goToProfile,
      goToSettings,
      goToLevels,
      logout,
      isAuthenticated,
      isMobile,
      currentUserName,
      isDarkMode,
      currentLogo,
      toggleTheme,
      currentLevel,
      currentExperience,
      experienceToNextLevel,
      levelProgress,
      userRank,
      getLevelIcon,
      getLevelColor,
      t
    };
  }
};
</script>

<style scoped>
/* ========== MOBILE HEADER ========== */
.mobile-nav-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Header with logo and brand in horizontal layout */
.logo-brand-container {
  padding: 16px;
  gap: 8px;
}

.logo-mobile {
  transition: all 0.3s ease;
  border-radius: 12px;
}

.logo-mobile:hover {
  transform: scale(1.05);
}

.logo-mobile:active {
  transform: scale(0.95);
}

/* Navigation icons in header */
.nav-icon-btn {
  transition: all 0.3s ease;
  min-width: 48px;
  min-height: 48px;
  margin: 0 4px;
}

.nav-icon-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.nav-icon-btn:active {
  transform: scale(0.95);
}

/* ========== MOBILE FOOTER CONTAINER ========== */
.mobile-footer-container {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 1000;
}


/* ========== FOOTER NAVIGATION BAR ========== */
.mobile-nav-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
}

.footer-toolbar {
  min-height: 64px;
  padding: 8px 24px;
}

/* Footer buttons */
.footer-btn {
  min-width: 56px;
  min-height: 56px;
  border-radius: 16px;
  transition: all 0.3s ease;
}

/* Connection status in footer */
.connection-status-mobile {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 56px;
  min-height: 56px;
}

.connection-status-mobile :deep(.connection-status) {
  display: flex;
  align-items: center;
  justify-content: center;
}

.connection-status-mobile :deep(.connection-btn) {
  min-width: 48px;
  min-height: 48px;
  border-radius: 16px;
  transition: all 0.3s ease;
}

.connection-status-mobile :deep(.connection-btn:hover) {
  background-color: rgba(255, 255, 255, 0.15);
  transform: translateY(-2px);
}

.footer-btn:hover {
  background-color: rgba(255, 255, 255, 0.15);
  transform: translateY(-2px);
}

.footer-btn:active {
  transform: translateY(0) scale(0.95);
}

/* ========== LEVEL DISPLAY COMPACT ========== */
.level-btn-compact {
  padding: 8px 12px;
}

.level-display-compact {
  display: flex;
  align-items: center;
  gap: 8px;
}

.level-text {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  min-width: 32px;
}

.level-progress-mini {
  width: 32px;
  margin-top: 2px;
}

/* ========== USER MENU STYLING ========== */
.user-menu {
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  border: 1px solid #e5e7eb;
}

/* Position user menu more to the right on mobile */
@media (max-width: 1023px) {
  .user-menu {
    transform: translateX(20px);
  }
}

/* ========== ACTIVE STATE ANIMATIONS ========== */
.nav-icon-btn[color="primary"],
.footer-btn[color="primary"] {
  animation: activeIconPulse 0.4s ease-out;
}

@keyframes activeIconPulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

/* ========== RESPONSIVE ADJUSTMENTS ========== */
@media (max-width: 360px) {
  .logo-mobile {
    height: 32px !important;
  }

  .logo-brand-container {
    padding: 12px 8px;
  }

  .footer-toolbar {
    padding: 6px 16px;
  }

  .footer-btn {
    min-width: 48px;
    min-height: 48px;
  }

  .connection-status-mobile {
    min-width: 48px;
    min-height: 48px;
  }

  .connection-status-mobile :deep(.connection-btn) {
    min-width: 44px;
    min-height: 44px;
  }
}

/* ========== DARK MODE SUPPORT ========== */
.body--dark .mobile-nav-header {
  background-color: #1e1e1e !important;
  border-color: #333333;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
}


.body--dark .mobile-nav-footer {
  background-color: #1e1e1e !important;
  border-color: #333333;
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
}

.body--dark .footer-btn:hover {
  background-color: rgba(255, 255, 255, 0.08);
}

.body--dark .connection-status-mobile :deep(.connection-btn:hover) {
  background-color: rgba(255, 255, 255, 0.08);
}

.body--dark .footer-btn[color="amber"] {
  color: #ffc107 !important;
}

.body--dark .footer-btn[color="primary"] {
  color: #90caf9 !important;
}

/* Dark mode user menu */
.body--dark .user-menu {
  background-color: #2d2d2d;
  border-color: #424242;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
}

.body--dark .user-menu :deep(.q-item) {
  color: #e0e0e0;
}

.body--dark .user-menu :deep(.q-item:hover) {
  background-color: rgba(255, 255, 255, 0.08);
}

.body--dark .user-menu :deep(.q-icon) {
  color: #b0b0b0;
}

.body--dark .user-menu :deep(.q-separator) {
  background-color: #424242;
}

/* Dark mode navigation icons */
.body--dark .nav-icon-btn[color="primary"] {
  color: #90caf9 !important;
  background-color: rgba(144, 202, 249, 0.16);
}

.body--dark .nav-icon-btn:hover {
  background-color: rgba(255, 255, 255, 0.08);
}

/* Touch optimization */
@media (pointer: coarse) {
  .nav-icon-btn,
  .footer-btn,
  .floating-plus-btn-pezon {
    touch-action: manipulation;
  }
}
</style>