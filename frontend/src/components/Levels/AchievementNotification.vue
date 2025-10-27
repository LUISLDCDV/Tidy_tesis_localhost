<template>
  <q-card class="achievement-notification">
    <q-card-section class="text-center q-pa-xl">
      <!-- Icono de logro desbloqueado -->
      <div class="achievement-header q-mb-lg">
        <div class="achievement-unlock-icon q-mb-md">🏆</div>
        <h2 class="text-h4 text-weight-bold text-amber q-mb-sm">
          ¡LOGRO DESBLOQUEADO!
        </h2>
      </div>

      <!-- Información del logro -->
      <div class="achievement-info q-mb-lg">
        <q-avatar 
          size="80px" 
          :color="achievement?.color || 'amber'" 
          class="achievement-avatar q-mb-md"
        >
          <q-icon 
            :name="achievement?.icon || 'star'" 
            size="48px" 
            color="white" 
          />
        </q-avatar>
        
        <h3 class="text-h5 text-weight-medium q-mb-xs">
          {{ achievement?.name || 'Logro Especial' }}
        </h3>
        
        <p class="text-body1 text-grey-7 q-mb-md">
          {{ achievement?.description || 'Has completado un logro especial' }}
        </p>

        <!-- Categoría del logro -->
        <q-chip 
          :color="getCategoryColor(achievement?.category)" 
          text-color="white" 
          :icon="getCategoryIcon(achievement?.category)"
          class="q-mb-md"
        >
          {{ getCategoryName(achievement?.category) }}
        </q-chip>
      </div>

      <!-- Recompensa -->
      <div class="achievement-reward q-mb-lg">
        <div class="reward-container">
          <q-icon name="stars" size="32px" color="amber" class="q-mb-sm" />
          <div class="text-h4 text-weight-bold text-positive">
            +{{ achievement?.experience_reward || 0 }} XP
          </div>
          <div class="text-caption text-grey-6">Experiencia ganada</div>
        </div>
      </div>

      <!-- Progreso del logro (si aplicable) -->
      <div v-if="achievement?.progress_info" class="achievement-progress q-mb-lg">
        <div class="progress-stats">
          <div class="text-weight-medium q-mb-xs">Progreso completado</div>
          <div class="row items-center justify-center q-gutter-sm">
            <span class="text-h6 text-weight-bold">{{ achievement.progress_info.current }}</span>
            <span class="text-grey-5">/</span>
            <span class="text-h6 text-weight-bold">{{ achievement.progress_info.total }}</span>
          </div>
          <q-linear-progress
            value="1"
            color="positive"
            size="8px"
            rounded
            class="q-mt-sm achievement-progress-bar"
          />
        </div>
      </div>

      <!-- Rareza del logro -->
      <div v-if="achievement?.rarity" class="achievement-rarity q-mb-lg">
        <div class="rarity-indicator">
          <q-icon :name="getRarityIcon(achievement.rarity)" :color="getRarityColor(achievement.rarity)" size="24px" />
          <span class="text-weight-medium q-ml-sm" :class="`text-${getRarityColor(achievement.rarity)}`">
            {{ getRarityText(achievement.rarity) }}
          </span>
        </div>
        <div class="text-caption text-grey-6">
          Solo el {{ getRarityPercentage(achievement.rarity) }}% de usuarios lo han conseguido
        </div>
      </div>

      <!-- Mensaje de felicitación -->
      <div class="congratulations-message">
        <q-icon name="format_quote" size="20px" color="grey-5" />
        <p class="text-body2 text-italic q-ma-sm">
          {{ getCongratulationsMessage() }}
        </p>
      </div>
    </q-card-section>

    <q-separator />

    <q-card-actions align="center" class="q-pa-lg">
      <q-btn
        label="¡Genial!"
        color="positive"
        size="lg"
        @click="$emit('close')"
        unelevated
        rounded
        class="celebration-btn"
      />
      
      <q-btn
        v-if="achievement?.shareable"
        label="Compartir"
        color="primary"
        size="lg"
        @click="shareAchievement"
        outline
        rounded
        class="q-ml-sm"
      />
    </q-card-actions>
  </q-card>
