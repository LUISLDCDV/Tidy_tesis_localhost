<template>
  <!-- Level Up Notification -->
  <teleport to="body">
    <transition name="level-up-modal" appear>
      <div
        v-if="showNotification"
        class="level-up-overlay"
        @click="closeNotification"
      >
        <div class="level-up-modal" @click.stop>
          <!-- Particles Background -->
          <div class="particles-container">
            <div
              v-for="i in 20"
              :key="'particle-' + i"
              class="particle"
              :style="getParticleStyle(i)"
            />
          </div>

          <!-- Main Content -->
          <div class="level-up-content">
            <!-- Trophy/Stars Animation -->
            <div class="level-icon-container">
              <q-icon
                name="emoji_events"
                size="80px"
                color="orange"
                class="level-trophy"
              />
              <div class="stars-animation">
                <q-icon
                  v-for="star in 5"
                  :key="'star-' + star"
                  name="star"
                  size="20px"
                  color="yellow"
                  class="star"
                  :style="getStarStyle(star)"
                />
              </div>
            </div>

            <!-- Level Up Text -->
            <div class="level-up-text">
              <h1 class="congratulations">{{ $t('level.congratulations') }}!</h1>
              <h2 class="level-number">{{ $t('level.level') }} {{ notification.newLevel }}</h2>
              <p class="level-message">{{ notification.message }}</p>

              <!-- User Rank Display -->
              <div v-if="userRank" class="rank-display">
                <q-chip
                  :color="getRankColor(userRank.level)"
                  text-color="white"
                  size="lg"
                  class="rank-chip"
                >
                  <q-icon name="stars" />
                  {{ userRank.name }}
                </q-chip>
              </div>
            </div>

            <!-- Unlocked Notes (if any) -->
            <div v-if="unlockedNotes && unlockedNotes.length > 0" class="unlocked-notes">
              <h3 class="unlocked-title">{{ $t('level.newNotesUnlocked') }}</h3>
              <div class="notes-grid">
                <div
                  v-for="note in unlockedNotes"
                  :key="note.id"
                  class="unlocked-note"
                >
                  <q-icon :name="note.icon || 'note'" size="24px" />
                  <span>{{ note.name }}</span>
                </div>
              </div>
            </div>

            <!-- Close Button -->
            <q-btn
              flat
              round
              icon="close"
              size="lg"
              color="white"
              class="close-btn"
              @click="closeNotification"
            />
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script>
import { computed, onMounted, onUnmounted } from 'vue'
import { useLevelsStore } from '@/stores/levels'

export default {
  name: 'LevelUpNotification',
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
    const levelsStore = useLevelsStore()

    const showNotification = computed(() => props.show && props.notification)
    const userRank = computed(() => levelsStore.getUserRank)
    const unlockedNotes = computed(() => props.notification?.unlockedNotes || [])

    const closeNotification = () => {
      emit('close')
    }

    const getParticleStyle = (index) => {
      const angle = (360 / 20) * index
      const radius = 200 + Math.random() * 100
      return {
        '--angle': `${angle}deg`,
        '--radius': `${radius}px`,
        '--delay': `${Math.random() * 3}s`
      }
    }

    const getStarStyle = (index) => {
      const angles = [-60, -30, 0, 30, 60]
      return {
        '--star-angle': `${angles[index - 1]}deg`,
        '--star-delay': `${0.5 + (index * 0.2)}s`
      }
    }

    const getRankColor = (level) => {
      const colors = {
        1: 'brown',
        2: 'grey',
        3: 'orange',
        4: 'purple',
        5: 'gold'
      }
      return colors[level] || 'primary'
    }

    // Auto close after 8 seconds
    let autoCloseTimer = null

    onMounted(() => {
      if (showNotification.value) {
        autoCloseTimer = setTimeout(() => {
          closeNotification()
        }, 8000)
      }
    })

    onUnmounted(() => {
      if (autoCloseTimer) {
        clearTimeout(autoCloseTimer)
      }
    })

    return {
      showNotification,
      userRank,
      unlockedNotes,
      closeNotification,
      getParticleStyle,
      getStarStyle,
      getRankColor
    }
  }
}
</script>

<style scoped>
.level-up-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  backdrop-filter: blur(5px);
}

.level-up-modal {
  position: relative;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 20px;
  padding: 40px;
  max-width: 500px;
  width: 90vw;
  text-align: center;
  color: white;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  overflow: hidden;
}

.particles-container {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
}

.particle {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 6px;
  height: 6px;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 50%;
  animation: particle-float 3s ease-out infinite;
  animation-delay: var(--delay);
}

