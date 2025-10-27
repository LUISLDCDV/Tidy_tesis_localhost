<template>
  <q-card class="note-progress-widget">
    <!-- Header -->
    <q-card-section class="widget-header">
      <div class="row items-center justify-between">
        <div>
          <div class="text-h6 text-weight-medium">Tipos de Notas</div>
          <div class="text-caption text-grey-6">
            {{ progress.unlocked }}/{{ progress.total }} desbloqueados ({{ progress.percentage }}%)
          </div>
        </div>
        <q-icon name="note_add" size="md" color="primary" />
      </div>
    </q-card-section>

    <!-- Progress bar -->
    <q-card-section class="q-pt-none">
      <q-linear-progress
        :value="progress.percentage / 100"
        color="primary"
        size="8px"
        rounded
        class="progress-bar"
      />
    </q-card-section>

    <!-- Categories progress -->
    <q-card-section class="categories-section">
      <div class="text-subtitle2 q-mb-sm">Progreso por categorías:</div>
      <div class="categories-grid">
        <div
          v-for="(category, name) in categorizedProgress"
          :key="name"
          class="category-item"
        >
          <div class="category-header">
            <div class="category-name">{{ getCategoryName(name) }}</div>
            <div class="category-stats">{{ category.unlocked }}/{{ category.total }}</div>
          </div>
          <q-linear-progress
            :value="category.percentage / 100"
            :color="getCategoryColor(name)"
            size="4px"
            rounded
            class="category-progress"
          />
        </div>
      </div>
    </q-card-section>

    <!-- Next unlock info -->
    <q-card-section v-if="progress.nextUnlock" class="next-unlock-section">
      <q-separator class="q-mb-md" />
      <div class="next-unlock-card">
        <div class="row items-center q-gutter-sm">
          <q-avatar size="sm" color="orange" text-color="white">
            <q-icon name="lock" size="xs" />
          </q-avatar>
          <div class="flex-1">
            <div class="text-body2 text-weight-medium">Próximo desbloqueo:</div>
            <div class="text-caption text-grey-6">{{ progress.nextUnlock.name }}</div>
          </div>
          <q-badge 
            :label="`Nv. ${progress.nextUnlock.level}`" 
            color="orange" 
            text-color="white" 
          />
        </div>
        <div class="text-caption text-grey-5 q-mt-xs">
          {{ progress.nextUnlock.description }}
        </div>
      </div>
    </q-card-section>

    <!-- All unlocked message -->
    <q-card-section v-else class="all-unlocked-section">
      <q-separator class="q-mb-md" />
      <div class="text-center q-pa-md">
        <q-icon name="celebration" size="lg" color="positive" class="q-mb-sm" />
        <div class="text-body1 text-weight-medium text-positive">
          ¡Felicitaciones!
        </div>
        <div class="text-caption text-grey-6">
          Has desbloqueado todos los tipos de notas disponibles
        </div>
      </div>
    </q-card-section>

    <!-- Action buttons -->
    <q-card-actions class="q-pa-md">
      <q-btn
        flat
        color="primary"
        icon="visibility"
        label="Ver detalles"
        @click="showDetails"
        class="q-mr-sm"
      />
      <q-space />
      <q-btn
        unelevated
        color="primary"
        icon="add"
        label="Crear nota"
        @click="createNote"
      />
    </q-card-actions>
  </q-card>
</template>

<script>
import { useLevelsStore } from '@/stores/levels';
import noteLevelService from '@/services/noteLevelService';

