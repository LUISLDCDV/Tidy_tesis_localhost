<template>
  <q-page class="q-pa-md ">
    <div class="row justify-center">
      <div class="col-12 col-lg-10">
        <!-- Budget Summary -->
        <div class="row q-gutter-md q-mb-lg">
          <div class="col-12 col-md-6">
            <q-card flat bordered>
              <q-card-section>
                <div class="text-h6 text-weight-medium">Total Ingresos</div>
                <div class="text-h4 text-weight-bold text-positive">${{ totalIncome }}</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12 col-md-6">
            <q-card flat bordered>
              <q-card-section>
                <div class="text-h6 text-weight-medium">Total Gastos</div>
                <div class="text-h4 text-weight-bold text-negative">${{ totalExpenses }}</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12">
            <q-card flat bordered>
              <q-card-section>
                <div class="text-h6 text-weight-medium">Balance</div>
                <div class="text-h3 text-weight-bold" :class="balance >= 0 ? 'text-positive' : 'text-negative'">${{ balance }}</div>
              </q-card-section>
            </q-card>
          </div>
        </div>

        <!-- Work Value Section -->
        <q-card flat bordered class="q-mb-lg">
          <q-card-section>
            <div class="text-h5 text-weight-bold q-mb-md">Valor de la Hora Trabajada</div>
            <div class="row q-gutter-md">
              <div class="col-12 col-md-6">
                <q-input
                  v-model.number="hourlyRate"
                  type="number"
                  label="Tarifa por hora ($)"
                  outlined
                  dense
                />
              </div>
              <div class="col-12 col-md-6">
                <q-input
                  v-model.number="hoursWorked"
                  type="number"
                  label="Horas trabajadas"
                  outlined
                  dense
                />
              </div>
              <div class="col-12">
                <q-btn
                  @click="addWorkEntry"
                  color="positive"
                  label="Calcular Valor"
                  class="full-width"
                />
              </div>
            </div>
          </q-card-section>
        </q-card>

        <!-- Expenses Section -->
        <q-card flat bordered class="q-mb-lg">
          <q-card-section>
            <div class="text-h5 text-weight-bold q-mb-md">Gastos</div>
            <div class="row q-gutter-md q-mb-md">
              <div class="col-12 col-md-4">
                <q-input
                  v-model="newExpense.description"
                  label="Descripción"
                  outlined
                  dense
                />
              </div>
              <div class="col-12 col-md-4">
                <q-input
                  v-model.number="newExpense.amount"
                  type="number"
                  label="Monto ($)"
                  outlined
                  dense
                />
              </div>
              <div class="col-12 col-md-4">
                <q-btn
                  @click="addExpense"
                  color="negative"
                  label="Agregar Gasto"
                  class="full-width"
                />
              </div>
            </div>
            
            <q-table
              :columns="expenseColumns"
              :rows="expenses"
              row-key="description"
              flat
              bordered
              :pagination="{ rowsPerPage: 0 }"
            >
              <template v-slot:body-cell-amount="props">
                <q-td :props="props">
                  <div class="text-negative text-weight-bold">${{ props.value }}</div>
                </q-td>
              </template>
              
              <template v-slot:body-cell-actions="props">
                <q-td :props="props">
                  <div class="q-gutter-xs">
                    <q-btn
                      @click="editExpense(props.rowIndex)"
                      icon="edit"
                      color="primary"
                      flat
                      round
                      dense
                      size="sm"
                    />
                    <q-btn
                      @click="deleteExpense(props.rowIndex)"
                      icon="delete"
                      color="negative"
                      flat
                      round
                      dense
                      size="sm"
                    />
                  </div>
                </q-td>
              </template>
              
              <template v-slot:no-data>
                <div class="full-width row flex-center text-grey q-gutter-sm">
                  <q-icon size="2em" name="sentiment_dissatisfied" />
                  <span>No hay gastos registrados</span>
                </div>
              </template>
            </q-table>
          </q-card-section>
        </q-card>

        <!-- Work Log Section -->
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h5 text-weight-bold q-mb-md">Registro de Trabajo</div>
            
            <q-table
              :columns="workColumns"
              :rows="workEntries"
              row-key="date"
              flat
              bordered
              :pagination="{ rowsPerPage: 0 }"
            >
              <template v-slot:body-cell-total="props">
                <q-td :props="props">
                  <div class="text-positive text-weight-bold">${{ props.value }}</div>
                </q-td>
              </template>
              
              <template v-slot:no-data>
                <div class="full-width row flex-center text-grey q-gutter-sm">
                  <q-icon size="2em" name="work_off" />
                  <span>No hay registros de trabajo</span>
                </div>
              </template>
            </q-table>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Modal -->
    <q-dialog v-model="showModal" persistent>
      <q-card style="min-width: 400px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Agregar Entrada de Trabajo</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="showModal = false" />
        </q-card-section>

        <q-card-section>
          <q-form class="q-gutter-md">
            <q-input
              v-model="newWorkEntry.date"
              type="date"
              label="Fecha"
              outlined
              dense
            />
            
            <q-input
              v-model.number="newWorkEntry.hours"
              type="number"
              label="Horas trabajadas"
              outlined
              dense
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="q-gutter-sm">
          <q-btn
            @click="showModal = false"
            label="Cancelar"
            color="grey"
            outline
          />
          <q-btn
            @click="saveWorkEntry"
            label="Guardar"
            color="positive"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>

