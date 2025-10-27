<template>
  <transition name="level-up" appear>
    <div v-if="show" class="level-up-overlay" @click="handleClose">
      <div class="level-up-container" @click.stop>
        <!-- Confetti effect -->
        <div class="confetti-container">
          <div
            v-for="i in 50"
            :key="i"
            class="confetti"
            :style="{
              '--delay': Math.random() * 3 + 's',
              '--rotation': Math.random() * 360 + 'deg',
              '--x': Math.random() * 100 + '%',
              '--color': confettiColors[Math.floor(Math.random() * confettiColors.length)]
            }"
          ></div>
        </div>

        <!-- Level up content -->
        <div class="level-up-content">
          <!-- Level up badge -->
          <div class="level-badge">
            <div class="level-badge-inner">
              <q-icon name="stars" size="3rem" class="level-icon" />
              <div class="level-number">{{ newLevel }}</div>
            </div>
            <div class="level-glow"></div>
          </div>

          <!-- Level up text -->
          <h2 class="level-title">
            ¡{{ t('levels.levelUp') }}!
          </h2>

          <p class="level-message">
            {{ message ? message : t('levels.congratulationsLevel', { level: newLevel }) }}
          </p>

          <!-- Experience gained -->
          <div v-if="experienceGained" class="experience-info">
            <q-icon name="add" />
            <span>{{ experienceGained }} XP</span>
          </div>

          <!-- Share buttons -->
          <div class="share-section">
            <p class="share-title">{{ t('levels.shareAchievement') }}</p>
            <div class="share-buttons">
              <q-btn
                round
                flat
                @click="handleShareTwitter"
                class="share-btn twitter"
              >
                <q-icon name="fab fa-twitter" size="24px" />
                <q-tooltip>Twitter</q-tooltip>
              </q-btn>

              <q-btn
                round
                flat
                @click="handleShareFacebook"
                class="share-btn facebook"
              >
                <q-icon name="fab fa-facebook" size="24px" />
                <q-tooltip>Facebook</q-tooltip>
              </q-btn>

              <q-btn
                round
                flat
                @click="handleShareLinkedIn"
                class="share-btn linkedin"
              >
                <q-icon name="fab fa-linkedin" size="24px" />
                <q-tooltip>LinkedIn</q-tooltip>
              </q-btn>

              <q-btn
                round
                flat
                icon="link"
                @click="handleCopyLink"
                class="share-btn copy"
              >
                <q-tooltip>{{ t('common.copyLink') }}</q-tooltip>
              </q-btn>
            </div>
          </div>

          <!-- Action buttons -->
          <div class="level-actions">
            <q-btn
              color="primary"
              :label="t('common.continue')"
              @click="handleContinue"
              size="lg"
              class="continue-btn"
            />

            <q-btn
              v-if="hasUnlockedContent"
              flat
              :label="t('levels.seeUnlocked')"
              @click="handleShowUnlocked"
              class="unlock-btn"
            />
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useQuasar } from 'quasar';

