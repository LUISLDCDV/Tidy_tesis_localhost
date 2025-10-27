<template>
  <q-layout view="lHh Lpr lFf">
    <!-- 1. Header para usuarios NO AUTENTICADOS -->
    <template v-if="!isAuthenticated">
      <NavBarGuest />
    </template>

    <!-- 2. Header para usuarios autenticados en DESKTOP -->
    <template v-if="isAuthenticated && !isMobile">
      <DesktopHeader @toggle-drawer="toggleLeftDrawer" />
    </template>

    <!-- 3. Header para usuarios autenticados en MOBILE -->
    <template v-if="isAuthenticated && isMobile">
      <MobileNavigation />
    </template>

    <!-- Sidebar para usuarios autenticados SOLO EN DESKTOP -->
    <q-drawer
      v-if="isAuthenticated && !isMobile"
      v-model="leftDrawerOpen"
      show-if-above
      bordered
      :width="280"
      side="left"
    >
      <NavBarAuth />
    </q-drawer>
    
    <q-page-container>
      <router-view />
    </q-page-container>

    <!-- Global Notification Manager -->
    <NotificationManager v-if="isAuthenticated" />

    <!-- Permissions Guide Dialog (Android) -->
    <PermissionsGuideDialog />

    <!-- Level Up Animation -->
    <LevelUpAnimation
      v-if="isAuthenticated"
      :show="showLevelUpAnimation"
      :new-level="levelUpData.level"
      :message="levelUpData.message"
      :experience-gained="levelUpData.experienceGained"
      :has-unlocked-content="levelUpData.hasUnlockedContent"
      @close="handleLevelUpClose"
      @continue="handleLevelUpContinue"
      @show-unlocked="handleShowUnlocked"
    />

    <!-- Unlocked Content Modal -->
    <UnlockedContentModal
      v-if="isAuthenticated"
      v-model="showUnlockedModal"
      :new-level="unlockedData.level"
      :unlocked-note-types="unlockedData.noteTypes"
      :unlocked-features="unlockedData.features"
      :unlocked-achievements="unlockedData.achievements"
      :category-info="unlockedData.categoryInfo"
      :motivational-message="unlockedData.motivationalMessage"
      @try-notes="handleTryNotes"
      @continue="handleUnlockedContinue"
    />
    
    <!-- Footer para usuarios no autenticados -->
    <q-footer v-if="!isAuthenticated">
      <GuestFooter />
    </q-footer>
    
    <!-- Footer para usuarios autenticados SOLO EN DESKTOP -->
    <q-footer v-if="isAuthenticated && !isMobile">
      <AuthFooter />
    </q-footer>
  </q-layout>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useQuasar } from 'quasar';
import NavBarGuest from '@/components/Nav/NavBarGuest.vue';
import NavBarAuth from '@/components/Nav/NavBarAuth.vue';
import DesktopHeader from '@/components/Nav/DesktopHeader.vue';
import MobileNavigation from '@/components/Nav/MobileNavigation.vue';
import AuthFooter from '@/components/Footer/AuthFooter.vue';
import GuestFooter from '@/components/Footer/GuestFooter.vue';
import NotificationManager from '@/components/Animations/NotificationManager.vue';
import LevelUpAnimation from '@/components/Animations/LevelUpAnimation.vue';
import UnlockedContentModal from '@/components/Modals/UnlockedContentModal.vue';
import PermissionsGuideDialog from '@/components/Alarms/PermissionsGuideDialog.vue';
import { useAuthStore } from '@/stores/auth';
import { useLevelsStore } from '@/stores/levels';
import { usePaymentsStore } from '@/stores/payments';

