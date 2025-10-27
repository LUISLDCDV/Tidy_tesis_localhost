# Sistema de Optimización de Imágenes

## Descripción

El sistema de optimización de imágenes de Tidy proporciona herramientas avanzadas para mejorar el rendimiento y la experiencia del usuario mediante:

- **Lazy Loading**: Carga diferida de imágenes
- **Responsive Images**: Imágenes adaptativas según el dispositivo
- **Cache inteligente**: Gestión automática de cache
- **Formato optimizado**: Soporte para WebP y otros formatos modernos
- **Placeholders**: Estados de carga y error elegantes

## Componentes

### LazyImage

Componente principal para carga diferida de imágenes con múltiples opciones de configuración.

#### Uso Básico

```vue
<template>
  <LazyImage
    src="/ruta/a/imagen.jpg"
    alt="Descripción de la imagen"
    :width="300"
    :height="200"
  />
</template>
```

#### Props Disponibles

| Prop | Tipo | Defecto | Descripción |
|------|------|---------|-------------|
| `src` | String | - | URL de la imagen (requerido) |
| `fallbackSrc` | String | '' | URL de respaldo si falla la principal |
| `alt` | String | '' | Texto alternativo |
| `width` | String/Number | '100%' | Ancho de la imagen |
| `height` | String/Number | 'auto' | Alto de la imagen |
| `placeholderText` | String | 'Cargando imagen...' | Texto del placeholder |
| `errorText` | String | 'Error al cargar imagen' | Texto de error |
| `threshold` | Number | 0.1 | Umbral para activar carga |
| `rootMargin` | String | '50px' | Margen del viewport |
| `skeletonType` | String | 'rectangle' | Tipo de skeleton ('rectangle', 'spinner', 'icon') |
| `imageClass` | String | '' | Clases CSS adicionales |
| `nativeLoading` | Boolean | true | Usar loading nativo del navegador |
| `showPlaceholder` | Boolean | true | Mostrar placeholder |
| `showError` | Boolean | true | Mostrar estado de error |
| `preload` | Boolean | false | Precargar imagen inmediatamente |

#### Eventos

- `@load`: Se emite cuando la imagen se carga exitosamente
- `@error`: Se emite cuando hay un error al cargar
- `@intersect`: Se emite cuando entra en el viewport

#### Ejemplos Avanzados

**Imagen con fallback y skeleton personalizado:**
```vue
<LazyImage
  src="/imagen-principal.jpg"
  fallback-src="/imagen-fallback.jpg"
  skeleton-type="spinner"
  :threshold="0.3"
  @load="onImageLoad"
  @error="onImageError"
/>
```

**Imagen precargada para contenido crítico:**
```vue
<LazyImage
  src="/hero-image.jpg"
  :preload="true"
  skeleton-type="rectangle"
  :width="1200"
  :height="600"
/>
```

## Composables

### useImageOptimization

Composable que proporciona utilidades avanzadas para la gestión de imágenes.

```javascript
import { useImageOptimization } from '@/composables/useImageOptimization';

export default {
  setup() {
    const {
      preloadImages,
      generateBlurPlaceholder,
      getImageDimensions,
      isWebPSupported
    } = useImageOptimization();

    // Precargar imágenes críticas
    const criticalImages = [
      '/hero-image.jpg',
      '/logo.png'
    ];

    preloadImages(criticalImages);

    return {
      isWebPSupported
    };
  }
};
```

### useResponsiveImages

Composable para gestión de imágenes responsive.

```javascript
import { useResponsiveImages } from '@/composables/useImageOptimization';

export default {
  setup() {
    const {
      currentBreakpoint,
      viewportSize,
      getImageSizesForBreakpoint
    } = useResponsiveImages();

    const imageSizes = getImageSizesForBreakpoint({
      xs: '100vw',
      sm: '100vw',
      md: '50vw',
      lg: '33vw'
    });

    return {
      currentBreakpoint,
      imageSizes
    };
  }
};
```

## Directivas

### v-lazy-src

Directiva para lazy loading simple de imágenes existentes.

