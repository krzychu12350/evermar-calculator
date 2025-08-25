<template>
  <MainLayout>
    <SidebarFilter
        class="sm:p-12"
        title="Warianty paneli"
        :filters="variantFilters"
        :is_mode_toogling="false"
        @apply-filters="handleApplyFilters"
    >
      <template #actions>
        <div class="py-4 flex justify-end gap-2">
          <CustomButton @click="clearAllFilters" label="Wyczyść" variant="outline"/>
          <CustomButton @click="emit('close-mobile-filter-panel')" label="Zastosuj"/>
        </div>
      </template>

      <template #default>
        <AbstractTable
            v-if="variants.data.length > 0"
            :data="variants.data"
            :columns="columns"
            :sortable="true"
            :pagination="pagination"
            :items-per-page-options="[10,20,30,50]"
            title="Lista wariantów"
            description="Lista wszystkich wariantów paneli z modelami, typami instalacji i magazynami."
            @sort="handleSort"
        >
          <template #panel_count="{ data }">
            <div class="text-sm font-medium text-gray-900">{{ data.panel_count }}</div>
          </template>

          <!-- Kolumna: Modele -->
          <template #models="{ data }">
            <div class="flex flex-wrap gap-1">
              <span
                  v-for="model in [...new Map(data.models?.map(m => [m.name, m])).values()]"
                  :key="model.id"
                  class="px-2 inline-flex text-xs leading-5 rounded-full bg-green-100 text-gray-800"
              >
                {{ model.name }}
              </span>
              <span v-if="!data.models || data.models.length === 0" class="text-gray-400">Brak danych</span>
            </div>
          </template>

          <!-- Kolumna: Typy instalacji -->
          <template #install_types="{ data }">
            <div class="flex flex-wrap gap-1">
              <span
                  v-for="(type, index) in [...new Set(data.install_types || [])]"
                  :key="index"
                  class="px-2 inline-flex text-xs leading-5 rounded-full bg-blue-100 text-gray-800"
              >
                {{ type }}
              </span>
              <span v-if="!data.install_types || data.install_types.length === 0"
                    class="text-gray-400">Brak danych</span>
            </div>
          </template>

          <!-- Kolumna: Magazyny -->
          <template #storages="{ data }">
            <div class="flex flex-wrap gap-1">
              <span
                  v-for="(storage, index) in [...new Set(data.storages || [])]"
                  :key="index"
                  class="px-2 inline-flex text-xs leading-5 rounded-full bg-yellow-100 text-gray-800"
              >
                {{ storage }}
              </span>
              <span v-if="!data.storages || data.storages.length === 0" class="text-gray-400">Brak danych</span>
            </div>
          </template>


          <template #actions="{ data }">
            <div class="flex">
              <PencilSquareIcon @click="setAction('edit', data)" class="h-6 w-6 cursor-pointer text-gray-400 m-1"/>
              <TrashIcon @click="setAction('delete', data)" class="h-6 w-6 cursor-pointer text-red-500 m-1"/>
            </div>
          </template>

          <template #header-action>
            <button
                @click="isAddVariantModalVisible = true"
                class="cursor-pointer bg-green-500 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-green-400"
            >
              Dodaj wariant
            </button>
          </template>
        </AbstractTable>

        <div v-else class="w-full h-full flex justify-center items-center">
          <InformationCircleIcon class="w-8 h-8 text-gray-300 m-2"/>
          Brak danych
        </div>
      </template>
    </SidebarFilter>

    <!-- Modals -->
    <AddVariantModal
        :isOpen="isAddVariantModalVisible"
        @close="handleCloseAddVariantModal"
        @confirmed="reloadVariantsData"
    />
    <UpdateVariantModal
        :isOpen="isUpdateVariantModalVisible"
        :variant="variantToEdit"
        @close="handleCloseUpdateVariantModal"
        @confirmed="reloadVariantsData"
    />
    <DeleteVariantModal
        :isOpen="isDeleteVariantModalVisible"
        :variantId="variantToDelete"
        @close="isDeleteVariantModalVisible = false"
        @deleted="reloadVariantsData"
    />
  </MainLayout>
<!--  <pre>-->
<!--      {{ props.variants }}-->
<!--  </pre>-->

</template>

<script setup lang="ts">
import {ref, onMounted} from 'vue';
import {router} from '@inertiajs/vue3';
import {route} from 'ziggy-js';
import {useEventable} from '@/Shared/utilities/eventBus';
import AbstractTable from '@/Shared/tabels/AbstractTable.vue';
import SidebarFilter from '@/Shared/abstract/FilterContainer.vue';
import MainLayout from '@/Shared/Layouts/MainLayout.vue';
import AddVariantModal from '@/Components/Variants/Modals/AddVariantModal.vue';
import UpdateVariantModal from '@/Components/Variants/Modals/UpdateVariantModal.vue';
import DeleteVariantModal from '@/Components/Variants/Modals/DeleteVariantModal.vue';
import CustomButton from '@/Shared/forms/buttons/CustomButton.vue';
import {PencilSquareIcon, TrashIcon, InformationCircleIcon} from '@heroicons/vue/24/solid';
import RangeInput from "@/Shared/forms/inputs/RangeInput.vue";

const {emit, watchEvent} = useEventable();

interface PanelModel {
  name: string;
}

