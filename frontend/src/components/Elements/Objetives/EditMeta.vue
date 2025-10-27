<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
    <div class="mb-6">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
        {{ isEditing ? 'Editar Meta' : 'Nueva Meta' }}
      </h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        {{ isEditing ? 'Modifica los detalles de la meta' : 'Crea una nueva meta para tu objetivo' }}
      </p>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Nombre -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Nombre de la meta
        </label>
        <input 
          v-model="formData.nombre" 
          type="text" 
          required
          placeholder="Ejemplo: Completar módulo de autenticación"
          class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-transparent"
        >
      </div>

      <!-- Descripción -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Descripción
        </label>
        <textarea 
          v-model="formData.descripcion" 
          rows="3"
          placeholder="Describe los detalles de esta meta..."
          class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-transparent"
        ></textarea>
      </div>

      <!-- Fecha de vencimiento -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Fecha de vencimiento
        </label>
        <input 
          v-model="formData.fechaVencimiento" 
          type="date"
          required
          class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
        >
      </div>

      <!-- Prioridad -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Prioridad
        </label>
        <div class="grid grid-cols-3 gap-3">
          <button 
            v-for="priority in priorities" 
            :key="priority.value"
            type="button"
            class="flex items-center justify-center px-4 py-2 rounded-lg border transition-colors"
            :class="[
              formData.prioridad === priority.value 
                ? 'bg-primary text-white border-transparent' 
                : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'
            ]"
            @click="formData.prioridad = priority.value"
          >
            <span class="mr-2" v-html="priority.icon"></span>
            {{ priority.label }}
          </button>
        </div>
      </div>

      <!-- Estado -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Estado
        </label>
        <select 
          v-model="formData.estado"
          required
          class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
        >
          <option v-for="status in statuses" :key="status.value" :value="status.value">
            {{ status.label }}
          </option>
        </select>
      </div>

      <!-- Botones -->
      <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button 
          v-if="isEditing"
          type="button"
          @click="$emit('delete')"
          class="px-4 py-2 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-lg hover:bg-red-200 dark:hover:bg-red-800 transition-colors"
        >
          <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            Eliminar
          </div>
        </button>
        <button 
          type="button"
          @click="$emit('cancel')"
          class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
        >
          Cancelar
        </button>
        <button 
          type="submit"
          class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors"
        >
          {{ isEditing ? 'Actualizar' : 'Crear' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  name: 'EditMeta',
  props: {
    meta: {
      type: Object,
      default: () => ({
        nombre: '',
        descripcion: '',
        fechaVencimiento: new Date().toISOString().split('T')[0],
        prioridad: 'media',
        estado: 'pendiente'
      })
    }
  },
  data() {
    return {
      formData: { ...this.meta },
      priorities: [
        {
          value: 'baja',
          label: 'Baja',
          icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M3 3a1 1 0 000 2h14a1 1 0 100-2H3z" />
                </svg>`
        },
        {
          value: 'media',
          label: 'Media',
          icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" />
                </svg>`
        },
        {
          value: 'alta',
          label: 'Alta',
          icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" />
                </svg>`
        }
      ],
      statuses: [
        { value: 'pendiente', label: 'Pendiente' },
        { value: 'en_progreso', label: 'En Progreso' },
        { value: 'completada', label: 'Completada' },
        { value: 'cancelada', label: 'Cancelada' }
      ]
    };
  },
  computed: {
    isEditing() {
      return Object.keys(this.meta).length > 0;
    }
  },
  methods: {
    handleSubmit() {
      this.$emit('submit', this.formData);
    }
  }
};
</script>
