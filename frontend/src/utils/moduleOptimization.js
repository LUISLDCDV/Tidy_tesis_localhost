/**
 * Module optimization utilities for dynamic imports and lazy loading
 */

// Cache para módulos cargados dinámicamente
const moduleCache = new Map();

/**
 * Lazy load de componentes con cache
 * @param {Function} importFn - Función de import dinámico
 * @param {string} key - Clave única para el módulo
 * @returns {Promise} Promesa del componente
 */
export const lazyLoadComponent = (importFn, key) => {
  if (moduleCache.has(key)) {
    return Promise.resolve(moduleCache.get(key));
  }

  const promise = importFn().then(module => {
    const component = module.default || module;
    moduleCache.set(key, component);
    return component;
  });

  return promise;
};

/**
 * Preload de módulos críticos
 * @param {Array} modules - Array de objetos {importFn, key}
 */
export const preloadCriticalModules = (modules) => {
  const promises = modules.map(({ importFn, key }) => {
    if (!moduleCache.has(key)) {
      return lazyLoadComponent(importFn, key);
    }
    return Promise.resolve(moduleCache.get(key));
  });

  return Promise.allSettled(promises);
};

/**
 * Lazy load de stores Pinia
 * @param {Function} storeFactory - Factory del store
 * @param {string} storeName - Nombre del store
 */
export const lazyLoadStore = (storeFactory, storeName) => {
  const key = `store_${storeName}`;

  if (moduleCache.has(key)) {
    return moduleCache.get(key);
  }

  const store = storeFactory();
  moduleCache.set(key, store);
  return store;
};

/**
 * Dynamic import con retry en caso de fallo
 * @param {Function} importFn - Función de import
 * @param {number} retries - Número de reintentos
 * @param {number} delay - Delay entre reintentos
 */
export const dynamicImportWithRetry = async (importFn, retries = 3, delay = 1000) => {
  for (let i = 0; i < retries; i++) {
    try {
      return await importFn();
    } catch (error) {
      if (i === retries - 1) {
        console.error('Failed to load module after retries:', error);
        throw error;
      }

      console.warn(`Module load attempt ${i + 1} failed, retrying...`);
      await new Promise(resolve => setTimeout(resolve, delay));
    }
  }
};

/**
 * Prefetch de módulos basado en navegación
 * @param {Object} router - Router de Vue
 */
export const setupNavigationPrefetch = (router) => {
  const prefetchedRoutes = new Set();

  router.beforeEach((to, from, next) => {
    // Prefetch de rutas relacionadas
    const relatedRoutes = getRelatedRoutes(to.path);

    relatedRoutes.forEach(route => {
      if (!prefetchedRoutes.has(route) && route.component) {
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = route.component;
        document.head.appendChild(link);
        prefetchedRoutes.add(route);
      }
    });

    next();
  });
};

/**
 * Obtener rutas relacionadas basado en la ruta actual
 * @param {string} currentPath - Ruta actual
 * @returns {Array} Array de rutas relacionadas
 */
const getRelatedRoutes = (currentPath) => {
  const routeMap = {
    '/home': ['/notes', '/objectives', '/alarms'],
    '/notes': ['/home', '/objectives'],
    '/objectives': ['/home', '/notes', '/calendars'],
    '/alarms': ['/home', '/calendars'],
    '/calendars': ['/objectives', '/alarms'],
    '/profile': ['/settings'],
    '/settings': ['/profile']
  };

  return routeMap[currentPath] || [];
};

/**
 * Optimización de imports de utilidades
 */
