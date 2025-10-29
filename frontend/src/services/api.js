
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';
import router from '@/router';
import { Notify } from 'quasar';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ,
  withCredentials: false,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Estado global para el manejo de rate limiting
let rateLimitState = {
  isThrottled: false,
  retryQueue: [],
  activeNotification: null
};

// Interceptor para añadir el token a todas las peticiones
api.interceptors.request.use(config => {
  // Buscar token en localStorage primero, luego en sessionStorage
  const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  
  
  return config;
});

// Función para manejar rate limiting con retry automático
async function handleRateLimit(originalConfig) {
  return new Promise((resolve, reject) => {
    // Si ya estamos en estado throttled, añadir a la cola
    if (rateLimitState.isThrottled) {
      rateLimitState.retryQueue.push({ resolve, reject, config: originalConfig });
      return;
    }

    // Marcar como throttled
    rateLimitState.isThrottled = true;

    // Mostrar notificación de carga una sola vez
    if (!rateLimitState.activeNotification) {
      rateLimitState.activeNotification = Notify.create({
        type: 'ongoing',
        message: 'Cargando datos...',
        spinner: true,
        timeout: 0, // No auto-dismiss
        position: 'top'
      });
    }

    // Función de retry con backoff exponencial
    const attemptRetry = (attempt = 1) => {
      const delay = Math.min(1000 * Math.pow(2, attempt - 1), 10000); // Max 10 segundos

      setTimeout(async () => {
        try {
          const response = await api(originalConfig);

          // Éxito: limpiar estado y procesar cola
          rateLimitState.isThrottled = false;

          // Cerrar notificación
          if (rateLimitState.activeNotification) {
            rateLimitState.activeNotification();
            rateLimitState.activeNotification = null;
          }

          // Procesar cola de peticiones pendientes
          const queue = [...rateLimitState.retryQueue];
          rateLimitState.retryQueue = [];

          queue.forEach(({ resolve: queueResolve, config }) => {
            api(config).then(queueResolve).catch(() => {
              // Si fallan las peticiones de la cola, las reintentamos individualmente
              handleRateLimit(config).then(queueResolve);
            });
          });

          resolve(response);
        } catch (retryError) {
          if (retryError.response?.status === 429 && attempt < 5) {
            // Reintentar con mayor delay
            attemptRetry(attempt + 1);
          } else {
            // Fallo definitivo
            rateLimitState.isThrottled = false;

            if (rateLimitState.activeNotification) {
              rateLimitState.activeNotification();
              rateLimitState.activeNotification = null;
            }

            // Mostrar error
            Notify.create({
              type: 'negative',
              message: 'Error de conexión. Intenta recargar la página.',
              timeout: 3000,
              position: 'top'
            });

            reject(retryError);
          }
        }
      }, delay);
    };

    attemptRetry();
  });
}

// Interceptor para manejar la respuesta
api.interceptors.response.use(
  response => {
    return response;
  },
  async error => {
    // Manejar errores 429 (Too Many Requests)
    if (error.response?.status === 429) {
      console.warn('⚠️ Rate limit detectado, activando retry automático...');
      return handleRateLimit(error.config);
    }

    // Si el error es 401 (no autorizado), limpiar la autenticación y redireccionar
    // EXCEPTO si es una llamada a /logout (para evitar bucles infinitos)
    if (error.response &&
        error.response.status === 401 &&
        !error.config.url.includes('/logout')) {
      console.warn('Error 401: Token no válido o expirado, redirigiendo al login...');

      const authStore = useAuthStore();
      await authStore.logout(false); // logout automático por token expirado

      // Redireccionar al login solo si no estamos ya en la página de login
      if (router.currentRoute.value.path !== '/login') {
        router.push({
          path: '/login',
          query: {
            redirect: router.currentRoute.value.fullPath,
            message: 'Tu sesión ha expirado, por favor inicia sesión nuevamente'
          }
        });
      }
    }

    return Promise.reject(error);
  }
);

