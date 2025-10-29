<template>
  <q-page class="q-pa-md ">
    <div class="row justify-center">
      <div class="col-12 col-lg-10">
        <div class="text-h4 text-weight-bold q-mb-md">{{ $t('notes.recommendations.title') }}</div>
        <div class="text-positive q-mb-lg">{{ $t('notes.recommendations.subtitle') }}</div>

        <div v-for="(category, index) in formData.categories" :key="index" class="q-mb-lg">
          <q-card flat bordered>
            <q-card-section>
              <!-- Título de la categoría -->
              <div class="row items-center q-mb-md">
                <div class="col">
                  <q-input
                    v-model="category.title"
                    :placeholder="$t('notes.recommendations.categoryNamePlaceholder')"
                    outlined
                    dense
                    class="text-h6 text-weight-bold"
                  />
                </div>
                <div class="col-auto q-ml-sm">
                  <q-btn
                    @click="removeCategory(index)"
                    icon="close"
                    color="negative"
                    flat
                    round
                    dense
                    size="sm"
                    :title="$t('notes.recommendations.deleteCategory')"
                  />
                </div>
              </div>

              <!-- Items de la categoría -->
              <q-input
                v-model="category.itemsString"
                :placeholder="$t('notes.recommendations.itemNamePlaceholder')"
                type="textarea"
                rows="4"
                outlined
              />
            </q-card-section>
          </q-card>
        </div>

        <!-- Botón para añadir nueva categoría -->
        <q-btn
          @click="addNewCategory"
          color="positive"
          :label="`+ ${$t('notes.recommendations.addCategory')}`"
          class="full-width"
        />
      </div>
    </div>
  </q-page>
</template>

<script>
export default {
  name: 'RecommendationsView',
  props: {
    content: {
      type: String,
      default: '[]'
    }
  },
  emits: ['update:content'],
  data() {
    return {
      formData: {
        categories: []
      }
    };
  },
  watch: {
    content: {
      immediate: true,
      handler(newContent) {
        try {
          const parsed = JSON.parse(newContent || '[]');
          this.formData.categories = parsed.map(category => ({
            title: category.title || '',
            itemsString: Array.isArray(category.items) 
              ? category.items.join(', ') 
              : category.items || ''
          }));
        } catch (e) {
          console.error('Error parsing content:', e);
          this.formData.categories = [];
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
    addNewCategory() {
      this.formData.categories.push({
        title: '',
        itemsString: ''
      });
    },
    
    removeCategory(index) {
      this.formData.categories.splice(index, 1);
    },
    
    saveChanges() {
      const newData = this.formData.categories.map(category => ({
        title: category.title.trim(),
        items: category.itemsString
          .split(',')
          .map(item => item.trim())
          .filter(item => item)
      }));
      
      const jsonContent = JSON.stringify(newData);
      this.$emit('update:content', jsonContent);
    }
  }
};
</script>

<style scoped>
/* Quasar components already provide styling */
</style>