<template>
  <div class="micro-interactions">
    <!-- Botón con efecto ripple -->
    <div class="interaction-group">
      <h6>Botones con Efectos</h6>
      <div class="buttons-demo">
        <q-btn
          ref="rippleBtn"
          color="primary"
          label="Ripple Effect"
          @click="handleRippleClick"
          class="ripple-btn"
        />

        <q-btn
          ref="bounceBtn"
          color="secondary"
          label="Bounce Effect"
          @click="handleBounceClick"
          class="bounce-btn"
        />

        <q-btn
          ref="pulseBtn"
          color="positive"
          label="Pulse Effect"
          @click="handlePulseClick"
          class="pulse-btn"
        />
      </div>
    </div>

    <!-- Tarjetas con hover effects -->
    <div class="interaction-group">
      <h6>Tarjetas Interactivas</h6>
      <div class="cards-demo">
        <q-card
          v-for="(card, index) in demoCards"
          :key="index"
          ref="hoverCards"
          class="interactive-card"
          @mouseenter="handleCardHover(index, true)"
          @mouseleave="handleCardHover(index, false)"
          @click="handleCardClick(index)"
        >
          <q-card-section>
            <div class="card-icon">
              <q-icon :name="card.icon" size="md" />
            </div>
            <h6>{{ card.title }}</h6>
            <p>{{ card.description }}</p>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Elementos de entrada animados -->
    <div class="interaction-group">
      <h6>Elementos de Entrada</h6>
      <div class="inputs-demo">
        <q-input
          v-model="demoText"
          ref="animatedInput"
          label="Texto animado"
          class="animated-input"
          @focus="handleInputFocus"
          @blur="handleInputBlur"
        />

        <q-select
          v-model="demoSelect"
          ref="animatedSelect"
          :options="selectOptions"
          label="Selección animada"
          class="animated-select"
          @focus="handleSelectFocus"
        />
      </div>
    </div>

    <!-- Indicadores de progreso animados -->
    <div class="interaction-group">
      <h6>Indicadores de Progreso</h6>
      <div class="progress-demo">
        <q-linear-progress
          ref="progressBar"
          :value="progressValue"
          size="10px"
          color="primary"
          class="animated-progress"
        />

        <div class="progress-controls">
          <q-btn
            @click="animateProgress"
            label="Animar Progreso"
            color="primary"
            size="sm"
          />
          <q-btn
            @click="resetProgress"
            label="Reiniciar"
            color="grey"
            size="sm"
            outline
          />
        </div>
      </div>
    </div>

    <!-- Notificaciones animadas -->
    <div class="interaction-group">
      <h6>Notificaciones</h6>
      <div class="notifications-demo">
        <q-btn
          @click="showSuccessNotification"
          label="Éxito"
          color="positive"
          size="sm"
        />
        <q-btn
          @click="showWarningNotification"
          label="Advertencia"
          color="warning"
          size="sm"
        />
        <q-btn
          @click="showErrorNotification"
          label="Error"
          color="negative"
          size="sm"
        />
      </div>
    </div>

    <!-- Loading states animados -->
    <div class="interaction-group">
      <h6>Estados de Carga</h6>
      <div class="loading-demo">
        <LoadingSpinner
          type="dots"
          size="small"
          color="#176F46"
          text="Cargando..."
        />
        <LoadingSpinner
          type="pulse"
          size="medium"
          color="#9c27b0"
        />
        <LoadingSpinner
          type="bars"
          size="small"
          color="#f44336"
        />
      </div>
    </div>

    <!-- Elementos de confetti -->
    <div class="interaction-group">
      <h6>Efectos Especiales</h6>
      <div class="effects-demo">
        <q-btn
          ref="confettiBtn"
          @click="triggerConfetti"
          label="🎉 Confetti"
          color="amber"
          size="md"
        />

        <q-btn
          ref="shakeBtn"
          @click="triggerShake"
          label="📳 Shake"
          color="orange"
          size="md"
        />

        <q-btn
          ref="floatBtn"
          @click="triggerFloat"
          label="🎈 Float"
          color="purple"
          size="md"
        />
      </div>
    </div>

    <!-- Container para efectos de confetti -->
    <div ref="confettiContainer" class="confetti-container"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useQuasar } from 'quasar'
