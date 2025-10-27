<template>
  <transition
    :name="transitionName"
    :mode="mode"
    :appear="appear"
    @enter="onEnter"
    @leave="onLeave"
    @before-enter="onBeforeEnter"
    @after-enter="onAfterEnter"
    @before-leave="onBeforeLeave"
    @after-leave="onAfterLeave"
  >
    <slot />
  </transition>
</template>

<script setup>
import { computed, inject } from 'vue'
import { useAnimations } from 'src/composables/useAnimations'

const props = defineProps({
  type: {
    type: String,
    default: 'fade',
    validator: (value) => [
      'fade',
      'slide-left',
      'slide-right',
      'slide-up',
      'slide-down',
      'scale',
      'bounce',
      'flip',
      'zoom',
      'rotate'
    ].includes(value)
  },
  duration: {
    type: Number,
    default: 300
  },
  delay: {
    type: Number,
    default: 0
  },
  mode: {
    type: String,
    default: undefined,
    validator: (value) => !value || ['in-out', 'out-in'].includes(value)
  },
  appear: {
    type: Boolean,
    default: false
  },
  easing: {
    type: String,
    default: 'ease'
  },
  group: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'enter',
  'leave',
  'before-enter',
  'after-enter',
  'before-leave',
  'after-leave'
])

const animations = useAnimations()

const transitionName = computed(() => {
  return props.group ? `${props.type}-group` : props.type
})

const onBeforeEnter = (el) => {
  emit('before-enter', el)
}

const onEnter = (el, done) => {
  emit('enter', el)

  // Aplicar animación personalizada según el tipo
  switch (props.type) {
    case 'fade':
      animations.fadeIn(el, props.duration / 1000, props.delay / 1000)
      break
    case 'slide-left':
      animations.slideInLeft(el, props.duration / 1000, props.delay / 1000)
      break
    case 'slide-right':
      animations.slideInRight(el, props.duration / 1000, props.delay / 1000)
      break
    case 'scale':
      animations.scaleInModal(el, props.duration / 1000)
      break
    case 'bounce':
      animations.animateListItemEntry(el, 0, props.delay / 1000)
      break
    default:
      setTimeout(done, props.duration)
      return
  }

  setTimeout(done, props.duration + props.delay)
}

const onAfterEnter = (el) => {
  emit('after-enter', el)
}

const onBeforeLeave = (el) => {
  emit('before-leave', el)
}

const onLeave = (el, done) => {
  emit('leave', el)

  // Aplicar animación de salida según el tipo
  switch (props.type) {
    case 'fade':
      animations.fadeOut(el, props.duration / 1000, done)
      break
    case 'slide-left':
      animations.slideOutLeft(el, props.duration / 1000, done)
      break
    case 'slide-right':
      animations.slideOutRight(el, props.duration / 1000, done)
      break
    case 'scale':
      animations.scaleOutModal(el, props.duration / 1000, done)
      break
    case 'bounce':
      animations.animateItemExit(el, done)
      break
    default:
      setTimeout(done, props.duration)
  }
}

const onAfterLeave = (el) => {
  emit('after-leave', el)
}
</script>

<style scoped>
/* Transiciones CSS como fallback */
.fade-enter-active,
.fade-leave-active {
  transition: opacity v-bind(duration + 'ms') v-bind(easing);
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-left-enter-active,
.slide-left-leave-active {
  transition: all v-bind(duration + 'ms') v-bind(easing);
}

.slide-left-enter-from {
  transform: translateX(-100%);
  opacity: 0;
}

.slide-left-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}

.slide-right-enter-active,
.slide-right-leave-active {
  transition: all v-bind(duration + 'ms') v-bind(easing);
}

.slide-right-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.slide-right-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: all v-bind(duration + 'ms') v-bind(easing);
}

.slide-up-enter-from {
  transform: translateY(100%);
  opacity: 0;
}

.slide-up-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}

.slide-down-enter-active,
.slide-down-leave-active {
  transition: all v-bind(duration + 'ms') v-bind(easing);
}

.slide-down-enter-from {
  transform: translateY(-100%);
  opacity: 0;
}

.slide-down-leave-to {
  transform: translateY(100%);
  opacity: 0;
}

.scale-enter-active,
.scale-leave-active {
  transition: all v-bind(duration + 'ms') v-bind(easing);
}

.scale-enter-from,
.scale-leave-to {
  transform: scale(0.8);
  opacity: 0;
}

.bounce-enter-active {
  animation: bounce-in v-bind(duration + 'ms') v-bind(easing);
}

.bounce-leave-active {
  animation: bounce-out v-bind(duration + 'ms') v-bind(easing);
}

@keyframes bounce-in {
  0% {
    transform: scale(0.3);
    opacity: 0;
  }
  50% {
    transform: scale(1.05);
  }
  70% {
    transform: scale(0.9);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes bounce-out {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  100% {
    transform: scale(0.3);
    opacity: 0;
  }
}

.flip-enter-active,
.flip-leave-active {
  transition: all v-bind(duration + 'ms') v-bind(easing);
}

.flip-enter-from {
  transform: rotateY(-90deg);
  opacity: 0;
}

.flip-leave-to {
  transform: rotateY(90deg);
  opacity: 0;
}

.zoom-enter-active,
.zoom-leave-active {
  transition: all v-bind(duration + 'ms') v-bind(easing);
}

.zoom-enter-from,
.zoom-leave-to {
  transform: scale(0);
  opacity: 0;
}

.rotate-enter-active,
.rotate-leave-active {
  transition: all v-bind(duration + 'ms') v-bind(easing);
}

.rotate-enter-from {
  transform: rotate(-180deg) scale(0.8);
  opacity: 0;
}

.rotate-leave-to {
  transform: rotate(180deg) scale(0.8);
  opacity: 0;
}

/* Transiciones de grupo */
.fade-group-enter-active,
.fade-group-leave-active {
  transition: all v-bind(duration + 'ms') v-bind(easing);
}

.fade-group-enter-from,
.fade-group-leave-to {
  opacity: 0;
  transform: translateY(30px);
}

.fade-group-leave-active {
  position: absolute;
}

.slide-left-group-enter-active,
.slide-left-group-leave-active {
  transition: all v-bind(duration + 'ms') v-bind(easing);
}

.slide-left-group-enter-from,
.slide-left-group-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}

.slide-left-group-leave-active {
  position: absolute;
}

.slide-right-group-enter-active,
.slide-right-group-leave-active {
  transition: all v-bind(duration + 'ms') v-bind(easing);
}

.slide-right-group-enter-from,
.slide-right-group-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

.slide-right-group-leave-active {
  position: absolute;
}
</style>