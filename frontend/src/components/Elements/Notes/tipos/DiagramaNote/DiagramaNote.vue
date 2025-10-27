<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row q-gutter-md">
      <div class="col-12 col-md-3">
        <!-- Formulario -->
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h6 text-weight-bold q-mb-md">{{ $t('notes.diagram.addElement') }}</div>

            <q-form class="q-gutter-md">
              <!-- Tipo de elemento -->
              <q-select
                v-model="newItem.type"
                :options="elementTypes"
                outlined
                dense
                emit-value
                map-options
              />

              <!-- Nombre/texto -->
              <q-input
                v-if="newItem.type === 'text'"
                v-model="newItem.text"
                :placeholder="$t('notes.diagram.text')"
                outlined
                dense
              />

              <!-- Color -->
              <div v-if="newItem.type !== 'text' && newItem.type !== 'arrow'">
                <div class="text-subtitle2 q-mb-sm">{{ $t('notes.diagram.color') }}</div>
                <input type="color" v-model="newItem.color" class="color-input" />
              </div>

              <!-- Botones -->
              <q-btn
                @click="addItem"
                color="positive"
                :label="$t('notes.diagram.add')"
                class="full-width"
              />

              <q-btn
                @click="deleteSelectedItem"
                color="negative"
                :label="$t('notes.diagram.deleteSelected')"
                class="full-width"
              />
            </q-form>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-9">
        <!-- Área de dibujo -->
        <q-card flat bordered>
          <q-card-section>
            <div class="row items-center justify-between q-mb-md">
              <div class="text-h6 text-weight-bold">{{ $t('notes.diagram.title') }}</div>
              <div class="q-gutter-xs">
                <q-btn
                  @click="undo"
                  :disable="historyIndex <= 0"
                  icon="undo"
                  color="grey"
                  flat
                  round
                  dense
                >
                  <q-tooltip>Deshacer</q-tooltip>
                </q-btn>
                <q-btn
                  @click="redo"
                  :disable="historyIndex >= history.length - 1"
                  icon="redo"
                  color="grey"
                  flat
                  round
                  dense
                >
                  <q-tooltip>Rehacer</q-tooltip>
                </q-btn>
                <q-btn
                  @click="saveDiagram"
                  icon="save"
                  color="positive"
                  flat
                  round
                  dense
                >
                  <q-tooltip>Guardar</q-tooltip>
                </q-btn>
              </div>
            </div>
            <div 
              id="diagram-stage" 
              class="diagram-stage"
              style="width: 100%; height: 500px;"
            ></div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
import Konva from 'konva';