export default {
  name: 'LevelUpAnimation',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    newLevel: {
      type: Number,
      required: true
    },
    message: {
      type: String,
      default: null
    },
    experienceGained: {
      type: Number,
      default: null
    },
    hasUnlockedContent: {
      type: Boolean,
      default: false
    },
    autoClose: {
      type: Boolean,
      default: false
    },
    autoCloseDelay: {
      type: Number,
      default: 5000
    }
  },
  emits: ['close', 'continue', 'show-unlocked'],
  setup(props, { emit }) {
    const { t } = useI18n();
    const $q = useQuasar();

    const confettiColors = ref([
      '#ff6b6b',
      '#4ecdc4',
      '#45b7d1',
      '#f9ca24',
      '#f0932b',
      '#eb4d4b',
      '#6c5ce7',
      '#a29bfe',
      '#fd79a8',
      '#fdcb6e'
    ]);

    // Generar el texto para compartir
    const shareText = computed(() => {
      return `¡Acabo de alcanzar el nivel ${props.newLevel} en Tidy! 🎉 ¡Sigue mejorando tu productividad! #Tidy #Productividad #Nivel${props.newLevel}`;
    });

    // URL de la aplicación (ajustar según tu dominio)
    const shareUrl = computed(() => {
      return window.location.origin;
    });

    const handleClose = () => {
      emit('close');
    };

    const handleContinue = () => {
      emit('continue');
      handleClose();
    };

    const handleShowUnlocked = () => {
      emit('show-unlocked');
    };

    // Funciones para compartir en redes sociales
    const handleShareTwitter = () => {
      const url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText.value)}&url=${encodeURIComponent(shareUrl.value)}`;
      window.open(url, '_blank', 'width=600,height=400');
    };

    const handleShareFacebook = () => {
      const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl.value)}&quote=${encodeURIComponent(shareText.value)}`;
      window.open(url, '_blank', 'width=600,height=400');
    };

    const handleShareLinkedIn = () => {
      const url = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareUrl.value)}`;
      window.open(url, '_blank', 'width=600,height=400');
    };

    const handleCopyLink = async () => {
      try {
        await navigator.clipboard.writeText(`${shareText.value} ${shareUrl.value}`);
        $q.notify({
          type: 'positive',
          message: '¡Enlace copiado al portapapeles!',
          position: 'top',
          timeout: 2000
        });
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: 'Error al copiar el enlace',
          position: 'top',
          timeout: 2000
        });
      }
    };

    onMounted(() => {
      if (props.autoClose) {
        setTimeout(() => {
          handleClose();
        }, props.autoCloseDelay);
      }
    });

    return {
      t,
      confettiColors,
      handleClose,
      handleContinue,
      handleShowUnlocked,
      handleShareTwitter,
      handleShareFacebook,
      handleShareLinkedIn,
      handleCopyLink
    };
  }
}
</script>

<style scoped>
.level-up-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2147483647; /* Máximo z-index posible para estar delante de todo */
  backdrop-filter: blur(4px);
}

.level-up-container {
  position: relative;
  background: white;
  border-radius: 24px;
  padding: 2rem;
  max-width: 500px;
  width: 90%;
  text-align: center;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;
}

/* Confetti Animation */
.confetti-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  overflow: hidden;
}

.confetti {
  position: absolute;
  width: 10px;
  height: 10px;
  background: var(--color);
  top: -10px;
  left: var(--x);
  animation: confetti-fall 3s linear infinite;
  animation-delay: var(--delay);
  transform: rotate(var(--rotation));
}

.confetti:nth-child(odd) {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.confetti:nth-child(3n) {
  width: 6px;
  height: 12px;
  border-radius: 2px;
}

@keyframes confetti-fall {
  0% {
    transform: translateY(-10px) rotate(var(--rotation));
    opacity: 1;
  }
  100% {
    transform: translateY(calc(100vh + 10px)) rotate(calc(var(--rotation) + 720deg));
    opacity: 0;
  }
}

/* Level Badge */
.level-badge {
  position: relative;
  margin: 0 auto 2rem;
  width: 120px;
  height: 120px;
}

.level-badge-inner {
  position: relative;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #14887D 0%, #176F46 100%);
  border-radius: 50%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
  animation: badge-pulse 2s ease-in-out infinite;
}

.level-icon {
  animation: icon-sparkle 2s ease-in-out infinite;
}

.level-number {
  font-size: 2rem;
  font-weight: bold;
  margin-top: 0.5rem;
  animation: number-bounce 0.6s ease-out;
}

.level-glow {
  position: absolute;
  top: -10px;
  left: -10px;
  right: -10px;
  bottom: -10px;
  background: linear-gradient(135deg, #14887D 0%, #176F46 100%);
  border-radius: 50%;
  opacity: 0.3;
  animation: glow-pulse 2s ease-in-out infinite;
  z-index: -1;
}

@keyframes badge-pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

@keyframes icon-sparkle {
  0%, 100% { transform: rotate(0deg) scale(1); }
  25% { transform: rotate(-5deg) scale(1.1); }
  75% { transform: rotate(5deg) scale(1.1); }
}

@keyframes number-bounce {
  0% { transform: scale(0) rotate(180deg); }
  50% { transform: scale(1.2) rotate(0deg); }
  100% { transform: scale(1) rotate(0deg); }
}

@keyframes glow-pulse {
  0%, 100% { transform: scale(1); opacity: 0.3; }
  50% { transform: scale(1.2); opacity: 0.1; }
}

/* Content */
.level-content {
  position: relative;
  z-index: 1;
}

.level-title {
  font-size: 2.5rem;
  font-weight: bold;
  margin: 0 0 1rem 0;
  background: linear-gradient(135deg, #14887D 0%, #176F46 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  animation: title-slide-up 0.8s ease-out;
}

.level-message {
  font-size: 1.2rem;
  color: #64748b;
  margin-bottom: 2rem;
  animation: message-fade-in 1s ease-out 0.3s both;
}

.experience-info {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 1.1rem;
  font-weight: 600;
  color: #10b981;
  margin-bottom: 2rem;
  animation: exp-slide-up 0.8s ease-out 0.5s both;
}

.level-actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  animation: actions-fade-in 0.8s ease-out 0.7s both;
}

.continue-btn {
  border-radius: 12px;
  padding: 0.8rem 2rem;
  font-weight: 600;
  font-size: 1.1rem;
  background: linear-gradient(135deg, #14887D 0%, #176F46 100%);
  transition: all 0.3s ease;
}

.continue-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

.unlock-btn {
  color: #14887D;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.unlock-btn:hover {
  background-color: #f1f5f9;
  transform: translateY(-1px);
}

/* Share Section */
.share-section {
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, rgba(20, 136, 125, 0.1) 0%, rgba(23, 111, 70, 0.1) 100%);
  border-radius: 12px;
  animation: share-fade-in 0.8s ease-out 0.9s both;
}

.share-title {
  font-size: 1rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 1rem;
  text-align: center;
}

.share-buttons {
  display: flex;
  justify-content: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.share-btn {
  width: 50px;
  height: 50px;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.share-btn.twitter {
  color: #1DA1F2;
  border-color: #1DA1F2;
}

.share-btn.twitter:hover {
  background-color: #1DA1F2;
  color: white;
  transform: scale(1.1) rotate(5deg);
}

.share-btn.facebook {
  color: #1877F2;
  border-color: #1877F2;
}

.share-btn.facebook:hover {
  background-color: #1877F2;
  color: white;
  transform: scale(1.1) rotate(-5deg);
}

.share-btn.linkedin {
  color: #0A66C2;
  border-color: #0A66C2;
}

.share-btn.linkedin:hover {
  background-color: #0A66C2;
  color: white;
  transform: scale(1.1) rotate(5deg);
}

.share-btn.copy {
  color: #10b981;
  border-color: #10b981;
}

.share-btn.copy:hover {
  background-color: #10b981;
  color: white;
  transform: scale(1.1) rotate(-5deg);
}

@keyframes share-fade-in {
  0% {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Animations */
@keyframes title-slide-up {
  0% {
    opacity: 0;
    transform: translateY(30px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes message-fade-in {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes exp-slide-up {
  0% {
    opacity: 0;
    transform: translateY(20px) scale(0.9);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes actions-fade-in {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Transition */
.level-up-enter-active {
  transition: all 0.6s ease-out;
}

.level-up-leave-active {
  transition: all 0.4s ease-in;
}

.level-up-enter-from {
  opacity: 0;
  transform: scale(0.8);
}

.level-up-leave-to {
  opacity: 0;
  transform: scale(1.1);
}

.level-up-enter-from .level-up-container,
.level-up-leave-to .level-up-container {
  transform: scale(0.8) translateY(50px);
}

/* Dark mode support */
.body--dark .level-up-container {
  background: #1e293b;
  color: white;
}

.body--dark .level-message {
  color: #94a3b8;
}

.body--dark .unlock-btn:hover {
  background-color: #334155;
}

.body--dark .share-title {
  color: #cbd5e1;
}

.body--dark .share-section {
  background: linear-gradient(135deg, rgba(20, 136, 125, 0.15) 0%, rgba(23, 111, 70, 0.15) 100%);
}

/* Mobile responsive */
@media (max-width: 768px) {
  .level-up-container {
    padding: 1.5rem;
    margin: 1rem;
  }

  .level-title {
    font-size: 2rem;
  }

  .level-message {
    font-size: 1rem;
  }

  .level-badge {
    width: 100px;
    height: 100px;
  }

  .level-number {
    font-size: 1.5rem;
  }

  .level-icon {
    font-size: 2.5rem;
  }

  .share-btn {
    width: 45px;
    height: 45px;
  }

  .share-title {
    font-size: 0.9rem;
  }
}

@media (max-width: 480px) {
  .level-up-container {
    padding: 1rem;
    margin: 0.5rem;
  }

  .level-title {
    font-size: 1.8rem;
  }

  .level-badge {
    width: 80px;
    height: 80px;
    margin-bottom: 1.5rem;
  }

  .level-number {
    font-size: 1.2rem;
  }

  .level-icon {
    font-size: 2rem;
  }

  .share-section {
    padding: 1rem;
  }

  .share-btn {
    width: 40px;
    height: 40px;
  }

  .share-buttons {
    gap: 0.5rem;
  }
}
</style>