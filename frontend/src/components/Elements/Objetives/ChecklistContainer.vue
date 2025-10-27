<!-- ChecklistContainer.vue -->
<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
    <div class="mb-6">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Lista de verificación</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Marca las tareas completadas para seguir tu progreso
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <ChecklistItem 
        v-for="(item, index) in checklistItems" 
        :key="index"
        :item="item"
        @update:status="updateItemStatus(index, $event)"
      />
    </div>

    <!-- Botón para agregar nuevo item -->
    <button 
      @click="addNewItem"
      class="mt-6 inline-flex items-center px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
      </svg>
      Agregar tarea
    </button>
  </div>
</template>

<script>
import ChecklistItem from './ChecklistItem.vue';

export default {
  name: 'ChecklistContainer',
  components: {
    ChecklistItem,
  },
  data() {
    return {
      checklistItems: [
        {
          id: 1,
          title: 'Revisar documentación',
          description: 'Leer y entender los requerimientos del proyecto',
          completed: false,
          dueDate: '2024-03-20'
        },
        {
          id: 2,
          title: 'Configurar entorno',
          description: 'Preparar herramientas y dependencias necesarias',
          completed: false,
          dueDate: '2024-03-21'
        },
        {
          id: 3,
          title: 'Implementar funcionalidad',
          description: 'Desarrollar las características principales',
          completed: false,
          dueDate: '2024-03-22'
        }
      ]
    };
  },
  methods: {
    updateItemStatus(index, status) {
      this.checklistItems[index].completed = status;
    },
    addNewItem() {
      const newId = Math.max(...this.checklistItems.map(item => item.id)) + 1;
      this.checklistItems.push({
        id: newId,
        title: 'Nueva tarea',
        description: 'Descripción de la tarea',
        completed: false,
        dueDate: new Date().toISOString().split('T')[0]
      });
    }
  }
};
</script>

<style scoped>
/* Añadir estilos personalizados aquí si es necesario */
</style>
  