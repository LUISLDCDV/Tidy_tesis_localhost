# Componentes de Navegación - Documentación

## Descripción General

Los componentes de navegación proporcionan una experiencia de usuario fluida y accesible para el cambio de tema e idioma directamente desde el menú hamburguesa.

## Componentes Implementados

### 1. ThemeToggle.vue

Componente para alternar entre modo claro y oscuro con animaciones y notificaciones.

#### Características

- **Toggle visual**: Switch animado con iconos dinámicos
- **Doble activación**: Click en el ítem completo o solo en el switch
- **Persistencia**: Guarda la preferencia en localStorage
- **Notificaciones**: Feedback visual al usuario
- **Animaciones**: Transiciones suaves y efectos visuales
- **Accesibilidad**: Soporte completo para lectores de pantalla

#### Uso

```vue
<template>
  <ThemeToggle @theme-changed="onThemeChanged" />
</template>

<script>
export default {
  methods: {
    onThemeChanged(isDarkMode) {
      console.log('Tema cambiado:', isDarkMode ? 'Oscuro' : 'Claro');
    }
  }
};
</script>
```

#### Props

El componente no recibe props, utiliza el estado global de Quasar.

#### Eventos

- `@theme-changed(isDarkMode: boolean)`: Emitido cuando cambia el tema

#### Funcionalidades

- **Sincronización automática**: Se sincroniza con cambios externos del tema
- **Iconos dinámicos**: `light_mode` para modo oscuro, `dark_mode` para modo claro
- **Colores adaptativos**: Amber para modo oscuro, Indigo para modo claro
- **Notificaciones personalizadas**: Mensajes específicos según el tema seleccionado

### 2. LanguageSelector.vue

Componente avanzado para selección de idioma con banderas, menú desplegable y sugerencias inteligentes.

#### Características

- **Selector visual**: Menú con banderas y nombres de países
- **Idiomas soportados**: Español, Inglés y Portugués
- **Detección inteligente**: Sugiere idioma basado en el navegador
- **Persistencia**: Guarda preferencia en localStorage
- **Feedback visual**: Animaciones y notificaciones
- **Accesibilidad**: Navegación por teclado y etiquetas ARIA

#### Uso

```vue
<template>
  <LanguageSelector @language-changed="onLanguageChanged" />
</template>

<script>
export default {
  methods: {
    onLanguageChanged(language) {
      console.log('Idioma cambiado:', language);
      // language = { label: 'English', value: 'en', flag: '🇺🇸', country: 'United States' }
    }
  }
};
</script>
```

#### Props

El componente no recibe props, utiliza configuración interna.

#### Eventos

- `@language-changed(language: Object)`: Emitido cuando cambia el idioma

#### Estructura del Objeto Language

```javascript
{
  label: 'English',        // Nombre del idioma
  value: 'en',            // Código ISO del idioma
  flag: '🇺🇸',           // Emoji de bandera
  country: 'United States' // Nombre del país
}
```

#### Funcionalidades

- **Menú contextual**: Aparece al hacer click con animación scale
- **Estado activo**: Marca visual del idioma seleccionado
- **Sugerencias**: Dialog opcional para sugerir idioma del navegador
- **Integración vue-i18n**: Cambio automático del idioma en la aplicación

## Integración en ResponsiveNavBar

### Ubicación

Los componentes se ubican en la sección "Settings & Account" del menú hamburguesa, antes de los enlaces de configuración estándar.

```vue
<!-- Settings & Account -->
<q-item-label header>{{ t('nav.account') }}</q-item-label>

<!-- Theme Toggle Component -->
<ThemeToggle @theme-changed="onThemeChanged" />

<!-- Language Selector Component -->
<LanguageSelector @language-changed="onLanguageChanged" />

<!-- Otros enlaces de configuración -->
<q-item v-for="setting in settingLinks" ... />
```

### Eventos Manejados

