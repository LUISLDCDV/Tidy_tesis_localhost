import api from './api';

class LevelService {
  /**
   * Obtener información del nivel del usuario actual
   */
  async getUserLevel() {
    try {
      const response = await api.get('/user/level');
      return response.data;
    } catch (error) {
      console.error('Error al obtener nivel del usuario:', error);
      throw error;
    }
  }

  /**
   * Obtener estadísticas de experiencia del usuario
   */
  async getUserExperience() {
    try {
      const response = await api.get('/user/experience');
      return response.data;
    } catch (error) {
      console.error('Error al obtener experiencia del usuario:', error);
      throw error;
    }
  }

  /**
   * Obtener logros del usuario
   */
  async getUserAchievements(filter = 'all') {
    try {
      const response = await api.get('/user/achievements', {
        params: { filter }
      });
      return response.data;
    } catch (error) {
      console.error('Error al obtener logros del usuario:', error);
      throw error;
    }
  }

  /**
   * Obtener tabla de clasificación
   */
  async getLeaderboard(limit = 20, period = 'all_time') {
    try {
      const response = await api.get('/leaderboard', {
        params: { limit, period }
      });
      return response.data;
    } catch (error) {
      console.error('Error al obtener tabla de clasificación:', error);
      throw error;
    }
  }

  /**
   * Calcular experiencia necesaria para el siguiente nivel
   */
  calculateExperienceForLevel(level) {
    return 100 * level + (level * 50);
  }

  /**
   * Calcular experiencia total acumulada hasta llegar a un nivel específico
   */
  calculateTotalExperienceForLevel(targetLevel) {
    let totalExperience = 0;
    for (let level = 1; level <= targetLevel; level++) {
      totalExperience += this.calculateExperienceForLevel(level);
    }
    return totalExperience;
  }

  /**
   * Calcular progreso del nivel actual
   */
  calculateLevelProgress(currentExp, expToNext) {
    if (expToNext === 0) return 100;
    return Math.round((currentExp / expToNext) * 100);
  }

  /**
   * Obtener información del nivel basada en experiencia total
   */
  getLevelFromExperience(totalExperience) {
    let level = 1;
    let totalExpNeeded = 0;
    
    while (totalExpNeeded < totalExperience) {
      const expForThisLevel = this.calculateExperienceForLevel(level);
      totalExpNeeded += expForThisLevel;
      
      if (totalExpNeeded <= totalExperience) {
        level++;
      }
    }
    
    return Math.max(1, level - 1);
  }

  /**
   * Formatear número de experiencia para mostrar
   */
  formatExperience(experience) {
    if (experience >= 1000000) {
      return `${(experience / 1000000).toFixed(1)}M`;
    } else if (experience >= 1000) {
      return `${(experience / 1000).toFixed(1)}K`;
    }
    return experience.toString();
  }

  /**
   * Obtener información del rango/título basado en el nivel
   */
  getRankInfo(level) {
    const ranks = [
      {
        minLevel: 1,
        maxLevel: 4,
        title: 'Novato',
        color: '#64748b',
        icon: '🌱',
        experienceRequired: this.calculateTotalExperienceForLevel(4),
        description: 'Inicio del viaje productivo'
      },
      {
        minLevel: 5,
        maxLevel: 9,
        title: 'Aprendiz',
        color: '#06b6d4',
        icon: '⭐',
        experienceRequired: this.calculateTotalExperienceForLevel(9),
        description: 'Aprendiendo las bases'
      },
      {
        minLevel: 10,
        maxLevel: 19,
        title: 'Competente',
        color: '#10b981',
        icon: '🌟',
        experienceRequired: this.calculateTotalExperienceForLevel(19),
        description: 'Dominando las herramientas'
      },
      {
        minLevel: 20,
        maxLevel: 34,
        title: 'Experto',
        color: '#f59e0b',
        icon: '🏆',
        experienceRequired: this.calculateTotalExperienceForLevel(34),
        description: 'Maestría en productividad'
      },
      {
        minLevel: 35,
        maxLevel: 49,
        title: 'Maestro',
        color: '#8b5cf6',
        icon: '👑',
        experienceRequired: this.calculateTotalExperienceForLevel(49),
        description: 'Liderazgo en organización'
      },
      {
        minLevel: 50,
        maxLevel: 99,
        title: 'Gurú',
        color: '#ef4444',
        icon: '🔥',
        experienceRequired: this.calculateTotalExperienceForLevel(99),
        description: 'Sabiduría máxima'
      },
      {
        minLevel: 100,
        maxLevel: Infinity,
        title: 'Leyenda',
        color: '#f97316',
        icon: '💎',
        experienceRequired: this.calculateTotalExperienceForLevel(100),
        description: 'Status legendario'
      }
    ];

    return ranks.find(rank => level >= rank.minLevel && level <= rank.maxLevel) || ranks[0];
  }

