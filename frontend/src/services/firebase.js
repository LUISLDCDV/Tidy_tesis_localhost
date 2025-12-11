import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';
import { getDatabase } from 'firebase/database';
import {
  getAuth,
  GoogleAuthProvider,
  signInWithPopup,
  signOut,
  onAuthStateChanged
} from 'firebase/auth';
import {
  getStorage,
  ref,
  uploadBytes,
  getDownloadURL,
  deleteObject
} from 'firebase/storage';
import {
  getFirestore,
  collection,
  addDoc,
  updateDoc,
  deleteDoc,
  doc,
  getDocs,
  query,
  orderBy,
  onSnapshot
} from 'firebase/firestore';

// Configuración de Firebase
const firebaseConfig = {
  apiKey: "AIzaSyDxy_4SmzsoUcNF0v0WjpeVV8TSygnyLBg",
  authDomain: "tidy-1d736.firebaseapp.com",
  projectId: "tidy-1d736",
  storageBucket: "tidy-1d736.firebasestorage.app",
  messagingSenderId: "122328704991",
  appId: "1:122328704991:web:1da9edcd93d27936bb466a",
  measurementId: "G-17B0N15J5S",
  databaseURL: import.meta.env.VITE_APP_FIREBASE_DATABASE_URL
};

// Inicializar Firebase
const app = initializeApp(firebaseConfig);

// Inicializar servicios
const messaging = getMessaging(app);
const database = getDatabase(app);
const auth = getAuth(app);
const storage = getStorage(app);
const firestore = getFirestore(app);

// Provider para Google Auth
const googleProvider = new GoogleAuthProvider();
googleProvider.addScope('email');
googleProvider.addScope('profile');

class FirebaseService {
  constructor() {
    this.messaging = messaging;
    this.database = database;
    this.auth = auth;
    this.storage = storage;
    this.firestore = firestore;
    this.currentToken = null;
    this.authUser = null;
  }

  /**
   * Solicitar permisos de notificación y obtener token
   */
  async requestNotificationPermission() {
    try {
      // Verificar si el navegador soporta notificaciones
      if (!('Notification' in window)) {
        console.warn('Este navegador no soporta notificaciones');
        return null;
      }

      // Verificar si ya se dieron permisos
      if (Notification.permission === 'granted') {
        return await this.getRegistrationToken();
      }

      // Solicitar permisos
      const permission = await Notification.requestPermission();
      
      if (permission === 'granted') {
        console.log('Permisos de notificación otorgados');
        return await this.getRegistrationToken();
      } else {
        console.warn('Permisos de notificación denegados');
        return null;
      }
    } catch (error) {
      console.error('Error al solicitar permisos de notificación:', error);
      return null;
    }
  }

  /**
   * Obtener token de registro FCM
   */
  async getRegistrationToken() {
    try {
      const vapidKey = import.meta.env.VITE_APP_FIREBASE_VAPID_KEY;
      
      const token = await getToken(this.messaging, {
        vapidKey: vapidKey
      });

      if (token) {
        console.log('Token FCM obtenido:', token);
        this.currentToken = token;
        return token;
      } else {
        console.warn('No se pudo obtener el token FCM');
        return null;
      }
    } catch (error) {
      console.error('Error al obtener token FCM:', error);
      return null;
    }
  }

  /**
   * Configurar listener para mensajes en primer plano
   */
  onForegroundMessage(callback) {
    return onMessage(this.messaging, (payload) => {
      console.log('Mensaje recibido en primer plano:', payload);
      
      // Crear notificación personalizada
      if (payload.notification) {
        this.showNotification(
          payload.notification.title,
          payload.notification.body,
          payload.data
        );
      }

      // Ejecutar callback personalizado
      if (callback) {
        callback(payload);
      }
    });
  }

  /**
   * Mostrar notificación en el navegador
   */
  showNotification(title, body, data = {}) {
    if (Notification.permission === 'granted') {
      const options = {
        body,
        icon: '/icons/favicon-96x96.png',
        badge: '/icons/favicon-32x32.png',
        data,
        requireInteraction: false,
        silent: false
      };

      const notification = new Notification(title, options);
      
      notification.onclick = () => {
        // Manejar clic en notificación
        if (data.click_action) {
          this.handleNotificationClick(data);
        }
        notification.close();
      };

      // Auto cerrar después de 5 segundos
      setTimeout(() => {
        notification.close();
      }, 5000);
    }
  }

  /**
   * Manejar clic en notificación
   */
  handleNotificationClick(data) {
    // Focus en la ventana si está abierta
    if (window.focus) {
      window.focus();
    }

    // Navegar según el tipo de notificación
    const router = this.router; // Se configurará desde main.js
    
    switch (data.click_action) {
      case 'OPEN_ELEMENT':
        if (data.element_id) {
          router.push(`/Note/view/${data.element_id}`);
        }
        break;
      case 'OPEN_ACHIEVEMENTS':
        router.push('/user/1/achievements');
        break;
      case 'OPEN_PROFILE':
        router.push('/user/1/profile');
        break;
      default:
        router.push('/Home');
    }
  }

