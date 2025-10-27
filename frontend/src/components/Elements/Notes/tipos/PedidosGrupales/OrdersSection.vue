<template>
  <q-card flat bordered>
    <q-card-section>
      <div class="text-h6 text-weight-bold q-mb-md">{{ $t('notes.groupOrder.currentOrders') }}</div>

      <div class="q-gutter-md">
        <div 
          v-for="(items, person) in orders" 
          :key="person"
        >
          <q-card flat bordered class="bg-grey-2">
            <q-card-section>
              <div class="row justify-between items-center q-mb-sm">
                <div class="text-subtitle1 text-weight-medium">{{ person }}</div>
                <div class="text-caption text-grey-6">
                  {{ $t('notes.groupOrder.total') }}: ${{ calculatePersonTotal(items).toFixed(2) }}
                </div>
              </div>

              <div class="q-gutter-xs">
                <div 
                  v-for="(food, index) in items" 
                  :key="index"
                  class="row justify-between items-center q-pa-sm bg-white"
                  style="border-radius: 4px;"
                >
                  <div class="col">
                    <span>{{ food.name }}</span>
                    <span class="text-grey-6 q-ml-sm">${{ food.price.toFixed(2) }}</span>
                  </div>
                  <div class="col-auto">
                    <q-btn 
                      @click="$emit('remove-item', { person, foodId: food.id })"
                      icon="delete"
                      color="negative"
                      flat
                      round
                      dense
                      size="sm"
                    />
                  </div>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Resumen total -->
      <q-card flat bordered class="q-mt-lg bg-primary-1">
        <q-card-section>
          <div class="row justify-between items-center">
            <div class="text-h6 text-weight-bold">{{ $t('notes.groupOrder.total') }}</div>
            <div class="text-h5 text-weight-bold text-primary">${{ calculateGrandTotal().toFixed(2) }}</div>
          </div>
          
          <!-- Desglose por persona -->
          <div class="q-mt-md q-gutter-xs">
            <div 
              v-for="(items, person) in orders" 
              :key="person"
              class="row justify-between text-caption text-grey-7"
            >
              <span>{{ person }}</span>
              <span>${{ calculatePersonTotal(items).toFixed(2) }}</span>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-card-section>
  </q-card>
</template>

<script>
export default {
  name: 'OrdersSection',
  props: {
    orders: {
      type: Object,
      required: true
    }
  },
  methods: {
    calculatePersonTotal(items) {
      return items.reduce((total, item) => total + item.price, 0);
    },
    calculateGrandTotal() {
      return Object.values(this.orders).reduce((total, items) => {
        return total + this.calculatePersonTotal(items);
      }, 0);
    }
  }
};
</script>

<style scoped>
/* Quasar components already provide styling */
</style>