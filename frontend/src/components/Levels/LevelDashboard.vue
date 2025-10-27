<template>
  <q-page class="level-dashboard-page">
    <!-- Header con información del nivel actual -->
    <div class="level-header q-mb-lg">
      <div class="row items-center justify-between">
        <div>
          <h1 class="text-h4 text-weight-bold">
            <q-icon :name="rankInfo.icon" class="q-mr-sm" />
            Mi Progreso
          </h1>
          <p class="text-grey-6">{{ motivationalMessage }}</p>
        </div>
        <SyncStatusIndicator :always-show="false" />
      </div>
    </div>

    <div class="level-container">
      <!-- Card principal de nivel -->
      <q-card class="level-card q-mb-md">
        <q-card-section>
          <div class="row items-center q-gutter-lg">
            <!-- Avatar del nivel -->
            <div class="level-avatar-container">
              <q-avatar size="80px" :style="{ backgroundColor: rankInfo.color }" class="level-avatar">
                <span class="text-h3">{{ rankInfo.icon }}</span>
              </q-avatar>
              <div class="level-number">{{ getCurrentLevel }}</div>
            </div>

            <!-- Información del nivel -->
            <div class="col">
              <div class="level-info">
                <h2 class="text-h5 text-weight-bold q-mb-xs">
                  Nivel {{ getCurrentLevel }} - {{ rankInfo.title }}
                </h2>
                <p class="text-grey-6 q-mb-md">
                  {{ getFormattedExperience }} XP total
                </p>

                <!-- Barra de progreso -->
                <div class="progress-container q-mb-sm">
                  <div class="row items-center justify-between q-mb-xs">
                    <span class="text-caption">Progreso al siguiente nivel</span>
                    <span class="text-caption text-weight-bold">{{ getLevelProgress }}%</span>
                  </div>
                  <q-linear-progress
                    :value="getLevelProgress / 100"
                    :color="progressColor"
                    size="12px"
                    rounded
                    class="progress-bar"
                    animation-speed="500"
                  />
                  <div class="row items-center justify-between q-mt-xs">
                    <span class="text-caption text-grey-5">{{ getCurrentExperience }} XP</span>
                    <span class="text-caption text-grey-5">{{ getExperienceToNextLevel }} XP</span>
                  </div>
                </div>

                <!-- Aviso de verificación de email para nivel 1 -->
                <div v-if="getCurrentLevel === 0 && !level1Requirement.emailVerified" class="verification-warning q-mt-md">
                  <q-banner class="bg-warning text-dark" rounded>
                    <template v-slot:avatar>
                      <q-icon name="mail" color="warning" />
                    </template>
                    <div class="text-body2">
                      <strong>{{ $t('settings.account.verificationRequired') }}:</strong><br>
                      {{ $t('settings.account.verifyToAdvance') }}
                    </div>
                    <template v-slot:action>
                      <q-btn
                        flat
                        color="dark"
                        :label="$t('settings.account.verify')"
                        @click="goToSettings"
                        icon="arrow_forward"
                      />
                    </template>
                  </q-banner>
                </div>
              </div>
            </div>

            <!-- Botones de acciones -->
            <div class="level-actions">
              <q-btn
                @click="showLevelDetails = true"
                color="primary"
                icon="info"
                round
                flat
                size="lg"
              >
                <q-tooltip>Ver detalles del nivel</q-tooltip>
              </q-btn>

              <!-- Botón de prueba para simular level up -->
              <q-btn
                @click="testLevelUp"
                color="purple"
                icon="emoji_events"
                round
                flat
                size="lg"
              >
                <q-tooltip>🎮 Simular subida de nivel (PRUEBA)</q-tooltip>
              </q-btn>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Estadísticas rápidas -->
      <div class="row q-gutter-md q-mb-lg">
        <div class="col-12 col-md-4">
          <q-card class="stats-card">
            <q-card-section class="text-center">
              <q-icon name="emoji_events" size="32px" color="amber" class="q-mb-sm" />
              <div class="text-h6 text-weight-bold">{{ completedAchievements.length }}</div>
              <div class="text-caption text-grey-6">Logros Completados</div>
              <div class="text-caption text-positive">{{ achievementCompletionRate }}% completado</div>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-md-4">
          <q-card class="stats-card">
            <q-card-section class="text-center">
              <q-icon name="trending_up" size="32px" color="positive" class="q-mb-sm" />
              <div class="text-h6 text-weight-bold">{{ experienceStats.experience_today || 0 }}</div>
              <div class="text-caption text-grey-6">XP Hoy</div>
              <div class="text-caption text-blue">{{ experienceStats.experience_this_week || 0 }} esta semana</div>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-md-4">
          <q-card class="stats-card">
            <q-card-section class="text-center">
              <q-icon name="leaderboard" size="32px" color="purple" class="q-mb-sm" />
              <div class="text-h6 text-weight-bold">#{{ getCurrentUserRank || '?' }}</div>
              <div class="text-caption text-grey-6">Posición Global</div>
              <q-btn 
                flat 
                size="sm" 
                color="primary" 
                @click="showLeaderboard = true"
                class="q-mt-xs"
              >
                Ver ranking
              </q-btn>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Logros recientes y en progreso -->
      <div class="row q-gutter-md">
        <!-- Logros completados recientemente -->
        <div class="col-12 col-lg-6">
          <q-card class="achievements-card">
            <q-card-section>
              <h3 class="text-h6 text-weight-medium q-mb-md">
                <q-icon name="stars" class="q-mr-sm" />
                Logros Recientes
              </h3>

              <div v-if="completedAchievements.length === 0" class="text-center q-pa-lg">
                <q-icon name="emoji_events" size="48px" color="grey-4" />
                <div class="text-body2 text-grey-6 q-mt-sm">
                  Aún no has completado ningún logro
                </div>
                <q-btn 
                  @click="showAllAchievements = true" 
                  color="primary" 
                  flat 
                  class="q-mt-sm"
                >
                  Ver logros disponibles
                </q-btn>
              </div>

              <q-list v-else separator>
                <q-item
                  v-for="achievement in recentAchievements"
                  :key="achievement.achievement.id"
                >
                  <q-item-section avatar>
                    <q-avatar size="40px" :color="achievement.achievement.color || 'primary'">
                      <q-icon :name="achievement.achievement.icon || 'star'" color="white" />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ achievement.achievement.name }}</q-item-label>
                    <q-item-label caption>
                      {{ achievement.achievement.description }}
                    </q-item-label>
                    <q-item-label caption class="text-positive">
                      +{{ achievement.achievement.experience_reward }} XP
                    </q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-item-label caption>
                      {{ formatTimeAgo(achievement.completed_at) }}
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>

              <div v-if="completedAchievements.length > 3" class="text-center q-mt-md">
                <q-btn 
                  @click="showAllAchievements = true" 
                  flat 
                  color="primary"
                >
                  Ver todos los logros ({{ completedAchievements.length }})
                </q-btn>
              </div>
            </q-card-section>
          </q-card>
        </div>

        <!-- Logros en progreso -->
        <div class="col-12 col-lg-6">
          <q-card class="achievements-card">
            <q-card-section>
              <h3 class="text-h6 text-weight-medium q-mb-md">
                <q-icon name="timeline" class="q-mr-sm" />
                En Progreso
              </h3>

              <div v-if="inProgressAchievements.length === 0" class="text-center q-pa-lg">
                <q-icon name="hourglass_empty" size="48px" color="grey-4" />
                <div class="text-body2 text-grey-6 q-mt-sm">
                  No tienes logros en progreso
                </div>
              </div>

              <q-list v-else separator>
                <q-item
                  v-for="achievement in inProgressAchievements.slice(0, 3)"
                  :key="achievement.achievement.id"
                >
                  <q-item-section avatar>
                    <q-avatar size="40px" color="orange">
                      <q-icon :name="achievement.achievement.icon || 'schedule'" color="white" />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ achievement.achievement.name }}</q-item-label>
                    <q-item-label caption>
                      {{ achievement.progress }} / {{ achievement.achievement.condition_value }}
                    </q-item-label>
                    <q-linear-progress
                      :value="achievement.progress_percentage / 100"
                      color="orange"
                      size="4px"
                      class="q-mt-xs"
                    />
                  </q-item-section>
                  <q-item-section side>
                    <q-item-label caption class="text-weight-bold">
                      {{ achievement.progress_percentage }}%
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>

              <div v-if="inProgressAchievements.length > 3" class="text-center q-mt-md">
                <q-btn 
                  @click="showAllAchievements = true" 
                  flat 
                  color="primary"
                >
                  Ver todos en progreso
                </q-btn>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Logros necesarios para subir de nivel -->
      <q-card class="achievements-card q-mt-md">
        <q-card-section>
          <h3 class="text-h6 text-weight-medium q-mb-md">
            <q-icon name="emoji_events" class="q-mr-sm" />
            Logros Necesarios para Subir de Nivel
          </h3>

          <div class="row q-gutter-sm">
            <div
              v-for="achievement in requiredAchievements"
              :key="achievement.name"
              class="col-12 col-sm-6 col-md-4"
            >
              <q-card flat bordered class="achievement-item">
                <q-card-section class="q-pa-md">
                  <div class="achievement-header q-mb-sm">
                    <span class="achievement-icon">{{ achievement.icon }}</span>
                    <div class="achievement-info">
                      <div class="text-weight-medium">{{ achievement.name }}</div>
                      <q-chip
                        size="sm"
                        :color="achievement.category === 'Básico' ? 'blue' :
                               achievement.category === 'Intermedio' ? 'orange' :
                               achievement.category === 'Avanzado' ? 'purple' :
                               achievement.category === 'Experto' ? 'red' :
                               achievement.category === 'Hábito' ? 'green' : 'grey'"
                        text-color="white"
                      >
                        {{ achievement.category }}
                      </q-chip>
                    </div>
                  </div>
                  <div class="text-caption text-grey-6 q-mb-xs">{{ achievement.description }}</div>
                  <q-chip
                    size="sm"
                    color="positive"
                    text-color="white"
                    icon="add"
                  >
                    {{ achievement.experience }} XP
                  </q-chip>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Sistema de rangos -->
      <q-card class="ranks-card q-mt-md">
        <q-card-section>
          <h3 class="text-h6 text-weight-medium q-mb-md">
            <q-icon name="workspace_premium" class="q-mr-sm" />
            Sistema de Rangos
          </h3>

          <div class="ranks-grid">
            <div
              v-for="rank in allRanks"
              :key="rank.title"
              class="rank-item"
              :class="{ 'current-rank': getCurrentLevel >= rank.minLevel && getCurrentLevel <= rank.maxLevel }"
            >
              <q-card flat bordered class="rank-card">
                <q-card-section class="q-pa-md text-center">
                  <div class="rank-icon q-mb-sm" :style="{ color: rank.color }">
                    {{ rank.icon }}
                  </div>
                  <div class="text-weight-bold q-mb-xs">{{ rank.title }}</div>
                  <div class="text-caption text-grey-6 q-mb-sm">
                    Niveles {{ rank.minLevel }} - {{ rank.maxLevel === Infinity ? '∞' : rank.maxLevel }}
                  </div>
                  <div class="text-caption q-mb-sm">{{ rank.description }}</div>
                  <q-chip
                    size="sm"
                    :color="rank.color"
                    text-color="white"
                    icon="star"
                  >
                    {{ formatExperience(rank.experienceRequired) }} XP Total
                  </q-chip>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- Modal de detalles del nivel -->
    <q-dialog v-model="showLevelDetails">
      <LevelDetailsModal 
        :level="getCurrentLevel"
        :rank-info="rankInfo" 
        @close="showLevelDetails = false"
      />
    </q-dialog>

    <!-- Modal de logros -->
    <q-dialog v-model="showAllAchievements" maximized>
      <AchievementsModal @close="showAllAchievements = false" />
    </q-dialog>

    <!-- Modal de leaderboard -->
    <q-dialog v-model="showLeaderboard">
      <LeaderboardModal @close="showLeaderboard = false" />
    </q-dialog>

    <!-- Notificación de subida de nivel -->
    <q-dialog v-model="showLevelUpNotification" persistent>
      <LevelUpNotification 
        :level="levelUpNotification?.newLevel"
        @close="clearLevelUpNotification"
      />
    </q-dialog>

    <!-- Notificación de nuevo logro -->
    <q-dialog v-model="showAchievementNotification" persistent>
      <AchievementNotification 
        :achievement="achievementNotification?.achievement"
        @close="clearAchievementNotification"
      />
    </q-dialog>
  </q-page>
