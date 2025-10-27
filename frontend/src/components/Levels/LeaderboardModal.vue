<template>
  <q-card class="leaderboard-modal">
    <q-card-section class="row items-center q-pb-none">
      <div class="text-h6">Clasificación Global</div>
      <q-space />
      <q-btn icon="close" flat round dense @click="$emit('close')" />
    </q-card-section>

    <q-separator />

    <q-card-section>
      <q-tabs v-model="period" align="justify" class="q-mb-md">
        <q-tab name="all_time" label="Histórico" />
        <q-tab name="monthly" label="Mes" />
        <q-tab name="weekly" label="Semana" />
      </q-tabs>

      <div class="text-center q-pa-lg">
        <q-icon name="construction" size="48px" color="grey-5" />
        <div class="text-h6 q-mt-md">Clasificación</div>
        <div class="text-grey-6">Esta sección está en construcción</div>
        <div class="text-caption q-mt-sm">
          Aquí podrás ver tu posición en el ranking global y competir con otros usuarios
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script>
export default {
  name: 'LeaderboardModal',
  emits: ['close'],
  data() {
    return {
      period: 'all_time'
    };
  }
}
</script>

<style scoped>
.leaderboard-modal {
  min-width: 500px;
  max-width: 600px;
}

@media (max-width: 600px) {
  .leaderboard-modal {
    min-width: 300px;
    max-width: 90vw;
  }
}
</style>