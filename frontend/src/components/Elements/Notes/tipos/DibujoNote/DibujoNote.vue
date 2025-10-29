<template>
  <q-page class="q-pa-md ">
    <div class="row justify-center">
      <div class="col-12 col-lg-10">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h6 text-weight-bold q-mb-md">{{ $t('notes.drawing.title') || 'Área de Dibujo' }}</div>
            
            <!-- Canvas de dibujo -->
            <div class="drawing-container q-mb-md">
              <canvas
                ref="drawingCanvas"
                class="drawing-canvas"
                @mousedown="startDrawing"
                @mousemove="draw"
                @mouseup="stopDrawing"
                @mouseleave="stopDrawing"
              ></canvas>
            </div>

            <!-- Controles -->
            <div class="row items-center q-gutter-sm">
              <div class="col-auto">
                <div class="text-caption q-mb-xs">Colores:</div>
                <div class="row q-gutter-xs">
                  <q-btn
                    v-for="color in colorOptions"
                    :key="color.value"
                    @click="changeColor(color.value)"
                    :style="{ backgroundColor: color.value }"
                    :outline="currentColor !== color.value"
                    :unelevated="currentColor === color.value"
                    round
                    size="md"
                    class="color-btn"
                  />
                </div>
              </div>
              
              <div class="col-auto">
                <q-btn
                  @click="clearCanvas"
                  color="negative"
                  label="Limpiar"
                  icon="clear"
                />
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
  name: 'DibujoNote',
  props: {
    content: {
      type: String,
      default: ''
    }
  },
  emits: ['update:content'],
  data() {
    return {
      isDrawing: false,
      currentColor: '#000000',
      ctx: null,
      colorOptions: [
        { value: '#000000', label: 'Negro' },
        { value: '#FF0000', label: 'Rojo' },
        { value: '#00FF00', label: 'Verde' },
        { value: '#0000FF', label: 'Azul' },
        { value: '#FFFF00', label: 'Amarillo' },
        { value: '#FF00FF', label: 'Magenta' },
        { value: '#00FFFF', label: 'Cyan' }
      ]
    };
  },
  mounted() {
    this.initializeCanvas();
    this.loadContent();
  },
  watch: {
    content: {
      immediate: true,
      handler() {
        this.loadContent();
      }
    }
  },
  methods: {
    initializeCanvas() {
      const canvas = this.$refs.drawingCanvas;
      if (!canvas) return;
      
      canvas.width = canvas.offsetWidth || 800;
      canvas.height = 400;
      this.ctx = canvas.getContext('2d');
      this.ctx.lineWidth = 4;
      this.ctx.lineCap = 'round';
      this.ctx.lineJoin = 'round';
    },

    loadContent() {
      if (this.content && this.content.trim() !== '') {
        this.loadDrawingFromBase64(this.content);
      }
    },

    startDrawing(event) {
      this.isDrawing = true;
      this.ctx.beginPath();
      this.ctx.strokeStyle = this.currentColor;
      this.ctx.moveTo(event.offsetX, event.offsetY);
    },

    draw(event) {
      if (!this.isDrawing) return;
      this.ctx.lineTo(event.offsetX, event.offsetY);
      this.ctx.stroke();
    },

    stopDrawing() {
      if (this.isDrawing) {
        this.isDrawing = false;
        this.ctx.closePath();
        this.saveDrawing();
      }
    },

    changeColor(color) {
      this.currentColor = color;
    },

    clearCanvas() {
      const canvas = this.$refs.drawingCanvas;
      if (canvas && this.ctx) {
        this.ctx.clearRect(0, 0, canvas.width, canvas.height);
        this.saveDrawing();
      }
    },

    getCanvasData() {
      const canvas = this.$refs.drawingCanvas;
      return canvas ? canvas.toDataURL('image/png') : '';
    },

    loadDrawingFromBase64(base64Image) {
      const canvas = this.$refs.drawingCanvas;
      if (!canvas || !base64Image) return;

      const ctx = canvas.getContext('2d');
      const img = new Image();
      img.crossOrigin = 'anonymous';
      img.src = base64Image;

      img.onload = () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0);
      };

      img.onerror = (e) => {
        console.error('Error al cargar la imagen desde base64:', e);
      };
    },

    saveDrawing() {
      const dataURL = this.getCanvasData();
      this.$emit('update:content', dataURL);
    }
  }
};
</script>

<style scoped>
.drawing-container {
  display: flex;
  justify-content: center;
  align-items: center;
  border: 2px solid #ddd;
  border-radius: 8px;
  background: white;
  padding: 8px;
}

.drawing-canvas {
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  background: white;
  cursor: crosshair;
  max-width: 100%;
  height: 400px;
}

.color-btn {
  min-width: 32px;
  min-height: 32px;
  border: 2px solid #ddd;
}
</style>