// TODO : BUG Y HOVER PARA LOS DATOS 
export default {
  name: 'PresupuestoView',
  props: {
    content: {
      type: String,
      default: '{}'
    }
  },
  data() {
    return {
      hourlyRate: 0,
      hoursWorked: 0,
      newExpense: { description: '', amount: 0 },
      expenses: [],
      workEntries: [],
      showModal: false,
      newWorkEntry: { date: '', hours: 0 },
      
      // Configuración de columnas para las tablas
      expenseColumns: [
        { name: 'description', required: true, label: 'Descripción', align: 'left', field: 'description', sortable: true },
        { name: 'amount', required: true, label: 'Monto', align: 'right', field: 'amount', sortable: true },
        { name: 'actions', required: true, label: 'Acciones', align: 'center' }
      ],
      
      workColumns: [
        { name: 'date', required: true, label: 'Fecha', align: 'left', field: 'date', sortable: true, format: val => this.formatDate(val) },
        { name: 'hours', required: true, label: 'Horas', align: 'center', field: 'hours', sortable: true },
        { name: 'total', required: true, label: 'Total', align: 'right', field: 'total', sortable: true }
      ]
    };
  },
  computed: {
    totalIncome() {
      return this.workEntries.reduce((acc, entry) => acc + entry.total, 0);
    },
    totalExpenses() {
      return this.expenses.reduce((acc, expense) => acc + expense.amount, 0);
    },
    balance() {
      return this.totalIncome - this.totalExpenses;
    },
  },
  watch: {
    content: {
      immediate: true,
      handler(newContent) {
        try {
          const parsed = newContent ? JSON.parse(newContent) : {};
          this.hourlyRate = parsed.hourlyRate || 0;
          this.expenses = parsed.expenses || [];
          this.workEntries = parsed.workEntries || [];
        } catch (e) {
          console.error('Error parsing content:', e);
        }
      }
    }
  },
  methods: {
    addExpense() {
      if (this.newExpense.description && this.newExpense.amount > 0) {
        this.expenses.push({ ...this.newExpense });
        this.newExpense = { description: '', amount: 0 };
        this.saveChanges();
      }
    },
    addWorkEntry() {
      if (this.hourlyRate > 0 && this.hoursWorked > 0) {
        this.showModal = true;
        this.newWorkEntry = { date: new Date().toISOString().slice(0, 10), hours: this.hoursWorked };
      }
    },
    saveWorkEntry() {
      const total = this.hourlyRate * this.newWorkEntry.hours;
      this.workEntries.push({
        date: this.newWorkEntry.date,
        hours: this.newWorkEntry.hours,
        total
      });
      this.showModal = false;
      this.newWorkEntry = { date: '', hours: 0 };
      this.hoursWorked = 0;
      this.saveChanges();
    },
    editExpense(index) {
      this.newExpense = { ...this.expenses[index] };
      this.deleteExpense(index);
    },
    deleteExpense(index) {
      this.expenses.splice(index, 1);
      this.saveChanges();
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('es-ES');
    },
    saveChanges() {
      const newData = {
        hourlyRate: this.hourlyRate,
        expenses: this.expenses,
        workEntries: this.workEntries
      };
      this.$emit('update:content', JSON.stringify(newData));
    }
  }
};
</script>

<style scoped>
/* Estilos específicos si son necesarios */
</style>