/**
 * Composable para manejar colores personalizados del tema
 * Funcionalidad Premium que permite a los usuarios personalizar colores
 */
import { onMounted } from 'vue';
import { colors } from 'quasar';

/**
 * Hook para aplicar colores personalizados del tema
 * @returns {Object} Funciones para manejar el tema personalizado
 */
export function useThemeColors() {
  // Colores por defecto de Quasar
  const defaultColors = {
    primary: '#1976D2',
    secondary: '#26A69A',
    accent: '#9C27B0',
    positive: '#21BA45',
    negative: '#C10015',
    info: '#31CCEC',
    warning: '#F2C037'
  };

  /**
   * Cargar y aplicar colores guardados
   */
  const loadCustomColors = () => {
    try {
      const savedColors = localStorage.getItem('customThemeColors');
      if (savedColors) {
        const customColors = JSON.parse(savedColors);
        applyColors(customColors);
        return customColors;
      }
    } catch (error) {
      console.error('Error loading custom colors:', error);
    }
    return null;
  };

  /**
   * Aplicar conjunto de colores al tema de Quasar
   * @param {Object} colorSet - Objeto con los colores a aplicar
   */
  const applyColors = (colorSet) => {
    if (!colorSet || typeof colorSet !== 'object') {
      console.warn('Invalid color set provided');
      return;
    }

    Object.keys(colorSet).forEach(colorName => {
      try {
        colors.setBrand(colorName, colorSet[colorName]);
      } catch (error) {
        console.error(`Error setting color ${colorName}:`, error);
      }
    });
  };

  /**
   * Guardar colores personalizados
   * @param {Object} customColors - Colores a guardar
   */
  const saveCustomColors = (customColors) => {
    try {
      localStorage.setItem('customThemeColors', JSON.stringify(customColors));
      applyColors(customColors);
      return true;
    } catch (error) {
      console.error('Error saving custom colors:', error);
      return false;
    }
  };

  /**
   * Restablecer colores a valores por defecto
   */
  const resetToDefault = () => {
    try {
      localStorage.removeItem('customThemeColors');
      applyColors(defaultColors);
      return true;
    } catch (error) {
      console.error('Error resetting colors:', error);
      return false;
    }
  };

  /**
   * Obtener color actual de un tipo específico
   * @param {string} colorType - Tipo de color (primary, secondary, etc.)
   */
  const getCurrentColor = (colorType) => {
    try {
      const savedColors = localStorage.getItem('customThemeColors');
      if (savedColors) {
        const customColors = JSON.parse(savedColors);
        return customColors[colorType] || defaultColors[colorType];
      }
      return defaultColors[colorType];
    } catch (error) {
      console.error('Error getting current color:', error);
      return defaultColors[colorType];
    }
  };

  /**
   * Verificar si hay colores personalizados activos
   */
  const hasCustomColors = () => {
    return localStorage.getItem('customThemeColors') !== null;
  };

  /**
   * Obtener todos los colores actuales
   */
  const getAllColors = () => {
    try {
      const savedColors = localStorage.getItem('customThemeColors');
      if (savedColors) {
        return JSON.parse(savedColors);
      }
      return defaultColors;
    } catch (error) {
      console.error('Error getting all colors:', error);
      return defaultColors;
    }
  };

  // Auto-cargar colores al montar
  onMounted(() => {
    loadCustomColors();
  });

  return {
    loadCustomColors,
    applyColors,
    saveCustomColors,
    resetToDefault,
    getCurrentColor,
    hasCustomColors,
    getAllColors,
    defaultColors
  };
}

/**
 * Aplicar colores personalizados globalmente (para usar en main.js o boot)
 */
export function applyCustomColorsGlobally() {
  try {
    const savedColors = localStorage.getItem('customThemeColors');
    if (savedColors) {
      const customColors = JSON.parse(savedColors);
      Object.keys(customColors).forEach(colorName => {
        colors.setBrand(colorName, customColors[colorName]);
      });
      console.log('✅ Custom theme colors applied:', customColors);
    }
  } catch (error) {
    console.error('Error applying custom colors globally:', error);
  }
}

export default useThemeColors;
