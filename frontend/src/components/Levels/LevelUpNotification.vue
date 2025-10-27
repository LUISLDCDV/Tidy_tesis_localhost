<template>
  <q-card class="level-up-notification">
    <q-card-section class="text-center q-pa-xl">
      <!-- Animación de celebración -->
      <div class="celebration-container q-mb-lg">
        <div class="celebration-icon">🎉</div>
        <div class="level-up-text">
          <h2 class="text-h3 text-weight-bold text-positive q-mb-sm">
            ¡NIVEL {{ level }}!
          </h2>
          <p class="text-h6 text-grey-7">
            ¡Felicitaciones por tu progreso!
          </p>
        </div>
      </div>

      <!-- Información del nuevo nivel -->
      <div class="new-level-info q-mb-lg">
        <q-avatar size="80px" :style="{ backgroundColor: rankInfo.color }" class="level-avatar q-mb-md">
          <span class="text-h2">{{ rankInfo.icon }}</span>
        </q-avatar>
        <h3 class="text-h5 text-weight-medium q-mb-xs">{{ rankInfo.title }}</h3>
        <p class="text-grey-6">Has alcanzado un nuevo rango</p>
      </div>

      <!-- Beneficios desbloqueados -->
      <div v-if="unlockedFeatures.length > 0" class="unlocked-features q-mb-lg">
        <h4 class="text-h6 text-weight-medium q-mb-md">🔓 Nuevas funcionalidades</h4>
        <q-list dense>
          <q-item
            v-for="feature in unlockedFeatures"
            :key="feature"
            class="feature-item"
          >
            <q-item-section avatar>
              <q-icon name="new_releases" color="positive" />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ feature }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </div>

      <!-- Mensaje motivacional -->
      <div class="motivational-message q-mb-lg">
        <q-icon name="format_quote" size="24px" color="grey-5" />
        <p class="text-body1 text-italic q-ma-sm">
          {{ motivationalMessage }}
        </p>
      </div>

      <!-- Estadísticas del logro -->
      <div class="achievement-stats q-mb-lg">
        <div class="row q-gutter-md justify-center">
          <div class="stat-item text-center">
            <div class="text-h4 text-weight-bold text-primary">{{ level }}</div>
            <div class="text-caption text-grey-6">Nivel Actual</div>
          </div>
          <div class="stat-item text-center">
            <div class="text-h4 text-weight-bold text-positive">+{{ experienceGained }}</div>
            <div class="text-caption text-grey-6">XP Ganada</div>
          </div>
          <div class="stat-item text-center">
            <div class="text-h4 text-weight-bold text-secondary">{{ totalExperience }}</div>
            <div class="text-caption text-grey-6">XP Total</div>
          </div>
        </div>
      </div>
    </q-card-section>

    <q-separator />

    <q-card-actions align="center" class="q-pa-lg">
      <q-btn
        label="¡Seguir progresando!"
        color="positive"
        size="lg"
        @click="$emit('close')"
        unelevated
        rounded
        class="celebration-btn"
      />
    </q-card-actions>
  </q-card>
</template>

<script>
import levelService from '@/services/levelService';

