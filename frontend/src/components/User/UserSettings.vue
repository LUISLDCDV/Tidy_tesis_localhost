<template>
  <q-page class="user-settings-page">
    <!-- Header -->
    <div class="settings-header q-mb-lg">
      <div class="row items-center justify-between">
        <div>
          <h1 class="text-h4 text-weight-bold q-pt-xl">{{ $t('settings.title') }}</h1>
          <p class="text-grey-6">{{ $t('settings.subtitle') }}</p>
        </div>
        <SyncStatusIndicator :always-show="false" />
      </div>
    </div>

    <div class="settings-container">
      <!-- Enlace al Perfil -->
      <q-card class="settings-card q-mb-md">
        <q-card-section>
          <h3 class="text-h6 text-weight-medium q-mb-md">
            <q-icon name="person" class="q-mr-sm" />
            {{ $t('settings.sections.account') }}
          </h3>

          <q-list separator>
            <!-- Ir al Perfil -->
            <q-item clickable @click="goToProfile">
              <q-item-section avatar>
                <q-icon name="account_circle" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.account.viewProfile') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.account.viewProfileCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" />
              </q-item-section>
            </q-item>

            <q-item clickable @click="openChangePassword">
              <q-item-section avatar>
                <q-icon name="lock" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.account.changePassword') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.account.changePasswordCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" />
              </q-item-section>
            </q-item>

            <q-item>
              <q-item-section avatar>
                <q-icon name="verified_user" color="positive" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.account.emailVerification') }}</q-item-label>
                <q-item-label caption>{{ emailVerified ? $t('settings.account.emailVerified') : $t('settings.account.emailPending') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-btn
                  v-if="!emailVerified"
                  flat
                  color="primary"
                  size="sm"
                  @click="sendVerificationEmail"
                  :loading="sendingVerification"
                >
                  {{ $t('settings.account.verify') }}
                </q-btn>
                <q-icon v-else name="check_circle" color="positive" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>

      <!-- Configuración de Privacidad -->
      <q-card class="settings-card q-mb-md">
        <q-card-section>
          <h3 class="text-h6 text-weight-medium q-mb-md">
            <q-icon name="privacy_tip" class="q-mr-sm" />
            {{ $t('settings.sections.privacy') }}
          </h3>

          <q-list separator>
            <q-item>
              <q-item-section avatar>
                <q-icon name="visibility" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.privacy.publicProfile') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.privacy.publicProfileCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-toggle 
                  v-model="settings.publicProfile" 
                  @update:model-value="updateSetting('publicProfile', $event)"
                  color="primary"
                />
              </q-item-section>
            </q-item>

            <q-item>
              <q-item-section avatar>
                <q-icon name="analytics" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.privacy.publicStats') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.privacy.publicStatsCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-toggle 
                  v-model="settings.showStats" 
                  @update:model-value="updateSetting('showStats', $event)"
                  color="primary"
                />
              </q-item-section>
            </q-item>

            <q-item>
              <q-item-section avatar>
                <q-icon name="location_on" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.privacy.shareLocation') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.privacy.shareLocationCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-toggle 
                  v-model="settings.shareLocation" 
                  @update:model-value="updateSetting('shareLocation', $event)"
                  color="primary"
                />
              </q-item-section>
            </q-item>

            <q-item clickable @click="openDataExport">
              <q-item-section avatar>
                <q-icon name="download" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.privacy.exportData') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.privacy.exportDataCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>

      <!-- Configuración de Notificaciones -->
      <q-card class="settings-card q-mb-md">
        <q-card-section>
          <h3 class="text-h6 text-weight-medium q-mb-md">
            <q-icon name="notifications" class="q-mr-sm" />
            {{ $t('settings.sections.notifications') }}
          </h3>

          <q-list separator>
            <q-item>
              <q-item-section avatar>
                <q-icon name="notifications_active" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.notifications.push') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.notifications.pushCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-toggle 
                  v-model="settings.pushNotifications" 
                  @update:model-value="updateNotificationSetting('pushNotifications', $event)"
                  color="primary"
                />
              </q-item-section>
            </q-item>

            <q-item>
              <q-item-section avatar>
                <q-icon name="event_note" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.notifications.eventReminders') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.notifications.eventRemindersCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-toggle 
                  v-model="settings.eventReminders" 
                  @update:model-value="updateNotificationSetting('eventReminders', $event)"
                  color="primary"
                />
              </q-item-section>
            </q-item>

            <q-item>
              <q-item-section avatar>
                <q-icon name="flag" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.notifications.goalNotifications') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.notifications.goalNotificationsCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-toggle 
                  v-model="settings.goalNotifications" 
                  @update:model-value="updateNotificationSetting('goalNotifications', $event)"
                  color="primary"
                />
              </q-item-section>
            </q-item>

            <q-item>
              <q-item-section avatar>
                <q-icon name="schedule" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.notifications.reminderTime') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.notifications.reminderTimeCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-select
                  v-model="settings.reminderTime"
                  :options="reminderTimeOptionsComputed"
                  emit-value
                  map-options
                  @update:model-value="updateSetting('reminderTime', $event)"
                  borderless
                  dense
                  class="reminder-select"
                />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>

      <!-- Configuración de Apariencia -->
      <q-card class="settings-card q-mb-md">
        <q-card-section>
          <h3 class="text-h6 text-weight-medium q-mb-md">
            <q-icon name="palette" class="q-mr-sm" />
            {{ $t('settings.sections.appearance') }}
          </h3>

          <q-list separator>
            <!-- Tema Oscuro -->
            <q-item>
              <q-item-section avatar>
                <q-icon :name="isDarkMode ? 'dark_mode' : 'light_mode'" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.appearance.darkTheme') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.appearance.darkThemeCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-toggle
                  v-model="darkModeLocal"
                  @update:model-value="toggleTheme"
                  color="primary"
                />
              </q-item-section>
            </q-item>

            <!-- Idioma -->
            <q-item>
              <q-item-section avatar>
                <q-icon name="language" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.appearance.language') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.appearance.languageCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-select
                  v-model="currentLang"
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

            <q-item>
              <q-item-section avatar>
                <q-icon name="format_size" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.appearance.fontSize') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.appearance.fontSizeCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-select
                  v-model="settings.fontSize"
                  :options="fontSizeOptionsComputed"
                  emit-value
                  map-options
                  @update:model-value="updateSetting('fontSize', $event)"
                  borderless
                  dense
                  class="font-size-select"
                />
              </q-item-section>
            </q-item>

            <!-- Personalización de Colores (Solo Premium) -->
            <q-item clickable @click="openThemeCustomizer" :disable="!isPremium">
              <q-item-section avatar>
                <q-icon name="color_lens" :color="isPremium ? 'purple' : 'grey'" />
              </q-item-section>
              <q-item-section>
                <q-item-label>
                  Personalizar Colores
                  <q-chip v-if="isPremium" color="purple" text-color="white" size="sm" class="q-ml-sm">
                    <q-icon name="workspace_premium" size="xs" />
                  </q-chip>
                </q-item-label>
                <q-item-label caption>
                  {{ isPremium ? 'Personaliza los colores de la aplicación' : 'Requiere suscripción Premium' }}
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon :name="isPremium ? 'chevron_right' : 'lock'" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>

      <!-- Configuración de Suscripción -->
      <q-card id="subscription" class="settings-card q-mb-md">
        <q-card-section>
          <h3 class="text-h6 text-weight-medium q-mb-md">
            <q-icon name="workspace_premium" class="q-mr-sm" />
            {{ $t('settings.sections.premium') }}
          </h3>

          <q-list separator>
            <q-item clickable @click="upgradeToPremium">
              <q-item-section avatar>
                <q-icon name="stars" color="purple" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.premium.upgrade') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.premium.upgradeCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-chip color="purple" text-color="white" size="sm">
                  <q-icon name="star" size="xs" class="q-mr-xs" />
                  {{ $t('settings.premium.premiumLabel') }}
                </q-chip>
              </q-item-section>
            </q-item>

            <q-item clickable @click="viewPremiumFeatures">
              <q-item-section avatar>
                <q-icon name="features" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.premium.features') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.premium.featuresCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" />
              </q-item-section>
            </q-item>

            <q-item clickable @click="manageBilling">
              <q-item-section avatar>
                <q-icon name="receipt" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.premium.billing') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.premium.billingCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" />
              </q-item-section>
            </q-item>

            <q-item clickable @click="openReportPayment">
              <q-item-section avatar>
                <q-icon name="payment" color="positive" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Informar Pago</q-item-label>
                <q-item-label caption>Notifícanos sobre tu pago realizado</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>

      <!-- Configuración de Cuenta -->
      <q-card class="settings-card q-mb-md">
        <q-card-section>
          <h3 class="text-h6 text-weight-medium q-mb-md">
            <q-icon name="account_circle" class="q-mr-sm" />
            {{ $t('settings.sections.accountManagement') }}
          </h3>

          <q-list separator>
            <q-item clickable @click="goToAlarmTests">
              <q-item-section avatar>
                <q-icon name="bug_report" color="deep-orange" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Pruebas de Alarmas</q-item-label>
                <q-item-label caption>Probar funcionamiento de alarmas nativas</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" />
              </q-item-section>
            </q-item>

            <q-item clickable @click="syncData">
              <q-item-section avatar>
                <q-icon name="sync" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.account.syncData') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.account.syncDataCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-btn flat round icon="refresh" :loading="syncing" />
              </q-item-section>
            </q-item>

            <q-item clickable @click="clearCache">
              <q-item-section avatar>
                <q-icon name="clear_all" color="orange" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.account.clearCache') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.account.clearCacheCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" />
              </q-item-section>
            </q-item>

            <q-item clickable @click="deleteAccount">
              <q-item-section avatar>
                <q-icon name="delete_forever" color="negative" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-negative">{{ $t('settings.account.deleteAccount') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.account.deleteAccountCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" />
              </q-item-section>
            </q-item>

            <q-item clickable @click="logout">
              <q-item-section avatar>
                <q-icon name="logout" color="grey" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ $t('settings.account.logout') }}</q-item-label>
                <q-item-label caption>{{ $t('settings.account.logoutCaption') }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>
    </div>


    <!-- Modal para cambiar contraseña -->
    <q-dialog v-model="changePasswordModal">
      <q-card class="change-password-card">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ $t('settings.account.changePassword') }}</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="changePasswordModal = false" />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pt-md">
          <q-form @submit="changePassword" class="q-gutter-md">
            <q-input
              v-model="passwordForm.currentPassword"
              :label="$t('settings.password.currentPassword')"
              type="password"
              filled
              :rules="[val => !!val || $t('settings.password.currentPasswordRequired')]"
            />

            <q-input
              v-model="passwordForm.newPassword"
              :label="$t('settings.password.newPassword')"
              type="password"
              filled
              :rules="passwordRules"
            />

            <q-input
              v-model="passwordForm.confirmPassword"
              :label="$t('settings.password.confirmPassword')"
              type="password"
              filled
              :rules="confirmPasswordRules"
            />

            <div class="row justify-end q-gutter-sm q-mt-md">
              <q-btn
                :label="$t('common.cancel')"
                color="grey"
                @click="changePasswordModal = false"
                flat
              />
              <q-btn
                :label="$t('common.change')"
                color="primary"
                type="submit"
                :loading="changingPassword"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Modal para personalizar tema -->
    <q-dialog v-model="themeCustomizerModal" maximized>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">
            <q-icon name="palette" class="q-mr-sm" />
            Personalización de Tema
          </div>
          <q-space />
          <q-btn icon="close" flat round dense @click="themeCustomizerModal = false" />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pt-md" style="max-height: 80vh; overflow-y: auto;">
          <ThemeCustomizer :isPremium="isPremium" @colors-updated="handleColorsUpdated" />
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Modal para informar pago -->
    <q-dialog v-model="reportPaymentModal">
      <q-card class="report-payment-card">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">
            <q-icon name="payment" class="q-mr-sm" color="positive" />
            Informar Pago Realizado
          </div>
          <q-space />
          <q-btn icon="close" flat round dense @click="reportPaymentModal = false" />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pt-md">
          <q-form @submit="submitPaymentReport" class="q-gutter-md">
            <q-banner class="bg-info text-white">
              <template v-slot:avatar>
                <q-icon name="info" />
              </template>
              Una vez realizado el pago en MercadoPago, completa este formulario para que podamos activar tu suscripción Premium.
            </q-banner>

            <q-input
              v-model="paymentForm.email"
              label="Email de MercadoPago *"
              hint="Email que usaste para realizar el pago"
              filled
              type="email"
              :rules="[val => !!val || 'Email requerido', val => /.+@.+\..+/.test(val) || 'Email inválido']"
            >
              <template v-slot:prepend>
                <q-icon name="email" />
              </template>
            </q-input>

            <q-input
              v-model="paymentForm.transactionId"
              label="ID de Transacción"
              hint="Número de transacción de MercadoPago (opcional)"
              filled
            >
              <template v-slot:prepend>
                <q-icon name="receipt_long" />
              </template>
            </q-input>

            <q-input
              v-model="paymentForm.comments"
              label="Comentarios adicionales"
              hint="Cualquier información adicional que quieras compartir"
              filled
              type="textarea"
              rows="3"
            >
              <template v-slot:prepend>
                <q-icon name="comment" />
              </template>
            </q-input>

            <div class="row justify-end q-gutter-sm q-mt-md">
              <q-btn
                label="Cancelar"
                color="grey"
                @click="reportPaymentModal = false"
                flat
              />
              <q-btn
                label="Enviar Información"
                color="positive"
                type="submit"
                :loading="sendingPaymentReport"
                icon="send"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { useQuasar } from 'quasar';
