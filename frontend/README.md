# Tidy - Frontend

Una aplicación de productividad moderna y gamificada construida con Vue 3, Quasar Framework y Vite.

## 🚀 Características Principales

### 📝 Sistema de Notas Avanzado
- **16 tipos de notas especializadas** desbloqueables por nivel
- **Editor WYSIWYG** con soporte para código y formato rico
- **Notas colaborativas** para trabajo en equipo
- **Categorización automática** (Básicas, Productividad, Creatividad, etc.)

### 🎯 Gestión de Objetivos y Metas
- **Objetivos estructurados** con metas y pasos
- **Seguimiento de progreso** visual
- **Sistema de recordatorios** integrado
- **Análisis de cumplimiento**

### 📅 Calendario Inteligente
- **Integración VueCal** con drag & drop
- **Eventos con ubicación** y datos climáticos
- **Sincronización** de eventos
- **Notificaciones programadas**

### ⏰ Sistema de Alarmas
- **Alarmas configurables** con múltiples parámetros
- **Notificaciones del navegador**
- **Configuración avanzada** de intensidad y repetición

### 🎮 Sistema de Gamificación
- **Niveles de usuario** del 1 al 100+
- **Sistema de experiencia (XP)** por actividades
- **Logros desbloqueables** con recompensas
- **Ranking global** de usuarios
- **Desbloqueo progresivo** de funcionalidades

### 🌐 Funcionalidades Adicionales
- **Geolocalización** con API de Argentina
- **Datos meteorológicos** en tiempo real
- **Modo offline** con sincronización
- **Configuración personalizable** del usuario
- **Responsive design** optimizado para móvil

## 🛠️ Tecnologías

- **Frontend**: Vue 3 (Composition API)
- **Framework UI**: Quasar Framework
- **Build Tool**: Vite
- **State Management**: Pinia
- **Routing**: Vue Router 4
- **Internationalization**: Vue I18n
- **Calendar**: VueCal
- **Rich Text Editor**: TipTap
- **Drag & Drop**: Pragmatic Drag and Drop
- **Canvas**: Konva.js
- **HTTP Client**: Axios

## 📦 Instalación

```bash
# Clonar el repositorio
git clone <repository-url>
cd front-tidy

# Instalar dependencias
npm install
```

## 🚀 Scripts de Desarrollo

```bash
# Desarrollo con hot-reload
npm run dev
# o
npm run serve

# Build para producción
npm run build

# Preview del build
npm run preview
```

## 🏗️ Estructura del Proyecto

```
src/
├── components/          # Componentes reutilizables
│   ├── Elements/       # Componentes de elementos (notas, objetivos, etc.)
│   ├── Levels/         # Sistema de gamificación
│   ├── Nav/            # Navegación y menús
│   ├── User/           # Componentes de usuario
│   └── modals/         # Modales y diálogos
├── services/           # Servicios de API y lógica de negocio
├── stores/             # Stores de Pinia
├── utils/              # Utilidades y helpers
├── router/             # Configuración de rutas
└── assets/             # Assets estáticos
```

## 🎯 Funcionalidades por Nivel de Usuario

### Niveles 1-4 (Novato 🌱)
- Notas básicas
- Objetivos simples
- Alarmas básicas

### Niveles 5-9 (Aprendiz ⭐)
- Gestión de claves
- Recomendaciones
- Compras supermercado

### Niveles 10-19 (Competente 🌟)
- Control de presupuesto
- Gestión de tiempo
- Recetas de cocina

### Niveles 20+ (Experto+ 🏆)
- Planificación de viajes
- Diagramas avanzados
- Funcionalidades premium

## 🌐 APIs Integradas

- **GeoCoding**: georef-ar.datosgovernicos.gob.ar
- **Weather**: WeatherAPI.com
- **Notifications**: Browser Notification API
- **Location**: Geolocation API

## 📱 Responsive Design

La aplicación está optimizada para:
- **Desktop**: Experiencia completa con sidebar
- **Tablet**: Navegación adaptativa
- **Mobile**: Menu hamburguesa y gestos táctiles
- **Touch devices**: Áreas de toque optimizadas

## 🌙 Temas y Personalización

- **Light/Dark mode** automático
- **Colores personalizables** por categoría
- **Preferencias de usuario** persistentes
- **Configuraciones avanzadas**

## 🔐 Autenticación y Seguridad

- **JWT Authentication**
- **Local storage** para persistencia
- **Session management**
- **Logout automático** por inactividad

## 📊 Analytics y Métricas

- **Tracking de actividad** del usuario
- **Estadísticas de productividad**
- **Métricas de engagement**
- **Análisis de uso** por funcionalidad

## 🚧 Estado del Proyecto

✅ **Funcionalidades Completadas**:
- Sistema completo de notas con 16 tipos
- Gamificación con niveles y logros
- Calendario con eventos inteligentes
- Gestión de objetivos y metas
- Sistema de alarmas avanzado
- Configuración completa de usuario
- Responsive design optimizado
- Integración con APIs externas

## 🤝 Contribución

1. Fork el proyecto
2. Crear una branch (`git checkout -b feature/nueva-funcionalidad`)
3. Commit los cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la branch (`git push origin feature/nueva-funcionalidad`)
5. Crear un Pull Request

## 📄 Licencia

Este proyecto está bajo la licencia MIT.

## 📞 Soporte

Para reportar bugs o solicitar funcionalidades, crear un issue en el repositorio.

---

**Tidy** - Organiza tu vida de manera inteligente y divertida 🎯