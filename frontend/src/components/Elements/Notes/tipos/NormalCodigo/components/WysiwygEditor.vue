<template>
  <div class="code-editor-container">
    <!-- Barra de herramientas responsive CORREGIDA -->
    <div class="toolbar q-mb-sm">
      <!-- Botón principal para insertar código -->
      <div class="row q-gutter-xs q-mb-sm">
        <q-btn
          @click="toggleCodeBlock"
          color="primary"
          icon="code"
          :label="$q.screen.gt.xs ? 'Insertar Código' : ''"
          size="sm"
          unelevated
          class="col-grow"
        />
      </div>

      <!-- Selector de lenguaje - VERSIÓN CORREGIDA -->
      <div class="row q-gutter-xs justify-center">
        <!-- Botones principales siempre visibles -->
        <q-btn
          @click="setLanguage('javascript')"
          :color="currentLanguage === 'javascript' ? 'primary' : 'grey-5'"
          :text-color="currentLanguage === 'javascript' ? 'white' : 'grey-8'"
          icon="javascript"
          label="JS"
          size="sm"
          unelevated
          dense
        />
        
        <q-btn
          @click="setLanguage('python')"
          :color="currentLanguage === 'python' ? 'positive' : 'grey-5'"
          :text-color="currentLanguage === 'python' ? 'white' : 'grey-8'"
          icon="terminal"
          label="Python"
          size="sm"
          unelevated
          dense
        />
        
        <q-btn
          @click="setLanguage('html')"
          :color="currentLanguage === 'html' ? 'warning' : 'grey-5'"
          :text-color="currentLanguage === 'html' ? 'white' : 'grey-8'"
          icon="html"
          label="HTML"
          size="sm"
          unelevated
          dense
        />
        
        <q-btn
          @click="setLanguage('css')"
          :color="currentLanguage === 'css' ? 'purple' : 'grey-5'"
          :text-color="currentLanguage === 'css' ? 'white' : 'grey-8'"
          icon="css"
          label="CSS"
          size="sm"
          unelevated
          dense
        />

        <!-- Botones adicionales para desktop -->
        <q-btn
          v-if="$q.screen.gt.sm"
          @click="setLanguage('java')"
          :color="currentLanguage === 'java' ? 'deep-orange' : 'grey-5'"
          :text-color="currentLanguage === 'java' ? 'white' : 'grey-8'"
          icon="java"
          label="Java"
          size="sm"
          unelevated
          dense
        />
        
        <q-btn
          v-if="$q.screen.gt.sm"
          @click="setLanguage('php')"
          :color="currentLanguage === 'php' ? 'indigo' : 'grey-5'"
          :text-color="currentLanguage === 'php' ? 'white' : 'grey-8'"
          icon="php"
          label="PHP"
          size="sm"
          unelevated
          dense
        />
        
        <q-btn
          v-if="$q.screen.gt.sm"
          @click="setLanguage('sql')"
          :color="currentLanguage === 'sql' ? 'teal' : 'grey-5'"
          :text-color="currentLanguage === 'sql' ? 'white' : 'grey-8'"
          icon="storage"
          label="SQL"
          size="sm"
          unelevated
          dense
        />

        <!-- Botón para más opciones SOLO en mobile -->
        <q-btn
          v-if="$q.screen.lt.md"
          color="grey-6"
          icon="more_horiz"
          size="sm"
          unelevated
          dense
        >
          <q-menu auto-close>
            <q-list dense style="min-width: 120px">
              <q-item clickable @click="setLanguage('java')">
                <q-item-section avatar>
                  <q-icon name="java" color="deep-orange" />
                </q-item-section>
                <q-item-section>Java</q-item-section>
              </q-item>
              <q-item clickable @click="setLanguage('php')">
                <q-item-section avatar>
                  <q-icon name="php" color="indigo" />
                </q-item-section>
                <q-item-section>PHP</q-item-section>
              </q-item>
              <q-item clickable @click="setLanguage('sql')">
                <q-item-section avatar>
                  <q-icon name="storage" color="teal" />
                </q-item-section>
                <q-item-section>SQL</q-item-section>
              </q-item>
              <q-item clickable @click="setLanguage('cpp')">
                <q-item-section avatar>
                  <q-icon name="code" color="blue" />
                </q-item-section>
                <q-item-section>C++</q-item-section>
              </q-item>
              <q-item clickable @click="setLanguage('ruby')">
                <q-item-section avatar>
                  <q-icon name="ruby" color="red" />
                </q-item-section>
                <q-item-section>Ruby</q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </q-btn>
      </div>

      <!-- Indicador de lenguaje actual -->
      <div v-if="isCodeBlockActive" class="row justify-center q-mt-xs">
        <q-badge color="primary" class="text-caption">
          Lenguaje: {{ getLanguageName(currentLanguage) }}
        </q-badge>
      </div>
    </div>

    <!-- Editor Mobile Optimizado -->
    <q-card 
      class="editor-content text-body2 mobile-editor"
      @click="focusEditor"
      flat
      bordered
    >
      <q-card-section class="editor-card-section q-pa-sm">
        <!-- Botones flotantes para acciones rápidas -->
        <div v-if="isCodeBlockActive" class="floating-actions">
          <q-btn
            @click.stop="copyCode"
            icon="content_copy"
            color="white"
            text-color="grey-8"
            size="sm"
            round
            flat
            class="action-btn"
          >
            <q-tooltip>Copiar código</q-tooltip>
          </q-btn>
          <q-btn
            @click.stop="clearCode"
            icon="clear"
            color="white"
            text-color="grey-8"
            size="sm"
            round
            flat
            class="action-btn"
          >
            <q-tooltip>Limpiar código</q-tooltip>
          </q-btn>
        </div>
        
        <EditorContent :editor="editor" />
      </q-card-section>
    </q-card>

    <!-- Información para mobile -->
    <div v-if="$q.screen.lt.sm" class="q-mt-sm">
      <q-banner dense class="bg-blue-1 text-blue-8 text-caption">
        <template v-slot:avatar>
          <q-icon name="info" size="16px" />
        </template>
        Toca el código para editar. Usa los botones superiores para cambiar el lenguaje.
      </q-banner>
    </div>
  </div>
