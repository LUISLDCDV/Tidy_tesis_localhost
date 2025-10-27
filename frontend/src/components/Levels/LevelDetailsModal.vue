<template>
  <q-card class="level-details-modal">
    <q-card-section class="row items-center q-pb-none">
      <div class="text-h6">Detalles del Nivel {{ level }}</div>
      <q-space />
      <q-btn icon="close" flat round dense @click="$emit('close')" />
    </q-card-section>

    <q-separator />

    <q-card-section class="q-pt-lg">
      <!-- Información del rango -->
      <div class="text-center q-mb-lg">
        <q-avatar size="100px" :style="{ backgroundColor: rankInfo.color }" class="rank-avatar q-mb-md">
          <span class="text-h2">{{ rankInfo.icon }}</span>
        </q-avatar>
        <h2 class="text-h4 text-weight-bold q-mb-xs">{{ rankInfo.title }}</h2>
        <p class="text-grey-6">Nivel {{ level }}</p>
      </div>

      <!-- Información de rangos -->
      <q-list separator class="q-mb-lg">
        <q-item-label header>Sistema de Rangos</q-item-label>
        <q-item
          v-for="rank in rankSystem"
          :key="rank.title"
          :class="{ 'current-rank': isCurrentRank(rank) }"
        >
          <q-item-section avatar>
            <q-avatar size="40px" :style="{ backgroundColor: rank.color }">
              <span class="text-h6">{{ rank.icon }}</span>
            </q-avatar>
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ rank.title }}</q-item-label>
            <q-item-label caption>
              Niveles {{ rank.minLevel }}-{{ rank.maxLevel === Infinity ? '∞' : rank.maxLevel }}
            </q-item-label>
          </q-item-section>
          <q-item-section side v-if="isCurrentRank(rank)">
            <q-icon name="star" color="amber" size="md" />
          </q-item-section>
        </q-item>
      </q-list>

      <!-- Experiencia por actividad -->
      <div class="experience-info">
        <q-item-label header>Experiencia por Actividad</q-item-label>
        <div class="row q-gutter-sm">
          <div
            v-for="activity in experienceActivities"
            :key="activity.action"
            class="col-12 col-sm-6"
          >
            <q-card flat bordered class="activity-card">
              <q-card-section class="q-pa-md">
                <div class="row items-center">
                  <q-icon :name="activity.icon" size="24px" :color="activity.color" class="q-mr-sm" />
                  <div class="col">
                    <div class="text-weight-medium">{{ activity.action }}</div>
                    <div class="text-caption text-grey-6">{{ activity.description }}</div>
                  </div>
                  <q-chip size="sm" color="positive" text-color="white">
                    +{{ activity.experience }} XP
                  </q-chip>
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </q-card-section>

    <q-separator />

    <q-card-actions align="right" class="q-pa-md">
      <q-btn
        label="Cerrar"
        color="primary"
        @click="$emit('close')"
        unelevated
      />
    </q-card-actions>
  </q-card>
</template>

<script>
import levelService from '@/services/levelService';

export default {
  name: 'LevelDetailsModal',
  props: {
    level: {
      type: Number,
      required: true
    },
    rankInfo: {
      type: Object,
      required: true
    }
  },
  emits: ['close'],
  
  computed: {
    rankSystem() {
      return [
        { minLevel: 1, maxLevel: 4, title: 'Novato', color: '#64748b', icon: '🌱' },
        { minLevel: 5, maxLevel: 9, title: 'Aprendiz', color: '#06b6d4', icon: '⭐' },
        { minLevel: 10, maxLevel: 19, title: 'Competente', color: '#10b981', icon: '🌟' },
        { minLevel: 20, maxLevel: 34, title: 'Experto', color: '#f59e0b', icon: '🏆' },
        { minLevel: 35, maxLevel: 49, title: 'Maestro', color: '#8b5cf6', icon: '👑' },
        { minLevel: 50, maxLevel: 99, title: 'Gurú', color: '#ef4444', icon: '🔥' },
        { minLevel: 100, maxLevel: Infinity, title: 'Leyenda', color: '#f97316', icon: '💎' }
      ];
    },

    experienceActivities() {
      return [
        {
          action: 'Crear una nota',
          description: 'Documenta ideas y pensamientos',
          experience: 15,
          icon: 'note_add',
          color: 'blue'
        },
        {
          action: 'Establecer un objetivo',
          description: 'Define metas claras',
          experience: 25,
          icon: 'flag',
          color: 'green'
        },
        {
          action: 'Completar una meta',
          description: 'Alcanza tus hitos',
          experience: 50,
          icon: 'check_circle',
          color: 'positive'
        },
        {
          action: 'Crear un calendario',
          description: 'Organiza tu tiempo',
          experience: 25,
          icon: 'calendar_today',
          color: 'purple'
        },
        {
          action: 'Configurar una alarma',
          description: 'No olvides tareas importantes',
          experience: 15,
          icon: 'alarm',
          color: 'orange'
        },
        {
          action: 'Uso diario de la app',
          description: 'La constancia es clave',
          experience: 10,
          icon: 'today',
          color: 'cyan'
        }
      ];
    }
  },

  methods: {
    isCurrentRank(rank) {
      return this.level >= rank.minLevel && this.level <= rank.maxLevel;
    }
  }
}
</script>

<style scoped>
.level-details-modal {
  min-width: 500px;
  max-width: 600px;
}

.rank-avatar {
  border: 3px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.current-rank {
  background: rgba(255, 193, 7, 0.1);
  border-radius: 8px;
}

.activity-card {
  transition: transform 0.2s ease;
}

.activity-card:hover {
  transform: translateY(-2px);
}

/* Mobile responsive */
@media (max-width: 600px) {
  .level-details-modal {
    min-width: 300px;
    max-width: 90vw;
  }
}

/* Dark mode support */
.body--dark .activity-card {
  background: #374151;
  border-color: #4b5563;
}

.body--dark .current-rank {
  background: rgba(255, 193, 7, 0.2);
}
</style>