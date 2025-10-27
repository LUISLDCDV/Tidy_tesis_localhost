<template>
  <div v-if="show" class="loading-overlay">
    <q-spinner-hourglass 
      color="primary" 
      size="50px"
    />
  </div>
</template>

<script>
export default {
  props: {
    show: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style scoped>
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5); /* Fondo oscuro semitransparente */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999; /* Asegura que esté por encima de todo */
  backdrop-filter: blur(2px); /* Efecto de desenfoque opcional */
}

/* Removido CSS del spinner personalizado - ahora usa q-spinner-hourglass */
</style>