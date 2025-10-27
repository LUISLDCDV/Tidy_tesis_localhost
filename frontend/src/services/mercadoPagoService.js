/**
 * Servicio para integración con Mercado Pago
 * Maneja suscripciones premium y pagos únicos
 */

class MercadoPagoService {
  constructor() {
    this.publicKey = import.meta.env.VITE_MERCADOPAGO_PUBLIC_KEY || 'TEST-your-public-key';
    this.baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
    this.mp = null;
    this.initialized = false;
  }

  /**
   * Helper function to get auth token from both storages (consistent with API interceptor)
   */
  getAuthToken() {
    return localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
  }

  /**
   * Inicializa el SDK de Mercado Pago
   */
  async initialize() {
    if (this.initialized) return;

    try {
      // Cargar el SDK de Mercado Pago dinámicamente
      if (!window.MercadoPago) {
        await this.loadSDK();
      }

      this.mp = new window.MercadoPago(this.publicKey, {
        locale: 'es-AR'
      });

      this.initialized = true;
      console.log('MercadoPago SDK inicializado correctamente');
    } catch (error) {
      console.error('Error al inicializar MercadoPago SDK:', error);
      throw new Error('No se pudo inicializar el sistema de pagos');
    }
  }

  /**
   * Carga el SDK de Mercado Pago dinámicamente
   */
  loadSDK() {
    return new Promise((resolve, reject) => {
      if (window.MercadoPago) {
        resolve();
        return;
      }

      const script = document.createElement('script');
      script.src = 'https://sdk.mercadopago.com/js/v2';
      script.onload = resolve;
      script.onerror = reject;
      document.head.appendChild(script);
    });
  }

