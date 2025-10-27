<template>
  <ShoppingListView 
    :content="localContent" 
    @update:content="updateContent"
  />
</template>

<script>
// TODO: AGREGAR ICONOS Y CANTIDAD

import ShoppingListView from './components/ShoppingListView.vue'

export default {
  components: {
    ShoppingListView
  },
  props: {
    content: {
      type: String,
      default: () => JSON.stringify([
        { id: 1, name: 'Manzanas', price: '$2.99', checked: false },
        { id: 2, name: 'Leche', price: '$1.99', checked: true }
      ])
    }
  },
  data() {
    return {
      localContent: this.content
    }
  },
  watch: {
    content: {
      immediate: true,
      handler(newContent) {
        this.localContent = newContent
      }
    }
  },
  methods: {
    updateContent(newContent) {
      this.localContent = newContent
      this.$emit('update:content', newContent)
    }
  }
}
</script>