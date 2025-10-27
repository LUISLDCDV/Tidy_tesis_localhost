<template>
  <q-page class="user-profile-page">
    <!-- Header con indicador de sincronización -->
    <div class="profile-header q-mb-lg">
      <div class="row items-center justify-between">
        <h1 class="text-h4 text-weight-bold">{{ $t('profile.title') }}</h1>
        <SyncStatusIndicator :always-show="false" />
      </div>
    </div>

    <div class="profile-container">
      <!-- Card principal del perfil usando componente compartido -->
      <UserInfoCard
        :show-banner="true"
        avatar-size="120px"
        :show-level="true"
        :show-level-indicator="true"
        :show-additional-info="true"
        :show-actions="false"
        :editable="true"
        :user-level="userLevel"
        :edit-form-config="editFormConfig"
        @change-avatar="openImageSelector"
        @show-level-details="openLevelDashboard"
        @save-profile="handleSaveProfile"
      />

      <!-- Estadísticas del usuario -->
      <q-card class="profile-card q-mt-md">
        <q-card-section>

          <!-- Estadísticas principales -->
          <div class="row q-gutter-md q-mb-lg">
            <div class="col">
              <q-card flat bordered class="stats-card text-center">
                <q-card-section class="q-pa-md">
                  <div class="text-h4 text-weight-bold text-primary">{{ totalNotas }}</div>
                  <div class="text-grey-6">{{ $t('profile.stats.notes') }}</div>
                </q-card-section>
              </q-card>
            </div>
            <div class="col">
              <q-card flat bordered class="stats-card text-center">
                <q-card-section class="q-pa-md">
                  <div class="text-h4 text-weight-bold text-positive">{{ totalObjetivos }}</div>
                  <div class="text-grey-6">{{ $t('profile.stats.objectives') }}</div>
                </q-card-section>
              </q-card>
            </div>
            <div class="col">
              <q-card flat bordered class="stats-card text-center">
                <q-card-section class="q-pa-md">
                  <div class="text-h4 text-weight-bold text-secondary">{{ totalEventos }}</div>
                  <div class="text-grey-6">{{ $t('profile.stats.events') }}</div>
                </q-card-section>
              </q-card>
            </div>
          </div>

          <!-- Estadísticas adicionales -->
          <div class="row q-gutter-md q-mb-lg">
            <div class="col-6">
              <q-card flat bordered class="stats-card text-center">
                <q-card-section class="q-pa-md">
                  <div class="text-h5 text-weight-bold text-positive">{{ objetivosCompletados }}</div>
                  <div class="text-grey-6">{{ $t('profile.stats.objectivesCompleted') }}</div>
                </q-card-section>
              </q-card>
            </div>
            <div class="col-6">
              <q-card flat bordered class="stats-card text-center">
                <q-card-section class="q-pa-md">
                  <div class="text-h5 text-weight-bold text-info">{{ eventosDelMes }}</div>
                  <div class="text-grey-6">{{ $t('profile.stats.monthlyEvents') }}</div>
                </q-card-section>
              </q-card>
            </div>
          </div>

        </q-card-section>
      </q-card>

      <!-- Suscripción Premium -->
      <SubscriptionStatus
        class="q-mt-md"
        @show-plans="showPremiumPlans = true"
        @show-history="showPaymentHistory = true"
        @cancel-subscription="handleCancelSubscription"
        @go-to-settings="goToSubscription"
      />

      <!-- Acciones del perfil -->
      <q-card class="profile-actions-card q-mt-md">
        <q-card-section>
          <h3 class="text-h6 text-weight-medium q-mb-md">
            <q-icon name="person" class="q-mr-sm" />
            {{ $t('profile.actions.title') }}
          </h3>

          <q-list separator>
            <!-- Editar Perfil -->
            <q-item clickable @click="openEditProfile">
              <q-item-section avatar>
                <q-icon name="edit" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('profile.actions.editProfile') }}</q-item-label>
                <q-item-label caption>{{ $t('profile.actions.editProfileDesc') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" />
              </q-item-section>
            </q-item>

            <!-- Ver Estadísticas Completas -->
            <q-item clickable @click="openLevelDashboard">
              <q-item-section avatar>
                <q-icon name="analytics" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('profile.actions.detailedStats') }}</q-item-label>
                <q-item-label caption>{{ $t('profile.actions.detailedStatsDesc') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" />
              </q-item-section>
            </q-item>

            <!-- Ir a Suscripción Premium -->
            <q-item clickable @click="goToSubscription">
              <q-item-section avatar>
                <q-icon name="diamond" color="purple-7" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('profile.actions.premiumSubscription') }}</q-item-label>
                <q-item-label caption>{{ $t('profile.actions.premiumSubscriptionDesc') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" />
              </q-item-section>
            </q-item>

            <!-- Ir a Configuración -->
            <q-item clickable @click="goToSettings">
              <q-item-section avatar>
                <q-icon name="settings" color="grey-7" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('profile.actions.accountSettings') }}</q-item-label>
                <q-item-label caption>{{ $t('profile.actions.accountSettingsDesc') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" />
              </q-item-section>
            </q-item>

            <!-- Simular Premium (Testing) -->
            <q-item clickable @click="togglePremiumSimulation">
              <q-item-section avatar>
                <q-icon :name="hasActivePremium ? 'workspace_premium' : 'star_outline'" :color="hasActivePremium ? 'amber' : 'grey-6'" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ hasActivePremium ? 'Desactivar' : 'Activar' }} Premium (Prueba)</q-item-label>
                <q-item-label caption>{{ hasActivePremium ? 'Quitar suscripción de prueba' : 'Simular suscripción premium mensual' }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="science" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>
    </div>

    <!-- Modal para editar perfil -->
    <q-dialog v-model="editProfileModal">
      <q-card class="edit-profile-card">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ $t('profile.actions.editProfile') }}</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="editProfileModal = false" />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pt-md">
          <q-form @submit="saveProfile" class="q-gutter-md">
            <q-input
              v-model="editForm.name"
              :label="$t('profile.fullName')"
              filled
              :rules="[val => !!val || 'El nombre es requerido']"
            />
            
            <q-input
              v-model="editForm.email"
              :label="$t('profile.email')"
              type="email"
              filled
              :rules="[val => !!val || 'El email es requerido']"
            />

            <q-input
              v-model="editForm.imageUrl"
              :label="$t('profile.imageUrl')"
              filled
            />

            <div class="row justify-end q-gutter-sm q-mt-md">
              <q-btn
                :label="$t('common.cancel')"
                color="grey"
                @click="editProfileModal = false"
                flat
              />
              <q-btn
                :label="$t('common.save')"
                color="primary"
                type="submit"
                :loading="saving"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Modal para planes premium -->
    <PremiumPlansModal 
      v-model="showPremiumPlans"
      @subscription-success="handleSubscriptionSuccess"
    />
  </q-page>
