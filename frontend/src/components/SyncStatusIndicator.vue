<template>
  <div class="sync-status-indicator">
    <!-- Indicador principal -->
    <q-btn
      v-if="showIndicator"
      :class="indicatorClass"
      :icon="indicatorIcon"
      :color="indicatorColor"
      round
      flat
      size="sm"
      @click="handleClick"
    >
      <!-- Tooltip con información detallada -->
      <q-tooltip 
        :class="tooltipClass"
        anchor="bottom middle"
        self="top middle"
        :offset="[0, 8]"
      >
        <div class="sync-tooltip">
          <div class="sync-tooltip-title">{{ statusTitle }}</div>
          <div class="sync-tooltip-message">{{ statusMessage }}</div>
          <div v-if="hasPendingChanges" class="sync-tooltip-pending">
            {{ pendingChangesText }}
          </div>
          <div v-if="lastSyncTime" class="sync-tooltip-time">
            Última sincronización: {{ formatTime(lastSyncTime) }}
          </div>
        </div>
      </q-tooltip>

      <!-- Loading indicator para estado syncing -->
      <q-inner-loading 
        v-if="isSyncing" 
        :showing="true"
        color="white"
        size="xs"
      />
    </q-btn>

    <!-- Badge para cambios pendientes -->
    <q-badge
      v-if="hasPendingChanges && !isSyncing"
      floating
      color="orange"
      :label="pendingCount"
      class="pending-badge"
    />
  </div>
</template>

<script>
import { computed } from 'vue'
import { useElementsStore } from '@/stores/elements'
import { useQuasar } from 'quasar'

