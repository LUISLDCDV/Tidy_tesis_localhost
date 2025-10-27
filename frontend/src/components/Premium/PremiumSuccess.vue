<template>
  <q-page class="premium-success-page">
    <div class="success-container">
      <q-card class="success-card">
        <q-card-section class="text-center q-pa-lg">
          <q-icon
            name="check_circle"
            color="positive"
            size="4rem"
            class="q-mb-md"
          />

          <h2 class="text-h4 text-weight-bold text-positive q-mb-md">
            {{ $t('premium.success.title') }}
          </h2>

          <p class="text-h6 text-grey-7 q-mb-lg">
            {{ $t('premium.success.description') }}
          </p>

          <div class="premium-features q-mb-lg">
            <h3 class="text-h6 text-weight-medium q-mb-md">
              {{ $t('premium.success.accessTo') }}
            </h3>

            <q-list class="premium-list">
              <q-item>
                <q-item-section avatar>
                  <q-icon name="stars" color="purple" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Notas ilimitadas de todos los tipos</q-item-label>
                </q-item-section>
              </q-item>

              <q-item>
                <q-item-section avatar>
                  <q-icon name="location_on" color="purple" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Alarmas con geolocalización</q-item-label>
                </q-item-section>
              </q-item>

              <q-item>
                <q-item-section avatar>
                  <q-icon name="repeat" color="purple" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Configuración avanzada de periodicidad</q-item-label>
                </q-item-section>
              </q-item>

              <q-item>
                <q-item-section avatar>
                  <q-icon name="analytics" color="purple" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Estadísticas detalladas</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </div>

          <div class="action-buttons q-gutter-md">
            <q-btn
              label="Ir al Dashboard"
              color="primary"
              size="lg"
              @click="goToDashboard"
              class="q-px-xl"
            />

            <q-btn
              label="Explorar Funciones"
              color="purple"
              outline
              size="lg"
              @click="explorePremium"
              class="q-px-xl"
            />
          </div>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>

<script>
export default {
  name: 'PremiumSuccess',

  methods: {
    goToDashboard() {
      this.$router.push('/dashboard');
    },

    explorePremium() {
      this.$router.push('/features');
    }
  },

  mounted() {
    // Mostrar notificación de éxito
    this.$q.notify({
      type: 'positive',
      message: '¡Suscripción Premium activada!',
      icon: 'workspace_premium',
      position: 'top',
      timeout: 3000
    });
  }
}
</script>

<style scoped>
.premium-success-page {
  padding: 20px;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.success-container {
  width: 100%;
  max-width: 600px;
}

.success-card {
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.premium-list {
  text-align: left;
  max-width: 400px;
  margin: 0 auto;
}

.action-buttons {
  margin-top: 32px;
}

@media (max-width: 768px) {
  .premium-success-page {
    padding: 12px;
  }

  .success-card {
    margin: 0;
  }

  .action-buttons {
    flex-direction: column;
  }

  .action-buttons .q-btn {
    width: 100%;
    margin-bottom: 12px;
  }
}
</style>