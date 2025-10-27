<template>
  <q-dialog 
    v-model="showModal" 
    persistent 
    :maximized="$q.screen.lt.sm"
    transition-show="scale"
    transition-hide="scale"
  >
    <q-card class="premium-modal" :class="{ 'mobile-modal': $q.screen.lt.sm }">
      <!-- Header -->
      <q-card-section class="modal-header">
        <div class="row items-center justify-between">
          <div class="header-content">
            <div class="header-icon">
              <q-icon name="workspace_premium" size="md" color="amber" />
            </div>
            <div>
              <div class="text-h5 text-weight-bold">Tidy Premium</div>
              <div class="text-subtitle2 text-grey-6">Desbloquea todo el potencial de Tidy</div>
            </div>
          </div>
          <q-btn 
            v-if="!$q.screen.lt.sm"
            flat 
            round 
            icon="close" 
            @click="closeModal"
            color="grey-6" 
          />
        </div>
      </q-card-section>

      <!-- Plans comparison -->
      <q-card-section class="plans-section">
        <div class="plans-grid">
          <div 
            v-for="plan in plans" 
            :key="plan.id"
            class="plan-card"
            :class="{ 
              'popular-plan': plan.popular,
              'selected-plan': selectedPlan?.id === plan.id
            }"
            @click="selectPlan(plan)"
          >
            <!-- Popular badge -->
            <div v-if="plan.popular" class="popular-badge">
              <q-icon name="star" size="xs" />
              <span>Más popular</span>
            </div>

            <!-- Plan header -->
            <div class="plan-header">
              <div class="plan-name">{{ plan.name }}</div>
              <div class="plan-price">
                <span class="price-amount">{{ formatPrice(plan.price, plan.currency) }}</span>
                <span class="price-period">/{{ plan.interval === 'month' ? 'mes' : 'año' }}</span>
              </div>
              <div v-if="plan.originalPrice" class="original-price">
                <span class="text-strike text-grey-5">{{ formatPrice(plan.originalPrice, plan.currency) }}</span>
                <q-badge color="positive" text-color="white" class="q-ml-xs">
                  -{{ plan.discount }}%
                </q-badge>
              </div>
            </div>

            <!-- Plan features -->
            <div class="plan-features">
              <div 
                v-for="(feature, index) in plan.features" 
                :key="index"
                class="feature-item"
              >
                <q-icon name="check_circle" color="positive" size="sm" />
                <span>{{ feature }}</span>
              </div>
            </div>

            <!-- Selection indicator -->
            <div v-if="selectedPlan?.id === plan.id" class="selection-indicator">
              <q-icon name="radio_button_checked" color="primary" size="md" />
            </div>
            <div v-else class="selection-indicator">
              <q-icon name="radio_button_unchecked" color="grey-4" size="md" />
            </div>
          </div>
        </div>
      </q-card-section>

      <!-- Current subscription info -->
      <q-card-section v-if="hasActivePremium" class="current-subscription">
        <q-banner class="bg-positive text-white">
          <template v-slot:avatar>
            <q-icon name="verified" color="white" />
          </template>
          <div class="text-weight-medium">Suscripción Activa</div>
          <div class="text-caption">
            Tu plan {{ subscription.planType === 'monthly' ? 'mensual' : 'anual' }} 
            vence {{ formatExpirationDate() }}
          </div>
        </q-banner>
      </q-card-section>

      <!-- Loading and error states -->
      <q-card-section v-if="paymentsStore.isLoading" class="text-center">
        <q-spinner-dots size="lg" color="primary" />
        <div class="text-body2 q-mt-md">
          {{ paymentsStore.loading.payment ? 'Procesando pago...' : 'Cargando...' }}
        </div>
      </q-card-section>

      <q-card-section v-if="Object.keys(paymentsStore.activeErrors).length > 0">
        <q-banner class="bg-negative text-white">
          <template v-slot:avatar>
            <q-icon name="error" color="white" />
          </template>
          <div v-for="(error, type) in paymentsStore.activeErrors" :key="type">
            {{ error }}
          </div>
        </q-banner>
      </q-card-section>

      <!-- Actions -->
      <q-card-actions class="modal-actions" :class="{ 'mobile-actions': $q.screen.lt.sm }">
        <q-btn
          v-if="$q.screen.lt.sm"
          flat
          label="Cerrar"
          @click="closeModal"
          class="q-mr-sm"
        />

        <q-space v-if="!$q.screen.lt.sm" />

        <!-- MercadoPago Subscribe Button -->
        <div v-if="!hasActivePremium" class="mp-button-container">
          <a
            href="https://www.mercadopago.com.ar/subscriptions/checkout?preapproval_plan_id=ce3a6bac6a8146c8a784eb36c4b64e9d"
            name="MP-payButton"
            class="mp-subscribe-button"
            target="_blank"
            rel="noopener noreferrer"
          >
            Suscribirme
          </a>
        </div>

        <q-btn
          v-else
          flat
          color="negative"
          label="Cancelar Suscripción"
          :loading="paymentsStore.loading.subscription"
          @click="showCancelDialog = true"
          class="cancel-btn"
          no-caps
        />
      </q-card-actions>
    </q-card>

    <!-- Cancel subscription dialog -->
    <q-dialog v-model="showCancelDialog">
      <q-card class="cancel-dialog">
        <q-card-section>
          <div class="text-h6">Cancelar Suscripción</div>
        </q-card-section>

        <q-card-section>
          <p>¿Estás seguro que deseas cancelar tu suscripción Premium?</p>
          <p class="text-grey-6">
            Perderás acceso a todas las funciones premium al final del período actual.
          </p>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="No, mantener" @click="showCancelDialog = false" />
          <q-btn 
            unelevated 
            color="negative" 
            label="Sí, cancelar"
            :loading="paymentsStore.loading.subscription"
            @click="cancelSubscription"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-dialog>
