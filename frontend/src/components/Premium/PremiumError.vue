<template>
  <q-page class="premium-error-page">
    <div class="error-container">
      <q-card class="error-card">
        <q-card-section class="text-center q-pa-lg">
          <q-icon
            name="error"
            color="negative"
            size="4rem"
            class="q-mb-md"
          />

          <h2 class="text-h4 text-weight-bold text-negative q-mb-md">
            Oops! Algo salió mal
          </h2>

          <p class="text-h6 text-grey-7 q-mb-lg">
            No pudimos procesar tu suscripción Premium
          </p>

          <div class="error-details q-mb-lg">
            <p class="text-body1 text-grey-6">
              Esto puede suceder por varios motivos:
            </p>

            <q-list class="error-reasons">
              <q-item>
                <q-item-section avatar>
                  <q-icon name="credit_card_off" color="orange" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Problema con el método de pago</q-item-label>
                </q-item-section>
              </q-item>

              <q-item>
                <q-item-section avatar>
                  <q-icon name="wifi_off" color="orange" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Problemas de conexión</q-item-label>
                </q-item-section>
              </q-item>

              <q-item>
                <q-item-section avatar>
                  <q-icon name="cancel" color="orange" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Pago cancelado por el usuario</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </div>

          <div class="action-buttons q-gutter-md">
            <q-btn
              label="Intentar de nuevo"
              color="primary"
              size="lg"
              @click="retryPurchase"
              class="q-px-xl"
            />

            <q-btn
              label="Contactar Soporte"
              color="grey"
              outline
              size="lg"
              @click="contactSupport"
              class="q-px-xl"
            />

            <q-btn
              label="Volver al Dashboard"
              color="grey"
              flat
              size="lg"
              @click="goToDashboard"
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
  name: 'PremiumError',

  methods: {
    retryPurchase() {
      this.$router.push('/settings');
    },

    contactSupport() {
      this.$q.dialog({
        title: 'Contactar Soporte',
        message: `
          <div style="text-align: left;">
            <p>Si tienes problemas con tu suscripción, puedes contactarnos por:</p>
            <br>
            <p><strong>Email:</strong> soporte@tidy.app</p>
            <p><strong>WhatsApp:</strong> +54 9 11 1234-5678</p>
            <p><strong>Horario:</strong> Lunes a Viernes, 9:00 - 18:00</p>
          </div>
        `,
        html: true,
        ok: {
          label: 'Entendido',
          color: 'primary'
        }
      });
    },

    goToDashboard() {
      this.$router.push('/dashboard');
    }
  },

  mounted() {
    // Mostrar notificación de error
    this.$q.notify({
      type: 'negative',
      message: 'No se pudo completar la suscripción Premium',
      icon: 'error',
      position: 'top',
      timeout: 3000
    });
  }
}
</script>

<style scoped>
.premium-error-page {
  padding: 20px;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #ff6b6b 0%, #ffa726 100%);
}

.error-container {
  width: 100%;
  max-width: 600px;
}

.error-card {
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.error-reasons {
  text-align: left;
  max-width: 400px;
  margin: 0 auto;
}

.action-buttons {
  margin-top: 32px;
}

@media (max-width: 768px) {
  .premium-error-page {
    padding: 12px;
  }

  .error-card {
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