<template>
  <div class="flex flex-col">
    <!-- Header Section: Title, Description, and Action Button -->
    <div class="mb-4 flex flex-col lg:flex-row lg:justify-between lg:items-center">
      <div class="text-left mb-4 lg:mb-0" v-if="title || description">
        <div class="mb-4">
          <h2 class="text-lg font-semibold text-gray-900 mb-2" v-if="title">
            {{ title }}
          </h2>
          <p class="text-sm text-gray-600" v-if="description">{{ description }}</p>
        </div>
      </div>
      <div v-if="$slots['header-action']">
        <slot name="header-action"></slot>
      </div>
    </div>

    <!-- Table Section -->
    <div class="overflow-x-auto -my-2 sm:-mx-2 lg:-mx-2">
      <!-- <div class="overflow-x-auto -my-2 sm:-mx-6 lg:-mx-8"> -->
      <!-- <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8"> -->
      <div class="py-2 align-middle inline-block min-w-full">
        <div class="overflow-hidden">
          <!-- <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg"> -->
          <table class="min-w-full divide-y divide-gray-200">
            <!-- <thead class="bg-gray-50"> -->
            <thead class="bg-white">
              <tr>
                <!-- Column Headers with Sorting -->
                <th
                  v-for="column in columns"
                  :key="column.key"
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                  @click="sortable && sortData(column.key)"
                >
                  <div class="flex items-center">
                    {{ column.title }}
                    <span class="inline-flex items-center">
                      <!-- Ascending Arrow -->
                      <svg
                        v-if="sortColumn !== column.key || sortDirection === 'ASC'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 inline-block ml-1 text-gray-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M5 15l7-7 7 7"
                        />
                      </svg>

                      <!-- Descending Arrow -->
                      <svg
                        v-if="sortColumn === column.key && sortDirection === 'DESC'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 inline-block ml-1 text-gray-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M19 9l-7 7-7-7"
                        />
                      </svg>
                    </span>
                  </div>
                </th>

                <th scope="col" class="relative px-6 py-3">
                  <span class="sr-only">Actions</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(item, index) in data" :key="index">
                <td
                  v-for="column in columns"
                  :key="column.key"
                  class="px-6 py-4 max-w-[600px] break-words"
                >
                  <slot :name="column.key" :data="item">
                    <div class="text-sm font-medium text-gray-900">
                      {{ item[column.key] }}
                    </div>
                  </slot>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <slot name="actions" :data="item"></slot>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination Component -->
          <Pagination
            :pagination="pagination"
            :currentPage="currentPage"
            :totalItems="totalItems"
            :itemsPerPage="itemsPerPage"
            :itemsPerPageOptions="itemsPerPageOptions"
            @page-changed="goToPage"
            @items-per-page-changed="updateItemsPerPage"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, defineProps, defineEmits, onMounted } from "vue";
import Pagination from "../pagination/Pagination.vue";
import { useEventable } from "@/Shared/utilities/eventBus"; // Import event bus utility

const isCalendarView = ref(false);

const { emit } = useEventable();

interface Column {
  title: string;
  key: string;
}

interface Props {
  data: Array<Record<string, any>>;
  columns: Array<Column>;
  pagination: Object;
  itemsPerPageOptions?: number[];
  title?: string;
  description?: string;
  sortable?: boolean;
}

const defaultItemsPerPageOptions = [1, 2, 20, 50];
const props = defineProps<Props>();
// const emit = defineEmits();

// Default props for sorting
const defaultProps = {
  sortable: true,
};
const mergedProps = { ...defaultProps, ...props };

// Pagination and sorting state
const itemsPerPageOptions = mergedProps.itemsPerPageOptions || defaultItemsPerPageOptions;
const currentPage = ref(1);
const itemsPerPage = ref(itemsPerPageOptions[0]);

// Sorting state
const sortColumn = ref<string | null>(null); // Currently sorted column
const sortDirection = ref<"ASC" | "DESC">("ASC"); // Sorting direction

const totalItems = computed(() => props.data.length); // Total number of items for pagination

// Watch for changes in pagination and sorting
watch([currentPage, itemsPerPage, sortColumn, sortDirection], () => {
  emit("update:data", {
    page: currentPage.value,
    items_per_page: itemsPerPage.value,
    sort_by: sortColumn.value,
    sort_direction: sortDirection.value,
  });
});

onMounted(() => {
  if (mergedProps.columns.length > 0 && mergedProps.sortable !== false) {
    sortColumn.value = mergedProps.columns[0].key;
    sortDirection.value = "asc";
  }
});

// Sorting logic
const sortData = (columnKey: string) => {
  if (mergedProps.sortable) {
    if (sortColumn.value === columnKey) {
      // Toggle the direction if the same column is clicked
      sortDirection.value = sortDirection.value === "ASC" ? "DESC" : "ASC";
    } else {
      // If a new column is clicked, reset the sort direction to DESC for a noticeable change
      sortColumn.value = columnKey;
      sortDirection.value = "DESC"; // Change to DESC first so that the user sees an effect immediately
    }
    applySort();
  }
};

// Pagination handlers
const goToPage = (page: number) => {
  currentPage.value = page;
  fetchData();
};

const updateItemsPerPage = (newItemsPerPage: number) => {
  itemsPerPage.value = newItemsPerPage;
  currentPage.value = 1;
  fetchData();
};

const fetchData = () => {
  console.log({
    page: currentPage.value,
    items_per_page: itemsPerPage.value,
    sort_by: sortColumn.value,
    sort_direction: sortDirection.value,
  });
  // emit("fetch-data", {
  //   page: currentPage.value,
  //   items_per_page: itemsPerPage.value,
  //   sort_by: sortColumn.value,
  //   sort_direction: sortDirection.value,
  // });
};

// Apply sorting to the data
const applySort = () => {
  const key = sortColumn.value;
  if (key && props.data) {
    props.data.sort((a, b) => {
      const valA = a[key] ?? "";
      const valB = b[key] ?? "";

      if (typeof valA === "string" && typeof valB === "string") {
        return sortDirection.value === "ASC"
          ? valA.localeCompare(valB)
          : valB.localeCompare(valA);
      }

      return sortDirection.value === "ASC"
        ? valA > valB
          ? 1
          : -1
        : valA < valB
        ? 1
        : -1;
    });
  }
};

onMounted(() => {
  if (mergedProps.columns.length > 0 && mergedProps.sortable !== false) {
    // Pre-sort data by all columns in ascending order
    props.columns.forEach((column) => {
      sortColumn.value = column.key; // Temporarily set column key
      sortDirection.value = "ASC"; // Default sorting direction
      applySort(); // Sort the data for each column
    });

    // Set the first column as the visible sorting column
    sortColumn.value = mergedProps.columns[0].key;
  }
});
</script>
