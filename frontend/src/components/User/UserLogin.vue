<template>
  <div class="login-page flex flex-center">
    <q-card class="login-card">
      <q-card-section class="text-center">
        <div class="text-h4 text-primary q-mb-md">
          <q-icon name="login" size="md" class="q-mr-sm"/>
          Iniciar Sesión
        </div>
        
        <!-- Mensaje de sesión expirada -->
        <q-banner v-if="sessionExpiredMessage" class="bg-warning text-white q-mb-md">
          <template v-slot:avatar>
            <q-icon name="warning" color="white" />
          </template>
          <div class="text-body2">
            {{ sessionExpiredMessage }}
          </div>
        </q-banner>
      </q-card-section>

      <q-card-section>
        <q-form @submit.prevent="login" class="login-form">
          <q-input
            v-model="email"
            type="email"
            label="Correo electrónico"
            outlined
            required
            :loading="loading"
            :disable="loading"
            class="q-mb-md"
          >
            <template v-slot:prepend>
              <q-icon name="mail" />
            </template>
          </q-input>

          <q-input
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            label="Contraseña"
            outlined
            required
            :loading="loading"
            :disable="loading"
            class="q-mb-lg"
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

          <!-- Checkbox Recordarme -->
          <div class="row q-mb-md">
            <q-checkbox
              v-model="rememberMe"
              label="Recordarme"
              color="primary"
              class="text-grey-7"
            />
          </div>

          <q-btn
            type="submit"
            color="primary"
            size="lg"
            class="full-width q-mb-md"
            :loading="loading"
            :disable="!email || !password"
            no-caps
          >
            <template v-slot:loading>
              <q-spinner-hourglass class="on-left" />
              Iniciando sesión...
            </template>
            Iniciar Sesión
          </q-btn>

          <!-- Login con Google -->
          <div class="text-center q-my-md">
            <q-separator spaced />
            <div class="text-caption text-grey-6 q-mb-md">O inicia sesión con</div>
            <q-btn
              unelevated
              no-caps
              class="full-width google-btn"
              style="background: #ffffff; color: #444444; border: 1px solid #dadce0; padding: 12px 24px;"
              @click="loginWithGoogle"
              :loading="googleLoading"
              :disable="loading || googleLoading"
            >
              <template v-if="!googleLoading">
                <div class="row items-center no-wrap q-gutter-sm">
                  <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    <path fill="none" d="M0 0h48v48H0z"/>
                  </svg>
                  <span style="font-size: 14px; font-weight: 500;">Continuar con Google</span>
                </div>
              </template>
              <template v-else>
                <q-spinner-hourglass class="on-left" />
                <span>Conectando con Google...</span>
              </template>
            </q-btn>
          </div>

          <q-banner v-if="error" class="text-white bg-negative rounded-borders q-mb-md">
            <template v-slot:avatar>
              <q-icon name="error" color="white" />
            </template>
            {{ error }}
          </q-banner>

          <!-- Link al registro -->
          <div class="text-center">
            <q-separator spaced />
            <div class="text-caption text-grey-6 q-mb-md">¿No tienes una cuenta?</div>
            <q-btn
              flat
              color="primary"
              icon="person_add"
              label="Crear cuenta nueva"
              @click="$router.push('/Register')"
              no-caps
              class="full-width q-mb-md"
            />

            <!-- Botón de DEBUG -->
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </div>
</template>

<script>
import { useAuthStore } from '@/stores/auth';
import firebaseService from '@/services/firebase';