```javascript
methods: {
  onThemeChanged(isDarkMode) {
    console.log('Theme changed:', isDarkMode ? 'Dark' : 'Light');

    // Cerrar el drawer en móvil después del cambio
    if (this.$q.screen.lt.lg) {
      setTimeout(() => {
        this.closeDrawer();
      }, 500);
    }
  },

  onLanguageChanged(language) {
    console.log('Language changed:', language);

    // Actualizar vue-i18n si es necesario
    if (this.$i18n.locale !== language.value) {
      this.$i18n.locale = language.value;
    }

    // Cerrar el drawer en móvil
    if (this.$q.screen.lt.lg) {
      setTimeout(() => {
        this.closeDrawer();
      }, 500);
    }

    // Emitir evento para componentes padre
    this.$emit('language-changed', language);
  }
}
```

## Traducciones Requeridas

### Claves de Traducción

Para que los componentes funcionen correctamente, se requieren las siguientes claves en los archivos de idioma:

#### Español (es.js)

```javascript
nav: {
  // Theme toggle
  themeToggle: 'Cambiar Tema',
  switchToLight: 'Cambiar a modo claro',
  switchToDark: 'Cambiar a modo oscuro',
  darkModeEnabled: 'Modo oscuro activado',
  lightModeEnabled: 'Modo claro activado',

  // Language selector
  language: 'Idioma',
  selectLanguage: 'Seleccionar Idioma',
  languageChanged: 'Idioma cambiado a',
  languageChangeError: 'Error al cambiar el idioma',
  languageSuggestionTitle: 'Sugerencia de Idioma',
  languageSuggestionMessage: '¿Te gustaría cambiar el idioma a'
}
```

#### Inglés (en.js)

```javascript
nav: {
  // Theme toggle
  themeToggle: 'Change Theme',
  switchToLight: 'Switch to light mode',
  switchToDark: 'Switch to dark mode',
  darkModeEnabled: 'Dark mode enabled',
  lightModeEnabled: 'Light mode enabled',

  // Language selector
  language: 'Language',
  selectLanguage: 'Select Language',
  languageChanged: 'Language changed to',
  languageChangeError: 'Error changing language',
  languageSuggestionTitle: 'Language Suggestion',
  languageSuggestionMessage: 'Would you like to change the language to'
}
```

#### Portugués (pt.js)

```javascript
nav: {
  // Theme toggle
  themeToggle: 'Alterar Tema',
  switchToLight: 'Mudar para modo claro',
  switchToDark: 'Mudar para modo escuro',
  darkModeEnabled: 'Modo escuro ativado',
  lightModeEnabled: 'Modo claro ativado',

  // Language selector
  language: 'Idioma',
  selectLanguage: 'Selecionar Idioma',
  languageChanged: 'Idioma alterado para',
  languageChangeError: 'Erro ao alterar idioma',
  languageSuggestionTitle: 'Sugestão de Idioma',
  languageSuggestionMessage: 'Gostaria de alterar o idioma para'
}
```

## Estilos y Animaciones

### ThemeToggle

- **Animación de ícono**: Rotación 360° al cambiar tema
- **Efecto hover**: Escala del ícono y cambio de background
- **Animación de entrada**: slideInLeft con delay
- **Transiciones**: Smooth transitions para todos los elementos

### LanguageSelector

- **Menú desplegable**: Animación scale para aparición/desaparición
- **Flags animadas**: Hover con escala de las banderas
- **Estado activo**: Highlight visual y check mark
- **Responsive**: Adaptativo para dispositivos móviles

### Responsividad

```scss
@media (max-width: 768px) {
  .theme-toggle-item,
  .language-selector-item {
    min-height: 52px;
    margin: 2px 4px;
  }

  .language-menu {
    min-width: 200px;
  }
}
```

## Persistencia de Datos

### localStorage Keys

- **Tema**: `darkMode` (boolean), `theme` (string)
- **Idioma**: `language` (string), `app_language` (string)

### Sincronización

Los componentes se sincronizan automáticamente con:
- Cambios externos del tema (Quasar Dark Mode)
- Cambios de idioma en vue-i18n
- Preferencias guardadas en localStorage

