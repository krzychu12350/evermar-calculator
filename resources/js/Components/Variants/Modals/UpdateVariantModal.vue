<template>
  <BaseModalForm
      :isOpen="props.isOpen"
      title="Edytuj wariant"
      message="Zaktualizuj dane wariantu paneli"
      confirmText="Zapisz"
      cancelText="Anuluj"
      @close="closeModal"
  >
    <template #icon>
      <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
        <PlusIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
      </div>
    </template>

    <template #default>
      <form @submit.prevent="onSubmit" class="space-y-6">
        <!-- Number of Panels -->
        <!--
        <div>
          <label class="block text-sm font-medium text-gray-700">Liczba paneli</label>
          <VueMultiselect
              v-model="panelCount"
              :options="availableCounts"
              :multiple="false"
              :close-on-select="true"
              placeholder="Wybierz liczbę paneli"
              track-by="value"
              label="label"
              :reduce="option => option.value"
              :disabled="true"
        />
        <p v-if="errors.panelCount" class="text-red-500 text-xs mt-1">{{ errors.panelCount }}</p>
        </div>
        -->

        <!-- Panel Models -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Modele paneli</label>
          <VueMultiselect
              v-model="selectedPanels"
              :options="panelOptions"
              :multiple="true"
              :close-on-select="true"
              placeholder="Wybierz modele paneli"
              label="name"
              track-by="id"
              :reduce="option => option.id"
          />
          <p v-if="errors.panelModels" class="text-red-500 text-xs mt-1">{{ errors.panelModels }}</p>
        </div>

        <!-- Installation types -->
        <div>
          <h4 class="text-sm font-medium text-gray-700 mb-2">Typ instalacji</h4>
          <VueMultiselect
              v-model="selectedInstallTypes"
              :options="installTypes"
              :multiple="true"
              :close-on-select="true"
              placeholder="Wybierz typ instalacji"
              label="label"
              track-by="value"
              :reduce="option => option.value"
          />
          <p v-if="errors.installTypes" class="text-red-500 text-xs mt-1">{{ errors.installTypes }}</p>
        </div>

        <!-- Storage variants -->
        <div>
          <h4 class="text-sm font-medium text-gray-700 mb-2">Warianty magazynu</h4>
          <VueMultiselect
              v-model="selectedStorages"
              :options="storageVariants"
              :multiple="true"
              :close-on-select="true"
              placeholder="Wybierz warianty magazynu"
              label="label"
              track-by="value"
              :reduce="option => option.value"
          />
          <p v-if="errors.storageVariants" class="text-red-500 text-xs mt-1">{{ errors.storageVariants }}</p>
        </div>

        <!-- Submit / Cancel Buttons -->
        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
          <button type="submit"
                  class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
            Zapisz
          </button>
          <button type="button"
                  class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                  @click="closeModal">
            Anuluj
          </button>
        </div>
      </form>
    </template>
  </BaseModalForm>
</template>

<script setup lang="ts">
import { ref, reactive, defineProps, defineEmits, watch } from "vue";
import BaseModalForm from "@/Shared/modals/BaseModalForm.vue";
import VueMultiselect from "vue-multiselect";
import { PlusIcon } from "@heroicons/vue/20/solid";
import { route } from "ziggy-js";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import { useEventable } from "@/Shared/utilities/eventBus";

interface Variant {
  id: number;
  panel_count: number;
  panel_models: number[];
  install_types: string[];
  storage_variants: number[];
}

const props = defineProps<{ isOpen: boolean; variant: Variant }>();
const emiter = defineEmits<{ (e: "close"): void; (e: "confirm", newVariant: object): void }>();
const { emit } = useEventable();

const closeModal = () => emiter("close");

// Reactive state
// const panelCount = ref<number | null>(null);
const availableCounts = ref<{ label: string; value: number }[]>([]);
const panelOptions = ref<any[]>([]);
const selectedPanels = ref<any[]>([]);
const installTypes = ref<{ label: string; value: string }[]>([]);
const selectedInstallTypes = ref<any[]>([]);
const storageVariants = ref<{ label: string; value: number }[]>([]);
const selectedStorages = ref<any[]>([]);

// Reactive errors
const errors = reactive({
  // panelCount: "", <-- commented out
  panelModels: "",
  installTypes: "",
  storageVariants: ""
});

// Load options and prefill form
watch(() => props.isOpen, async (open) => {
  if (open) {
    const { data } = await axios.get(route("variants.options"));
    availableCounts.value = data.available_counts;
    panelOptions.value = data.panel_models;
    installTypes.value = data.install_types;
    storageVariants.value = data.storage_variants;

    // Prefill selections from variant
    selectedPanels.value = panelOptions.value.filter((m: any) =>
        props.variant.models.some((vm: any) => vm.id === m.id)
    );
    selectedInstallTypes.value = installTypes.value.filter((i: any) =>
        props.variant.install_types.includes(i.value)
    );
    selectedStorages.value = storageVariants.value.filter((s: any) =>
        props.variant.storages.includes(s.value)
    );

    Object.keys(errors).forEach(k => (errors[k] = ""));
  }
});

// Submit handler
const onSubmit = () => {
  // Reset errors
  errors.panelModels = "";
  errors.installTypes = "";
  errors.storageVariants = "";

  // Validate each field
  if (!selectedPanels.value.length) errors.panelModels = "Wybierz przynajmniej jeden model panelu";
  if (!selectedInstallTypes.value.length) errors.installTypes = "Wybierz przynajmniej jeden typ instalacji";
  if (!selectedStorages.value.length) errors.storageVariants = "Wybierz przynajmniej jeden wariant magazynu";

  if (errors.panelModels || errors.installTypes || errors.storageVariants) return;

  // Map objects to backend-friendly payload
  const payload = {
    // panel_count is not allowed to update anymore
    panel_models: selectedPanels.value.map(p => typeof p === 'object' ? p.id : p),
    install_types: selectedInstallTypes.value.map(i => typeof i === 'object' ? i.value : i),
    storage_variants: selectedStorages.value.map(s => typeof s === 'object' ? s.value : s),
  };

  emit("show-logo-loader");
  router.put(route("variants.update", props.variant.id), payload, {
    onSuccess: () => {
      emit("hide-logo-loader");
      // emit('reload-variants');
        emit("show-notification", {
          title: "Sukces!",
          message: "Wariant został zaktualizowany pomyślnie!",
          type: "success",
          timeout: 5000
        });


      closeModal();
    },
    onError: (errors) => {
      console.error("Backend validation errors:", errors);
    }
  });
};

</script>
