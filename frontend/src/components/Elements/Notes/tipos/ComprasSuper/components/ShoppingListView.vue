<template>
  <q-page class="q-pa-sm ">
    <!-- Main Content -->
    <div class="row justify-center">
      <div class="col-12">
        <q-card flat bordered class="mobile-card">
          <q-card-section class="q-pa-md">
            <!-- Header -->
            <div class="text-h5 text-weight-bold q-mb-md">{{ $t('notes.shoppingList.title') }}</div>

            <!-- Lista de items - Versión Mobile -->
            <div class="q-mb-lg">
              <div class="text-h6 text-weight-medium q-mb-sm">{{ $t('notes.shoppingList.items') }}</div>
              
              <!-- Empty State -->
              <div v-if="items.length === 0" class="text-center q-pa-xl text-grey-6">
                <q-icon name="shopping_cart" size="48px" class="q-mb-sm" />
                <div>{{ $t('notes.shoppingList.emptyList') }}</div>
              </div>

              <!-- Lista con diseño mobile -->
              <q-list separator class="rounded-borders" v-else>
                <q-item 
                  v-for="item in items" 
                  :key="item.id"
                  class="q-py-sm q-px-none list-item-mobile"
                  :class="{ 'bg-green-1': item.checked }"
                >
                  <!-- Checkbox -->
                  <q-item-section side class="q-pr-sm">
                    <q-checkbox 
                      v-model="item.checked"
                      color="positive"
                      size="sm"
                    />
                  </q-item-section>
                  
                  <!-- Contenido principal -->
                  <q-item-section class="q-pr-sm">
                    <q-item-label 
                      class="text-body2 item-name"
                      :class="{ 'text-strike text-grey': item.checked }"
                    >
                      {{ item.name }}
                    </q-item-label>
                    <q-item-label caption class="text-positive text-weight-medium">
                      {{ item.price }}
                    </q-item-label>
                  </q-item-section>
                  
                  <!-- Acciones - Solo mostrar en mobile cuando sea necesario -->
                  <q-item-section side class="min-width-auto">
                    <q-btn-group flat rounded>
                      <q-btn 
                        @click="editItem(item)" 
                        icon="edit"
                        color="primary"
                        flat
                        round
                        dense
                        size="sm"
                      >
                        <q-tooltip>{{ $t('common.edit') }}</q-tooltip>
                      </q-btn>
                      <q-btn 
                        @click="removeItem(item.id)" 
                        icon="delete"
                        color="negative"
                        flat
                        round
                        dense
                        size="sm"
                      >
                        <q-tooltip>{{ $t('common.delete') }}</q-tooltip>
                      </q-btn>
                    </q-btn-group>
                  </q-item-section>
                </q-item>
              </q-list>

              <!-- Resumen móvil -->
              <div v-if="items.length > 0" class="row justify-between items-center q-mt-md q-px-sm">
                <div class="text-caption text-grey-6">
                  {{ checkedItemsCount }} de {{ items.length }} completados
                </div>
                <q-btn 
                  v-if="checkedItemsCount > 0"
                  @click="clearCompleted"
                  label="Limpiar"
                  color="grey"
                  flat
                  dense
                  size="sm"
                />
              </div>
            </div>

            <!-- Separador -->
            <q-separator class="q-my-md" />
            
            <!-- Formulario mobile -->
            <div class="form-mobile">
              <div class="text-h6 text-weight-medium q-mb-sm">
                {{ isEditing ? $t('notes.shoppingList.editItem') : $t('notes.shoppingList.addNewItem') }}
              </div>
              
              <q-form @submit.prevent="addItem" class="q-gutter-y-md">
                <!-- Campos en columna para mobile -->
                <q-input
                  v-model="newItem.name"
                  :label="$t('notes.shoppingList.itemNamePlaceholder')"
                  outlined
                  dense
                  required
                  class="full-width"
                />
                
                <div class="row items-center q-gutter-sm">
                  <q-input
                    v-model="newItem.price"
                    :label="$t('notes.shoppingList.pricePlaceholder')"
                    outlined
                    dense
                    required
                    class="col"
                  />
                  
                  <!-- Botones en fila para mobile -->
                  <div class="row q-gutter-xs">
                    <q-btn
                      v-if="isEditing"
                      @click="cancelEdit"
                      icon="cancel"
                      color="grey"
                      flat
                      round
                      dense
                    >
                      <q-tooltip>{{ $t('common.cancel') }}</q-tooltip>
                    </q-btn>
                    <q-btn
                      type="submit"
                      :icon="isEditing ? 'check' : 'add'"
                      :color="isFormValid ? 'positive' : 'grey'"
                      :disable="!isFormValid"
                      round
                      dense
                    >
                      <q-tooltip>
                        {{ isEditing ? $t('notes.shoppingList.updateItem') : $t('notes.shoppingList.addItem') }}
                      </q-tooltip>
                    </q-btn>
                  </div>
                </div>

                <!-- Botones con texto en pantallas más grandes -->
                <div class="row q-gutter-sm justify-end gt-xs">
                  <q-btn
                    v-if="isEditing"
                    @click="cancelEdit"
                    :label="$t('common.cancel')"
                    color="grey"
                    outline
                    dense
                  />
                  <q-btn
                    type="submit"
                    :label="isEditing ? $t('notes.shoppingList.updateItem') : $t('notes.shoppingList.addItem')"
                    color="positive"
                    :disable="!isFormValid"
                    dense
                  />
                </div>
              </q-form>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
