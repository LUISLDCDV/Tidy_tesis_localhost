<template>
  <q-page class="objectives-page">
    <div class="objectives-container">
      <!-- Main Content -->
      <MainContent />
    </div>
  </q-page>
</template>

<script>
import MainContent from '@/components/Elements/Objetives/Main/MainContent.vue';

export default {
  name: 'ObjetivesList',
  components: {
    MainContent,
  },
};
</script>

<style scoped>
.objectives-page {
  min-height: 100vh;
  background-color: #f9fafb;
}

.objectives-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 20px 16px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .objectives-container {
    padding: 12px 8px;
  }
}

@media (max-width: 480px) {
  .objectives-container {
    padding: 8px 4px;
  }
}

@media (min-width: 640px) {
  .objectives-container {
    padding: 24px 20px;
  }
}

@media (min-width: 1024px) {
  .objectives-container {
    padding: 32px;
  }
}

/* Dark mode support */
.body--dark .objectives-page {
  background-color: #121212;
}
</style>
