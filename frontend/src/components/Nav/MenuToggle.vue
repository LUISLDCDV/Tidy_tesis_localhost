<template>
  <q-btn
    v-if="shouldShowToggle"
    dense
    flat
    round
    :icon="icon"
    @click="handleToggle"
    :color="color"
    :class="btnClass"
    :aria-label="ariaLabel"
  >
    <q-tooltip v-if="tooltip" :delay="500">
      {{ tooltip }}
    </q-tooltip>
  </q-btn>
</template>

<script>
import { computed } from 'vue'
import { useNavigation } from '@/composables/useNavigation'
import { useI18n } from 'vue-i18n'

export default {
  name: 'MenuToggle',
  props: {
    // Personalización del botón
    color: {
      type: String,
      default: 'primary'
    },
    icon: {
      type: String,
      default: 'menu'
    },
    btnClass: {
      type: String,
      default: 'q-mr-sm'
    },
    tooltip: {
      type: String,
      default: ''
    },
    // Control de visibilidad
    showOnMobile: {
      type: Boolean,
      default: true
    },
    showOnTablet: {
      type: Boolean,
      default: false
    },
    showOnDesktop: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const { t } = useI18n()
    const { 
      toggleLeftDrawer, 
      leftDrawerOpen,
      isMobile, 
      isTablet, 
      isDesktop 
    } = useNavigation()

    // Determinar si debe mostrar el toggle basado en las props y tamaño de pantalla
    const shouldShowToggle = computed(() => {
      if (isMobile.value && props.showOnMobile) return true
      if (isTablet.value && props.showOnTablet) return true
      if (isDesktop.value && props.showOnDesktop) return true
      return false
    })

    // Aria label para accesibilidad
    const ariaLabel = computed(() => {
      return props.tooltip || 
             (leftDrawerOpen.value ? 
              t('nav.closeMenu', 'Cerrar menú') : 
              t('nav.openMenu', 'Abrir menú'))
    })

    const handleToggle = () => {
      toggleLeftDrawer()
    }

    return {
      shouldShowToggle,
      handleToggle,
      ariaLabel
    }
  }
}
</script>

<style scoped>
/* Animación suave para el ícono */
.q-btn :deep(.q-icon) {
  transition: transform 0.3s ease;
}

.q-btn:hover :deep(.q-icon) {
  transform: rotate(90deg);
}

/* Estados de focus mejorados para accesibilidad */
.q-btn:focus {
  outline: 2px solid var(--q-primary);
  outline-offset: 2px;
}

/* Ripple personalizado */
.q-btn :deep(.q-ripple) {
  color: rgba(var(--q-primary-rgb), 0.2);
}
</style>