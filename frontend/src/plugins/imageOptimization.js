import LazyImage from '@/components/Common/LazyImage.vue';

export default {
  install(app) {
    // Registrar el componente LazyImage globalmente
    app.component('LazyImage', LazyImage);

    // Configurar opciones globales para optimización de imágenes
    app.config.globalProperties.$imageOptimization = {
      // Configuración por defecto para lazy loading
      defaultLazyConfig: {
        threshold: 0.1,
        rootMargin: '50px',
        showPlaceholder: true,
        skeletonType: 'rectangle'
      },

      // Configuración de calidad por defecto
      defaultQuality: {
        high: 95,
        medium: 80,
        low: 60
      },

      // Breakpoints para imágenes responsive
      breakpoints: {
        xs: 576,
        sm: 768,
        md: 992,
        lg: 1200,
        xl: 1400
      },

      // Formatos soportados por orden de preferencia
      supportedFormats: ['webp', 'avif', 'jpg', 'png', 'gif'],

      // CDN configuration (si se usa)
      cdn: {
        baseUrl: '',
        enabled: false
      }
    };

    // Método global para generar srcset responsive
    app.config.globalProperties.$generateSrcSet = function(baseUrl, sizes = [480, 768, 1024, 1200]) {
      return sizes.map(size => `${baseUrl}?w=${size} ${size}w`).join(', ');
    };

    // Método global para detectar soporte WebP
    app.config.globalProperties.$supportsWebP = function() {
      if (typeof window === 'undefined') return false;

      const canvas = document.createElement('canvas');
      canvas.width = 1;
      canvas.height = 1;

      return canvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
    };

    // Directiva personalizada para lazy loading de imágenes
    app.directive('lazy-src', {
      mounted(el, binding) {
        const imageUrl = binding.value;

        if (!imageUrl) return;

        const options = binding.modifiers || {};
        const threshold = options.threshold || 0.1;
        const rootMargin = options.rootMargin || '50px';

        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              const img = entry.target;

              // Crear nueva imagen para precargar
              const newImg = new Image();

              newImg.onload = function() {
                img.src = imageUrl;
                img.classList.add('loaded');
              };

              newImg.onerror = function() {
                img.classList.add('error');
                // Emitir evento de error si es necesario
                img.dispatchEvent(new CustomEvent('lazy-error', {
                  detail: { url: imageUrl }
                }));
              };

              newImg.src = imageUrl;
              observer.unobserve(img);
            }
          });
        }, {
          threshold,
          rootMargin
        });

        observer.observe(el);

        // Guardar el observer en el elemento para limpieza
        el._lazyObserver = observer;
      },

      unmounted(el) {
        if (el._lazyObserver) {
          el._lazyObserver.disconnect();
          delete el._lazyObserver;
        }
      }
    });

    // Directiva para imágenes responsive automáticas
    app.directive('responsive-img', {
      mounted(el, binding) {
        const baseUrl = binding.value;

        if (!baseUrl || el.tagName !== 'IMG') return;

        const updateSrc = () => {
          const containerWidth = el.parentElement?.offsetWidth || window.innerWidth;
          const devicePixelRatio = window.devicePixelRatio || 1;
          const targetWidth = Math.ceil(containerWidth * devicePixelRatio);

          // Generar URL optimizada basada en el ancho del contenedor
          let optimizedUrl = baseUrl;

          // Si tienes un servicio de redimensionamiento, úsalo aquí
          if (app.config.globalProperties.$imageOptimization.cdn.enabled) {
            const cdnBase = app.config.globalProperties.$imageOptimization.cdn.baseUrl;
            optimizedUrl = `${cdnBase}${baseUrl}?w=${targetWidth}&q=80&f=auto`;
          }

          el.src = optimizedUrl;
        };

        // Actualizar al montar
        updateSrc();

        // Actualizar en resize (debounced)
        let resizeTimeout;
        const handleResize = () => {
          clearTimeout(resizeTimeout);
          resizeTimeout = setTimeout(updateSrc, 100);
        };

        window.addEventListener('resize', handleResize);

        // Guardar el handler para limpieza
        el._resizeHandler = handleResize;
      },

      unmounted(el) {
        if (el._resizeHandler) {
          window.removeEventListener('resize', el._resizeHandler);
          delete el._resizeHandler;
        }
      }
    });

    // Método global para precargar imágenes críticas
    app.config.globalProperties.$preloadCriticalImages = function(imageUrls) {
      if (!Array.isArray(imageUrls)) {
        imageUrls = [imageUrls];
      }

      return Promise.allSettled(
        imageUrls.map(url => {
          return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = () => reject(new Error(`Failed to preload: ${url}`));
            img.src = url;
          });
        })
      );
    };

    // Servicio para gestión de cache de imágenes
    const imageCache = new Map();

    app.config.globalProperties.$imageCache = {
      get(url) {
        return imageCache.get(url);
      },

      set(url, data) {
        imageCache.set(url, {
          data,
          timestamp: Date.now()
        });
      },

      clear(maxAge = 300000) { // 5 minutos por defecto
        const now = Date.now();
        for (const [url, item] of imageCache.entries()) {
          if (now - item.timestamp > maxAge) {
            imageCache.delete(url);
          }
        }
      },

      size() {
        return imageCache.size;
      }
    };

    // Auto-limpiar cache cada 5 minutos
    if (typeof window !== 'undefined') {
      setInterval(() => {
        app.config.globalProperties.$imageCache.clear();
      }, 300000);
    }
  }
};