//sacar el cargando para loas ordenes- y ponerlos para notas y alarmas
export const ordenElementos = async (token, elementos) => {
  try {
    const response = await api.post('/elementos/updateOrder', { elementos }, {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    });
    return response.data;
  } catch (error) {
    console.error('Error al actualizar el orden:', error);
    throw error;
  }
};

export const obtenerCalendarios = async (token) => {
  const response = await api.get('/usuarios/calendarios', {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });
  // console.log(response.data);
  return response.data;
};

export const obtenerEventos = async (token, calendarioId) => {
  const response = await api.get(`/usuarios/${calendarioId}/eventos`, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });
  return response.data;
};

export const obtenerElementos = async (token) => {
  // console.log("token - obtenerDatosElemento: " + token);

  const response = await api.get('/usuarios/elementos', {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });
  // console.log("respuesta endpoind : " + response.data);
  return response.data;
};

export const obtenerAlarmas = async (token) => {
  const response = await api.get('/usuarios/alarmas', {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });
  return response.data;
};

export const obtenerNotas = async (token) => {
  const response = await api.get('/usuarios/notas', {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });
  return response.data;
};

export const obtenerObjetivos = async (token) => {
  const response = await api.get('/usuarios/objetivos', {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });
  return response.data;
};


export const obtenerMetas = async (token, id) => {
  const response = await api.get(`/usuarios/${id}/metas`, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });
  return response.data;
};

export const obtenerDatosElemento = async (token, id) => {
  const response = await api.get(`/usuarios/elemento/${id}`, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });
  return response.data;
};






// En tu archivo de servicios (src/services/api.js)
export const guardarElemento = async (token,tipo,elementoData) => {
  try {
    const url = `/elementos/saveUpdate`; // Usar template string para el ID opcional
    const response = await api.post(url, {
      ...elementoData,
      tipo: tipo
    }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });
    return response.data;
  } catch (error) {
    throw error;
  }
};



export const eliminarElemento = async (token, id) => {
  try {
    const response = await api.post(`/elementos/eliminarElemento/${id}`,{}, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
    });
    return response.data;
  } catch (error) {
    console.error('Error al eliminar el elemento:', error);
    throw error;
  }
}


export const login = async (email,password) => {
  try {
    const response = await api.post(`/login`,{
          email: email,
          password: password,
    });
    return response;
  } catch (error) {
    console.error('Error al realizar login:', error);
    throw error;
  }
}

// Auth API methods para el store
const authAPI = {
  login: async (credentials) => {
    const response = await api.post('/login', credentials);
    return response;
  },

  register: async (userData) => {
    const response = await api.post('/register', userData);
    return response;
  },

  logout: async () => {
    try {
      const response = await api.post('/logout');
      return response;
    } catch (error) {
      // Si es 401, el token ya no es válido (comportamiento esperado)
      if (error.response?.status === 401) {
        console.info('Logout: token ya inválido, logout local exitoso');
        return { data: { message: 'Logout local exitoso' } };
      }
      throw error;
    }
  },

  getUserData: async () => {
    const response = await api.get('/user');
    return response;
  },

  validateToken: async () => {
    const response = await api.get('/validate-token');
    return response;
  },

  refreshToken: async () => {
    const response = await api.post('/refresh-token');
    return response;
  },

  getUserSessions: async () => {
    const response = await api.get('/sessions');
    return response;
  },

  revokeSession: async (tokenId) => {
    const response = await api.delete(`/sessions/${tokenId}`);
    return response;
  },

  googleLogin: async (tokenData) => {
    const response = await api.post('/auth/google-login', tokenData);
    return response;
  }
};

// API methods específicos para alarmas
const alarmsAPI = {
  getAll: async () => {
    const token = localStorage.getItem('auth_token');
    return await obtenerAlarmas(token);
  },
  create: async (alarmData) => {
    const token = localStorage.getItem('auth_token');
    return await guardarElemento(token, 'alarma', alarmData);
  },
  update: async (alarmData) => {
    const token = localStorage.getItem('auth_token');
    return await guardarElemento(token, 'alarma', alarmData);
  },
  delete: async (id) => {
    const token = localStorage.getItem('auth_token');
    return await eliminarElemento(token, id);
  }
};