</template>

<script>
import { usePaymentsStore } from '@/stores/payments';
import { computed, ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';

export default {
  name: 'PremiumPlansModal',
  props: {
    modelValue: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update:modelValue', 'subscription-success'],
  setup(props, { emit }) {
    const $q = useQuasar();
    const paymentsStore = usePaymentsStore();
    
    const selectedPlan = ref(null);
    const showCancelDialog = ref(false);

    // Computed properties
    const showModal = computed({
      get: () => props.modelValue,
      set: (value) => emit('update:modelValue', value)
    });

    const plans = computed(() => paymentsStore.plans);
    const hasActivePremium = computed(() => paymentsStore.hasActivePremium);
    const subscription = computed(() => paymentsStore.subscription);

    // Methods
    const formatPrice = (price, currency) => {
      return paymentsStore.formatPrice(price, currency);
    };

    const formatExpirationDate = () => {
      if (!subscription.value.expiresAt) return '';
      
      const date = new Date(subscription.value.expiresAt);
      return date.toLocaleDateString('es-AR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    };

    const selectPlan = (plan) => {
      if (!hasActivePremium.value) {
        selectedPlan.value = plan;
      }
    };

    const getActionButtonLabel = () => {
      if (!selectedPlan.value) return 'Selecciona un plan';
      return `Suscribirse - ${formatPrice(selectedPlan.value.price, selectedPlan.value.currency)}`;
    };

    const subscribeToPlan = async () => {
      if (!selectedPlan.value) return;

      try {
        const result = await paymentsStore.subscribeToPremium(selectedPlan.value.id);
        
        if (result.success) {
          $q.notify({
            type: 'positive',
            message: '¡Suscripción activada correctamente!',
            position: 'top'
          });
          
          emit('subscription-success');
          closeModal();
        } else if (result.pending) {
          $q.notify({
            type: 'warning',
            message: 'Pago pendiente de aprobación',
            position: 'top'
          });
        }
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: error.message || 'Error al procesar la suscripción',
          position: 'top'
        });
      }
    };

    const cancelSubscription = async () => {
      try {
        const result = await paymentsStore.cancelSubscription();
        
        if (result.success) {
          $q.notify({
            type: 'positive',
            message: 'Suscripción cancelada correctamente',
            position: 'top'
          });
          
          showCancelDialog.value = false;
          closeModal();
        }
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: error.message || 'Error al cancelar la suscripción',
          position: 'top'
        });
      }
    };

    const closeModal = () => {
      showModal.value = false;
      paymentsStore.clearAllErrors();
      paymentsStore.resetCurrentPayment();
    };

    // Lifecycle
    onMounted(async () => {
      // Temporalmente deshabilitado hasta implementar endpoints correctos
      // await paymentsStore.initialize();

      // Cargar planes localmente
      await paymentsStore.loadPlans();

      // Auto-select popular plan if user doesn't have premium
      if (!hasActivePremium.value && plans.value.length > 0) {
        selectedPlan.value = paymentsStore.popularPlan;
      }
    });

    return {
      paymentsStore,
      selectedPlan,
      showCancelDialog,
      showModal,
      plans,
      hasActivePremium,
      subscription,
      formatPrice,
      formatExpirationDate,
      selectPlan,
      getActionButtonLabel,
      subscribeToPlan,
      cancelSubscription,
      closeModal
    };
  }
}
</script>

