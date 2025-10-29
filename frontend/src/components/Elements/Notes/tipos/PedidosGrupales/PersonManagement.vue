<template>
  <q-card flat bordered>
    <q-card-section>
      <div class="text-h6 text-weight-bold q-mb-md">{{ $t('notes.groupOrder.participants.title') }}</div>
      
      <!-- Agregar nueva persona -->
      <div class="row q-gutter-sm q-mb-md">
        <div class="col">
          <q-input 
            v-model="newPersonName" 
            :placeholder="$t('notes.groupOrder.participants.namePlaceholder')" 
            outlined
            dense
            @keyup.enter="addPerson"
          />
        </div>
        <div class="col-auto">
          <q-btn 
            @click="addPerson" 
            color="positive"
            :label="$t('notes.groupOrder.participants.addNew')"
          />
        </div>
      </div>

      <!-- Lista de personas -->
      <div class="q-gutter-sm">
        <div 
          v-for="person in people" 
          :key="person"
          class="row items-center justify-between q-pa-sm"
          :class="selectedPerson === person ? 'bg-positive-1' : ''"
          style="border-radius: 8px;"
        >
          <div class="col row items-center no-wrap">
            <q-icon 
              name="circle" 
              :color="selectedPerson === person ? 'positive' : 'grey-6'"
              size="xs"
              class="q-mr-sm"
            />
            <span :class="{ 'text-weight-bold': selectedPerson === person }">
              {{ person }}
            </span>
          </div>
          <div class="col-auto q-gutter-xs">
            <q-btn 
              @click="selectPerson(person)"
              size="sm"
              :color="selectedPerson === person ? 'positive' : 'primary'"
              :outline="selectedPerson !== person"
              :label="selectedPerson === person ? $t('notes.groupOrder.participants.selected') : $t('notes.groupOrder.participants.select')"
            />
            <q-btn 
              @click="removePerson(person)"
              size="sm"
              color="negative"
              outline
              :label="$t('notes.groupOrder.delete')"
            />
          </div>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script>
export default {
  name: 'PersonManagement',
  props: {
    people: {
      type: Array,
      required: true
    },
    selectedPerson: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      newPersonName: ''
    }
  },
  methods: {
    addPerson() {
      if (this.newPersonName.trim()) {
        this.$emit('add-person', this.newPersonName.trim())
        this.newPersonName = ''
      }
    },
    removePerson(person) {
      this.$emit('remove-person', person)
    },
    selectPerson(person) {
      this.$emit('select-person', person)
    }
  }
}
</script> 