  /**
   * Registrar token en el servidor
   */
  async registerTokenWithServer(token) {
    try {
      const api = (await import('./api')).default;
      
      const deviceInfo = {
        device_token: token,
        device_type: 'web',
        device_name: this.getDeviceName()
      };

      const response = await api.post('/notifications/register-device', deviceInfo);
      console.log('Token registrado en el servidor:', response.data);
      return response.data;
    } catch (error) {
      console.error('Error al registrar token en el servidor:', error);
      throw error;
    }
  }

  /**
   * Desregistrar token del servidor
   */
  async unregisterTokenFromServer(token) {
    try {
      const api = (await import('./api')).default;
      
      const response = await api.post('/notifications/unregister-device', {
        device_token: token
      });
      
      console.log('Token desregistrado del servidor:', response.data);
      return response.data;
    } catch (error) {
      console.error('Error al desregistrar token del servidor:', error);
      throw error;
    }
  }

  /**
   * Obtener información del dispositivo
   */
  getDeviceName() {
    const userAgent = navigator.userAgent;
    let deviceName = 'Navegador Web';

    // Detectar navegador
    if (userAgent.includes('Chrome')) {
      deviceName = 'Chrome';
    } else if (userAgent.includes('Firefox')) {
      deviceName = 'Firefox';
    } else if (userAgent.includes('Safari')) {
      deviceName = 'Safari';
    } else if (userAgent.includes('Edge')) {
      deviceName = 'Edge';
    }

    // Detectar SO
    if (userAgent.includes('Windows')) {
      deviceName += ' - Windows';
    } else if (userAgent.includes('Mac')) {
      deviceName += ' - MacOS';
    } else if (userAgent.includes('Linux')) {
      deviceName += ' - Linux';
    } else if (userAgent.includes('Android')) {
      deviceName += ' - Android';
    } else if (userAgent.includes('iOS')) {
      deviceName += ' - iOS';
    }

    return deviceName;
  }

  /**
   * Inicializar servicio de notificaciones
   */
  async initialize(router) {
    this.router = router;
    
    try {
      // Obtener token
      const token = await this.requestNotificationPermission();
      
      if (token) {
        // Registrar token en el servidor
        await this.registerTokenWithServer(token);
        
        // Configurar listener para mensajes
        this.onForegroundMessage((payload) => {
          // Manejar mensajes específicos de la aplicación
          this.handleAppMessage(payload);
        });

        return token;
      }
      
      return null;
    } catch (error) {
      console.error('Error al inicializar Firebase Messaging:', error);
      return null;
    }
  }

  /**
   * Manejar mensajes específicos de la aplicación
   */
  handleAppMessage(payload) {
    const { data } = payload;
    
    // Actualizar estado de Vuex según el tipo de mensaje
    const store = this.store; // Se configurará desde main.js
    
    switch (data.type) {
      case 'level_up':
        store.dispatch('user/updateLevel', {
          level: parseInt(data.new_level),
          showNotification: true
        });
        break;
      case 'achievement_unlocked':
        store.dispatch('user/addAchievement', {
          achievement_id: data.achievement_id,
          achievement_name: data.achievement_name,
          showNotification: true
        });
        break;
      case 'element_created':
        store.dispatch('elements/refreshElements');
        break;
      default:
        console.log('Mensaje no manejado:', data);
    }
  }

  /**
   * Configurar store de Vuex
   */
  setStore(store) {
    this.store = store;
  }

  /**
   * Limpiar recursos
   */
  cleanup() {
    if (this.currentToken) {
      this.unregisterTokenFromServer(this.currentToken);
    }
  }

  // ===========================================
  // MÉTODOS DE AUTENTICACIÓN CON GOOGLE
  // ===========================================

  /**
   * Login con Google - Detecta automáticamente si es web o app nativa
   */
  async loginWithGoogle() {
    try {
      // Detectar si estamos en Capacitor (app nativa)
      const isNative = window.Capacitor?.isNativePlatform();

      if (isNative) {
        // Usar plugin de Capacitor para apps nativas
        console.log('Usando Capacitor GoogleAuth para app nativa');
        const { GoogleAuth } = await import('@codetrix-studio/capacitor-google-auth');

        // Inicializar GoogleAuth
        await GoogleAuth.initialize();

        // Hacer login
        const googleUser = await GoogleAuth.signIn();

        this.authUser = {
          uid: googleUser.id,
          email: googleUser.email,
          name: googleUser.name,
          photo: googleUser.imageUrl,
          token: googleUser.authentication.idToken
        };

        console.log('Login con Google (Capacitor) exitoso:', this.authUser);
        return this.authUser;
      } else {
        // Usar Firebase para web
        console.log('Usando Firebase signInWithPopup para web');
        const result = await signInWithPopup(this.auth, googleProvider);
        const user = result.user;

        this.authUser = {
          uid: user.uid,
          email: user.email,
          name: user.displayName,
          photo: user.photoURL,
          token: await user.getIdToken()
        };

        console.log('Login con Google (Firebase) exitoso:', this.authUser);
        return this.authUser;
      }
    } catch (error) {
      console.error('Error en login con Google:', error);
      throw new Error(error.message);
    }
  }

