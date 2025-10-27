<template>
  <q-card class="subscription-status-card">
    <!-- Premium Status Header -->
    <q-card-section class="status-header" :class="statusHeaderClass">
      <div class="status-content">
        <div class="status-icon">
          <q-icon :name="statusIcon" :color="statusIconColor" size="lg" />
        </div>
        <div class="status-info">
          <div class="status-title">{{ statusTitle }}</div>
          <div class="status-subtitle">{{ statusSubtitle }}</div>
        </div>
      </div>
      
      <div v-if="hasActivePremium" class="premium-badge">
        <q-icon name="verified" size="sm" />
        <span>Premium</span>
      </div>
    </q-card-section>

    <!-- Subscription Details -->
    <q-card-section v-if="hasActivePremium" class="subscription-details">
      <div class="details-grid">
        <!-- Plan Type -->
        <div class="detail-item">
          <div class="detail-label">{{ $t('subscription.currentPlan') }}</div>
          <div class="detail-value">
            {{ subscription.planType === 'monthly' ? $t('subscription.monthlyPremium') : $t('subscription.annualPremium') }}
          </div>
        </div>

        <!-- Expiration Date -->
        <div class="detail-item">
          <div class="detail-label">{{ $t('subscription.nextRenewal') }}</div>
          <div class="detail-value">{{ formatExpirationDate() }}</div>
        </div>

        <!-- Days Remaining -->
        <div v-if="daysRemaining !== null" class="detail-item">
          <div class="detail-label">{{ $t('subscription.remainingDays') }}</div>
          <div class="detail-value" :class="{ 'expiring-soon': daysRemaining <= 7 }">
            {{ daysRemaining }} {{ $t('subscription.days') }}
          </div>
        </div>

        <!-- Status -->
        <div class="detail-item">
          <div class="detail-label">{{ $t('subscription.status') }}</div>
          <div class="detail-value">
            <q-badge :color="getStatusBadgeColor()" :label="getStatusLabel()" />
          </div>
        </div>
      </div>

      <!-- Progress Bar for Days Remaining -->
      <div v-if="daysRemaining !== null && subscription.planType" class="renewal-progress">
        <div class="progress-label">{{ $t('subscription.timeUntilRenewal') }}</div>
        <q-linear-progress
          :value="getRenewalProgress()"
          :color="daysRemaining <= 7 ? 'warning' : 'positive'"
          size="8px"
          rounded
          class="progress-bar"
        />
        <div class="progress-text">
          {{ daysRemaining }} {{ $t('subscription.of') }} {{ getTotalDaysInPeriod() }} {{ $t('subscription.days') }}
        </div>
      </div>
    </q-card-section>

    <!-- Free User Benefits -->
    <q-card-section v-else class="free-benefits">
      <div class="benefits-title">{{ $t('subscription.freeAccessTitle') }}</div>
      <div class="benefits-list">
        <div v-for="benefit in freeBenefits" :key="benefit" class="benefit-item">
          <q-icon name="check" color="positive" size="sm" />
          <span>{{ benefit }}</span>
        </div>
      </div>
    </q-card-section>

    <!-- Premium Benefits Preview -->
    <q-card-section v-if="!hasActivePremium" class="premium-preview">
      <div class="preview-title">{{ $t('subscription.unlockWithPremium') }}</div>
      <div class="premium-benefits">
        <div v-for="benefit in premiumBenefits" :key="benefit" class="premium-benefit">
          <q-icon name="star" color="amber" size="sm" />
          <span>{{ benefit }}</span>
        </div>
      </div>
    </q-card-section>

    <!-- Actions -->
    <q-card-actions class="status-actions">
      <q-btn
        v-if="!hasActivePremium"
        unelevated
        color="primary"
        icon="workspace_premium"
        :label="$t('subscription.activatePremium')"
        @click="$emit('go-to-settings')"
        class="upgrade-btn"
        no-caps
      />
      
      <template v-else>
        <q-btn
          flat
          color="primary"
          icon="payment"
          :label="$t('subscription.paymentHistory')"
          @click="$emit('show-history')"
          class="history-btn"
          no-caps
        />
        
        <q-space />
        
        <q-btn
          flat
          color="negative"
          icon="cancel"
          :label="$t('subscription.cancel')"
          @click="$emit('cancel-subscription')"
          class="cancel-btn"
          no-caps
        />
      </template>
    </q-card-actions>

    <!-- Loading Overlay -->
    <q-inner-loading :showing="paymentsStore.isLoading">
      <q-spinner-dots size="50px" color="primary" />
    </q-inner-loading>
  </q-card>
