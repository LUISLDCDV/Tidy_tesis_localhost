/**
 * Frontend Logger - Sistema de logging para enviar errores al backend
 *
 * Uso:
 * Logger.error('Mensaje de error', { datos: 'adicionales' });
 * Logger.warning('Mensaje de advertencia');
 * Logger.info('Mensaje informativo');
 * Logger.performance('page_load', 1234, 'ms');
 * Logger.event('user_click', 'navigation', { button: 'login' });
 */

class FrontendLogger {
    constructor(options = {}) {
        this.baseUrl = options.baseUrl || '/api';
        this.batchSize = options.batchSize || 10;
        this.batchTimeout = options.batchTimeout || 5000; // 5 segundos
        this.maxRetries = options.maxRetries || 3;
        this.source = options.source || 'frontend';

        this.logQueue = [];
        this.batchTimer = null;
        this.sessionId = this.generateSessionId();

        // Configurar captura automática de errores
        if (options.autoCapture !== false) {
            this.setupAutoCapture();
        }

        // Procesar cola de logs offline al recuperar conexión
        this.setupOfflineSync();
    }

    /**
     * Generar ID único de sesión
     */
    generateSessionId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }

    /**
     * Configurar captura automática de errores
     */
    setupAutoCapture() {
        // Capturar errores JavaScript no manejados
        window.addEventListener('error', (event) => {
            this.error('JavaScript Error: ' + event.message, {
                component: 'global',
                action: 'unhandled_error',
                error_code: 'JS_ERROR',
                url: event.filename,
                line_number: event.lineno,
                stack_trace: event.error?.stack
            });
        });

        // Capturar promesas rechazadas no manejadas
        window.addEventListener('unhandledrejection', (event) => {
            this.error('Unhandled Promise Rejection: ' + event.reason, {
                component: 'global',
                action: 'unhandled_promise_rejection',
                error_code: 'PROMISE_REJECTION',
                stack_trace: event.reason?.stack
            });
        });

        // Capturar errores de recursos (imágenes, scripts, etc.)
        window.addEventListener('error', (event) => {
            if (event.target !== window) {
                this.warning('Resource Load Error: ' + event.target.src || event.target.href, {
                    component: 'resource_loader',
                    action: 'resource_load_failed',
                    error_code: 'RESOURCE_ERROR',
                    url: event.target.src || event.target.href
                });
            }
        }, true);
    }

    /**
     * Configurar sincronización offline
     */
    setupOfflineSync() {
        // Detectar cuando vuelve la conexión
        window.addEventListener('online', () => {
            this.syncOfflineLogs();
        });

        // Cargar logs offline al inicializar
        this.loadOfflineLogs();
    }

    /**
     * Log de error
     */
    error(message, additionalData = {}) {
        this.log('error', message, additionalData);
    }

    /**
     * Log de advertencia
     */
    warning(message, additionalData = {}) {
        this.log('warning', message, additionalData);
    }

    /**
     * Log informativo
     */
    info(message, additionalData = {}) {
        this.log('info', message, additionalData);
    }

    /**
     * Log de debug
     */
    debug(message, additionalData = {}) {
        this.log('debug', message, additionalData);
    }

    /**
     * Log de métrica de rendimiento
     */
    performance(metricName, value, unit = 'ms', additionalData = {}) {
        const performanceData = {
            metric_name: metricName,
            metric_value: value,
            metric_unit: unit,
            ...additionalData
        };

        this.sendLog('/logs/performance', performanceData);
    }

    /**
     * Log de evento de usuario
     */
    event(eventName, category = 'user_interaction', eventData = {}) {
        const event = {
            event_name: eventName,
            event_category: category,
            event_data: eventData,
            session_id: this.sessionId
        };

        this.sendLog('/logs/event', event);
    }

    /**
     * Log genérico
     */
    log(level, message, additionalData = {}) {
        const logEntry = {
            message: message,
            level: level,
            source: this.source,
            timestamp: new Date().toISOString(),
            url: window.location.href,
            user_agent: navigator.userAgent,
            ...additionalData
        };

        // Agregar a la cola para envío en lote
        this.logQueue.push(logEntry);

        // Configurar timer para envío en lote
        if (this.batchTimer) {
            clearTimeout(this.batchTimer);
        }

        // Enviar inmediatamente si es error crítico o si la cola está llena
        if (level === 'error' || this.logQueue.length >= this.batchSize) {
            this.flushLogs();
        } else {
            this.batchTimer = setTimeout(() => {
                this.flushLogs();
            }, this.batchTimeout);
        }
    }

    /**
     * Enviar logs en lote
     */
    async flushLogs() {
        if (this.logQueue.length === 0) return;

        const logsToSend = [...this.logQueue];
        this.logQueue = [];

        if (this.batchTimer) {
            clearTimeout(this.batchTimer);
            this.batchTimer = null;
        }

        try {
            if (navigator.onLine) {
                await this.sendLogBatch(logsToSend);
            } else {
                // Guardar para envío offline
                this.saveLogsOffline(logsToSend);
            }
        } catch (error) {
            console.error('Error sending logs:', error);
            // Guardar para reintento
            this.saveLogsOffline(logsToSend);
        }
    }

    /**
     * Enviar lote de logs al backend
     */
    async sendLogBatch(logs) {
        const endpoint = this.getAuthenticatedEndpoint('/logs/batch');

        return fetch(this.baseUrl + endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...this.getAuthHeaders()
            },
            body: JSON.stringify({ logs })
        });
    }

    /**
     * Enviar log individual
     */
    async sendLog(endpoint, data) {
        try {
            if (!navigator.onLine) {
                this.saveLogsOffline([data]);
                return;
            }

            const authEndpoint = this.getAuthenticatedEndpoint(endpoint);

            const response = await fetch(this.baseUrl + authEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...this.getAuthHeaders()
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

        } catch (error) {
            console.error('Error sending individual log:', error);
            this.saveLogsOffline([data]);
        }
    }

    /**
     * Obtener endpoint autenticado si hay token disponible
     */
    getAuthenticatedEndpoint(endpoint) {
        const token = this.getAuthToken();
        return token ? '/authenticated' + endpoint : endpoint;
    }

    /**
     * Obtener headers de autenticación
     */
    getAuthHeaders() {
        const token = this.getAuthToken();
        return token ? { 'Authorization': `Bearer ${token}` } : {};
    }

    /**
     * Obtener token de autenticación
     */
    getAuthToken() {
        // Implementar según tu sistema de autenticación
        return localStorage.getItem('auth_token') ||
               sessionStorage.getItem('auth_token') ||
               this.getCookie('auth_token');
    }

    /**
     * Obtener cookie
     */
    getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    /**
     * Guardar logs offline
     */
    saveLogsOffline(logs) {
        try {
            const offlineLogs = this.getOfflineLogs();
            offlineLogs.push(...logs);
            localStorage.setItem('frontend_logs_offline', JSON.stringify(offlineLogs));
        } catch (error) {
            console.error('Error saving logs offline:', error);
        }
    }

    /**
     * Obtener logs offline guardados
     */
    getOfflineLogs() {
        try {
            const logs = localStorage.getItem('frontend_logs_offline');
            return logs ? JSON.parse(logs) : [];
        } catch (error) {
            console.error('Error loading offline logs:', error);
            return [];
        }
    }

    /**
     * Cargar logs offline al inicializar
     */
    loadOfflineLogs() {
        const offlineLogs = this.getOfflineLogs();
        if (offlineLogs.length > 0) {
            console.info(`Loaded ${offlineLogs.length} offline logs`);
        }
    }

    /**
     * Sincronizar logs offline
     */
    async syncOfflineLogs() {
        const offlineLogs = this.getOfflineLogs();
        if (offlineLogs.length === 0) return;

        try {
            console.info(`Syncing ${offlineLogs.length} offline logs...`);
            await this.sendLogBatch(offlineLogs);

            // Limpiar logs offline después del envío exitoso
            localStorage.removeItem('frontend_logs_offline');
            console.info('Offline logs synced successfully');

        } catch (error) {
            console.error('Error syncing offline logs:', error);
        }
    }

    /**
     * Limpiar logs offline manualmente
     */
    clearOfflineLogs() {
        localStorage.removeItem('frontend_logs_offline');
    }

    /**
     * Obtener estadísticas de logs
     */
    getStats() {
        return {
            queueSize: this.logQueue.length,
            offlineLogsCount: this.getOfflineLogs().length,
            sessionId: this.sessionId,
            isOnline: navigator.onLine
        };
    }
}

// Crear instancia global
const Logger = new FrontendLogger({
    source: 'web-app', // Cambiar según tu aplicación
    autoCapture: true,
    batchSize: 10,
    batchTimeout: 5000
});

// Exponer globalmente
window.Logger = Logger;

// Para módulos ES6
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Logger;
}

// Ejemplo de uso:
/*
// Log de error
Logger.error('Error al cargar datos del usuario', {
    component: 'user-profile',
    action: 'load_user_data',
    error_code: 'API_ERROR',
    additional_data: { userId: 123 }
});

// Log de evento
Logger.event('button_clicked', 'navigation', {
    button_name: 'login',
    page: 'home'
});

// Log de rendimiento
Logger.performance('api_response_time', 1245, 'ms', {
    endpoint: '/api/users',
    component: 'user-service'
});

// Log informativo
Logger.info('Usuario autenticado exitosamente', {
    component: 'auth-service',
    action: 'user_login'
});
*/