@keyframes particle-float {
  0% {
    transform: translate(-50%, -50%) rotate(var(--angle)) translateY(0);
    opacity: 1;
    scale: 0;
  }
  50% {
    opacity: 0.8;
    scale: 1;
  }
  100% {
    transform: translate(-50%, -50%) rotate(var(--angle)) translateY(calc(-1 * var(--radius)));
    opacity: 0;
    scale: 0.5;
  }
}

.level-up-content {
  position: relative;
  z-index: 1;
}

.level-icon-container {
  position: relative;
  margin-bottom: 30px;
}

.level-trophy {
  animation: trophy-bounce 0.8s ease-out;
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
}

@keyframes trophy-bounce {
  0% {
    transform: scale(0) rotate(-180deg);
    opacity: 0;
  }
  50% {
    transform: scale(1.2) rotate(0deg);
  }
  100% {
    transform: scale(1) rotate(0deg);
    opacity: 1;
  }
}

.stars-animation {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.star {
  position: absolute;
  animation: star-sparkle 2s ease-in-out infinite;
  animation-delay: var(--star-delay);
  transform-origin: center;
}

@keyframes star-sparkle {
  0%, 100% {
    transform: rotate(var(--star-angle)) translateX(60px) scale(0);
    opacity: 0;
  }
  50% {
    transform: rotate(var(--star-angle)) translateX(60px) scale(1);
    opacity: 1;
  }
}

.level-up-text {
  margin-bottom: 20px;
}

.congratulations {
  font-size: 2.5rem;
  font-weight: bold;
  margin: 0 0 10px 0;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  animation: text-glow 1s ease-out;
}

@keyframes text-glow {
  0% {
    transform: scale(0.8);
    opacity: 0;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.level-number {
  font-size: 3rem;
  font-weight: bold;
  margin: 10px 0;
  background: linear-gradient(45deg, #ffd700, #ffed4e);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  animation: level-pulse 1.5s ease-in-out infinite alternate;
}

@keyframes level-pulse {
  0% {
    transform: scale(1);
  }
  100% {
    transform: scale(1.05);
  }
}

.level-message {
  font-size: 1.1rem;
  opacity: 0.9;
  margin: 15px 0;
}

.rank-display {
  margin: 20px 0;
}

.rank-chip {
  animation: rank-slide 0.8s ease-out 1s both;
  font-weight: bold;
}

@keyframes rank-slide {
  0% {
    transform: translateY(20px);
    opacity: 0;
  }
  100% {
    transform: translateY(0);
    opacity: 1;
  }
}

.unlocked-notes {
  margin-top: 20px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  backdrop-filter: blur(10px);
}

.unlocked-title {
  font-size: 1.2rem;
  margin-bottom: 15px;
  color: #ffd700;
}

.notes-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: center;
}

.unlocked-note {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.2);
  padding: 8px 12px;
  border-radius: 20px;
  font-size: 0.9rem;
  animation: note-unlock 0.5s ease-out;
  animation-fill-mode: both;
}

.unlocked-note:nth-child(1) { animation-delay: 1.5s; }
.unlocked-note:nth-child(2) { animation-delay: 1.7s; }
.unlocked-note:nth-child(3) { animation-delay: 1.9s; }

@keyframes note-unlock {
  0% {
    transform: scale(0) rotate(180deg);
    opacity: 0;
  }
  100% {
    transform: scale(1) rotate(0deg);
    opacity: 1;
  }
}

.close-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Modal Transitions */
.level-up-modal-enter-active,
.level-up-modal-leave-active {
  transition: all 0.5s ease;
}

.level-up-modal-enter-from,
.level-up-modal-leave-to {
  opacity: 0;
  transform: scale(0.5) rotate(180deg);
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .level-up-modal {
    padding: 30px 20px;
    margin: 20px;
  }

  .congratulations {
    font-size: 2rem;
  }

  .level-number {
    font-size: 2.5rem;
  }

  .level-trophy {
    transform: scale(0.8);
  }

  .notes-grid {
    flex-direction: column;
    align-items: center;
  }

  .unlocked-note {
    min-width: 200px;
    justify-content: center;
  }
}

/* Dark mode support */
.body--dark .level-up-modal {
  background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%);
  color: #e0e0e0;
}

.body--dark .unlocked-notes {
  background: rgba(255, 255, 255, 0.05);
}

.body--dark .unlocked-note {
  background: rgba(255, 255, 255, 0.1);
  color: #e0e0e0;
}

.body--dark .close-btn {
  color: #e0e0e0;
}
</style>