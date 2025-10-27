<template>
  <div :class="cardClasses">
    <div class="flex justify-between items-center">
      <h3 :class="textClasses">{{ player.name }}</h3>
      <button 
        @click="$emit('delete')" 
        class="text-red-500 hover:text-red-700"
      >
        ✕
      </button>
    </div>
    
    <p :class="scoreClasses">Score: {{ player.score }}</p>
    
    <div class="flex justify-between mt-2">
      <button 
        @click="decrementScore" 
        :class="destructiveButtonClasses"
      >
        -
      </button>
      <button 
        @click="incrementScore" 
        :class="buttonClasses"
      >
        +
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PlayerCard',
  props: {
    player: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      cardClasses: "bg-card p-4 rounded-md",
      textClasses: "text-lg font-semibold mb-2",
      scoreClasses: "text-sm text-muted-foreground",
      buttonClasses: "bg-primary text-primary-foreground px-3 py-1 rounded-md",
      destructiveButtonClasses: "bg-primary text-primary-foreground px-3 py-1 rounded-md"
    };
  },
  methods: {
    incrementScore() {
      const updatedPlayer = { ...this.player, score: this.player.score + 1 };
      this.$emit('update:score', updatedPlayer.score);
    },
    decrementScore() {
      const updatedPlayer = { ...this.player, score: Math.max(0, this.player.score - 1) };
      this.$emit('update:score', updatedPlayer.score);
    }
  }
};
</script>