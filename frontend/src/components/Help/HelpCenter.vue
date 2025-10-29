<template>
  <div class="help-center-container">
    <!-- Hero Section -->
    <div class="text-center q-pa-lg">
      <q-icon name="help_center" size="80px" class="q-mb-md" />
      <h1 class="text-h3 text-weight-bold q-mb-sm">{{ $t('help.title') }}</h1>
      <p class="text-h6 text-grey-7">{{ $t('help.subtitle') }}</p>
    </div>
    

    <!-- FAQ Section -->
    <div class="faq-section q-pa-lg">
      <h2 class="text-h4 text-center q-mb-lg text-weight-medium">
        {{ $t('help.faq.title') }}
      </h2>

      <div class="faq-container max-width-800 q-mx-auto">
        <q-expansion-item
          v-for="(faq, index) in faqs"
          :key="index"
          :icon="faq.icon"
          :label="faq.question"
          class="faq-item q-mb-sm"
          expand-separator
          header-class="text-weight-medium"
          :default-opened="index === 0"
        >
          <template v-slot:header>
            <q-item-section avatar>
              <q-icon :name="faq.icon" color="primary" size="24px" />
            </q-item-section>
            <q-item-section>
              <div class="text-body1 text-weight-medium">{{ faq.question }}</div>
            </q-item-section>
          </template>

          <q-card class="faq-answer">
            <q-card-section class="text-body1 text-grey-8">
              <div v-html="faq.answer"></div>
            </q-card-section>
          </q-card>
        </q-expansion-item>
      </div>
    </div>

    <!-- Comments and Assistance Section -->
    <div class="feedback-section  q-pa-xl">
      <div class="max-width-800 q-mx-auto">
        <div class="text-center q-mb-lg">
          <q-icon name="feedback" size="60px" color="primary" class="q-mb-md" />
          <h3 class="text-h5 text-weight-medium q-mb-md">
            {{ $t('help.feedback.title') }}
          </h3>
          <p class="text-body1 text-grey-7">
            {{ $t('help.feedback.description') }}
          </p>
        </div>

        <q-form @submit="submitFeedback" class="feedback-form">
          <div class="row q-gutter-md">
            <div class="col-12 col-md-5">
              <q-input
                v-model="feedbackForm.name"
                :label="$t('help.feedback.form.name')"
                outlined
                required
                :rules="[val => !!val || $t('validation.required')]"
              />
            </div>
            <div class="col-12 col-md-6">
              <q-input
                v-model="feedbackForm.email"
                :label="$t('help.feedback.form.email')"
                type="email"
                outlined
                required
                :rules="[
                  val => !!val || $t('validation.required'),
                  val => /.+@.+\..+/.test(val) || $t('validation.email')
                ]"
              />
            </div>
          </div>

          <div class="q-mt-md">
            <q-select
              v-model="feedbackForm.type"
              :options="feedbackTypes"
              :label="$t('help.feedback.form.type')"
              outlined
              required
              emit-value
              map-options
              :rules="[val => !!val || $t('validation.required')]"
            />
          </div>

          <div class="q-mt-md">
            <q-input
              v-model="feedbackForm.subject"
              :label="$t('help.feedback.form.subject')"
              outlined
              required
              :rules="[val => !!val || $t('validation.required')]"
            />
          </div>

          <div class="q-mt-md">
            <q-input
              v-model="feedbackForm.message"
              :label="$t('help.feedback.form.message')"
              type="textarea"
              outlined
              required
              rows="4"
              :rules="[val => !!val || $t('validation.required')]"
            />
          </div>

          <div class="text-center q-mt-lg">
            <q-btn
              type="submit"
              color="primary"
              size="lg"
              icon="send"
              :label="$t('help.feedback.form.submit')"
              :loading="submitting"
              class="q-px-xl"
              unelevated
            />
          </div>
        </q-form>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useQuasar } from 'quasar'