export default {
  name: 'UserLogin',
  
  data() {
    return {
      email: '',
      password: '',
      error: '',
      loading: false,
      googleLoading: false,
      showPassword: false,
      sessionExpiredMessage: '',
      rememberMe: false
    };
  },

  setup() {
    const authStore = useAuthStore();
    return { authStore };
  },

  mounted() {
    // Verificar si hay una preferencia guardada de recordarme
    this.rememberMe = this.authStore.rememberMe;

    // Si había recordarme activado y hay datos guardados, prellenar email
    if (this.rememberMe && localStorage.getItem('user')) {
      try {
        const userData = JSON.parse(localStorage.getItem('user'));
        this.email = userData.email || '';
      } catch (err) {
        console.warn('Error al cargar email guardado:', err);
      }
    }

    // Verificar si hay mensaje de sesión expirada en la query
    if (this.$route.query.message) {
      this.sessionExpiredMessage = this.$route.query.message;
    }
  },

  methods: {
    async login() {
      this.error = '';
      this.loading = true;
      
      try {
        console.log('Intentando login...');
        const credentials = {
          email: this.email,
          password: this.password,
        };
        
        // Intentar login con el store
        try {
          const result = await this.authStore.login(credentials, this.rememberMe);
          console.log('Resultado login:', result);
          
          if (result) {
            console.log('Login exitoso, redirigiendo...');

            // Pequeño delay para asegurar que el token se guarde correctamente
            await new Promise(resolve => setTimeout(resolve, 100));

            // Redireccionar a la página original o a Home
            const redirectTo = this.$route.query.redirect || '/Home';
            this.$router.push(redirectTo);
            return;
          }
        } catch (serverError) {
          console.warn('Error del servidor, intentando login demo:', serverError.message);
        }
        
        // Si el servidor no está disponible, hacer login demo para desarrollo
        if (this.email === 'demo@demo.com' && this.password === 'demo123') {
          console.log('Login demo exitoso');
          
          // Simular datos de usuario para desarrollo
          const mockUser = {
            id: 1,
            name: 'Usuario Demo',
            email: 'demo@demo.com'
          };
          const mockToken = 'demo-token-' + Date.now();
          
          // Guardar en localStorage
          localStorage.setItem('auth_token', mockToken);
          localStorage.setItem('user', JSON.stringify(mockUser));
          
          // Actualizar store
          this.authStore.token = mockToken;
          this.authStore.user = mockUser;
          this.authStore.isAuthenticated = true;
          
          // Redireccionar a la página original o a Home
          const redirectTo = this.$route.query.redirect || '/Home';
          this.$router.push(redirectTo);
        } else {
          this.error = 'Credenciales inválidas. Para demo use: demo@demo.com / demo123';
        }
      } catch (error) {
        console.error('Error en login:', error);
        this.error = 'Error al intentar iniciar sesión';
      } finally {
        this.loading = false;
      }
    },

    async loginWithGoogle() {
      this.error = '';
      this.googleLoading = true;

      try {
        console.log('Iniciando login con Google...');

        // Login con Firebase
        const firebaseUser = await firebaseService.loginWithGoogle();
        console.log('Firebase user:', firebaseUser);

        // Enviar datos a Laravel para crear/vincular usuario
        const response = await this.authStore.loginWithGoogle({
          firebase_uid: firebaseUser.uid,
          email: firebaseUser.email,
          name: firebaseUser.name,
          photo: firebaseUser.photo,
          firebase_token: firebaseUser.token
        });

        if (response.success) {
          console.log('Login con Google exitoso');

          // Aplicar configuración recordarme si estaba activada
          if (this.rememberMe) {
            this.authStore.setRememberMe(true);
          }

          // Redirigir al home o página solicitada
          const redirectPath = this.$route.query.redirect || '/Home';
          this.$router.push(redirectPath);
        } else {
          this.error = response.message || 'Error al iniciar sesión con Google';
        }
      } catch (error) {
        console.error('Error en login con Google:', error);
        this.error = error.message || 'Error al conectar con Google';

        // Logout de Firebase si falló el login en Laravel
        try {
          await firebaseService.firebaseLogout();
        } catch (logoutError) {
          console.error('Error al hacer logout de Firebase:', logoutError);
        }
      } finally {
        this.googleLoading = false;
      }
    }
  },

  // Verificar si ya está autenticado
  created() {
    console.log('Login component created');
    
    // Capturar mensaje de sesión expirada de la query string
    const message = this.$route.query.message;
    if (message) {
      this.sessionExpiredMessage = message;
    }
    
    const token = localStorage.getItem('auth_token');
    console.log('Token existente:', token);
    if (token) {
      console.log('Usuario ya autenticado, redirigiendo a Home');
      this.$router.push('/Home');
    }
  }
};
</script>

<style scoped>
.login-page {
  width: 100%;
  padding: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.login-card {
  width: 100%;
  max-width: 450px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  border-radius: 12px;
}

.login-form {
  width: 100%;
}

/* Responsive design */
@media (max-width: 600px) {
  .login-page {
    padding: 4px;
    min-height: 100vh;
  }
  
  .login-card {
    max-width: 100%;
    margin: 0;
    border-radius: 8px;
    min-height: auto;
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
  .login-page {
    padding: 2px;
  }
  
  .login-card {
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
  
  /* Banners más compactos */
  :deep(.q-banner) {
    padding: 8px 12px;
    font-size: 0.9rem;
  }
}

/* Mobile landscape */
@media (max-height: 500px) and (orientation: landscape) {
  .login-page {
    padding: 4px;
    align-items: flex-start;
    padding-top: 10px;
  }
  
  .login-card {
    max-height: 95vh;
    overflow-y: auto;
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

/* Better error styling */
:deep(.q-field--error .q-field__control) {
  border-color: var(--q-negative);
}

:deep(.q-field--error .q-field__label) {
  color: var(--q-negative);
}
</style>
