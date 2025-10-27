<template>
  <div class="loading-spinner" :class="spinnerClass">
    <div v-if="type === 'dots'" class="dots-spinner">
      <div class="dot" v-for="i in 3" :key="i"></div>
    </div>

    <div v-else-if="type === 'pulse'" class="pulse-spinner">
      <div class="pulse-circle"></div>
    </div>

    <div v-else-if="type === 'bars'" class="bars-spinner">
      <div class="bar" v-for="i in 5" :key="i"></div>
    </div>

    <div v-else-if="type === 'orbit'" class="orbit-spinner">
      <div class="orbit"></div>
      <div class="orbit"></div>
      <div class="orbit"></div>
    </div>

    <div v-else-if="type === 'wave'" class="wave-spinner">
      <div class="wave-bar" v-for="i in 5" :key="i"></div>
    </div>

    <div v-else-if="type === 'square'" class="square-spinner">
      <div class="square"></div>
    </div>

    <div v-else-if="type === 'circle'" class="circle-spinner">
      <div class="circle"></div>
    </div>

    <div v-else class="ring-spinner">
      <div class="ring"></div>
    </div>

    <p v-if="text" class="loading-text" :style="{ color: textColor }">
      {{ text }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'ring',
    validator: (value) => [
      'ring',
      'dots',
      'pulse',
      'bars',
      'orbit',
      'wave',
      'square',
      'circle'
    ].includes(value)
  },
  size: {
    type: String,
    default: 'medium',
    validator: (value) => ['small', 'medium', 'large'].includes(value)
  },
  color: {
    type: String,
    default: '#176F46'
  },
  textColor: {
    type: String,
    default: '#666'
  },
  text: {
    type: String,
    default: ''
  },
  overlay: {
    type: Boolean,
    default: false
  }
})

const spinnerClass = computed(() => ({
  [`spinner-${props.size}`]: true,
  'with-overlay': props.overlay
}))
</script>

<style scoped>
.loading-spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
}

.loading-spinner.with-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.9);
  z-index: 9999;
  backdrop-filter: blur(2px);
}

.loading-text {
  margin: 0;
  font-size: 14px;
  font-weight: 500;
  text-align: center;
}

/* Tamaños */
.spinner-small {
  --spinner-size: 24px;
  --animation-duration: 1s;
}

.spinner-medium {
  --spinner-size: 40px;
  --animation-duration: 1.2s;
}

.spinner-large {
  --spinner-size: 60px;
  --animation-duration: 1.5s;
}

/* Ring Spinner */
.ring-spinner {
  width: var(--spinner-size);
  height: var(--spinner-size);
}

.ring {
  width: 100%;
  height: 100%;
  border: 3px solid transparent;
  border-top: 3px solid v-bind(color);
  border-radius: 50%;
  animation: ring-spin var(--animation-duration) linear infinite;
}

@keyframes ring-spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Dots Spinner */
.dots-spinner {
  display: flex;
  gap: 4px;
  align-items: center;
}

.dot {
  width: 8px;
  height: 8px;
  background-color: v-bind(color);
  border-radius: 50%;
  animation: dots-bounce var(--animation-duration) infinite ease-in-out;
}

.dot:nth-child(1) { animation-delay: -0.32s; }
.dot:nth-child(2) { animation-delay: -0.16s; }
.dot:nth-child(3) { animation-delay: 0s; }

@keyframes dots-bounce {
  0%, 80%, 100% {
    transform: scale(0.8);
    opacity: 0.5;
  }
  40% {
    transform: scale(1.2);
    opacity: 1;
  }
}

/* Pulse Spinner */
.pulse-spinner {
  width: var(--spinner-size);
  height: var(--spinner-size);
  position: relative;
}

.pulse-circle {
  width: 100%;
  height: 100%;
  background-color: v-bind(color);
  border-radius: 50%;
  animation: pulse-scale var(--animation-duration) infinite ease-in-out;
}

