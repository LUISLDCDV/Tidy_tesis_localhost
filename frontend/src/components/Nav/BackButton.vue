<template>
    <button
      class="back-button"
      @click="goBack"
    >
      ← Volver
    </button>
  </template>
  
  <script>
  export default {
    methods: {
      goBack() {
        
        this.$router.back(); // Navega a la ruta anterior

      },
    },
  };
  </script>
  
  <style scoped>
  .back-button {
    background-color: #227c80;
    color: white;
    /* border: none; */
    padding: 10px 15px;
    font-size: 1rem;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    position: fixed;
    bottom: 85%;
    right: 10%;
    z-index: 10000;
    border-radius: 50px; /* Borde redondeado */
    box-shadow: #333 2px 0px 10px;
  }
  
  .back-button:hover {
    background-color: #ca5d14;
  }
  
  .back-button:active {
    transform: scale(0.98);
  }
  </style>