export default {
  name: 'ShoppingListView',
  props: {
    content: {
      type: String,
      default: '[]'
    }
  },
  data() {
    return {
      newItem: {
        name: '',
        price: ''
      },
      localItems: [],
      editingItemId: null
    }
  },
  computed: {
    items() {
      return this.localItems
    },
    isFormValid() {
      return this.newItem.name.trim() && this.newItem.price.trim()
    },
    isEditing() {
      return this.editingItemId !== null
    },
    checkedItemsCount() {
      return this.localItems.filter(item => item.checked).length
    }
  },
  watch: {
    content: {
      immediate: true,
      handler(newContent) {
        console.log('Content received:', newContent);
        if (typeof newContent === 'string' && newContent.trim() !== '') {
          try {
            const parsed = JSON.parse(newContent);
            this.localItems = Array.isArray(parsed) ? parsed : [];
          } catch (e) {
            console.error('Error parsing items:', e);
            this.localItems = [];
          }
        } else {
          this.localItems = [];
        }
        console.log('LocalItems after update:', this.localItems);
      }
    },
    localItems: {
      deep: true,
      handler(newItems) {
        this.saveChanges(newItems)
      }
    }
  },
  methods: {
    addItem() {
      console.log('addItem called, localItems:', this.localItems);
      
      if (this.isFormValid) {
        if (!Array.isArray(this.localItems)) {
          console.warn('localItems is not an array, resetting to empty array');
          this.localItems = [];
        }
        
        if (this.isEditing) {
          const index = this.localItems.findIndex(item => item.id === this.editingItemId);
          if (index !== -1) {
            this.localItems.splice(index, 1, {
              id: this.editingItemId,
              name: this.newItem.name.trim(),
              price: this.newItem.price.trim(),
              checked: this.localItems[index].checked
            });
          }
          this.editingItemId = null;
        } else {
          this.localItems.push({
            id: Date.now(),
            name: this.newItem.name.trim(),
            price: this.newItem.price.trim(),
            checked: false
          });
        }
        this.newItem = { name: '', price: '' };
      }
    },
    editItem(item) {
      this.newItem = { name: item.name, price: item.price };
      this.editingItemId = item.id;
      // Scroll al formulario para edición
      this.$nextTick(() => {
        document.querySelector('.form-mobile')?.scrollIntoView({ 
          behavior: 'smooth', 
          block: 'start' 
        });
      });
    },
    removeItem(id) {
      this.$q.dialog({
        title: this.$t('common.confirmDelete'),
        message: this.$t('notes.shoppingList.confirmDeleteItem'),
        cancel: true,
        persistent: true
      }).onOk(() => {
        this.localItems = this.localItems.filter(item => item.id !== id);
      });
    },
    cancelEdit() {
      this.newItem = { name: '', price: '' };
      this.editingItemId = null;
    },
    clearCompleted() {
      this.localItems = this.localItems.filter(item => !item.checked);
    },
    saveChanges(items) {
      const jsonContent = JSON.stringify(items);
      this.$emit('update:content', jsonContent);
    }
  }
}
</script>

<style scoped>
/* Estilos optimizados para mobile */
.mobile-card {
  border-radius: 12px;
}

.list-item-mobile {
  min-height: 56px;
  border-radius: 8px;
  margin-bottom: 4px;
}

.list-item-mobile:hover {
  background-color: #f8f9fa;
}

.item-name {
  word-break: break-word;
  line-height: 1.3;
}

.min-width-auto {
  min-width: auto !important;
}

/* Mejoras de responsive */
@media (max-width: 599px) {
  .q-pa-md {
    padding: 12px;
  }
  
  .text-h5 {
    font-size: 1.25rem;
  }
  
  .text-h6 {
    font-size: 1.1rem;
  }
}

/* Para pantallas muy pequeñas */
@media (max-width: 380px) {
  .list-item-mobile {
    min-height: 52px;
  }
  
  .q-btn--round {
    width: 32px;
    height: 32px;
  }
}

/* Transiciones suaves */
.q-item {
  transition: background-color 0.3s ease;
}

/* Mejorar la legibilidad del texto tachado */
.text-strike {
  opacity: 0.7;
}
</style>