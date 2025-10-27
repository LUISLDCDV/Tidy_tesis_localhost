<template>
  <div class="rounded-xl border border-[#d0e7d0] bg-[#f8fcf8] overflow-hidden">
    <table class="w-full">
      <thead>
        <tr class="bg-[#f8fcf8]">
          <th class="table-column-120 px-4 py-3 text-left text-[#0e1b0e] text-sm font-medium">Título</th>
          <th class="table-column-240 px-4 py-3 text-left text-[#0e1b0e] text-sm font-medium">Fecha</th>
          <th class="table-column-360 px-4 py-3 text-left text-[#0e1b0e] text-sm font-medium">Monto</th>
          <th class="table-column-480 px-4 py-3 text-left text-[#0e1b0e] text-sm font-medium">Estado</th>
          <th class="px-4 py-3 text-left text-[#0e1b0e] text-sm font-medium">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr 
          v-for="(bill, index) in bills" 
          :key="bill.id" 
          class="border-t border-[#d0e7d0]"
        >
          <td class="table-column-120 px-4 py-2 text-[#0e1b0e] text-sm">
            {{ bill.name }}
            <span v-if="bill.installmentInfo" class="text-xs text-gray-500 ml-2">
              ({{ bill.installmentInfo }})
            </span>
          </td>
          <td class="table-column-240 px-4 py-2 text-[#4e974e] text-sm">{{ bill.dueDate }}</td>
          <td class="table-column-360 px-4 py-2 text-[#4e974e] text-sm">{{ bill.amount }}</td>
          <td class="table-column-480 px-4 py-2">
            <button 
              class="w-full h-8 rounded-xl bg-[#e7f3e7] text-[#0e1b0e] text-sm font-medium"
              @click="$emit('update-status', index)"
            >
              {{ bill.status }}
            </button>
          </td>
          <td class="px-4 py-2">
            <button 
              @click="$emit('edit-bill', { bill, index })" 
              class="text-blue-600 hover:underline mr-2"
            >
              Editar
            </button>
            <button 
              @click="$emit('delete-bill', { billIndex: index })" 
              class="text-red-600 hover:underline"
            >
              Eliminar
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  month: String,
  bills: Array
});
</script>