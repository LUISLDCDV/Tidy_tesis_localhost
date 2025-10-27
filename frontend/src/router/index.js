import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

// Lazy loading components - Componentes críticos que se cargan inmediatamente
import UserLogin from '@/components/User/UserLogin.vue';
import UserRegister from '@/components/User/UserRegister.vue';
import LandingPage from '@/components/LandingPage.vue';

// Lazy loading - Componentes que se cargan dinámicamente
const HomePage = () => import('@/components/Elements/ElementsContainer.vue');
const AlarmasList = () => import('@/components/Elements/Alarms/AlarmsList.vue');
const DebugAlarms = () => import('@/pages/DebugAlarms.vue');
const VerifyEmail = () => import('@/pages/VerifyEmail.vue');
const NormalNote = () => import('@/components/Elements/Notes/tipos/NormalNote/NormalNote.vue');
const NotesList = () => import('@/components/Elements/Notes/NotesList.vue');
const UserProfile = () => import('@/components/User/UserProfile.vue');
const UserSettings = () => import('@/components/User/UserSettings.vue');
const ObjetivesList = () => import('@/components/Elements/Objetives/ObjetivesList.vue');
const StepsHome = () => import('@/components/Elements/Objetives/StepsHome.vue');
const CalendarList = () => import('@/components/Elements/Calendary/CalendarList.vue');
const PremiumSuccess = () => import('@/components/Premium/PremiumSuccess.vue');
const PremiumError = () => import('@/components/Premium/PremiumError.vue');
const LevelDashboard = () => import('@/components/Levels/LevelDashboard.vue');
const HelpCenter = () => import('@/components/Help/HelpCenter.vue');

const routes = [
  {
    path: '/',
    name: 'landing',
    component: LandingPage,
    meta: { requiresAuth: false }
  },
  {
    path: '/login',
    name: 'login',
    component: UserLogin,
    meta: { requiresAuth: false }
  },
  {
    path: '/Register',
    name: 'Register',
    component: UserRegister,
    meta: { requiresAuth: false }
  },
  {
    path: '/logout',
    name: 'logout',
    beforeEnter: async (to, from, next) => {
      const authStore = useAuthStore();
      try {
        await authStore.logout(true); // logout manual desde ruta /logout
      } catch (error) {
        console.error('Error durante logout:', error);
      }
      // Redirigir a landing page sin verificar autenticación
      next({ name: 'landing', replace: true });
    }
  },
  { 
    path: '/Home', 
    name: 'Home', 
    component: HomePage, 
    meta: { requiresAuth: true } 
  },
  {
    path: '/Alarms',
    name: 'AlarmasList',
    component: AlarmasList,
    meta: { requiresAuth: true }
  },
  {
    path: '/debug',
    name: 'DebugAlarms',
    component: DebugAlarms,
    meta: { requiresAuth: false }
  },
  { 
    path: '/Notes', 
    name: 'NotesList', 
    component: NotesList, 
    meta: { requiresAuth: true } 
  },
  { 
    path: '/Objectives', 
    name: 'ObjetivesList', 
    component: ObjetivesList, 
    meta: { requiresAuth: true } 
  },
  {
    path: '/Calendars',
    name: 'CalendarList',
    component: CalendarList,
    meta: { requiresAuth: true }
  },
  {
    path: '/levels',
    name: 'Levels',
    component: LevelDashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/Steps/:id',
    name: 'StepsHome',
    component: StepsHome,
    props: true,
    meta: { requiresAuth: true }
  },
  {
    path: '/Note/:type/:modo?/:id?',
    name: 'DynamicNote',
    component: () => import('@/components/Elements/Notes/DinamicNote.vue'),
    props: true,
    meta: { requiresAuth: true }
  },
  {
    path: '/user/:id/:name',
    name: 'UserProfile',
    component: UserProfile,
    props: true,
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: UserProfile,
    props: true,
    meta: { requiresAuth: true }
  },
  {
    path: '/note/:nombre',
    name: 'NormalNote',
    component: NormalNote,
    props: true,
    meta: { requiresAuth: true }
  },
  {
    path: '/settings',
    name: 'Settings',
    component: UserSettings,
    meta: { requiresAuth: true }
  },
  {
    path: '/premium-success',
    name: 'PremiumSuccess',
    component: PremiumSuccess,
    meta: { requiresAuth: true }
  },
  {
    path: '/premium-error',
    name: 'PremiumError',
    component: PremiumError,
    meta: { requiresAuth: true }
  },
  {
    path: '/help',
    name: 'Help',
    component: HelpCenter,
    meta: { requiresAuth: false }
  },
  {
    path: '/verify-email',
    name: 'VerifyEmail',
    component: VerifyEmail,
    meta: { requiresAuth: false }
  }
];

const router = createRouter({
  history: createWebHistory('/'),
  routes
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  // Permitir acceso directo a landing page
  if (to.path === '/') {
    // Si está autenticado, redirigir a Home
    if (authStore.isAuthenticated) {
      next('/Home');
      return;
    }
    // Si no está autenticado, permitir acceso a landing
    next();
    return;
  }

  // Permitir acceso directo a logout sin verificar autenticación
  if (to.name === 'logout') {
    next();
    return;
  }

  // Si el usuario está autenticado y va a login/register, redirigir a Home
  if (authStore.isAuthenticated && (to.name === 'login' || to.name === 'Register')) {
    next('/Home');
    return;
  }

  // Verificar rutas protegidas
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      next('/');
    } else {
      next();
    }
  } else {
    next();
  }
});

export default router;
