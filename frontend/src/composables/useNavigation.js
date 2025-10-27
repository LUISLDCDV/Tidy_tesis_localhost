import { ref, computed, toRefs } from 'vue'
import { useQuasar } from 'quasar'

// Estado global del navigation drawer
const leftDrawerOpen = ref(false)
const drawerMini = ref(false)

// Debug del estado global
console.log('Inicializando composable useNavigation');

export function useNavigation() {
  const $q = useQuasar()

  // Inicializar el estado basado en el tamaño de pantalla
  const initializeDrawer = () => {
    leftDrawerOpen.value = $q.screen.gt.sm
    // En desktop, comenzar en modo normal
    drawerMini.value = false
    console.log('Drawer inicializado desde composable:', { 
      leftDrawerOpen: leftDrawerOpen.value, 
      drawerMini: drawerMini.value 
    })
  }

  // Toggle del drawer (abrir/cerrar completamente)
  const toggleLeftDrawer = () => {
    if (!leftDrawerOpen.value) {
      // Si está cerrado, abrir en modo normal
      leftDrawerOpen.value = true
      drawerMini.value = false
    } else {
      // Si está abierto, cerrar completamente
      leftDrawerOpen.value = false
      drawerMini.value = false
    }
  }

  // Toggle modo mini (solo iconos)
  const toggleMiniMode = () => {
    try {
      console.log('toggleMiniMode ejecutado', { 
        isDesktop: $q.screen.gt.sm, 
        drawerOpen: leftDrawerOpen.value, 
        drawerMini: drawerMini.value 
      });
      
      if ($q.screen.gt.sm) { // Solo en desktop
        if (!leftDrawerOpen.value) {
          // Si está cerrado, abrir en modo normal
          leftDrawerOpen.value = true
          drawerMini.value = false
        } else {
          // Si está abierto, alternar entre mini y normal
          drawerMini.value = !drawerMini.value
        }
        
        console.log('Nuevo estado:', { 
          drawerOpen: leftDrawerOpen.value, 
          drawerMini: drawerMini.value 
        });
      } else {
        console.log('No está en desktop, no se puede usar modo mini');
      }
    } catch (error) {
      console.error('Error en toggleMiniMode:', error);
    }
  }

  // Abrir drawer en modo normal
  const openDrawer = () => {
    leftDrawerOpen.value = true
    drawerMini.value = false
  }

  // Cerrar drawer completamente
  const closeDrawer = () => {
    leftDrawerOpen.value = false
    drawerMini.value = false
  }

  // Activar modo mini
  const enableMiniMode = () => {
    if ($q.screen.gt.sm) {
      leftDrawerOpen.value = true
      drawerMini.value = true
    }
  }

  // Desactivar modo mini
  const disableMiniMode = () => {
    drawerMini.value = false
  }

  // Computadas útiles
  const isMobile = computed(() => $q.screen.lt.md)
  const isTablet = computed(() => $q.screen.between('sm', 'md'))
  const isDesktop = computed(() => $q.screen.gt.sm)

  // Estado del drawer
  const drawerState = computed(() => ({
    isOpen: leftDrawerOpen.value,
    isMini: drawerMini.value,
    shouldShowOverlay: isMobile.value,
    isPersistent: isDesktop.value,
    width: drawerMini.value ? 56 : (isMobile.value ? 260 : 280),
    miniWidth: 56,
    // Margen para el contenido principal
    contentMargin: drawerMini.value ? 56 : (isDesktop.value ? 280 : 0)
  }))

  // Asegurar reactividad con toRefs
  const state = {
    leftDrawerOpen,
    drawerMini,
    isMobile,
    isTablet,
    isDesktop,
    drawerState
  }

  return {
    // Estado reactivo
    ...toRefs(state),
    
    // Métodos (no necesitan toRefs)
    toggleLeftDrawer,
    toggleMiniMode,
    openDrawer,
    closeDrawer,
    enableMiniMode,
    disableMiniMode,
    initializeDrawer
  }
}