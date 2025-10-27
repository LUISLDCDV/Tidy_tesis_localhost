<template>
  <q-card class="achievements-modal">
    <q-card-section class="row items-center q-pb-none">
      <div class="text-h6">Logros</div>
      <q-space />
      <q-btn icon="close" flat round dense @click="$emit('close')" />
    </q-card-section>

    <q-separator />

    <q-card-section>
      <q-tabs v-model="activeTab" align="justify" class="q-mb-md">
        <q-tab name="completed" label="Completados" />
        <q-tab name="progress" label="En Progreso" />
        <q-tab name="available" label="Disponibles" />
      </q-tabs>

      <q-tab-panels v-model="activeTab" style="min-height: 400px;">
        <q-tab-panel name="completed">
          <div class="text-center q-pa-lg">
            <q-icon name="construction" size="48px" color="grey-5" />
            <div class="text-h6 q-mt-md">Logros Completados</div>
            <div class="text-grey-6">Esta sección está en construcción</div>
          </div>
        </q-tab-panel>

        <q-tab-panel name="progress">
          <div class="text-center q-pa-lg">
            <q-icon name="construction" size="48px" color="grey-5" />
            <div class="text-h6 q-mt-md">Logros en Progreso</div>
            <div class="text-grey-6">Esta sección está en construcción</div>
          </div>
        </q-tab-panel>

        <q-tab-panel name="available">
          <div class="text-center q-pa-lg">
            <q-icon name="construction" size="48px" color="grey-5" />
            <div class="text-h6 q-mt-md">Logros Disponibles</div>
            <div class="text-grey-6">Esta sección está en construcción</div>
          </div>
        </q-tab-panel>
      </q-tab-panels>
    </q-card-section>
  </q-card>
</template>

<script>
export default {
  name: 'AchievementsModal',
  emits: ['close'],
  data() {
    return {
      activeTab: 'completed'
    };
  }
}
</script>

<style scoped>
.achievements-modal {
  min-width: 600px;
  max-width: 800px;
}

@media (max-width: 600px) {
  .achievements-modal {
    min-width: 300px;
    max-width: 90vw;
  }
}
</style>