interface Variant {
  id: number;
  panel_count: number;
  models?: PanelModel[];
  install_types?: string[];
  storages?: string[];
}

interface FilterOption {
  label: string;
  value: string;
  checked: boolean;
}

interface Paginated<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

const props = defineProps<{
  variants: Paginated<Variant>;
  filters: { install_types: FilterOption[] };
}>();

// Reactive State
const variants = ref<Paginated<Variant>>(props.variants ?? {
  data: [],
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0
});
const panelCountFilter = ref({ from: 1, to: 1000 }); // default range
const pagination = ref({...variants.value});
const sortBy = ref<'panel_count' | 'models' | 'install_types' | 'storages'>('panel_count');
const sortDirection = ref<'asc' | 'desc'>('asc');
const variantFilters = ref([
  {
    id: 'install_type',
    name: 'Typ instalacji',
    type: 'checkbox',
    options: props.filters.install_types ?? []
  },
  {
    id: 'panel_models',
    name: 'Modele paneli',
    type: 'checkbox',
    options: props.filters.panel_models ?? []
  },
  {
    id: 'storage_variants',
    name: 'Warianty magazynów',
    type: 'checkbox',
    options: props.filters.storage_variants?.map(sv => ({
      label: `${sv.value} kWh`,
      value: sv.value,
      checked: false
    })) ?? []
  },
  {
    id: "panel_count",
    name: "Liczba paneli",
    type: "range",
    value: panelCountFilter.value,
    min: 1,
    max: 1000,
  },
]);
const urlParams = ref<Record<string, any>>({});

// Columns
const columns = [
  {title: 'Liczba paneli', key: 'panel_count'},
  {title: 'Modele', key: 'models'},
  {title: 'Typy instalacji', key: 'install_types'},
  {title: 'Magazyny', key: 'storages'},
];

// Modals
const isAddVariantModalVisible = ref(false);
const isUpdateVariantModalVisible = ref(false);
const isDeleteVariantModalVisible = ref(false);
const variantToEdit = ref<Variant | null>(null);
const variantToDelete = ref<number | null>(null);

// Sorting
const handleSort = (column: string) => {
  if (sortBy.value === column) sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  else sortBy.value = column as any;
  reloadVariantsData();
};

// Actions
const setAction = (action: 'add' | 'edit' | 'delete', variant: Variant) => {
  if (action === 'delete') {
    variantToDelete.value = variant.id;
    isDeleteVariantModalVisible.value = true;
  } else if (action === 'edit') {
    variantToEdit.value = variant;
    isUpdateVariantModalVisible.value = true;
  } else if (action === 'add') {
    isAddVariantModalVisible.value = true;
  }
};

// Reload variants
const reloadVariantsData = () => {
  const queryParams = {
    ...urlParams.value,
    sort_by: sortBy.value,
    sort_direction: sortDirection.value,
    page: pagination.value.current_page,
    per_page: pagination.value.per_page,
  };
  router.visit(route('variants.index'), {
    method: 'get',
    data: queryParams,
    preserveState: true,
    preserveScroll: true,
    onSuccess: (pageData) => {
      const newData = pageData.props.variants as Paginated<Variant>;
      variants.value = newData ?? variants.value;
      pagination.value = {...variants.value};
    },
  });
};

// Update handleApplyFilters
const handleApplyFilters = (appliedFilters: any) => {
  const queryParams: Record<string, any> = {};

  appliedFilters.forEach((filter: any) => {
    // Handle range filters
    if (filter.range) {
      queryParams[`${filter.id}_from`] = filter.range.from;
      queryParams[`${filter.id}_to`] = filter.range.to;
      return;
    }

    filter.options?.forEach((opt: FilterOption, i: number) => {
      if (opt.checked) {
        switch (filter.id) {
          case 'install_type':
            queryParams[`install_type[${i}]`] = opt.value;
            break;
          case 'panel_models':
            queryParams[`panel_models[${i}]`] = opt.value;
            break;
          case 'storage_variants':
            queryParams[`storage_variants[${i}]`] = opt.value;
            break;
        }
      }
    });
  });

  urlParams.value = queryParams;
  console.log(urlParams.value)
  reloadVariantsData();
};

// Modal Handlers
function handleCloseAddVariantModal() {
  isAddVariantModalVisible.value = false;
  reloadVariantsData();
}

function handleCloseUpdateVariantModal() {
  isUpdateVariantModalVisible.value = false;
  reloadVariantsData();
}

// Pagination / Sorting Event Handlers
const updatePage = (data: { page: number }) => {
  pagination.value.current_page = data.page;
  reloadVariantsData();
};
const updateItemsPerPage = (data: { items: number }) => {
  pagination.value.per_page = data.items;
  pagination.value.current_page = 1;
  reloadVariantsData();
};
const updateSorting = (data: { sort_by: string; sort_direction: string }) => {
  sortBy.value = data.sort_by as any;
  sortDirection.value = data.sort_direction as 'asc' | 'desc';
  reloadVariantsData();
};

onMounted(() => {
  watchEvent('page-changed', updatePage);
  watchEvent('items-per-page-changed', updateItemsPerPage);
  watchEvent('sorting-changed', updateSorting);
  watchEvent('reload-variants', reloadVariantsData);
});

// Clear Filters
function clearAllFilters() {
  emit('clear-all-filters');
  urlParams.value = {};
  reloadVariantsData();
}
</script>