</template>

<script>
export default {
  name: 'AchievementNotification',
  props: {
    achievement: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ['close'],

  mounted() {
    // Animación de entrada
    this.$nextTick(() => {
      const notification = this.$el.querySelector('.achievement-notification');
      if (notification) {
        notification.classList.add('show');
      }
    });

    // Reproducir sonido de logro
    this.playAchievementSound();
  },

  methods: {
    getCategoryColor(category) {
      const colors = {
        productivity: 'green',
        social: 'blue',
        creativity: 'purple',
        consistency: 'orange',
        milestone: 'red',
        special: 'amber'
      };
      return colors[category] || 'grey';
    },

    getCategoryIcon(category) {
      const icons = {
        productivity: 'trending_up',
        social: 'people',
        creativity: 'palette',
        consistency: 'schedule',
        milestone: 'flag',
        special: 'star'
      };
      return icons[category] || 'category';
    },

    getCategoryName(category) {
      const names = {
        productivity: 'Productividad',
        social: 'Social',
        creativity: 'Creatividad',
        consistency: 'Constancia',
        milestone: 'Hito',
        special: 'Especial'
      };
      return names[category] || 'General';
    },

    getRarityIcon(rarity) {
      const icons = {
        common: 'circle',
        uncommon: 'hexagon',
        rare: 'star',
        epic: 'stars',
        legendary: 'diamond'
      };
      return icons[rarity] || 'circle';
    },

    getRarityColor(rarity) {
      const colors = {
        common: 'grey',
        uncommon: 'green',
        rare: 'blue',
        epic: 'purple',
        legendary: 'amber'
      };
      return colors[rarity] || 'grey';
    },

    getRarityText(rarity) {
      const texts = {
        common: 'Común',
        uncommon: 'Poco común',
        rare: 'Raro',
        epic: 'Épico',
        legendary: 'Legendario'
      };
      return texts[rarity] || 'Desconocido';
    },

    getRarityPercentage(rarity) {
      const percentages = {
        common: '50',
        uncommon: '25',
        rare: '10',
        epic: '3',
        legendary: '1'
      };
      return percentages[rarity] || '0';
    },

    getCongratulationsMessage() {
      const messages = [
        '¡Tu dedicación ha sido recompensada!',
        '¡Excelente trabajo completando este desafío!',
        '¡Sigues demostrando tu compromiso!',
        '¡Este logro refleja tu perseverancia!',
        '¡Cada logro te acerca más a la maestría!'
      ];
      return messages[Math.floor(Math.random() * messages.length)];
    },

    shareAchievement() {
      if (navigator.share) {
        navigator.share({
          title: `¡Logro desbloqueado en Tidy!`,
          text: `Acabo de conseguir el logro "${this.achievement?.name}" en Tidy. ¡+${this.achievement?.experience_reward || 0} XP!`,
          url: window.location.href
        }).catch(console.error);
      } else {
        // Fallback para navegadores que no soportan Web Share API
        this.copyAchievementText();
      }
    },

    copyAchievementText() {
      const text = `¡Logro desbloqueado en Tidy! "${this.achievement?.name}" - +${this.achievement?.experience_reward || 0} XP`;
      
      if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
          this.$q.notify({
            type: 'positive',
            message: 'Texto del logro copiado al portapapeles',
            position: 'top'
          });
        });
      }
    },

    playAchievementSound() {
      try {
        // Crear un sonido de logro
        if ('AudioContext' in window || 'webkitAudioContext' in window) {
          const audioContext = new (window.AudioContext || window.webkitAudioContext)();
          const oscillator = audioContext.createOscillator();
          const gainNode = audioContext.createGain();
          
          oscillator.connect(gainNode);
          gainNode.connect(audioContext.destination);
          
          // Secuencia musical para logro
          oscillator.frequency.setValueAtTime(659.25, audioContext.currentTime); // Mi
          oscillator.frequency.setValueAtTime(783.99, audioContext.currentTime + 0.1); // Sol
          oscillator.frequency.setValueAtTime(987.77, audioContext.currentTime + 0.2); // Si
          oscillator.frequency.setValueAtTime(1318.51, audioContext.currentTime + 0.3); // Mi alta
          
          gainNode.gain.setValueAtTime(0.2, audioContext.currentTime);
          gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
          
          oscillator.start();
          oscillator.stop(audioContext.currentTime + 0.5);
        }
      } catch (error) {
        console.log('No se pudo reproducir el sonido del logro:', error);
      }
    }
  }
}
</script>

