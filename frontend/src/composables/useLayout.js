import { computed } from 'vue'
import { useNavigation } from './useNavigation'

/**
 * Composable para manejar el layout y espaciado basado en el estado del drawer
 */
export function useLayout() {
  const { drawerState, isDesktop } = useNavigation()

  // Clases CSS computadas para el contenido principal
  const contentClasses = computed(() => ({
    'layout-with-mini-drawer': drawerState.value.isMini && isDesktop.value,
    'layout-with-full-drawer': !drawerState.value.isMini && drawerState.value.isOpen && isDesktop.value,
    'layout-mobile': !isDesktop.value
  }))

  // Estilos inline para casos especiales
  const contentStyles = computed(() => ({
    marginLeft: isDesktop.value && drawerState.value.isOpen 
      ? `${drawerState.value.contentMargin}px` 
      : '0px',
    transition: 'margin-left 0.3s ease'
  }))

  // Ancho disponible para el contenido
  const availableWidth = computed(() => {
    if (!isDesktop.value) return '100%'
    if (!drawerState.value.isOpen) return '100%'
    return `calc(100% - ${drawerState.value.contentMargin}px)`
  })

  // Helpers para verificar estado
  const isDrawerMini = computed(() => drawerState.value.isMini && isDesktop.value)
  const isDrawerOpen = computed(() => drawerState.value.isOpen)

  return {
    // Estado
    drawerState,
    
    // Clases y estilos
    contentClasses,
    contentStyles,
    availableWidth,
    
    // Helpers
    isDrawerMini,
    isDrawerOpen,
    isDesktop
  }
}