<style scoped>
/* MercadoPago Button */
.mp-button-container {
  display: flex;
  justify-content: center;
  width: 100%;
}

.mp-subscribe-button {
  background-color: #3483FA;
  color: white;
  padding: 12px 32px;
  text-decoration: none;
  border-radius: 8px;
  display: inline-block;
  font-size: 16px;
  font-weight: 600;
  transition: background-color 0.3s, transform 0.2s;
  font-family: Arial, sans-serif;
  min-height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(52, 131, 250, 0.3);
}

.mp-subscribe-button:hover {
  background-color: #2a68c8;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52, 131, 250, 0.4);
}

.mp-subscribe-button:active {
  transform: translateY(0);
}

.premium-modal {
  min-width: 500px;
  max-width: 800px;
  border-radius: 16px;
  overflow: hidden;
}

.mobile-modal {
  min-width: 100vw;
  max-width: 100vw;
  height: 100vh;
  border-radius: 0;
}

/* Header styles */
.modal-header {
  background: linear-gradient(135deg, #176F46 0%, #1565c0 100%);
  color: white;
  padding: 24px;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-icon {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  padding: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Plans grid */
.plans-section {
  padding: 32px 24px;
}

.plans-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
}

.plan-card {
  position: relative;
  border: 2px solid #e5e7eb;
  border-radius: 16px;
  padding: 24px;
  cursor: pointer;
  transition: all 0.3s ease;
  background: white;
}

.plan-card:hover {
  border-color: #176F46;
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(25, 118, 210, 0.15);
}

.popular-plan {
  border-color: #ffa726;
  position: relative;
}

.selected-plan {
  border-color: #176F46;
  background: rgba(25, 118, 210, 0.05);
}

.popular-badge {
  position: absolute;
  top: -1px;
  right: 16px;
  background: linear-gradient(135deg, #ffa726 0%, #ff9800 100%);
  color: white;
  padding: 6px 12px;
  border-radius: 0 0 8px 8px;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}

/* Plan content */
.plan-header {
  text-align: center;
  margin-bottom: 24px;
}

.plan-name {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 8px;
  color: #1a202c;
}

.plan-price {
  margin-bottom: 8px;
}

.price-amount {
  font-size: 2rem;
  font-weight: 800;
  color: #176F46;
}

.price-period {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.original-price {
  display: flex;
  align-items: center;
  justify-content: center;
}

.plan-features {
  margin-bottom: 24px;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
  font-size: 0.875rem;
  color: #374151;
}

.selection-indicator {
  text-align: center;
}

/* Current subscription */
.current-subscription {
  padding: 16px 24px;
}

/* Actions */
.modal-actions {
  padding: 24px;
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
}

.mobile-actions {
  flex-direction: column;
  gap: 12px;
}

.action-btn {
  min-height: 48px;
  font-weight: 600;
  font-size: 1rem;
  border-radius: 8px;
}

.cancel-btn {
  min-height: 44px;
  border-radius: 8px;
}

.cancel-dialog {
  min-width: 400px;
  border-radius: 12px;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .modal-header {
    padding: 20px 16px;
  }
  
  .plans-section {
    padding: 24px 16px;
  }
  
  .plans-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .plan-card {
    padding: 20px;
  }
  
  .plan-name {
    font-size: 1.125rem;
  }
  
  .price-amount {
    font-size: 1.75rem;
  }
  
  .modal-actions {
    padding: 16px;
  }
  
  .action-btn {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .header-content {
    gap: 12px;
  }
  
  .header-icon {
    padding: 8px;
  }
  
  .plans-section {
    padding: 16px 12px;
  }
  
  .plan-card {
    padding: 16px;
  }
  
  .cancel-dialog {
    min-width: 90vw;
  }
}

/* Dark mode support */
.body--dark .premium-modal {
  background-color: #1f2937;
}

.body--dark .plan-card {
  background-color: #374151;
  border-color: #4b5563;
}

.body--dark .plan-card:hover {
  border-color: #60a5fa;
}

.body--dark .selected-plan {
  background-color: rgba(96, 165, 250, 0.1);
  border-color: #60a5fa;
}

.body--dark .plan-name {
  color: #f9fafb;
}

.body--dark .feature-item {
  color: #d1d5db;
}

.body--dark .modal-actions {
  background-color: #111827;
  border-color: #374151;
}

/* Touch improvements */
@media (hover: none) and (pointer: coarse) {
  .plan-card:hover {
    transform: none;
  }
  
  .plan-card:active {
    transform: scale(0.98);
  }
  
  .action-btn,
  .cancel-btn {
    min-height: 48px;
    touch-action: manipulation;
  }
}

/* Animation for modal entrance */
.premium-modal {
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}
</style>