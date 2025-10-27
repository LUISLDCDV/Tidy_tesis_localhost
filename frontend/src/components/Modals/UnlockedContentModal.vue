<template>
  <q-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" persistent>
    <q-card class="unlocked-modal" style="min-width: 350px; max-width: 600px;">
      <q-card-section class="modal-header">
        <div class="header-content">
          <div class="unlock-icon">
            <q-icon name="lock_open" size="2rem" />
          </div>
          <h3 class="modal-title">
            {{ $t('levels.contentUnlocked') }}
          </h3>
          <p class="modal-subtitle">
            {{ $t('levels.reachedLevel', { level: newLevel }) }}
          </p>
        </div>
      </q-card-section>

      <q-card-section class="modal-content">
        <!-- Unlocked Note Types -->
        <div v-if="unlockedNoteTypes?.length" class="unlocked-section">
          <h4 class="section-title">
            <q-icon name="note_add" class="section-icon" />
            {{ $t('levels.newNoteTypes') }}
          </h4>

          <div class="unlocked-grid">
            <div
              v-for="noteType in unlockedNoteTypes"
              :key="noteType.id"
              class="unlocked-item note-item"
            >
              <div class="item-icon" :style="{ backgroundColor: noteType.color }">
                <q-icon :name="noteType.icon" />
              </div>
              <div class="item-content">
                <h5 class="item-title">{{ noteType.label }}</h5>
                <p class="item-description">{{ noteType.description }}</p>
              </div>
              <div class="unlock-badge">
                <q-icon name="new_releases" size="1rem" />
              </div>
            </div>
          </div>
        </div>

        <!-- Unlocked Features -->
        <div v-if="unlockedFeatures?.length" class="unlocked-section">
          <h4 class="section-title">
            <q-icon name="auto_awesome" class="section-icon" />
            {{ $t('levels.newFeatures') }}
          </h4>

          <div class="unlocked-grid">
            <div
              v-for="feature in unlockedFeatures"
              :key="feature.id"
              class="unlocked-item feature-item"
            >
              <div class="item-icon" :style="{ backgroundColor: feature.color }">
                <q-icon :name="feature.icon" />
              </div>
              <div class="item-content">
                <h5 class="item-title">{{ feature.name }}</h5>
                <p class="item-description">{{ feature.description }}</p>
              </div>
              <div class="unlock-badge">
                <q-icon name="new_releases" size="1rem" />
              </div>
            </div>
          </div>
        </div>

        <!-- Unlocked Achievements -->
        <div v-if="unlockedAchievements?.length" class="unlocked-section">
          <h4 class="section-title">
            <q-icon name="emoji_events" class="section-icon" />
            {{ $t('levels.newAchievements') }}
          </h4>

          <div class="unlocked-grid">
            <div
              v-for="achievement in unlockedAchievements"
              :key="achievement.id"
              class="unlocked-item achievement-item"
            >
              <div class="item-icon achievement-icon">
                <q-icon :name="achievement.icon || 'emoji_events'" />
              </div>
              <div class="item-content">
                <h5 class="item-title">{{ achievement.name }}</h5>
                <p class="item-description">{{ achievement.description }}</p>
                <div class="achievement-reward">
                  <q-icon name="stars" size="0.9rem" />
                  <span>+{{ achievement.experience_reward }} XP</span>
                </div>
              </div>
              <div class="unlock-badge">
                <q-icon name="new_releases" size="1rem" />
              </div>
            </div>
          </div>
        </div>

        <!-- Category Progress -->
        <div v-if="categoryInfo" class="category-section">
          <h4 class="section-title">
            <q-icon name="trending_up" class="section-icon" />
            {{ $t('levels.categoryProgress') }}
          </h4>

          <div class="category-card">
            <div class="category-header">
              <div class="category-badge" :style="{ backgroundColor: categoryInfo.color }">
                <q-icon :name="categoryInfo.icon" />
              </div>
              <div class="category-info">
                <h5 class="category-name">{{ categoryInfo.name }}</h5>
                <p class="category-description">{{ categoryInfo.description }}</p>
              </div>
            </div>

            <div class="category-progress">
              <div class="progress-info">
                <span class="progress-text">
                  {{ $t('levels.levelRange', { min: categoryInfo.minLevel, max: categoryInfo.maxLevel }) }}
                </span>
                <span class="progress-current">
                  {{ $t('levels.currentLevel', { level: newLevel }) }}
                </span>
              </div>
              <q-linear-progress
                :value="categoryProgressValue"
                color="primary"
                size="8px"
                class="category-progress-bar"
              />
            </div>
          </div>
        </div>

        <!-- Motivational Message -->
        <div class="motivation-section">
          <div class="motivation-card">
            <q-icon name="format_quote" class="quote-icon" />
            <p class="motivation-text">
              {{ motivationalMessage || $t('levels.keepGoing') }}
            </p>
          </div>
        </div>
      </q-card-section>

      <q-card-actions class="modal-actions">
        <div class="actions-container">
          <q-btn
            v-if="showTryButton && unlockedNoteTypes?.length"
            color="primary"
            :label="$t('levels.tryNewNotes')"
            @click="handleTryNotes"
            class="action-btn primary-btn"
          />

          <q-btn
            flat
            :label="$t('common.continue')"
            @click="handleContinue"
            class="action-btn continue-btn"
          />
        </div>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
