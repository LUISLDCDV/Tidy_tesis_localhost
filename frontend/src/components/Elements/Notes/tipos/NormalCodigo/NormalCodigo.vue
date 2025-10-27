<template>
  <div class="bg-white dark:bg-gray-900 min-h-screen">
    <!-- <h1>Editor codigo</h1> -->
    <BackButton />

    <WysiwygEditor />
  </div>
</template>

<script>
// TODO: FINALIZAR EL COMPONENTE DE EDITOR DE CODIGO
// TODO: Error cargar y guarda 

import WysiwygEditor from './components/WysiwygEditor.vue';
import BackButton from '@/components/Nav/BackButton.vue';

export default {
  components: {
    WysiwygEditor,
    BackButton,
  },
  props: {
    nombre: { type: String, default: 'Sin título' },
    noteData: { type: Object, default: () => ({}) },
    type: { type: Number, required: true },
  },
  data() {
    return {
      updatedNombre: this.nombre,
    };
  },
};
</script>
