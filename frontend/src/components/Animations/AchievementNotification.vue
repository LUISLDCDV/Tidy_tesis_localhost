<template>
  <!-- Achievement Notification -->
  <teleport to="body">
    <transition name="achievement-slide" appear>
      <div
        v-if="showNotification"
        class="achievement-notification"
        @click="closeNotification"
      >
        <!-- Achievement Card -->
        <div class="achievement-card" @click.stop>
          <!-- Sparkle Effects -->
          <div class="sparkles-container">
            <div
              v-for="i in 12"
              :key="'sparkle-' + i"
              class="sparkle"
              :style="getSparkleStyle(i)"
            />
          </div>

          <!-- Achievement Icon -->
          <div class="achievement-icon-container">
            <div class="icon-glow">
              <q-icon
                :name="achievement?.icon || 'emoji_events'"
                size="50px"
                :color="getAchievementColor(achievement?.category)"
                class="achievement-icon"
              />
            </div>
          </div>

          <!-- Achievement Content -->
          <div class="achievement-content">
            <div class="achievement-header">
              <span class="achievement-badge">{{ $t('achievement.unlocked') }}</span>
            </div>

            <h3 class="achievement-title">{{ achievement?.name }}</h3>
            <p class="achievement-description">{{ achievement?.description }}</p>

            <!-- Achievement Category -->
            <q-chip
              v-if="achievement?.category"
              :color="getCategoryColor(achievement.category)"
              text-color="white"
              size="sm"
              class="achievement-category"
            >
              {{ $t(`achievement.categories.${achievement.category}`) }}
            </q-chip>
          </div>

          <!-- Close Button -->
          <q-btn
            flat
            round
            icon="close"
            size="sm"
            color="grey-7"
            class="close-btn"
            @click="closeNotification"
          />
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script>
import { computed, onMounted, onUnmounted } from 'vue'

export default {
  name: 'AchievementNotification',
  props: {
    notification: {
      type: Object,
      default: null
    },
    show: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close'],
  setup(props, { emit }) {
    const showNotification = computed(() => props.show && props.notification)
    const achievement = computed(() => props.notification?.achievement)

    const closeNotification = () => {
      emit('close')
    }

    const getSparkleStyle = (index) => {
      const angle = (360 / 12) * index
      const radius = 80 + Math.random() * 40
      const duration = 2 + Math.random() * 2
      return {
        '--angle': `${angle}deg`,
        '--radius': `${radius}px`,
        '--duration': `${duration}s`,
        '--delay': `${Math.random() * 2}s`
      }
    }

    const getAchievementColor = (category) => {
      const colors = {
        'productivity': 'blue',
        'creativity': 'purple',
        'consistency': 'green',
        'milestone': 'orange',
        'special': 'pink'
      }
      return colors[category] || 'primary'
    }

    const getCategoryColor = (category) => {
      const colors = {
        'productivity': 'blue',
        'creativity': 'purple',
        'consistency': 'green',
        'milestone': 'orange',
        'special': 'pink'
      }
      return colors[category] || 'grey-6'
    }

    // Auto close after 6 seconds
    let autoCloseTimer = null

    onMounted(() => {
      if (showNotification.value) {
        autoCloseTimer = setTimeout(() => {
          closeNotification()
        }, 6000)
      }
    })

    onUnmounted(() => {
      if (autoCloseTimer) {
        clearTimeout(autoCloseTimer)
      }
    })

    return {
      showNotification,
      achievement,
      closeNotification,
      getSparkleStyle,
      getAchievementColor,
      getCategoryColor
    }
  }
}
</script>

<style scoped>
.achievement-notification {
  position: fixed;
  top: 80px;
  right: 20px;
  z-index: 9998;
  pointer-events: all;
}

.achievement-card {
  position: relative;
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  border-radius: 16px;
  padding: 20px;
  min-width: 320px;
  max-width: 400px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  border: 2px solid #ffd700;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
}

.achievement-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
}

.sparkles-container {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
}

