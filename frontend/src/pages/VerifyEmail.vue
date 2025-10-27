<template>
  <div class="verify-email-page flex flex-center">
    <q-card class="verify-card">
      <q-card-section class="text-center">
        <!-- Loading state -->
        <div v-if="loading">
          <q-spinner-hourglass color="primary" size="80px" />
          <div class="text-h6 q-mt-md">Verificando tu email...</div>
          <div class="text-caption text-grey-6">Por favor espera un momento</div>
        </div>

        <!-- Success state -->
        <div v-else-if="success">
          <q-icon name="check_circle" color="positive" size="80px" />
          <div class="text-h5 text-positive q-mt-md">¡Email verificado!</div>
          <div class="text-body1 q-mt-sm">Tu dirección de email ha sido verificada exitosamente.</div>
          <q-btn
            color="primary"
            label="Ir a Inicio"
            @click="goToHome"
            class="q-mt-lg"
            size="lg"
            no-caps
          />
        </div>

        <!-- Error state -->
        <div v-else-if="error">
          <q-icon name="error" color="negative" size="80px" />
          <div class="text-h5 text-negative q-mt-md">Error al verificar</div>
          <div class="text-body1 q-mt-sm">{{ errorMessage }}</div>
          <q-btn
            color="primary"
            label="Volver al Inicio"
            @click="goToHome"
            class="q-mt-lg"
            size="lg"
            no-caps
          />
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useQuasar } from 'quasar';
import axios from 'axios';

export default {
  name: 'VerifyEmail',

  setup() {
    const router = useRouter();
    const route = useRoute();
    const $q = useQuasar();

    const loading = ref(true);
    const success = ref(false);
    const error = ref(false);
    const errorMessage = ref('');

    const verifyEmail = async () => {
      try {
        const token = route.query.token;

        if (!token) {
          throw new Error('Token de verificación no encontrado');
        }

        console.log('Verificando email con token:', token);

        const apiUrl = import.meta.env.VITE_API_URL || 'https://tidyback-production.up.railway.app';

        const response = await axios.post(`${apiUrl}/api/email/verify-token`, {
          token: token
        });

        console.log('Respuesta de verificación:', response.data);

        if (response.data.success) {
          success.value = true;

          $q.notify({
            type: 'positive',
            message: '¡Email verificado exitosamente!',
            position: 'top',
            timeout: 3000
          });
        } else {
          throw new Error(response.data.message || 'Error al verificar email');
        }
      } catch (err) {
        console.error('Error verificando email:', err);
        error.value = true;

        if (err.response?.status === 404) {
          errorMessage.value = 'El token de verificación no es válido o no existe.';
        } else if (err.response?.status === 410) {
          errorMessage.value = 'El token de verificación ha expirado. Por favor solicita uno nuevo desde tu perfil.';
        } else if (err.response?.data?.message) {
          errorMessage.value = err.response.data.message;
        } else {
          errorMessage.value = err.message || 'Ocurrió un error al verificar tu email. Por favor intenta nuevamente.';
        }

        $q.notify({
          type: 'negative',
          message: errorMessage.value,
          position: 'top',
          timeout: 5000
        });
      } finally {
        loading.value = false;
      }
    };

    const goToHome = () => {
      router.push('/Home');
    };

    onMounted(() => {
      verifyEmail();
    });

    return {
      loading,
      success,
      error,
      errorMessage,
      goToHome
    };
  }
};
</script>

<style scoped>
.verify-email-page {
  width: 100%;
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.verify-card {
  width: 100%;
  max-width: 500px;
  padding: 40px 20px;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

@media (max-width: 600px) {
  .verify-card {
    padding: 30px 15px;
  }
}
</style>
