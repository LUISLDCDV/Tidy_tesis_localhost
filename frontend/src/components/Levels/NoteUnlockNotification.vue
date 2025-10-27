<template>
  <q-dialog 
    v-model="isVisible" 
    persistent
    transition-show="scale"
    transition-hide="scale"
  >
    <q-card class="unlock-notification-card">
      <q-card-section class="text-center celebration-section">
        <!-- Celebration animation -->
        <div class="celebration-animation">
          <q-icon name="star" class="star-icon star-1" color="amber" size="24px" />
          <q-icon name="star" class="star-icon star-2" color="orange" size="20px" />
          <q-icon name="star" class="star-icon star-3" color="amber" size="16px" />
          <q-icon name="star" class="star-icon star-4" color="orange" size="18px" />
        </div>

        <!-- Main unlock icon -->
        <div class="unlock-icon-container">
          <q-icon name="lock_open" size="64px" color="positive" class="unlock-icon" />
        </div>

        <!-- Title -->
        <div class="text-h5 text-weight-bold q-mt-md unlock-title">
          ¡Nuevas notas desbloqueadas!
        </div>
        
        <!-- Subtitle -->
        <div class="text-subtitle1 text-grey-7 q-mt-xs">
          Has alcanzado el nivel {{ newLevel }} y has desbloqueado {{ unlockedNotes.length }} tipo(s) de nota nuevos
        </div>
      </q-card-section>

      <q-separator />

      <!-- Unlocked notes list -->
      <q-card-section class="unlocked-notes-section">
        <q-list>
          <q-item
            v-for="note in unlockedNotes"
            :key="note.id"
            class="unlocked-note-item"
          >
            <q-item-section avatar>
              <q-avatar 
                :color="getNoteTypeColor(note.id - 1)" 
                text-color="white" 
                size="md"
                class="pulse-animation"
              >
                {{ note.id }}
              </q-avatar>
            </q-item-section>
            <q-item-section>
              <q-item-label class="text-weight-medium">
                {{ note.name }}
              </q-item-label>
              <q-item-label caption class="text-grey-6">
                {{ note.description }}
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-icon name="new_releases" color="positive" size="sm" />
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>

      <q-separator />

      <!-- Actions -->
      <q-card-actions class="q-pa-md">
        <q-btn
          flat
          label="Crear nota ahora"
          color="primary"
          icon="create"
          @click="createNote"
          class="q-mr-sm"
        />
        <q-space />
        <q-btn
          unelevated
          label="¡Genial!"
          color="positive"
          @click="close"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
export default {
  name: 'NoteUnlockNotification',
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    newLevel: {
      type: Number,
      required: true
    },
    unlockedNotes: {
      type: Array,
      default: () => []
    }
  },
  emits: ['update:modelValue', 'create-note', 'close'],
  
  computed: {
    isVisible: {
      get() {
        return this.modelValue;
      },
      set(val) {
        this.$emit('update:modelValue', val);
      }
    }
  },

  methods: {
    getNoteTypeColor(index) {
      const colors = [
        'primary', 'secondary', 'accent', 'positive', 'negative', 
        'info', 'warning', 'purple', 'pink', 'indigo',
        'teal', 'orange', 'brown', 'blue-grey', 'deep-orange', 'light-green'
      ];
      return colors[index % colors.length];
    },

    createNote() {
      // Emitir evento para crear una nota con el primer tipo desbloqueado
      if (this.unlockedNotes.length > 0) {
        this.$emit('create-note', this.unlockedNotes[0]);
      }
      this.close();
    },

    close() {
      this.isVisible = false;
      this.$emit('close');
    }
  },

  mounted() {
    // Auto-play sound effect if available
    if (this.isVisible && typeof Audio !== 'undefined') {
      try {
        // Create a simple success sound using Web Audio API
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.setValueAtTime(523.25, audioContext.currentTime); // C5
        oscillator.frequency.setValueAtTime(659.25, audioContext.currentTime + 0.1); // E5
        oscillator.frequency.setValueAtTime(783.99, audioContext.currentTime + 0.2); // G5
        
        gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.5);
      } catch (error) {
        // Silently fail if audio is not supported
        console.log('Audio not supported');
      }
    }
  }
};
</script>

