<template>
  <q-dialog 
    :model-value="true" 
    persistent 
    @update:model-value="$emit('cancel')"
  >
    <q-card class="confirm-modal-card">
      <q-card-section class="text-center">
        <q-icon 
          name="warning" 
          color="warning" 
          size="3rem" 
          class="q-mb-md"
        />
        <div class="text-h6 q-mb-sm">Confirmar acción</div>
        <p class="text-body2 text-grey-7">{{ message }}</p>
      </q-card-section>

      <q-separator />

      <q-card-actions class="justify-end q-pa-md">
        <q-btn
          flat
          :label="cancelText || 'Cancelar'"
          color="grey-7"
          @click="$emit('cancel')"
          class="q-mr-sm"
        />
        <q-btn
          unelevated
          :label="confirmText || 'Confirmar'"
          :color="confirmColor || 'negative'"
          @click="$emit('confirm')"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
export default {
  props: {
    message: {
      type: String,
      required: true
    },
    confirmText: {
      type: String,
      default: null
    },
    cancelText: {
      type: String,
      default: null
    },
    confirmColor: {
      type: String,
      default: 'negative'
    }
  },
  emits: ['confirm', 'cancel']
};
</script>

<style scoped>
.confirm-modal-card {
  min-width: 320px;
  max-width: 400px;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
}

.mobile-confirm-modal {
  min-width: 100vw !important;
  max-width: 100vw !important;
  min-height: 100vh !important;
  border-radius: 0 !important;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.modal-content {
  padding: 24px;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.warning-icon {
  transition: all 0.2s ease;
}

.modal-title {
  font-weight: 600;
  color: #1a202c;
  line-height: 1.3;
}

.modal-message {
  font-size: 1rem;
  line-height: 1.5;
  max-width: 350px;
  margin: 0 auto;
}

.modal-actions {
  padding: 16px 24px;
  justify-content: flex-end;
  gap: 12px;
}

.mobile-actions {
  padding: 20px 24px 24px;
  flex-direction: column;
  gap: 12px;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.cancel-btn,
.confirm-btn {
  min-height: 44px;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.mobile-btn {
  width: 100%;
  min-height: 48px;
  font-size: 1rem;
}

/* Mobile responsive */
@media (max-width: 599px) {
  .confirm-modal-card {
    min-width: 280px;
    max-width: 90vw;
    margin: 16px;
  }
  
  .modal-content {
    padding: 20px;
  }
  
  .modal-title {
    font-size: 1.25rem;
  }
  
  .modal-message {
    font-size: 0.95rem;
  }
  
  .modal-actions {
    padding: 16px 20px;
  }
  
  .mobile-actions {
    padding: 16px 20px 20px;
  }
}

@media (max-width: 480px) {
  .confirm-modal-card {
    min-width: 260px;
    max-width: 95vw;
    margin: 8px;
  }
  
  .modal-content {
    padding: 16px;
  }
  
  .modal-title {
    font-size: 1.1rem;
  }
  
  .modal-message {
    font-size: 0.9rem;
  }
  
  .warning-icon {
    margin-bottom: 16px !important;
  }
  
  .modal-actions {
    padding: 12px 16px;
  }
  
  .mobile-actions {
    padding: 16px;
    gap: 10px;
  }
}

/* Touch device optimizations */
@media (pointer: coarse) {
  .cancel-btn:active,
  .confirm-btn:active {
    transform: scale(0.98);
  }
  
  .mobile-btn {
    min-height: 52px;
  }
}

/* Animation */
:deep(.q-dialog) {
  .q-card {
    animation: scaleIn 0.25s ease-out;
  }
}

@media (max-width: 599px) {
  :deep(.q-dialog) {
    .q-card {
      animation: slideUp 0.3s ease-out;
    }
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Enhanced backdrop */
:deep(.q-dialog__backdrop) {
  backdrop-filter: blur(3px);
  background: rgba(0, 0, 0, 0.6);
}

/* Dark mode support */
.body--dark .modal-title {
  color: #f7fafc;
}

.body--dark .mobile-actions {
  border-color: rgba(255, 255, 255, 0.1);
}

.body--dark .confirm-modal-card {
  background: #1f2937;
}

/* Accessibility improvements */
.confirm-btn:focus-visible,
.cancel-btn:focus-visible {
  outline: 2px solid var(--q-primary);
  outline-offset: 2px;
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .confirm-modal-card {
    border: 2px solid currentColor;
  }
  
  .modal-actions {
    border-top: 2px solid currentColor;
  }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .confirm-modal-card,
  .warning-icon,
  .cancel-btn,
  .confirm-btn {
    transition: none;
  }
  
  :deep(.q-dialog) {
    .q-card {
      animation: none;
    }
  }
}
</style>