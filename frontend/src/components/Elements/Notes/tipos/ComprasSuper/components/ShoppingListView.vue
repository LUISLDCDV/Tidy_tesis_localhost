<template>
  <q-page class="q-pa-md bg-grey-1">
    <!-- Main Content -->
    <div class="row justify-center">
      <div class="col-12 col-md-8 col-lg-6">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h4 text-weight-bold q-mb-lg">{{ $t('notes.shoppingList.title') }}</div>

            <!-- Lista de items -->
            <div class="q-mb-lg">
              <div class="text-h6 text-weight-medium q-mb-md">{{ $t('notes.shoppingList.items') }}</div>
              <q-list separator>
                <q-item 
                  v-for="item in items" 
                  :key="item.id"
                  class="q-pa-md"
                >
                  <q-item-section side>
                    <q-checkbox 
                      v-model="item.checked"
                      color="positive"
                    />
                  </q-item-section>
                  
                  <q-item-section>
                    <q-item-label 
                      class="text-body1"
                      :class="{ 'text-strike text-grey': item.checked }"
                    >
                      {{ item.name }}
                    </q-item-label>
                  </q-item-section>
                  
                  <q-item-section side>
                    <div class="text-positive text-weight-medium q-mr-md">{{ item.price }}</div>
                  </q-item-section>
                  
                  <q-item-section side>
                    <div class="q-gutter-xs">
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
                    </div>
                  </q-item-section>
                </q-item>
              </q-list>
            </div>

            <!-- Formulario para agregar/editar items -->
            <q-separator class="q-my-lg" />
            
            <div>
              <div class="text-h6 text-weight-medium q-mb-md">
                {{ isEditing ? $t('notes.shoppingList.editItem') : $t('notes.shoppingList.addNewItem') }}
              </div>
              
              <q-form @submit.prevent="addItem" class="q-gutter-md">
                <div class="row q-gutter-md">
                  <div class="col">
                    <q-input
                      v-model="newItem.name"
                      :label="$t('notes.shoppingList.itemNamePlaceholder')"
                      outlined
                      dense
                      required
                    />
                  </div>
                  
                  <div class="col-auto" style="min-width: 150px;">
                    <q-input
                      v-model="newItem.price"
                      :label="$t('notes.shoppingList.pricePlaceholder')"
                      outlined
                      dense
                      required
                    />
                  </div>
                </div>
                
                <div class="row q-gutter-sm justify-end">
                  <q-btn
                    v-if="isEditing"
                    @click="cancelEdit"
                    :label="$t('common.cancel')"
                    color="grey"
                    outline
                  />
                  <q-btn
                    type="submit"
                    :label="isEditing ? $t('notes.shoppingList.updateItem') : $t('notes.shoppingList.addItem')"
                    color="positive"
                    :disable="!isFormValid"
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
        // Asegurar que localItems es un array
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
    },
    removeItem(id) {
      this.localItems = this.localItems.filter(item => item.id !== id);
    },
    cancelEdit() {
      this.newItem = { name: '', price: '' };
      this.editingItemId = null;
    },
    saveChanges(items) {
      const jsonContent = JSON.stringify(items);
      this.$emit('update:content', jsonContent);
    }
  }
}
</script>

<style scoped>
/* Estilos personalizados si son necesarios */
</style>