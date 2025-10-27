<template>
  <q-list>
    <q-item-label header>
      {{ t('nav.mainNavigation') }}
    </q-item-label>

    <q-item
      v-for="link in links"
      :key="link.name"
      clickable
      v-ripple
      :to="getRoutePath(link.name)"
      :active="isActive(link.name)"
    >
      <q-item-section avatar>
        <q-icon :name="getIcon(link.name)" />
      </q-item-section>

      <q-item-section>
        <q-item-label>{{ t(`nav.${link.name.toLowerCase()}`) }}</q-item-label>
      </q-item-section>
    </q-item>

    <q-separator />

    <!-- User info -->
    <q-item clickable @click="goToUserSettings">
      <q-item-section avatar>
          <q-icon name="settings" color="grey-7" />
      </q-item-section>
      <q-item-section>
        <q-item-label caption>{{ t('nav.settings') }}</q-item-label>
      </q-item-section>
    </q-item>

    <!-- Config section -->
    <q-item
      v-for="link in configLinks"
      :key="link.name"
      clickable
      v-ripple
      :to="getRoutePath(link.name)"
      :active="isActive(link.name)"
    >
      <q-item-section avatar>
        <q-icon :name="getIcon(link.name)" color="grey-7" />
      </q-item-section>

      <q-item-section>
        <q-item-label caption>{{ t(`nav.${link.name.toLowerCase()}`) }}</q-item-label>
      </q-item-section>
    </q-item>
  </q-list>
</template>

<script>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

export default {
  name: 'NavBarAuth',

  setup() {
    const { t } = useI18n();
    const router = useRouter();
    const route = useRoute();
    const authStore = useAuthStore();

    const links = ref([
      { name: 'home' },
      { name: 'alarms' },
      { name: 'notes' },
      { name: 'calendars' },
      { name: 'objectives' },
      { name: 'levels' },
      { name: 'profile' }
    ]);

    const configLinks = ref([
      { name: 'help' },
      { name: 'logout' }
    ]);

    const userName = computed(() => {
      // Intentar obtener el nombre del store de auth
      if (authStore.user && authStore.user.name) {
        return authStore.user.name;
      }

      // Fallback a localStorage si no está en el store
      const userFromStorage = localStorage.getItem('user');
      if (userFromStorage) {
        try {
          const user = JSON.parse(userFromStorage);
          return user.name || t('common.user');
        } catch (error) {
          console.error('Error parsing user from localStorage:', error);
        }
      }

      // Fallback final
      return t('common.user');
    });

    const getRoutePath = (name) => {
      const routes = {
        home: '/Home',
        alarms: '/Alarms',
        notes: '/Notes',
        calendars: '/Calendars',
        objectives: '/Objectives',
        levels: '/levels',
        profile: '/profile',
        helpnav: '/help',
        help: '/help',
        settings: '/settings',
        logout: '/logout'
      };
      return routes[name.toLowerCase()] || '/Home';
    };

    const isActive = (name) => {
      const routePath = getRoutePath(name);
      return route.path === routePath;
    };

    const getIcon = (name) => {
      const icons = {
        home: 'home',
        alarms: 'alarm',
        notes: 'edit_note',
        calendars: 'event',
        objectives: 'flag',
        levels: 'trending_up',
        helpnav: 'help_center',
        help: 'help_center',
        settings: 'settings',
        profile: 'person',
        logout: 'logout'
      };
      return icons[name.toLowerCase()] || 'help';
    };

    const goToUserSettings = () => {
      router.push('/settings');
    };

    return {
      t,
      authStore,
      links,
      configLinks,
      userName,
      getRoutePath,
      isActive,
      getIcon,
      goToUserSettings
    };
  }
};
</script>

<style scoped>
/* Navigation list styling */
.q-list {
  background-color: transparent;
}

.q-item {
  border-radius: 8px;
  margin: 2px 8px;
  transition: all 0.2s ease;
}

.q-item:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.q-item.q-router-link--active {
  background-color: rgba(25, 118, 210, 0.12);
  color: #176F46;
}

.q-item.q-router-link--active .q-icon {
  color: #176F46;
}

.q-item-label {
  font-weight: 500;
}

.q-separator {
  margin: 8px 16px;
}

/* Dark mode support */
.body--dark .q-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.body--dark .q-item.q-router-link--active {
  background-color: rgba(144, 202, 249, 0.16);
  color: #90caf9;
}

.body--dark .q-item.q-router-link--active .q-icon {
  color: #90caf9;
}

.body--dark .q-item-label {
  color: #e0e0e0;
}

.body--dark .q-item-label.q-item-label--header {
  color: #b0b0b0;
}

.body--dark .q-item-label.q-item-label--caption {
  color: #9e9e9e;
}

.body--dark .q-separator {
  background-color: #424242;
}

.body--dark .q-avatar {
  background-color: #424242;
}
</style>
