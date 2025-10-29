<template>
  <q-page class="q-pa-md ">
    <div class="row justify-center">
      <div class="col-12">
        <div class="text-h4 text-weight-bold q-mb-lg">{{ $t('notes.groupOrder.title') }}</div>

        <!-- Error messages -->
        <q-banner 
          v-if="errorMessage" 
          class="q-mb-md bg-negative text-white"
          rounded
        >
          {{ errorMessage }}
        </q-banner>
        
        <div class="row q-gutter-md">
          <!-- Columna izquierda: Gestión de personas y comidas -->
          <div class="col-12 col-lg-5">
            <div class="q-gutter-md">
              <PersonManagement 
                :people="Object.keys(orders)"
                :selected-person="selectedPerson"
                @add-person="addPerson"
                @remove-person="removePerson"
                @select-person="selectPerson"
              />

              <FoodChoicesSection 
                :selected-person="selectedPerson" 
                :food-options="foodOptions"
                @food-selected="addToOrder"
                @custom-food="addCustomFood"
                @remove-food="removeFood"
                @show-error="showError"
              />
            </div>
          </div>

          <!-- Columna derecha: Pedidos -->
          <div class="col-12 col-lg-6">
            <OrdersSection 
              v-if="loaded"
              :orders="orders"
              @remove-item="removeFromOrder"
            />
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import OrdersSection from './OrdersSection.vue';
import FoodChoicesSection from './FoodChoicesSection.vue';
import PersonManagement from './PersonManagement.vue';

export default {
  name: 'PedidoGrupales',
  components: {
    OrdersSection,
    FoodChoicesSection,
    PersonManagement
  },
  props: {
    content: {
      type: String,
      default: '{}'
    }
  },
  emits: ['update:content'],
  data() {
    return {
      loaded: false,
      selectedPerson: '',
      orders: {},
      foodOptions: [],
      errorMessage: null
    };
  },
  watch: {
    content: {
      immediate: true,
      handler(newContent) {
        try {
          const parsed = newContent ? JSON.parse(newContent) : {};
          this.orders = parsed.orders || {};
          this.foodOptions = parsed.foodOptions || [
            { id: 1, name: this.$t('notes.groupOrder.menu.categories.food'), price: 10 },
            { id: 2, name: this.$t('notes.groupOrder.menu.categories.drinks'), price: 8 },
            { id: 3, name: this.$t('notes.groupOrder.menu.categories.desserts'), price: 6 }
          ];
          
          if (!this.selectedPerson && Object.keys(this.orders).length > 0) {
            this.selectedPerson = Object.keys(this.orders)[0];
          }
          this.loaded = true;
        } catch (e) {
          console.error('Error parsing content:', e);
          this.initializeDefaultState();
        }
      }
    }
  },
  methods: {
    
    initializeDefaultState() {
      this.orders = {};
      this.foodOptions = [
        { id: 1, name: this.$t('notes.groupOrder.menu.categories.food'), price: 10 },
        { id: 2, name: this.$t('notes.groupOrder.menu.categories.drinks'), price: 8 },
        { id: 3, name: this.$t('notes.groupOrder.menu.categories.desserts'), price: 6 }
      ];
      this.loaded = true;
    },

    showError(message) {
      this.errorMessage = message;
      setTimeout(() => {
        this.errorMessage = null;
      }, 3000);
    },

    addPerson(name) {
      if (!this.orders[name]) {
        this.orders = {
          ...this.orders,
          [name]: []
        };
        this.selectedPerson = name;
        this.emitChanges();
      } else {
        this.showError(this.$t('notes.groupOrder.participants.alreadyExists'));
      }
    },

    removePerson(person) {
      const newOrders = { ...this.orders };
      delete newOrders[person];
      this.orders = newOrders;
      
      if (this.selectedPerson === person) {
        this.selectedPerson = Object.keys(this.orders)[0] || '';
      }
      this.emitChanges();
    },

    selectPerson(person) {
      this.selectedPerson = person;
    },

    async addToOrder(payload) {
      const { person, foodId } = payload;
      const food = this.foodOptions.find(f => f.id === foodId);
      if (!food) return;

      if (!this.orders[person]) {
        this.orders = {
          ...this.orders,
          [person]: []
        };
      }
      
      this.orders = {
        ...this.orders,
        [person]: [...this.orders[person], food]
      };
      
      this.emitChanges();
    },

    removeFromOrder({ person, foodId }) {
      if (this.orders[person]) {
        this.orders = {
          ...this.orders,
          [person]: this.orders[person].filter(food => food.id !== foodId)
        };
        this.emitChanges();
      }
    },

    addCustomFood(customFood) {
      const newFood = {
        id: Date.now(),
        name: customFood.name,
        price: customFood.price
      };
      this.foodOptions = [...this.foodOptions, newFood];
      this.emitChanges();
    },

    removeFood(foodId) {
      this.foodOptions = this.foodOptions.filter(food => food.id !== foodId);
      
      // También eliminar este item de todos los pedidos
      const newOrders = {};
      Object.entries(this.orders).forEach(([person, items]) => {
        newOrders[person] = items.filter(food => food.id !== foodId);
      });
      this.orders = newOrders;
      
      this.emitChanges();
    },

    emitChanges() {
      const newData = {
        orders: this.orders,
        foodOptions: this.foodOptions
      };
      const jsonContent = JSON.stringify(newData);
      this.$emit('update:content', jsonContent);
    }
  }
};
</script>

<style scoped>
/* .containerClass {
  @apply bg-background text-danger-foreground p-4 rounded-lg shadow-lg;
} */
</style>