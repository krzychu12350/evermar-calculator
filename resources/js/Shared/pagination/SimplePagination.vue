<template>
  <nav
    v-if="lastPage !== 1"
    class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6"
    aria-label="Pagination"
  >
    <div class="hidden sm:block">
      <p class="text-sm text-gray-700">
        Rekordy od
        {{ " " }}
        <span class="font-medium">{{ startItem }}</span>
        {{ " " }}
        do
        {{ " " }}
        <span class="font-medium">{{ endItem }}</span>
        {{ " " }}
        z
        {{ " " }}
        <span class="font-medium">{{ totalItems }}</span>
        {{ " " }}
        wyników
      </p>
    </div>
    <div class="flex-1 flex justify-between sm:justify-end">
      <button
        @click="previousPage"
        :disabled="currentPage === 1"
        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
      >
        Poprzednie
      </button>
      <button
        @click="nextPage"
        :disabled="currentPage === lastPage"
        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
      >
        Następne
      </button>
    </div>
  </nav>
</template>
<script setup lang="ts">
import { defineProps, defineEmits, ref, computed, watch, onMounted } from "vue";
import { useEventable } from "../../../shared/utilities/eventBus";

// Access event bus
const { emit } = useEventable();

// Define the props
const props = defineProps<{
  pagination: {
    current_page: number;
    last_page: number;
    total: number;
    start_item: number;
    end_item: number;
    items_per_page: number;
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
const startItem = computed(() => props.pagination.start_item);
const endItem = computed(() => props.pagination.end_item);
const lastPage = computed(() => props.pagination.last_page);
const itemsPerPage = ref(props.pagination.items_per_page); // Initialize with pagination value

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
  // Ensure itemsPerPage reflects the initial value from the props
  itemsPerPage.value = props.pagination.items_per_page;
});
</script>