export default {
  name: 'NoteProgressWidget',
  
  setup() {
    const levelsStore = useLevelsStore();
    
    return {
      levelsStore
    };
  },

  computed: {
    userLevel() {
      return this.levelsStore.getCurrentLevel;
    },

    progress() {
      return noteLevelService.getUnlockProgress(this.userLevel);
    },

    categorizedProgress() {
      return noteLevelService.getCategorizedProgress(this.userLevel);
    }
  },

  methods: {
    getCategoryName(category) {
      const names = {
        basic: 'Básicas',
        productivity: 'Productividad',
        creative: 'Creatividad',
        social: 'Colaboración',
        advanced: 'Avanzadas'
      };
      return names[category] || category;
    },

    getCategoryColor(category) {
      const colors = {
        basic: 'positive',
        productivity: 'primary',
        creative: 'purple',
        social: 'orange',
        advanced: 'negative'
      };
      return colors[category] || 'grey';
    },

    showDetails() {
      this.$q.dialog({
        title: 'Progreso de Tipos de Notas',
        message: this.getDetailedProgressMessage(),
        html: true,
        ok: {
          label: 'Cerrar',
          color: 'primary'
        }
      });
    },

    getDetailedProgressMessage() {
      const available = noteLevelService.getAvailableNoteTypes(this.userLevel);
      const locked = noteLevelService.getLockedNoteTypes(this.userLevel);

      let message = `<div class="text-body2 q-mb-md">
        <strong>Disponibles (${available.length}):</strong><br>`;
      
      available.forEach(note => {
        message += `• ${note.name}<br>`;
      });

      if (locked.length > 0) {
        message += `<br><strong>Próximas a desbloquear:</strong><br>`;
        locked.slice(0, 3).forEach(note => {
          message += `• ${note.name} (Nivel ${note.level})<br>`;
        });
      }

      message += '</div>';
      return message;
    },

    createNote() {
      this.$emit('create-note');
    }
  }
};
</script>

<style scoped>
.note-progress-widget {
  border-radius: 12px;
  overflow: hidden;
}

.widget-header {
  background: linear-gradient(135deg, rgba(25, 118, 210, 0.1) 0%, rgba(25, 118, 210, 0.05) 100%);
}

.progress-bar {
  margin-bottom: 8px;
}

.categories-section {
  padding-top: 8px;
  padding-bottom: 8px;
}

.categories-grid {
  display: grid;
  gap: 12px;
}

.category-item {
  padding: 8px 12px;
  background: rgba(0, 0, 0, 0.02);
  border-radius: 8px;
  border-left: 3px solid transparent;
}

.category-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.category-name {
  font-weight: 500;
  font-size: 0.85rem;
}

.category-stats {
  font-size: 0.75rem;
  color: #666;
  font-weight: 600;
}

.category-progress {
  margin-top: 4px;
}

.next-unlock-section {
  background: rgba(255, 152, 0, 0.05);
}

.next-unlock-card {
  padding: 12px;
  background: white;
  border-radius: 8px;
  border-left: 4px solid #ff9800;
}

.all-unlocked-section {
  background: rgba(76, 175, 80, 0.05);
}

/* Category colors */
.category-item:nth-child(1) {
  border-left-color: #4caf50;
}

.category-item:nth-child(2) {
  border-left-color: #2196f3;
}

.category-item:nth-child(3) {
  border-left-color: #9c27b0;
}

.category-item:nth-child(4) {
  border-left-color: #ff9800;
}

.category-item:nth-child(5) {
  border-left-color: #f44336;
}

/* Mobile responsive */
@media (max-width: 599px) {
  .widget-header {
    padding: 16px;
  }

  .widget-header .text-h6 {
    font-size: 1.1rem;
  }

  .categories-grid {
    gap: 8px;
  }

  .category-item {
    padding: 6px 10px;
  }

  .category-name {
    font-size: 0.8rem;
  }

  .category-stats {
    font-size: 0.7rem;
  }

  .next-unlock-card {
    padding: 10px;
  }

  /* Stack action buttons on mobile */
  :deep(.q-card-actions) {
    flex-direction: column;
    gap: 8px;
  }

  :deep(.q-card-actions .q-btn) {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .widget-header {
    padding: 12px;
  }

  .categories-section {
    padding: 8px 12px;
  }

  .next-unlock-section,
  .all-unlocked-section {
    padding: 12px;
  }
}

/* Dark mode support */
.body--dark .category-item {
  background: rgba(255, 255, 255, 0.05);
}

.body--dark .next-unlock-card {
  background: #2d2d2d;
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .category-item {
    border-left-width: 4px;
  }

  .progress-bar,
  .category-progress {
    height: 12px !important;
  }
}

/* Animations */
.category-item {
  transition: all 0.2s ease;
}

.category-item:hover {
  transform: translateX(2px);
  background: rgba(0, 0, 0, 0.05);
}

.body--dark .category-item:hover {
  background: rgba(255, 255, 255, 0.08);
}

/* Progress animations */
@keyframes progressFill {
  from {
    width: 0%;
  }
  to {
    width: var(--progress-width);
  }
}

.progress-bar,
.category-progress {
  animation: progressFill 0.8s ease-out;
}
</style>