export default {
  name: 'SyncStatusIndicator',
  props: {
    // Mostrar siempre o solo cuando hay problemas
    alwaysShow: {
      type: Boolean,
      default: false
    },
    // Tamaño del indicador
    size: {
      type: String,
      default: 'sm',
      validator: (value) => ['xs', 'sm', 'md', 'lg'].includes(value)
    },
    // Posición del tooltip
    tooltipPosition: {
      type: String,
      default: 'bottom middle'
    }
  },
  setup(props) {
    const $q = useQuasar()
    const elementsStore = useElementsStore()

    // Estado computado
    const isOnline = computed(() => elementsStore.isOnline)
    const isSyncing = computed(() => elementsStore.isSyncing)
    const syncStatus = computed(() => elementsStore.syncStatus)
    const hasPendingChanges = computed(() => elementsStore.hasPendingChanges)
    const lastSyncTime = computed(() => elementsStore.lastSyncTime)
    
    // Simular pending count (en implementación real vendría del store)
    const pendingCount = computed(() => {
      // Esto debería venir del sistema de sincronización real
      return hasPendingChanges.value ? '!' : 0
    })

    const showIndicator = computed(() => {
      if (props.alwaysShow) return true
      
      // Mostrar solo si hay problemas o está sincronizando
      return !isOnline.value || isSyncing.value || hasPendingChanges.value || syncStatus.value === 'error'
    })

    const indicatorIcon = computed(() => {
      switch (syncStatus.value) {
        case 'syncing':
          return 'sync'
        case 'offline':
          return 'cloud_off'
        case 'error':
          return 'sync_problem'
        case 'synced':
          return hasPendingChanges.value ? 'sync_disabled' : 'cloud_done'
        default:
          return 'cloud_queue'
      }
    })

    const indicatorColor = computed(() => {
      switch (syncStatus.value) {
        case 'syncing':
          return 'blue'
        case 'offline':
          return 'orange'
        case 'error':
          return 'red'
        case 'synced':
          return hasPendingChanges.value ? 'orange' : 'green'
        default:
          return 'grey'
      }
    })

    const indicatorClass = computed(() => {
      const classes = ['sync-indicator']
      
      if (isSyncing.value) {
        classes.push('sync-indicator--syncing')
      }
      
      if (!isOnline.value) {
        classes.push('sync-indicator--offline')
      }
      
      return classes.join(' ')
    })

    const tooltipClass = computed(() => {
      return `sync-tooltip--${syncStatus.value}`
    })

    const statusTitle = computed(() => {
      switch (syncStatus.value) {
        case 'syncing':
          return 'Sincronizando...'
        case 'offline':
          return 'Sin conexión'
        case 'error':
          return 'Error de sincronización'
        case 'synced':
          return hasPendingChanges.value ? 'Cambios pendientes' : 'Sincronizado'
        default:
          return 'Estado desconocido'
      }
    })

    const statusMessage = computed(() => {
      switch (syncStatus.value) {
        case 'syncing':
          return 'Guardando cambios en el servidor...'
        case 'offline':
          return 'Los cambios se guardan localmente'
        case 'error':
          return 'No se pudieron sincronizar algunos cambios'
        case 'synced':
          return hasPendingChanges.value 
            ? 'Los cambios se sincronizarán cuando haya conexión'
            : 'Todos los datos están actualizados'
        default:
          return 'Verificando estado de sincronización...'
      }
    })

    const pendingChangesText = computed(() => {
      const count = pendingCount.value
      if (typeof count === 'number' && count > 0) {
        return `${count} cambio${count > 1 ? 's' : ''} pendiente${count > 1 ? 's' : ''}`
      }
      return 'Cambios pendientes'
    })

    // Métodos
    function handleClick() {
      switch (syncStatus.value) {
        case 'offline':
          $q.notify({
            type: 'info',
            message: 'Sin conexión a internet',
            caption: 'Los cambios se guardarán cuando se restaure la conexión',
            icon: 'cloud_off',
            position: 'top',
            timeout: 3000
          })
          break
          
        case 'error':
          $q.dialog({
            title: 'Error de sincronización',
            message: 'Algunos cambios no se pudieron sincronizar. ¿Quieres intentar de nuevo?',
            cancel: true,
            persistent: false
          }).onOk(() => {
            elementsStore.fullSync()
          })
          break
          
        case 'synced':
          if (hasPendingChanges.value && isOnline.value) {
            elementsStore.fullSync()
          }
          break
          
        default:
          // Para estado syncing o desconocido, mostrar info
          $q.notify({
            type: 'info',
            message: statusTitle.value,
            caption: statusMessage.value,
            icon: indicatorIcon.value,
            position: 'top',
            timeout: 2000
          })
      }
    }

    function formatTime(timestamp) {
      if (!timestamp) return 'Nunca'
      
      const date = new Date(timestamp)
      const now = new Date()
      const diffMs = now - date
      const diffMins = Math.floor(diffMs / 60000)
      
      if (diffMins < 1) return 'Hace un momento'
      if (diffMins < 60) return `Hace ${diffMins} min`
      
      const diffHours = Math.floor(diffMins / 60)
      if (diffHours < 24) return `Hace ${diffHours}h`
      
      const diffDays = Math.floor(diffHours / 24)
      return `Hace ${diffDays} día${diffDays > 1 ? 's' : ''}`
    }

    return {
      // Estado
      isOnline,
      isSyncing,
      syncStatus,
      hasPendingChanges,
      lastSyncTime,
      pendingCount,
      
      // Computados
      showIndicator,
      indicatorIcon,
      indicatorColor,
      indicatorClass,
      tooltipClass,
      statusTitle,
      statusMessage,
      pendingChangesText,
      
      // Métodos
      handleClick,
      formatTime
    }
  }
}
</script>

<style scoped>
.sync-status-indicator {
  position: relative;
  display: inline-block;
}

.sync-indicator {
  transition: all 0.3s ease;
}

.sync-indicator--syncing {
  animation: pulse 2s infinite;
}

.sync-indicator--offline {
  opacity: 0.7;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.pending-badge {
  animation: bounce 1s infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}

/* Tooltip styling */
.sync-tooltip {
  max-width: 250px;
  text-align: left;
}

.sync-tooltip-title {
  font-weight: 600;
  margin-bottom: 4px;
  font-size: 0.9rem;
}

.sync-tooltip-message {
  font-size: 0.8rem;
  opacity: 0.9;
  margin-bottom: 6px;
}

.sync-tooltip-pending {
  font-size: 0.75rem;
  color: #ff9800;
  font-weight: 500;
  margin-bottom: 4px;
}

.sync-tooltip-time {
  font-size: 0.7rem;
  opacity: 0.7;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  padding-top: 4px;
  margin-top: 6px;
}

/* Tooltip colors by status */
:deep(.sync-tooltip--syncing) {
  background: #2196f3;
}

:deep(.sync-tooltip--offline) {
  background: #ff9800;
}

:deep(.sync-tooltip--error) {
  background: #f44336;
}

:deep(.sync-tooltip--synced) {
  background: #4caf50;
}

/* Dark mode support */
.body--dark .sync-tooltip-time {
  border-top-color: rgba(0, 0, 0, 0.2);
}
</style>