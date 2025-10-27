<template>
  <div class="auth-footer">
    <div class="footer-content">
      <!-- Center - App info -->
      <div class="footer-section center-section">        
        <span class="app-desc">{{ $t('footer.appDescription') }}</span>
      </div>

      <!-- Right side - Copyright -->
      <div class="footer-section">
        <q-icon name="copyright" size="xs" />
        <span class="copyright">{{ $t('footer.copyright') }}</span>
      </div>
    </div>
  </div>
</template>
  
<script>
export default {
  name: 'AuthFooter',
};
</script>
  
<style scoped>
.auth-footer {
  background: #176F46;
  color: white;
  box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
  position: relative;
}

.footer-content {
  display: flex;
  justify-content: space-around;
  align-items: center;
  padding: 8px 16px;
  min-height: 50px;
}

.footer-section {
  display: flex;
  align-items: center;
  gap: 6px;
}

.center-section {
  gap: 8px;
}

.app-name {
  font-size: 0.875rem;
  font-weight: 600;
}

.separator {
  opacity: 0.6;
  margin: 0 4px;
}

.app-desc {
  font-size: 0.75rem;
  opacity: 0.8;
}

.copyright {
  font-size: 0.75rem;
  opacity: 0.8;
}


/* Responsive adjustments */
@media (max-width: 599px) {
  .footer-content {
    padding: 6px 12px;
  }
  
  /* Hide center content on mobile */
  .center-section {
    display: none;
  }
  
  .copyright {
    font-size: 0.75rem;
  }
}

/* Icon hover effects */
:deep(.q-icon) {
  transition: transform 0.2s ease;
}

:deep(.q-icon:hover) {
  transform: scale(1.1);
}

/* Dark mode support */
.body--dark .auth-footer {
  background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%);
  color: #e0e0e0;
  box-shadow: 0 -2px 8px rgba(255, 255, 255, 0.05);
}

.body--dark .app-name,
.body--dark .app-desc,
.body--dark .copyright {
  color: #e0e0e0;
}

.body--dark .separator {
  color: #b0b0b0;
}

.body--dark .app-desc,
.body--dark .copyright {
  opacity: 0.9;
}

/* Dark mode icon colors */
.body--dark :deep(.q-icon[name="task_alt"]) {
  color: #90caf9;
}

.body--dark :deep(.q-icon[name="copyright"]) {
  color: #b0b0b0;
}
</style>
  