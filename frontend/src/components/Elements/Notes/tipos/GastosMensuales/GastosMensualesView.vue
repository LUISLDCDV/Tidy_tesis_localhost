<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row justify-center">
      <div class="col-12 col-lg-10">
        <!-- Header -->
        <q-card flat bordered class="q-mb-lg">
          <q-card-section>
            <div class="row items-center justify-between">
              <div class="text-h4 text-positive text-weight-bold">Gestión de Gastos</div>
              <q-btn 
                @click="openForm('add')" 
                color="positive"
                icon="add"
                label="Agregar Gasto"
              />
            </div>
          </q-card-section>
        </q-card>

        <!-- Selector de año -->
        <q-card flat bordered class="q-mb-lg">
          <q-card-section>
            <div class="row items-center justify-between">
              <q-btn 
                @click="decrementYear" 
                flat
                color="positive"
                icon="chevron_left"
                label="Anterior"
              />
              <div class="text-h5 text-weight-bold">{{ currentYear }}</div>
              <q-btn 
                @click="currentYear++" 
                flat
                color="positive"
                icon="chevron_right"
                label="Siguiente"
              />
            </div>
          </q-card-section>
        </q-card>

        <!-- Meses con gastos -->
        <div class="q-gutter-md">
          <q-card v-for="(month, index) in filteredMonthNames" :key="index" flat bordered>
            <q-card-section>
              <div class="row items-center justify-between q-mb-md">
                <div class="text-h5 text-weight-bold">{{ month }} {{ currentYear }}</div>
                <div class="text-h6 text-negative text-weight-bold">
                  Total: ${{ getTotalForMonth(month).toFixed(2) }}
                </div>
              </div>

              <q-table
                :columns="tableColumns"
                :rows="getExpensesForMonth(month)"
                row-key="id"
                flat
                bordered
                :pagination="{ rowsPerPage: 0 }"
              >
                <template v-slot:body-cell-status="props">
                  <q-td :props="props">
                    <q-chip 
                      @click="toggleStatus(getExpenseIndex(month, props.rowIndex))"
                      :color="props.value === 'Paid' ? 'positive' : 'negative'"
                      text-color="white"
                      clickable
                    >
                      {{ props.value }}
                    </q-chip>
                  </q-td>
                </template>

                <template v-slot:body-cell-actions="props">
                  <q-td :props="props">
                    <div class="q-gutter-xs">
                      <q-btn
                        @click="openForm('edit', getExpenseIndex(month, props.rowIndex))"
                        icon="edit"
                        color="primary"
                        flat
                        round
                        dense
                        size="sm"
                      >
                        <q-tooltip>Editar</q-tooltip>
                      </q-btn>
                      <q-btn
                        @click="deleteGasto(getExpenseIndex(month, props.rowIndex))"
                        icon="delete"
                        color="negative"
                        flat
                        round
                        dense
                        size="sm"
                      >
                        <q-tooltip>Eliminar</q-tooltip>
                      </q-btn>
                    </div>
                  </q-td>
                </template>

                <template v-slot:no-data>
                  <div class="full-width row flex-center text-grey q-gutter-sm">
                    <q-icon size="2em" name="sentiment_dissatisfied" />
                    <span>No hay gastos para este mes</span>
                  </div>
                </template>
              </q-table>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Modal Form -->
    <q-dialog v-model="formVisible" persistent>
      <q-card style="min-width: 400px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">
            {{ formMode === 'add' ? 'Agregar' : 'Editar' }} Gasto
          </div>
          <q-space />
          <q-btn icon="close" flat round dense @click="closeForm" />
        </q-card-section>

        <q-card-section>
          <q-form class="q-gutter-md">
            <q-input
              v-model="form.name"
              label="Nombre"
              outlined
              dense
              required
            />
            
            <q-input
              v-model.number="form.amount"
              type="number"
              label="Monto"
              outlined
              dense
              required
            />
            
            <q-input
              v-model="form.dueDate"
              type="date"
              label="Fecha de vencimiento"
              outlined
              dense
              required
            />
            
            <q-select
              v-model="form.installments"
              :options="installmentOptions"
              label="Cuotas"
              outlined
              dense
              emit-value
              map-options
              :option-label="opt => `${opt} cuotas`"
              :option-value="opt => opt"
            />

            <!-- Errores -->
            <div v-if="error" class="text-negative">{{ error }}</div>
          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="q-gutter-sm">
          <q-btn
            @click="closeForm"
            label="Cancelar"
            color="grey"
            outline
          />
          <q-btn
            @click="saveForm"
            label="Guardar"
            color="positive"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>

