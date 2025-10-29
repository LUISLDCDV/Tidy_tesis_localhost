<template>
  <q-page class="q-pa-md ">
    <div class="row justify-center">
      <div class="col-12 col-lg-8">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h4 text-weight-bold q-mb-lg">{{ $t('notes.recipe.title') }}</div>
            
            <q-form class="q-gutter-md">
              <!-- Título -->
              <q-input
                v-model="formData.title"
                :label="$t('notes.recipe.name')"
                :placeholder="$t('notes.recipe.namePlaceholder')"
                outlined
                dense
              />

              <!-- Ingredientes -->
              <q-input
                v-model="formData.ingredients"
                :label="$t('notes.recipe.ingredients')"
                :placeholder="$t('notes.recipe.ingredientsPlaceholder')"
                type="textarea"
                rows="4"
                outlined
              />

              <!-- Pasos -->
              <q-input
                v-model="formData.steps"
                :label="$t('notes.recipe.instructions')"
                :placeholder="$t('notes.recipe.instructionsPlaceholder')"
                type="textarea"
                rows="6"
                outlined
              />

              <!-- Tiempo de Cocción -->
              <q-input
                v-model.number="formData.cookingTime"
                :label="$t('notes.recipe.cookTime')"
                :placeholder="$t('notes.recipe.cookTimePlaceholder')"
                type="number"
                min="0"
                outlined
                dense
              />
            </q-form>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
export default {
  name: 'RecetaView',
  props: {
    content: {
      type: String,
      default: '{}'
    }
  },
  data() {
    return {
      formData: {
        title: '',
        ingredients: '',
        steps: '',
        cookingTime: 0
      }
    };
  },
  watch: {
    content: {
      immediate: true,
      handler(newContent) {
        try {
          const parsed = newContent ? JSON.parse(newContent) : {};
          this.formData = {
            title: parsed.title || '',
            ingredients: Array.isArray(parsed.ingredients) ? parsed.ingredients.join(', ') : parsed.ingredients || '',
            steps: Array.isArray(parsed.steps) ? parsed.steps.join('\n') : parsed.steps || '',
            cookingTime: parsed.cookingTime || 0
          };
        } catch (e) {
          console.error('Error parsing content:', e);
          this.formData = {
            title: '',
            ingredients: '',
            steps: '',
            cookingTime: 0
          };
        }
      }
    },
    formData: {
      deep: true,
      handler() {
        this.saveChanges();
      }
    }
  },
  methods: {
    saveChanges() {
      const newData = {
        title: this.formData.title,
        ingredients: this.formData.ingredients.split(',').map(i => i.trim()).filter(i => i),
        steps: this.formData.steps.split('\n').map(s => s.trim()).filter(s => s),
        cookingTime: this.formData.cookingTime
      };
      
      const jsonContent = JSON.stringify(newData);
      this.$emit('update:content', jsonContent);
    }
  }
};
</script>

<style scoped>
/* Estilos específicos si son necesarios */
</style>