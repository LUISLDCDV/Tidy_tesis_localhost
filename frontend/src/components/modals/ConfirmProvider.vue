<template>
  <ConfirmModal
    v-if="confirmationState.show"
    :message="confirmationState.message"
    @confirm="confirmYes"
    @cancel="confirmNo"
  />
</template>

<script>
import ConfirmModal from './ConfirmModal.vue';

export default {
  name: 'ConfirmProvider',
  components: { ConfirmModal },
  data() {
    return {
      confirmationState: {
        show: false,
        message: '',
        resolveFn: null     // renombramos a algo más claro
      }
    };
  },
  mounted() {
    window.addEventListener('show-confirm-modal', this.handleShowModal);
  },
  beforeUnmount() {
    window.removeEventListener('show-confirm-modal', this.handleShowModal);
  },
  methods: {
    handleShowModal(e) {
      // Extraemos la misma propiedad que le pasamos en useConfirm()
      const { message, resolverFunction } = e.detail;

      this.confirmationState = {
        show: true,
        message,
        resolveFn: resolverFunction
      };
    },
    confirmYes() {
      this.confirmationState.show = false;
      if (this.confirmationState.resolveFn) {
        this.confirmationState.resolveFn(true);
        this.confirmationState.resolveFn = null;
      }
    },
    confirmNo() {
      this.confirmationState.show = false;
      if (this.confirmationState.resolveFn) {
        this.confirmationState.resolveFn(false);
        this.confirmationState.resolveFn = null;
      }
    }
  }
};
</script>
