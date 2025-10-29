<template>
  <div class="register-page flex flex-center">
    <q-card class="register-card">
      <q-card-section class="text-center">
        <div class="text-h4 text-primary q-mb-md">
          <q-icon name="person_add" size="md" class="q-mr-sm"/>
          Crear Cuenta
        </div>
        <p class="text-body2 text-grey-7">Completa tus datos para registrarte</p>
      </q-card-section>

      <q-card-section>
        <q-form @submit.prevent="registerUser" class="register-form">
          <!-- Nombre y Apellido en fila (responsive) -->
          <div class="row q-gutter-md q-mb-md name-row">
            <q-input
              v-model="name"
              type="text"
              label="Nombre"
              outlined
              required
              :loading="loading"
              :disable="loading"
              class="col name-input"
            >
              <template v-slot:prepend>
                <q-icon name="person" />
              </template>
            </q-input>

            <q-input
              v-model="last_name"
              type="text"
              label="Apellido"
              outlined
              required
              :loading="loading"
              :disable="loading"
              class="col lastname-input"
            >
              <template v-slot:prepend>
                <q-icon name="person_outline" />
              </template>
            </q-input>
          </div>

          <!-- Teléfono -->
          <q-input
            v-model="phone"
            type="tel"
            label="Teléfono"
            outlined
            required
            :loading="loading"
            :disable="loading"
            class="q-mb-md"
          >
            <template v-slot:prepend>
              <q-icon name="phone" />
            </template>
          </q-input>

          <!-- Email -->
          <q-input
            v-model="email"
            type="email"
            label="Correo electrónico"
            outlined
            required
            :loading="loading"
            :disable="loading"
            class="q-mb-md"
            :rules="[val => /.+@.+\..+/.test(val) || 'Ingresa un email válido']"
          >
            <template v-slot:prepend>
              <q-icon name="mail" />
            </template>
          </q-input>

          <!-- Contraseña -->
          <q-input
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            label="Contraseña"
            outlined
            required
            :loading="loading"
            :disable="loading"
            class="q-mb-md"
            :rules="[val => val.length >= 6 || 'Mínimo 6 caracteres']"
          >
            <template v-slot:prepend>
              <q-icon name="lock" />
            </template>
            <template v-slot:append>
              <q-icon
                :name="showPassword ? 'visibility_off' : 'visibility'"
                class="cursor-pointer"
                @click="showPassword = !showPassword"
              />
            </template>
          </q-input>

          <!-- Confirmar contraseña -->
          <q-input
            v-model="password_confirmation"
            :type="showPasswordConfirm ? 'text' : 'password'"
            label="Confirmar contraseña"
            outlined
            required
            :loading="loading"
            :disable="loading"
            class="q-mb-lg"
            :rules="[val => val === password || 'Las contraseñas no coinciden']"
          >
            <template v-slot:prepend>
              <q-icon name="lock_outline" />
            </template>
            <template v-slot:append>
              <q-icon
                :name="showPasswordConfirm ? 'visibility_off' : 'visibility'"
                class="cursor-pointer"
                @click="showPasswordConfirm = !showPasswordConfirm"
              />
            </template>
          </q-input>

          <!-- Botón de registro -->
          <q-btn
            type="submit"
            color="primary"
            size="lg"
            class="full-width q-mb-md"
            :loading="loading"
            :disable="!isFormValid"
            no-caps
          >
            <template v-slot:loading>
              <q-spinner-hourglass class="on-left" />
              Registrando...
            </template>
            Crear Cuenta
          </q-btn>

          <!-- Error message -->
          <q-banner v-if="errorMessage" class="text-white bg-negative rounded-borders q-mb-md">
            <template v-slot:avatar>
              <q-icon name="error" color="white" />
            </template>
            {{ errorMessage }}
          </q-banner>

          <!-- Link al login -->
          <div class="text-center">
            <q-separator spaced />
            <div class="text-caption text-grey-6 q-mb-md">¿Ya tienes una cuenta?</div>
            <q-btn
              flat
              color="primary"
              icon="login"
              label="Iniciar sesión"
              @click="$router.push('/login')"
              no-caps
              class="full-width"
            />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </div>
</template>

<script>
import axios from 'axios';
import { Notify } from 'quasar';