</template>

<script>
import { useElementsStore } from '@/stores/elements';
import { useAuthStore } from '@/stores/auth';
import { useQuasar } from 'quasar';
import { useI18n } from 'vue-i18n';
import SyncStatusIndicator from '@/components/SyncStatusIndicator.vue';
import LevelIndicator from '@/components/Levels/LevelIndicator.vue';
import SubscriptionStatus from '@/components/Payments/SubscriptionStatus.vue';
import PremiumPlansModal from '@/components/Payments/PremiumPlansModal.vue';
import UserInfoCard from '@/components/User/shared/UserInfoCard.vue';
import { usePaymentsStore } from '@/stores/payments';
import { useLevelsStore } from '@/stores/levels';

export default {
  name: 'UserProfile',
  components: {
    SyncStatusIndicator,
    LevelIndicator,
    SubscriptionStatus,
    PremiumPlansModal,
    UserInfoCard
  },
  props: {
    id: {
      type: String,
      required: false,
      default: null
    },
    name: {
      type: String,
      required: false,
      default: 'Usuario'
    }
  },
  setup() {
    const { t } = useI18n()
    return { t }
  },
  data() {
    return {
      defaultImageUrl: 'https://cdn.quasar.dev/img/avatar.png',
      totalNotas: 0,
      totalObjetivos: 0,
      totalEventos: 0,
      editProfileModal: false,
      saving: false,
      editForm: {
        name: '',
        email: '',
        imageUrl: '',
        phone: '',
        bio: ''
      },
      showPremiumPlans: false,
      showPaymentHistory: false
    }
  },
  setup() {
    const elementsStore = useElementsStore();
    const authStore = useAuthStore();
    const levelsStore = useLevelsStore();
    const paymentsStore = usePaymentsStore();

    return {
      elementsStore,
      authStore,
      levelsStore,
      paymentsStore
    };
  },
  
  computed: {
    editFormConfig() {
      return {
        enabled: true,
        showPhone: true,
        showBio: true
      };
    },
    
    userEmail() {
      return this.authStore.currentUser?.email || 
             this.authStore.currentUser?.correo || 
             'usuario@example.com';
    },
    
    userImageUrl() {
      return this.authStore.currentUser?.image_url || 
             this.authStore.currentUser?.imagen || 
             this.authStore.currentUser?.avatar || '';
    },

    realUserName() {
      return this.authStore.currentUser?.name || 
             this.authStore.currentUser?.nombre || 
             this.authStore.currentUser?.usuario || 
             'Usuario';
    },

    decodedName() {
      try {
        return decodeURIComponent(this.realUserName);
      } catch {
        return this.realUserName;
      }
    },

    userPhone() {
      return this.authStore.currentUser?.phone || 
             this.authStore.currentUser?.telefono || '';
    },

    userBio() {
      return this.authStore.currentUser?.bio || 
             this.authStore.currentUser?.biografia || '';
    },

    userJoinDate() {
      const createdAt = this.authStore.currentUser?.created_at || 
                       this.authStore.currentUser?.fecha_registro;
      if (createdAt) {
        return new Date(createdAt).toLocaleDateString('es-ES', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
      }
      return '';
    },

    userLevel() {
      return this.levelsStore.getCurrentLevel;
    },

    hasActivePremium() {
      return this.paymentsStore.hasActivePremium;
    },

    userLevelText() {
      return this.levelsStore.getUserRank?.title || 'Novato';
    },

    userLevelColor() {
      return this.levelsStore.getUserRank?.color || 'grey';
    },

    userLevelIcon() {
      return this.levelsStore.getUserRank?.icon || '🌱';
    },

    objetivosCompletados() {
      return this.elementsStore.allElements.filter(el => 
        el.tipo === 'objetivo' && el.estado === 'completado'
      ).length;
    },

    eventosDelMes() {
      const now = new Date();
      const firstDayOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
      const lastDayOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0);
      
      return this.elementsStore.allElements.filter(el => {
        if (el.tipo !== 'evento') return false;
        const eventDate = new Date(el.fecha_evento || el.fechaCreacion);
        return eventDate >= firstDayOfMonth && eventDate <= lastDayOfMonth;
      }).length;
    },

    diasCumpliendoMetas() {
      // Calcular días del mes actual cumpliendo metas diarias
      const now = new Date();
      const currentMonth = now.getMonth();
      const currentYear = now.getFullYear();
      
      // Por ahora retornamos un valor simulado
      // En una implementación real, esto vendría del historial de actividad
      return Math.floor(Math.random() * now.getDate()) + 1;
    }
  },

  methods: {
    // Método para manejar el guardado del perfil desde UserInfoCard
    async handleSaveProfile(formData) {
      try {
        // Simular guardado en el servidor
        await new Promise(resolve => setTimeout(resolve, 1500));

        // Actualizar datos locales si es necesario
        if (this.authStore.currentUser) {
          this.authStore.currentUser.name = formData.name;
          this.authStore.currentUser.email = formData.email;
          this.authStore.currentUser.phone = formData.phone;
          this.authStore.currentUser.bio = formData.bio;
        }

        this.$q.notify({
          type: 'positive',
          message: 'Perfil actualizado exitosamente',
          icon: 'check_circle',
          position: 'top'
        });
      } catch (error) {
        console.error('Error saving profile:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error al actualizar el perfil',
          position: 'top'
        });
      }
    },

    
    async loadUserStats() {
      try {
        // Obtener estadísticas del store
        await this.elementsStore.fetchElements();
        
        const elements = this.elementsStore.allElements;
        
        this.totalNotas = elements.filter(el => el.tipo === 'nota').length;
        this.totalObjetivos = elements.filter(el => el.tipo === 'objetivo').length;
        this.totalEventos = elements.filter(el => el.tipo === 'evento').length;
        
        // Nivel se obtiene del sistema de gamificación
        
      } catch (error) {
        console.error('Error loading user stats:', error);
        // Valores por defecto
        this.totalNotas = 15;
        this.totalObjetivos = 8;
        this.totalEventos = 12;
        // Los niveles se obtienen del sistema de gamificación
      }
    },

    openEditProfile() {
      this.editForm.name = this.decodedName;
      this.editForm.email = this.userEmail;
      this.editForm.imageUrl = this.userImageUrl;
      this.editForm.phone = this.userPhone;
      this.editForm.bio = this.userBio;
      this.editProfileModal = true;
    },


    goToSettings() {
      // Navegar al componente de configuración completo
      this.$router.push('/settings');
    },

    goToSubscription() {
      // Navegar directamente a la sección de suscripción en settings
      this.$router.push({
        path: '/settings',
        hash: '#subscription' // Ancla para scrollear a la sección de suscripción
      });
    },

    openLevelDashboard() {
      // Navegar al dashboard de niveles
      this.$router.push('/levels');
    },

    async saveProfile() {
      this.saving = true;
      
      try {
        // Aquí iría la lógica para guardar el perfil
        await new Promise(resolve => setTimeout(resolve, 1000)); // Simular guardado
        
        this.$q.notify({
          type: 'positive',
          message: 'Perfil actualizado exitosamente',
          icon: 'check_circle',
          position: 'top'
        });
        
        this.editProfileModal = false;
      } catch (error) {
        console.error('Error saving profile:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error al actualizar el perfil',
          position: 'top'
        });
      } finally {
        this.saving = false;
      }
    },

    openImageSelector() {
      // Aquí iría la lógica para seleccionar imagen
      this.$q.notify({
        type: 'info',
        message: 'Funcionalidad de cambio de imagen próximamente',
        icon: 'camera_alt',
        position: 'top'
      });
    },

    handleImageError() {
      // Si falla la carga de imagen, usar la por defecto
      this.userImageUrl = this.defaultImageUrl;
    },


    // Métodos para manejo de pagos
    handleCancelSubscription() {
      this.$q.dialog({
        title: 'Cancelar Suscripción Premium',
        message: '¿Estás seguro que deseas cancelar tu suscripción? Perderás acceso a todas las funciones premium al final del período actual.',
        cancel: true,
        persistent: true,
        ok: {
          label: 'Sí, cancelar',
          color: 'negative'
        },
        cancel: {
          label: 'No, mantener',
          flat: true
        }
      }).onOk(async () => {
        try {
          const paymentsStore = usePaymentsStore();
          const result = await paymentsStore.cancelSubscription();
          
          if (result.success) {
            this.$q.notify({
              type: 'positive',
              message: 'Suscripción cancelada correctamente',
              position: 'top'
            });
          }
        } catch (error) {
          this.$q.notify({
            type: 'negative',
            message: error.message || 'Error al cancelar la suscripción',
            position: 'top'
          });
        }
      });
    },

    handleShowPaymentHistory() {
      // TODO: Implementar modal de historial de pagos
      this.$q.notify({
        type: 'info',
        message: 'Historial de pagos próximamente disponible',
        position: 'top'
      });
    },

    handleSubscriptionSuccess() {
      this.$q.notify({
        type: 'positive',
        message: '¡Bienvenido a Tidy Premium! Disfruta de todas las funciones avanzadas.',
        position: 'top',
        timeout: 5000
      });

      // Recargar estadísticas del usuario para reflejar el cambio premium
      this.loadUserStats();
    },

    togglePremiumSimulation() {
      console.log('🎮 Toggle Premium - Estado actual:', this.hasActivePremium);
      console.log('🎮 Subscription actual:', this.paymentsStore.subscription);

      if (this.hasActivePremium) {
        this.paymentsStore.clearSimulatedSubscription();
        console.log('✅ Premium desactivado');
        this.$q.notify({
          type: 'info',
          message: 'Premium desactivado (modo prueba)',
          icon: 'star_outline',
          position: 'top',
          timeout: 2000
        });
      } else {
        this.paymentsStore.simulatePremiumSubscription();
        console.log('✅ Premium activado');
        console.log('🎮 Nueva subscription:', this.paymentsStore.subscription);
        this.$q.notify({
          type: 'positive',
          message: 'Premium activado (modo prueba) - válido por 30 días',
          icon: 'workspace_premium',
          position: 'top',
          timeout: 3000
        });
      }

      console.log('🎮 Estado final hasActivePremium:', this.hasActivePremium);
    }
  },

  async created() {
    // Cargar estadísticas del usuario
    await this.loadUserStats();

    // Inicializar datos de niveles
    try {
      await this.levelsStore.initializeLevelData();
    } catch (error) {
      console.error('Error loading level data in profile:', error);
    }
  }
}
</script>