</template>

<script>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight'
import { createLowlight } from 'lowlight'
import javascript from 'highlight.js/lib/languages/javascript'
import python from 'highlight.js/lib/languages/python'
import xml from 'highlight.js/lib/languages/xml'
import css from 'highlight.js/lib/languages/css'
import java from 'highlight.js/lib/languages/java'
import php from 'highlight.js/lib/languages/php'
import sql from 'highlight.js/lib/languages/sql'
import cpp from 'highlight.js/lib/languages/cpp'
import ruby from 'highlight.js/lib/languages/ruby'
import { ref, computed, onBeforeUnmount, watch } from 'vue'
import { useQuasar } from 'quasar'

// Crea lowlight y registra lenguajes
const lowlight = createLowlight()
lowlight.register('javascript', javascript)
lowlight.register('python', python)
lowlight.register('html', xml)
lowlight.register('css', css)
lowlight.register('java', java)
lowlight.register('php', php)
lowlight.register('sql', sql)
lowlight.register('cpp', cpp)
lowlight.register('ruby', ruby)

export default {
  components: {
    EditorContent,
  },
  setup() {
    const $q = useQuasar()
    const editor = useEditor({
      extensions: [
        StarterKit.configure({ 
          codeBlock: false,
          heading: {
            levels: [1, 2, 3]
          }
        }),
        CodeBlockLowlight.configure({ 
          lowlight, 
          defaultLanguage: 'javascript',
          HTMLAttributes: {
            class: 'code-block-mobile'
          }
        }),
      ],
      content: `<pre><code class="language-javascript">// Escribe tu código aquí\nconsole.log('Hello World')</code></pre>`,
      editorProps: {
        attributes: {
          class: 'code-note-editor-mobile',
          spellcheck: 'false',
        },
      },
    })

    const currentLanguage = ref('javascript')

    // Computed
    const isCodeBlockActive = computed(() => {
      return editor.value?.isActive('codeBlock') || false
    })

    // Watch para detectar cambios de lenguaje
    watch(() => editor.value?.getAttributes('codeBlock')?.language, (newLang) => {
      if (newLang) {
        currentLanguage.value = newLang
      }
    }, { immediate: true })

    // Métodos
    const focusEditor = () => {
      editor.value?.chain().focus().run()
    }

    const setLanguage = (language) => {
      if (!editor.value?.isActive('codeBlock')) {
        // Si no hay bloque de código, crear uno
        editor.value?.chain().focus().toggleCodeBlock().setCodeBlock({ language }).run()
      } else {
        editor.value?.chain().focus().setCodeBlock({ language }).run()
      }
      currentLanguage.value = language
    }

    const getLanguageName = (lang) => {
      const names = {
        javascript: 'JavaScript',
        python: 'Python',
        html: 'HTML',
        css: 'CSS',
        java: 'Java',
        php: 'PHP',
        sql: 'SQL',
        cpp: 'C++',
        ruby: 'Ruby'
      }
      return names[lang] || lang
    }

    const copyCode = async () => {
      const text = editor.value?.getText() || ''
      try {
        await navigator.clipboard.writeText(text)
        $q.notify({
          message: 'Código copiado',
          color: 'positive',
          position: 'top',
          timeout: 1000
        })
      } catch (err) {
        console.error('Error al copiar:', err)
      }
    }

    const clearCode = () => {
      $q.dialog({
        title: 'Limpiar código',
        message: '¿Estás seguro de que quieres limpiar todo el código?',
        cancel: true,
        persistent: true
      }).onOk(() => {
        editor.value?.commands.clearContent()
        editor.value?.commands.setContent('<pre><code class="language-javascript">// Escribe tu código aquí</code></pre>')
      })
    }

    onBeforeUnmount(() => {
      if (editor.value) {
        editor.value.destroy()
      }
    })

    return {
      editor,
      isCodeBlockActive,
      currentLanguage,
      focusEditor,
      setLanguage,
      getLanguageName,
      copyCode,
      clearCode,
    }
  },

  methods: {
    toggleCodeBlock() {
      this.editor.chain().focus().toggleCodeBlock().run()
    },
  },
}
</script>

