<template>
  <MainLayout>
    <SidebarFilter
        class="sm:p-12"
        title="Modele paneli"
        :filters="panelFilters"
        @apply-filters="handleApplyFilters"
    >
      <template #actions>


        <div class="py-4 flex justify-end gap-2">
          <!-- Clear all filters button -->
          <CustomButton
              @click="clearAllFilters"
              label="Wyczyść"
              variant="outline"
          />

          <!-- Apply filters button -->
          <CustomButton
              @click="emit('close-mobile-filter-panel')"
              label="Zastosuj"
          />
        </div>
      </template>

      <template #default>
        <AbstractTable
            v-if="panels.data && panels.data.length > 0"
            :data="panels.data"
            :columns="columns"
            :sortable="true"
            :pagination="pagination"
            :items-per-page-options="[10, 20, 30, 50]"
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
            <div class="text-sm font-medium text-gray-900">
              {{ data.manufacturer || 'Brak danych' }}
            </div>
          </template>

          <!-- Kolumna: Moc -->
          <template #power_watt="{ data }">
            <span class="px-2 inline-flex text-xs leading-5 rounded-full bg-green-100 text-gray-800">{{
                data.power_watt
              }} W</span>
          </template>

          <!-- Kolumna: Akcje -->
          <template #actions="{ data }">
            <div class="flex">
              <PencilSquareIcon
                  @click="setAction('edit', data)"
                  class="h-6 w-6 cursor-pointer text-gray-400 m-1"
              />
              <TrashIcon
                  @click="setAction('delete', data)"
                  class="h-6 w-6 cursor-pointer text-red-500 m-1"
              />
            </div>
          </template>

          <!-- Header Action -->
          <template #header-action>
            <button
                @click="() => isAddPanelModalVisible = !isAddPanelModalVisible"
                type="button"
                class="cursor-pointer  bg-green-500 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-green-400"
            >
              Dodaj panel
            </button>
          </template>
        </AbstractTable>

        <div v-else class="w-full h-full flex justify-center items-center">
          <InformationCircleIcon class="w-8 h-8 text-gray-300 m-2"/>
          Brak danych
        </div>
      </template>
    </SidebarFilter>
    <AddPanelModal :isOpen="isAddPanelModalVisible" @close="handleCloseOfAddPanelModal"></AddPanelModal>
    <UpdatePanelModal :isOpen="false"></UpdatePanelModal>
    <DeletePanelModelModal
        :isOpen="isDeletePanelModalVisible"
        :panelId="panelToDelete"
        @close="isDeletePanelModalVisible = false"
        @deleted="reloadPanelsData"
    />
  </MainLayout>

</template>

<script setup lang="ts">
import {ref, onMounted} from "vue";
import AbstractTable from "@/Shared/tabels/AbstractTable.vue";
import SidebarFilter from "@/Shared/abstract/FilterContainer.vue";
import Pagination from "@/Shared/pagination/Pagination.vue";
import {TrashIcon, PencilSquareIcon, InformationCircleIcon} from "@heroicons/vue/24/solid";
import {router} from "@inertiajs/vue3";
import {route} from "ziggy-js";
import {useEventable} from "@/Shared/utilities/eventBus";
import Navbar from "@/Components/Navbar.vue";
import MainLayout from "@/Shared/Layouts/MainLayout.vue";
import AddPanelModal from "@/Components/Panels/Modals/AddPanelModal.vue";
import UpdatePanelModal from "@/Components/Panels/Modals/UpdatePanelModal.vue";
import DeletePanelModelModal from "@/Components/Panels/Modals/DeletePanelModelModal.vue";
import CustomButton from "@/Shared/forms/buttons/CustomButton.vue";

const {emit} = useEventable();

