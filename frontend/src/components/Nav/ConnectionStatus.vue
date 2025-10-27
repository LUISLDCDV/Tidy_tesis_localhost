<template>
  <div class="connection-status">
    <q-btn
      flat
      dense
      round
      :icon="connectionIcon"
      :color="connectionColor"
      class="connection-btn"
      @click="showConnectionDetails"
    >
      <q-tooltip :delay="300" anchor="bottom middle" self="top middle">
        {{ connectionTooltip }}
      </q-tooltip>
    </q-btn>

    <!-- Indicador de sincronización/actividad -->
    <q-inner-loading
      v-if="isUpdating"
      :showing="isUpdating"
      class="sync-indicator"
    >
      <q-spinner-dots
        :color="connectionColor"
        size="16px"
      />
    </q-inner-loading>
  </div>
</template>

<script>
import { computed, ref, watch } from 'vue'
import { useQuasar } from 'quasar'
import { useI18n } from 'vue-i18n'
import { useNetworkStatus } from '@/composables/useNetworkStatus'
import { requestQueue } from '@/utils/requestQueue'

export default {
  name: 'ConnectionStatus',

  setup() {
    const $q = useQuasar()
    const { t } = useI18n()
    const { isOnline, getConnectionQuality } = useNetworkStatus()

    // Estados del componente
    const isUpdating = ref(false)
    const lastSyncTime = ref(new Date())

    // Estados de conexión
    const CONNECTION_STATES = {
      UPDATED: 'updated',      // ✅ Actualizado - todo en sync
      UPDATING: 'updating',    // 🔄 Actualizando - procesando datos
      OFFLINE: 'offline'       // ❌ Sin conexión
    }

    // Estado actual de conexión
    const connectionState = computed(() => {
      if (!isOnline.value) {
        return CONNECTION_STATES.OFFLINE
      }

      if (isUpdating.value) {
        return CONNECTION_STATES.UPDATING
      }

      return CONNECTION_STATES.UPDATED
    })

    // Iconos y colores para cada estado
    const connectionConfig = computed(() => {
      switch (connectionState.value) {
        case CONNECTION_STATES.UPDATED:
          return {
            icon: 'cloud_done',
            color: 'positive',
            tooltip: t('connection.tooltips.updated'),
            bgClass: 'bg-positive'
          }

        case CONNECTION_STATES.UPDATING:
          return {
            icon: 'cloud_sync',
            color: 'warning',
            tooltip: t('connection.tooltips.updating'),
            bgClass: 'bg-warning'
          }

        case CONNECTION_STATES.OFFLINE:
          return {
            icon: 'cloud_off',
            color: 'negative',
            tooltip: t('connection.tooltips.offline'),
            bgClass: 'bg-negative'
          }

        default:
          return {
            icon: 'cloud_queue',
            color: 'grey',
            tooltip: 'Estado desconocido',
            bgClass: 'bg-grey'
          }
      }
    })

    const connectionIcon = computed(() => connectionConfig.value.icon)
    const connectionColor = computed(() => connectionConfig.value.color)
    const connectionTooltip = computed(() => connectionConfig.value.tooltip)

    // Monitorear actividad de la cola de peticiones
    const monitorRequestQueue = () => {
      // Comprobar periódicamente el estado de la cola
      const checkQueueStatus = () => {
        const queueStatus = requestQueue.getStatus()
        const wasUpdating = isUpdating.value

        // Está actualizando si hay peticiones procesándose o en cola
        isUpdating.value = queueStatus.isProcessing || queueStatus.queueSize > 0

        // Si terminó de actualizar, marcar tiempo de sync
        if (wasUpdating && !isUpdating.value) {
          lastSyncTime.value = new Date()
        }
      }

      // Comprobar cada 500ms
      setInterval(checkQueueStatus, 500)
    }

    // Mostrar detalles de conexión
    const showConnectionDetails = () => {
      const quality = getConnectionQuality()
      const timeSinceSync = Math.floor((Date.now() - lastSyncTime.value.getTime()) / 1000)

      let message = ''
      let caption = ''
      let type = 'info'

      switch (connectionState.value) {
        case CONNECTION_STATES.UPDATED:
          message = `✅ ${t('connection.notifications.updated')}`
          caption = t('connection.notifications.captions.updated', { seconds: timeSinceSync })
          type = 'positive'
          break

        case CONNECTION_STATES.UPDATING:
          message = `🔄 ${t('connection.notifications.updating')}`
          caption = t('connection.notifications.captions.updating')
          type = 'warning'
          break

        case CONNECTION_STATES.OFFLINE:
          message = `❌ ${t('connection.notifications.offline')}`
          caption = t('connection.notifications.captions.offline')
          type = 'negative'
          break
      }

      $q.notify({
        type,
        message,
        caption,
        position: 'top',
        timeout: 3000,
        actions: [{ icon: 'close', color: 'white' }]
      })
    }

    // Watchers para cambios de estado
    watch(isOnline, (newValue) => {
      if (newValue) {
        console.log('🔗 Conexión restaurada')
        lastSyncTime.value = new Date()
      } else {
        console.log('🔗 Conexión perdida')
      }
    })

    // Inicializar monitoreo
    monitorRequestQueue()

    return {
      // Estados reactivos
      isUpdating,
      connectionState,

      // Computed para el template
      connectionIcon,
      connectionColor,
      connectionTooltip,

      // Métodos
      showConnectionDetails
    }
  }
}
</script>

<style scoped>
.connection-status {
  position: relative;
  display: inline-block;
}

.connection-btn {
  transition: all 0.3s ease;
  min-width: 40px;
  min-height: 40px;
  border-radius: 50%;
}

.connection-btn:hover {
  background-color: rgba(255, 255, 255, 0.15);
  transform: scale(1.05);
}

.connection-btn .q-icon {
  transition: all 0.3s ease;
}

.sync-indicator {
  position: absolute;
  top: 0;
  right: 0;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: transparent;
  pointer-events: none;
}

/* Animación sutil para el icono de sincronización */
.connection-btn[color="warning"] .q-icon {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.6; }
  100% { opacity: 1; }
}

/* Dark mode support */
.body--dark .connection-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.body--dark .sync-indicator {
  background: transparent;
}

/* Accessibility */
.connection-btn:focus-visible {
  outline: 2px solid rgba(255, 255, 255, 0.5);
  outline-offset: 2px;
  border-radius: 50%;
}
</style>