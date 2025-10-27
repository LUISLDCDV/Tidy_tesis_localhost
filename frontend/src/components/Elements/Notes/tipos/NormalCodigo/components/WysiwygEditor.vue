<template>
  <div class="code-editor-container">
    <!-- Barra de herramientas básica -->
    <div class="toolbar q-mb-sm">
      <q-btn-group>
        <q-btn
          @click="setLanguage('javascript')"
          :disable="!isCodeBlockActive"
          :color="isCodeBlockActive ? 'primary' : 'grey-5'"
          :text-color="isCodeBlockActive ? 'white' : 'grey-7'"
          label="JavaScript"
          size="sm"
          unelevated
        />

        <q-btn
          @click="toggleCodeBlock"
          color="grey-5"
          text-color="grey-8"
          label="Insertar Código"
          size="sm"
          unelevated
        />
        
        <q-btn
          @click="setLanguage('javascript')"
          color="primary"
          label="JavaScript"
          size="sm"
          unelevated
        />
        
        <q-btn
          @click="setLanguage('python')"
          color="positive"
          label="Python"
          size="sm"
          unelevated
        />
        
        <q-btn
          @click="setLanguage('html')"
          color="warning"
          label="HTML"
          size="sm"
          unelevated
        />
        
        <q-btn
          @click="setLanguage('css')"
          color="purple"
          label="CSS"
          size="sm"
          unelevated
        />
      </q-btn-group>
    </div>

    <!-- Editor -->
    <q-card 
      class="editor-content text-body2"
      @click="focusEditor"
      flat
      bordered
    >
      <q-card-section class="editor-card-section">
        <EditorContent :editor="editor" />
      </q-card-section>
    </q-card>
  </div>
</template>

<script>
import { useEditor,EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight'
import { createLowlight } from 'lowlight'
import javascript from 'highlight.js/lib/languages/javascript'
import python from 'highlight.js/lib/languages/python'
import xml from 'highlight.js/lib/languages/xml'
import css from 'highlight.js/lib/languages/css'
import EditorMenuBar from './EditorMenuBar.vue'
import { ref, computed, onBeforeUnmount } from 'vue'

// Crea lowlight y registra lenguajes
const lowlight = createLowlight()
lowlight.register('javascript', javascript)
lowlight.register('python', python)
lowlight.register('html', xml)
lowlight.register('css', css)

export default {
  components: {
    EditorMenuBar,
    EditorContent,
  },
  setup() {
    const editor = useEditor({
      extensions: [
        StarterKit.configure({ codeBlock: false }),
        CodeBlockLowlight.configure({ lowlight, defaultLanguage: 'plaintext' }),
      ],
      content: `<pre><code class="language-javascript">console.log('Hello World')</code></pre>`,
      editorProps: {
        attributes: {
          class: 'code-note-editor',
        },
      },
      onDestroy: () => {
        // Clean up when editor is destroyed
      },
    })

    // ✅ Propiedad reactiva para saber si hay un bloque de código activo
    const isCodeBlockActive = computed(() => {
      return editor.value?.isActive('codeBlock') || false
    })

    // Métodos
    const focusEditor = () => {
      editor.value?.chain().focus().run()
    }

    const setLanguage = (language) => {
      if (!editor.value?.isActive('codeBlock')) {
        alert('Selecciona un bloque de código primero')
        return
      }
      editor.value?.chain().focus().setCodeBlock({ language }).run()
    }

    onBeforeUnmount(() => {
      if (editor.value) {
        editor.value.destroy();
      }
    });

    return {
      editor,
      isCodeBlockActive, 
      focusEditor,
      setLanguage,
    }
  },

  methods: {
    focusEditor() {
      this.editor.chain().focus().run()
    },
    toggleCodeBlock() {
      this.editor.chain().focus().toggleCodeBlock().run()
    },
    setLanguage(language) {
      try {
        const { editor } = this

        if (!editor.isActive('codeBlock')) {
          alert('Selecciona un bloque de código para cambiar su lenguaje')
          return
        }

        editor.chain().focus().setCodeBlock({ language }).run()
      } catch (error) {
        console.error('Error al cambiar el lenguaje:', error)
        alert('No se pudo cambiar el lenguaje. Asegúrate de tener un bloque de código seleccionado.')
      }
    },
    saveCode() {
      const html = this.editor.getHTML()
      const text = this.editor.getText()
      console.log('HTML:', html)
      console.log('Texto plano:', text)
      // Guarda en Vuex, localStorage o envía al backend
    },
  },
}


</script>

<style scoped>
.tiptap.ProseMirror:focus {
  outline: none !important;
  /* background: #007bff;
  border-color: #007bff !important;
  box-shadow: 0 0 8px rgba(0, 123, 255, 0.2) !important; */
}


.editor-content {
  text-align: left;
  min-height: 300px;
}

.editor-card-section {
  font-family: 'Roboto Mono', 'Monaco', 'Consolas', monospace;
  background-color: #1e1e1e;
  color: #d4d4d4;
  border-radius: 4px;
  min-height: 300px;
  padding: 16px;
}

.editor-content:focus-within {
  outline: none;
}




.ProseMirror pre {
  background-color: #2d2d2d;
  color: #f8f8f2;
  padding: 1rem;
  border-radius: 0.5rem;
  overflow-x: auto;
  font-family: monospace;
}

.ProseMirror code {
  background-color: #f0f0f0;
  padding: 0.2em 0.4em;
  border-radius: 4px;
}

/* Colores personalizados para syntax highlighting */
.hljs-comment       { color: #75715e; } /* Comentarios */
.hljs-string        { color: #e6db74; } /* Strings */
.hljs-keyword       { color: #f92672; } /* Palabras clave como if, function, etc. */
.hljs-number        { color: #ae81ff; } /* Números */
.hljs-variable      { color: #fd971f; } /* Variables */
.hljs-function .hljs-title { color: #a6e22e; } /* Funciones */
.hljs-type           { color: #66d9ef; } /* Tipos (en TypeScript, etc.) */
</style>