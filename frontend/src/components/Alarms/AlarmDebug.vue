<template>
  <div class="alarm-debug-wrapper">
    <!-- Banner super visible SIEMPRE -->
    <div class="debug-banner">
      ⚠️ COMPONENTE DE PRUEBAS - SI VES ESTO, FUNCIONA ⚠️
    </div>

    <q-card class="q-mb-md debug-card">
      <q-card-section class="bg-orange-2">
        <div class="row items-center">
          <q-icon name="bug_report" size="24px" class="q-mr-sm" />
          <div class="text-h6">🔧 Pruebas de Alarmas</div>
          <q-space />
          <q-btn
            flat
            dense
            :icon="expanded ? 'expand_less' : 'expand_more'"
            @click="expanded = !expanded"
          />
        </div>
      </q-card-section>

    <q-slide-transition>
      <div v-show="expanded">
        <!-- Información del Sistema -->
        <q-card-section class="q-pt-none">
          <div class="text-subtitle2 q-mb-sm">📱 Información del Sistema:</div>
          <div class="text-caption q-mb-xs">Platform: {{ platform }}</div>
          <div class="text-caption q-mb-xs">
            Capacitor: {{ capacitorAvailable ? '✅ Disponible' : '❌ No disponible' }}
          </div>
          <div class="text-caption q-mb-xs">
            Android: {{ isAndroid ? '✅ Sí' : '❌ No' }}
          </div>
          <div class="text-caption q-mb-xs">
            Usando alarmas nativas: {{ usingNativeAlarms ? '✅ Sí' : '❌ No (fallback)' }}
          </div>
        </q-card-section>

        <!-- Plugins Disponibles -->
        <q-card-section class="q-pt-none">
          <div class="text-subtitle2 q-mb-sm">🔌 Plugins Registrados:</div>
          <div v-for="plugin in availablePlugins" :key="plugin" class="text-caption">
            • {{ plugin }} ✅
          </div>
          <div v-if="availablePlugins.length === 0" class="text-caption text-grey">
            No se detectaron plugins personalizados
          </div>
        </q-card-section>

        <!-- Botones de Prueba -->
        <q-card-section class="q-pt-none">
          <div class="text-subtitle2 q-mb-sm">🧪 Pruebas:</div>

          <!-- Advertencia si no está en Android -->
          <q-banner v-if="!isAndroid" class="bg-warning text-white q-mb-sm" dense rounded>
            <template v-slot:avatar>
              <q-icon name="warning" />
            </template>
            Las pruebas de alarmas solo funcionan en Android. Estás en: {{ platform }}
          </q-banner>

          <div class="column q-gutter-sm">
            <q-btn
              color="primary"
              icon="alarm"
              label="Probar Alarma en 10 segundos"
              @click="testAlarmIn10Seconds"
              :loading="testingAlarm"
              :disable="!isAndroid"
              dense
              class="full-width"
            />
            <q-btn
              color="secondary"
              icon="verified"
              label="Verificar Permisos"
              @click="checkPermissions"
              :loading="checkingPermissions"
              :disable="!isAndroid"
              dense
              class="full-width"
            />
            <q-btn
              color="green"
              icon="info"
              label="Info del Dispositivo"
              @click="getDeviceInfo"
              :disable="!isAndroid"
              dense
              class="full-width"
            />
            <q-btn
              color="orange"
              icon="settings"
              label="Abrir Configuración de Alarmas"
              @click="openAlarmSettings"
              :disable="!isAndroid"
              dense
              class="full-width"
            />
            <q-btn
              color="purple"
              icon="refresh"
              label="Verificar Plugins"
              @click="checkPlugins"
              dense
              class="full-width"
            />
          </div>
        </q-card-section>

        <!-- Logs -->
        <q-card-section class="q-pt-none">
          <div class="row items-center q-mb-sm">
            <div class="text-subtitle2">📋 Logs:</div>
            <q-space />
            <q-btn
              flat
              dense
              icon="clear"
              @click="logs = []"
              label="Limpiar"
              size="sm"
            />
          </div>
          <q-scroll-area
            style="height: 200px"
            class="bg-grey-2 rounded-borders q-pa-sm"
          >
            <div
              v-for="(log, index) in logs"
              :key="index"
              class="q-mb-xs q-pa-xs rounded-borders"
              :class="{
                'bg-red-1': log.type === 'error',
                'bg-green-1': log.type === 'success',
                'bg-blue-1': log.type === 'info',
                'bg-orange-1': log.type === 'warning'
              }"
            >
              <div class="text-caption text-bold">{{ log.time }}</div>
              <div class="text-caption" style="white-space: pre-wrap; word-break: break-all;">
                {{ log.message }}
              </div>
            </div>
            <div v-if="logs.length === 0" class="text-grey text-center text-caption q-pa-md">
              No hay logs todavía. Presiona algún botón para probar.
            </div>
          </q-scroll-area>
        </q-card-section>
      </div>
    </q-slide-transition>
  </q-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Capacitor } from '@capacitor/core'
