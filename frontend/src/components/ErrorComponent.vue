<!-- ErrorComponent.vue -->
<template>
    <div class="error-component">
      ⚠️ Error cargando el componente
      <button @click="retry">Reintentar</button>
    </div>
  </template>
  
  <script>
  export default {
    methods: {
      retry() {
        this.$emit('retry');
      }
    }
  }
  </script>