import { useAnimations } from 'src/composables/useAnimations'
import LoadingSpinner from './LoadingSpinner.vue'

const $q = useQuasar()
const animations = useAnimations()

// Referencias del template
const rippleBtn = ref(null)
const bounceBtn = ref(null)
const pulseBtn = ref(null)
const hoverCards = ref([])
const animatedInput = ref(null)
const animatedSelect = ref(null)
const progressBar = ref(null)
const confettiContainer = ref(null)
const confettiBtn = ref(null)
const shakeBtn = ref(null)
const floatBtn = ref(null)

// Estado reactivo
const demoText = ref('')
const demoSelect = ref(null)
const progressValue = ref(0)

// Datos de demostración
const demoCards = ref([
  {
    title: 'Tarjeta 1',
    description: 'Descripción de la primera tarjeta',
    icon: 'star'
  },
  {
    title: 'Tarjeta 2',
    description: 'Descripción de la segunda tarjeta',
    icon: 'favorite'
  },
  {
    title: 'Tarjeta 3',
    description: 'Descripción de la tercera tarjeta',
    icon: 'thumb_up'
  }
])

const selectOptions = ref([
  'Opción 1',
  'Opción 2',
  'Opción 3',
  'Opción 4'
])

// Manejadores de eventos
const handleRippleClick = () => {
  if (rippleBtn.value) {
    const btnEl = rippleBtn.value.$el
    createRippleEffect(btnEl)
  }
}

const handleBounceClick = () => {
  if (bounceBtn.value) {
    animations.bounceButton(bounceBtn.value.$el)
  }
}

const handlePulseClick = () => {
  if (pulseBtn.value) {
    animations.pulseNotification(pulseBtn.value.$el)
  }
}

const handleCardHover = (index, isHovering) => {
  if (hoverCards.value[index]) {
    animations.hoverCard(hoverCards.value[index].$el, isHovering)
  }
}

const handleCardClick = (index) => {
  if (hoverCards.value[index]) {
    animations.bounceButton(hoverCards.value[index].$el)
  }
}

const handleInputFocus = () => {
  if (animatedInput.value) {
    const inputEl = animatedInput.value.$el
    animations.pulseNotification(inputEl)
  }
}

const handleInputBlur = () => {
  // Animación sutil al perder el foco
}

const handleSelectFocus = () => {
  if (animatedSelect.value) {
    animations.bounceButton(animatedSelect.value.$el)
  }
}

const animateProgress = () => {
  if (progressBar.value) {
    const progressEl = progressBar.value.$el.querySelector('.q-linear-progress__track')
    animations.animateProgress(progressEl, 0, 100, 2)

    // Animar el valor reactivo también
    const interval = setInterval(() => {
      if (progressValue.value < 1) {
        progressValue.value += 0.02
      } else {
        clearInterval(interval)
      }
    }, 40)
  }
}

const resetProgress = () => {
  progressValue.value = 0
}

const showSuccessNotification = () => {
  $q.notify({
    type: 'positive',
    message: '¡Operación exitosa!',
    icon: 'check_circle',
    position: 'top-right',
    timeout: 3000,
    actions: [
      {
        icon: 'close',
        color: 'white',
        round: true,
        dense: true
      }
    ]
  })
}

const showWarningNotification = () => {
  $q.notify({
    type: 'warning',
    message: 'Advertencia importante',
    icon: 'warning',
    position: 'top-right',
    timeout: 3000,
    actions: [
      {
        icon: 'close',
        color: 'white',
        round: true,
        dense: true
      }
    ]
  })
}

const showErrorNotification = () => {
  $q.notify({
    type: 'negative',
    message: 'Error en la operación',
    icon: 'error',
    position: 'top-right',
    timeout: 3000,
    actions: [
      {
        icon: 'close',
        color: 'white',
        round: true,
        dense: true
      }
    ]
  })
}

