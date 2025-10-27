<template>
  <q-page class="q-pa-md bg-grey-1">
    <!-- Weekly Grid -->
    <div class="q-mb-lg">
      <div class="row q-gutter-sm">
        <div 
          v-for="(day, index) in days" 
          :key="index" 
          class="col-12 col-sm-6 col-md-4 col-lg col-xl"
          style="min-width: 200px; max-width: 250px;"
        >
          <q-card 
            class="cursor-pointer full-height"
            :class="{ 'shadow-4': selectedDay === index }"
            :style="selectedDay === index ? 'border: 2px solid #4caf50' : ''"
            @click="selectDay(index)"
            flat
            bordered
          >
            <q-card-section class="q-pb-sm">
              <div class="text-h6 text-weight-medium">{{ day }}</div>
              <q-separator class="q-mt-xs" />
            </q-card-section>
            
            <q-card-section class="q-pt-xs">
              <div class="q-gutter-xs">
                <div v-for="mealType in mealTypes" :key="mealType" class="q-mb-sm">
                  <div class="text-caption text-grey-8 q-mb-xs">{{ mealType }}</div>
                  <q-btn 
                    @click.stop="editMeal(index, mealType)"
                    :label="mealsData[index][mealType]?.recipe || 'Add'"
                    color="positive"
                    outline
                    dense
                    no-caps
                    class="full-width text-left"
                    align="left"
                  />
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>


    <!-- Shopping List -->
    <q-card class="q-mt-lg" flat bordered>
      <q-card-section>
        <div class="text-h6 text-weight-medium q-mb-md">Shopping List</div>
        <q-list separator>
          <q-item 
            v-for="(item, index) in shoppingList" 
            :key="index"
            class="q-px-none"
          >
            <q-item-section>
              <q-item-label>{{ item }}</q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-btn 
                @click="removeItem(index)"
                icon="close"
                color="negative"
                flat
                round
                dense
              />
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>
    </q-card>

    <!-- Modal para edición de comidas -->
    <q-dialog v-model="showModal" persistent>
      <q-card style="min-width: 400px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Add/Edit Meal</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-input 
            v-model="currentMeal.recipe" 
            label="Recipe name" 
            outlined
            class="q-mb-md"
          />
          <q-input 
            v-model="currentMeal.ingredients" 
            label="Ingredients (one per line)"
            type="textarea"
            outlined
            rows="4"
          />
        </q-card-section>

        <q-card-actions align="right" class="q-gutter-sm">
          <q-btn 
            @click="cancelMeal" 
            label="Cancel" 
            color="negative"
            outline
          />
          <q-btn 
            @click="saveMeal" 
            label="Save" 
            color="positive"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'PlanificacionComidasSemanales',
  props: {
    content: {
      type: String,
      default: '[]'
    }
  },
  data() {
    return {
      days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
      mealTypes: ['Breakfast', 'Lunch', 'Dinner'],
      showModal: false,
      currentMeal: { recipe: '', ingredients: '' },
      selectedDay: null,
      selectedMealType: null,
      mealsData: []
    };
  },
  computed: {
    shoppingList() {
      const list = [];
      this.days.forEach((_, dayIndex) => {
        this.mealTypes.forEach(type => {
          const ingredients = this.mealsData[dayIndex]?.[type]?.ingredients || [];
          if (Array.isArray(ingredients)) {
            list.push(...ingredients.filter(i => i));
          } else if (typeof ingredients === 'string') {
            list.push(...ingredients.split('\n').filter(i => i.trim()));
          }
        });
      });
      return [...new Set(list)];
    }
  },
  methods: {
    selectDay(index) {
      this.selectedDay = index;
    },
    editMeal(dayIndex, mealType) {
      this.selectedDay = dayIndex;
      this.selectedMealType = mealType;

      const meal = this.mealsData[dayIndex]?.[mealType] || { recipe: '', ingredients: [] };

      this.currentMeal = {
        recipe: meal.recipe || '',
        ingredients: Array.isArray(meal.ingredients)
          ? meal.ingredients.join('\n')
          : (typeof meal.ingredients === 'string' ? meal.ingredients : '')
      };

      this.showModal = true;
    },
    saveMeal() {
      const updatedMeals = JSON.parse(JSON.stringify(this.mealsData));
      const dayIndex = this.selectedDay;
      const mealType = this.selectedMealType;

      updatedMeals[dayIndex][mealType] = {
        recipe: this.currentMeal.recipe,
        ingredients: this.currentMeal.ingredients
          .split('\n')
          .map(i => i.trim())
          .filter(i => i)
      };

      this.mealsData = updatedMeals;

      const contentToSave = JSON.stringify(updatedMeals);
      this.$emit('update:content', contentToSave);

      this.showModal = false;
      this.currentMeal = { recipe: '', ingredients: '' };
    },
    cancelMeal() {
      this.showModal = false;
      this.currentMeal = { recipe: '', ingredients: '' };
    },
    removeItem(index) {
      const itemToRemove = this.shoppingList[index];
      let removed = false;
      const updatedMeals = JSON.parse(JSON.stringify(this.mealsData));

      for (let dayIndex = 0; dayIndex < updatedMeals.length && !removed; dayIndex++) {
        for (const mealType of this.mealTypes) {
          const ingredients = updatedMeals[dayIndex]?.[mealType]?.ingredients || [];

          const idx = ingredients.indexOf(itemToRemove);
          if (idx > -1) {
            ingredients.splice(idx, 1);
            removed = true;
            break;
          }
        }
        if (removed) break;
      }

      if (removed) {
        this.mealsData = updatedMeals;
        const contentToSave = JSON.stringify(updatedMeals);
        this.$emit('update:content', contentToSave);
      }
    }
  },
  watch: {
    content: {
      immediate: true,
      handler(newContent) {
        try {
          let parsed;

          if (typeof newContent === 'string' && newContent.trim()) {
            parsed = JSON.parse(newContent);
          } else if (typeof newContent === 'object' && newContent !== null && typeof newContent.contenido === 'string') {
            parsed = JSON.parse(newContent.contenido);
          } else {
            parsed = [];
          }

          this.mealsData = this.days.map((_, index) => {
            const dayData = parsed[index] || {};
            return {
              Breakfast: dayData.Breakfast || { recipe: '', ingredients: [] },
              Lunch: dayData.Lunch || { recipe: '', ingredients: [] },
              Dinner: dayData.Dinner || { recipe: '', ingredients: [] }
            };
          });

        } catch (e) {
          console.error('Error parsing contenido:', e);
          this.mealsData = this.days.map(() => ({
            Breakfast: { recipe: '', ingredients: [] },
            Lunch: { recipe: '', ingredients: [] },
            Dinner: { recipe: '', ingredients: [] }
          }));
        }
      }
    }
  }
};
</script>

<style scoped>
.form-input {
  @apply w-full px-3 py-2 border rounded-xl bg-[#f8fcf8] focus:outline-none focus:ring-2 focus:ring-[#d0e7d0];
}

.save-button {
  @apply fixed bottom-6 right-6 bg-[#19e619] text-[#0e1b0e] p-3 rounded-full shadow-lg z-40;
}
.save-button svg {
  @apply w-5 h-5;
}
</style>