// API methods específicos para objetivos
const objectivesAPI = {
  getAll: async () => {
    const token = localStorage.getItem('auth_token');
    return await obtenerObjetivos(token);
  },
  create: async (objectiveData) => {
    const token = localStorage.getItem('auth_token');
    return await guardarElemento(token, 'objetivo', objectiveData);
  },
  update: async (objectiveData) => {
    const token = localStorage.getItem('auth_token');
    return await guardarElemento(token, 'objetivo', objectiveData);
  },
  delete: async (id) => {
    const token = localStorage.getItem('auth_token');
    return await eliminarElemento(token, id);
  }
};

// API methods específicos para notas
const notesAPI = {
  getAll: async () => {
    const token = localStorage.getItem('auth_token');
    return await obtenerNotas(token);
  },
  create: async (noteData) => {
    const token = localStorage.getItem('auth_token');
    return await guardarElemento(token, 'nota', noteData);
  },
  update: async (noteData) => {
    const token = localStorage.getItem('auth_token');
    return await guardarElemento(token, 'nota', noteData);
  },
  delete: async (id) => {
    const token = localStorage.getItem('auth_token');
    return await eliminarElemento(token, id);
  }
};

// API methods específicos para calendarios
const calendarsAPI = {
  getAll: async () => {
    const token = localStorage.getItem('auth_token');
    return await obtenerCalendarios(token);
  },
  getEvents: async (calendarId) => {
    const token = localStorage.getItem('auth_token');
    return await obtenerEventos(token, calendarId);
  }
};

// API methods específicos para elementos
const elementsAPI = {
  getAll: async () => {
    const token = localStorage.getItem('auth_token');
    return await obtenerElementos(token);
  },
  getById: async (id) => {
    const token = localStorage.getItem('auth_token');
    return await obtenerDatosElemento(token, id);
  },
  updateOrder: async (elements) => {
    const token = localStorage.getItem('auth_token');
    return await ordenElementos(token, elements);
  }
};

// API methods específicos para usuarios
const userAPI = {
  getProfile: async () => {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    const response = await api.get('/usuarios/profile', {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },
  updateProfile: async (profileData) => {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    const response = await api.put('/usuarios/profile', profileData, {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },
  getSettings: async () => {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    const response = await api.get('/usuarios/settings', {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },
  updateSettings: async (settingsData) => {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    const response = await api.put('/usuarios/settings', settingsData, {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },
  sendVerification: async () => {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    const response = await api.post('/email/verification-notification', {}, {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },
  getVerificationStatus: async () => {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    const response = await api.get('/email/verification-status', {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },
  changePassword: async (passwordData) => {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    const response = await api.put('/usuarios/password', passwordData, {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  }
};

// API methods específicos para notificaciones
const notificationsAPI = {
  getSettings: async () => {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    const response = await api.get('/usuarios/notifications/settings', {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },
  updateSettings: async (notificationSettings) => {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    const response = await api.put('/usuarios/notifications/settings', notificationSettings, {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },
  getHistory: async (params = {}) => {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    const response = await api.get('/usuarios/notifications/history', {
      headers: { Authorization: `Bearer ${token}` },
      params
    });
    return response.data;
  },
  registerDevice: async (deviceData) => {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    const response = await api.post('/usuarios/notifications/device', deviceData, {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },
  unregisterDevice: async (deviceToken) => {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    const response = await api.delete(`/usuarios/notifications/device/${deviceToken}`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },
  sendTest: async (testData) => {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    const response = await api.post('/usuarios/notifications/test', testData, {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  }
};

// Exportar API con estructura esperada por el store
const apiWithAuth = {
  ...api,
  auth: authAPI,
  alarms: alarmsAPI,
  objectives: objectivesAPI,
  notes: notesAPI,
  calendars: calendarsAPI,
  elements: elementsAPI,
  user: userAPI,
  notifications: notificationsAPI
};

export default apiWithAuth;