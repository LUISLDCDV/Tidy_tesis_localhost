<template>
  <q-page class="q-pa-md ">
    <div class="row justify-center">
      <div class="col-12">
        <!-- Formulario de nuevo elemento -->
        <q-card flat bordered class="q-mb-lg" style="max-width: 600px; margin: 0 auto;">
          <q-card-section>
            <div class="text-h5 text-weight-bold text-center q-mb-md">Agregar Objeto</div>

            <q-form class="q-gutter-md">
              <!-- Nombre -->
              <q-input
                v-model="newItem.name"
                label="Nombre del objeto"
                placeholder="Sofá, mesa, puerta..."
                outlined
                dense
              />

              <!-- Ubicación -->
              <div>
                <div class="text-subtitle2 q-mb-sm">Ubicación X,Y (metros)</div>
                <div class="row q-gutter-sm">
                  <div class="col">
                    <q-input
                      v-model.number="newItem.x"
                      label="X"
                      type="number"
                      outlined
                      dense
                    />
                  </div>
                  <div class="col">
                    <q-input
                      v-model.number="newItem.y"
                      label="Y"
                      type="number"
                      outlined
                      dense
                    />
                  </div>
                </div>
              </div>

              <!-- Dimensiones -->
              <div>
                <div class="text-subtitle2 q-mb-sm">Dimensiones</div>
                <div class="row q-gutter-sm">
                  <div class="col">
                    <q-input
                      v-model.number="newItem.width"
                      label="Ancho"
                      type="number"
                      outlined
                      dense
                    />
                  </div>
                  <div class="col">
                    <q-select
                      v-model="newItem.widthUnit"
                      :options="unitOptions"
                      outlined
                      dense
                      emit-value
                      map-options
                    />
                  </div>
                  <div class="col">
                    <q-input
                      v-model.number="newItem.depth"
                      label="Profundidad"
                      type="number"
                      outlined
                      dense
                    />
                  </div>
                  <div class="col">
                    <q-select
                      v-model="newItem.depthUnit"
                      :options="unitOptions"
                      outlined
                      dense
                      emit-value
                      map-options
                    />
                  </div>
                </div>
              </div>

              <!-- Altura -->
              <div>
                <div class="text-subtitle2 q-mb-sm">Altura</div>
                <div class="row q-gutter-sm">
                  <div class="col">
                    <q-input
                      v-model.number="newItem.height"
                      label="Altura"
                      type="number"
                      outlined
                      dense
                    />
                  </div>
                  <div class="col">
                    <q-select
                      v-model="newItem.heightUnit"
                      :options="unitOptions"
                      outlined
                      dense
                      emit-value
                      map-options
                    />
                  </div>
                </div>
              </div>

              <!-- Notas -->
              <q-input
                v-model="newItem.notes"
                label="Notas (opcional)"
                type="textarea"
                rows="2"
                outlined
              />

              <!-- Botón Agregar -->
              <q-btn
                @click="addItem"
                color="positive"
                label="Agregar Elemento"
                class="full-width"
              />
            </q-form>
          </q-card-section>
        </q-card>

        <!-- Lista de elementos -->
        <div style="max-width: 600px; margin: 0 auto;" class="q-mb-lg">
          <div class="text-h6 text-weight-bold q-mb-md q-ml-sm">Elementos Agregados</div>
          <div class="q-gutter-sm">
            <q-card
              v-for="(item, index) in items"
              :key="index"
              flat
              bordered
              class="q-pa-md"
              style="border-left: 4px solid #4e974e;"
            >
              <q-card-section class="q-pa-none">
                <div class="text-h6 text-weight-bold">{{ item.name }}</div>
                <div class="text-body2 q-mt-xs">
                  <div>Ancho: {{ item.width }}{{ item.widthUnit }}</div>
                  <div>Profundidad: {{ item.depth }}{{ item.depthUnit }}</div>
                  <div>Altura: {{ item.height }}{{ item.heightUnit }}</div>
                  <div>Posición: ({{ item.x }}, {{ item.y }})</div>
                  <div v-if="item.notes" class="q-mt-xs text-grey-7">{{ item.notes }}</div>
                </div>
                
                <div class="q-mt-sm q-gutter-xs">
                  <q-btn
                    @click="editItem(index)"
                    color="primary"
                    label="Editar"
                    size="sm"
                    outline
                  />
                  <q-btn
                    @click="deleteItem(index)"
                    color="negative"
                    label="Eliminar"
                    size="sm"
                    outline
                  />
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>

        <!-- Área de Dibujo -->
        <q-card flat bordered style="max-width: 800px; margin: 0 auto; position: relative;">
          <q-card-section>
            <div class="text-h6 text-weight-bold q-mb-md">Plano 2D</div>
            
            <div class="drawing-area" :style="{ width: '800px', height: '600px', position: 'relative', border: '1px solid #ddd' }">
              <!-- Cuadrícula -->
              <div class="grid-container">
                <div 
                  v-for="(cell, i) in 20 * 15" 
                  :key="i" 
                  class="grid-cell"
                ></div>
              </div>

              <!-- Ejes X e Y -->
              <div class="axes-container">
                <!-- Línea del eje X -->
                <div class="axis-x"></div>
                <!-- Línea del eje Y -->
                <div class="axis-y"></div>

                <!-- Marcas en eje X -->
                <div v-for="col in 20" :key="'x'+col" class="mark-x" :style="{ left: `${col * 40}px` }">
                  <span class="mark-label">{{ col - 1 }}</span>
                  <div class="mark-tick"></div>
                </div>

                <!-- Marcas en eje Y -->
                <div v-for="row in 15" :key="'y'+row" class="mark-y" :style="{ top: `${row * 40}px` }">
                  <span class="mark-label">{{ row - 1 }}</span>
                  <div class="mark-tick"></div>
                </div>
              </div>

              <!-- Objetos renderizados -->
              <div class="objects-container">
                <div 
                  v-for="(item, index) in items" 
                  :key="index"
                  class="object-item"
                  :style="{
                    left: `${item.x * cellSize}px`,
                    top: `${item.y * cellSize}px`,
                    width: `${convertToMeters(item.width, item.widthUnit) * cellSize}px`,
                    height: `${convertToMeters(item.depth, item.depthUnit) * cellSize}px`
                  }"
                >
                  {{ item.name }}<br/>
                  {{ item.width }}{{ item.widthUnit }} x {{ item.depth }}{{ item.depthUnit }}
                  <span v-if="item.notes" class="object-notes">{{ item.notes }}</span>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