export default {
  name: 'UnlockedContentModal',
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    newLevel: {
      type: Number,
      required: true
    },
    unlockedNoteTypes: {
      type: Array,
      default: () => []
    },
    unlockedFeatures: {
      type: Array,
      default: () => []
    },
    unlockedAchievements: {
      type: Array,
      default: () => []
    },
    categoryInfo: {
      type: Object,
      default: null
    },
    motivationalMessage: {
      type: String,
      default: null
    },
    showTryButton: {
      type: Boolean,
      default: true
    }
  },
  emits: ['update:modelValue', 'try-notes', 'continue'],
  computed: {
    categoryProgressValue() {
      if (!this.categoryInfo) return 0

      const { minLevel, maxLevel } = this.categoryInfo
      const range = maxLevel - minLevel
      const current = this.newLevel - minLevel

      return Math.min(Math.max(current / range, 0), 1)
    }
  },
  methods: {
    handleTryNotes() {
      this.$emit('try-notes', this.unlockedNoteTypes)
    },

    handleContinue() {
      this.$emit('continue')
      this.$emit('update:modelValue', false)
    }
  }
}
</script>

<style scoped>
.unlocked-modal {
  border-radius: 16px;
  overflow: hidden;
}

/* Header */
.modal-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
  text-align: center;
}

.header-content {
  animation: header-slide-down 0.8s ease-out;
}

.unlock-icon {
  margin-bottom: 1rem;
  animation: icon-bounce 0.8s ease-out 0.2s both;
}

.modal-title {
  margin: 0 0 0.5rem 0;
  font-size: 1.8rem;
  font-weight: bold;
}

.modal-subtitle {
  margin: 0;
  opacity: 0.9;
  font-size: 1rem;
}

/* Content */
.modal-content {
  padding: 2rem;
  max-height: 60vh;
  overflow-y: auto;
}

.unlocked-section {
  margin-bottom: 2rem;
  animation: section-fade-in 0.6s ease-out;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0 0 1rem 0;
  font-size: 1.2rem;
  font-weight: 600;
  color: #374151;
}

.section-icon {
  color: #667eea;
}

/* Unlocked Items Grid */
.unlocked-grid {
  display: grid;
  gap: 1rem;
  grid-template-columns: 1fr;
}

.unlocked-item {
  position: relative;
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  background: white;
  transition: all 0.3s ease;
  animation: item-slide-up 0.6s ease-out;
}

.unlocked-item:hover {
  border-color: #667eea;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
}

.item-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.item-content {
  flex: 1;
  min-width: 0;
}

.item-title {
  margin: 0 0 0.25rem 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #111827;
}

.item-description {
  margin: 0;
  font-size: 0.9rem;
  color: #6b7280;
  line-height: 1.4;
}

.unlock-badge {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 24px;
  height: 24px;
  background: #f59e0b;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  animation: badge-pulse 2s ease-in-out infinite;
}

