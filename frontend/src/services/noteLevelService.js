import levelService from './levelService';

class NoteLevelService {
  /**
   * Configuración de restricciones por nivel para tipos de notas
   * Cada tipo de nota requiere un nivel mínimo para ser desbloqueado
   */
  noteTypeRestrictions = {
    1: { level: 1, name: 'Nota Normal', description: 'Nota básica de texto', is_premium: false },
    2: { level: 3, name: 'Comidas Semanales', description: 'Planificación de comidas', is_premium: false },
    3: { level: 5, name: 'Gestión de Claves', description: 'Almacenamiento seguro de contraseñas', is_premium: false },
    4: { level: 8, name: 'Código', description: 'Notas con sintaxis de código', is_premium: false },
    5: { level: 2, name: 'Compras Supermercado', description: 'Lista de compras inteligente', is_premium: false },
    6: { level: 6, name: 'Gastos Mensuales', description: 'Control de presupuesto mensual', is_premium: false },
    7: { level: 4, name: 'Puntos de Juego', description: 'Sistema de puntuación por equipos', is_premium: false },
    8: { level: 10, name: 'Control de Presupuesto', description: 'Gestión avanzada de finanzas', is_premium: false },
    9: { level: 7, name: 'Gestión de Tiempo', description: 'Técnicas de time boxing', is_premium: false },
    10: { level: 9, name: 'Recetas', description: 'Organización de recetas de cocina', is_premium: false },
    11: { level: 12, name: 'Medidas de Construcción', description: 'Cálculos para proyectos de construcción', is_premium: false },
    12: { level: 5, name: 'Recomendaciones', description: 'Sistema de recomendaciones personalizadas', is_premium: false },
    13: { level: 11, name: 'Pedidos Grupales', description: 'Coordinación de pedidos en grupo', is_premium: false },
    14: { level: 1, name: 'Planificación de Viajes 👑', description: 'Organización completa de viajes (Premium)', is_premium: true },
    15: { level: 1, name: 'Dibujo Digital 👑', description: 'Notas con capacidades de dibujo (Premium)', is_premium: true },
    16: { level: 1, name: 'Diagramas Avanzados 👑', description: 'Creación de diagramas profesionales (Premium)', is_premium: true }
  };

  /**
   * Verificar si un usuario puede acceder a un tipo de nota específico
   * @param {number} noteTypeId - ID del tipo de nota
   * @param {number} userLevel - Nivel actual del usuario
   * @param {boolean} isPremium - Si el usuario es premium
   * @returns {boolean} - true si puede acceder, false si no
   */
  canAccessNoteType(noteTypeId, userLevel, isPremium = false) {
    const restriction = this.noteTypeRestrictions[noteTypeId];

    if (!restriction) {
      console.warn(`Tipo de nota ${noteTypeId} no encontrado en las restricciones`);
      return false;
    }

    // Verificar nivel
    const hasLevel = userLevel >= restriction.level;

    // Si requiere premium, verificar que el usuario sea premium
    if (restriction.is_premium && !isPremium) {
      return false;
    }

    return hasLevel;
  }

  /**
   * Obtener información de restricción para un tipo de nota
   * @param {number} noteTypeId - ID del tipo de nota
   * @returns {object|null} - Información de la restricción o null si no existe
   */
  getNoteTypeRestriction(noteTypeId) {
    return this.noteTypeRestrictions[noteTypeId] || null;
  }

  /**
   * Obtener todos los tipos de notas disponibles para un nivel específico
   * @param {number} userLevel - Nivel del usuario
   * @param {boolean} isPremium - Si el usuario es premium
   * @returns {array} - Array de tipos de notas disponibles
   */
  getAvailableNoteTypes(userLevel, isPremium = false) {
    return Object.entries(this.noteTypeRestrictions)
      .filter(([typeId, restriction]) => {
        const hasLevel = userLevel >= restriction.level;
        const canAccess = !restriction.is_premium || isPremium;
        return hasLevel && canAccess;
      })
      .map(([typeId, restriction]) => ({
        id: parseInt(typeId),
        ...restriction,
        unlocked: true
      }));
  }

  /**
   * Obtener tipos de notas bloqueados para un nivel específico
   * @param {number} userLevel - Nivel del usuario
   * @param {boolean} isPremium - Si el usuario es premium
   * @returns {array} - Array de tipos de notas bloqueados
   */
  getLockedNoteTypes(userLevel, isPremium = false) {
    return Object.entries(this.noteTypeRestrictions)
      .filter(([typeId, restriction]) => {
        const hasLevel = userLevel >= restriction.level;
        const needsPremium = restriction.is_premium && !isPremium;
        // Bloquear si no tiene nivel O si necesita premium pero no lo tiene
        return !hasLevel || needsPremium;
      })
      .map(([typeId, restriction]) => ({
        id: parseInt(typeId),
        ...restriction,
        unlocked: false,
        levelsToUnlock: restriction.level - userLevel,
        requiresPremium: restriction.is_premium && !isPremium,
        hasLevel: userLevel >= restriction.level
      }))
      .sort((a, b) => a.level - b.level); // Ordenar por nivel requerido
  }

  /**
   * Obtener el próximo tipo de nota que se desbloqueará
   * @param {number} userLevel - Nivel del usuario
   * @returns {object|null} - Próximo tipo de nota a desbloquear o null
   */
  getNextUnlockableNoteType(userLevel) {
    const locked = this.getLockedNoteTypes(userLevel);
    return locked.length > 0 ? locked[0] : null;
  }

