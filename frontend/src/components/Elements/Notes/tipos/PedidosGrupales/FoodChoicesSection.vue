<template>
  <q-card flat bordered>
    <q-card-section>
      <div class="text-h6 text-weight-bold q-mb-md">{{ $t('notes.groupOrder.menu.title') }}</div>

      <!-- Persona seleccionada -->
      <q-banner 
        class="q-mb-md"
        :class="selectedPerson ? 'bg-positive-1 text-positive' : 'bg-negative-1 text-negative'"
        rounded
      >
        {{ selectedPerson 
          ? $t('notes.groupOrder.menu.addingFor', { person: selectedPerson })
          : $t('notes.groupOrder.menu.selectPerson') }}
      </q-banner>

      <!-- Agregar comida personalizada -->
      <q-card flat bordered class="q-mb-md ">
        <q-card-section>
          <div class="text-subtitle1 text-weight-medium q-mb-md">{{ $t('notes.groupOrder.menu.addItem') }}</div>
          <div class="row q-gutter-sm q-mb-md">
            <div class="col">
              <q-input 
                v-model="newFood.name" 
                :placeholder="$t('notes.groupOrder.menu.itemName')" 
                outlined
                dense
              />
            </div>
            <div class="col-4">
              <q-input 
                v-model.number="newFood.price" 
                type="number" 
                :placeholder="$t('notes.groupOrder.menu.itemPrice')" 
                outlined
                dense
                min="0"
                step="0.01"
              />
            </div>
          </div>
          <q-btn 
            @click="addCustomFood" 
            color="positive"
            :label="$t('notes.groupOrder.menu.addItem')"
            class="full-width"
            :disable="!newFood.name || !newFood.price"
          />
        </q-card-section>
      </q-card>

      <!-- Lista de opciones -->
      <div class="q-mb-md">
        <div class="text-subtitle1 text-weight-medium q-mb-sm">{{ $t('notes.groupOrder.menu.availableItems') }}</div>
        <div class="q-gutter-sm">
          <div 
            v-for="food in foodOptions" 
            :key="food.id"
            class="row items-center justify-between q-pa-sm "
            style="border-radius: 8px;"
          >
            <div class="col">
              <span class="text-weight-medium">{{ food.name }}</span>
              <span class="text-grey-6 q-ml-sm">${{ food.price.toFixed(2) }}</span>
            </div>
            <div class="col-auto q-gutter-xs">
              <q-btn 
                @click="selectFood(food)"
                size="sm"
                color="primary"
                outline
                :label="$t('notes.groupOrder.menu.addToOrder')"
                :disable="!selectedPerson"
              />
              <q-btn 
                @click="removeFood(food.id)"
                size="sm"
                color="negative"
                outline
                :label="$t('notes.groupOrder.delete')"
              />
            </div>
          </div>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script>
export default {
  props: {
    selectedPerson: {
      type: String,
      default: ''
    },
    foodOptions: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      newFood: {
        name: '',
        price: null
      }
    };
  },
  methods: {
    selectFood(food) {
      if (!this.selectedPerson) {
        this.$emit('show-error', this.$t('notes.groupOrder.menu.selectPersonFirst'));
        return;
      }
      this.$emit('food-selected', { person: this.selectedPerson, foodId: food.id });
    },
    addCustomFood() {
      if (this.newFood.name && this.newFood.price > 0) {
        this.$emit('custom-food', { ...this.newFood });
        this.newFood.name = '';
        this.newFood.price = null;
      } else {
        this.$emit('show-error', this.$t('notes.groupOrder.menu.invalidInput'));
      }
    },
    removeFood(foodId) {
      this.$emit('remove-food', foodId);
    }
  }
};
</script>