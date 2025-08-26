<script setup lang="ts">
import {ref, nextTick, onMounted} from 'vue';
import AbstractTable from "@/Shared/tabels/AbstractTable.vue";
import {useEventable} from "@/Shared/utilities/eventBus";

// Table reactive state
const tableData = ref([]);
const tableColumns = ref([]);
const tableTitle = ref('Oferta szczegółowa');
const tableDescription = ref('Szczegóły pozycji instalacji');

// Helper for formatting PLN prices
const formatPrice = (value: number) => {
  if (!value && value !== 0) return '-';
  return new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'PLN' }).format(value);
};

// Listen for invoice updates
const { emit, watchEvent } = useEventable();


// Default columns if none provided
if (tableColumns.value.length === 0) {
  tableColumns.value = [
    { title: 'Pozycja', key: 'name' },
    { title: 'Ilość', key: 'quantity' },
    { title: 'Cena netto', key: 'unit_price' },
    { title: 'VAT', key: 'vat' },
    { title: 'Cena brutto', key: 'price_with_vat' },
    { title: 'Suma netto', key: 'total' },
    { title: 'Suma brutto', key: 'total_with_vat' },
  ];
}

onMounted(()=> {
  //emit('show-logo-loader');
  watchEvent('updateInvoice',  (payload) => {

    // Ensure reactivity triggers
    nextTick();
    tableData.value = payload.data || [];
    console.log(tableData.value)
    tableColumns.value = payload.columns || [];
    tableTitle.value = payload.title || 'Faktura szczegółowa';
    tableDescription.value = payload.description || 'Szczegóły pozycji instalacji';
    console.log('Invoice updated:', tableData.value);

    // setTimeout(()=> {
    //   emit('hide-logo-loader');
    // },1000);

  });
})
</script>

<template>
  <AbstractTable
      :data="tableData"
      :columns="tableColumns"
      :title="tableTitle"
      :description="tableDescription"
      :sortable="true"
  >
    <!-- Custom slot for formatted prices -->
    <template #unit_price="{ data }">
      <div class="text-sm font-medium text-gray-900">{{ formatPrice(data.unit_price) }}</div>
    </template>

    <template #price_with_vat="{ data }">
      <div class="text-sm font-medium text-gray-900">{{ formatPrice(data.price_with_vat) }}</div>
    </template>

    <template #total="{ data }">
      <div class="text-sm font-semibold text-gray-900">{{ formatPrice(data.total) }}</div>
    </template>

    <template #total_with_vat="{ data }">
      <div class="text-sm font-semibold text-gray-900">{{ formatPrice(data.total_with_vat) }}</div>
    </template>

    <template #vat="{ data }">
      <div class="text-sm text-gray-600">{{ data.vat }}</div>
    </template>
  </AbstractTable>
</template>
