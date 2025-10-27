<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row justify-center">
      <div class="col-12 col-lg-10">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h4 text-weight-bold q-mb-lg">Puntuación del Juego</div>
            
            <!-- Agregar nuevo jugador -->
            <div class="q-mb-lg">
              <q-input 
                v-model="newPlayerName" 
                label="Nombre del jugador"
                outlined
                dense
                class="q-mb-md"
              />
              <q-btn 
                @click="addPlayer" 
                color="positive"
                icon="person_add"
                label="Agregar Jugador"
                :disable="!newPlayerName.trim()"
              />
            </div>

            <!-- Lista de jugadores -->
            <div class="row q-gutter-md">
              <div 
                v-for="(player, index) in players" 
                :key="index"
                class="col-12 col-sm-6 col-md-4"
              >
                <q-card flat bordered class="bg-grey-2">
                  <q-card-section>
                    <div class="text-h6 text-weight-medium q-mb-sm">{{ player.name }}</div>
                    <div class="text-body1 text-grey-8 q-mb-md">
                      Puntuación: <span class="text-weight-bold text-primary">{{ player.score }}</span>
                    </div>
                    
                    <div class="row q-gutter-xs">
                      <q-btn 
                        @click="updatePlayerScore(index, player.score + 1)" 
                        color="primary"
                        icon="add"
                        label="+1"
                        size="sm"
                        class="col"
                      />
                      <q-btn 
                        @click="updatePlayerScore(index, player.score - 1)" 
                        color="negative"
                        icon="remove"
                        label="-1"
                        size="sm"
                        class="col"
                      />
                      <q-btn 
                        @click="removePlayer(index)" 
                        color="grey"
                        icon="delete"
                        label="Eliminar"
                        size="sm"
                        class="col"
                      />
                    </div>
                  </q-card-section>
                </q-card>
              </div>
            </div>

            <!-- Estado vacío -->
            <div v-if="players.length === 0" class="text-center q-py-xl">
              <q-icon name="sports_esports" size="4em" color="grey-5" class="q-mb-md" />
              <div class="text-grey-8">No hay jugadores agregados aún</div>
              <div class="text-caption text-grey-6">Agrega el primer jugador para comenzar</div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>

// TODO CAMBIAR FORMATO Y INPUT PARA VALOR PUNTO
import PlayerCard from './PlayerCard.vue';

export default {
  name: 'NotePuntos',
  components: {
    PlayerCard
  },
  props: {
    content: {
      type: String,
      default: '[]'
    }
  },
  data() {
    return {
      newPlayerName: '',
      players: []
    };
  },
  watch: {
    content: {
      immediate: true,
      handler(newContent) {
        try {
          const parsed = newContent ? JSON.parse(newContent) : [];
          this.players = Array.isArray(parsed) ? parsed : [];
        } catch (e) {
          console.error('Error parsing content:', e);
          this.players = [];
        }
      }
    }
  },
  methods: {
    addPlayer() {
      if (this.newPlayerName.trim()) {
        // Ensure players is an array
        if (!Array.isArray(this.players)) {
          this.players = [];
        }
        
        const newPlayer = {
          id: Date.now(),
          name: this.newPlayerName.trim(),
          score: 0
        };
        this.players.push(newPlayer);
        this.newPlayerName = '';
        this.saveChanges();
      }
    },
    updatePlayerScore(index, score) {
      // Ensure players is an array
      if (!Array.isArray(this.players)) {
        this.players = [];
        return;
      }
      
      if (this.players[index]) {
        this.players[index].score = score;
        this.saveChanges();
      }
    },
    removePlayer(index) {
      // Ensure players is an array
      if (!Array.isArray(this.players)) {
        this.players = [];
        return;
      }
      
      this.players.splice(index, 1);
      this.saveChanges();
    },
    saveChanges() {
      const newData = JSON.stringify(this.players);
      this.$emit('update:content', newData);
    }
  }
};
</script>

<style scoped>
.containerClasses {
  /* @apply bg-background text-danger-foreground p-4 rounded-lg shadow-lg; */
}
</style>