export const OptimizedUtils = {
  // Lazy load de moment/date-fns solo cuando se necesite
  async getDateUtils() {
    const key = 'date_utils';
    if (moduleCache.has(key)) {
      return moduleCache.get(key);
    }

    // Usar la API nativa cuando sea posible
    const dateUtils = {
      format: (date, format) => {
        return new Intl.DateTimeFormat('es-ES', {
          year: 'numeric',
          month: '2-digit',
          day: '2-digit',
          hour: '2-digit',
          minute: '2-digit'
        }).format(new Date(date));
      },

      addDays: (date, days) => {
        const result = new Date(date);
        result.setDate(result.getDate() + days);
        return result;
      },

      isToday: (date) => {
        const today = new Date();
        const targetDate = new Date(date);
        return today.toDateString() === targetDate.toDateString();
      }
    };

    moduleCache.set(key, dateUtils);
    return dateUtils;
  },

  // Lazy load de utilidades de validación
  async getValidationUtils() {
    const key = 'validation_utils';
    if (moduleCache.has(key)) {
      return moduleCache.get(key);
    }

    const validationUtils = {
      email: (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email),
      required: (value) => value !== null && value !== undefined && value !== '',
      minLength: (value, min) => value && value.length >= min,
      maxLength: (value, max) => value && value.length <= max,
      phone: (phone) => /^[\+]?[1-9][\d]{0,15}$/.test(phone)
    };

    moduleCache.set(key, validationUtils);
    return validationUtils;
  },

  // Lazy load de utilidades de formato
  async getFormatUtils() {
    const key = 'format_utils';
    if (moduleCache.has(key)) {
      return moduleCache.get(key);
    }

    const formatUtils = {
      currency: (amount, currency = 'EUR') => {
        return new Intl.NumberFormat('es-ES', {
          style: 'currency',
          currency
        }).format(amount);
      },

      number: (num, decimals = 2) => {
        return new Intl.NumberFormat('es-ES', {
          minimumFractionDigits: decimals,
          maximumFractionDigits: decimals
        }).format(num);
      },

      percentage: (value) => {
        return new Intl.NumberFormat('es-ES', {
          style: 'percent',
          minimumFractionDigits: 1,
          maximumFractionDigits: 1
        }).format(value / 100);
      }
    };

    moduleCache.set(key, formatUtils);
    return formatUtils;
  }
};

/**
 * Cleanup de cache de módulos (útil para HMR en desarrollo)
 */
export const clearModuleCache = () => {
  moduleCache.clear();
};

/**
 * Obtener estadísticas del cache de módulos
 */
export const getModuleCacheStats = () => {
  return {
    size: moduleCache.size,
    keys: Array.from(moduleCache.keys())
  };
};

/**
 * Tree-shaking helper: marca funciones como no utilizadas
 * en builds de producción
 */
export const markAsUnused = (fn) => {
  if (process.env.NODE_ENV === 'development') {
    return fn;
  }
  // En producción, retorna una función vacía para tree-shaking
  return () => {};
};

/**
 * Configuración de code splitting inteligente
 */
export const CodeSplittingConfig = {
  // Rutas que deben cargarse inmediatamente
  criticalRoutes: ['/home', '/login'],

  // Rutas que pueden cargarse de forma diferida
  lazyRoutes: ['/settings', '/profile', '/analytics'],

  // Configuración de prefetch por ruta
  prefetchConfig: {
    '/home': {
      prefetch: ['/notes', '/objectives'],
      priority: 'high'
    },
    '/notes': {
      prefetch: ['/objectives'],
      priority: 'medium'
    }
  },

  // Configuración de chunks por funcionalidad
  chunkConfig: {
    notes: {
      include: ['notes', 'editor', 'markdown'],
      priority: 'medium'
    },
    maps: {
      include: ['leaflet', 'geolocation'],
      priority: 'low'
    },
    charts: {
      include: ['chart', 'analytics'],
      priority: 'low'
    }
  }
};

export default {
  lazyLoadComponent,
  preloadCriticalModules,
  lazyLoadStore,
  dynamicImportWithRetry,
  setupNavigationPrefetch,
  OptimizedUtils,
  clearModuleCache,
  getModuleCacheStats,
  markAsUnused,
  CodeSplittingConfig
};