import { useI18n } from 'vue-i18n';
import { useNotificationsStore } from '@/stores/notifications';
import { useUserSettingsStore } from '@/stores/userSettings';
import { usePaymentsStore } from '@/stores/payments';
import SyncStatusIndicator from '@/components/SyncStatusIndicator.vue';
import ThemeCustomizer from '@/components/Settings/ThemeCustomizer.vue';

export default {
  name: 'UserSettings',
  components: {
    SyncStatusIndicator,
    ThemeCustomizer
  },
  data() {
    return {
      defaultImageUrl: 'https://cdn.quasar.dev/img/avatar.png',
      changePasswordModal: false,
      themeCustomizerModal: false,
      reportPaymentModal: false,
      changingPassword: false,
      sendingVerification: false,
      syncing: false,
      sendingPaymentReport: false,
      darkModeLocal: false,
      currentLang: 'es',

      // Configuraciones del usuario
      settings: {
        publicProfile: true,
        showStats: true,
        shareLocation: false,
        pushNotifications: true,
        eventReminders: true,
        goalNotifications: true,
        reminderTime: 15,
        fontSize: 'medium'
      },


      passwordForm: {
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
      },

      paymentForm: {
        email: '',
        transactionId: '',
        comments: ''
      },

      // Opciones
      languageOptions: [
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
      ],

      reminderTimeOptions: [],

      fontSizeOptions: []
    }
  },

  setup() {
    const { t } = useI18n();
    const notificationsStore = useNotificationsStore();
    const userSettingsStore = useUserSettingsStore();
    const paymentsStore = usePaymentsStore();

    return {
      t,
      notificationsStore,
      userSettingsStore,
      paymentsStore
    };
  },

  computed: {
    isDarkMode() {
      return this.$q.dark.isActive;
    },


    emailVerified() {
      return this.userSettingsStore.isEmailVerified;
    },

    isPremium() {
      return this.paymentsStore.hasActivePremium;
    },



    passwordRules() {
      return [
        val => !!val || this.$t('settings.password.required'),
        val => val.length >= 8 || this.$t('settings.password.minLength'),
        val => /(?=.*[a-z])/.test(val) || this.$t('settings.password.lowercase'),
        val => /(?=.*[A-Z])/.test(val) || this.$t('settings.password.uppercase'),
        val => /(?=.*\d)/.test(val) || this.$t('settings.password.number')
      ];
    },

    confirmPasswordRules() {
      return [
        val => !!val || this.$t('settings.password.confirmPasswordRequired'),
        val => val === this.passwordForm.newPassword || this.$t('settings.password.passwordMismatch')
      ];
    },

    reminderTimeOptionsComputed() {
      return [
        { label: this.$t('settings.reminderTime.5min'), value: 5 },
        { label: this.$t('settings.reminderTime.15min'), value: 15 },
        { label: this.$t('settings.reminderTime.30min'), value: 30 },
        { label: this.$t('settings.reminderTime.1hour'), value: 60 },
        { label: this.$t('settings.reminderTime.2hours'), value: 120 },
        { label: this.$t('settings.reminderTime.1day'), value: 1440 }
      ];
    },

    fontSizeOptionsComputed() {
      return [
        { label: this.$t('settings.fontSize.small'), value: 'small' },
        { label: this.$t('settings.fontSize.medium'), value: 'medium' },
        { label: this.$t('settings.fontSize.large'), value: 'large' }
      ];
    }
  },

  methods: {
    // Navegación
    goToProfile() {
      this.$router.push('/profile');
    },

    goToAlarmTests() {
      this.$router.push('/debug');
      this.$q.notify({
        type: 'info',
        message: 'Abriendo panel de debug de alarmas',
        position: 'top',
        timeout: 2000
      });
    },

    async updateSetting(key, value) {
      try {
        // Determinar la categoría basada en la clave
        let category = 'functionality'; // categoría por defecto
        
        if (['publicProfile', 'showStats', 'shareLocation'].includes(key)) {
          category = 'privacy';
        } else if (['fontSize', 'colorScheme', 'compactMode'].includes(key)) {
          category = 'appearance';
        } else if (key === 'reminderTime') {
          category = 'notifications';
        }
        
        this.settings[key] = value;
        await this.userSettingsStore.updateSingleSetting(category, key, value);
        
        this.$q.notify({
          type: 'positive',
          message: 'Configuración actualizada',
          position: 'top',
          timeout: 2000
        });
      } catch (error) {
        console.error('Error updating setting:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error al actualizar configuración',
          position: 'top'
        });
      }
    },

    async updateNotificationSetting(key, value) {
      try {
        this.settings[key] = value;
        
        // Actualizar configuraciones de notificaciones en el store
        await this.notificationsStore.updateSingleNotificationSetting(key, value);
        
        await this.saveSettings();
        
        this.$q.notify({
          type: 'positive',
          message: 'Configuración de notificaciones actualizada',
          position: 'top',
          timeout: 2000
        });
      } catch (error) {
        console.error('Error updating notification setting:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error al actualizar notificaciones',
          position: 'top'
        });
      }
    },

    toggleTheme() {
      this.$q.dark.toggle();
      this.darkModeLocal = this.$q.dark.isActive;

      localStorage.setItem('darkMode', this.$q.dark.isActive);

      // Notificación de cambio de tema deshabilitada
      console.log('🎨 Tema cambiado:', this.$q.dark.isActive ? 'Modo oscuro' : 'Modo claro');
    },

    async changeLanguage(lang) {
      try {
        // Guardar el tema actual para preservarlo
        const currentTheme = this.$q.dark.isActive;

        // Cambiar idioma en vue-i18n
        this.$i18n.locale = lang;
        this.currentLang = lang;
        localStorage.setItem('app_language', lang);

        // Guardar en configuraciones del usuario si está autenticado
        // SOLO actualizar el idioma, no tocar otras configuraciones de appearance
        if (this.userSettingsStore.isAuthenticated) {
          // Actualizar solo el idioma sin afectar otras configuraciones de appearance
          const currentSettings = this.userSettingsStore.getAppearanceSettings;
          const preservedTheme = currentSettings.theme;

          await this.userSettingsStore.updateSingleSetting('appearance', 'language', lang);

          // Asegurarse de que el tema se mantenga igual
          if (this.userSettingsStore.getAppearanceSettings.theme !== preservedTheme) {
            await this.userSettingsStore.updateSingleSetting('appearance', 'theme', preservedTheme);
          }
        }

        // Restaurar el tema si cambió
        if (this.$q.dark.isActive !== currentTheme) {
          this.$q.dark.set(currentTheme);
          localStorage.setItem('darkMode', currentTheme.toString());
        }

        // Forzar actualización del documento
        document.documentElement.setAttribute('lang', lang);

        const selectedLanguage = this.languageOptions.find(l => l.value === lang);

        this.$q.notify({
          type: 'positive',
          message: `${selectedLanguage?.flag} ${this.$t('common.languageChanged')} ${selectedLanguage?.label}`,
          icon: 'language',
          position: 'top',
          timeout: 2000
        });
      } catch (error) {
        console.error('Error changing language:', error);
        this.$q.notify({
          type: 'negative',
          message: this.$t('common.error'),
          position: 'top'
        });
      }
    },


    openChangePassword() {
      this.passwordForm = {
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
      };
      this.changePasswordModal = true;
    },

    async changePassword() {
      this.changingPassword = true;
      
      try {
        // Simular cambio de contraseña
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        this.$q.notify({
          type: 'positive',
          message: 'Contraseña cambiada exitosamente',
          icon: 'check_circle',
          position: 'top'
        });
        
        this.changePasswordModal = false;
      } catch (error) {
        console.error('Error changing password:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error al cambiar la contraseña',
          position: 'top'
        });
      } finally {
        this.changingPassword = false;
      }
    },

    async sendVerificationEmail() {
      this.sendingVerification = true;

      try {
        const response = await this.userSettingsStore.sendEmailVerification();

        this.$q.notify({
          type: 'positive',
          message: this.$t('settings.account.verificationSent') || 'Email de verificación enviado',
          icon: 'mail',
          position: 'top'
        });
      } catch (error) {
        console.error('Error sending verification:', error);
        this.$q.notify({
          type: 'negative',
          message: this.$t('settings.account.verificationError') || 'Error al enviar email de verificación',
          position: 'top'
        });
      } finally {
        this.sendingVerification = false;
      }
    },

    async syncData() {
      this.syncing = true;
      
      try {
        await this.userSettingsStore.syncData();
        
        this.$q.notify({
          type: 'positive',
          message: 'Datos sincronizados exitosamente',
          icon: 'sync',
          position: 'top'
        });
      } catch (error) {
        console.error('Error syncing data:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error al sincronizar datos',
          position: 'top'
        });
      } finally {
        this.syncing = false;
      }
    },

    clearCache() {
      this.$q.dialog({
        title: 'Limpiar Cache',
        message: '¿Estás seguro de que quieres limpiar todos los datos temporales?',
        cancel: true,
        persistent: false
      }).onOk(() => {
        localStorage.clear();
        
        this.$q.notify({
          type: 'positive',
          message: 'Cache limpiado exitosamente',
          icon: 'clear_all',
          position: 'top'
        });
      });
    },

    openDataExport() {
      this.$q.dialog({
        title: 'Exportar Datos',
        message: '¿Deseas descargar todos tus datos personales en formato JSON?',
        cancel: true,
        persistent: false
      }).onOk(() => {
        try {
          this.userSettingsStore.exportUserData();
          
          this.$q.notify({
            type: 'positive',
            message: 'Datos exportados exitosamente',
            icon: 'download',
            position: 'top'
          });
        } catch (error) {
          console.error('Error exporting data:', error);
          this.$q.notify({
            type: 'negative',
            message: 'Error al exportar datos',
            position: 'top'
          });
        }
      });
    },

    deleteAccount() {
      this.$q.dialog({
        title: 'Eliminar Cuenta',
        message: '¿Estás seguro de que quieres eliminar permanentemente tu cuenta? Esta acción no se puede deshacer.',
        cancel: true,
        persistent: false,
        color: 'negative'
      }).onOk(() => {
        this.$q.notify({
          type: 'negative',
          message: 'Función de eliminación en desarrollo',
          icon: 'delete_forever',
          position: 'top'
        });
      });
    },

    logout() {
      this.$q.dialog({
        title: 'Cerrar Sesión',
        message: '¿Estás seguro de que quieres cerrar sesión?',
        cancel: true,
        persistent: false
      }).onOk(() => {
        this.$q.notify({
          type: 'positive',
          message: 'Sesión cerrada exitosamente',
          icon: 'logout',
          position: 'top'
        });
        
        this.$router.push('/login');
      });
    },

    // Métodos para Premium
    async upgradeToPremium() {
      this.$q.dialog({
        title: '🚀 Pasar a Premium',
        message: '¿Estás listo para desbloquear todas las funciones avanzadas?',
        cancel: {
          label: 'Cancelar',
          flat: true,
          color: 'grey'
        },
        ok: {
          label: 'Suscribirme ✨',
          color: 'purple'
        },
        persistent: false,
        style: 'max-width: 400px'
      }).onOk(() => {
        // Abrir directamente el link de MercadoPago
        const mercadoPagoSubscriptionURL = 'https://www.mercadopago.com.ar/subscriptions/checkout?preapproval_plan_id=ce3a6bac6a8146c8a784eb36c4b64e9d';
        window.open(mercadoPagoSubscriptionURL, '_blank');

        this.$q.notify({
          type: 'info',
          message: 'Serás redirigido a MercadoPago para completar tu suscripción',
          position: 'top',
          icon: 'open_in_new',
          timeout: 3000
        });
      });
    },

    viewPremiumFeatures() {
      this.$q.dialog({
        title: '⭐ Funciones Premium',
        message: `
          <div style="text-align: left; max-width: 400px;">
            <div style="margin-bottom: 16px;">
              <strong>🚀 Funciones Avanzadas:</strong>
            </div>
            <div style="font-size: 14px; line-height: 1.6;">
              • ✨ Notas ilimitadas<br>
              • 📍 Alarmas con ubicación<br>
              • 📅 Periodicidad avanzada<br>
              • 🎯 Objetivos sin límites<br>
              • 📊 Estadísticas detalladas<br>
              • ☁️ Sincronización prioritaria<br>
              • 🎨 Temas exclusivos<br>
              • 🛠️ Soporte prioritario
            </div>
          </div>
        `,
        html: true,
        style: 'max-width: 450px',
        ok: {
          label: 'Entendido',
          color: 'primary'
        }
      });
    },

    manageBilling() {
      // Abrir panel de MercadoPago para gestionar suscripciones
      const mercadoPagoURL = 'https://www.mercadopago.com.ar/subscriptions';

      this.$q.dialog({
        title: 'Gestionar Facturación',
        message: 'Serás redirigido a MercadoPago para cargar comprobantes y gestionar tu suscripción.',
        cancel: {
          label: 'Cancelar',
          flat: true,
          color: 'grey'
        },
        ok: {
          label: 'Ir a MercadoPago',
          color: 'primary',
          icon: 'open_in_new'
        },
        persistent: false
      }).onOk(() => {
        window.open(mercadoPagoURL, '_blank');
      });
    },

    async saveSettings() {
      // Guardar configuraciones en localStorage y/o servidor
      localStorage.setItem('userSettings', JSON.stringify(this.settings));
    },

    loadSettings() {
      // Cargar configuraciones desde localStorage
      const saved = localStorage.getItem('userSettings');
      if (saved) {
        this.settings = { ...this.settings, ...JSON.parse(saved) };
      }

      // Cargar otras preferencias
      this.currentLang = localStorage.getItem('language') || 'es';
      this.darkModeLocal = localStorage.getItem('darkMode') === 'true';
      this.$q.dark.set(this.darkModeLocal);
    },

    syncLocalSettingsWithStore() {
      const storeSettings = this.userSettingsStore.getAllSettings;

      // Sincronizar configuraciones con el store
      this.settings = {
        ...this.settings,
        ...storeSettings.privacy,
        ...storeSettings.notifications,
        ...storeSettings.appearance,
        ...storeSettings.functionality
      };

      // Solo sincronizar el idioma, no el tema
      this.currentLang = storeSettings.appearance.language;

      // Mantener el tema actual del usuario, no el del store
      // Esto evita cambios no deseados cuando se actualizan otras configuraciones
      this.darkModeLocal = this.$q.dark.isActive;
    },

    openThemeCustomizer() {
      if (!this.isPremium) {
        this.$q.notify({
          type: 'warning',
          message: 'Esta función requiere una suscripción Premium',
          position: 'top',
          icon: 'workspace_premium',
          actions: [
            {
              label: 'Ver Planes',
              color: 'white',
              handler: () => {
                this.upgradeToPremium();
              }
            }
          ]
        });
        return;
      }
      this.themeCustomizerModal = true;
    },

    handleColorsUpdated(colors) {
      console.log('Colors updated:', colors);
      this.$q.notify({
        type: 'positive',
        message: 'Tema personalizado aplicado correctamente',
        position: 'top',
        icon: 'palette'
      });
      // Cerrar modal opcionalmente
      // this.themeCustomizerModal = false;
    },

    // Métodos para informar pago
    openReportPayment() {
      // Resetear el formulario
      this.paymentForm = {
        email: '',
        transactionId: '',
        comments: ''
      };
      this.reportPaymentModal = true;
    },

    async submitPaymentReport() {
      this.sendingPaymentReport = true;

      try {
        // Importar API dinámicamente
        const api = (await import('@/services/api')).default;

        // Enviar datos al backend
        const response = await api.post('/payment-reports', {
          email: this.paymentForm.email,
          transaction_id: this.paymentForm.transactionId,
          comments: this.paymentForm.comments
        });

        if (response.data.success) {
          this.$q.notify({
            type: 'positive',
            message: 'Información de pago enviada correctamente. Te contactaremos pronto.',
            icon: 'check_circle',
            position: 'top',
            timeout: 4000
          });

          this.reportPaymentModal = false;

          // Resetear formulario
          this.paymentForm = {
            email: '',
            transactionId: '',
            comments: ''
          };
        } else {
          throw new Error(response.data.message || 'Error al procesar la solicitud');
        }
      } catch (error) {
        console.error('Error reporting payment:', error);
        this.$q.notify({
          type: 'negative',
          message: error.response?.data?.message || 'Error al enviar la información. Por favor, intenta nuevamente.',
          position: 'top'
        });
      } finally {
        this.sendingPaymentReport = false;
      }
    }
  },

  async created() {
    // IMPORTANTE: Cargar tema ANTES de que se renderice el componente
    // para evitar el parpadeo de modo oscuro
    const savedDarkMode = localStorage.getItem('darkMode') === 'true';
    this.$q.dark.set(savedDarkMode);
    this.darkModeLocal = savedDarkMode;

    // Inicializar store de configuraciones de usuario
    try {
      await this.userSettingsStore.initializeSettings();

      // Sincronizar configuraciones locales con el store
      this.syncLocalSettingsWithStore();
    } catch (error) {
      console.error('Error initializing user settings:', error);
    }

    this.loadSettings();

    // Inicializar configuraciones de notificaciones
    try {
      await this.notificationsStore.fetchNotificationSettings();
    } catch (error) {
      console.error('Error loading notification settings:', error);
    }

    // Inicializar store de pagos para verificar estado premium
    try {
      await this.paymentsStore.initialize();
    } catch (error) {
      console.error('Error initializing payments store:', error);
    }
  }
}
</script>

