import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import levelService from '@/services/levelService'
import noteLevelService from '@/services/noteLevelService'
import { useUserSettingsStore } from '@/stores/userSettings'

export const useLevelsStore = defineStore('levels', () => {
  // Estado reactivo
  const userLevel = ref({
    level: 1,
    current_experience: 0,
    total_experience: 0,
    experience_to_next_level: 150,
    level_progress: 0
  })

  const experienceStats = ref({
    current_level: 1,
    total_experience: 0,
    current_level_experience: 0,
    experience_needed_for_next: 150,
    progress_percentage: 0,
    experience_today: 0,
    experience_this_week: 0,
    experience_this_month: 0
  })

  const achievements = ref({
    completed: [],
    in_progress: [],
    available: []
  })

  const leaderboard = ref({
    users: [],
    current_user_rank: null,
    total_users: 0
  })

  // Estados de carga
  const loading = ref({
    level: false,
    experience: false,
    achievements: false,
    leaderboard: false
  })

  // Errores
  const errors = ref({
    level: null,
    experience: null,
    achievements: null,
    leaderboard: null
  })

  // Notificaciones de nivel
  const levelUpNotification = ref(null)
  const achievementNotification = ref(null)
  const noteUnlockNotification = ref(null)

  // Cache de datos
  const lastUpdated = ref({
    level: null,
    experience: null,
    achievements: null,
    leaderboard: null
  })

  // Getters computados
  const getCurrentLevel = computed(() => userLevel.value.level)
  const getCurrentExperience = computed(() => userLevel.value.current_experience)
  const getTotalExperience = computed(() => userLevel.value.total_experience)
  const getExperienceToNextLevel = computed(() => userLevel.value.experience_to_next_level)
  const getLevelProgress = computed(() => userLevel.value.level_progress)

  const getUserRank = computed(() => {
    return levelService.getRankInfo(userLevel.value.level)
  })

  const getCompletedAchievements = computed(() => achievements.value.completed)
  const getInProgressAchievements = computed(() => achievements.value.in_progress)
  const getAvailableAchievements = computed(() => achievements.value.available)

  const getAchievementCompletionRate = computed(() => {
    const total = achievements.value.completed.length + 
                 achievements.value.in_progress.length + 
                 achievements.value.available.length
    
    if (total === 0) return 0
    
    return Math.round((achievements.value.completed.length / total) * 100)
  })

  const getFormattedExperience = computed(() => {
    return levelService.formatExperience(userLevel.value.total_experience)
  })

  const getMotivationalMessage = computed(() => {
    return levelService.getMotivationalMessage(
      userLevel.value.level_progress,
      userLevel.value.level
    )
  })

  // Verificación de email para nivel 1
  const canAdvanceToLevel1 = computed(() => {
    const userSettings = useUserSettingsStore()
    if (userLevel.value.level === 0) {
      return userSettings.isEmailVerified
    }
    return true
  })

  const getLevel1Requirement = computed(() => {
    const userSettings = useUserSettingsStore()
    return {
      emailVerified: userSettings.isEmailVerified,
      canAdvance: canAdvanceToLevel1.value,
      message: userSettings.isEmailVerified
        ? 'Email verificado correctamente'
        : 'Debes verificar tu email para avanzar al nivel 1'
    }
  })

  // Estados de carga
  const isLoadingLevel = computed(() => loading.value.level)
  const isLoadingAchievements = computed(() => loading.value.achievements)
  const isLoadingLeaderboard = computed(() => loading.value.leaderboard)

  // Errores
  const getLevelError = computed(() => errors.value.level)
  const getAchievementsError = computed(() => errors.value.achievements)
  const getLeaderboardError = computed(() => errors.value.leaderboard)

  // Notificaciones
  const getLevelUpNotification = computed(() => levelUpNotification.value)
  const getAchievementNotification = computed(() => achievementNotification.value)
  const getNoteUnlockNotification = computed(() => noteUnlockNotification.value)

  // Clasificación
  const getLeaderboardUsers = computed(() => leaderboard.value.users)
  const getCurrentUserRank = computed(() => leaderboard.value.current_user_rank)

  // Acciones
  async function fetchUserLevel() {
    loading.value.level = true
    errors.value.level = null
    
    try {
      const response = await levelService.getUserLevel()
      userLevel.value = { ...userLevel.value, ...response.level_info }
      lastUpdated.value.level = new Date()
    } catch (error) {
      console.error('Error al cargar nivel del usuario:', error)
      errors.value.level = error.response?.data?.message || 'Error al cargar información de nivel'
    } finally {
      loading.value.level = false
    }
  }

  async function fetchExperienceStats() {
    loading.value.experience = true
    
    try {
      const response = await levelService.getUserExperience()
      experienceStats.value = { ...experienceStats.value, ...response.experience_stats }
      lastUpdated.value.experience = new Date()
    } catch (error) {
      console.error('Error al cargar estadísticas de experiencia:', error)
      errors.value.experience = error.response?.data?.message || 'Error al cargar experiencia'
    } finally {
      loading.value.experience = false
    }
  }

  async function fetchUserAchievements(filter = 'all') {
    loading.value.achievements = true
    errors.value.achievements = null
    
    try {
      const response = await levelService.getUserAchievements(filter)
      
      if (filter === 'all') {
        achievements.value = {
          completed: response.achievements.filter(a => a.is_completed),
          in_progress: response.achievements.filter(a => !a.is_completed && a.progress > 0),
          available: response.achievements.filter(a => !a.is_completed && a.progress === 0)
        }
      } else {
        achievements.value[filter] = response.achievements
      }
      
      lastUpdated.value.achievements = new Date()
    } catch (error) {
      console.error('Error al cargar logros:', error)
      errors.value.achievements = error.response?.data?.message || 'Error al cargar logros'
    } finally {
      loading.value.achievements = false
    }
  }

  async function fetchLeaderboard({ limit = 20, period = 'all_time' } = {}) {
    loading.value.leaderboard = true
    errors.value.leaderboard = null
    
    try {
      const response = await levelService.getLeaderboard(limit, period)
      leaderboard.value = { ...leaderboard.value, ...response }
      lastUpdated.value.leaderboard = new Date()
    } catch (error) {
      console.error('Error al cargar clasificación:', error)
      errors.value.leaderboard = error.response?.data?.message || 'Error al cargar clasificación'
    } finally {
      loading.value.leaderboard = false
    }
  }

  // Inicializar todos los datos
  async function initializeLevelData() {
    await Promise.all([
      fetchUserLevel(),
      fetchExperienceStats(),
      fetchUserAchievements('all')
    ])
  }

  // Actualizar nivel del usuario (para notificaciones en tiempo real)
  function updateUserLevel({ level, experience, showNotification = false }) {
    const currentLevel = userLevel.value.level
    const userSettings = useUserSettingsStore()

    // Verificar si puede subir al nivel 1 sin verificar email
    if (level >= 1 && currentLevel === 0 && !userSettings.isEmailVerified) {
      // No permitir subir al nivel 1 sin verificar email
      level = 0
      experience = Math.min(experience, levelService.calculateExperienceForLevel(1) - 1)
    }

    // Actualizar datos del nivel
    userLevel.value = {
      level: level,
      total_experience: experience,
      current_experience: experience % levelService.calculateExperienceForLevel(level),
      experience_to_next_level: levelService.calculateExperienceForLevel(level + 1),
      level_progress: levelService.calculateLevelProgress(
        experience % levelService.calculateExperienceForLevel(level),
        levelService.calculateExperienceForLevel(level + 1)
      )
    }

    // Mostrar notificación si subió de nivel
    if (showNotification && level > currentLevel) {
      console.log(`🎉 ¡NIVEL SUBIDO! Nivel anterior: ${currentLevel} → Nuevo nivel: ${level}`)

      // Verificar si se desbloquearon nuevos tipos de notas
      const newlyUnlockedNotes = noteLevelService.getNewlyUnlockedNoteTypes(currentLevel, level)

      levelUpNotification.value = {
        newLevel: level,
        timestamp: new Date(),
        message: `¡Felicitaciones! Has alcanzado el nivel ${level}`
      }

      // Si se desbloquearon notas, mostrar notificación especial
      if (newlyUnlockedNotes.length > 0) {
        noteUnlockNotification.value = {
          newLevel: level,
          unlockedNotes: newlyUnlockedNotes,
          timestamp: new Date()
        }
      }

      // Auto-limpiar notificación después de 5 segundos
      setTimeout(() => {
        levelUpNotification.value = null
      }, 5000)
    }

    // Recargar estadísticas
    fetchExperienceStats()
  }

  // Agregar nuevo logro
  function addAchievement({ achievement, showNotification = false }) {
    // Remover de in_progress o available
    achievements.value.in_progress = achievements.value.in_progress
      .filter(a => a.achievement.id !== achievement.achievement.id)
    achievements.value.available = achievements.value.available
      .filter(a => a.achievement.id !== achievement.achievement.id)
    
    // Agregar a completed
    achievements.value.completed.unshift(achievement)

    if (showNotification) {
      achievementNotification.value = {
        achievement: achievement.achievement,
        timestamp: new Date(),
        message: `¡Nuevo logro desbloqueado! ${achievement.achievement.name}`
      }

      // Auto-limpiar notificación después de 5 segundos
      setTimeout(() => {
        achievementNotification.value = null
      }, 5000)
    }
  }

  // Actualizar progreso de logro
  function updateAchievementProgress({ achievementId, progress }) {
    const inProgressIndex = achievements.value.in_progress
      .findIndex(a => a.achievement.id === achievementId)
    
    if (inProgressIndex !== -1) {
      achievements.value.in_progress[inProgressIndex].progress = progress
      achievements.value.in_progress[inProgressIndex].progress_percentage = 
        Math.round((progress / achievements.value.in_progress[inProgressIndex].achievement.condition_value) * 100)
    }
  }

  // Limpiar notificaciones manualmente
  function clearLevelUpNotification() {
    levelUpNotification.value = null
  }

  function clearAchievementNotification() {
    achievementNotification.value = null
  }

  function clearNoteUnlockNotification() {
    noteUnlockNotification.value = null
  }

  // Simular subida de nivel (para pruebas)
  function simulateLevelUp(targetLevel) {
    const currentLevel = userLevel.value.level
    const expForTargetLevel = levelService.calculateExperienceForLevel(targetLevel)

    // Actualizar nivel del usuario con notificación
    updateUserLevel({
      level: targetLevel,
      experience: expForTargetLevel,
      showNotification: true
    })
  }

  // Resetear estado (para logout)
  function resetLevelState() {
    userLevel.value = {
      level: 1,
      current_experience: 0,
      total_experience: 0,
      experience_to_next_level: 150,
      level_progress: 0
    }
    
    experienceStats.value = {
      current_level: 1,
      total_experience: 0,
      current_level_experience: 0,
      experience_needed_for_next: 150,
      progress_percentage: 0,
      experience_today: 0,
      experience_this_week: 0,
      experience_this_month: 0
    }
    
    achievements.value = {
      completed: [],
      in_progress: [],
      available: []
    }
    
    leaderboard.value = {
      users: [],
      current_user_rank: null,
      total_users: 0
    }
    
    loading.value = {
      level: false,
      experience: false,
      achievements: false,
      leaderboard: false
    }
    
    errors.value = {
      level: null,
      experience: null,
      achievements: null,
      leaderboard: null
    }
    
    levelUpNotification.value = null
    achievementNotification.value = null
    noteUnlockNotification.value = null
    
    lastUpdated.value = {
      level: null,
      experience: null,
      achievements: null,
      leaderboard: null
    }
  }

  return {
    // Estado
    userLevel,
    experienceStats,
    achievements,
    leaderboard,
    loading,
    errors,
    levelUpNotification,
    achievementNotification,
    noteUnlockNotification,
    lastUpdated,

    // Getters
    getCurrentLevel,
    getCurrentExperience,
    getTotalExperience,
    getExperienceToNextLevel,
    getLevelProgress,
    getUserRank,
    getCompletedAchievements,
    getInProgressAchievements,
    getAvailableAchievements,
    getAchievementCompletionRate,
    getFormattedExperience,
    getMotivationalMessage,
    isLoadingLevel,
    isLoadingAchievements,
    isLoadingLeaderboard,
    getLevelError,
    getAchievementsError,
    getLeaderboardError,
    getLevelUpNotification,
    getAchievementNotification,
    getNoteUnlockNotification,
    getLeaderboardUsers,
    getCurrentUserRank,
    canAdvanceToLevel1,
    getLevel1Requirement,

    // Acciones
    fetchUserLevel,
    fetchExperienceStats,
    fetchUserAchievements,
    fetchLeaderboard,
    initializeLevelData,
    updateUserLevel,
    addAchievement,
    updateAchievementProgress,
    clearLevelUpNotification,
    clearAchievementNotification,
    clearNoteUnlockNotification,
    simulateLevelUp,
    resetLevelState
  }
})