<template>
  <div class="user-ranking-container">
    <q-card class="ranking-card">
      <q-card-section class="ranking-header">
        <div class="row items-center justify-between">
          <div>
            <h5 class="text-weight-bold q-my-none">{{ $t('admin.ranking.title') }}</h5>
            <p class="text-caption text-grey-6 q-mb-none">{{ $t('admin.ranking.subtitle') }}</p>
          </div>
          <q-btn-group>
            <q-btn
              :outline="rankingType !== 'experience'"
              :color="rankingType === 'experience' ? 'primary' : 'grey-5'"
              @click="rankingType = 'experience'"
              no-caps
            >
              {{ $t('admin.ranking.byExperience') }}
            </q-btn>
            <q-btn
              :outline="rankingType !== 'elements'"
              :color="rankingType === 'elements' ? 'primary' : 'grey-5'"
              @click="rankingType = 'elements'"
              no-caps
            >
              {{ $t('admin.ranking.byElements') }}
            </q-btn>
            <q-btn
              :outline="rankingType !== 'activity'"
              :color="rankingType === 'activity' ? 'primary' : 'grey-5'"
              @click="rankingType = 'activity'"
              no-caps
            >
              {{ $t('admin.ranking.byActivity') }}
            </q-btn>
          </q-btn-group>
        </div>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <q-linear-progress
          v-if="loading"
          indeterminate
          color="primary"
          class="q-mb-md"
        />

        <div v-if="!loading && rankingData.length > 0" class="ranking-list">
          <q-list separator>
            <q-item
              v-for="(user, index) in rankingData"
              :key="user.id"
              class="ranking-item"
              :class="getRankingClass(index)"
            >
              <q-item-section avatar>
                <div class="ranking-position">
                  <q-icon
                    v-if="index < 3"
                    :name="getTrophyIcon(index)"
                    :color="getTrophyColor(index)"
                    size="md"
                  />
                  <span v-else class="ranking-number">{{ index + 1 }}</span>
                </div>
              </q-item-section>

              <q-item-section>
                <q-item-label class="text-weight-medium">
                  {{ user.name }}
                  <q-badge
                    v-if="user.premium"
                    color="amber"
                    text-color="black"
                    label="PREMIUM"
                    class="q-ml-xs"
                  />
                </q-item-label>
                <q-item-label caption>
                  {{ user.email }}
                </q-item-label>
              </q-item-section>

              <q-item-section side>
                <div class="ranking-stats text-right">
                  <div class="stat-value">
                    {{ formatStatValue(user) }}
                  </div>
                  <div class="stat-label">
                    {{ getStatLabel() }}
                  </div>
                </div>
              </q-item-section>

              <q-item-section side>
                <q-badge
                  :color="user.level_color || 'grey'"
                  :label="`Nivel ${user.level || 1}`"
                  rounded
                />
              </q-item-section>
            </q-item>
          </q-list>
        </div>

        <div v-else-if="!loading" class="text-center q-py-lg">
          <q-icon name="emoji_events" size="xl" color="grey-4" />
          <p class="text-grey-6 q-mt-md">{{ $t('admin.ranking.noData') }}</p>
        </div>
      </q-card-section>
    </q-card>

    <!-- Estadísticas Generales -->
    <q-card class="stats-card q-mt-md">
      <q-card-section>
        <h6 class="text-weight-bold q-my-none q-mb-md">{{ $t('admin.ranking.generalStats') }}</h6>

        <div class="row q-gutter-md">
          <div class="col-12 col-md-6">
            <q-linear-progress
              :value="stats.averageLevel / 10"
              color="primary"
              size="20px"
              class="q-mb-xs"
            >
              <div class="absolute-full flex flex-center">
                <q-badge color="white" text-color="primary" :label="`${$t('admin.ranking.avgLevel')}: ${stats.averageLevel}`" />
              </div>
            </q-linear-progress>
          </div>

          <div class="col-12 col-md-6">
            <q-linear-progress
              :value="stats.premiumPercentage / 100"
              color="amber"
              size="20px"
              class="q-mb-xs"
            >
              <div class="absolute-full flex flex-center">
                <q-badge color="white" text-color="amber-8" :label="`Premium: ${stats.premiumPercentage}%`" />
              </div>
            </q-linear-progress>
          </div>
        </div>

        <div class="row q-gutter-sm q-mt-md">
          <q-chip
            v-for="stat in additionalStats"
            :key="stat.key"
            :color="stat.color"
            text-color="white"
            :icon="stat.icon"
            :label="`${stat.label}: ${stat.value}`"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- Distribución por Niveles -->
    <q-card class="levels-distribution-card q-mt-md">
      <q-card-section>
        <h6 class="text-weight-bold q-my-none q-mb-md">{{ $t('admin.ranking.levelDistribution') }}</h6>

        <div class="levels-chart">
          <div
            v-for="level in levelDistribution"
            :key="level.id"
            class="level-bar q-mb-sm"
          >
            <div class="level-info row items-center q-mb-xs">
              <q-icon :name="level.icon || 'star'" :color="level.color" class="q-mr-sm" />
              <span class="level-name text-weight-medium">{{ level.name }}</span>
              <q-space />
              <span class="level-count">{{ level.count }} usuarios ({{ level.percentage }}%)</span>
            </div>
            <q-linear-progress
              :value="level.percentage / 100"
              :color="level.color"
              size="8px"
              rounded
            />
          </div>
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { api } from 'src/services/api'

