<!-- SearchInput.vue -->
<template>
  <div class="search-container">
    <q-input
      v-model="searchValue"
      :placeholder="placeholder"
      outlined
      dense
      color="primary"
      class="search-input"
      @input="$emit('update:modelValue', $event)"
      @keyup.enter="$emit('search', searchValue)"
      clearable
    >
      <template v-slot:prepend>
        <q-icon name="search" color="grey-6" />
      </template>
      <template v-slot:append>
        <q-btn 
          v-if="searchValue && $q.screen.gt.xs"
          flat
          dense
          icon="search"
          color="primary"
          @click="$emit('search', searchValue)"
          class="search-btn"
        />
      </template>
    </q-input>
  </div>
</template>

<script>
export default {
  name: 'SearchInput',
  props: {
    modelValue: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: 'Buscar...'
    }
  },
  emits: ['update:modelValue', 'search'],
  data() {
    return {
      searchValue: this.modelValue
    }
  },
  watch: {
    modelValue(newVal) {
      this.searchValue = newVal;
    }
  }
}
</script>

<style scoped>
.search-container {
  width: 100%;
  padding: 16px;
}

.search-input {
  width: 100%;
  transition: all 0.2s ease;
}

.search-btn {
  border-radius: 4px;
  transition: all 0.2s ease;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .search-container {
    padding: 12px;
  }
  
  /* Larger input fields for mobile */
  :deep(.q-field--outlined .q-field__control) {
    min-height: 48px;
  }
  
  :deep(.q-field__native) {
    font-size: 16px; /* Prevents zoom on iOS */
  }
}

@media (max-width: 480px) {
  .search-container {
    padding: 8px;
  }
  
  :deep(.q-field--outlined .q-field__control) {
    min-height: 44px;
  }
}

/* Touch optimizations */
@media (hover: none) and (pointer: coarse) {
  .search-btn {
    min-width: 44px;
    min-height: 44px;
    touch-action: manipulation;
  }
  
  .search-btn:active {
    transform: scale(0.95);
  }
}

/* Dark mode support */
.body--dark .search-container {
  background-color: transparent;
}

.body--dark :deep(.q-field--outlined .q-field__control) {
  background-color: #1f2937;
  border-color: #374151;
}

.body--dark :deep(.q-field--outlined.q-field--focused .q-field__control) {
  border-color: var(--q-primary);
}

/* Focus improvements */
:deep(.q-field--outlined.q-field--focused .q-field__control) {
  border-width: 2px;
  box-shadow: 0 0 0 2px rgba(var(--q-primary-rgb), 0.2);
}

/* Accessibility */
:deep(.q-field__native):focus {
  outline: none;
}

/* Animation for clear button */
:deep(.q-field__append .q-icon) {
  transition: transform 0.2s ease;
}

:deep(.q-field__append .q-icon):hover {
  transform: scale(1.1);
}
</style>
  