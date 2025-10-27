<template>
  <q-card class="user-info-card">
    <!-- Banner superior (solo en perfil completo) -->
    <div v-if="showBanner" class="profile-banner">
      <div class="profile-avatar-container">
        <q-avatar :size="avatarSize" class="profile-avatar">
          <img
            :src="userImageUrl || defaultImageUrl"
            :alt="userName"
            @error="handleImageError"
          >
        </q-avatar>
        <!-- Botón para cambiar foto (solo si es editable) -->
        <q-btn
          v-if="editable"
          fab
          mini
          color="primary"
          icon="camera_alt"
          class="avatar-edit-btn"
          @click="$emit('change-avatar')"
        />
      </div>
    </div>

    <!-- Información del usuario -->
    <q-card-section :class="{ 'profile-info': showBanner, 'compact-info': !showBanner }">
      <!-- Versión completa del perfil -->
      <div v-if="showBanner" class="text-center q-mb-md">
        <h2 class="text-h5 text-weight-bold q-mb-xs">{{ userName }}</h2>
        <p class="text-grey-6">{{ userEmail || t('profile.emailNotAvailable') }}</p>
        <p v-if="userJoinDate" class="text-caption text-grey-5">
          {{ t('profile.memberSince') }} {{ userJoinDate }}
        </p>

        <!-- Chip de nivel -->
        <q-chip
          v-if="showLevel"
          :color="userLevelColor"
          text-color="white"
          :icon="userLevelIcon"
          class="q-mt-sm"
          clickable
          @click="$emit('show-level-details')"
        >
          {{ userLevelText }}
        </q-chip>

        <!-- Indicador de nivel interactivo -->
        <div v-if="showLevelIndicator" class="q-mt-md">
          <LevelIndicator
            clickable
            show-more
            @click="$emit('show-level-details')"
            @show-details="$emit('show-level-details')"
          />
        </div>
      </div>

      <!-- Versión compacta (para configuración) -->
      <q-item v-else clickable @click="$emit('edit-profile')" class="compact-user-item">
        <q-item-section avatar>
          <q-avatar :size="avatarSize">
            <img :src="userImageUrl || defaultImageUrl" :alt="userName">
          </q-avatar>
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ userName }}</q-item-label>
          <q-item-label caption>{{ userEmail }}</q-item-label>
        </q-item-section>
        <q-item-section side>
          <q-icon name="edit" />
        </q-item-section>
      </q-item>

      <!-- Información adicional (biografía, teléfono) -->
      <div v-if="showAdditionalInfo && (userPhone || userBio)" class="q-mb-lg">
        <q-separator class="q-mb-md" />
        <div v-if="userBio" class="q-mb-sm">
          <div class="text-body2 text-weight-medium text-grey-7">{{ t('profile.biography') }}</div>
          <div class="text-body2">{{ userBio }}</div>
        </div>
        <div v-if="userPhone" class="q-mb-sm">
          <div class="text-body2 text-weight-medium text-grey-7">{{ t('profile.phone') }}</div>
          <div class="text-body2">{{ userPhone }}</div>
        </div>
      </div>

      <!-- Controles de tema e idioma (para settings compacto) -->
      <div v-if="themeConfig || languageConfig" class="q-mb-md">
        <q-separator class="q-mb-md" />

        <!-- Control de tema -->
        <div v-if="themeConfig" class="q-mb-sm">
          <q-item>
            <q-item-section avatar>
              <q-icon :name="isDarkMode ? 'dark_mode' : 'light_mode'" color="primary" />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ t('profile.darkTheme') }}</q-item-label>
              <q-item-label caption>{{ t('profile.darkThemeDescription') }}</q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-toggle
                :model-value="isDarkMode"
                @update:model-value="toggleTheme"
                color="primary"
              />
            </q-item-section>
          </q-item>
        </div>

        <!-- Selector de idioma -->
        <div v-if="languageConfig" class="q-mb-sm">
          <q-item>
            <q-item-section avatar>
              <q-icon name="language" color="primary" />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ t('profile.language') }}</q-item-label>
              <q-item-label caption>{{ t('profile.languageDescription') }}</q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-select
                :model-value="currentLanguage"
                :options="languageOptions"
                emit-value
                map-options
                @update:model-value="changeLanguage"
                borderless
                dense
                class="language-select"
                option-value="value"
                option-label="label"
              >
                <template v-slot:selected-item="scope">
                  <div class="row items-center no-wrap">
                    <span class="text-h6 q-mr-xs">{{ scope.opt.flag }}</span>
                    <span>{{ scope.opt.label }}</span>
                  </div>
                </template>
                <template v-slot:option="scope">
                  <q-item v-bind="scope.itemProps">
                    <q-item-section avatar>
                      <span class="text-h6">{{ scope.opt.flag }}</span>
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>{{ scope.opt.label }}</q-item-label>
                      <q-item-label caption>{{ scope.opt.country }}</q-item-label>
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </q-item-section>
          </q-item>
        </div>
      </div>

      <!-- Acciones adicionales -->
      <div v-if="showActions" class="row q-gutter-sm justify-center">
        <q-btn
          color="primary"
          icon="edit"
          :label="t('profile.editProfile')"
          @click="openEditProfile"
          flat
        />
        <q-btn
          v-if="showSettingsButton"
          color="grey-7"
          icon="settings"
          :label="t('profile.settings')"
          @click="$emit('go-to-settings')"
          flat
        />
      </div>
    </q-card-section>

    <!-- Modal para editar perfil -->
    <q-dialog v-model="editProfileModal" v-if="editFormConfig">
      <q-card class="edit-profile-card">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ t('profile.editProfile') }}</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="editProfileModal = false" />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pt-md">
          <q-form @submit="saveProfile" class="q-gutter-md">
            <q-input
              v-model="editForm.name"
              :label="t('profile.fullName')"
              filled
              :rules="nameRules"
            />

            <q-input
              v-model="editForm.email"
              :label="t('profile.email')"
              type="email"
              filled
              :rules="emailRules"
            />

            <q-input
              v-model="editForm.phone"
              :label="t('profile.phoneOptional')"
              filled
              mask="###-###-####"
            />

            <q-input
              v-model="editForm.bio"
              :label="t('profile.bioOptional')"
              type="textarea"
              filled
              rows="3"
              maxlength="200"
              counter
            />

            <div class="row justify-end q-gutter-sm q-mt-md">
              <q-btn
                :label="t('common.cancel')"
                color="grey"
                @click="editProfileModal = false"
                flat
              />
              <q-btn
                :label="t('common.save')"
                color="primary"
                type="submit"
                :loading="saving"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-card>