const { t } = useI18n()

const loading = ref(false)
const rankingType = ref('experience')
const rankingData = ref([])
const stats = ref({
  averageLevel: 0,
  premiumPercentage: 0,
  totalUsers: 0,
  activeUsers: 0
})
const levelDistribution = ref([])

const additionalStats = computed(() => [
  {
    key: 'total',
    label: t('admin.ranking.totalUsers'),
    value: stats.value.totalUsers,
    icon: 'people',
    color: 'blue'
  },
  {
    key: 'active',
    label: t('admin.ranking.activeUsers'),
    value: stats.value.activeUsers,
    icon: 'online_prediction',
    color: 'green'
  },
  {
    key: 'premium',
    label: t('admin.ranking.premiumUsers'),
    value: Math.round(stats.value.totalUsers * stats.value.premiumPercentage / 100),
    icon: 'diamond',
    color: 'amber'
  }
])

const fetchRankingData = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/ranking', {
      params: { type: rankingType.value }
    })

    if (response.data.success) {
      rankingData.value = response.data.data.ranking
      stats.value = response.data.data.stats
      levelDistribution.value = response.data.data.level_distribution
    }
  } catch (error) {
    console.error('Error obteniendo ranking:', error)
  } finally {
    loading.value = false
  }
}

const getRankingClass = (index) => {
  if (index === 0) return 'ranking-first'
  if (index === 1) return 'ranking-second'
  if (index === 2) return 'ranking-third'
  return ''
}

const getTrophyIcon = (index) => {
  const icons = ['emoji_events', 'emoji_events', 'emoji_events']
  return icons[index]
}

const getTrophyColor = (index) => {
  const colors = ['amber', 'grey', 'orange']
  return colors[index]
}

const formatStatValue = (user) => {
  switch (rankingType.value) {
    case 'experience':
      return `${user.experience?.toLocaleString() || 0} XP`
    case 'elements':
      return `${user.elements_count || 0} elementos`
    case 'activity':
      return `${user.activity_score || 0} puntos`
    default:
      return '-'
  }
}

const getStatLabel = () => {
  switch (rankingType.value) {
    case 'experience':
      return t('admin.ranking.experiencePoints')
    case 'elements':
      return t('admin.ranking.totalElements')
    case 'activity':
      return t('admin.ranking.activityScore')
    default:
      return ''
  }
}

watch(rankingType, () => {
  fetchRankingData()
})

onMounted(() => {
  fetchRankingData()
})
</script>

<style scoped>
.user-ranking-container {
  max-width: 1200px;
  margin: 0 auto;
}

.ranking-card, .stats-card, .levels-distribution-card {
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.ranking-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 12px 12px 0 0;
}

.ranking-item {
  border-radius: 8px;
  margin-bottom: 8px;
  transition: all 0.3s ease;
}

.ranking-item:hover {
  background-color: rgba(0, 0, 0, 0.04);
  transform: translateY(-2px);
}

.ranking-first {
  background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
  color: #1a1a1a;
  font-weight: bold;
}

.ranking-second {
  background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%);
  color: #1a1a1a;
  font-weight: bold;
}

.ranking-third {
  background: linear-gradient(135deg, #cd7f32 0%, #deb887 100%);
  color: #1a1a1a;
  font-weight: bold;
}

.ranking-position {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: rgba(0, 0, 0, 0.1);
}

.ranking-number {
  font-weight: bold;
  font-size: 1.1em;
}

.ranking-stats {
  min-width: 100px;
}

.stat-value {
  font-weight: bold;
  font-size: 1.1em;
  color: #176F46;
}

.stat-label {
  font-size: 0.8em;
  color: #666;
  text-transform: uppercase;
}

.levels-chart {
  max-height: 400px;
  overflow-y: auto;
}

.level-bar {
  padding: 8px 0;
}

.level-info {
  margin-bottom: 4px;
}

.level-name {
  font-size: 0.9em;
}

.level-count {
  font-size: 0.85em;
  color: #666;
}

@media (max-width: 600px) {
  .ranking-header .row {
    flex-direction: column;
    gap: 16px;
  }

  .q-btn-group {
    width: 100%;
  }

  .q-btn-group .q-btn {
    flex: 1;
  }
}