```vue
<template>
  <img v-lazy-src="'/ruta/imagen.jpg'" alt="Descripción" />
</template>
```

### v-responsive-img

Directiva para imágenes responsive automáticas.

```vue
<template>
  <img v-responsive-img="'/base-image.jpg'" alt="Imagen responsive" />
</template>
```

## Métodos Globales

### $preloadCriticalImages

Precarga imágenes críticas para mejorar la velocidad de carga inicial.

```javascript
// En cualquier componente
this.$preloadCriticalImages([
  '/hero-image.jpg',
  '/logo.png',
  '/featured-image.jpg'
]);
```

### $generateSrcSet

Genera srcset para imágenes responsive.

```javascript
const srcSet = this.$generateSrcSet('/base-image.jpg', [480, 768, 1024, 1200]);
// Resultado: "/base-image.jpg?w=480 480w, /base-image.jpg?w=768 768w, ..."
```

### $imageCache

Gestión manual del cache de imágenes.

```javascript
// Verificar si una imagen está en cache
const cached = this.$imageCache.get('/imagen.jpg');

// Limpiar cache antiguo
this.$imageCache.clear(300000); // 5 minutos

// Obtener tamaño del cache
const cacheSize = this.$imageCache.size();
```

## Configuración Global

El plugin se puede configurar globalmente en `main.js`:

```javascript
app.use(imageOptimizationPlugin, {
  defaultLazyConfig: {
    threshold: 0.2,
    rootMargin: '100px'
  },
  defaultQuality: {
    high: 95,
    medium: 80,
    low: 60
  },
  cdn: {
    baseUrl: 'https://cdn.ejemplo.com',
    enabled: true
  }
});
```

## Mejores Prácticas

### 1. Usar LazyImage para Contenido No Crítico

```vue
<!-- Para imágenes del contenido principal -->
<LazyImage
  src="/content-image.jpg"
  :width="400"
  :height="300"
  skeleton-type="rectangle"
/>
```

### 2. Precargar Imágenes Críticas

```vue
<script>
export default {
  async mounted() {
    // Precargar imágenes que se van a necesitar pronto
    await this.$preloadCriticalImages([
      '/next-page-hero.jpg',
      '/user-avatar.jpg'
    ]);
  }
};
</script>
```

### 3. Usar Placeholders Apropiados

```vue
<!-- Para listas de imágenes pequeñas -->
<LazyImage
  src="/thumbnail.jpg"
  skeleton-type="rectangle"
  :width="80"
  :height="80"
/>

<!-- Para imágenes de carga lenta -->
<LazyImage
  src="/large-image.jpg"
  skeleton-type="spinner"
/>
```

### 4. Gestionar Estados de Error

```vue
<LazyImage
  src="/imagen-principal.jpg"
  fallback-src="/imagen-por-defecto.jpg"
  @error="handleImageError"
/>

<script>
export default {
  methods: {
    handleImageError(error) {
      console.warn('Error cargando imagen:', error);
      // Reportar error o mostrar notificación
    }
  }
};
</script>
```

## Optimizaciones de Build

El sistema está integrado con Vite para optimizar el build:

```javascript
// vite.config.js
export default {
  build: {
    rollupOptions: {
      output: {
        assetFileNames: (assetInfo) => {
          const extType = assetInfo.name.split('.').at(1);
          if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
            return `images/[name]-[hash][extname]`;
          }
          return `assets/[name]-[hash][extname]`;
        }
      }
    },
    assetsInlineLimit: 4096 // Inline assets < 4KB
  }
};
```

## Consideraciones de Rendimiento

- **Lazy Loading**: Reduce la carga inicial de la página
- **WebP Support**: Automáticamente usa WebP cuando está disponible
- **Cache Management**: Limpia automáticamente el cache cada 5 minutos
- **Intersection Observer**: Usa APIs nativas para mejor rendimiento
- **Asset Inlining**: Convierte assets pequeños en data URLs

## Compatibilidad

- **Navegadores modernos**: Soporte completo
- **Internet Explorer**: Funcionalidad básica (sin lazy loading)
- **Mobile**: Optimizado para dispositivos táctiles
- **PWA**: Compatible con Service Workers