export default {
  name: 'UserRegister',
  
  data() {
    return {
      name: '',
      last_name: '',
      phone: '',
      email: '',
      password: '',
      password_confirmation: '',
      errorMessage: '',
      loading: false,
      showPassword: false,
      showPasswordConfirm: false
    };
  },

  computed: {
    isFormValid() {
      return (
        this.name.trim() !== '' &&
        this.last_name.trim() !== '' &&
        this.phone.trim() !== '' &&
        this.email.trim() !== '' &&
        this.password.length >= 6 &&
        this.password === this.password_confirmation &&
        /.+@.+\..+/.test(this.email)
      );
    }
  },

  methods: {
    async registerUser() {
      if (!this.isFormValid) return;

      this.errorMessage = '';
      this.loading = true;

      try {
        console.log('Datos a enviar:', {
          name: this.name,
          last_name: this.last_name,
          phone: this.phone,
          email: this.email,
          password: this.password,
          password_confirmation: this.password_confirmation,
        });
        
        const response = await axios.post(import.meta.env.VITE_API_URL + '/register', {
          name: this.name,
          last_name: this.last_name,
          phone: this.phone,
          email: this.email,
          password: this.password,
          password_confirmation: this.password_confirmation,
        }, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });
        
        console.log('Registro exitoso:', response);
        
        Notify.create({
          type: 'positive',
          message: '¡Cuenta creada exitosamente!',
          position: 'top'
        });

        this.$router.push('/');
      } catch (error) {
        console.error('Error en registro:', error);
        console.error('Datos de error:', error.response?.data);
        console.error('Status:', error.response?.status);
        
        if (error.response && error.response.data) {
          // Mostrar errores específicos de validación
          if (error.response.data.errors) {
            const errors = Object.values(error.response.data.errors).flat();
            this.errorMessage = errors.join(', ');
          } else {
            this.errorMessage = error.response.data.message || 'Error al crear la cuenta';
          }
        } else if (error.request) {
          this.errorMessage = 'No se recibió respuesta del servidor';
        } else {
          this.errorMessage = 'Error al intentar crear la cuenta';
        }

        Notify.create({
          type: 'negative',
          message: this.errorMessage,
          position: 'top'
        });
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>

<style scoped>
.register-page {
  width: 100%;
  padding: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.register-card {
  width: 100%;
  max-width: 500px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  border-radius: 12px;
}

.register-form {
  width: 100%;
}

/* Responsive design */
@media (max-width: 600px) {
  .register-page {
    padding: 4px;
    min-height: 100vh;
  }
  
  .register-card {
    max-width: 100%;
    margin: 0;
    border-radius: 8px;
  }
  
  /* Campos nombre y apellido en columna en móvil */
  .name-row {
    flex-direction: column;
    gap: 12px !important;
  }
  
  .name-input,
  .lastname-input {
    width: 100%;
  }
  
  /* Inputs más grandes para mobile */
  :deep(.q-field--outlined .q-field__control) {
    min-height: 56px;
  }
  
  /* Botones más accesibles */
  :deep(.q-btn) {
    min-height: 48px;
    font-size: 1rem;
  }
  
  /* Título más pequeño en mobile */
  .text-h4 {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .register-page {
    padding: 2px;
  }
  
  .register-card {
    border-radius: 0;
    box-shadow: none;
    width: 100vw;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  
  /* Espaciado más compacto */
  :deep(.q-card__section) {
    padding: 16px;
  }
  
  .text-h4 {
    font-size: 1.3rem;
    margin-bottom: 1rem;
  }
  
  /* Campos más compactos */
  .name-row {
    margin-bottom: 16px !important;
    gap: 8px !important;
  }
  
  /* Banners más compactos */
  :deep(.q-banner) {
    padding: 8px 12px;
    font-size: 0.9rem;
  }
}

/* Mobile landscape */
@media (max-height: 500px) and (orientation: landscape) {
  .register-page {
    padding: 4px;
    align-items: flex-start;
    padding-top: 5px;
  }
  
  .register-card {
    max-height: 95vh;
    overflow-y: auto;
  }
  
  /* Mantener los campos en fila en landscape si hay espacio */
  .name-row {
    flex-direction: row;
    gap: 8px !important;
  }
  
  /* Elementos más compactos en landscape */
  :deep(.q-card__section) {
    padding: 12px;
  }
  
  .text-h4 {
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
  }
  
  :deep(.q-field--outlined .q-field__control) {
    min-height: 48px;
  }
  
  :deep(.q-btn) {
    min-height: 44px;
  }
}

/* Touch device optimizations */
@media (pointer: coarse) {
  /* Eliminar hover effects */
  :deep(.q-btn):hover {
    box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2), 0 2px 2px rgba(0, 0, 0, 0.14), 0 3px 1px -2px rgba(0, 0, 0, 0.12);
  }
  
  /* Área de toque más grande para iconos */
  :deep(.q-field__append),
  :deep(.q-field__prepend) {
    min-width: 48px;
    min-height: 48px;
  }
  
  /* Feedback táctil */
  :deep(.q-btn):active {
    transform: scale(0.98);
  }
}

/* Custom Quasar overrides */
:deep(.q-field--outlined .q-field__control) {
  border-radius: 8px;
  transition: all 0.2s ease;
}

:deep(.q-field--outlined.q-field--focused .q-field__control) {
  border-width: 2px;
}

:deep(.q-btn) {
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.2s ease;
}

:deep(.q-banner) {
  border-radius: 8px;
  margin-bottom: 12px;
}

/* Validation styling */
:deep(.q-field--error .q-field__control) {
  border-color: var(--q-negative);
}

:deep(.q-field--error .q-field__label) {
  color: var(--q-negative);
}

/* Loading state improvements */
:deep(.q-btn--loading) {
  pointer-events: none;
}

:deep(.q-spinner-hourglass) {
  color: white;
}

/* Input focus improvements */
:deep(.q-field--focused .q-field__label) {
  color: var(--q-primary);
}

/* Password visibility toggle improvements */
:deep(.q-field__append .q-icon) {
  cursor: pointer;
  padding: 8px;
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

:deep(.q-field__append .q-icon):hover {
  background-color: rgba(0, 0, 0, 0.05);
}

/* Form animations */
.register-form {
  animation: slideInUp 0.5s ease-out;
}

@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>




