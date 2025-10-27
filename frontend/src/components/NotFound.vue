<!-- src/components/NotFound.vue -->
<template>
    <div class="not-found">
      <h1>404 - Not Found</h1>
      <p>La vista solicitada no existe</p>
      <button @click="this.$router.go(-1)">Volver atrás</button>
    </div>
  </template>
  
<script>


export default {
  name: 'NotFound',
  data() {
    return {
      componentLoader: null,
    };
  },
};
// async created() {
//     try {
//       const component = await this.currentComponent();
//       this.componentLoader = component;
//     } catch (error) {
//       console.error('Error loading component:', error);
//       this.componentLoader = (await import('@/components/NotFound.vue')).default;
//     }
//   }

</script>

  <style scoped>
  .not-found {
    text-align: center;
    padding: 2rem;
    color: #ff4444;
  }
  </style>