</template>

<script>
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';
import { computed, ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import LevelIndicator from '@/components/Levels/LevelIndicator.vue';

export default {
  name: 'UserInfoCard',

  components: {
    LevelIndicator
  },

  props: {
    // Modo de visualización
    showBanner: {
      type: Boolean,
      default: false
    },

    // Tamaño del avatar
    avatarSize: {
      type: String,
      default: '40px'
    },

    // Funcionalidades visibles
    showLevel: {
      type: Boolean,
      default: true
    },

    showLevelIndicator: {
      type: Boolean,
      default: false
    },

    showAdditionalInfo: {
      type: Boolean,
      default: false
    },

    showActions: {
      type: Boolean,
      default: false
    },

    showSettingsButton: {
      type: Boolean,
      default: false
    },

    // Editable
    editable: {
      type: Boolean,
      default: false
    },

    // Datos del usuario (opcional, si no se usan del store)
    userData: {
      type: Object,
      default: null
    },

    // Nivel del usuario
    userLevel: {
      type: Number,
      default: 1
    },

    // Configuraciones para formulario de edición
    editFormConfig: {
      type: Object,
      default: null
    },

    // Configuraciones para selector de idioma
    languageConfig: {
      type: Object,
      default: null
    },

    // Configuraciones para tema
    themeConfig: {
      type: Object,
      default: null
    }
  },

  emits: [
    'edit-profile',
    'change-avatar',
    'go-to-settings',
    'show-level-details',
    'theme-changed',
    'language-changed',
    'save-profile'
  ],

  setup(props, { emit }) {
    const { t, locale } = useI18n();
    const authStore = useAuthStore();
    const $q = useQuasar();

    // Estado reactivo para modales y formularios
    const editProfileModal = ref(false);
    const saving = ref(false);
    const currentLanguage = ref('es');

    // Formulario de edición
    const editForm = ref({
      name: '',
      email: '',
      phone: '',
      bio: ''
    });

    // Computed properties para datos del usuario
    const userName = computed(() => {
      if (props.userData) {
        return props.userData.name || props.userData.nombre || 'Usuario';
      }
      return authStore.currentUser?.name ||
             authStore.currentUser?.nombre ||
             authStore.currentUser?.usuario ||
             'Usuario';
    });

    const userEmail = computed(() => {
      if (props.userData) {
        return props.userData.email || props.userData.correo;
      }
      return authStore.currentUser?.email ||
             authStore.currentUser?.correo ||
             'usuario@ejemplo.com';
    });

    const userImageUrl = computed(() => {
      if (props.userData) {
        return props.userData.imageUrl || props.userData.avatar;
      }
      return authStore.currentUser?.image_url ||
             authStore.currentUser?.imagen ||
             authStore.currentUser?.avatar || '';
    });

    const userPhone = computed(() => {
      if (props.userData) {
        return props.userData.phone || props.userData.telefono;
      }
      return authStore.currentUser?.phone ||
             authStore.currentUser?.telefono || '';
    });

    const userBio = computed(() => {
      if (props.userData) {
        return props.userData.bio || props.userData.biografia;
      }
      return authStore.currentUser?.bio ||
             authStore.currentUser?.biografia || '';
    });

    const userJoinDate = computed(() => {
      let createdAt;
      if (props.userData) {
        createdAt = props.userData.createdAt || props.userData.fecha_registro;
      } else {
        createdAt = authStore.currentUser?.created_at ||
                   authStore.currentUser?.fecha_registro;
      }

      if (createdAt) {
        const localeMap = {
          'es': 'es-ES',
          'en': 'en-US',
          'pt': 'pt-BR'
        };
        const currentLocale = localeMap[locale.value] || 'es-ES';

        return new Date(createdAt).toLocaleDateString(currentLocale, {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
      }
      return '';
    });

    // Computed para datos del nivel
    const userLevelText = computed(() => {
      const levels = {
        1: t('profile.levels.novice'),
        2: t('profile.levels.beginner'),
        3: t('profile.levels.intermediate'),
        4: t('profile.levels.advanced'),
        5: t('profile.levels.expert')
      };
      return levels[props.userLevel] || t('profile.levels.novice');
    });

    const userLevelColor = computed(() => {
      const colors = {
        1: 'grey',
        2: 'blue',
        3: 'green',
        4: 'orange',
        5: 'purple'
      };
      return colors[props.userLevel] || 'grey';
    });

    const userLevelIcon = computed(() => {
      const icons = {
        1: 'star_border',
        2: 'star_half',
        3: 'star',
        4: 'stars',
        5: 'workspace_premium'
      };
      return icons[props.userLevel] || 'star_border';
    });

    const defaultImageUrl = 'https://cdn.quasar.dev/img/avatar.png';

    // Opciones de idioma
    const languageOptions = ref([
      {
        label: 'Español',
        value: 'es',
        flag: '🇪🇸',
        country: 'España'
      },
      {
        label: 'English',
        value: 'en',
        flag: '🇺🇸',
        country: 'United States'
      },
      {
        label: 'Português',
        value: 'pt',
        flag: '🇧🇷',
        country: 'Brasil'
      }
    ]);

    // Computed para modo oscuro
    const isDarkMode = computed(() => $q.dark.isActive);

    // Reglas de validación
    const nameRules = [
      val => !!val || t('validation.nameRequired'),
      val => val.length >= 2 || t('validation.nameMinLength'),
      val => val.length <= 50 || t('validation.nameMaxLength')
    ];

    const emailRules = [
      val => !!val || t('validation.emailRequired'),
      val => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) || t('validation.emailInvalid')
    ];

    // Funciones
    const handleImageError = (event) => {
      event.target.src = defaultImageUrl;
    };

    const toggleTheme = () => {
      $q.dark.toggle();

      // Guardar preferencia
      localStorage.setItem('darkMode', $q.dark.isActive);

      // Notificación de cambio de tema deshabilitada
      console.log('🎨 Tema cambiado:', $q.dark.isActive ? 'Modo oscuro activado' : 'Modo claro activado');

      emit('theme-changed', $q.dark.isActive);
    };

    const changeLanguage = (lang) => {
      currentLanguage.value = lang;
      locale.value = lang;

      // Guardar preferencia
      localStorage.setItem('language', lang);

      const selectedLanguage = languageOptions.value.find(l => l.value === lang);

      // Mostrar notificación
      $q.notify({
        type: 'positive',
        message: `${selectedLanguage?.flag} ${t('profile.languageChanged')} ${selectedLanguage?.label}`,
        icon: 'language',
        position: 'top',
        timeout: 2000
      });

      emit('language-changed', selectedLanguage);
    };

    const openEditProfile = () => {
      editForm.value.name = userName.value;
      editForm.value.email = userEmail.value;
      editForm.value.phone = userPhone.value;
      editForm.value.bio = userBio.value;
      editProfileModal.value = true;
    };

    const saveProfile = async () => {
      saving.value = true;

      try {
        // Emitir evento para que el componente padre maneje el guardado
        emit('save-profile', editForm.value);

        // Simular guardado
        await new Promise(resolve => setTimeout(resolve, 1500));

        $q.notify({
          type: 'positive',
          message: t('profile.profileUpdated'),
          icon: 'check_circle',
          position: 'top'
        });

        editProfileModal.value = false;
      } catch (error) {
        console.error('Error saving profile:', error);
        $q.notify({
          type: 'negative',
          message: t('profile.profileUpdateError'),
          position: 'top'
        });
      } finally {
        saving.value = false;
      }
    };

    // Inicialización
    onMounted(() => {
      const savedLanguage = localStorage.getItem('language') || locale.value;
      currentLanguage.value = savedLanguage;
    });

    return {
      t,
      userName,
      userEmail,
      userImageUrl,
      userPhone,
      userBio,
      userJoinDate,
      userLevelText,
      userLevelColor,
      userLevelIcon,
      defaultImageUrl,
      editProfileModal,
      saving,
      currentLanguage,
      editForm,
      languageOptions,
      isDarkMode,
      nameRules,
      emailRules,
      handleImageError,
      toggleTheme,
      changeLanguage,
      openEditProfile,
      saveProfile
    };
  }
};
</script>

<style scoped>
.user-info-card {
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

.compact-info {
  padding: 0;
}

.compact-user-item {
  border-radius: 8px;
  transition: background-color 0.2s ease;
}

.compact-user-item:hover {
  background-color: rgba(0, 0, 0, 0.04);
}

/* Dark mode support */
.body--dark .profile-banner {
  background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
}

.body--dark .profile-avatar {
  border-color: #2d3748;
}

.body--dark .compact-user-item:hover {
  background-color: rgba(255, 255, 255, 0.08);
}

/* Edit profile modal */
.edit-profile-card {
  min-width: 400px;
}

/* Language selector */
.language-select {
  min-width: 150px;
}

.language-select .q-item {
  padding: 8px 12px;
}

.language-select .q-item-section--avatar {
  min-width: 30px;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .profile-banner {
    height: 100px;
  }

  .profile-avatar-container {
    bottom: -50px;
  }

  .profile-info {
    padding-top: 70px !important;
  }

  .edit-profile-card {
    min-width: 280px;
  }

  .language-select {
    min-width: 120px;
  }
}
</style>