<style scoped>
.unlock-notification-card {
  min-width: 400px;
  max-width: 500px;
  border-radius: 16px;
  overflow: hidden;
  position: relative;
}

.celebration-section {
  background: linear-gradient(135deg, #f3e5f5 0%, #e8f5e8 100%);
  position: relative;
  padding: 32px 24px 24px;
}

.celebration-animation {
  position: absolute;
  top: 16px;
  left: 0;
  right: 0;
  height: 60px;
  pointer-events: none;
}

.star-icon {
  position: absolute;
  animation: twinkle 2s ease-in-out infinite;
}

.star-1 {
  top: 10px;
  left: 20%;
  animation-delay: 0s;
}

.star-2 {
  top: 20px;
  right: 25%;
  animation-delay: 0.3s;
}

.star-3 {
  top: 5px;
  left: 70%;
  animation-delay: 0.6s;
}

.star-4 {
  top: 25px;
  left: 50%;
  animation-delay: 0.9s;
}

.unlock-icon-container {
  margin-top: 24px;
}

.unlock-icon {
  animation: bounce 1s ease-in-out infinite;
}

.unlock-title {
  color: #2e7d32;
  animation: slideInUp 0.6s ease-out;
}

.unlocked-notes-section {
  max-height: 300px;
  overflow-y: auto;
}

.unlocked-note-item {
  border-radius: 8px;
  margin: 4px 0;
  background: rgba(76, 175, 80, 0.05);
  border-left: 4px solid #4caf50;
  animation: slideInLeft 0.5s ease-out;
}

.pulse-animation {
  animation: pulse 2s ease-in-out infinite;
}

/* Animations */
@keyframes twinkle {
  0%, 100% {
    opacity: 0.3;
    transform: scale(1) rotate(0deg);
  }
  50% {
    opacity: 1;
    transform: scale(1.2) rotate(180deg);
  }
}

@keyframes bounce {
  0%, 20%, 53%, 80%, 100% {
    transform: translate3d(0, 0, 0);
  }
  40%, 43% {
    transform: translate3d(0, -8px, 0);
  }
  70% {
    transform: translate3d(0, -4px, 0);
  }
  90% {
    transform: translate3d(0, -2px, 0);
  }
}

@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes pulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

/* Mobile responsive */
@media (max-width: 599px) {
  .unlock-notification-card {
    min-width: 300px;
    max-width: 95vw;
    margin: 16px;
  }

  .celebration-section {
    padding: 24px 16px 16px;
  }

  .unlock-icon {
    font-size: 48px !important;
  }

  .unlock-title {
    font-size: 1.25rem;
  }

  .star-icon {
    display: none; /* Hide stars on small screens to reduce clutter */
  }
}

@media (max-width: 480px) {
  .unlock-notification-card {
    min-width: 280px;
    border-radius: 12px;
  }

  .celebration-section {
    padding: 20px 12px 12px;
  }

  .text-h5 {
    font-size: 1.1rem;
  }

  .text-subtitle1 {
    font-size: 0.9rem;
  }

  .unlocked-note-item {
    margin: 2px 0;
  }

  .unlocked-note-item :deep(.q-item__section) {
    padding: 8px 4px;
  }
}

/* Dark mode support */
.body--dark .celebration-section {
  background: linear-gradient(135deg, #2d2d2d 0%, #1e3a1e 100%);
}

.body--dark .unlock-title {
  color: #81c784;
}

.body--dark .unlocked-note-item {
  background: rgba(76, 175, 80, 0.1);
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .unlocked-note-item {
    border-left-width: 6px;
  }

  .unlock-icon {
    filter: drop-shadow(0 0 4px currentColor);
  }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .star-icon,
  .unlock-icon,
  .pulse-animation {
    animation: none;
  }

  .unlock-title,
  .unlocked-note-item {
    animation: none;
  }
}

/* Print styles */
@media print {
  .unlock-notification-card {
    box-shadow: none;
    border: 1px solid #ccc;
  }

  .celebration-animation {
    display: none;
  }
}
</style>