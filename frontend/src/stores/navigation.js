import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export const useNavigationStore = defineStore('navigation', () => {
  // Estado reactivo
  const leftDrawerOpen = ref(false)
  const drawerMini = ref(false)
  const currentScreenInfo = ref({
    isDesktop: false,
    isMobile: true,
    isTablet: false
  })
  
  console.log('Inicializando NavigationStore')

  // Método para actualizar info de pantalla desde componentes
  function updateScreenInfo(screenInfo) {
    currentScreenInfo.value = screenInfo
  }

  // Getters computados
  const isDesktop = computed(() => currentScreenInfo.value.isDesktop)
  const isMobile = computed(() => currentScreenInfo.value.isMobile)
  const isTablet = computed(() => currentScreenInfo.value.isTablet)

  const drawerState = computed(() => {
    return {
      isOpen: leftDrawerOpen.value,
      isMini: drawerMini.value,
      shouldShowOverlay: currentScreenInfo.value.isMobile,
      isPersistent: currentScreenInfo.value.isDesktop,
      width: drawerMini.value ? 56 : (currentScreenInfo.value.isMobile ? 260 : 280),
      miniWidth: 56,
      contentMargin: drawerMini.value ? 56 : (currentScreenInfo.value.isDesktop ? 280 : 0)
    }
  })

  // Acciones
  function initializeDrawer() {
    leftDrawerOpen.value = currentScreenInfo.value.isDesktop
    drawerMini.value = false
    console.log('Drawer inicializado:', { 
      leftDrawerOpen: leftDrawerOpen.value, 
      drawerMini: drawerMini.value 
    })
  }

  function toggleLeftDrawer() {
    if (!leftDrawerOpen.value) {
      leftDrawerOpen.value = true
      drawerMini.value = false
    } else {
      leftDrawerOpen.value = false
      drawerMini.value = false
    }
    console.log('toggleLeftDrawer:', { 
      leftDrawerOpen: leftDrawerOpen.value, 
      drawerMini: drawerMini.value 
    })
  }

  function toggleMiniMode() {
    try {
      console.log('toggleMiniMode ejecutado en store', { 
        isDesktop: currentScreenInfo.value.isDesktop, 
        drawerOpen: leftDrawerOpen.value, 
        drawerMini: drawerMini.value 
      })
      
      if (currentScreenInfo.value.isDesktop) {
        if (!leftDrawerOpen.value) {
          leftDrawerOpen.value = true
          drawerMini.value = false
        } else {
          drawerMini.value = !drawerMini.value
        }
        
        console.log('Nuevo estado en store:', { 
          drawerOpen: leftDrawerOpen.value, 
          drawerMini: drawerMini.value 
        })
      } else {
        console.log('No está en desktop, no se puede usar modo mini')
      }
    } catch (error) {
      console.error('Error en toggleMiniMode:', error)
    }
  }

  function openDrawer() {
    leftDrawerOpen.value = true
    drawerMini.value = false
  }

  function closeDrawer() {
    leftDrawerOpen.value = false
    drawerMini.value = false
  }

  function enableMiniMode() {
    const $q = useQuasar()
    if ($q.screen.gt.sm) {
      leftDrawerOpen.value = true
      drawerMini.value = true
    }
  }

  function disableMiniMode() {
    drawerMini.value = false
  }

  return {
    // Estado
    leftDrawerOpen,
    drawerMini,
    
    // Getters
    isDesktop,
    isMobile,
    isTablet,
    drawerState,
    
    // Acciones
    initializeDrawer,
    toggleLeftDrawer,
    toggleMiniMode,
    openDrawer,
    closeDrawer,
    enableMiniMode,
    disableMiniMode
  }
})