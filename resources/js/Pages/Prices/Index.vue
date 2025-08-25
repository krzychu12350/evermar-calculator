<template>
  <MainLayout>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-semibold text-gray-900">Ustaw ceny wariantu</h1>

      <!-- Variant Select -->
      <div class="w-1/4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Wybierz wariant</label>
        <select
            v-model="selectedVariantId"
            class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-gray-900 focus:border-gray-900 sm:text-sm"
        >
          <option value="" disabled>-- Wybierz wariant --</option>
          <option v-for="v in variants" :key="v.id" :value="v.id">
            Wariant {{ v.panel_count }} paneli
          </option>
        </select>
      </div>

      <!-- Form for selected variant -->
      <BaseForm
          v-if="selectedVariant"
          title="Ceny wariantu"
          description="Ustaw ceny dla wariantu, paneli, typów instalacji i magazynów."
          submitLabel="Zapisz ceny"
          @submit.prevent="submitPrices"
      >
        <h2 class="text-lg font-medium text-gray-800">
          Wariant {{ selectedVariant.panel_count }} paneli
        </h2>

        <!-- Panel Model Selection -->
        <div class="mt-4 w-1/2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Wybierz model panelu</label>
          <select
              v-model="selectedPanelId"
              class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-gray-900 focus:border-gray-900 sm:text-sm"
          >
            <option value="" disabled>-- Wybierz model --</option>
            <option v-for="panel in selectedVariant.models" :key="panel.id" :value="panel.id">
              {{ panel.name }}
            </option>
          </select>
        </div>

        <!-- Panel Prices for Selected Model -->
        <div v-if="selectedPanel">
          <h3 class="text-md font-medium text-gray-700 mt-4">{{ selectedPanel.name }}</h3>
          <div class="grid grid-cols-3 gap-4">
            <div v-for="type in selectedVariant.install_types" :key="type" class="col-span-1">
              <CustomInput
                  v-model="prices.panels[selectedPanel.id][type]"
                  :label="`Cena dla ${type}`"
                  type="number"
                  min="0"
                  :attrs="{ step: 0.01 }"
              />
            </div>
          </div>
        </div>

        <!-- Inverter Price -->
        <div v-if="selectedVariant.panel_count <= 30" class="mt-4 w-1/3">
          <CustomInput
              v-model="prices.inverter"
              label="Cena inwertera"
              type="number"
              min="0"
              :attrs="{ step: 0.01 }"
          />
        </div>

        <!-- Storage Variants -->
        <div v-if="selectedVariant.storages?.length > 0" class="mt-4">
          <h3 class="text-md font-medium text-gray-700">Magazyny</h3>
          <div class="grid grid-cols-3 gap-4">
            <div v-for="storage in selectedVariant.storages" :key="storage" class="col-span-1">
              <CustomInput
                  v-model="prices.storages[storage]"
                  :label="`Cena magazynu ${storage} kWh`"
                  type="number"
                  min="0"
                  :attrs="{ step: 0.01 }"
              />
            </div>
          </div>
        </div>
      </BaseForm>
    </div>
  </MainLayout>
</template>

<script setup lang="ts">
import {reactive, ref, watch, computed, onMounted} from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import axios from "axios";
import {useEventable} from "@/Shared/utilities/eventBus";

const {emit, watchEvent } = useEventable();
import MainLayout from "@/Shared/Layouts/MainLayout.vue";
import BaseForm from "@/Shared/forms/BaseForm.vue";
import CustomInput from "@/Shared/forms/inputs/CustomInput.vue";
import {number} from "yup";

interface Variant {
  id: number;
  panel_count: number;
  models: { id: number; name: string }[];
  install_types: string[];
  storages?: number[];
  panel_prices?: Array<{ panel_model_id: number; install_type: string; price_per_panel: number }>;
  storage_prices?: Array<{ capacity_kwh: number; price: number }>;
}

interface PriceForm {
  panels: Record<number, Record<string, number>>;
  storages: Record<number, number>;
  inverter: number;
}

const props = defineProps<{ variants: Variant[] }>();
const variants = props.variants;

const selectedVariantId = ref<number | null>(null);
const selectedVariant = ref<Variant | null>(null);
const selectedPanelId = ref<number | null>(null);

const prices = reactive<PriceForm>({
  panels: {},
  storages: {},
  inverter: number,
});

const inverterPrice = ref<number>(0);

// Get URL query parameter
function getQueryParam(param: string): string | null {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get(param);
}


// Compute current panel object
const selectedPanel = computed(() =>
    selectedVariant.value?.models.find(m => m.id === selectedPanelId.value) ?? null
);
watch(selectedVariantId, async (val) => {
  if (!val) {
    selectedVariant.value = null;
    selectedPanelId.value = null;
    return;
  }

  try {
    const { data: variantData } = await axios.get(route("prices.show", val));
    selectedVariant.value = variantData;

    // Initialize prices
    prices.panels = {};
    inverterPrice.value = Number(variantData.prices?.inverter ?? 0);

    (variantData.models ?? []).forEach(panel => {
      prices.panels[panel.id] = {};
      (variantData.install_types ?? []).forEach(type => {
        const existingPrice = variantData.panel_prices?.find(
            pp => pp.panel_model_id === panel.id && pp.install_type === type
        );
        prices.panels[panel.id][type] = existingPrice?.price_per_panel ?? 0;
      });
    });

    prices.storages = {};
    (variantData.storages ?? []).forEach(storage => {
      const existingPrice = variantData.storage_prices?.find(sp => sp.capacity_kwh === storage);
      prices.storages[storage] = existingPrice?.price ?? 0;
    });

    prices.inverter = inverterPrice.value;

    // ✅ Auto-select the first model in the list
    selectedPanelId.value = variantData.models?.length ? variantData.models[0].id : null;

  } catch (error) {
    console.error("Failed to fetch variant data", error);
  }
});


const submitPrices = () => {
  if (!selectedVariant.value) return;
  emit('show-logo-loader');
  router.put(
      route("prices.update", { variant: selectedVariantId.value }),
      { prices },
      {
        onSuccess: (page) => {
          console.log("Prices updated successfully!", page);
          emit('hide-logo-loader');
          // Example: show a success notification or call a callback
          emit("show-notification", { title: "Sukces!", message: "Ceny zostały zapisane pomyślnie!", type: "success" });
        },
        onError: (errors) => {
          console.error("Failed to update prices:", errors);
          // Example: show an error notification or call a callback
          // emit("show-notification", { title: "Error", message: "Failed to save prices.", type: "error" });
        },
      }
  );
};

onMounted(() => {
  // Check if URL has selected_variant_id param
  const variantIdFromUrl = getQueryParam("selected_variant_id");

  if (variantIdFromUrl && !isNaN(Number(variantIdFromUrl))) {
    selectedVariantId.value = Number(variantIdFromUrl);
    console.log( selectedVariantId.value)
  }
  // else if (variants.length) {
  //   // Fallback: select last variant
  //   selectedVariantId.value = variants[variants.length - 1].id;
  // }
});
</script>