  /**
   * Logout de Firebase
   */
  async firebaseLogout() {
    try {
      await signOut(this.auth);
      this.authUser = null;
      console.log('Logout de Firebase exitoso');
    } catch (error) {
      console.error('Error en logout de Firebase:', error);
      throw new Error(error.message);
    }
  }

  /**
   * Obtener usuario actual
   */
  getCurrentUser() {
    return this.auth.currentUser;
  }

  /**
   * Escuchar cambios de autenticación
   */
  onAuthStateChanged(callback) {
    return onAuthStateChanged(this.auth, callback);
  }

  /**
   * Obtener token Firebase actual
   */
  async getCurrentFirebaseToken() {
    const user = this.auth.currentUser;
    if (user) {
      return await user.getIdToken();
    }
    return null;
  }

  // ===========================================
  // MÉTODOS DE STORAGE
  // ===========================================

  /**
   * Subir imagen a Firebase Storage
   */
  async uploadImage(file, path = 'general') {
    try {
      const imageRef = ref(this.storage, `images/${path}/${Date.now()}_${file.name}`);
      const snapshot = await uploadBytes(imageRef, file);
      const downloadURL = await getDownloadURL(snapshot.ref);

      return {
        url: downloadURL,
        path: snapshot.ref.fullPath,
        name: file.name,
        size: snapshot.metadata.size
      };
    } catch (error) {
      console.error('Error subiendo imagen:', error);
      throw new Error(error.message);
    }
  }

  /**
   * Eliminar imagen de Firebase Storage
   */
  async deleteImage(imagePath) {
    try {
      const imageRef = ref(this.storage, imagePath);
      await deleteObject(imageRef);
      console.log('Imagen eliminada exitosamente');
    } catch (error) {
      console.error('Error eliminando imagen:', error);
      throw new Error(error.message);
    }
  }

  /**
   * Subir múltiples archivos
   */
  async uploadMultipleFiles(files, path = 'general') {
    try {
      const uploadPromises = files.map(file => this.uploadImage(file, path));
      return await Promise.all(uploadPromises);
    } catch (error) {
      console.error('Error subiendo múltiples archivos:', error);
      throw new Error(error.message);
    }
  }

  // ===========================================
  // MÉTODOS DE FIRESTORE (MENSAJES Y OFFLINE)
  // ===========================================

  /**
   * Agregar mensaje a chat
   */
  async addMessage(chatId, message) {
    try {
      const docRef = await addDoc(collection(this.firestore, 'chats', chatId, 'messages'), {
        ...message,
        timestamp: new Date(),
        read: false
      });
      return docRef.id;
    } catch (error) {
      console.error('Error agregando mensaje:', error);
      throw new Error(error.message);
    }
  }

  /**
   * Escuchar mensajes en tiempo real
   */
  onMessagesChanged(chatId, callback) {
    const messagesRef = collection(this.firestore, 'chats', chatId, 'messages');
    const q = query(messagesRef, orderBy('timestamp', 'asc'));

    return onSnapshot(q, callback);
  }

  /**
   * Agregar cambio offline pendiente
   */
  async addOfflineChange(userId, change) {
    try {
      const docRef = await addDoc(collection(this.firestore, 'offline_changes', userId, 'pending'), {
        ...change,
        timestamp: new Date(),
        synced: false
      });
      return docRef.id;
    } catch (error) {
      console.error('Error guardando cambio offline:', error);
      throw new Error(error.message);
    }
  }

  /**
   * Obtener cambios offline pendientes
   */
  async getPendingChanges(userId) {
    try {
      const changesRef = collection(this.firestore, 'offline_changes', userId, 'pending');
      const q = query(changesRef, orderBy('timestamp', 'asc'));
      const snapshot = await getDocs(q);

      return snapshot.docs.map(doc => ({
        id: doc.id,
        ...doc.data()
      }));
    } catch (error) {
      console.error('Error obteniendo cambios pendientes:', error);
      throw new Error(error.message);
    }
  }

  /**
   * Marcar cambio como sincronizado
   */
  async markChangeSynced(userId, changeId) {
    try {
      const changeRef = doc(this.firestore, 'offline_changes', userId, 'pending', changeId);
      await updateDoc(changeRef, { synced: true });
    } catch (error) {
      console.error('Error marcando cambio como sincronizado:', error);
      throw new Error(error.message);
    }
  }

  /**
   * Eliminar cambio sincronizado
   */
  async deleteChange(userId, changeId) {
    try {
      const changeRef = doc(this.firestore, 'offline_changes', userId, 'pending', changeId);
      await deleteDoc(changeRef);
    } catch (error) {
      console.error('Error eliminando cambio:', error);
      throw new Error(error.message);
    }
  }
}

// Exportar instancia singleton
const firebaseService = new FirebaseService();

export default firebaseService;