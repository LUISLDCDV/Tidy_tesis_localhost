<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
    <div class="mb-6">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
        {{ isEditing ? 'Editar Objetivo' : 'Nuevo Objetivo' }}
      </h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        {{ isEditing ? 'Modifica los detalles del objetivo' : 'Establece un nuevo objetivo' }}
      </p>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Nombre -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Nombre del objetivo
        </label>
        <input 
          v-model="formData.nombre" 
          type="text" 
          required
          placeholder="Ejemplo: Aprender Vue.js"
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
          placeholder="Describe tu objetivo..."
          class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-transparent"
        ></textarea>
      </div>

      <!-- Fecha de inicio -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Fecha de inicio
        </label>
        <input 
          v-model="formData.fechaInicio" 
          type="date"
          required
          class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
        >
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
          :min="formData.fechaInicio"
          class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
        >
      </div>

      <!-- Tipo -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Tipo de objetivo
        </label>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
          <button 
            v-for="type in types" 
            :key="type.value"
            type="button"
            class="flex items-center justify-center px-4 py-2 rounded-lg border transition-colors"
            :class="[
              formData.tipo === type.value 
                ? 'bg-primary text-white border-transparent' 
                : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'
            ]"
            @click="formData.tipo = type.value"
          >
            <span class="mr-2" v-html="type.icon"></span>
            {{ type.label }}
          </button>
        </div>
      </div>

      <!-- Categoría -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Categoría
        </label>
        <select 
          v-model="formData.categoria"
          required
          class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
        >
          <option v-for="category in categories" :key="category.value" :value="category.value">
            {{ category.label }}
          </option>
        </select>
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

      <!-- Recordatorios -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Recordatorios
        </label>
        <div class="space-y-3">
          <div 
            v-for="(reminder, index) in formData.recordatorios" 
            :key="index"
            class="flex items-center gap-3"
          >
            <input 
              v-model="reminder.fecha" 
              type="datetime-local"
              class="flex-1 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
            >
            <button 
              type="button"
              @click="removeReminder(index)"
              class="p-2 text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
          <button 
            type="button"
            @click="addReminder"
            class="inline-flex items-center text-sm text-primary hover:text-primary/90"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Agregar recordatorio
          </button>
        </div>
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
  name: 'EditObjetive',
  props: {
    objetivo: {
      type: Object,
      default: () => ({
        nombre: '',
        descripcion: '',
        fechaInicio: new Date().toISOString().split('T')[0],
        fechaVencimiento: '',
        tipo: 'personal',
        categoria: 'desarrollo',
        prioridad: 'media',
        recordatorios: []
      })
    }
  },
  data() {
    return {
      formData: { ...this.objetivo },
      types: [
        {
          value: 'personal',
          label: 'Personal',
          icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>`
        },
        {
          value: 'profesional',
          label: 'Profesional',
          icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
                  <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z" />
                </svg>`
        },
        {
          value: 'academico',
          label: 'Académico',
          icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                </svg>`
        },
        {
          value: 'financiero',
          label: 'Financiero',
          icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                </svg>`
        }
      ],
      categories: [
        { value: 'desarrollo', label: 'Desarrollo Personal' },
        { value: 'salud', label: 'Salud y Bienestar' },
        { value: 'carrera', label: 'Carrera Profesional' },
        { value: 'educacion', label: 'Educación' },
        { value: 'finanzas', label: 'Finanzas' },
        { value: 'relaciones', label: 'Relaciones' },
        { value: 'otros', label: 'Otros' }
      ],
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
      ]
    };
  },
  computed: {
    isEditing() {
      return Object.keys(this.objetivo).length > 0;
    }
  },
  methods: {
    handleSubmit() {
      this.$emit('submit', this.formData);
    },
    addReminder() {
      if (!this.formData.recordatorios) {
        this.formData.recordatorios = [];
      }
      this.formData.recordatorios.push({
        fecha: new Date().toISOString().slice(0, 16)
      });
    },
    removeReminder(index) {
      this.formData.recordatorios.splice(index, 1);
    }
  }
};
</script>
