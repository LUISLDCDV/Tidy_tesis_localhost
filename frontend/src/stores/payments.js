/**
 * Store de Pinia para manejar pagos y suscripciones
 */
import { defineStore } from 'pinia';
import mercadoPagoService from '@/services/mercadoPagoService';
import { useAuthStore } from './auth';

export const usePaymentsStore = defineStore('payments', {
  state: () => ({
    // Estado de suscripción del usuario
    subscription: {
      isPremium: false,
      planType: null,
      expiresAt: null,
      status: 'inactive',
      paymentMethod: null
    },
    
    // Planes disponibles
    plans: [],
    
    // Estado de carga y errores
    loading: {
      subscription: false,
      payment: false,
      plans: false
    },
    
    errors: {
      subscription: null,
      payment: null,
      plans: null
    },
    
    // Historial de pagos
    paymentHistory: [],
    
    // Estado del proceso de pago actual
    currentPayment: {
      preferenceId: null,
      status: null,
      processing: false
    }
  }),

  getters: {
    /**
     * Verifica si el usuario tiene una suscripción activa
     */
    hasActivePremium: (state) => {
      return state.subscription.isPremium && 
             state.subscription.status === 'active' &&
             (!state.subscription.expiresAt || new Date(state.subscription.expiresAt) > new Date());
    },

    /**
     * Obtiene el plan más popular
     */
    popularPlan: (state) => {
      return state.plans.find(plan => plan.popular) || state.plans[0];
    },

    /**
     * Verifica si hay algún proceso de carga activo
     */
    isLoading: (state) => {
      return Object.values(state.loading).some(loading => loading);
    },

    /**
     * Obtiene errores activos
     */
    activeErrors: (state) => {
      return Object.entries(state.errors)
        .filter(([key, error]) => error !== null)
        .reduce((acc, [key, error]) => ({ ...acc, [key]: error }), {});
    },

    /**
     * Calcula días restantes de la suscripción
     */
    daysRemaining: (state) => {
      if (!state.subscription.expiresAt) return null;
      
      const now = new Date();
      const expiresAt = new Date(state.subscription.expiresAt);
      const diffTime = expiresAt - now;
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      
      return diffDays > 0 ? diffDays : 0;
    },

    /**
     * Formatea el precio para mostrar
     */
    formatPrice: () => (price, currency = 'ARS') => {
      return mercadoPagoService.formatPrice(price, currency);
    }
  },

  actions: {
    /**
     * Inicializa el store cargando datos del usuario
     */
    async initialize() {
      await Promise.all([
        this.loadPlans(),
        this.loadUserSubscription()
      ]);
    },

    /**
     * Carga los planes de suscripción disponibles
     */
    async loadPlans() {
      this.loading.plans = true;
      this.errors.plans = null;

      try {
        this.plans = mercadoPagoService.getPremiumPlans();
      } catch (error) {
        console.error('Error al cargar planes:', error);
        this.errors.plans = 'Error al cargar los planes de suscripción';
      } finally {
        this.loading.plans = false;
      }
    },

    /**
     * Carga el estado de suscripción del usuario actual
     */
    async loadUserSubscription() {
      const authStore = useAuthStore();
      if (!authStore.user?.id) return;

      this.loading.subscription = true;
      this.errors.subscription = null;

      try {
        // Importar API dinámicamente para evitar problemas de dependencias circulares
        const api = (await import('@/services/api')).default;

        // Obtener datos del usuario incluyendo estado premium
        const response = await api.get('/user-data');

        if (response.data && response.data.user) {
          const userData = response.data.user;

          // Actualizar estado de suscripción basado en datos del backend
          this.subscription = {
            isPremium: userData.is_premium || false,
            planType: userData.is_premium ? 'monthly' : null, // Por ahora asumimos mensual si es premium
            expiresAt: userData.premium_expires_at || null,
            status: this.determineStatus(userData.is_premium, userData.premium_expires_at),
            paymentMethod: null // No disponible en este endpoint
          };
        }
      } catch (error) {
        console.error('Error al cargar suscripción:', error);
        this.errors.subscription = 'Error al cargar el estado de la suscripción';
      } finally {
        this.loading.subscription = false;
      }
    },

    /**
     * Determina el estado de la suscripción basado en los datos del backend
     */
    determineStatus(isPremium, expiresAt) {
      if (!isPremium) return 'inactive';
      if (!expiresAt) return 'inactive';

      const expirationDate = new Date(expiresAt);
      const now = new Date();

      if (expirationDate > now) {
        return 'active';
      } else {
        return 'expired';
      }
    },

    /**
     * Inicia el proceso de suscripción premium
     * @param {string} planType - Tipo de plan (monthly, annual)
     */
    async subscribeToPremium(planType) {
      const authStore = useAuthStore();
      if (!authStore.user) {
        throw new Error('Usuario no autenticado');
      }

      this.loading.payment = true;
      this.errors.payment = null;
      this.currentPayment.processing = true;

      try {
        // Crear preferencia de pago
        const preferenceId = await mercadoPagoService.createPremiumSubscription({
          planType,
          userId: authStore.user.id,
          email: authStore.user.email,
          name: authStore.user.name
        });

        this.currentPayment.preferenceId = preferenceId;

        // Procesar pago
        const paymentResult = await mercadoPagoService.processPayment(preferenceId);
        
        this.currentPayment.status = paymentResult.status;

        if (paymentResult.status === 'approved') {
          // Actualizar estado de suscripción
          await this.loadUserSubscription();
          
          return {
            success: true,
            paymentId: paymentResult.paymentId,
            message: 'Suscripción activada correctamente'
          };
        } else if (paymentResult.status === 'pending') {
          return {
            success: false,
            pending: true,
            message: 'Pago pendiente de aprobación'
          };
        } else {
          throw new Error('Pago rechazado o cancelado');
        }

      } catch (error) {
        console.error('Error en suscripción premium:', error);
        this.errors.payment = error.message || 'Error al procesar el pago';
        throw error;
      } finally {
        this.loading.payment = false;
        this.currentPayment.processing = false;
      }
    },

    /**
     * Cancela la suscripción actual
     */
    async cancelSubscription() {
      const authStore = useAuthStore();
      if (!authStore.user?.id) {
        throw new Error('Usuario no autenticado');
      }

      this.loading.subscription = true;
      this.errors.subscription = null;

      try {
        const success = await mercadoPagoService.cancelSubscription(authStore.user.id);
        
        if (success) {
          // Actualizar estado local
          this.subscription.status = 'cancelled';
          await this.loadUserSubscription();
          
          return {
            success: true,
            message: 'Suscripción cancelada correctamente'
          };
        } else {
          throw new Error('No se pudo cancelar la suscripción');
        }

      } catch (error) {
        console.error('Error al cancelar suscripción:', error);
        this.errors.subscription = error.message || 'Error al cancelar la suscripción';
        throw error;
      } finally {
        this.loading.subscription = false;
      }
    },

    /**
     * Verifica el estado de un pago específico
     * @param {string} preferenceId - ID de la preferencia
     */
    async verifyPaymentStatus(preferenceId) {
      try {
        const paymentStatus = await mercadoPagoService.verifyPaymentStatus(preferenceId);
        
        if (paymentStatus.status === 'approved') {
          await this.loadUserSubscription();
        }

        return paymentStatus;
      } catch (error) {
        console.error('Error al verificar estado del pago:', error);
        throw error;
      }
    },

    /**
     * Carga el historial de pagos del usuario
     */
    async loadPaymentHistory() {
      const authStore = useAuthStore();
      if (!authStore.user?.id) return;

      try {
        // Aquí se implementaría la llamada a la API para obtener el historial
        // Por ahora simulamos datos
        this.paymentHistory = [
          {
            id: 1,
            date: new Date(),
            amount: 999,
            currency: 'ARS',
            status: 'approved',
            planType: 'monthly',
            paymentMethod: 'credit_card'
          }
        ];
      } catch (error) {
        console.error('Error al cargar historial de pagos:', error);
      }
    },

    /**
     * Limpia errores específicos
     * @param {string} errorType - Tipo de error a limpiar
     */
    clearError(errorType) {
      if (this.errors[errorType]) {
        this.errors[errorType] = null;
      }
    },

    /**
     * Limpia todos los errores
     */
    clearAllErrors() {
      Object.keys(this.errors).forEach(key => {
        this.errors[key] = null;
      });
    },

    /**
     * Reinicia el estado del pago actual
     */
    resetCurrentPayment() {
      this.currentPayment = {
        preferenceId: null,
        status: null,
        processing: false
      };
    },

    /**
     * Actualiza el estado de suscripción local (para uso de webhooks)
     * @param {Object} subscriptionData - Datos de suscripción actualizados
     */
    updateSubscriptionStatus(subscriptionData) {
      this.subscription = { ...this.subscription, ...subscriptionData };
    },

    /**
     * Simula una suscripción premium para testing
     */
    simulatePremiumSubscription() {
      const nextMonth = new Date();
      nextMonth.setMonth(nextMonth.getMonth() + 1);

      this.subscription = {
        isPremium: true,
        planType: 'monthly',
        expiresAt: nextMonth.toISOString(),
        status: 'active',
        paymentMethod: 'credit_card'
      };
    },

    /**
     * Limpia la suscripción simulada
     */
    clearSimulatedSubscription() {
      this.subscription = {
        isPremium: false,
        planType: null,
        expiresAt: null,
        status: 'inactive',
        paymentMethod: null
      };
    }
  }
});