</template>

<script>
import { useLevelsStore } from '@/stores/levels';
import { useAuthStore } from '@/stores/auth';
import levelService from '@/services/levelService';
import SyncStatusIndicator from '@/components/SyncStatusIndicator.vue';
import LevelDetailsModal from './LevelDetailsModal.vue';
import AchievementsModal from './AchievementsModal.vue';
import LeaderboardModal from './LeaderboardModal.vue';
import LevelUpNotification from './LevelUpNotification.vue';
import AchievementNotification from './AchievementNotification.vue';

export default {
  name: 'LevelDashboard',
  components: {
    SyncStatusIndicator,
    LevelDetailsModal,
    AchievementsModal,
    LeaderboardModal,
    LevelUpNotification,
    AchievementNotification
  },
  data() {
    return {
      showLevelDetails: false,
      showAllAchievements: false,
      showLeaderboard: false,
      requiredAchievements: [],
      allRanks: [],
      levelsStore: useLevelsStore(),
      authStore: useAuthStore()
    };
  },

  computed: {
    getCurrentLevel() {
      return this.levelsStore.getCurrentLevel;
    },

    getCurrentExperience() {
      return this.levelsStore.getCurrentExperience;
    },

    getTotalExperience() {
      return this.levelsStore.getTotalExperience;
    },

    getExperienceToNextLevel() {
      return this.levelsStore.getExperienceToNextLevel;
    },

    getLevelProgress() {
      return this.levelsStore.getLevelProgress;
    },

    getFormattedExperience() {
      return this.levelsStore.getFormattedExperience;
    },

    motivationalMessage() {
      return this.levelsStore.getMotivationalMessage;
    },

    rankInfo() {
      return this.levelsStore.getUserRank;
    },

    progressColor() {
      return levelService.getProgressBarColor(this.getLevelProgress);
    },

    experienceStats() {
      return this.levelsStore.experienceStats;
    },

    completedAchievements() {
      return this.levelsStore.getCompletedAchievements;
    },

    inProgressAchievements() {
      return this.levelsStore.getInProgressAchievements;
    },

    achievementCompletionRate() {
      return this.levelsStore.getAchievementCompletionRate;
    },

    getCurrentUserRank() {
      return this.levelsStore.getCurrentUserRank;
    },

    recentAchievements() {
      return this.completedAchievements
        .sort((a, b) => new Date(b.completed_at) - new Date(a.completed_at))
        .slice(0, 3);
    },

    levelUpNotification() {
      return this.levelsStore.getLevelUpNotification;
    },

    // Función para obtener colores de categoría
    getCategoryColor() {
      return (category) => {
        const colors = {
          'Básico': 'blue',
          'Intermedio': 'orange',
          'Avanzado': 'purple',
          'Experto': 'red',
          'Hábito': 'green'
        };
        return colors[category] || 'grey';
      };
    },

    showLevelUpNotification() {
      return !!this.levelUpNotification;
    },

    showAchievementNotification() {
      return !!this.achievementNotification;
    },

    achievementNotification() {
      return this.levelsStore.getAchievementNotification;
    },

    level1Requirement() {
      return this.levelsStore.getLevel1Requirement;
    },

    isAdmin() {
      return this.authStore.user?.role === 'admin' || this.authStore.user?.is_admin === true;
    }
  },

  methods: {
    formatExperience(experience) {
      return levelService.formatExperience(experience);
    },
    formatTimeAgo(date) {
      return levelService.formatTimeAgo(date);
    },

    clearLevelUpNotification() {
      this.levelsStore.clearLevelUpNotification();
    },

    clearAchievementNotification() {
      this.levelsStore.clearAchievementNotification();
    },

    goToSettings() {
      this.$router.push('/settings');
    },

    async refreshData() {
      try {
        await this.levelsStore.initializeLevelData();
      } catch (error) {
        console.error('Error refreshing level data:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error al actualizar datos del nivel',
          position: 'top'
        });
      }
    },

    testLevelUp() {
      this.$q.dialog({
        title: '🎮 Simular Subida de Nivel',
        message: '¿A qué nivel quieres subir?',
        prompt: {
          model: this.getCurrentLevel + 1,
          type: 'number',
          min: 1,
          max: 50
        },
        cancel: true,
        persistent: false
      }).onOk(targetLevel => {
        this.levelsStore.simulateLevelUp(parseInt(targetLevel));
        this.$q.notify({
          type: 'info',
          message: `Simulando subida al nivel ${targetLevel}...`,
          position: 'top',
          timeout: 2000
        });
      });
    }
  },

  async created() {
    // Inicializar datos del nivel
    try {
      await this.levelsStore.initializeLevelData();

      // Cargar logros necesarios y rangos del sistema
      this.requiredAchievements = levelService.getRequiredAchievements();
      this.allRanks = levelService.getAllRanks();

    } catch (error) {
      console.error('Error initializing level dashboard:', error);
      this.$q.notify({
        type: 'negative',
        message: 'Error al cargar datos del nivel',
        position: 'top'
      });
    }
  },

  beforeUnmount() {
    // Cerrar todos los dialogs para evitar que se queden abiertos al navegar
    this.showLevelDetails = false;
    this.showAllAchievements = false;
    this.showLeaderboard = false;

    // Limpiar todas las notificaciones persistentes y globales de nivel
    this.levelsStore.clearLevelUpNotification();
    this.levelsStore.clearAchievementNotification();

    // También limpiar notificaciones de desbloqueo de notas si existen
    if (this.levelsStore.clearNoteUnlockNotification) {
      this.levelsStore.clearNoteUnlockNotification();
    }
  }
}
</script>