<style scoped>
.user-settings-page {
  padding: 16px;
  max-width: 800px;
  margin: 0 auto;
}

.settings-container {
  width: 100%;
}

.settings-card {
  border-radius: 8px;
  margin-bottom: 16px;
}

.language-select,
.reminder-select,
.font-size-select {
  min-width: 120px;
}

.language-select {
  min-width: 150px;
}

.language-select .q-item {
  padding: 8px 12px;
}

.language-select .q-item-section--avatar {
  min-width: 30px;
}

.language-select .row.items-center span {
  font-size: 16px;
}

.edit-profile-card,
.change-password-card {
  min-width: 400px;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .user-settings-page {
    padding: 12px;
  }
  
  .edit-profile-card,
  .change-password-card {
    min-width: 280px;
  }

  .language-select,
  .reminder-select,
  .font-size-select {
    min-width: 100px;
  }
}

@media (max-width: 480px) {
  .user-settings-page {
    padding: 8px;
  }
  
  .settings-header h1 {
    font-size: 1.3rem;
  }

  .settings-header p {
    font-size: 0.9rem;
  }
}

/* Dark mode support */
.body--dark .settings-card {
  background: #1f2937;
}

/* Touch improvements */
@media (pointer: coarse) {
  .q-item {
    min-height: 56px;
  }
}
</style>