</template>

<script>
import { usePaymentsStore } from '@/stores/payments';
import { computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

export default {
  name: 'SubscriptionStatus',
  emits: ['show-plans', 'show-history', 'cancel-subscription', 'go-to-settings'],
  setup() {
    const paymentsStore = usePaymentsStore();
    const { t } = useI18n();

    // Computed properties
    const hasActivePremium = computed(() => paymentsStore.hasActivePremium);
    const subscription = computed(() => paymentsStore.subscription);
    const daysRemaining = computed(() => paymentsStore.daysRemaining);

    const statusTitle = computed(() => {
      if (hasActivePremium.value) {
        return t('subscription.premiumActive');
      }
      return t('subscription.freePlan');
    });

    const statusSubtitle = computed(() => {
      if (hasActivePremium.value) {
        const planType = subscription.value.planType === 'monthly'
          ? t('subscription.monthly')
          : t('subscription.annual');
        return t('subscription.activeSubscription', { planType });
      }
      return t('subscription.basicFeaturesAvailable');
    });

    const statusIcon = computed(() => {
      return hasActivePremium.value ? 'workspace_premium' : 'account_circle';
    });

    const statusIconColor = computed(() => {
      return hasActivePremium.value ? 'amber' : 'grey-6';
    });

    const statusHeaderClass = computed(() => ({
      'premium-header': hasActivePremium.value,
      'free-header': !hasActivePremium.value
    }));

    // Data
    const freeBenefits = computed(() => [
      t('subscription.freeBenefits.basicNotes'),
      t('subscription.freeBenefits.simpleGoals'),
      t('subscription.freeBenefits.basicAlarms'),
      t('subscription.freeBenefits.limitedSync'),
      t('subscription.freeBenefits.communitySupport')
    ]);

    const premiumBenefits = computed(() => [
      t('subscription.premiumBenefits.allNotes'),
      t('subscription.premiumBenefits.advancedExport'),
      t('subscription.premiumBenefits.unlimitedSync'),
      t('subscription.premiumBenefits.prioritySupport'),
      t('subscription.premiumBenefits.premiumThemes'),
      t('subscription.premiumBenefits.unlimitedStorage')
    ]);

    // Methods
    const formatExpirationDate = () => {
      if (!subscription.value.expiresAt) return t('subscription.notDefined');

      const date = new Date(subscription.value.expiresAt);
      const locale = t('common.locale');
      return date.toLocaleDateString(locale, {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    };

    const getStatusLabel = () => {
      const status = subscription.value.status;
      const labels = {
        active: t('subscription.statusLabels.active'),
        cancelled: t('subscription.statusLabels.cancelled'),
        expired: t('subscription.statusLabels.expired'),
        pending: t('subscription.statusLabels.pending'),
        inactive: t('subscription.statusLabels.inactive')
      };
      return labels[status] || t('subscription.statusLabels.unknown');
    };

    const getStatusBadgeColor = () => {
      const status = subscription.value.status;
      const colors = {
        active: 'positive',
        cancelled: 'warning',
        expired: 'negative',
        pending: 'info',
        inactive: 'grey'
      };
      return colors[status] || 'grey';
    };

    const getTotalDaysInPeriod = () => {
      return subscription.value.planType === 'monthly' ? 30 : 365;
    };

    const getRenewalProgress = () => {
      if (daysRemaining.value === null) return 0;
      
      const totalDays = getTotalDaysInPeriod();
      const elapsed = totalDays - daysRemaining.value;
      return elapsed / totalDays;
    };

    // Lifecycle
    onMounted(async () => {
      await paymentsStore.loadUserSubscription();
    });

    return {
      paymentsStore,
      hasActivePremium,
      subscription,
      daysRemaining,
      statusTitle,
      statusSubtitle,
      statusIcon,
      statusIconColor,
      statusHeaderClass,
      freeBenefits,
      premiumBenefits,
      formatExpirationDate,
      getStatusLabel,
      getStatusBadgeColor,
      getTotalDaysInPeriod,
      getRenewalProgress
    };
  }
}
</script>

<style scoped>
.subscription-status-card {
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Header styles */
.status-header {
  padding: 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.premium-header {
  background: linear-gradient(135deg, #ffa726 0%, #ff9800 100%);
  color: white;
}

.free-header {
  background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
  color: #1e293b;
}

.status-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.status-icon {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  padding: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.free-header .status-icon {
  background: rgba(255, 255, 255, 0.8);
}

.status-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 4px;
}

.status-subtitle {
  font-size: 0.875rem;
  opacity: 0.9;
}

.premium-badge {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  padding: 6px 12px;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.875rem;
  font-weight: 600;
}

/* Subscription details */
.subscription-details {
  padding: 20px 24px;
  background: #f8fafc;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.detail-item {
  background: white;
  padding: 16px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.detail-label {
  font-size: 0.75rem;
  color: #64748b;
  text-transform: uppercase;
  font-weight: 600;
  margin-bottom: 4px;
}

.detail-value {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1e293b;
}

.expiring-soon {
  color: #ea580c;
}

/* Progress bar */
.renewal-progress {
  background: white;
  padding: 16px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.progress-label {
  font-size: 0.875rem;
  color: #64748b;
  margin-bottom: 8px;
  font-weight: 500;
}

.progress-bar {
  margin-bottom: 8px;
}

.progress-text {
  font-size: 0.75rem;
  color: #64748b;
  text-align: center;
}

/* Benefits sections */
.free-benefits,
.premium-preview {
  padding: 20px 24px;
}

.benefits-title,
.preview-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 12px;
}

.benefits-list,
.premium-benefits {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.benefit-item,
.premium-benefit {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.875rem;
  color: #6b7280;
}

.premium-benefit {
  color: #92400e;
}

/* Actions */
.status-actions {
  padding: 20px 24px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

.upgrade-btn {
  width: 100%;
  min-height: 48px;
  font-weight: 600;
  border-radius: 8px;
}

.history-btn,
.cancel-btn {
  min-height: 44px;
  border-radius: 8px;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .status-header {
    padding: 20px 16px;
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .status-content {
    width: 100%;
  }
  
  .premium-badge {
    align-self: flex-end;
  }
  
  .subscription-details {
    padding: 16px;
  }
  
  .details-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .detail-item {
    padding: 12px;
  }
  
  .renewal-progress {
    padding: 12px;
  }
  
  .free-benefits,
  .premium-preview {
    padding: 16px;
  }
  
  .status-actions {
    padding: 16px;
    flex-direction: column;
    gap: 12px;
  }
  
  .history-btn {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .status-header {
    padding: 16px 12px;
  }
  
  .status-content {
    gap: 12px;
  }
  
  .status-icon {
    padding: 8px;
  }
  
  .status-title {
    font-size: 1.125rem;
  }
  
  .subscription-details {
    padding: 12px;
  }
  
  .detail-item {
    padding: 10px;
  }
}

/* Dark mode support */
.body--dark .subscription-status-card {
  background-color: #1f2937;
}

.body--dark .subscription-details {
  background-color: #111827;
}

.body--dark .detail-item {
  background-color: #374151;
  border-color: #4b5563;
}

.body--dark .detail-value {
  color: #f9fafb;
}

.body--dark .renewal-progress {
  background-color: #374151;
  border-color: #4b5563;
}

.body--dark .status-actions {
  background-color: #111827;
  border-color: #374151;
}

.body--dark .free-benefits,
.body--dark .premium-preview {
  background-color: transparent;
}

.body--dark .benefits-title,
.body--dark .preview-title {
  color: #d1d5db;
}

.body--dark .benefit-item {
  color: #9ca3af;
}

/* Touch improvements */
@media (hover: none) and (pointer: coarse) {
  .upgrade-btn,
  .history-btn,
  .cancel-btn {
    min-height: 48px;
    touch-action: manipulation;
  }
}

/* Animation for status changes */
.subscription-status-card {
  transition: all 0.3s ease;
}

.detail-value {
  transition: color 0.2s ease;
}

.progress-bar {
  transition: all 0.3s ease;
}
</style>