  /**
   * Crea una preferencia de pago para suscripción premium
   * @param {Object} subscriptionData - Datos de la suscripción
   * @returns {Promise<string>} - ID de la preferencia
   */
  async createPremiumSubscription(subscriptionData) {
    await this.initialize();

    try {
      const token = this.getAuthToken();

      if (!token) {
        throw new Error('No hay token de autenticación');
      }

      const response = await fetch(`${this.baseURL}/payments/create-premium-subscription`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          planType: subscriptionData.planType || 'monthly',
          userId: subscriptionData.userId,
          email: subscriptionData.email,
          name: subscriptionData.name,
          successUrl: `${window.location.origin}/payment/success`,
          failureUrl: `${window.location.origin}/payment/failure`,
          pendingUrl: `${window.location.origin}/payment/pending`
        })
      });

      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`);
      }

      const data = await response.json();
      
      if (data.success && data.preferenceId) {
        return data.preferenceId;
      } else {
        throw new Error(data.message || 'Error al crear la preferencia de pago');
      }
    } catch (error) {
      console.error('Error al crear suscripción premium:', error);
      throw error;
    }
  }

  /**
   * Procesa el pago usando Checkout Pro
   * @param {string} preferenceId - ID de la preferencia de MercadoPago
   */
  async processPayment(preferenceId) {
    await this.initialize();

    try {
      const checkout = this.mp.checkout({
        preference: {
          id: preferenceId
        },
        autoOpen: true,
        theme: {
          elementsColor: '#176F46',
          headerColor: '#176F46'
        }
      });

      return new Promise((resolve, reject) => {
        // Escuchar eventos del checkout
        checkout.open();
        
        // Simular resolución (en producción, esto se maneja con webhooks)
        const checkPaymentStatus = setInterval(() => {
          // En un escenario real, verificarías el estado del pago via API
          // Por ahora simulamos que el usuario puede cerrar el checkout
          if (document.querySelector('.mercadopago-checkout-wrapper') === null) {
            clearInterval(checkPaymentStatus);
            // Verificar estado del pago
            this.verifyPaymentStatus(preferenceId)
              .then(resolve)
              .catch(reject);
          }
        }, 1000);

        // Timeout después de 10 minutos
        setTimeout(() => {
          clearInterval(checkPaymentStatus);
          reject(new Error('Timeout en el proceso de pago'));
        }, 600000);
      });
    } catch (error) {
      console.error('Error al procesar el pago:', error);
      throw error;
    }
  }

  /**
   * Verifica el estado de un pago
   * @param {string} preferenceId - ID de la preferencia
   * @returns {Promise<Object>} - Estado del pago
   */
  async verifyPaymentStatus(preferenceId) {
    try {
      const token = this.getAuthToken();

      if (!token) {
        throw new Error('No hay token de autenticación');
      }

      const response = await fetch(`${this.baseURL}/payments/status/${preferenceId}`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });

      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`);
      }

      const data = await response.json();
      return {
        status: data.status, // approved, pending, rejected
        paymentId: data.paymentId,
        detail: data.detail
      };
    } catch (error) {
      console.error('Error al verificar estado del pago:', error);
      throw error;
    }
  }

  /**
   * Obtiene los planes de suscripción disponibles
   * @returns {Array} - Lista de planes
   */
  getPremiumPlans() {
    return [
      {
        id: 'monthly',
        name: 'Premium Mensual',
        price: 999, // En centavos (ARS $9.99)
        currency: 'ARS',
        interval: 'month',
        features: [
          'Acceso a todos los tipos de notas',
          'Sincronización ilimitada',
          'Soporte prioritario',
          'Funciones avanzadas de exportación',
          'Temas premium',
          'Sin límites de almacenamiento'
        ],
        popular: false
      },
      {
        id: 'annual',
        name: 'Premium Anual',
        price: 9999, // En centavos (ARS $99.99)
        originalPrice: 11988, // Precio mensual * 12
        currency: 'ARS',
        interval: 'year',
        features: [
          'Acceso a todos los tipos de notas',
          'Sincronización ilimitada',
          'Soporte prioritario',
          'Funciones avanzadas de exportación',
          'Temas premium',
          'Sin límites de almacenamiento',
          '2 meses gratis',
          'Descuentos en futuras funciones'
        ],
        popular: true,
        discount: 17 // Porcentaje de descuento
      }
    ];
  }

  /**
   * Formatea precio para mostrar
   * @param {number} price - Precio en centavos
   * @param {string} currency - Moneda
   * @returns {string} - Precio formateado
   */
  formatPrice(price, currency = 'ARS') {
    const formatter = new Intl.NumberFormat('es-AR', {
      style: 'currency',
      currency: currency,
      minimumFractionDigits: 2
    });

    return formatter.format(price / 100);
  }

  /**
   * Obtiene el estado de suscripción del usuario
   * @param {number} userId - ID del usuario
   * @returns {Promise<Object>} - Estado de la suscripción
   */
  async getUserSubscriptionStatus(userId) {
    try {
      const token = this.getAuthToken();

      if (!token) {
        throw new Error('No hay token de autenticación');
      }

      const response = await fetch(`${this.baseURL}/users/${userId}/subscription`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });

      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`);
      }

      const data = await response.json();
      return {
        isPremium: data.isPremium || false,
        planType: data.planType || null,
        expiresAt: data.expiresAt || null,
        status: data.status || 'inactive', // active, inactive, cancelled, expired
        paymentMethod: data.paymentMethod || null
      };
    } catch (error) {
      console.error('Error al obtener estado de suscripción:', error);
      return {
        isPremium: false,
        planType: null,
        expiresAt: null,
        status: 'inactive',
        paymentMethod: null
      };
    }
  }

  /**
   * Cancela una suscripción
   * @param {number} userId - ID del usuario
   * @returns {Promise<boolean>} - Éxito de la cancelación
   */
  async cancelSubscription(userId) {
    try {
      const token = this.getAuthToken();

      if (!token) {
        throw new Error('No hay token de autenticación');
      }

      const response = await fetch(`${this.baseURL}/users/${userId}/subscription/cancel`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });

      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`);
      }

      const data = await response.json();
      return data.success || false;
    } catch (error) {
      console.error('Error al cancelar suscripción:', error);
      throw error;
    }
  }
}

// Singleton
const mercadoPagoService = new MercadoPagoService();
export default mercadoPagoService;