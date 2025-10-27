<template>
  <div class="normal-note-container">
    <!-- Editor de texto enriquecido -->
    <WysiwygEditor
      :content="parsedContent.text"
      @contentUpdated="updateTextContent"
    />
  </div>
</template>

<script>
import { computed } from 'vue'
import WysiwygEditor from './components/WysiwygEditor.vue'

export default {
  components: {
    WysiwygEditor,
  },
  props: {
    content: { type: String, default: '' },
    noteId: { type: [String, Number], default: null }
  },

  emits: ['update:content'],

  setup(props, { emit }) {
    // Parsear el contenido que puede ser string o JSON
    const parsedContent = computed(() => {
      try {
        if (typeof props.content === 'string' && props.content.startsWith('{')) {
          const parsed = JSON.parse(props.content)
          return {
            text: parsed.text || ''
          }
        }
        return {
          text: props.content || ''
        }
      } catch (error) {
        return {
          text: props.content || ''
        }
      }
    })

    // Actualizar el contenido de texto
    const updateTextContent = (newText) => {
      emit('update:content', newText)
    }

    return {
      parsedContent,
      updateTextContent
    }
  }
};
</script>

<style scoped>
.normal-note-container {
  padding: 1rem;
  max-width: 100%;
}

@media (max-width: 600px) {
  .normal-note-container {
    padding: 0.5rem;
  }
}
</style>