export default {
  name: 'DiagramaFlujo',
  props: {
    content: {
      type: String,
      default: '{}'
    }
  },
  data() {
    return {
      savedDiagramJson: '{"attrs":{...}}',
      newItem: {
        type: 'process',
        text: '',
        color: '#ffffff'
      },
      stage: null,
      elementsLayer: null,
      arrowsLayer: null,
      transformer: null,
      selectedItemsForArrow: [],
      history: [],
      historyIndex: -1,
      isInternalUpdate: false
    };
  },
  computed: {
    elementTypes() {
      return [
        { label: this.$t('notes.diagram.types.process'), value: 'process' },
        { label: this.$t('notes.diagram.types.start'), value: 'start' },
        { label: this.$t('notes.diagram.types.decision'), value: 'decision' },
        { label: this.$t('notes.diagram.types.end'), value: 'end' },
        { label: this.$t('notes.diagram.types.text'), value: 'text' },
        { label: this.$t('notes.diagram.types.arrow'), value: 'arrow' }
      ];
    },
    isFormValid() {
      if (this.newItem.type === 'text') {
        return this.newItem.text && this.newItem.text.trim() !== '';
      }
      return true;
    }
  },
  mounted() {
    // Inicializar siempre el stage, independientemente del modo
    this.$nextTick(() => {
      this.initializeStage();
    });
  },
  beforeUnmount() {
    if (this.stage) {
      this.stage.destroy();
    }
  },
  methods: {
    initializeStage() {
      // Usar un setTimeout para asegurar que el DOM esté completamente renderizado
      setTimeout(() => {
        try {
          // Destruir stage existente si existe
          if (this.stage) {
            this.stage.destroy();
          }

          const container = document.getElementById('diagram-stage');
          if (!container) {
            console.error("No se encontró el contenedor con ID 'diagram-stage'");
            return;
          }

          // Limpiar el contenedor
          container.innerHTML = '';

          // Asegurar que el contenedor tenga dimensiones
          const containerRect = container.getBoundingClientRect();
          const width = Math.max(containerRect.width, container.clientWidth, 700);
          const height = 480; // Un poco menos que el contenedor para dejar espacio

          console.log('Dimensiones del contenedor:', {
            containerRect,
            clientWidth: container.clientWidth,
            finalWidth: width,
            finalHeight: height
          });

          this.stage = new Konva.Stage({
            container: container,
            width,
            height
          });

          // Agregar un fondo visible para debugging
          const backgroundLayer = new Konva.Layer();
          const background = new Konva.Rect({
            x: 0,
            y: 0,
            width,
            height,
            fill: '#f8f9fa',
            stroke: '#dee2e6',
            strokeWidth: 1
          });
          backgroundLayer.add(background);
          this.stage.add(backgroundLayer);

          this.elementsLayer = new Konva.Layer();
          this.stage.add(this.elementsLayer);
          this.arrowsLayer = new Konva.Layer();
          this.stage.add(this.arrowsLayer);

          this.transformer = new Konva.Transformer({
            rotateEnabled: true,
            resizeEnabled: false,
            keepRatio: true,
            borderStroke: '#4e974e',
            anchorFill: '#4e974e',
            anchorStroke: '#4e974e',
            anchorStrokeWidth: 2,
            anchorStyle: 'circle',
            anchorSize: 10
          });

          this.elementsLayer.add(this.transformer);
          this.setupEventListeners();
          
          // Dibujar todos los layers
          backgroundLayer.draw();
          this.elementsLayer.draw();
          this.arrowsLayer.draw();
          
          this.saveState();
          
          console.log('Stage inicializado correctamente', {
            width,
            height,
            stage: this.stage,
            elementsLayer: this.elementsLayer,
            canvasElement: container.querySelector('canvas')
          });
          
        } catch (error) {
          console.error('Error al inicializar el stage:', error);
        }
      }, 100);
    },

    

    loadInitialContent() {
      this.$nextTick(() => {
        try {
          // Verificar si el contenido es válido antes de parsear
          if (!this.content || typeof this.content !== 'string' || this.content.trim() === '') {
            console.warn('Contenido inválido o vacío al cargar diagrama');
            this.initializeStage();
            return;
          }

          const container = document.getElementById('diagram-stage');
          if (!container) {
            throw new Error("No se encontró el contenedor con ID 'diagram-stage'");
          }

          console.log('Contenido cargado antes del parse:', this.content);

          let content;
          try {
            content = JSON.parse(this.content);
          } catch (parseError) {
            console.error('Error al parsear JSON:', parseError);
            this.initializeStage();
            return;
          }

          console.log('Contenido cargado:', content);

          if (this.stage) {
            this.stage.destroy();
          }

          this.stage = Konva.Node.create(content, container);

          this.elementsLayer = this.stage.findOne('Layer') || new Konva.Layer();
          this.arrowsLayer = this.stage.findOne('Layer') || new Konva.Layer();

          if (!this.stage.findOne('Layer')) {
            this.stage.add(this.elementsLayer);
            this.stage.add(this.arrowsLayer);
          }

          this.transformer = this.elementsLayer.findOne('Transformer') || new Konva.Transformer({
            rotateEnabled: true,
            resizeEnabled: false,
            keepRatio: true,
            borderStroke: '#4e974e',
            anchorFill: '#4e974e',
            anchorStroke: '#4e974e',
            anchorStrokeWidth: 2,
            anchorStyle: 'circle',
            anchorSize: 10
          });

          this.elementsLayer.add(this.transformer);
          this.setupEventListeners();

          this.history = [content];
          this.historyIndex = 0;

        } catch (error) {
          console.error('Error al cargar el contenido inicial:', error);
          this.initializeStage();
        }
      });
    },
    
    saveDiagram() {
      const cleanState = this.stage.toObject();
      this.isInternalUpdate = true;
      this.$emit('update:content', JSON.stringify(cleanState));
      this.$emit('save', cleanState);
      
      this.$nextTick(() => {
        setTimeout(() => {
          this.isInternalUpdate = false;
        }, 100);
      });
    },

    setupEventListeners() {
      this.stage.on('click tap', (e) => {
        if (e.target === this.stage) {
          this.transformer.nodes([]);
          this.selectedItemsForArrow = [];
          return;
        }

        const clickedNode = this.findParentNode(e.target);
        const isArrow = this.isArrowNode(clickedNode);

        if (isArrow) {
          this.transformer.nodes([clickedNode]);
          clickedNode.moveToTop();
          return;
        }

        if (clickedNode.nodeType === 'Group' || clickedNode.getClassName() === 'Text') {
          this.transformer.nodes([clickedNode]);
          clickedNode.moveToTop();
        }

        if (!isArrow && clickedNode.nodeType === 'Group') {
          this.handleNodeSelectionForArrow(clickedNode);
        }
      });

      this.stage.on('dragend', (e) => {
        if (e.target.name() === 'arrow-group') {
          e.target.moveToBottom();
          this.elementsLayer.children.forEach(child => child.moveToTop());
        }
        this.saveState();
      });
    },

    saveState() {
      if (this.historyIndex < this.history.length - 1) {
        this.history = this.history.slice(0, this.historyIndex + 1);
      }

      const state = this.stage.toObject();
      this.history.push(state);
      this.historyIndex = this.history.length - 1;
      
      // Marcar que estamos actualizando internamente
      this.isInternalUpdate = true;
      this.$emit('update:content', JSON.stringify(state));
      
      // Resetear la bandera después de un pequeño delay
      this.$nextTick(() => {
        setTimeout(() => {
          this.isInternalUpdate = false;
        }, 100);
      });
    },

    undo() {
      if (this.historyIndex > 0) {
        this.historyIndex--;
        this.restoreState();
      }
    },

    redo() {
      if (this.historyIndex < this.history.length - 1) {
        this.historyIndex++;
        this.restoreState();
      }
    },

    restoreState() {
      const state = this.history[this.historyIndex];
      this.stage = Konva.Node.create(state, 'diagram-stage');
      this.elementsLayer = this.stage.find('Layer')[0];
      this.arrowsLayer = this.stage.find('Layer')[1];
      this.transformer = this.elementsLayer.findOne('Transformer');
      this.setupEventListeners();
    },

    findParentNode(node) {
      return node.parent && node.parent.nodeType === 'Group' ? node.parent : node;
    },

    isArrowNode(node) {
      return node.children?.some(child => child.getClassName() === 'Arrow');
    },

    handleNodeSelectionForArrow(node) {
      this.selectedItemsForArrow.push(node);
      if (this.selectedItemsForArrow.length === 2) {
        this.createArrowBetweenNodes(this.selectedItemsForArrow[0], this.selectedItemsForArrow[1]);
        this.selectedItemsForArrow = [];
        this.saveState();
      }
    },

    getEdgeIntersection(node, targetPoint) {
      const nodeCenter = this.getNodeCenter(node);
      const shape = node.children ? node.children[0] : node;
      
      const angle = Math.atan2(targetPoint.y - nodeCenter.y, targetPoint.x - nodeCenter.x);
      
      if (shape instanceof Konva.Circle) {
        const radius = shape.radius();
        return {
          x: nodeCenter.x + radius * Math.cos(angle),
          y: nodeCenter.y + radius * Math.sin(angle)
        };
      } else if (shape instanceof Konva.RegularPolygon && shape.sides() === 4) {
        const radius = shape.radius();
        const diamondAngle = Math.atan2(
          Math.sin(angle) * Math.cos(Math.PI/4),
          Math.cos(angle) * Math.cos(Math.PI/4)
        );
        return {
          x: nodeCenter.x + radius * Math.cos(diamondAngle),
          y: nodeCenter.y + radius * Math.sin(diamondAngle)
        };
      } else if (shape instanceof Konva.Rect) {
        const halfWidth = shape.width() / 2;
        const halfHeight = shape.height() / 2;
        
        const ratio = Math.min(
          Math.abs(halfWidth / Math.cos(angle)),
          Math.abs(halfHeight / Math.sin(angle))
        );
        
        return {
          x: nodeCenter.x + ratio * Math.cos(angle),
          y: nodeCenter.y + ratio * Math.sin(angle)
        };
      }
      
      if (node.getClassName() === 'Text') {
        const halfWidth = node.width() / 2;
        const halfHeight = node.height() / 2;
        
        const ratio = Math.min(
          Math.abs(halfWidth / Math.cos(angle)),
          Math.abs(halfHeight / Math.sin(angle))
        );
        
        return {
          x: nodeCenter.x + ratio * Math.cos(angle),
          y: nodeCenter.y + ratio * Math.sin(angle)
        };
      }
      
      return nodeCenter;
    },

    createArrowBetweenNodes(fromNode, toNode) {
      const fromEdge = this.getEdgeIntersection(fromNode, this.getNodeCenter(toNode));
      const toEdge = this.getEdgeIntersection(toNode, this.getNodeCenter(fromNode));
      
      const arrowGroup = new Konva.Group({
        draggable: true,
        name: 'arrow-group',
        listening: false
      });

      const arrow = new Konva.Arrow({
        points: [0, 0, toEdge.x - fromEdge.x, toEdge.y - fromEdge.y],
        stroke: '#0e1b0e',
        strokeWidth: 2,
        pointerLength: 10,
        pointerWidth: 10,
        name: 'arrow-line',
        hitStrokeWidth: 0
      });

      arrowGroup.position(fromEdge);
      arrowGroup.add(arrow);
      this.arrowsLayer.add(arrowGroup);
      
      const updateZIndex = () => {
        this.elementsLayer.children.forEach(child => child.moveToTop());
        arrowGroup.moveToBottom();
      };

      const updateArrowPosition = () => {
        const newFromEdge = this.getEdgeIntersection(fromNode, this.getNodeCenter(toNode));
        const newToEdge = this.getEdgeIntersection(toNode, this.getNodeCenter(fromNode));
        
        arrowGroup.position(newFromEdge);
        arrow.points([0, 0, newToEdge.x - newFromEdge.x, newToEdge.y - newFromEdge.y]);
        updateZIndex();
        this.arrowsLayer.batchDraw();
      };

      fromNode.on('dragmove', updateArrowPosition);
      toNode.on('dragmove', updateArrowPosition);

      arrowGroup.on('mouseover', () => {
        arrow.strokeWidth(4);
        this.arrowsLayer.draw();
      });

      arrowGroup.on('mouseout', () => {
        arrow.strokeWidth(2);
        this.arrowsLayer.draw();
      });

      arrowGroup.on('dragstart', () => {
        arrowGroup.moveToTop();
      });

      arrowGroup.on('dragend', () => {
        updateZIndex();
        this.saveState();
      });

      updateZIndex();
      this.arrowsLayer.draw();
    },

    getNodeCenter(node) {
      if (node.children) {
        const shape = node.children[0];
        if (shape instanceof Konva.Circle) {
          return { x: node.x(), y: node.y() };
        } else if (shape instanceof Konva.RegularPolygon) {
          return { x: node.x(), y: node.y() };
        } else if (shape instanceof Konva.Rect) {
          return { 
            x: node.x() + shape.width() / 2, 
            y: node.y() + shape.height() / 2 
          };
        }
      }
      if (node.getClassName() === 'Text') {
        return { 
          x: node.x() + node.width() / 2, 
          y: node.y() + node.height() / 2 
        };
      }
      return { x: node.x(), y: node.y() };
    },

    addItem() {
      console.log('addItem llamado', {
        isFormValid: this.isFormValid,
        newItemType: this.newItem.type,
        stage: this.stage,
        elementsLayer: this.elementsLayer
      });

      if (!this.isFormValid) {
        console.log('Formulario no válido');
        return;
      }

      if (!this.stage || !this.elementsLayer) {
        console.error('Stage o elementsLayer no inicializados');
        this.$q.notify({
          type: 'negative',
          message: 'Error: Canvas no inicializado',
          position: 'top'
        });
        return;
      }

      if (this.newItem.type === 'arrow') {
        this.$q.notify({
          type: 'info',
          message: this.$t('notes.diagram.selectTwoElements'),
          position: 'top'
        });
        return;
      }

      try {
        const centerX = this.stage.width() / 2;
        const centerY = this.stage.height() / 2;

        let shape;
        switch (this.newItem.type) {
          case 'start':
            shape = this.createStart(centerX, centerY, this.newItem.color);
            break;
          case 'end':
            shape = this.createEnd(centerX, centerY, this.newItem.color);
            break;
          case 'decision':
            shape = this.createDecision(centerX, centerY, this.newItem.color);
            break;
          case 'process':
            shape = this.createProcess(centerX, centerY, this.newItem.color);
            break;
          case 'text':
            shape = this.createText(centerX, centerY, this.newItem.text);
            break;
          default:
            console.error('Tipo de elemento no reconocido:', this.newItem.type);
            return;
        }

        if (shape) {
          console.log('Agregando forma al layer:', shape);
          this.elementsLayer.add(shape);
          shape.moveToTop();
          
          // Forzar redraw de todos los layers
          this.elementsLayer.draw();
          this.arrowsLayer.draw();
          this.stage.draw();
          
          this.saveState();
          
          console.log('Elemento agregado y renderizado:', {
            layerChildren: this.elementsLayer.children.length,
            stageSize: { width: this.stage.width(), height: this.stage.height() }
          });
          
          this.$q.notify({
            type: 'positive',
            message: 'Elemento agregado correctamente',
            position: 'top'
          });
        } else {
          console.error('No se pudo crear la forma');
        }

        this.newItem.text = '';
      } catch (error) {
        console.error('Error al agregar elemento:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error al agregar elemento: ' + error.message,
          position: 'top'
        });
      }
    },

    createProcess(x, y, fillColor = '#ffffff') {
      const group = new Konva.Group({ 
        x, 
        y, 
        draggable: true,
        name: 'process'
      });

      const rect = new Konva.Rect({
        width: 120,
        height: 50,
        fill: fillColor,
        stroke: '#0e1b0e',
        strokeWidth: 2,
        cornerRadius: 8
      });

      const text = new Konva.Text({
        text: this.$t('notes.diagram.types.process'),
        fontSize: 14,
        fontFamily: 'Arial',
        fill: '#0e1b0e',
        width: 120,
        align: 'center',
        y: 15
      });

      group.add(rect);
      group.add(text);
      return group;
    },

    createStart(x, y, fillColor = '#e7f3e7') {
      const group = new Konva.Group({ 
        x, 
        y, 
        draggable: true,
        name: 'start'
      });

      const circle = new Konva.Circle({
        radius: 30,
        fill: fillColor,
        stroke: '#0e1b0e',
        strokeWidth: 2
      });

      const text = new Konva.Text({
        text: this.$t('notes.diagram.types.start'),
        fontSize: 14,
        fontFamily: 'Arial',
        fill: '#0e1b0e',
        width: 60,
        align: 'center',
        y: -8,
        x: -30
      });

      group.add(circle);
      group.add(text);
      return group;
    },

    createEnd(x, y, fillColor = '#fce6e6') {
      const group = new Konva.Group({ 
        x, 
        y, 
        draggable: true,
        name: 'end'
      });

      const circle = new Konva.Circle({
        radius: 30,
        fill: fillColor,
        stroke: '#0e1b0e',
        strokeWidth: 2
      });

      const text = new Konva.Text({
        text: this.$t('notes.diagram.types.end'),
        fontSize: 14,
        fontFamily: 'Arial',
        fill: '#0e1b0e',
        width: 60,
        align: 'center',
        y: -8,
        x: -30
      });

      group.add(circle);
      group.add(text);
      return group;
    },

    createDecision(x, y, fillColor = '#f8e5e5') {
      const group = new Konva.Group({ 
        x, 
        y, 
        draggable: true,
        name: 'decision'
      });

      const diamond = new Konva.RegularPolygon({
        sides: 4,
        radius: 40,
        rotation: 45,
        fill: fillColor,
        stroke: '#0e1b0e',
        strokeWidth: 2
      });

      const text = new Konva.Text({
        text: this.$t('notes.diagram.types.decision'),
        fontSize: 14,
        fontFamily: 'Arial',
        fill: '#0e1b0e',
        width: 80,
        align: 'center',
        y: -8,
        x: -40
      });

      group.add(diamond);
      group.add(text);
      return group;
    },

    createText(x, y, text = this.$t('notes.diagram.types.text')) {
      return new Konva.Text({
        x,
        y,
        text,
        fontSize: 14,
        fontFamily: 'Arial',
        fill: '#0e1b0e',
        draggable: true,
        name: 'text'
      });
    },

    deleteSelectedItem() {
      const selectedNodes = this.transformer.nodes();
      if (selectedNodes.length === 0) return;

      const selectedNode = selectedNodes[0];
      
      if (selectedNode.name() !== 'arrow-group') {
        this.arrowsLayer.find('.arrow-group').forEach(arrowGroup => {
          const arrow = arrowGroup.children[0];
          const points = arrow.points();
          const arrowPos = arrowGroup.position();
          
          const fromPos = { x: arrowPos.x + points[0], y: arrowPos.y + points[1] };
          const toPos = { x: arrowPos.x + points[2], y: arrowPos.y + points[3] };
          const nodeCenter = this.getNodeCenter(selectedNode);
          
          if (
            (Math.abs(fromPos.x - nodeCenter.x) < 5 && Math.abs(fromPos.y - nodeCenter.y) < 5) ||
            (Math.abs(toPos.x - nodeCenter.x) < 5 && Math.abs(toPos.y - nodeCenter.y) < 5)
          ) {
            arrowGroup.destroy();
          }
        });
      }
      
      selectedNode.destroy();
      this.transformer.nodes([]);
      this.elementsLayer.draw();
      this.arrowsLayer.draw();
      this.saveState();
    }
  },
  watch: {
    content: {
      immediate: true,
      handler() {
        // No reinicializar si estamos haciendo una actualización interna
        if (this.isInternalUpdate) {
          console.log('Evitando reinicialización - actualización interna');
          return;
        }
        
        this.$nextTick(() => {
          if (this.$route.path.includes('editar')) {
            // Solo intentamos cargar si hay contenido válido
            if (this.content && typeof this.content === 'string' && this.content.trim() !== '') {
              console.log('Cargando contenido inicial para modo editar');
              this.loadInitialContent();
            }
          } else {
            console.log('Inicializando stage para modo nuevo');
            this.initializeStage();
          }
        });
      }
    }
  }
};
</script>

<style scoped>
.diagram-stage {
  border: 2px solid #4e974e;
  background: white;
  min-height: 500px;
  height: 500px;
  border-radius: 8px;
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.diagram-stage canvas {
  border-radius: 6px;
  max-width: 100%;
  max-height: 100%;
}

.color-input {
  width: 100%;
  height: 40px;
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 2px;
}
</style>