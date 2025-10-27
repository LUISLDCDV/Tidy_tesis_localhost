<template>
  <div class="claves-container">
    <div class="claves-content">
      <!-- Formulario para agregar nuevas claves -->
      <div class="form-card">
        <h2 class="form-title">Agregar nueva clave</h2>
        <div class="form-fields">
          <input
            v-model="newEntry.service"
            placeholder="Servicio (ej: Gmail)"
            class="form-input"
          />
          <input
            v-model="newEntry.username"
            placeholder="Usuario/Email"
            class="form-input"
          />
          <div class="password-input-wrapper">
            <input
              v-model="newEntry.password"
              :type="showNewPassword ? 'text' : 'password'"
              placeholder="Contraseña"
              class="form-input"
            />
            <button
              @click="toggleNewPassword"
              class="toggle-password-btn"
              type="button"
            >
              <q-icon :name="showNewPassword ? 'visibility' : 'visibility_off'" size="20px" />
            </button>
          </div>
          <button
            @click="addEntry"
            class="submit-btn"
          >
            Guardar clave
          </button>
        </div>
      </div>

      <!-- Lista de claves guardadas -->
      <div v-for="(entry, index) in entries" :key="index" class="entry-card">
        <div class="entry-info">
          <h3 class="entry-service">{{ entry.service }}</h3>
          <p class="entry-username">{{ entry.username }}</p>
        </div>

        <div class="entry-actions">
          <div class="password-display">
            <input
              :value="entry.password"
              :type="entry.visible ? 'text' : 'password'"
              readonly
              class="password-field"
            />
            <button
              @click="toggleVisibility(index)"
              class="action-btn"
              type="button"
            >
              <q-icon :name="entry.visible ? 'visibility' : 'visibility_off'" size="20px" />
            </button>
          </div>

          <button
            @click="editEntry(index)"
            class="action-btn edit-btn"
            type="button"
          >
            <q-icon name="edit" size="20px" />
          </button>

          <button
            @click="deleteEntry(index)"
            class="action-btn delete-btn"
            type="button"
          >
            <q-icon name="delete" size="20px" />
          </button>
        </div>
      </div>
    </div>
  </div>



</template>

<script>
import { useConfirm } from '@/services/useConfirm';


import { mapGetters, mapActions } from '@/utils/vuex-compatibility';

export default {
  props: {
    content: {
      type: String,
      default: '[]'
    }
  },

  data() {
    return {
      newEntry: {
        service: '',
        username: '',
        password: ''
      },
      showNewPassword: false,
      entries: [],
      showConfirmModal: false,
      Delete: false
    };
  },
  computed: {
    ...mapGetters('notes', ['notaActual']),

    parsedContent() {
      try {
        return JSON.parse(this.notaActual?.contenido || '[]');
      } catch (e) {
        console.warn('Contenido inválido:', this.notaActual?.contenido);
        return [];
      }
    }
  },
  watch: {
    entries: {
      deep: true,
      handler(newEntries) {
        this.$emit('update:content', JSON.stringify(newEntries));
      }
    },

    // Opcional: escuchar cambios en contenido desde Vuex
    'notaActual.contenido': {
      immediate: true,
      handler(newContent) {
        try {
          this.entries = JSON.parse(newContent || '[]');
        } catch (e) {
          this.entries = [];
        }
      }
    }
  },
  mounted() {

  },
  methods: {
  toggleNewPassword() {
    this.showNewPassword = !this.showNewPassword;
  },

  addEntry() {
    this.entries.push({
      ...this.newEntry,
      visible: false
    });
    this.newEntry = { service: '', username: '', password: '' };
    this.$emit('update:content', JSON.stringify(this.entries));
  },

  toggleVisibility(index) {
    this.entries[index].visible = !this.entries[index].visible;
    this.$emit('update:content', JSON.stringify(this.entries));
  },

  editEntry(index) {
    const entry = this.entries[index];
    this.newEntry = { ...entry }; // Cargar datos actuales en formulario
    this.showNewPassword = true; // Mostrar campo como texto al editar
    this.entries.splice(index, 1); // Eliminar temporalmente
  },

  async deleteEntry(index) {
      const confirmed = await useConfirm('¿Estás seguro de eliminar este elemento?');
      if (!confirmed) return;
      console.log('Elemento eliminado');
      this.entries.splice(index, 1);
      this.$emit('update:content', JSON.stringify(this.entries));
    }

  
}
};
</script>

<style scoped>
.claves-container {
  min-height: 100vh;
  background-color: #f5f5f5;
  padding: 20px;
}

.claves-content {
  max-width: 800px;
  margin: 0 auto;
}

/* Formulario */
.form-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.form-title {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 20px;
  color: #333;
}

.form-fields {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-input {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #1976d2;
  box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
}

.password-input-wrapper {
  position: relative;
}

.toggle-password-btn {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #666;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toggle-password-btn:hover {
  color: #333;
}

.submit-btn {
  width: 100%;
  padding: 12px;
  background: #1976d2;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.submit-btn:hover {
  background: #1565c0;
}

/* Entradas guardadas */
.entry-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.entry-info {
  flex: 1;
  min-width: 0;
}

.entry-service {
  font-size: 16px;
  font-weight: 600;
  color: #333;
  margin-bottom: 4px;
}

.entry-username {
  font-size: 14px;
  color: #666;
  margin: 0;
}

.entry-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.password-display {
  position: relative;
  display: flex;
  align-items: center;
}

.password-field {
  width: 160px;
  padding: 8px 40px 8px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
  background: #f9f9f9;
}

.action-btn {
  background: none;
  border: none;
  color: #666;
  cursor: pointer;
  padding: 8px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.action-btn:hover {
  background: #f5f5f5;
}

.edit-btn:hover {
  color: #1976d2;
  background: #e3f2fd;
}

.delete-btn:hover {
  color: #d32f2f;
  background: #ffebee;
}

/* Responsive */
@media (max-width: 768px) {
  .claves-container {
    padding: 12px;
  }

  .form-card, .entry-card {
    padding: 16px;
  }

  .entry-card {
    flex-direction: column;
    align-items: stretch;
  }

  .entry-actions {
    margin-top: 12px;
    justify-content: space-between;
  }

  .password-field {
    width: 100%;
  }
}

/* Dark mode support */
.body--dark .claves-container {
  background-color: #1e1e1e;
}

.body--dark .form-card,
.body--dark .entry-card {
  background: #2d2d2d;
}

.body--dark .form-title,
.body--dark .entry-service {
  color: #e0e0e0;
}

.body--dark .entry-username {
  color: #b0b0b0;
}

.body--dark .form-input,
.body--dark .password-field {
  background: #3a3a3a;
  border-color: #555;
  color: #e0e0e0;
}

.body--dark .toggle-password-btn,
.body--dark .action-btn {
  color: #b0b0b0;
}

.body--dark .action-btn:hover {
  background: #3a3a3a;
}
</style>