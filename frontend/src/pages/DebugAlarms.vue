<template>
  <q-page class="q-pa-md">
    <!-- BANNER SUPER VISIBLE -->
    <div class="debug-banner">
      ⚠️ PÁGINA DE DEBUG - ESTÁS EN /debug ⚠️
    </div>

    <div class="text-h5 q-mb-md">🐛 Debug & Pruebas de Alarmas</div>

    <!-- Información del Sistema -->
    <q-card class="q-mb-md">
      <q-card-section class="bg-primary text-white">
        <div class="text-h6">📱 Información del Sistema</div>
      </q-card-section>
      <q-card-section>
        <div class="text-body1 q-mb-xs">
          <strong>Platform:</strong> {{ platform }}
        </div>
        <div class="text-body1 q-mb-xs">
          <strong>Capacitor:</strong> {{ capacitorAvailable ? '✅ Disponible' : '❌ No disponible' }}
        </div>
        <div class="text-body1 q-mb-xs">
          <strong>Android:</strong> {{ isAndroid ? '✅ Sí' : '❌ No' }}
        </div>
      </q-card-section>
    </q-card>

    <!-- Plugins Disponibles -->
    <q-card class="q-mb-md">
      <q-card-section class="bg-secondary text-white">
        <div class="text-h6">🔌 Plugins Registrados</div>
      </q-card-section>
      <q-card-section>
        <div v-for="plugin in availablePlugins" :key="plugin" class="text-body1 q-mb-xs">
          • {{ plugin }} ✅
        </div>
        <div v-if="availablePlugins.length === 0" class="text-body1 text-grey">
          No se detectaron plugins personalizados
        </div>
      </q-card-section>
    </q-card>

    <!-- Botones de Prueba -->
    <q-card class="q-mb-md">
      <q-card-section class="bg-orange text-white">
        <div class="text-h6">🧪 Pruebas</div>
      </q-card-section>
      <q-card-section class="q-gutter-md">
        <q-btn
          color="green"
          label="Probar Alarma AHORA (testAlarmNow)"
          @click="testAlarmNow"
          :loading="testingAlarm"
          icon="alarm"
          size="lg"
          class="full-width"
        />
        <q-btn
          color="primary"
          label="Probar Alarma en 10 segundos"
          @click="testAlarmIn10Seconds"
          :loading="testingAlarm"
          icon="schedule"
          size="lg"
          class="full-width"
        />
        <q-btn
          color="secondary"
          label="Verificar Plugins"
          @click="checkPlugins"
          icon="refresh"
          size="lg"
          class="full-width"
        />
        <q-btn
          color="purple"
          label="Verificar Permisos"
          @click="checkPermissions"
          icon="verified"
          size="lg"
          class="full-width"
        />
        <q-btn
          color="deep-orange"
          label="Desactivar Optimización de Batería"
          @click="requestBatteryExemption"
          icon="battery_alert"
          size="lg"
          class="full-width"
        />
      </q-card-section>
    </q-card>

    <!-- Historial de Ejecuciones de Alarmas -->
    <q-card class="q-mb-md">
      <q-card-section class="bg-teal text-white">
        <div class="row items-center">
          <div class="text-h6 col">📜 Historial de Ejecuciones</div>
          <q-btn flat dense icon="refresh" @click="loadAlarmLogs" label="Actualizar" color="white" class="q-mr-sm" />
          <q-btn flat dense icon="clear_all" @click="clearAlarmHistory" label="Limpiar" color="white" />
        </div>
      </q-card-section>
      <q-card-section class="q-pt-none" style="max-height: 400px; overflow-y: auto;">
        <div v-if="alarmHistory.length > 0">
          <div
            v-for="(alarm, index) in alarmHistory"
            :key="index"
            class="q-mb-sm q-pa-md rounded-borders bg-teal-1"
          >
            <div class="row items-center q-mb-xs">
              <q-icon name="alarm" size="sm" class="q-mr-sm" color="teal" />
              <span class="text-bold">{{ alarm.title }}</span>
              <q-space />
              <q-chip size="sm" color="teal" text-color="white">ID: {{ alarm.alarmId }}</q-chip>
            </div>
            <div class="text-caption text-grey-7 q-mb-xs">{{ alarm.message }}</div>
            <div class="text-caption">
              <strong>Ejecutada:</strong> {{ alarm.executionDate }}
            </div>
            <div class="text-caption">
              <strong>Programada para:</strong> {{ alarm.triggerDate }}
            </div>
          </div>
        </div>
        <div v-else class="text-grey text-center q-pa-lg">
          <q-icon name="history" size="48px" class="q-mb-sm" />
          <div>No hay ejecuciones registradas</div>
          <div class="text-caption">Las alarmas que se ejecuten aparecerán aquí</div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Logs -->
    <q-card>
      <q-card-section class="bg-grey-8 text-white">
        <div class="row items-center">
          <div class="text-h6 col">📋 Logs de Pruebas</div>
          <q-btn flat dense icon="clear" @click="logs = []" label="Limpiar" color="white" />
        </div>
      </q-card-section>
      <q-card-section class="q-pt-none" style="max-height: 400px; overflow-y: auto;">
        <div
          v-for="(log, index) in logs"
          :key="index"
          class="q-mb-sm q-pa-md rounded-borders"
          :class="{
            'bg-red-2': log.type === 'error',
            'bg-green-2': log.type === 'success',
            'bg-orange-2': log.type === 'warning',
            'bg-blue-2': log.type === 'info'
          }"
        >
          <div class="text-caption text-bold">{{ log.time }}</div>
          <div class="text-body1" style="white-space: pre-wrap; word-break: break-all;">
            {{ log.message }}
          </div>
        </div>
        <div v-if="logs.length === 0" class="text-grey text-center q-pa-lg">
          No hay logs todavía. Presiona algún botón para probar.
        </div>
      </q-card-section>
    </q-card>

    <q-btn
      flat
      color="primary"
      label="Volver al Home"
      icon="arrow_back"
      @click="$router.push('/')"
      class="q-mt-md full-width"
      size="lg"
    />
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Capacitor } from '@capacitor/core'
import { useQuasar } from 'quasar'

