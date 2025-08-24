<template>
 <pre>
   {{props}}
 </pre>

  <SidebarFilter
      title="Panele - widok tabeli"
      :filters="panel_filters"
      @apply-filters="handleApplyFilters"
  >
    <template #default>
      <AbstractTable
          v-if="panels.length > 0"
          :data="panels"
          :columns="columns"
          :sortable="true"
          :pagination="pagination"
          title="Lista paneli fotowoltaicznych"
          description="Lista wszystkich modeli paneli fotowoltaicznych zawierająca nazwę, producenta oraz moc w Wp."
          @sort="handleSort"
      >
        <!-- Kolumna: Nazwa -->
        <template #name="{ data }">
          <div class="text-sm font-medium text-gray-900">
            {{ data.name }}
          </div>
        </template>

        <!-- Kolumna: Producent -->
        <template #manufacturer="{ data }">
          <span class="px-2 inline-flex text-xs leading-5 rounded-full bg-gray-100 text-gray-800">
            {{ data.manufacturer || 'Brak danych' }}
          </span>
        </template>

        <!-- Kolumna: Moc -->
        <template #power_watt="{ data }">
          <span class="font-semibold text-gray-700">{{ data.power_watt }} Wp</span>
        </template>

        <!-- Kolumna: Akcje -->
        <template #actions="{ data }">
          <div class="flex">
            <PencilSquareIcon
                @click="setAction('edit', data)"
                class="h-6 w-6 cursor-pointer text-gray-400 m-1"
                aria-hidden="true"
            />
            <TrashIcon
                @click="setAction('delete', data)"
                class="h-6 w-6 cursor-pointer text-red-500 m-1"
                aria-hidden="true"
            />
          </div>
        </template>

        <!-- Header Action -->
        <template #header-action>
          <button
              @click="setAction('add', {})"
              type="button"
              class="bg-yellow-500 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-yellow-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900"
          >
            Dodaj panel
          </button>
        </template>
      </AbstractTable>

      <!-- Brak danych -->
      <div
          class="w-full h-full flex justify-center items-center"
          v-else="panels.length === 0"
      >
        <InformationCircleIcon class="w-8 h-8 text-gray-300 m-2" aria-hidden="true" />
        Brak danych
      </div>
    </template>
  </SidebarFilter>
</template>

<script setup lang="ts">
import { ref } from "vue";
import AbstractTable from "@/Shared/tabels/AbstractTable.vue";
import SidebarFilter from "@/Shared/abstract/FilterContainer.vue";
import {
  TrashIcon,
  PencilSquareIcon,
  InformationCircleIcon,
} from "@heroicons/vue/24/solid";

// ✅ Tymczasowy mockup danych (zamiast danych z controllera)
const panels = ref([
  { id: 1, name: "LONGi 450W", manufacturer: "LONGi", power_watt: 450 },
  { id: 2, name: "JA Solar 400W", manufacturer: "JA Solar", power_watt: 400 },
  { id: 3, name: "Trina 500W", manufacturer: "Trina", power_watt: 500 },
  { id: 4, name: "Risen 550W", manufacturer: "Risen", power_watt: 550 },
]);

// ✅ Tymczasowa paginacja (mock)
const pagination = ref({
  current_page: 1,
  items_per_page: 10,
  total: panels.value.length,
});

// ✅ Definicja kolumn
const columns = [
  { title: "Nazwa", key: "name" },
  { title: "Producent", key: "manufacturer" },
  { title: "Moc [Wp]", key: "power_watt" },
  { title: "Akcje", key: "actions", sortable: false },
];

// ✅ Mock filtrów
const panel_filters = ref([
  {
    id: "manufacturer",
    name: "Producent",
    type: "checkbox",
    options: [
      { label: "LONGi", value: "LONGi", checked: false },
      { label: "JA Solar", value: "JA Solar", checked: false },
      { label: "Trina", value: "Trina", checked: false },
      { label: "Risen", value: "Risen", checked: false },
    ],
  },
  {
    id: "power_range",
    name: "Zakres mocy",
    type: "radio",
    selected: null,
    options: [
      { label: "do 400 Wp", value: "under400" },
      { label: "401 - 500 Wp", value: "400to500" },
      { label: "powyżej 500 Wp", value: "above500" },
    ],
  },
]);

// ✅ Obsługa sortowania
const sortBy = ref("name");
const sortDirection = ref("asc");

const handleSort = (column: string) => {
  if (sortBy.value === column) {
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    sortBy.value = column;
    sortDirection.value = "asc";
  }
  console.log("Sortowanie:", sortBy.value, sortDirection.value);
};

// ✅ Obsługa akcji
const setAction = (action: string, panel: any) => {
  if (action === "delete") {
    panels.value = panels.value.filter((p) => p.id !== panel.id);
  } else if (action === "add") {
    console.log("Dodawanie nowego panelu...");
  } else if (action === "edit") {
    console.log("Edycja panelu:", panel);
  }
};

// ✅ Obsługa filtrów
const handleApplyFilters = (appliedFilters: any) => {
  console.log("Filtry zastosowane:", appliedFilters);
};
</script>