<style scoped>
.user-profile-page {
  padding: 16px;
  max-width: 800px;
  margin: 0 auto;
}

.profile-container {
  width: 100%;
}

.profile-card {
  overflow: visible;
}

.profile-banner {
  height: 120px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  position: relative;
  border-radius: 4px 4px 0 0;
}

.profile-avatar-container {
  position: absolute;
  bottom: -60px;
  left: 50%;
  transform: translateX(-50%);
}

.profile-avatar {
  border: 4px solid white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.avatar-edit-btn {
  position: absolute;
  bottom: 0;
  right: 0;
}

.profile-info {
  padding-top: 80px !important;
}

.stats-card {
  transition: transform 0.2s ease;
}

.stats-card:hover {
  transform: scale(1.02);
}

.config-card {
  border-radius: 8px;
}

.language-select {
  min-width: 100px;
}

.edit-profile-card {
  min-width: 400px;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .user-profile-page {
    padding: 12px;
  }
  
  .profile-banner {
    height: 100px;
  }
  
  .profile-avatar-container {
    bottom: -50px;
  }
  
  .profile-avatar {
    width: 100px !important;
    height: 100px !important;
  }
  
  .profile-info {
    padding-top: 70px !important;
  }
  
  .edit-profile-card {
    min-width: 280px;
  }
  
  .row.q-gutter-md {
    flex-direction: column;
  }
  
  .row.q-gutter-md .col {
    margin-bottom: 12px;
  }
}

@media (max-width: 480px) {
  .user-profile-page {
    padding: 8px;
  }
  
  .profile-header h1 {
    font-size: 1.3rem;
  }
  
  .stats-card .text-h4 {
    font-size: 1.5rem;
  }
}

/* Dark mode support */
.body--dark .profile-banner {
  background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
}

.body--dark .profile-avatar {
  border-color: #2d3748;
}

.body--dark .stats-card {
  background: #374151;
}
</style>