/* Achievement specific styles */
.achievement-icon {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.achievement-reward {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  margin-top: 0.5rem;
  font-size: 0.85rem;
  color: #059669;
  font-weight: 500;
}

/* Category Section */
.category-section {
  margin-bottom: 2rem;
  animation: section-fade-in 0.8s ease-out 0.2s both;
}

.category-card {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem;
  background: #f9fafb;
}

.category-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.category-badge {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.8rem;
}

.category-name {
  margin: 0 0 0.25rem 0;
  font-size: 1.3rem;
  font-weight: 600;
  color: #111827;
}

.category-description {
  margin: 0;
  color: #6b7280;
  font-size: 0.95rem;
}

.category-progress {
  margin-top: 1rem;
}

.progress-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.progress-text {
  color: #6b7280;
}

.progress-current {
  color: #667eea;
  font-weight: 500;
}

.category-progress-bar {
  border-radius: 4px;
}

/* Motivation Section */
.motivation-section {
  animation: section-fade-in 1s ease-out 0.4s both;
}

.motivation-card {
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.quote-icon {
  position: absolute;
  top: 0.5rem;
  left: 0.5rem;
  font-size: 1.5rem;
  color: #d1d5db;
  transform: rotate(180deg);
}

.motivation-text {
  margin: 0;
  font-size: 1.1rem;
  font-style: italic;
  color: #374151;
  line-height: 1.6;
}

/* Actions */
.modal-actions {
  padding: 1.5rem 2rem;
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
}

.actions-container {
  display: flex;
  gap: 1rem;
  width: 100%;
}

.action-btn {
  flex: 1;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
}

.primary-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.continue-btn {
  color: #6b7280;
}

.continue-btn:hover {
  background-color: #f3f4f6;
}

/* Animations */
@keyframes header-slide-down {
  0% {
    opacity: 0;
    transform: translateY(-30px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes icon-bounce {
  0% {
    opacity: 0;
    transform: scale(0) rotate(180deg);
  }
  50% {
    opacity: 1;
    transform: scale(1.2) rotate(0deg);
  }
  100% {
    opacity: 1;
    transform: scale(1) rotate(0deg);
  }
}

@keyframes section-fade-in {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes item-slide-up {
  0% {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes badge-pulse {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.2);
    opacity: 0.8;
  }
}

/* Dark mode support */
.body--dark .unlocked-modal {
  background: #1e293b;
  color: white;
}

.body--dark .section-title {
  color: #f1f5f9;
}

.body--dark .unlocked-item {
  background: #334155;
  border-color: #475569;
}

.body--dark .unlocked-item:hover {
  border-color: #667eea;
}

.body--dark .item-title {
  color: #f1f5f9;
}

.body--dark .item-description {
  color: #94a3b8;
}

.body--dark .category-card {
  background: #334155;
  border-color: #475569;
}

.body--dark .category-name {
  color: #f1f5f9;
}

.body--dark .category-description {
  color: #94a3b8;
}

.body--dark .motivation-card {
  background: #334155;
}

.body--dark .motivation-text {
  color: #f1f5f9;
}

.body--dark .modal-actions {
  background: #1e293b;
  border-top-color: #475569;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .modal-header {
    padding: 1.5rem;
  }

  .modal-content {
    padding: 1.5rem;
  }

  .modal-title {
    font-size: 1.5rem;
  }

  .unlocked-item {
    padding: 0.75rem;
  }

  .item-icon {
    width: 40px;
    height: 40px;
    font-size: 1.2rem;
  }

  .category-badge {
    width: 48px;
    height: 48px;
    font-size: 1.5rem;
  }

  .actions-container {
    flex-direction: column;
  }
}

@media (max-width: 480px) {
  .modal-header {
    padding: 1rem;
  }

  .modal-content {
    padding: 1rem;
  }

  .modal-title {
    font-size: 1.3rem;
  }

  .unlocked-item {
    padding: 0.5rem;
    gap: 0.75rem;
  }

  .item-icon {
    width: 36px;
    height: 36px;
    font-size: 1.1rem;
  }

  .item-title {
    font-size: 1rem;
  }

  .item-description {
    font-size: 0.85rem;
  }
}
</style>