export default {
  name: 'DibujoDimensionalView',
  props: {
    content: {
      type: String,
      default: '[]'
    }
  },
  emits: ['update:content'],
  data() {
    return {
      // Tamaño de celda: 1 metro = 40px
      cellSize: 40,

      // Opciones de unidades
      unitOptions: [
        { label: 'Metros', value: 'm' },
        { label: 'Centímetros', value: 'cm' },
        { label: 'Pulgadas', value: 'in' }
      ],

      // Datos del nuevo elemento
      newItem: {
        name: '',
        x: 0,
        y: 0,
        width: 1,
        depth: 1,
        height: 1,
        widthUnit: 'm',
        depthUnit: 'm',
        heightUnit: 'm',
        notes: ''
      },

      // Lista de elementos
      items: []
    };
  },
  watch: {
    content: {
      immediate: true,
      handler(newContent) {
        try {
          const parsed = newContent ? JSON.parse(newContent) : [];
          this.items = Array.isArray(parsed) ? [...parsed] : [];
        } catch (e) {
          console.error('Error parsing content:', e);
          this.items = [];
        }
      }
    }
  },
  methods: {
    addItem() {
      if (!this.newItem.name || this.newItem.width <= 0 || this.newItem.depth <= 0) {
        this.$q.notify({
          type: 'negative',
          message: 'Por favor, completa todos los campos',
          position: 'top'
        });
        return;
      }

      this.items.push({ ...this.newItem });
      this.saveChanges();

      // Resetear formulario
      this.newItem = {
        name: '',
        x: 0,
        y: 0,
        width: 1,
        depth: 1,
        height: 1,
        widthUnit: 'm',
        depthUnit: 'm',
        heightUnit: 'm',
        notes: ''
      };
    },

    editItem(index) {
      this.newItem = { ...this.items[index] };
      this.editingIndex = index;
      this.showModal = true;
    },

    deleteItem(index) {
      this.items.splice(index, 1);
      this.saveChanges();
    },

    convertToMeters(value, unit) {
      switch (unit) {
        case 'cm': return value / 100;
        case 'in': return value * 0.0254;
        default: return value;
      }
    },

    saveChanges() {
      this.$emit('update:content', JSON.stringify(this.items));
    }
  }
};
</script>

<style scoped>
.drawing-area {
  background: white;
  overflow: hidden;
}

.grid-container {
  position: absolute;
  inset: 0;
  display: grid;
  grid-template-columns: repeat(20, 1fr);
  grid-template-rows: repeat(15, 1fr);
  border-top: 1px solid #ddd;
  border-left: 1px solid #ddd;
}

.grid-cell {
  border-right: 1px solid #eee;
  border-bottom: 1px solid #eee;
  width: 100%;
  height: 100%;
}

.axes-container {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.axis-x {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background: black;
  z-index: 10;
}

.axis-y {
  position: absolute;
  left: 0;
  top: 0;
  height: 100%;
  width: 2px;
  background: black;
  z-index: 10;
}

.mark-x {
  position: absolute;
  bottom: 0;
  transform: translateX(-50%);
}

.mark-x .mark-label {
  font-size: 12px;
  color: #666;
  position: absolute;
  bottom: 4px;
  transform: translateX(-50%);
}

.mark-x .mark-tick {
  width: 2px;
  height: 8px;
  background: #666;
  position: absolute;
  transform: translateX(-50%);
}

.mark-y {
  position: absolute;
  right: 0;
  transform: translateY(-50%);
}

.mark-y .mark-label {
  font-size: 12px;
  color: #666;
  position: absolute;
  right: 8px;
  transform: translateY(-50%);
}

.mark-y .mark-tick {
  width: 8px;
  height: 2px;
  background: #666;
  position: absolute;
  right: 0;
  transform: translateY(-50%);
}

.objects-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
}

.object-item {
  position: absolute;
  border: 2px solid #ef4444;
  background: rgba(34, 197, 94, 0.1);
  font-size: 12px;
  text-align: center;
  color: #333;
  padding: 2px;
  overflow: hidden;
}

.object-notes {
  display: block;
  font-size: 10px;
  margin-top: 4px;
  color: #666;
}
</style>