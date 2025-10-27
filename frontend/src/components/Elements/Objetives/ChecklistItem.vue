<!-- ChecklistItem.vue -->
<template>
  <q-card 
    class="checklist-item-card"
    :class="{ 'completed-item': item.completed }"
    flat
    bordered
  >
    <q-card-section class="q-pa-md">
      <div class="row items-start q-gutter-md">
        <!-- Checkbox -->
        <div class="checklist-checkbox">
          <q-checkbox
            :model-value="item.completed"
            @update:model-value="toggleComplete"
            color="positive"
            size="md"
            checked-icon="check"
            unchecked-icon="panorama_fish_eye"
          />
        </div>

        <!-- Contenido -->
        <div class="col">
          <div class="row justify-between items-start">
            <div class="col">
              <div 
                class="checklist-title"
                :class="{ 'completed-text': item.completed }"
              >
                {{ item.title }}
              </div>
              <div 
                class="checklist-description q-mt-xs"
                :class="{ 'completed-text': item.completed }"
              >
                {{ item.description }}
              </div>
            </div>

            <!-- Acciones -->
            <div class="row items-center q-gutter-xs">
              <q-btn 
                @click="$emit('edit', item)"
                flat
                round
                dense
                size="sm"
                color="grey-6"
                icon="edit"
                class="action-btn"
              />
              <q-btn 
                @click="$emit('delete', item)"
                flat
                round
                dense
                size="sm"
                color="negative"
                icon="delete"
                class="action-btn"
              />
            </div>
          </div>

          <!-- Fecha -->
          <div class="row items-center q-mt-sm text-grey-6">
            <q-icon name="event" size="16px" class="q-mr-xs" />
            <span class="text-caption">{{ formatDate(item.dueDate) }}</span>
          </div>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script>
export default {
  name: 'ChecklistItem',
  props: {
    item: {
      type: Object,
      required: true,
      validator: (obj) => {
        return obj.hasOwnProperty('id') &&
               obj.hasOwnProperty('title') &&
               obj.hasOwnProperty('description') &&
               obj.hasOwnProperty('completed') &&
               obj.hasOwnProperty('dueDate');
      }
    }
  },
  methods: {
    toggleComplete() {
      this.$emit('update:status', !this.item.completed);
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const options = { year: 'numeric', month: 'short', day: 'numeric' };
      return new Date(dateString).toLocaleDateString('es-ES', options);
    }
  }
};
</script>

<style scoped>
.checklist-item-card {
  transition: all 0.2s ease;
  background: #f8fafb;
}

.checklist-item-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.06);
}

.completed-item {
  opacity: 0.7;
}

.completed-text {
  text-decoration: line-through;
  opacity: 0.6;
}

.checklist-checkbox {
  display: flex;
  align-items: flex-start;
  padding-top: 2px;
}

.checklist-title {
  font-weight: 500;
  line-height: 1.4;
}

.checklist-description {
  font-size: 0.875rem;
  line-height: 1.3;
  color: #6b7280;
}

.action-btn {
  opacity: 0.6;
  transition: opacity 0.2s ease;
}

.checklist-item-card:hover .action-btn {
  opacity: 1;
}

/* Dark mode support */
.body--dark .checklist-item-card {
  background: #374151;
  color: white;
}

.body--dark .checklist-item-card:hover {
  background: #4b5563;
}

.body--dark .checklist-description {
  color: #d1d5db;
}

.body--dark .completed-text {
  color: #9ca3af;
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .checklist-item-card .action-btn {
    opacity: 1;
  }
  
  .checklist-title {
    font-size: 0.95rem;
  }
  
  .checklist-description {
    font-size: 0.8rem;
  }
}
</style>
  