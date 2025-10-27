import { ref, computed, nextTick } from 'vue';

export function useImageOptimization() {
  const imageCache = new Map();
  const loadingImages = new Set();

  /**
   * Genera una URL optimizada para imágenes basada en el tamaño de la pantalla
   * @param {string} originalUrl - URL original de la imagen
   * @param {Object} options - Opciones de optimización
   * @returns {string} URL optimizada
   */
  const getOptimizedImageUrl = (originalUrl, options = {}) => {
    const {
      width = null,
      height = null,
      quality = 80,
      format = 'auto',
      fit = 'cover'
    } = options;

    // Si es una imagen local de assets, no necesita optimización de URL
    if (originalUrl.startsWith('/src/') || originalUrl.startsWith('@/')) {
      return originalUrl;
    }

    // Para imágenes externas, podríamos usar un servicio de optimización
    // Por ahora, devolvemos la URL original
    return originalUrl;
  };

  /**
   * Convierte imágenes a WebP si el navegador lo soporta
   * @param {string} imageUrl - URL de la imagen
   * @returns {Promise<string>} URL de la imagen optimizada
   */
  const convertToWebP = async (imageUrl) => {
    return new Promise((resolve) => {
      // Verificar soporte de WebP
      const webpSupported = checkWebPSupport();

      if (!webpSupported) {
        resolve(imageUrl);
        return;
      }

      // Intentar convertir a WebP (simulado - en producción usarías un servicio real)
      const webpUrl = imageUrl.replace(/\.(jpg|jpeg|png)$/i, '.webp');

      // Verificar si la versión WebP existe
      const img = new Image();
      img.onload = () => resolve(webpUrl);
      img.onerror = () => resolve(imageUrl);
      img.src = webpUrl;
    });
  };

  /**
   * Verifica si el navegador soporta WebP
   * @returns {boolean} True si soporta WebP
   */
  const checkWebPSupport = () => {
    if (typeof window === 'undefined') return false;

    const canvas = document.createElement('canvas');
    canvas.width = 1;
    canvas.height = 1;

    return canvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
  };

  /**
   * Precarga una imagen y la almacena en cache
   * @param {string} imageUrl - URL de la imagen a precargar
   * @returns {Promise<HTMLImageElement>} Promesa con la imagen cargada
   */
  const preloadImage = (imageUrl) => {
    if (imageCache.has(imageUrl)) {
      return Promise.resolve(imageCache.get(imageUrl));
    }

    if (loadingImages.has(imageUrl)) {
      return new Promise((resolve) => {
        const checkCache = () => {
          if (imageCache.has(imageUrl)) {
            resolve(imageCache.get(imageUrl));
          } else {
            setTimeout(checkCache, 50);
          }
        };
        checkCache();
      });
    }

    loadingImages.add(imageUrl);

    return new Promise((resolve, reject) => {
      const img = new Image();

      img.onload = () => {
        imageCache.set(imageUrl, img);
        loadingImages.delete(imageUrl);
        resolve(img);
      };

      img.onerror = () => {
        loadingImages.delete(imageUrl);
        reject(new Error(`Failed to load image: ${imageUrl}`));
      };

      img.src = imageUrl;
    });
  };

  /**
   * Precarga múltiples imágenes
   * @param {string[]} imageUrls - Array de URLs de imágenes
   * @returns {Promise<HTMLImageElement[]>} Promesa con todas las imágenes cargadas
   */
  const preloadImages = (imageUrls) => {
    return Promise.allSettled(
      imageUrls.map(url => preloadImage(url))
    );
  };

  /**
   * Genera un placeholder blur para una imagen
   * @param {string} imageUrl - URL de la imagen
   * @returns {Promise<string>} Data URL del placeholder
   */
  const generateBlurPlaceholder = async (imageUrl) => {
    try {
      const img = await preloadImage(imageUrl);

      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d');

      // Crear una versión muy pequeña y borrosa
      canvas.width = 10;
      canvas.height = 10;

      ctx.filter = 'blur(2px)';
      ctx.drawImage(img, 0, 0, 10, 10);

      return canvas.toDataURL('image/jpeg', 0.1);
    } catch (error) {
      console.warn('Failed to generate blur placeholder:', error);
      return '';
    }
  };

  /**
   * Obtiene las dimensiones de una imagen
   * @param {string} imageUrl - URL de la imagen
   * @returns {Promise<{width: number, height: number}>} Dimensiones
   */
  const getImageDimensions = async (imageUrl) => {
    try {
      const img = await preloadImage(imageUrl);
      return {
        width: img.naturalWidth,
        height: img.naturalHeight
      };
    } catch (error) {
      console.warn('Failed to get image dimensions:', error);
      return { width: 0, height: 0 };
    }
  };

  /**
   * Calcula el tamaño responsive para una imagen
   * @param {Object} viewport - Información del viewport
   * @param {Object} container - Información del contenedor
   * @returns {Object} Dimensiones calculadas
   */
  const calculateResponsiveSize = (viewport, container) => {
    const { width: viewportWidth, height: viewportHeight } = viewport;
    const { maxWidth = viewportWidth, maxHeight = viewportHeight } = container;

    // Calcular tamaño basado en breakpoints
    let targetWidth, targetHeight;

    if (viewportWidth < 576) { // xs
      targetWidth = Math.min(maxWidth, viewportWidth - 32);
    } else if (viewportWidth < 768) { // sm
      targetWidth = Math.min(maxWidth, viewportWidth - 48);
    } else if (viewportWidth < 992) { // md
      targetWidth = Math.min(maxWidth, viewportWidth - 64);
    } else { // lg+
      targetWidth = Math.min(maxWidth, viewportWidth - 80);
    }

    targetHeight = maxHeight;

    return {
      width: Math.round(targetWidth),
      height: Math.round(targetHeight)
    };
  };

  /**
   * Limpia el cache de imágenes
   * @param {number} maxAge - Edad máxima en milisegundos
   */
  const cleanImageCache = (maxAge = 300000) => { // 5 minutos por defecto
    const now = Date.now();
    const entriesToDelete = [];

    imageCache.forEach((img, url) => {
      if (img.loadTime && (now - img.loadTime) > maxAge) {
        entriesToDelete.push(url);
      }
    });

    entriesToDelete.forEach(url => {
      imageCache.delete(url);
    });
  };

  /**
   * Observa elementos para lazy loading
   * @param {Element[]} elements - Elementos a observar
   * @param {Function} callback - Función callback
   * @param {Object} options - Opciones del observer
   * @returns {IntersectionObserver} Observer creado
   */
  const createIntersectionObserver = (elements, callback, options = {}) => {
    const defaultOptions = {
      threshold: 0.1,
      rootMargin: '50px',
      ...options
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          callback(entry);
          observer.unobserve(entry.target);
        }
      });
    }, defaultOptions);

    elements.forEach(element => {
      if (element) observer.observe(element);
    });

    return observer;
  };

  // Composable state
  const isWebPSupported = ref(checkWebPSupport());
  const cacheSize = computed(() => imageCache.size);
  const loadingCount = computed(() => loadingImages.size);

  // Cleanup cache periodically
  if (typeof window !== 'undefined') {
    setInterval(() => {
      cleanImageCache();
    }, 300000); // Cada 5 minutos
  }

  return {
    // Methods
    getOptimizedImageUrl,
    convertToWebP,
    preloadImage,
    preloadImages,
    generateBlurPlaceholder,
    getImageDimensions,
    calculateResponsiveSize,
    cleanImageCache,
    createIntersectionObserver,

    // State
    isWebPSupported,
    cacheSize,
    loadingCount,

    // Utils
    checkWebPSupport
  };
}