  /**
   * Obtener información de todos los rangos del sistema
   */
  getAllRanks() {
    const ranks = [
      {
        minLevel: 1,
        maxLevel: 4,
        title: 'Novato',
        color: '#64748b',
        icon: '🌱',
        experienceRequired: this.calculateTotalExperienceForLevel(4),
        description: 'Inicio del viaje productivo'
      },
      {
        minLevel: 5,
        maxLevel: 9,
        title: 'Aprendiz',
        color: '#06b6d4',
        icon: '⭐',
        experienceRequired: this.calculateTotalExperienceForLevel(9),
        description: 'Aprendiendo las bases'
      },
      {
        minLevel: 10,
        maxLevel: 19,
        title: 'Competente',
        color: '#10b981',
        icon: '🌟',
        experienceRequired: this.calculateTotalExperienceForLevel(19),
        description: 'Dominando las herramientas'
      },
      {
        minLevel: 20,
        maxLevel: 34,
        title: 'Experto',
        color: '#f59e0b',
        icon: '🏆',
        experienceRequired: this.calculateTotalExperienceForLevel(34),
        description: 'Maestría en productividad'
      },
      {
        minLevel: 35,
        maxLevel: 49,
        title: 'Maestro',
        color: '#8b5cf6',
        icon: '👑',
        experienceRequired: this.calculateTotalExperienceForLevel(49),
        description: 'Liderazgo en organización'
      },
      {
        minLevel: 50,
        maxLevel: 99,
        title: 'Gurú',
        color: '#ef4444',
        icon: '🔥',
        experienceRequired: this.calculateTotalExperienceForLevel(99),
        description: 'Sabiduría máxima'
      },
      {
        minLevel: 100,
        maxLevel: Infinity,
        title: 'Leyenda',
        color: '#f97316',
        icon: '💎',
        experienceRequired: this.calculateTotalExperienceForLevel(100),
        description: 'Status legendario'
      }
    ];

    return ranks;
  }

  /**
   * Obtener color de la barra de progreso basado en el porcentaje
   */
  getProgressBarColor(percentage) {
    if (percentage < 25) return '#ef4444'; // Rojo
    if (percentage < 50) return '#f59e0b'; // Amarillo
    if (percentage < 75) return '#06b6d4'; // Azul
    return '#10b981'; // Verde
  }

  /**
   * Generar mensaje motivacional basado en el progreso
   */
  getMotivationalMessage(levelProgress, level) {
    const messages = {
      low: [
        '¡Cada paso cuenta! Sigue creando elementos.',
        '¡Tu viaje hacia la productividad comienza ahora!',
        '¡Pequeños pasos, grandes logros!'
      ],
      medium: [
        '¡Vas por buen camino! Ya estás a la mitad.',
        '¡Excelente progreso! No te detengas.',
        '¡La constancia es clave del éxito!'
      ],
      high: [
        '¡Casi lo logras! Un poco más al siguiente nivel.',
        '¡Estás muy cerca del siguiente nivel!',
        '¡El próximo nivel te espera!'
      ],
      levelUp: [
        `¡Felicitaciones! Has alcanzado el nivel ${level}`,
        `¡Increíble! Nivel ${level} desbloqueado`,
        `¡Eres imparable! Bienvenido al nivel ${level}`
      ]
    };

    let category = 'low';
    if (levelProgress >= 75) category = 'high';
    else if (levelProgress >= 40) category = 'medium';

    const categoryMessages = messages[category];
    return categoryMessages[Math.floor(Math.random() * categoryMessages.length)];
  }

