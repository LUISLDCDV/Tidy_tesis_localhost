<template>
  <q-dialog v-model="showDialog" persistent>
    <q-card style="min-width: 350px; max-width: 500px">
      <q-card-section class="row items-center q-pb-none">
        <q-icon name="info" color="primary" size="32px" class="q-mr-sm" />
        <div class="text-h6">Configuración de Alarmas</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section>
        <p class="text-body1 q-mb-md">
          Para que las alarmas funcionen correctamente en segundo plano,
          necesitas configurar algunos permisos en Android:
        </p>

        <q-list bordered separator>
          <q-item>
            <q-item-section avatar>
              <q-icon
                :name="permissions.notifications ? 'check_circle' : 'cancel'"
                :color="permissions.notifications ? 'positive' : 'negative'"
              />
            </q-item-section>
            <q-item-section>
              <q-item-label>Notificaciones</q-item-label>
              <q-item-label caption>
                Permitir notificaciones de alarmas
              </q-item-label>
            </q-item-section>
          </q-item>

          <q-item>
            <q-item-section avatar>
              <q-icon name="warning" color="orange" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Optimización de Batería</q-item-label>
              <q-item-label caption>
                Desactivar para que funcionen en segundo plano
              </q-item-label>
            </q-item-section>
          </q-item>

          <q-item v-if="isAndroid12Plus">
            <q-item-section avatar>
              <q-icon
                :name="permissions.exactAlarms ? 'check_circle' : 'cancel'"
                :color="permissions.exactAlarms ? 'positive' : 'orange'"
              />
            </q-item-section>
            <q-item-section>
              <q-item-label>Alarmas Exactas (Android 12+)</q-item-label>
              <q-item-label caption>
                Activar "Alarmas y recordatorios"
              </q-item-label>
            </q-item-section>
          </q-item>

          <q-item>
            <q-item-section avatar>
              <q-icon name="lock_open" color="blue" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Pantalla Bloqueada</q-item-label>
              <q-item-label caption>
                Mostrar notificaciones con pantalla bloqueada
              </q-item-label>
            </q-item-section>
          </q-item>
        </q-list>

        <q-expansion-item
          class="q-mt-md"
          icon="settings"
          label="Instrucciones detalladas"
          header-class="bg-grey-3"
        >
          <q-card>
            <q-card-section>
              <div class="text-body2" style="white-space: pre-line">
                {{ manufacturerInstructions }}
              </div>
            </q-card-section>
          </q-card>
        </q-expansion-item>

        <div class="q-mt-md bg-orange-1 q-pa-md rounded-borders">
          <div class="text-weight-bold text-orange-9 q-mb-xs">
            ⚠️ Importante
          </div>
          <div class="text-body2 text-orange-9">
            Sin estos permisos, las alarmas pueden NO sonar cuando la app
            esté cerrada o el teléfono bloqueado.
          </div>
        </div>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          flat
          label="Más tarde"
          color="grey"
          v-close-popup
        />
        <q-btn
          label="Abrir configuración"
          color="primary"
          @click="openSettings"
          icon="open_in_new"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import androidPermissions from '@/utils/androidPermissions'

export default {
  name: 'PermissionsGuideDialog',

  setup() {
    const showDialog = ref(false)
    const permissions = ref({
      notifications: false,
      exactAlarms: false,
      batteryOptimization: 'unknown'
    })
    const manufacturerInstructions = ref('')
    const isAndroid12Plus = ref(false)

    const handlePermissionsGuide = (event) => {
      console.log('📱 Mostrando guía de permisos:', event.detail)
      permissions.value = event.detail.permissions || {}
      manufacturerInstructions.value = event.detail.guide || androidPermissions.getManufacturerSpecificInstructions()
      showDialog.value = true
    }

    const openSettings = () => {
      // Intentar abrir configuración de la app
      if (window.Capacitor?.Plugins?.App) {
        window.Capacitor.Plugins.App.openSettings()
          .then(() => {
            console.log('✅ Configuración abierta')
          })
          .catch(error => {
            console.error('Error abriendo configuración:', error)
          })
      } else {
        console.warn('App plugin no disponible')
      }

      showDialog.value = false
    }

    onMounted(async () => {
      // Escuchar evento de guía de permisos
      window.addEventListener('show-permissions-guide', handlePermissionsGuide)

      // Detectar versión de Android
      try {
        if (window.Capacitor?.Plugins?.Device) {
          const info = await window.Capacitor.Plugins.Device.getInfo()
          const androidVersion = parseInt(info.osVersion?.split('.')[0] || '0')
          isAndroid12Plus.value = androidVersion >= 12
        }
      } catch (error) {
        console.warn('No se pudo detectar versión de Android:', error)
      }

      // Cargar instrucciones del fabricante
      manufacturerInstructions.value = androidPermissions.getManufacturerSpecificInstructions()
    })

    onUnmounted(() => {
      window.removeEventListener('show-permissions-guide', handlePermissionsGuide)
    })

    return {
      showDialog,
      permissions,
      manufacturerInstructions,
      isAndroid12Plus,
      openSettings
    }
  }
}
</script>

<style scoped>
.rounded-borders {
  border-radius: 8px;
}
</style>