// Props
const props = defineProps<{
  panels: {
    data: { id: number; name: string; manufacturer: string; power_watt: number }[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
  };
  filters: {
    manufacturers: { label: string; value: string; checked: boolean }[];
  };
}>();

// Reactive state
const panels = ref(props.panels ?? {data: [], current_page: 1, last_page: 1, per_page: 10, total: 0});
const pagination = ref({
  current_page: props.panels?.current_page ?? 1,
  last_page: props.panels?.last_page ?? 1,
  per_page: props.panels?.per_page ?? 1,
  total: props.panels?.total ?? 0,
  from: props.panels?.from,
  to: props.panels?.to,
});
const sortBy = ref("name");
const sortDirection = ref<"asc" | "desc">("asc");
const panelFilters = ref([
  {
    id: "manufacturer",
    name: "Producent",
    type: "checkbox",
    options: props.filters.manufacturers || [],
  },
  {
    id: "power_range",
    name: "Moc panelu (Wp)",
    type: "range",
    value: {from: 100, to: 1000}, // default range
    min: 100,
    max: 1000,
    unit: " W",
  },
]);

const urlParams = ref<any>({});

// Columns
const columns = [
  {title: "Nazwa", key: "name"},
  {title: "Producent", key: "manufacturer"},
  {title: "Moc [W]", key: "power_watt"},
];

// Sorting
const handleSort = (column: string) => {
  if (sortBy.value === column) sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  else sortBy.value = column;
  reloadPanelsData();
};

// Actions
const setAction = (action: string, panel: any) => {
  if (action === "delete") {
    panelToDelete.value = panel.id;
    isDeletePanelModalVisible.value = true;
  } else if (action === "add") {
    isAddPanelModalVisible.value = true;
  } else if (action === "edit") {
    emit("show-update-panel-model-modal", panel);
  }
};

// Delete API
const deletePanel = (panelId: number) => {
  router.delete(route("panels.destroy", {panel: panelId}), {onSuccess: () => reloadPanelsData()});
};

// Reload panels
const reloadPanelsData = () => {
  const queryParams = {
    ...urlParams.value,
    sort_by: sortBy.value,
    sort_direction: sortDirection.value,
    page: pagination.value.current_page,
    per_page: pagination.value.per_page,
  };
  router.visit(route("panels.index"), {
    method: "get",
    data: queryParams,
    preserveState: true,
    preserveScroll: true,
    onSuccess: (pageData) => {
      panels.value = pageData.props.panels ?? panels.value;
      pagination.value = {
        current_page: pageData.props.panels?.current_page ?? 1,
        last_page: pageData.props.panels?.last_page ?? 1,
        per_page: pageData.props.panels?.per_page ?? 10,
        total: pageData.props.panels?.total ?? 0,
        from: pageData.props.panels?.from ?? 0,
        to: pageData.props.panels?.to ?? 0,
      };
    },
  });
};

// Filters
const handleApplyFilters = (appliedFilters: any) => {
  const queryParams: any = {};

  // Safely handle undefined/null
  if (appliedFilters && Array.isArray(appliedFilters)) {
    appliedFilters.forEach((filter: any) => {
      if (filter.id === "manufacturer") {
        filter.options.forEach((opt: any, i: number) => {
          if (opt.checked) queryParams[`manufacturer[${i}]`] = opt.value;
        });
      }

      if (filter.id === "power_range" && filter.range) {
        if (filter.range.from != null) queryParams["power_range[from]"] = filter.range.from;
        if (filter.range.to != null) queryParams["power_range[to]"] = filter.range.to;
      }
    });
  }

  urlParams.value = queryParams;
  reloadPanelsData();
};


// Pagination / Sorting Handlers
const {watchEvent} = useEventable();

const updatePage = (data: { page: number }) => {
  console.log("Page changed:", data.page);
  pagination.value.current_page = data.page;
  reloadPanelsData();
};

const updateItemsPerPage = (data: { items: number }) => {
  console.log("Items per page changed:", data.items);
  pagination.value.per_page = data.items;
  pagination.value.current_page = 1;
  reloadPanelsData();
};

const updateSorting = (data: { sort_by: string; sort_direction: string }) => {
  console.log("Sorting changed:", data.sort_by, data.sort_direction);
  sortBy.value = data.sort_by;
  sortDirection.value = data.sort_direction;
  reloadPanelsData();
};

onMounted(() => {
  watchEvent("page-changed", updatePage);
  watchEvent("items-per-page-changed", updateItemsPerPage);
  watchEvent("sorting-changed", updateSorting);
});

const isAddPanelModalVisible = ref(false);

function handleCloseOfAddPanelModal() {
  isAddPanelModalVisible.value = false;
}

const isDeletePanelModalVisible = ref(false);
const panelToDelete = ref<number | null>(null);

function clearAllFilters()
{
  emit('clear-all-filters');
  router.visit(route('panels.index'), {
    method: 'get',
    data: {}, // no filters
    preserveState: false,
    preserveScroll: true,
  });
}
</script>