// Hook para gestión de imágenes responsive
export function useResponsiveImages() {
  const currentBreakpoint = ref('md');
  const viewportSize = ref({ width: 1024, height: 768 });

  const updateViewport = () => {
    if (typeof window === 'undefined') return;

    viewportSize.value = {
      width: window.innerWidth,
      height: window.innerHeight
    };

    // Determinar breakpoint actual
    const width = window.innerWidth;
    if (width < 576) {
      currentBreakpoint.value = 'xs';
    } else if (width < 768) {
      currentBreakpoint.value = 'sm';
    } else if (width < 992) {
      currentBreakpoint.value = 'md';
    } else if (width < 1200) {
      currentBreakpoint.value = 'lg';
    } else {
      currentBreakpoint.value = 'xl';
    }
  };

  // Update on mount and resize
  if (typeof window !== 'undefined') {
    updateViewport();
    window.addEventListener('resize', updateViewport);
  }

  const getImageSizesForBreakpoint = (sizes = {}) => {
    const defaultSizes = {
      xs: '100vw',
      sm: '100vw',
      md: '50vw',
      lg: '33vw',
      xl: '25vw'
    };

    return { ...defaultSizes, ...sizes };
  };

  return {
    currentBreakpoint,
    viewportSize,
    updateViewport,
    getImageSizesForBreakpoint
  };
}