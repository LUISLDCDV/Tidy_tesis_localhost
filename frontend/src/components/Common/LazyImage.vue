<template>
  <div class="lazy-image-container" :class="{ 'loaded': imageLoaded }">
    <div v-if="!imageLoaded && showPlaceholder" class="image-placeholder">
      <q-skeleton
        v-if="skeletonType === 'rectangle'"
        animation="pulse"
        :height="height"
        :width="width"
        class="image-skeleton"
      />
      <q-circular-progress
        v-else-if="skeletonType === 'spinner'"
        indeterminate
        size="50px"
        :thickness="0.2"
        color="primary"
        track-color="grey-3"
      />
      <div v-else class="placeholder-content">
        <q-icon name="image" size="32px" color="grey-5" />
        <div class="text-caption text-grey-5 q-mt-sm">{{ placeholderText }}</div>
      </div>
    </div>

    <img
      v-show="imageLoaded"
      ref="imageRef"
      :src="currentSrc"
      :alt="alt"
      :class="imageClass"
      @load="onLoad"
      @error="onError"
      :loading="nativeLoading ? 'lazy' : undefined"
    />

    <div v-if="error && showError" class="error-state">
      <q-icon name="broken_image" size="32px" color="negative" />
      <div class="text-caption text-negative q-mt-sm">{{ errorText }}</div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';

export default {
  name: 'LazyImage',
  props: {
    src: {
      type: String,
      required: true
    },
    fallbackSrc: {
      type: String,
      default: ''
    },
    alt: {
      type: String,
      default: ''
    },
    width: {
      type: [String, Number],
      default: '100%'
    },
    height: {
      type: [String, Number],
      default: 'auto'
    },
    placeholderText: {
      type: String,
      default: 'Cargando imagen...'
    },
    errorText: {
      type: String,
      default: 'Error al cargar imagen'
    },
    threshold: {
      type: Number,
      default: 0.1
    },
    rootMargin: {
      type: String,
      default: '50px'
    },
    skeletonType: {
      type: String,
      default: 'rectangle',
      validator: (value) => ['rectangle', 'spinner', 'icon'].includes(value)
    },
    imageClass: {
      type: String,
      default: ''
    },
    nativeLoading: {
      type: Boolean,
      default: true
    },
    showPlaceholder: {
      type: Boolean,
      default: true
    },
    showError: {
      type: Boolean,
      default: true
    },
    preload: {
      type: Boolean,
      default: false
    }
  },

  emits: ['load', 'error', 'intersect'],

  setup(props, { emit }) {
    const imageRef = ref(null);
    const imageLoaded = ref(false);
    const error = ref(false);
    const inViewport = ref(false);
    const observer = ref(null);

    const currentSrc = computed(() => {
      if (error.value && props.fallbackSrc) {
        return props.fallbackSrc;
      }
      return props.src;
    });

    const loadImage = () => {
      if (!props.src || imageLoaded.value) return;

      const img = new Image();

      img.onload = () => {
        imageLoaded.value = true;
        error.value = false;
        emit('load', img);
        nextTick(() => {
          if (imageRef.value) {
            imageRef.value.src = currentSrc.value;
          }
        });
      };

      img.onerror = () => {
        error.value = true;
        if (props.fallbackSrc && img.src !== props.fallbackSrc) {
          img.src = props.fallbackSrc;
        } else {
          emit('error', new Error('Failed to load image'));
        }
      };

      img.src = props.src;
    };

    const onLoad = () => {
      imageLoaded.value = true;
      error.value = false;
      emit('load');
    };

    const onError = () => {
      error.value = true;
      emit('error', new Error('Failed to load image'));
    };

    const setupIntersectionObserver = () => {
      if (!window.IntersectionObserver || props.preload) {
        loadImage();
        return;
      }

      observer.value = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              inViewport.value = true;
              emit('intersect', entry);
              loadImage();
              observer.value?.unobserve(entry.target);
            }
          });
        },
        {
          threshold: props.threshold,
          rootMargin: props.rootMargin
        }
      );

      if (imageRef.value?.parentElement) {
        observer.value.observe(imageRef.value.parentElement);
      }
    };

    onMounted(() => {
      if (props.preload) {
        loadImage();
      } else {
        nextTick(() => {
          setupIntersectionObserver();
        });
      }
    });

    onUnmounted(() => {
      observer.value?.disconnect();
    });

    watch(() => props.src, () => {
      imageLoaded.value = false;
      error.value = false;
      if (inViewport.value || props.preload) {
        loadImage();
      }
    });

    return {
      imageRef,
      imageLoaded,
      error,
      currentSrc,
      onLoad,
      onError
    };
  }
};
</script>

<style scoped>
.lazy-image-container {
  position: relative;
  display: inline-block;
  width: 100%;
  overflow: hidden;
}

.image-placeholder,
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100px;
  background: #f5f5f5;
  border-radius: 4px;
  transition: opacity 0.3s ease;
}

.placeholder-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px;
}

.image-skeleton {
  border-radius: 4px;
}

.lazy-image-container img {
  max-width: 100%;
  height: auto;
  display: block;
  transition: opacity 0.3s ease;
}

.lazy-image-container.loaded .image-placeholder {
  display: none;
}

/* Dark mode */
.body--dark .image-placeholder,
.body--dark .error-state {
  background: #2d2d2d;
}

/* Responsive */
@media (max-width: 768px) {
  .image-placeholder,
  .error-state {
    min-height: 80px;
  }

  .placeholder-content {
    padding: 15px;
  }
}

/* Animation effects */
.lazy-image-container img {
  opacity: 0;
  animation: fadeIn 0.3s ease forwards;
}

@keyframes fadeIn {
  to {
    opacity: 1;
  }
}

/* Error state styling */
.error-state {
  border: 2px dashed #ccc;
  background: #fafafa;
}

.body--dark .error-state {
  border-color: #555;
  background: #1a1a1a;
}
</style>