<style scoped>
.code-editor-container {
  min-height: 400px;
}

/* Toolbar mobile */
.toolbar {
  padding: 8px 4px;
}

/* Editor mobile */
.mobile-editor {
  border-radius: 12px;
  min-height: 350px;
  max-height: 70vh;
  overflow: hidden;
}

.editor-card-section {
  font-family: 'Roboto Mono', 'Monaco', 'Consolas', 'Courier New', monospace;
  background-color: #1e1e1e;
  color: #d4d4d4;
  border-radius: 8px;
  min-height: 350px;
  max-height: 65vh;
  overflow-y: auto;
  position: relative;
  padding: 12px 8px;
}

/* Botones flotantes */
.floating-actions {
  position: absolute;
  top: 8px;
  right: 8px;
  z-index: 10;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.action-btn {
  background: rgba(255, 255, 255, 0.9) !important;
  backdrop-filter: blur(4px);
}

/* Mejoras de responsive */
@media (max-width: 599px) {
  .code-editor-container {
    min-height: 350px;
  }
  
  .mobile-editor {
    min-height: 300px;
  }
  
  .editor-card-section {
    min-height: 300px;
    padding: 10px 6px;
    font-size: 0.9em;
  }
  
  .toolbar {
    padding: 6px 2px;
  }
}

@media (max-width: 380px) {
  .editor-card-section {
    font-size: 0.85em;
    padding: 8px 4px;
  }
  
  .mobile-editor {
    min-height: 280px;
  }
}

/* Scrollbar personalizado para mobile */
.editor-card-section::-webkit-scrollbar {
  width: 6px;
}

.editor-card-section::-webkit-scrollbar-track {
  background: #2d2d2d;
  border-radius: 3px;
}

.editor-card-section::-webkit-scrollbar-thumb {
  background: #555;
  border-radius: 3px;
}

.editor-card-section::-webkit-scrollbar-thumb:hover {
  background: #777;
}

/* Estilos para el contenido del editor */
:deep(.code-note-editor-mobile) {
  min-height: 300px;
  outline: none;
  line-height: 1.5;
}

:deep(.code-block-mobile) {
  background: #2d2d2d;
  color: #f8f8f2;
  padding: 12px;
  border-radius: 6px;
  overflow-x: auto;
  font-size: 0.9em;
  margin: 8px 0;
  border-left: 4px solid #007bff;
}

:deep(.code-block-mobile pre) {
  margin: 0;
  padding: 0;
  background: transparent !important;
}

/* Mejoras de syntax highlighting para mobile */
:deep(.hljs-comment)       { color: #75715e; font-style: italic; }
:deep(.hljs-string)        { color: #e6db74; }
:deep(.hljs-keyword)       { color: #f92672; font-weight: bold; }
:deep(.hljs-number)        { color: #ae81ff; }
:deep(.hljs-variable)      { color: #fd971f; }
:deep(.hljs-function .hljs-title) { color: #a6e22e; }
:deep(.hljs-type)          { color: #66d9ef; }
:deep(.hljs-attr)          { color: #a6e22e; }
:deep(.hljs-built_in)      { color: #66d9ef; }

/* Mejorar la selección de texto */
:deep(.code-block-mobile ::selection) {
  background: #264f78;
  color: white;
}

/* Ajustes para pantallas muy pequeñas */
@media (max-width: 320px) {
  .editor-card-section {
    font-size: 0.8em;
  }
  
  :deep(.code-block-mobile) {
    padding: 8px;
    font-size: 0.85em;
  }
}

/* Estilos específicos para desktop */
@media (min-width: 1024px) {
  .toolbar {
    padding: 12px 8px;
  }
  
  .editor-card-section {
    padding: 16px 12px;
    font-size: 1em;
  }
}
</style>