## Accesibilidad

### Características ARIA

- `aria-label` en botones y controles
- `role` apropiados para elementos interactivos
- Navegación por teclado completa
- Estados de enfoque visibles
- Lectores de pantalla compatibles

### Navegación por Teclado

- **Tab**: Navegar entre componentes
- **Enter/Space**: Activar controles
- **Escape**: Cerrar menús desplegables
- **Arrow keys**: Navegar opciones de idioma

## Testing

### Unit Tests

```javascript
import { mount } from '@vue/test-utils';
import ThemeToggle from '@/components/Nav/ThemeToggle.vue';

describe('ThemeToggle', () => {
  it('should toggle theme on click', async () => {
    const wrapper = mount(ThemeToggle);

    await wrapper.find('.theme-toggle-item').trigger('click');

    expect(wrapper.emitted('theme-changed')).toBeTruthy();
  });
});
```

### Integration Tests

```javascript
import ResponsiveNavBar from '@/components/Nav/ResponsiveNavBar.vue';

describe('ResponsiveNavBar Integration', () => {
  it('should include theme and language components', () => {
    const wrapper = mount(ResponsiveNavBar);

    expect(wrapper.findComponent(ThemeToggle).exists()).toBe(true);
    expect(wrapper.findComponent(LanguageSelector).exists()).toBe(true);
  });
});
```

## Mejores Prácticas

### 1. Uso de Eventos

```javascript
// ✅ Correcto - Escuchar eventos específicos
<ThemeToggle @theme-changed="handleThemeChange" />

// ❌ Incorrecto - Acceso directo al estado
// No acceder directamente a this.$q.dark desde componentes padre
```

### 2. Gestión de Estado

```javascript
// ✅ Correcto - Dejar que los componentes manejen su estado
const themeToggle = this.$refs.themeToggle;
// themeToggle maneja su propio estado

// ❌ Incorrecto - Forzar estado desde el padre
// this.forceDarkMode = true; // Los componentes deben ser autónomos
```

### 3. Persistencia

```javascript
// ✅ Correcto - Los componentes manejan su propia persistencia
// ThemeToggle y LanguageSelector guardan automáticamente en localStorage

// ❌ Incorrecto - Manejar persistencia externamente
// localStorage.setItem('theme', 'dark'); // Puede causar desincronización
```

## Extensibilidad

### Agregar Nuevos Idiomas

```javascript
// En LanguageSelector.vue
const languageOptions = ref([
  // Idiomas existentes...
  {
    label: 'Français',
    value: 'fr',
    flag: '🇫🇷',
    country: 'France'
  },
  {
    label: 'Deutsch',
    value: 'de',
    flag: '🇩🇪',
    country: 'Deutschland'
  }
]);
```

### Personalizar Temas

```javascript
// Extender ThemeToggle para más temas
const themes = [
  { name: 'light', icon: 'light_mode', color: 'amber' },
  { name: 'dark', icon: 'dark_mode', color: 'indigo' },
  { name: 'auto', icon: 'brightness_auto', color: 'blue-grey' }
];
```

## Troubleshooting

### Problemas Comunes

1. **Tema no se sincroniza**
   - Verificar que Quasar esté correctamente configurado
   - Revisar console.log para errores de localStorage

2. **Idioma no cambia**
   - Verificar configuración de vue-i18n
   - Confirmar que las claves de traducción existen

3. **Componentes no aparecen**
   - Verificar importación en ResponsiveNavBar
   - Confirmar que los componentes están registrados

4. **Estilos no se aplican**
   - Verificar que los estilos scoped funcionen correctamente
   - Revisar que las clases CSS están bien definidas

### Debug

```javascript
// En ThemeToggle.vue
console.log('Current theme:', this.$q.dark.isActive);
console.log('Saved theme:', localStorage.getItem('darkMode'));

// En LanguageSelector.vue
console.log('Current language:', this.$i18n.locale);
console.log('Saved language:', localStorage.getItem('language'));
```