<style scoped>
.achievement-notification {
  min-width: 400px;
  max-width: 500px;
  border-radius: 16px;
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
  overflow: hidden;
  transform: scale(0.8) rotateY(180deg);
  opacity: 0;
  transition: all 0.4s ease;
}

.achievement-notification.show {
  transform: scale(1) rotateY(0deg);
  opacity: 1;
}

.achievement-unlock-icon {
  font-size: 4rem;
  animation: bounce 1.5s ease-in-out infinite;
}

.achievement-avatar {
  border: 3px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
  animation: glow 2s ease-in-out infinite alternate;
}

.reward-container {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 16px;
  border: 2px solid rgba(255, 255, 255, 0.2);
}

.achievement-progress {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 16px;
}

.achievement-progress-bar {
  animation: fillProgress 1s ease-out;
}

.achievement-rarity {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 8px;
  padding: 12px;
}

.rarity-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 4px;
}

.celebration-btn {
  animation: pulse 2s infinite;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Animaciones */
@keyframes bounce {
  0%, 20%, 53%, 80%, 100% { transform: translateY(0); }
  40%, 43% { transform: translateY(-20px); }
  70% { transform: translateY(-10px); }
  90% { transform: translateY(-5px); }
}

@keyframes glow {
  0% { box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3); }
  100% { box-shadow: 0 4px 24px rgba(255, 255, 255, 0.5), 0 0 32px rgba(255, 193, 7, 0.4); }
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

@keyframes fillProgress {
  0% { transform: scaleX(0); }
  100% { transform: scaleX(1); }
}

/* Efectos de partículas */
.achievement-header::before {
  content: '⭐';
  position: absolute;
  top: 15%;
  left: 15%;
  font-size: 1.5rem;
  animation: sparkle 2s ease-in-out infinite;
}

.achievement-header::after {
  content: '✨';
  position: absolute;
  top: 10%;
  right: 15%;
  font-size: 1.5rem;
  animation: sparkle 2s ease-in-out infinite reverse;
}

@keyframes sparkle {
  0%, 100% { 
    transform: scale(1) rotate(0deg);
    opacity: 1;
  }
  50% { 
    transform: scale(1.2) rotate(180deg);
    opacity: 0.8;
  }
}

/* Mobile responsive */
@media (max-width: 600px) {
  .achievement-notification {
    min-width: 300px;
    max-width: 90vw;
  }
  
  .achievement-unlock-icon {
    font-size: 3rem;
  }
  
  .achievement-header h2 {
    font-size: 1.8rem;
  }
  
  .achievement-avatar {
    width: 60px !important;
    height: 60px !important;
  }
  
  .achievement-avatar :deep(.q-icon) {
    font-size: 32px;
  }
}

/* Dark mode support */
.body--dark .achievement-notification {
  background: linear-gradient(135deg, #92400e 0%, #78350f 100%);
}

.body--dark .reward-container,
.body--dark .achievement-progress {
  background: rgba(0, 0, 0, 0.3);
}

.body--dark .achievement-rarity {
  background: rgba(0, 0, 0, 0.4);
}
</style>