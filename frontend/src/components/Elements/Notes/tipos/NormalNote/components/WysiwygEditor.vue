<template>
  <div class="editor-wrapper">
    <q-card class="editor-card">
      <q-card-section class="q-pa-md">
        <!-- Barra de herramientas del editor -->
        <EditorMenuBar v-if="editor" :editor="editor" class="q-mb-md" />

        <!-- Contenido editable -->
        <div class="editor-content-wrapper" @click="focusEditor">
          <EditorContent ref="editorContentRef" :editor="editor" class="editor-content" />
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script>
import { ref, onBeforeUnmount, watch } from 'vue';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';
import EditorMenuBar from './EditorMenuBar.vue';

export default {
  props: {
      content: { type: String, default: '' 
    },
    modo: {
      type: String,
      default: 'crear',
    },
  },
  components: {
    EditorContent,
    EditorMenuBar,
  },
  emits: ['titleUpdated', 'contentUpdated'], // Emitir eventos al padre
  setup(props, { emit }) {


    const editor = useEditor({
      extensions: [
        StarterKit.configure({
          // Desactivar underline del StarterKit para evitar duplicados
          underline: false,
        }),
        Underline,
        TextAlign.configure({ types: ['heading', 'paragraph'] }),
      ],
      content: props.content || '<p>Start editing...</p>',
      editorProps: {
        attributes: {
          class: 'normal-note-editor',
        },
      },
      onUpdate: ({ editor }) => {
        emit('contentUpdated', editor.getHTML());
      },
    });

    const focusEditor = () => {
      if (editor && typeof editor.chain === 'function') {
        editor.chain().focus().run();
      }
    };

    const updateTitle = (event) => {
      localNombre.value = event.target.innerText;
      emit('titleUpdated', localNombre.value);
    };

    watch(
      () => props.content,
      (newContent) => {
        if (editor.value && newContent !== editor.value.getHTML()) {
          editor.value.commands.setContent(newContent || '<p>Start editing...</p>');
        }
      }
    );

    onBeforeUnmount(() => {
      if (editor.value) {
        editor.value.destroy();
      }
    });

    return {
      editor,
      focusEditor,
      updateTitle,
    };
  },
  methods: {
    // Método para manejar el evento de actualización del título
    async created() {
        const modo = this.$route.params.modo || 'crear';
        console.log('modo:', modo);
        // Si el modo es 'editar', buscar la nota
        if (modo === 'editar') {
          // Si hay una nota en Vuex o localStorage, usarla
          console.log('Nota actual dentro : ', this.notaActual);
          this.updatedNombre = this.notaActual.nombre;
          this.updatedContent = this.notaActual.contenido;
          // this.$router.push({
          //   name: 'DynamicNote',
          //   params: { 
          //     type: this.notaActual.tipo_nota_id,
          //     id: this.notaActual.elemento_id,
          //     modo: 'editar',
          //    },
          // });
        } else {
          // Modo creación: inicializar nota vacía
          this.initializeNewNote();
        }
      
    },
    


  },
  

  
};
</script>


<style scoped>
.editor-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #f9fafb;
  min-height: 100%;
}

.editor-card {
  width: 100%;
  max-width: 48rem; /* max-w-3xl equivalent */
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.editor-content-wrapper {
  background-color: #f3f4f6;
  padding: 4px;
  border-radius: 8px;
  cursor: text;
}

.editor-content {
  background-color: white;
  border-radius: 8px;
  padding: 4px;
  min-height: 200px;
  color: #111827;
}

/* TipTap Editor Styles */
.tiptap.ProseMirror:focus {
  outline: none !important;
  background-color: #f3f4f6;
  border-radius: 0.3rem;
  caret-color: #0c5761;
}

.ProseMirror-focused {
  outline: none !important;
}

.ProseMirror h1 {
  font-size: 2em;
  font-weight: bold;
  color: #111827;
  margin-bottom: 0.5em;
}

.ProseMirror h2 {
  font-size: 1.75em;
  font-weight: bold;
  color: #1f2937;
  margin-bottom: 0.5em;
}

.ProseMirror h3 {
  font-size: 1.5em;
  font-weight: bold;
  color: #374151;
  margin-bottom: 0.5em;
}

.tiptap .ProseMirror p {
  font-size: 1em;
  color: #991b1b;
  margin-bottom: 0.5em;
}

.ProseMirror blockquote {
  padding-left: 1em;
  border-left: 4px solid #ddd;
  color: #555;
  font-style: italic;
  margin: 1em 0;
}

.ProseMirror ul {
  list-style-type: disc;
  padding-left: 1.5em;
  margin: 0.5em 0;
}

.ProseMirror ul li {
  margin-bottom: 0.3em;
}

.ProseMirror ol {
  list-style-type: decimal;
  padding-left: 1.5em;
  margin: 0.5em 0;
}

.ProseMirror ol li {
  margin-bottom: 0.3em;
}

.ProseMirror ul ul,
.ProseMirror ol ol {
  margin-left: 1.5em;
}

.ProseMirror code {
  background-color: #f3f4f6;
  padding: 0.2em 0.4em;
  border-radius: 4px;
  font-family: 'Courier New', monospace;
  font-size: 0.9em;
}

.ProseMirror pre {
  background-color: #1f2937;
  border-radius: 0.5rem;
  color: white;
  font-family: 'Courier New', monospace;
  margin: 1.5rem 0;
  padding: 0.75rem 1rem;
  overflow-x: auto;
}

.ProseMirror pre code {
  background: none;
  color: inherit;
  font-size: 0.8rem;
  padding: 0;
}

/* Dark mode support */
.body--dark .editor-wrapper {
  background-color: #111827;
}

.body--dark .editor-card {
  background-color: #1f2937;
}

.body--dark .editor-content-wrapper {
  background-color: #374151;
}

.body--dark .editor-content {
  background-color: #1f2937;
  color: white;
}

.body--dark .ProseMirror h1,
.body--dark .ProseMirror h2,
.body--dark .ProseMirror h3 {
  color: white;
}

.body--dark .tiptap.ProseMirror:focus {
  background-color: #374151;
}
</style>


