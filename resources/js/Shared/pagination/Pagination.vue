<template>
  <!-- <div>{{ pagination }}</div> -->
  <div
    class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6"
  >
    <!-- Pagination Info -->
    <div class="flex items-center">
      <p class="text-sm text-gray-700">
        Rekordy od
        <span class="font-medium">{{ startItem }}</span>
        do
        <span class="font-medium">{{ endItem }}</span>
        z
        <span class="font-medium">{{ totalItems }}</span>
        wyników
      </p>
    </div>

    <!-- Items Per Page Selector -->
    <div class="flex items-center space-x-4">
      <div class="flex items-center">
        <span class="mr-3 text-sm text-gray-700">Rekordy na strone:</span>
        <select
            v-model="itemsPerPage"
            @change="updateItemsPerPage"
            class="cursor-pointer relative inline-flex items-center justify-center pr-8 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 text-center hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 rounded-md"
        >
          <option
              v-for="option in itemsPerPageOptions"
              :key="option"
              :value="option"
              class="text-center"
          >
            {{ option }}
          </option>
        </select>

      </div>

      <!-- Pagination Controls -->
      <nav
        class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
        aria-label="Pagination"
      >
        <button
          @click="previousPage"
          :disabled="currentPage === 1"
          class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
        >
          <span class="sr-only">Previous</span>
          <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
        </button>

        <span
          v-for="page in pages"
          :key="page"
          @click="goToPage(page)"
          :class="{
            'z-10 bg-green-50 border-green-500 text-green-600': page === currentPage,
            'bg-white border-gray-300 text-gray-500 hover:bg-gray-50':
              page !== currentPage,
          }"
          class="curosor-pointer relative inline-flex items-center px-4 py-2 border text-sm font-medium cursor-pointer"
        >
          {{ page }}
        </span>

        <button
          @click="nextPage"
          :disabled="currentPage === lastPage"
          class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
        >
          <span class="sr-only">Next</span>
          <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
        </button>
      </nav>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ChevronLeftIcon, ChevronRightIcon } from "@heroicons/vue/24/solid";
import { defineProps, defineEmits, ref, computed, watch, onMounted } from "vue";
import {useEventable} from "@/Shared/utilities/eventBus";

// Access event bus
const { emit } = useEventable();

// Define the props
const props = defineProps<{
  pagination: {
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
    per_page: number;
  };
  itemsPerPageOptions?: number[];
}>();

// Define emits
// const emit = defineEmits<{
//   (e: "page-changed", page: number): void;
//   (e: "items-per-page-changed", itemsPerPage: number): void;
// }>();

// Pagination and sorting state
const currentPage = computed(() => props.pagination.current_page);
const totalItems = computed(() => props.pagination.total);
const startItem = computed(() => props.pagination.from);
const endItem = computed(() => props.pagination.to);
const lastPage = computed(() => props.pagination.last_page);
// force number
const itemsPerPage = ref(Number(props.pagination.per_page));

// Methods to handle pagination
const updateItemsPerPage = () => {
  emit("items-per-page-changed", { items: itemsPerPage.value });
};

const previousPage = () => {
  if (currentPage.value > 1) {
    emit("page-changed", {
      page: currentPage.value - 1,
    });
  }
};

const nextPage = () => {
  if (currentPage.value < lastPage.value) {
    emit("page-changed", {
      page: currentPage.value + 1,
    });
  }
};

const goToPage = (page: number) => {
  if (page > 0 && page <= lastPage.value) {
    emit("page-changed", {
      page: page,
    });
  }
};

// Generate visible pages with dynamic range
const pages = computed(() => {
  const rangeLimit = 5; // Show up to 5 page links
  const start = Math.max(1, currentPage.value - Math.floor(rangeLimit / 2));
  const end = Math.min(lastPage.value, start + rangeLimit - 1);

  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

// Watch for changes to pagination and update accordingly
watch([itemsPerPage], () => {
  emit("update:data", {
    page: currentPage.value,
    items_per_page: itemsPerPage.value,
  });
});

onMounted(() => {
  const perPage = Number(props.pagination.per_page);
  const firstOption = props.itemsPerPageOptions?.at(0) ?? 10;
  itemsPerPage.value = props.itemsPerPageOptions?.includes(perPage)
      ? perPage
      : firstOption;
});
</script>
