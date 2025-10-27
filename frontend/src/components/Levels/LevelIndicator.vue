<template>
  <div class="level-indicator" :class="{ 'compact': compact, 'clickable': clickable }" @click="handleClick">
    <div class="level-info">
      <!-- Avatar del nivel -->
      <q-avatar 
        :size="compact ? '32px' : '40px'" 
        :style="{ backgroundColor: rankInfo.color }" 
        class="level-avatar"
      >
        <span :class="compact ? 'text-caption' : 'text-body2'">{{ rankInfo.icon }}</span>
      </q-avatar>

      <!-- Información del nivel y progreso -->
      <div class="level-details" v-if="!compact">
        <div class="level-text">
          <span class="text-weight-bold">Nivel {{ getCurrentLevel }}</span>
          <span class="text-grey-6 q-ml-xs">{{ rankInfo.title }}</span>
        </div>
        
        <!-- Barra de progreso -->
        <div class="progress-container">
          <q-linear-progress
            :value="getLevelProgress / 100"
            :color="progressColor"
            size="4px"
            rounded
            class="progress-bar"
          />
          <div class="progress-text">
            <span class="text-caption text-grey-5">{{ getCurrentExperience }} / {{ getExperienceToNextLevel }} XP</span>
          </div>
        </div>
      </div>

      <!-- Versión compacta -->
      <div class="level-compact" v-else>
        <div class="text-weight-bold text-caption">Nv. {{ getCurrentLevel }}</div>
        <q-linear-progress
          :value="getLevelProgress / 100"
          :color="progressColor"
          size="3px"
          rounded
          class="compact-progress"
        />
      </div>
    </div>

    <!-- Botón de más información -->
    <q-btn 
      v-if="showMore && !compact"
      flat 
      round 
      dense 
      size="sm"
      icon="info"
      @click.stop="$emit('show-details')"
      class="more-btn"
    />

    <!-- Tooltip con información adicional -->
    <q-tooltip v-if="showTooltip" anchor="top middle" self="bottom middle" class="level-tooltip">
      <div>
        <div class="text-weight-bold">{{ rankInfo.title }} - Nivel {{ getCurrentLevel }}</div>
        <div>{{ getCurrentExperience }} / {{ getExperienceToNextLevel }} XP</div>
        <div>Progreso: {{ getLevelProgress }}%</div>
        <div class="text-caption q-mt-xs">{{ motivationalMessage }}</div>
      </div>
    </q-tooltip>
  </div>
</template>

<script>
import { useLevelsStore } from '@/stores/levels';
import levelService from '@/services/levelService';

export default {
  name: 'LevelIndicator',
  props: {
    compact: {
      type: Boolean,
      default: false
    },
    clickable: {
      type: Boolean,
      default: false
    },
    showMore: {
      type: Boolean,
      default: false
    },
    showTooltip: {
      type: Boolean,
      default: true
    }
  },
  emits: ['click', 'show-details'],

  setup() {
    const levelsStore = useLevelsStore();
    
    return {
      levelsStore
    };
  },

  computed: {
    getCurrentLevel() {
      return this.levelsStore.getCurrentLevel;
    },

    getCurrentExperience() {
      return this.levelsStore.getCurrentExperience;
    },

    getExperienceToNextLevel() {
      return this.levelsStore.getExperienceToNextLevel;
    },

    getLevelProgress() {
      return this.levelsStore.getLevelProgress;
    },

    rankInfo() {
      return this.levelsStore.getUserRank;
    },

    progressColor() {
      return levelService.getProgressBarColor(this.getLevelProgress);
    },

    motivationalMessage() {
      return this.levelsStore.getMotivationalMessage;
    }
  },

  methods: {
    handleClick() {
      if (this.clickable) {
        this.$emit('click');
      }
    }
  }
}
</script>

<style scoped>
.level-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.1);
  transition: all 0.2s ease;
}

.level-indicator.compact {
  padding: 4px;
  gap: 4px;
}

.level-indicator.clickable {
  cursor: pointer;
}

.level-indicator.clickable:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-1px);
}

.level-info {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
}

.level-avatar {
  border: 2px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.level-details {
  flex: 1;
  min-width: 0;
}

.level-text {
  display: flex;
  align-items: baseline;
  gap: 4px;
  margin-bottom: 4px;
}

.progress-container {
  width: 100%;
}

.progress-bar {
  margin-bottom: 2px;
}

.progress-text {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.level-compact {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 60px;
}

.compact-progress {
  width: 100%;
}

.more-btn {
  opacity: 0.7;
  transition: opacity 0.2s ease;
}

.more-btn:hover {
  opacity: 1;
}

/* Variaciones de tamaño */
.level-indicator.compact .level-info {
  gap: 4px;
}

/* Mobile adjustments */
@media (max-width: 480px) {
  .level-indicator {
    padding: 6px;
  }
  
  .level-text {
    flex-direction: column;
    gap: 0;
    line-height: 1.2;
  }
}

/* Dark mode support */
.body--dark .level-indicator {
  background: rgba(0, 0, 0, 0.2);
}

.body--dark .level-indicator.clickable:hover {
  background: rgba(0, 0, 0, 0.3);
}

.body--dark .level-avatar {
  border-color: rgba(255, 255, 255, 0.2);
}

/* Animaciones */
@keyframes levelUp {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.level-up-animation {
  animation: levelUp 0.6s ease-in-out;
}

/* Tooltip styling */
.level-tooltip {
  background: rgba(0, 0, 0, 0.9);
  color: white;
  border-radius: 6px;
  padding: 8px 12px;
  max-width: 200px;
  text-align: center;
}
</style>