.sparkle {
  position: absolute;
  top: 50%;
  left: 15%;
  width: 4px;
  height: 4px;
  background: #ffd700;
  border-radius: 50%;
  animation: sparkle-float var(--duration) ease-in-out infinite;
  animation-delay: var(--delay);
}

@keyframes sparkle-float {
  0% {
    transform: translate(-50%, -50%) rotate(var(--angle)) translateY(0) scale(0);
    opacity: 0;
  }
  25% {
    opacity: 1;
    scale: 1;
  }
  100% {
    transform: translate(-50%, -50%) rotate(var(--angle)) translateY(calc(-1 * var(--radius))) scale(0);
    opacity: 0;
  }
}

.achievement-icon-container {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

.icon-glow {
  position: relative;
  padding: 10px;
  border-radius: 50%;
  background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
  animation: icon-pulse 2s ease-in-out infinite alternate;
}

@keyframes icon-pulse {
  0% {
    box-shadow: 0 0 20px rgba(255, 215, 0, 0.4);
  }
  100% {
    box-shadow: 0 0 30px rgba(255, 215, 0, 0.8);
  }
}

.achievement-icon {
  animation: icon-bounce 0.6s ease-out;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

@keyframes icon-bounce {
  0% {
    transform: scale(0) rotate(-90deg);
  }
  50% {
    transform: scale(1.1) rotate(0deg);
  }
  100% {
    transform: scale(1) rotate(0deg);
  }
}

.achievement-content {
  flex: 1;
}

.achievement-header {
  margin-bottom: 8px;
}

.achievement-badge {
  background: linear-gradient(45deg, #ffd700, #ffed4e);
  color: #333;
  font-size: 0.75rem;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 4px 8px;
  border-radius: 12px;
  animation: badge-glow 0.8s ease-out;
}

@keyframes badge-glow {
  0% {
    transform: translateX(-20px);
    opacity: 0;
  }
  100% {
    transform: translateX(0);
    opacity: 1;
  }
}

.achievement-title {
  font-size: 1.2rem;
  font-weight: bold;
  color: #333;
  margin: 8px 0 6px 0;
  line-height: 1.3;
  animation: text-slide 0.8s ease-out 0.2s both;
}

.achievement-description {
  font-size: 0.9rem;
  color: #666;
  margin: 6px 0 12px 0;
  line-height: 1.4;
  animation: text-slide 0.8s ease-out 0.4s both;
}

@keyframes text-slide {
  0% {
    transform: translateY(10px);
    opacity: 0;
  }
  100% {
    transform: translateY(0);
    opacity: 1;
  }
}

.achievement-category {
  animation: chip-pop 0.6s ease-out 0.6s both;
}

@keyframes chip-pop {
  0% {
    transform: scale(0);
    opacity: 0;
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(0, 0, 0, 0.05);
  transition: background-color 0.2s ease;
}

.close-btn:hover {
  background: rgba(0, 0, 0, 0.1);
}

/* Slide Transitions */
.achievement-slide-enter-active,
.achievement-slide-leave-active {
  transition: all 0.4s ease;
}

.achievement-slide-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.achievement-slide-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .achievement-notification {
    top: 70px;
    right: 10px;
    left: 10px;
  }

  .achievement-card {
    min-width: auto;
    max-width: none;
    padding: 16px;
  }

  .achievement-title {
    font-size: 1.1rem;
  }

  .achievement-description {
    font-size: 0.85rem;
  }

  .icon-glow {
    padding: 8px;
  }

  .achievement-icon {
    font-size: 40px;
  }
}

/* Dark mode support */
.body--dark .achievement-card {
  background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%);
  border-color: #ffd700;
  color: #e0e0e0;
}

.body--dark .achievement-title {
  color: #e0e0e0;
}

.body--dark .achievement-description {
  color: #b0b0b0;
}

.body--dark .close-btn {
  background: rgba(255, 255, 255, 0.1);
  color: #e0e0e0;
}

.body--dark .close-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

/* Landscape mobile adjustments */
@media (max-width: 896px) and (orientation: landscape) {
  .achievement-notification {
    top: 10px;
  }

  .achievement-card {
    padding: 12px;
  }
}
</style>