import AlarmPlugin from '@/plugins/AlarmPlugin'
import PermissionsPlugin from '@/plugins/PermissionsPlugin'
import alarmService from '@/services/alarmService'
import { useQuasar } from 'quasar'

const $q = useQuasar()

const expanded = ref(true) // Expandido por defecto para que sea visible
const platform = ref('')
const capacitorAvailable = ref(false)
const isAndroid = ref(false)
const usingNativeAlarms = ref(false)
const availablePlugins = ref([])
const logs = ref([])
const testingAlarm = ref(false)
const checkingPermissions = ref(false)

const addLog = (message, type = 'info') => {
  const time = new Date().toLocaleTimeString()
  logs.value.push({ time, message, type })
  console.log(`[${time}] ${message}`)

  // Scroll al final
  setTimeout(() => {
    const scrollArea = document.querySelector('.q-scrollarea')
    if (scrollArea) {
      scrollArea.scrollTop = scrollArea.scrollHeight
    }
  }, 100)
}

onMounted(() => {
  platform.value = Capacitor.getPlatform()
  capacitorAvailable.value = Capacitor.isNativePlatform()
  isAndroid.value = platform.value === 'android'
  usingNativeAlarms.value = alarmService.useNativeAlarms || false

  addLog(`Platform: ${platform.value}`)
  addLog(`Capacitor Native: ${capacitorAvailable.value}`)
  addLog(`Android: ${isAndroid.value}`)
  addLog(`Alarmas nativas: ${usingNativeAlarms.value}`)

  checkPlugins()
})

const checkPlugins = async () => {
  addLog('🔍 Verificando plugins disponibles...')

  try {
    if (window.Capacitor && window.Capacitor.Plugins) {
      const plugins = Object.keys(window.Capacitor.Plugins)
      availablePlugins.value = plugins
      addLog(`✅ Plugins encontrados (${plugins.length}): ${plugins.join(', ')}`, 'success')

      // Verificar específicamente AlarmPlugin y PermissionsPlugin
      if (plugins.includes('AlarmPlugin')) {
        addLog('✅ AlarmPlugin está registrado', 'success')
      } else {
        addLog('❌ AlarmPlugin NO está registrado', 'error')
      }

      if (plugins.includes('PermissionsPlugin')) {
        addLog('✅ PermissionsPlugin está registrado', 'success')
      } else {
        addLog('❌ PermissionsPlugin NO está registrado', 'error')
      }
    } else {
      addLog('❌ window.Capacitor.Plugins no está disponible', 'error')
    }
  } catch (error) {
    addLog(`❌ Error verificando plugins: ${error.message}`, 'error')
  }
}

const testAlarmIn10Seconds = async () => {
  testingAlarm.value = true
  addLog('🧪 Iniciando prueba de alarma en 10 segundos...')

  try {
    if (!isAndroid.value) {
      addLog('⚠️ Esta prueba solo funciona en Android', 'warning')
      $q.notify({
        type: 'warning',
        message: 'Esta prueba solo funciona en Android',
        position: 'top'
      })
      return
    }

    // Verificar que el plugin esté disponible
    if (!window.Capacitor.Plugins.AlarmPlugin) {
      addLog('❌ AlarmPlugin no está disponible', 'error')
      $q.notify({
        type: 'negative',
        message: 'AlarmPlugin no está disponible. Verifica la compilación.',
        position: 'top'
      })
      return
    }

    // Crear alarma de prueba para 10 segundos en el futuro
    const testTime = new Date()
    testTime.setSeconds(testTime.getSeconds() + 10)

    addLog(`⏰ Programando alarma para: ${testTime.toLocaleTimeString()}`)

    const result = await AlarmPlugin.scheduleAlarm({
      id: 99999, // ID de prueba
      triggerTime: testTime.getTime(),
      title: 'Prueba de Alarma',
      message: '¡La alarma nativa funciona! 🎉'
    })

    if (result.success) {
      addLog(`✅ Alarma programada exitosamente!`, 'success')
      addLog(`📅 Hora programada: ${result.scheduledFor}`, 'success')
      $q.notify({
        type: 'positive',
        message: 'Alarma programada para 10 segundos. ¡Espera!',
        position: 'top',
        timeout: 3000
      })
    } else {
      addLog('❌ No se pudo programar la alarma', 'error')
      $q.notify({
        type: 'negative',
        message: 'Error al programar la alarma',
        position: 'top'
      })
    }
  } catch (error) {
    addLog(`❌ ERROR: ${error.message}`, 'error')
    if (error.stack) {
      addLog(`Stack: ${error.stack}`, 'error')
    }
    $q.notify({
      type: 'negative',
      message: `Error: ${error.message}`,
      position: 'top',
      timeout: 5000
    })
  } finally {
    testingAlarm.value = false
  }
}