const $q = useQuasar()

const platform = ref('')
const capacitorAvailable = ref(false)
const isAndroid = ref(false)
const availablePlugins = ref([])
const logs = ref([])
const testingAlarm = ref(false)
const alarmHistory = ref([])

const addLog = (message, type = 'info') => {
  const time = new Date().toLocaleTimeString()
  logs.value.push({ time, message, type })
  console.log(`[${time}] ${message}`)
}

onMounted(() => {
  platform.value = Capacitor.getPlatform()
  capacitorAvailable.value = Capacitor.isNativePlatform()
  isAndroid.value = platform.value === 'android'

  addLog(`Platform: ${platform.value}`)
  addLog(`Capacitor Native: ${capacitorAvailable.value}`)
  addLog(`Android: ${isAndroid.value}`)

  checkPlugins()
  loadAlarmLogs()
})

const checkPlugins = async () => {
  addLog('🔍 Verificando plugins disponibles...')

  try {
    if (window.Capacitor && window.Capacitor.Plugins) {
      const plugins = Object.keys(window.Capacitor.Plugins)
      availablePlugins.value = plugins
      addLog(`✅ Plugins encontrados (${plugins.length}): ${plugins.join(', ')}`, 'success')

      // Verificar específicamente AlarmPlugin
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

const testAlarmNow = async () => {
  testingAlarm.value = true
  addLog('🧪 Intentando llamar a AlarmPlugin.testAlarmNow()...')

  try {
    if (!window.Capacitor || !window.Capacitor.Plugins || !window.Capacitor.Plugins.AlarmPlugin) {
      addLog('❌ AlarmPlugin no está disponible en Capacitor.Plugins', 'error')
      $q.notify({
        type: 'negative',
        message: 'AlarmPlugin no está disponible',
        position: 'top'
      })
      return
    }

    const AlarmPlugin = window.Capacitor.Plugins.AlarmPlugin
    addLog('✅ AlarmPlugin encontrado, llamando a testAlarmNow()...')

    const result = await AlarmPlugin.testAlarmNow()
    addLog(`✅ Resultado: ${JSON.stringify(result)}`, 'success')

    $q.notify({
      type: 'positive',
      message: '¡Alarma de prueba programada! Debería sonar en unos segundos.',
      position: 'top'
    })
  } catch (error) {
    addLog(`❌ ERROR: ${error.message}`, 'error')
    if (error.stack) {
      addLog(`Stack: ${error.stack}`, 'error')
    }
    $q.notify({
      type: 'negative',
      message: `Error: ${error.message}`,
      position: 'top'
    })
  } finally {
    testingAlarm.value = false
  }
}

const testAlarmIn10Seconds = async () => {
  testingAlarm.value = true
  addLog('🧪 Programando alarma para 10 segundos...')

  try {
    if (!window.Capacitor || !window.Capacitor.Plugins || !window.Capacitor.Plugins.AlarmPlugin) {
      addLog('❌ AlarmPlugin no está disponible', 'error')
      return
    }

    const AlarmPlugin = window.Capacitor.Plugins.AlarmPlugin
    const testTime = new Date()
    testTime.setSeconds(testTime.getSeconds() + 10)

    addLog(`⏰ Programando para: ${testTime.toLocaleTimeString()}`)

    const result = await AlarmPlugin.scheduleAlarm({
      id: 99999,
      triggerTime: testTime.getTime(),
      title: 'Prueba de Alarma',
      message: '¡La alarma funciona! 🎉'
    })

    if (result.success) {
      addLog(`✅ Alarma programada exitosamente para ${result.scheduledFor}`, 'success')
      $q.notify({
        type: 'positive',
        message: 'Alarma programada para 10 segundos',
        position: 'top'
      })
    }
  } catch (error) {
    addLog(`❌ ERROR: ${error.message}`, 'error')
  } finally {
    testingAlarm.value = false
  }
}

const checkPermissions = async () => {
  addLog('🔍 Verificando permisos...')

  try {
    if (!window.Capacitor || !window.Capacitor.Plugins) {
      addLog('❌ Capacitor no disponible', 'error')
      return
    }

    const AlarmPlugin = window.Capacitor.Plugins.AlarmPlugin
    const PermissionsPlugin = window.Capacitor.Plugins.PermissionsPlugin

    if (!AlarmPlugin || !PermissionsPlugin) {
      addLog('❌ Plugins no disponibles', 'error')
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

    if (canSchedule.canSchedule && notifPermission.granted) {
      $q.notify({
        type: 'positive',
        message: '✅ Todos los permisos están otorgados',
        position: 'top'
      })
    } else {
      $q.notify({
        type: 'warning',
        message: '⚠️ Faltan algunos permisos',
        position: 'top'
      })
    }
  } catch (error) {
    addLog(`❌ Error verificando permisos: ${error.message}`, 'error')
  }
}

const requestBatteryExemption = async () => {
  addLog('🔋 Solicitando exención de optimización de batería...')

  try {
    if (!window.Capacitor || !window.Capacitor.Plugins || !window.Capacitor.Plugins.PermissionsPlugin) {
      addLog('❌ PermissionsPlugin no disponible', 'error')
      return
    }

    const PermissionsPlugin = window.Capacitor.Plugins.PermissionsPlugin

    // Verificar estado actual
    const batteryStatus = await PermissionsPlugin.checkBatteryOptimization()
    addLog(`📊 Estado actual: ${batteryStatus.isIgnoring ? '✅ Exento' : '❌ No exento'}`,
      batteryStatus.isIgnoring ? 'success' : 'warning')

    if (batteryStatus.isIgnoring) {
      $q.notify({
        type: 'positive',
        message: '✅ La app ya está exenta de optimización de batería',
        position: 'top'
      })
      return
    }

    // Solicitar exención
    addLog('⚙️ Abriendo configuración de batería...')
    const result = await PermissionsPlugin.requestBatteryOptimizationExemption()

    if (result.success) {
      addLog('✅ Configuración abierta. Por favor, permite que Tidy no se optimice', 'success')
      $q.notify({
        type: 'info',
        message: 'Por favor, selecciona "No optimizar" en la configuración',
        position: 'top',
        timeout: 5000
      })
    }

  } catch (error) {
    addLog(`❌ Error: ${error.message}`, 'error')
    $q.notify({
      type: 'negative',
      message: `Error: ${error.message}`,
      position: 'top'
    })
  }
}

const loadAlarmLogs = async () => {
  addLog('📜 Cargando historial de alarmas...')

  try {
    if (!window.Capacitor || !window.Capacitor.Plugins || !window.Capacitor.Plugins.AlarmPlugin) {
      addLog('❌ AlarmPlugin no disponible', 'error')
      return
    }

    const AlarmPlugin = window.Capacitor.Plugins.AlarmPlugin
    const result = await AlarmPlugin.getAlarmLogs()

    if (result.success) {
      const logs = JSON.parse(result.logs)
      alarmHistory.value = logs
      addLog(`✅ Historial cargado: ${result.count} ejecuciones`, 'success')

      $q.notify({
        type: 'positive',
        message: `Historial cargado: ${result.count} alarmas`,
        position: 'top'
      })
    }
  } catch (error) {
    addLog(`❌ Error al cargar historial: ${error.message}`, 'error')
    $q.notify({
      type: 'negative',
      message: `Error: ${error.message}`,
      position: 'top'
    })
  }
}

const clearAlarmHistory = async () => {
  addLog('🗑️ Limpiando historial de alarmas...')

  try {
    if (!window.Capacitor || !window.Capacitor.Plugins || !window.Capacitor.Plugins.AlarmPlugin) {
      addLog('❌ AlarmPlugin no disponible', 'error')
      return
    }

    const AlarmPlugin = window.Capacitor.Plugins.AlarmPlugin
    const result = await AlarmPlugin.clearAlarmLogs()

    if (result.success) {
      alarmHistory.value = []
      addLog('✅ Historial limpiado', 'success')

      $q.notify({
        type: 'positive',
        message: 'Historial de alarmas limpiado',
        position: 'top'
      })
    }
  } catch (error) {
    addLog(`❌ Error al limpiar historial: ${error.message}`, 'error')
    $q.notify({
      type: 'negative',
      message: `Error: ${error.message}`,
      position: 'top'
    })
  }
}
</script>

<style scoped>
.debug-banner {
  background: linear-gradient(45deg, #ff0000, #ff6600, #ff0000);
  color: white;
  padding: 20px;
  text-align: center;
  font-weight: bold;
  font-size: 20px;
  border-radius: 8px;
  margin-bottom: 16px;
  animation: pulse 2s infinite;
  box-shadow: 0 4px 12px rgba(255, 0, 0, 0.5);
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.03);
  }
}

.rounded-borders {
  border-radius: 8px;
}
</style>