// TODO: MEJORAR FORMATO MAS PROLIJO
// TODO: Modal para ver lo pagado y lo que falta por paga
export default {
  name: 'GastosManager',
  props: {
    content: {
      type: String,
      default: '{}'
    }
  },
  data() {
    const current = new Date();
    return {
      minYear: current.getFullYear(),
      currentYear: current.getFullYear(),
      newGasto: { descripcion: '', monto: 0 },
      gastos: [],
      
      // Configuración del formulario
      formVisible: false,
      formMode: 'add',
      formIndex: null,
      form: {
        id: null,
        name: '',
        amount: null,
        dueDate: '',
        status: 'Unpaid',
        installments: 1
      },
      
      // Opciones
      installmentOptions: [1, 3, 6, 9, 12, 24, 36],
      error: '',
      
      // Configuración de fechas
      monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
      
      // Configuración de tabla
      tableColumns: [
        { name: 'name', required: true, label: 'Nombre', align: 'left', field: 'name', sortable: true },
        { name: 'amount', required: true, label: 'Monto', align: 'right', field: 'amount', sortable: true, format: val => `$${val}` },
        { name: 'dueDate', required: true, label: 'Fecha', align: 'center', field: 'dueDate', sortable: true },
        { name: 'status', required: true, label: 'Estado', align: 'center', field: 'status', sortable: true },
        { name: 'actions', required: true, label: 'Acciones', align: 'center' }
      ]
    };
  },
  watch: {
    content: {  // ✅ Ahora sí, se observa la propiedad "content"
      immediate: true,
      handler(newContent) {
        try {
          const parsed = newContent ? JSON.parse(newContent) : {};
          this.gastos = parsed.gastos || [];
        } catch (e) {
          console.error('Error parsing content:', e);
        }
      }
    },
    gastos: {
      deep: true,
      handler(newVal) {
        const dataToSave = {
          gastos: newVal
        };
        this.$emit('update:content', JSON.stringify(dataToSave));
      }
    }
  },
  computed: {
    // Gastos filtrados por mes actual
    filteredMonthNames() {
    const current = new Date();
    const currentMonthIndex = current.getMonth();
    if (this.currentYear === current.getFullYear()) {
      console.log(this.monthNames.slice(currentMonthIndex));

      return this.monthNames.slice(currentMonthIndex);
    }
    console.log(this.monthNames);
    return this.monthNames;
  },
    monthlyExpenses() {
      const result = {};
      
      this.gastos.forEach(gasto => {
        const date = new Date(gasto.dueDate);
        const month = this.monthNames[date.getMonth()];
        const year = date.getFullYear();
        
        if (year === this.currentYear) {
          if (!result[month]) {
            result[month] = [];
          }
          result[month].push(gasto);
        }
      });
      
      // Asegurar que todos los meses estén presentes
      this.monthNames.forEach(month => {
        if (!result[month]) {
          result[month] = [];
        }
      });
      
      return result;
    }
  },
  
  methods: {
    // Formulario
    decrementYear() {
    if (this.currentYear > this.minYear) {
      this.currentYear--;
    }
  },
    openForm(mode, index = null) {
      this.error = '';
      this.formMode = mode;
      
      if (mode === 'edit' && index !== null) {
        this.formIndex = index;
        this.form = { ...this.gastos[index] };
      } else {
        this.formIndex = null;
        this.form = {
          id: Date.now(),
          name: '',
          amount: null,
          dueDate: '',
          status: 'Unpaid',
          installments: 1
        };
      }
      this.formVisible = true;
    },
    
    closeForm() {
      this.formVisible = false;
    },
    
    // Guardar gasto
    saveForm() {
      // Validación básica
      if (!this.form.name || !this.form.amount || !this.form.dueDate) {
        this.error = 'Todos los campos son obligatorios';
        return;
      }

      const { name, amount, dueDate, installments } = this.form;

      if (installments === 1) {
        // Solo un registro
        const newGasto = {
          id: this.form.id || Date.now(),
          name,
          amount: amount.toFixed(2),
          dueDate,
          status: this.form.status || 'Unpaid',
          installments
        };

        if (this.formMode === 'add') {
          this.gastos.push(newGasto);
        } else {
          this.$set(this.gastos, this.formIndex, newGasto);
        }
      } else {
        // Generar múltiples cuotas
        const amountPerInstallment = (amount / installments).toFixed(2);
        const baseDate = new Date(dueDate);
        const gastosToAdd = [];

        for (let i = 0; i < installments; i++) {
          const installmentNumber = i + 1;
          const currentDate = new Date(baseDate);
          currentDate.setMonth(baseDate.getMonth() + i);

          const formattedDate = currentDate.toISOString().split('T')[0];

          gastosToAdd.push({
            id: Date.now() + i,
            name: `${name} (${installmentNumber}/${installments})`,
            amount: amountPerInstallment,
            dueDate: formattedDate,
            status: 'Unpaid',
            installments: 1 // Cada uno es una sola cuota
          });
        }

        if (this.formMode === 'add') {
          this.gastos.push(...gastosToAdd);
        } else {
          this.gastos.splice(this.formIndex, 1, ...gastosToAdd);
        }
      }

      this.closeForm();
    },
    
    // Eliminar gasto
    deleteGasto(index) {
      this.gastos.splice(index, 1);
    },
    
    // Cambiar estado
    toggleStatus(index) {
  this.gastos = this.gastos.map((gasto, idx) => {
    if (idx === index) {
      return {
        ...gasto,
        status: gasto.status === 'Paid' ? 'Unpaid' : 'Paid'
      };
    }
    return gasto;
  });
},
getTotalForMonth(month) {
    const expenses = this.getExpensesForMonth(month);
    return expenses.reduce((sum, expense) => {
      const amount = parseFloat(expense.amount);
      return sum + (isNaN(amount) ? 0 : amount);
    }, 0);
  },
    // Métodos auxiliares
    getExpensesForMonth(month) {
      const date = new Date();
      const year = this.currentYear;
      const monthIndex = this.monthNames.indexOf(month);
      
      return this.gastos.filter(gasto => {
        const gastoDate = new Date(gasto.dueDate);
        return gastoDate.getFullYear() === year && gastoDate.getMonth() === monthIndex;
      });
    },
    
    getExpenseIndex(month, localIndex) {
      const date = new Date();
      const year = this.currentYear;
      const monthIndex = this.monthNames.indexOf(month);
      
      // Encontrar el índice real en el array completo
      let globalIndex = 0;
      for (let i = 0; i < this.gastos.length; i++) {
        const gastoDate = new Date(this.gastos[i].dueDate);
        const gastoMonth = this.monthNames[gastoDate.getMonth()];
        const gastoYear = gastoDate.getFullYear();
        
        if (gastoYear === year && gastoMonth === month) {
          if (localIndex === 0) {
            return i;
          }
          localIndex--;
        }
      }
      return -1;
    },
    
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.getDate();
    }
  }
};
</script>

<style scoped>
.font-epilogue { font-family: Epilogue, "Noto Sans", sans-serif; }
</style>