const checkPermissions = async () => {
  checkingPermissions.value = true
  addLog('🔍 Verificando permisos...')

  try {
    if (!isAndroid.value) {
      addLog('⚠️ Verificación de permisos solo en Android', 'warning')
      return
    }

    // Verificar alarmas exactas
    const canSchedule = await AlarmPlugin.canScheduleExactAlarms()
    addLog(`📋 Puede programar alarmas exactas: ${canSchedule.canSchedule ? '✅ Sí' : '❌ No'}`,
      canSchedule.canSchedule ? 'success' : 'error')

    // Verificar notificaciones
    const notifPermission = await PermissionsPlugin.checkNotificationPermission()
    addLog(`📋 Permisos de notificaciones: ${notifPermission.granted ? '✅ Sí' : '❌ No'}`,
      notifPermission.granted ? 'success' : 'error')

    // Verificar optimización de batería
    const batteryStatus = await PermissionsPlugin.checkBatteryOptimization()
    addLog(`📋 Exento de optimización de batería: ${batteryStatus.isIgnoring ? '✅ Sí' : '❌ No'}`,
      batteryStatus.isIgnoring ? 'success' : 'warning')

    if (!batteryStatus.isIgnoring) {
      addLog('⚠️ Se recomienda desactivar la optimización de batería', 'warning')
    }

    // Resumen
    if (canSchedule.canSchedule && notifPermission.granted) {
      $q.notify({
        type: 'positive',
        message: '✅ Todos los permisos críticos están otorgados',
        position: 'top'
      })
    } else {
      $q.notify({
        type: 'warning',
        message: '⚠️ Faltan algunos permisos. Revisa los logs.',
        position: 'top'
      })
    }
  } catch (error) {
    addLog(`❌ Error verificando permisos: ${error.message}`, 'error')
  } finally {
    checkingPermissions.value = false
  }
}

const getDeviceInfo = async () => {
  addLog('📱 Obteniendo información del dispositivo...')

  try {
    if (!isAndroid.value) {
      addLog('⚠️ Info de dispositivo solo en Android', 'warning')
      return
    }

    const deviceInfo = await PermissionsPlugin.getDeviceInfo()

    addLog(`📱 Fabricante: ${deviceInfo.manufacturer}`, 'info')
    addLog(`📱 Modelo: ${deviceInfo.model}`, 'info')
    addLog(`📱 Android: ${deviceInfo.androidVersion} (SDK ${deviceInfo.sdkVersion})`, 'info')
    addLog(`📱 Ahorro agresivo: ${deviceInfo.hasAggressiveBatterySaving ? '⚠️ Sí' : '✅ No'}`,
      deviceInfo.hasAggressiveBatterySaving ? 'warning' : 'success')

    if (deviceInfo.warning) {
      addLog(`⚠️ ${deviceInfo.warning}`, 'warning')
    }

    $q.notify({
      type: 'info',
      message: `${deviceInfo.manufacturer} ${deviceInfo.model} - Android ${deviceInfo.androidVersion}`,
      position: 'top'
    })
  } catch (error) {
    addLog(`❌ Error obteniendo info: ${error.message}`, 'error')
  }
}

const openAlarmSettings = async () => {
  addLog('⚙️ Abriendo configuración de alarmas...')

  try {
    if (!isAndroid.value) {
      addLog('⚠️ Solo disponible en Android', 'warning')
      return
    }

    await AlarmPlugin.openExactAlarmSettings()
    addLog('✅ Configuración abierta. Activa "Alarmas y recordatorios"', 'success')

    $q.notify({
      type: 'info',
      message: 'Activa "Alarmas y recordatorios" en la configuración',
      position: 'top',
      timeout: 5000
    })
  } catch (error) {
    addLog(`❌ Error abriendo configuración: ${error.message}`, 'error')
  }
}
</script>

<style scoped>
.alarm-debug-wrapper {
  width: 100%;
  margin-bottom: 16px;
}

.debug-banner {
  background: linear-gradient(45deg, #ff0000, #ff6600, #ff0000);
  color: white;
  padding: 16px;
  text-align: center;
  font-weight: bold;
  font-size: 18px;
  border-radius: 8px;
  margin-bottom: 8px;
  animation: pulse 2s infinite;
  box-shadow: 0 4px 8px rgba(255, 0, 0, 0.3);
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.02);
  }
}

.debug-card {
  border: 3px solid #ff6600 !important;
  box-shadow: 0 4px 12px rgba(255, 102, 0, 0.4) !important;
}

.rounded-borders {
  border-radius: 8px;
}
</style>