export default {
  name: 'LevelUpNotification',
  props: {
    level: {
      type: Number,
      required: true
    }
  },
  emits: ['close'],

  computed: {
    rankInfo() {
      return levelService.getRankInfo(this.level);
    },

    motivationalMessage() {
      const messages = [
        '¡Tu dedicación y constancia han dado frutos!',
        '¡Cada paso te acerca más a tus metas!',
        '¡Sigue así, estás haciendo un gran trabajo!',
        '¡Tu productividad está alcanzando nuevas alturas!',
        '¡Este nivel es solo el comienzo de grandes cosas!'
      ];
      return messages[Math.floor(Math.random() * messages.length)];
    },

    experienceGained() {
      // Calcular XP ganada para este nivel (simplificado)
      return levelService.calculateExperienceForLevel(this.level - 1);
    },

    totalExperience() {
      // Calcular XP total aproximada (simplificado)
      let total = 0;
      for (let i = 1; i < this.level; i++) {
        total += levelService.calculateExperienceForLevel(i);
      }
      return levelService.formatExperience(total);
    },

    unlockedFeatures() {
      const features = [];
      
      // Características que se desbloquean en ciertos niveles
      if (this.level === 5) {
        features.push('Estadísticas avanzadas');
      }
      if (this.level === 10) {
        features.push('Temas personalizados');
        features.push('Exportación de datos');
      }
      if (this.level === 15) {
        features.push('Integración con calendario externo');
      }
      if (this.level === 20) {
        features.push('Modo colaborativo');
      }
      if (this.level === 25) {
        features.push('Análisis de productividad');
      }
      if (this.level === 30) {
        features.push('Automatizaciones avanzadas');
      }
      if (this.level === 50) {
        features.push('Acceso a funciones beta');
      }

      return features;
    }
  },

  mounted() {
    // Reproducir animación de entrada
    this.$nextTick(() => {
      const notification = this.$el.querySelector('.level-up-notification');
      if (notification) {
        notification.classList.add('show');
      }
    });

    // Auto-reproducir sonido de celebración si está disponible
    this.playLevelUpSound();
  },

  methods: {
    playLevelUpSound() {
      try {
        // Crear un sonido de celebración simple
        if ('AudioContext' in window || 'webkitAudioContext' in window) {
          const audioContext = new (window.AudioContext || window.webkitAudioContext)();
          const oscillator = audioContext.createOscillator();
          const gainNode = audioContext.createGain();
          
          oscillator.connect(gainNode);
          gainNode.connect(audioContext.destination);
          
          oscillator.frequency.setValueAtTime(523.25, audioContext.currentTime); // Do
          oscillator.frequency.setValueAtTime(659.25, audioContext.currentTime + 0.2); // Mi
          oscillator.frequency.setValueAtTime(783.99, audioContext.currentTime + 0.4); // Sol
          
          gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
          gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.6);
          
          oscillator.start();
          oscillator.stop(audioContext.currentTime + 0.6);
        }
      } catch (error) {
        console.log('No se pudo reproducir el sonido de celebración:', error);
      }
    }
  }
}
</script>

<style scoped>
.level-up-notification {
  min-width: 400px;
  max-width: 500px;
  border-radius: 16px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  overflow: hidden;
  transform: scale(0.8);
  opacity: 0;
  transition: all 0.3s ease;
}

.level-up-notification.show {
  transform: scale(1);
  opacity: 1;
}

.celebration-container {
  position: relative;
}

.celebration-icon {
  font-size: 4rem;
  animation: bounce 1s infinite alternate;
}

.level-avatar {
  border: 3px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  animation: glow 2s ease-in-out infinite alternate;
}

.unlocked-features {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 16px;
}

.feature-item {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  margin-bottom: 4px;
}

.achievement-stats {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 16px;
}

.stat-item {
  min-width: 80px;
}

.celebration-btn {
  animation: pulse 2s infinite;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Animaciones */
@keyframes bounce {
  0% { transform: translateY(0); }
  100% { transform: translateY(-20px); }
}

@keyframes glow {
  0% { box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3); }
  100% { box-shadow: 0 4px 24px rgba(255, 255, 255, 0.4); }
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

/* Efectos de partículas de celebración */
.celebration-container::before {
  content: '✨';
  position: absolute;
  top: 10%;
  left: 20%;
  font-size: 1.5rem;
  animation: float 3s ease-in-out infinite;
}

.celebration-container::after {
  content: '🎊';
  position: absolute;
  top: 20%;
  right: 20%;
  font-size: 1.5rem;
  animation: float 3s ease-in-out infinite reverse;
}

@keyframes float {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(180deg); }
}

/* Mobile responsive */
@media (max-width: 600px) {
  .level-up-notification {
    min-width: 300px;
    max-width: 90vw;
  }
  
  .celebration-icon {
    font-size: 3rem;
  }
  
  .level-up-text h2 {
    font-size: 2rem;
  }
  
  .achievement-stats .row {
    flex-direction: column;
    gap: 12px;
  }
}

/* Dark mode support */
.body--dark .level-up-notification {
  background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
}

.body--dark .unlocked-features,
.body--dark .achievement-stats {
  background: rgba(0, 0, 0, 0.2);
}

.body--dark .feature-item {
  background: rgba(0, 0, 0, 0.1);
}
</style>