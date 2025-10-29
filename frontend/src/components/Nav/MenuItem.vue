<template>
  <q-item 
    clickable
    v-ripple
    :class="{ '': highlighted }"
    class="rounded"
  >
    <q-item-section avatar>
      <span v-html="icon"></span>
    </q-item-section>
    <q-item-section>
      <q-item-label>{{ title }}</q-item-label>
    </q-item-section>
  </q-item>
</template>

<script>
export default {
  name: 'MenuItem',
  props: {
    icon: {
      type: String,
      default: 'icon1',
    },
    title: {
      type: String,
      default: 'Default Title',
    },
    highlighted: {
      type: Boolean,
      default: false,
    },
  },
  methods: {

  },
};
</script>