const triggerConfetti = () => {
  if (confettiContainer.value) {
    animations.createConfetti(confettiContainer.value, 30)
  }
}

const triggerShake = () => {
  if (shakeBtn.value) {
    animations.shakeError(shakeBtn.value.$el)
  }
}

const triggerFloat = () => {
  if (floatBtn.value) {
    animations.floatAnimation(floatBtn.value.$el, 15, 1)
  }
}

// Función auxiliar para efecto ripple
const createRippleEffect = (element) => {
  const rect = element.getBoundingClientRect()
  const ripple = document.createElement('span')
  const size = Math.max(rect.width, rect.height)

  ripple.style.cssText = `
    position: absolute;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.6);
    transform: scale(0);
    animation: ripple-animation 0.6s linear;
    pointer-events: none;
    z-index: 1;
  `

  ripple.style.width = ripple.style.height = size + 'px'
  ripple.style.left = (rect.width / 2 - size / 2) + 'px'
  ripple.style.top = (rect.height / 2 - size / 2) + 'px'

  element.style.position = 'relative'
  element.style.overflow = 'hidden'
  element.appendChild(ripple)

  // Agregar animación CSS si no existe
  if (!document.querySelector('#ripple-animation-style')) {
    const style = document.createElement('style')
    style.id = 'ripple-animation-style'
    style.textContent = `
      @keyframes ripple-animation {
        to {
          transform: scale(4);
          opacity: 0;
        }
      }
    `
    document.head.appendChild(style)
  }

  setTimeout(() => {
    ripple.remove()
  }, 600)
}

onMounted(async () => {
  await nextTick()

  // Animar elementos al cargar
  if (hoverCards.value.length > 0) {
    const cardElements = hoverCards.value.map(ref => ref.$el)
    animations.staggerListItems(cardElements, 0.15, 'bottom')
  }
})
</script>

<style scoped>
.micro-interactions {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.interaction-group {
  margin-bottom: 40px;
  padding: 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.interaction-group h6 {
  margin: 0 0 20px 0;
  color: #176F46;
  font-weight: 600;
}

.buttons-demo {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.cards-demo {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.interactive-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 12px;
}

.interactive-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.card-icon {
  text-align: center;
  color: #176F46;
  margin-bottom: 16px;
}

.card-icon .q-icon {
  font-size: 48px;
}

.interactive-card h6 {
  margin: 0 0 8px 0;
  text-align: center;
  color: #333;
}

.interactive-card p {
  margin: 0;
  text-align: center;
  color: #666;
  font-size: 14px;
}

.inputs-demo {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.animated-input,
.animated-select {
  transition: all 0.3s ease;
}

.progress-demo {
  space-y: 16px;
}

.animated-progress {
  margin-bottom: 16px;
  border-radius: 5px;
  overflow: hidden;
}

.progress-controls {
  display: flex;
  gap: 12px;
}

.notifications-demo {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.loading-demo {
  display: flex;
  align-items: center;
  gap: 40px;
  justify-content: center;
  padding: 20px;
  background: #f5f5f5;
  border-radius: 8px;
}

.effects-demo {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  justify-content: center;
}

.confetti-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1000;
}

/* Estilos específicos para animaciones */
.ripple-btn {
  position: relative;
  overflow: hidden;
}

.bounce-btn {
  transition: transform 0.3s ease;
}

.pulse-btn {
  transition: all 0.3s ease;
}

/* Responsive */
@media (max-width: 768px) {
  .micro-interactions {
    padding: 16px;
  }

  .interaction-group {
    padding: 16px;
    margin-bottom: 24px;
  }

  .buttons-demo,
  .notifications-demo,
  .effects-demo {
    justify-content: center;
  }

  .cards-demo {
    grid-template-columns: 1fr;
  }

  .loading-demo {
    flex-direction: column;
    gap: 20px;
  }

  .progress-controls {
    justify-content: center;
  }
}
</style>