export default {
  name: 'App',
  components: {
    NavBarAuth,
    NavBarGuest,
    DesktopHeader,
    MobileNavigation,
    AuthFooter,
    GuestFooter,
    NotificationManager,
    LevelUpAnimation,
    UnlockedContentModal,
    PermissionsGuideDialog
  },
  setup() {
    const authStore = useAuthStore();
    const levelsStore = useLevelsStore();
    const paymentsStore = usePaymentsStore();
    const $q = useQuasar();
    const leftDrawerOpen = ref(true); // Inicializar como true para que se vea por defecto

    // Level up animation state
    const showLevelUpAnimation = ref(false);
    const levelUpData = ref({
      level: 1,
      message: null,
      experienceGained: null,
      hasUnlockedContent: false
    });

    // Unlocked content modal state
    const showUnlockedModal = ref(false);
    const unlockedData = ref({
      level: 1,
      noteTypes: [],
      features: [],
      achievements: [],
      categoryInfo: null,
      motivationalMessage: null
    });

    // Responsive variable for mobile detection
    const isMobile = computed(() => {
      return $q.screen.lt.md; // Móvil: menor a 1024px
    });

    // Navigation methods
    const toggleLeftDrawer = () => {
      leftDrawerOpen.value = !leftDrawerOpen.value;
    };

    const isAuthenticated = computed(() => authStore.isAuthenticated);

    // Watch for level up notifications
    watch(() => levelsStore.getLevelUpNotification, (notification) => {
      if (notification && notification.newLevel) {
        levelUpData.value = {
          level: notification.newLevel,
          message: notification.message,
          experienceGained: null, // Se puede agregar si está disponible
          hasUnlockedContent: !!levelsStore.getNoteUnlockNotification
        };
        showLevelUpAnimation.value = true;
      }
    }, { deep: true });

    // Watch for unlocked content notifications
    watch(() => levelsStore.getNoteUnlockNotification, (notification) => {
      if (notification && notification.unlockedNotes?.length > 0) {
        unlockedData.value = {
          level: notification.newLevel,
          noteTypes: notification.unlockedNotes,
          features: [], // Se puede extender para otras características
          achievements: [], // Se puede extender para logros
          categoryInfo: determineCategoryInfo(notification.newLevel),
          motivationalMessage: generateMotivationalMessage(notification.newLevel)
        };
      }
    }, { deep: true });

    // Level up animation handlers
    const handleLevelUpClose = () => {
      showLevelUpAnimation.value = false;
      levelsStore.clearLevelUpNotification();
    };

    const handleLevelUpContinue = () => {
      showLevelUpAnimation.value = false;
      levelsStore.clearLevelUpNotification();
    };

    const handleShowUnlocked = () => {
      showLevelUpAnimation.value = false;
      showUnlockedModal.value = true;
    };

    // Unlocked content modal handlers
    const handleTryNotes = (noteTypes) => {
      console.log('Trying new note types:', noteTypes);
      showUnlockedModal.value = false;
      levelsStore.clearNoteUnlockNotification();

      // Navigate to notes creation with the first unlocked type
      if (noteTypes.length > 0) {
        // Aquí se puede agregar navegación específica
      }
    };

    const handleUnlockedContinue = () => {
      showUnlockedModal.value = false;
      levelsStore.clearNoteUnlockNotification();
    };

    // Helper functions
    const determineCategoryInfo = (level) => {
      // Determinar información de categoría basada en el nivel
      if (level <= 5) {
        return {
          name: 'Principiante',
          description: 'Aprendiendo lo básico',
          color: '#10b981',
          icon: 'school',
          minLevel: 1,
          maxLevel: 5
        };
      } else if (level <= 15) {
        return {
          name: 'Intermedio',
          description: 'Desarrollando habilidades',
          color: '#f59e0b',
          icon: 'trending_up',
          minLevel: 6,
          maxLevel: 15
        };
      } else if (level <= 30) {
        return {
          name: 'Avanzado',
          description: 'Dominando funcionalidades',
          color: '#8b5cf6',
          icon: 'auto_awesome',
          minLevel: 16,
          maxLevel: 30
        };
      } else {
        return {
          name: 'Experto',
          description: 'Maestro de la productividad',
          color: '#ef4444',
          icon: 'psychology',
          minLevel: 31,
          maxLevel: 50
        };
      }
    };

    const generateMotivationalMessage = (level) => {
      const messages = [
        '¡Sigue así! Tu dedicación está dando frutos.',
        '¡Increíble progreso! Cada paso te acerca más a tus objetivos.',
        '¡Excelente trabajo! Tu constancia es inspiradora.',
        '¡Fantástico! Estás dominando nuevas herramientas.',
        '¡Impresionante! Tu productividad no tiene límites.'
      ];

      return messages[Math.floor(Math.random() * messages.length)];
    };

    // Initialize auth on mount
    onMounted(async () => {
      console.log('Aplicación Vue cargada exitosamente!');
      await authStore.checkAuthStatus();

      // Inicializar payments store si el usuario está autenticado
      if (authStore.isAuthenticated) {
        await paymentsStore.initialize();
      }
    });

    // Watch for authentication changes to initialize payments store
    watch(() => authStore.isAuthenticated, async (isAuth) => {
      if (isAuth) {
        await paymentsStore.initialize();
      }
    });

    return {
      leftDrawerOpen,
      toggleLeftDrawer,
      isAuthenticated,
      isMobile,

      // Level up animation
      showLevelUpAnimation,
      levelUpData,
      handleLevelUpClose,
      handleLevelUpContinue,
      handleShowUnlocked,

      // Unlocked content modal
      showUnlockedModal,
      unlockedData,
      handleTryNotes,
      handleUnlockedContinue
    };
  }
}
</script>


<style>
/* Global application styles */
.q-layout {
  font-family: 'Jua', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

body {
  font-family: 'Jua', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Basic responsive adjustments */
@media (max-width: 1023px) {
  .q-layout {
    padding-bottom: 0;
  }

  /* Ajustar contenido para header fijo en mobile */
  .q-page-container {
    padding-top: 140px; /* Espacio para el header fijo */
  }

  /* Ajustar padding específico para usuarios autenticados en mobile */
  .q-page-container .q-page {
    padding-top: 0;
  }
}

/* Drawer responsive behavior */
@media (min-width: 1024px) {
  .q-drawer {
    display: block !important;
  }
}
</style>