<style scoped>
.level-dashboard-page {
  padding: 16px;
  max-width: 1200px;
  margin: 0 auto;
}

.level-container {
  width: 100%;
}

.level-card {
  border-radius: 12px;
  background: linear-gradient(135deg, #14887D 0%, #176F46 100%);
  color: white;
}

.level-avatar-container {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.level-avatar {
  border: 3px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.level-number {
  position: absolute;
  bottom: -8px;
  right: -8px;
  background: white;
  color: #667eea;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.progress-container {
  max-width: 400px;
}

.progress-bar {
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.2);
}

.stats-card {
  border-radius: 8px;
  transition: transform 0.2s ease;
}

.stats-card:hover {
  transform: translateY(-2px);
}

.achievements-card {
  border-radius: 8px;
  height: 100%;
}

.suggestions-card {
  border-radius: 8px;
}

.suggestion-item {
  height: 100%;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.suggestion-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.level-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .level-dashboard-page {
    padding: 12px;
  }
  
  .level-avatar-container {
    margin-bottom: 16px;
  }
  
  .level-card .row {
    flex-direction: column;
    text-align: center;
  }

  .level-actions {
    margin-top: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
}

@media (max-width: 480px) {
  .level-dashboard-page {
    padding: 8px;
  }
  
  .level-header h1 {
    font-size: 1.3rem;
  }
  
  .suggestion-item {
    margin-bottom: 8px;
  }
}

/* Dark mode support */
.body--dark .level-card {
  background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
}

.body--dark .stats-card,
.body--dark .achievements-card,
.body--dark .suggestions-card {
  background: #1f2937;
}

.body--dark .suggestion-item {
  background: #374151;
  border-color: #4b5563;
}

/* Animations */
@keyframes levelUp {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.level-up-animation {
  animation: levelUp 0.6s ease-in-out;
}

/* Progress bar glow effect */
.progress-bar :deep(.q-linear-progress__track) {
  background: rgba(255, 255, 255, 0.2);
}

.progress-bar :deep(.q-linear-progress__model) {
  box-shadow: 0 0 8px currentColor;
}

/* Achievement styles */
.achievement-item {
  transition: all 0.3s ease;
  height: 100%;
}

.achievement-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.achievement-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.achievement-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.achievement-info {
  flex: 1;
}

/* Ranks grid */
.ranks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.rank-item {
  transition: all 0.3s ease;
}

.rank-item:hover {
  transform: translateY(-2px);
}

.rank-card {
  height: 100%;
  transition: all 0.3s ease;
}

.current-rank .rank-card {
  border: 2px solid #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.rank-icon {
  font-size: 2rem;
}

/* Dark mode styles for new sections */
.body--dark .achievement-item,
.body--dark .rank-card {
  background: #374151;
  border-color: #4b5563;
}
</style>