@keyframes pulse-scale {
  0%, 100% {
    transform: scale(0.8);
    opacity: 0.5;
  }
  50% {
    transform: scale(1.2);
    opacity: 1;
  }
}

/* Bars Spinner */
.bars-spinner {
  display: flex;
  align-items: flex-end;
  gap: 2px;
  height: var(--spinner-size);
}

.bar {
  width: 4px;
  height: 100%;
  background-color: v-bind(color);
  animation: bars-bounce var(--animation-duration) infinite ease-in-out;
}

.bar:nth-child(1) { animation-delay: -1.2s; }
.bar:nth-child(2) { animation-delay: -1.1s; }
.bar:nth-child(3) { animation-delay: -1.0s; }
.bar:nth-child(4) { animation-delay: -0.9s; }
.bar:nth-child(5) { animation-delay: -0.8s; }

@keyframes bars-bounce {
  0%, 40%, 100% {
    transform: scaleY(0.4);
  }
  20% {
    transform: scaleY(1);
  }
}

/* Orbit Spinner */
.orbit-spinner {
  width: var(--spinner-size);
  height: var(--spinner-size);
  position: relative;
}

.orbit {
  position: absolute;
  width: 100%;
  height: 100%;
  border: 2px solid transparent;
  border-top: 2px solid v-bind(color);
  border-radius: 50%;
  animation: orbit-spin var(--animation-duration) linear infinite;
}

.orbit:nth-child(1) {
  animation-delay: 0s;
  opacity: 1;
}

.orbit:nth-child(2) {
  animation-delay: -0.4s;
  opacity: 0.7;
  transform: scale(0.8);
}

.orbit:nth-child(3) {
  animation-delay: -0.8s;
  opacity: 0.4;
  transform: scale(0.6);
}

@keyframes orbit-spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Wave Spinner */
.wave-spinner {
  display: flex;
  align-items: center;
  gap: 2px;
  height: var(--spinner-size);
}

.wave-bar {
  width: 4px;
  height: 20%;
  background-color: v-bind(color);
  animation: wave-scale var(--animation-duration) infinite ease-in-out;
}

.wave-bar:nth-child(1) { animation-delay: -0.4s; }
.wave-bar:nth-child(2) { animation-delay: -0.3s; }
.wave-bar:nth-child(3) { animation-delay: -0.2s; }
.wave-bar:nth-child(4) { animation-delay: -0.1s; }
.wave-bar:nth-child(5) { animation-delay: 0s; }

@keyframes wave-scale {
  0%, 40%, 100% {
    transform: scaleY(1);
  }
  20% {
    transform: scaleY(3);
  }
}

/* Square Spinner */
.square-spinner {
  width: var(--spinner-size);
  height: var(--spinner-size);
}

.square {
  width: 100%;
  height: 100%;
  background-color: v-bind(color);
  animation: square-rotate var(--animation-duration) infinite ease-in-out;
}

@keyframes square-rotate {
  0% {
    transform: perspective(120px) rotateX(0deg) rotateY(0deg);
  }
  50% {
    transform: perspective(120px) rotateX(-180.1deg) rotateY(0deg);
  }
  100% {
    transform: perspective(120px) rotateX(-180deg) rotateY(-179.9deg);
  }
}

/* Circle Spinner */
.circle-spinner {
  width: var(--spinner-size);
  height: var(--spinner-size);
}

.circle {
  width: 100%;
  height: 100%;
  background: conic-gradient(from 0deg, transparent, v-bind(color));
  border-radius: 50%;
  animation: circle-spin var(--animation-duration) linear infinite;
  position: relative;
}

.circle::before {
  content: '';
  position: absolute;
  top: 10%;
  left: 10%;
  width: 80%;
  height: 80%;
  background-color: white;
  border-radius: 50%;
}

@keyframes circle-spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .spinner-small {
    --spinner-size: 20px;
  }

  .spinner-medium {
    --spinner-size: 32px;
  }

  .spinner-large {
    --spinner-size: 48px;
  }

  .loading-text {
    font-size: 12px;
  }
}
</style>