<template>
  <div class="guest-footer">
    <div class="footer-content">
      <q-icon name="favorite" color="red" size="sm" />
      <span class="footer-message">{{ footerMessage }}</span>
      <q-icon name="copyright" size="xs" />
      <span class="footer-year">{{ t('footer.copyright') }} Tidy</span>
    </div>
  </div>
</template>
  
<script>
import { ref, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';

export default {
  name: 'GuestFooter',

  setup() {
    const { t } = useI18n();
    const route = useRoute();
    const footerMessage = ref('');

    const changeFooterMessage = (routeName) => {
      const messages = {
        'home': t('auth.welcomeMessage'),
        'login': t('auth.loginPrompt'),
        'register': t('auth.registerPrompt'),
        'about': t('auth.aboutPrompt'),
        'contact': t('auth.contactPrompt')
      };

      footerMessage.value = messages[routeName] || t('auth.welcomeMessage');
    };

    onMounted(() => {
      changeFooterMessage(route.name);
    });

    watch(() => route.name, (newRouteName) => {
      changeFooterMessage(newRouteName);
    });

    return {
      footerMessage,
      t
    };
  }
};
</script>
  
<style scoped>
.guest-footer {
  background: #424242;
  color: white;
  box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
  position: relative;
}

.footer-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 16px;
  min-height: 50px;
}

.footer-message {
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.footer-year {
  font-size: 0.75rem;
  opacity: 0.8;
}

/* Responsive text sizing */
@media (max-width: 599px) {
  .footer-content {
    padding: 8px 12px;
    gap: 6px;
  }
  
  .footer-message {
    font-size: 0.75rem;
  }
  
  .footer-year {
    font-size: 0.6875rem;
  }
}

/* Icon styling */
:deep(.q-icon) {
  transition: transform 0.2s ease;
}

:deep(.q-icon:hover) {
  transform: scale(1.1);
}

/* Dark mode support */
.body--dark .guest-footer {
  background: #1a1a1a;
  color: #e0e0e0;
  box-shadow: 0 -2px 8px rgba(255, 255, 255, 0.05);
}

.body--dark .footer-message,
.body--dark .footer-year {
  color: #e0e0e0;
}

.body--dark .footer-year {
  opacity: 0.9;
}

/* Dark mode icon colors */
.body--dark :deep(.q-icon[name="favorite"]) {
  color: #e57373;
}

.body--dark :deep(.q-icon[name="copyright"]) {
  color: #b0b0b0;
}
</style>
  