  /**
   * Formatear tiempo relativo para logros
   */
  formatTimeAgo(date) {
    const now = new Date();
    const achievementDate = new Date(date);
    const diffInSeconds = Math.floor((now - achievementDate) / 1000);

    if (diffInSeconds < 60) return 'Hace un momento';
    if (diffInSeconds < 3600) return `Hace ${Math.floor(diffInSeconds / 60)} min`;
    if (diffInSeconds < 86400) return `Hace ${Math.floor(diffInSeconds / 3600)} h`;
    if (diffInSeconds < 604800) return `Hace ${Math.floor(diffInSeconds / 86400)} días`;
    
    return achievementDate.toLocaleDateString();
  }

  /**
   * Verificar si un logro está cerca de completarse
   */
  isAchievementNearCompletion(progress, total, threshold = 0.8) {
    return (progress / total) >= threshold;
  }

  /**
   * Obtener logros necesarios para subir de nivel
   */
  getRequiredAchievements() {
    return [
      {
        name: 'Primer Paso',
        description: 'Crea tu primer elemento en Tidy',
        icon: '🎯',
        experience: 50,
        category: 'Básico'
      },
      {
        name: 'Productivo',
        description: 'Crea 10 elementos',
        icon: '📝',
        experience: 200,
        category: 'Intermedio'
      },
      {
        name: 'Alcanzador',
        description: 'Completa tu primer objetivo',
        icon: '🎯',
        experience: 100,
        category: 'Básico'
      },
      {
        name: 'Determinado',
        description: 'Completa 5 objetivos',
        icon: '🏅',
        experience: 300,
        category: 'Avanzado'
      },
      {
        name: 'Planificador Maestro',
        description: 'Completa un objetivo con más de 5 metas',
        icon: '🎯',
        experience: 500,
        category: 'Experto'
      },
      {
        name: 'Consistente',
        description: 'Usa la app 7 días seguidos',
        icon: '📅',
        experience: 250,
        category: 'Hábito'
      }
    ];
  }

  /**
   * Calcular experiencia estimada para alcanzar un nivel objetivo
   */
  calculateExperienceNeeded(currentLevel, currentExp, targetLevel) {
    if (targetLevel <= currentLevel) return 0;

    let totalNeeded = 0;
    
    // Experiencia restante para completar el nivel actual
    const currentLevelExp = this.calculateExperienceForLevel(currentLevel);
    totalNeeded += currentLevelExp - currentExp;

    // Experiencia para todos los niveles intermedios
    for (let level = currentLevel + 1; level < targetLevel; level++) {
      totalNeeded += this.calculateExperienceForLevel(level);
    }

    return totalNeeded;
  }

  /**
   * Obtener estadísticas resumidas del usuario
   */
  async getUserStats() {
    try {
      const [levelData, experienceData, achievementsData] = await Promise.all([
        this.getUserLevel(),
        this.getUserExperience(),
        this.getUserAchievements('completed')
      ]);

      const rankInfo = this.getRankInfo(levelData.level_info.level);
      
      return {
        level: levelData.level_info.level,
        experience: experienceData.experience_stats,
        achievements: achievementsData.achievements,
        rank: rankInfo,
        motivationalMessage: this.getMotivationalMessage(
          levelData.level_info.level_progress,
          levelData.level_info.level
        )
      };
    } catch (error) {
      console.error('Error al obtener estadísticas del usuario:', error);
      throw error;
    }
  }
}

// Exportar instancia singleton
const levelService = new LevelService();

export default levelService;