  /**
   * Verificar si un tipo de nota se desbloqueó recientemente
   * @param {number} noteTypeId - ID del tipo de nota
   * @param {number} previousLevel - Nivel anterior del usuario
   * @param {number} currentLevel - Nivel actual del usuario
   * @returns {boolean} - true si se desbloqueó en esta subida de nivel
   */
  wasRecentlyUnlocked(noteTypeId, previousLevel, currentLevel) {
    const restriction = this.noteTypeRestrictions[noteTypeId];
    
    if (!restriction) return false;

    return previousLevel < restriction.level && currentLevel >= restriction.level;
  }

  /**
   * Obtener tipos de notas desbloqueados en una subida de nivel
   * @param {number} previousLevel - Nivel anterior
   * @param {number} currentLevel - Nivel actual
   * @returns {array} - Array de tipos de notas recién desbloqueados
   */
  getNewlyUnlockedNoteTypes(previousLevel, currentLevel) {
    return Object.entries(this.noteTypeRestrictions)
      .filter(([typeId, restriction]) => 
        previousLevel < restriction.level && currentLevel >= restriction.level
      )
      .map(([typeId, restriction]) => ({
        id: parseInt(typeId),
        ...restriction,
        unlocked: true,
        isNew: true
      }));
  }

  /**
   * Generar mensaje de error personalizado para tipo de nota bloqueado
   * @param {number} noteTypeId - ID del tipo de nota
   * @param {number} userLevel - Nivel actual del usuario
   * @returns {string} - Mensaje de error personalizado
   */
  getAccessDeniedMessage(noteTypeId, userLevel) {
    const restriction = this.noteTypeRestrictions[noteTypeId];
    
    if (!restriction) {
      return 'Tipo de nota no válido.';
    }

    const levelsNeeded = restriction.level - userLevel;
    
    return `Necesitas nivel ${restriction.level} para crear "${restriction.name}". Te faltan ${levelsNeeded} nivel(es). ¡Sigue usando la app para subir de nivel!`;
  }

  /**
   * Obtener progreso de desbloqueo para UI
   * @param {number} userLevel - Nivel actual del usuario
   * @returns {object} - Información de progreso de desbloqueo
   */
  getUnlockProgress(userLevel) {
    const total = Object.keys(this.noteTypeRestrictions).length;
    const unlocked = this.getAvailableNoteTypes(userLevel).length;
    const percentage = Math.round((unlocked / total) * 100);

    return {
      total,
      unlocked,
      locked: total - unlocked,
      percentage,
      nextUnlock: this.getNextUnlockableNoteType(userLevel)
    };
  }

  /**
   * Validar creación de nota con verificación de nivel
   * @param {number} noteTypeId - ID del tipo de nota
   * @param {number} userLevel - Nivel actual del usuario
   * @returns {object} - Resultado de la validación
   */
  validateNoteCreation(noteTypeId, userLevel) {
    const canAccess = this.canAccessNoteType(noteTypeId, userLevel);
    const restriction = this.getNoteTypeRestriction(noteTypeId);

    return {
      allowed: canAccess,
      restriction,
      message: canAccess 
        ? `Puedes crear "${restriction?.name}"`
        : this.getAccessDeniedMessage(noteTypeId, userLevel)
    };
  }

  /**
   * Obtener recomendaciones de experiencia para desbloquear próximas notas
   * @param {number} userLevel - Nivel actual del usuario
   * @returns {array} - Array de recomendaciones
   */
  getUnlockRecommendations(userLevel) {
    const nextUnlock = this.getNextUnlockableNoteType(userLevel);
    
    if (!nextUnlock) {
      return [
        {
          message: '¡Felicitaciones! Has desbloqueado todos los tipos de notas.',
          type: 'success'
        }
      ];
    }

    const levelsNeeded = nextUnlock.level - userLevel;
    const experienceNeeded = levelService.calculateExperienceNeeded(
      userLevel, 
      0, // Asumimos experiencia actual en 0 para cálculo simplificado
      nextUnlock.level
    );

    return [
      {
        message: `Próximo desbloqueo: "${nextUnlock.name}" en nivel ${nextUnlock.level}`,
        levelsNeeded,
        experienceNeeded,
        suggestions: levelService.getExperienceSuggestions().slice(0, 3),
        type: 'info'
      }
    ];
  }

  /**
   * Estadísticas de progreso de desbloqueo por categorías
   * @param {number} userLevel - Nivel actual del usuario
   * @returns {object} - Estadísticas categorizadas
   */
  getCategorizedProgress(userLevel) {
    const categories = {
      basic: [1, 2, 5], // Notas básicas
      productivity: [3, 6, 8, 9], // Productividad y organización
      creative: [4, 10, 15, 16], // Creatividad y contenido
      social: [7, 13], // Colaboración y social
      advanced: [11, 14] // Funciones avanzadas
    };

    const result = {};

    Object.entries(categories).forEach(([category, noteTypes]) => {
      const total = noteTypes.length;
      const unlocked = noteTypes.filter(typeId => 
        this.canAccessNoteType(typeId, userLevel)
      ).length;

      result[category] = {
        total,
        unlocked,
        percentage: Math.round((unlocked / total) * 100),
        noteTypes: noteTypes.map(typeId => ({
          id: typeId,
          ...this.noteTypeRestrictions[typeId],
          unlocked: this.canAccessNoteType(typeId, userLevel)
        }))
      };
    });

    return result;
  }
}

// Exportar instancia singleton
const noteLevelService = new NoteLevelService();

export default noteLevelService;