export default {
  name: 'HelpCenter',

  setup() {
    const { t } = useI18n({ useScope: 'global' })
    const $q = useQuasar()

    const submitting = ref(false)
    const feedbackForm = ref({
      name: '',
      email: '',
      type: '',
      subject: '',
      message: ''
    })

    const feedbackTypes = computed(() => [
      { label: t('help.feedback.types.suggestion'), value: 'suggestion' },
      { label: t('help.feedback.types.bug'), value: 'bug' },
      { label: t('help.feedback.types.support'), value: 'support' },
      { label: t('help.feedback.types.feature'), value: 'feature' },
      { label: t('help.feedback.types.other'), value: 'other' }
    ])

    const faqs = computed(() => [
      {
        icon: 'info',
        question: t('help.faq.q1.question'),
        answer: t('help.faq.q1.answer')
      },
      {
        icon: 'task_alt',
        question: t('help.faq.q2.question'),
        answer: t('help.faq.q2.answer')
      },
      {
        icon: 'notifications',
        question: t('help.faq.q3.question'),
        answer: t('help.faq.q3.answer')
      },
      {
        icon: 'star',
        question: t('help.faq.q4.question'),
        answer: t('help.faq.q4.answer')
      },
      {
        icon: 'calendar_month',
        question: t('help.faq.q5.question'),
        answer: t('help.faq.q5.answer')
      },
      {
        icon: 'note',
        question: t('help.faq.q6.question'),
        answer: t('help.faq.q6.answer')
      },
      {
        icon: 'dark_mode',
        question: t('help.faq.q7.question'),
        answer: t('help.faq.q7.answer')
      },
      {
        icon: 'language',
        question: t('help.faq.q8.question'),
        answer: t('help.faq.q8.answer')
      },
      {
        icon: 'workspace_premium',
        question: t('help.faq.q9.question'),
        answer: t('help.faq.q9.answer')
      },
      {
        icon: 'security',
        question: t('help.faq.q10.question'),
        answer: t('help.faq.q10.answer')
      }
    ])

    const submitFeedback = async () => {
      submitting.value = true

      try {
        // Simular envío (aquí irías al backend)
        await new Promise(resolve => setTimeout(resolve, 1500))

        $q.notify({
          type: 'positive',
          message: t('help.feedback.success'),
          icon: 'check_circle'
        })

        // Limpiar formulario
        feedbackForm.value = {
          name: '',
          email: '',
          type: '',
          subject: '',
          message: ''
        }
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: t('help.feedback.error'),
          icon: 'error'
        })
      } finally {
        submitting.value = false
      }
    }

    return {
      faqs,
      feedbackForm,
      feedbackTypes,
      submitting,
      submitFeedback,
      t
    }
  }
}
</script>

<style scoped>
.help-center-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.hero-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  margin-bottom: 2rem;
}

.hero-section h1 {
  text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.faq-section {
  max-width: 1000px;
  margin: 0 auto;
}

.max-width-800 {
  max-width: 800px;
}

.max-width-600 {
  max-width: 600px;
}

.faq-item {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  overflow: hidden;
  transition: all 0.3s ease;
}

.faq-item:hover {
  box-shadow: 0 4px 16px rgba(0,0,0,0.15);
  transform: translateY(-2px);
}

.faq-answer {
  background: #f8f9fa;
  border: none;
  box-shadow: none;
}

.faq-answer .q-card-section {
  padding: 1.5rem;
  line-height: 1.6;
}

.feedback-section {
  border-top: 1px solid #e0e0e0;
}

.feedback-form {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.1);
  margin-top: 2rem;
}

.feedback-section .q-btn {
  box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
  transition: all 0.3s ease;
}

.feedback-section .q-btn:hover {
  box-shadow: 0 6px 20px rgba(25, 118, 210, 0.4);
  transform: translateY(-2px);
}

.feedback-section .q-input {
  margin-bottom: 0.5rem;
}

.feedback-section .q-select {
  margin-bottom: 0.5rem;
}

/* Dark mode styles */
.body--dark .help-center-container {
  background: linear-gradient(135deg, #2d2d30 0%, #1e1e1e 100%);
}

.body--dark .faq-item {
  background: #2d2d2d;
  border: 1px solid #404040;
}

.body--dark .faq-answer {
  background: #1e1e1e;
  color: #e0e0e0;
}

.body--dark .feedback-section {
  background: #2d2d2d !important;
  border-top-color: #404040;
}

.body--dark .feedback-form {
  background: #1e1e1e;
  border-color: #404040;
  color: #e0e0e0;
}

/* Mobile responsive */
@media (max-width: 600px) {
  .hero-section {
    padding: 2rem 1rem;
  }

  .hero-section h1 {
    font-size: 2rem;
  }

  .faq-section {
    padding: 1rem;
  }

  .support-section {
    padding: 2rem 1rem;
  }
}

/* Animation for FAQ items */
.faq-item .q-expansion-item__content {
  animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>