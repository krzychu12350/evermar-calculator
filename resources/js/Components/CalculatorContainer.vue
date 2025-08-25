<template>
  <div class="bg-gray-50 min-h-screen flex justify-center items-start py-12">
    <!-- Mobile dialog (form on small screens) -->
    <TransitionRoot as="template" :show="mobileFiltersOpen">
      <Dialog class="relative z-40 lg:hidden" @close="mobileFiltersOpen = false">
        <TransitionChild
            as="template"
            enter="transition-opacity ease-linear duration-300"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="transition-opacity ease-linear duration-300"
            leave-from="opacity-100"
            leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/25"/>
        </TransitionChild>

        <div class="fixed inset-0 z-40 flex">
          <TransitionChild
              as="template"
              enter="transition ease-in-out duration-300 transform"
              enter-from="translate-x-full"
              enter-to="translate-x-0"
              leave="transition ease-in-out duration-300 transform"
              leave-from="translate-x-0"
              leave-to="translate-x-full"
          >
            <DialogPanel
                class="relative ml-auto flex h-full max-w-xs flex-col overflow-y-auto bg-white py-4 pb-12 shadow-xl">
              <div class="flex items-center justify-between px-4">
                <h2 class="text-lg font-medium text-gray-900">Formularz</h2>
                <button
                    type="button"
                    class="-mr-2 flex h-10 w-10 items-center justify-center rounded-md bg-white p-2 text-gray-400"
                    @click="mobileFiltersOpen = false"
                >
                  <span class="sr-only">Zamknij</span>
                  <XMarkIcon class="h-6 w-6" aria-hidden="true"/>
                </button>
              </div>

              <!-- Mobile CalculationForm -->
              <div class="mt-4 px-4">
                <CalculationForm/>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Desktop layout with steps -->
    <div :class="containerClasses">
      <!-- Step indicators -->
      <div class="flex justify-center mb-6 space-x-2">
  <span
      v-for="(s, index) in steps"
      :key="index"
      class="w-3 h-3 rounded-full cursor-pointer"
      :class="currentStep === index + 1 ? 'bg-green-600' : 'bg-gray-300'"
      @click="currentStep = index + 1"
      title="Kliknij, aby przejść do tego kroku"
  ></span>
      </div>

      <!-- Header -->
      <div class="flex items-baseline justify-between border-b border-gray-200 pb-4 mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ title }}</h1>
        <button
            type="button"
            class="-m-2 ml-4 p-2 text-gray-400 hover:text-gray-500 lg:hidden"
            @click="mobileFiltersOpen = true"
        >
          <span class="sr-only">Otwórz formularz</span>
          <FunnelIcon class="h-6 w-6" aria-hidden="true"/>
        </button>
      </div>

      <!-- Step content -->
      <section>
        <div v-if="currentStep === 1">
          <CalculationForm/>
        </div>

        <div v-if="currentStep === 2">
          <InvoiceTable/>
          <InvoiceSummary/>
        </div>
      </section>

      <!-- Navigation buttons -->
      <div v-if="currentStep === 2" class="flex justify-between mt-6">
        <button
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
            :disabled="currentStep === 1"
            @click="prevStep"
        >
          Wstecz
        </button>

        <button
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
            :disabled="currentStep === steps.length"
            @click="nextStep"
        >
          Dalej
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {onMounted, ref, computed} from "vue";
import {Dialog, DialogPanel, TransitionChild, TransitionRoot} from "@headlessui/vue";
import {XMarkIcon, FunnelIcon} from "@heroicons/vue/24/outline";
import CalculationForm from './CalculationForm.vue';
import InvoiceTable from "@/Components/InvoiceTable.vue";
import {useEventable} from "@/Shared/utilities/eventBus";
import InvoiceSummary from "@/Components/InvoiceSummary.vue";

const props = defineProps<{ title: string }>();
const mobileFiltersOpen = ref(false);
const {watchEvent} = useEventable();
// Step logic
const steps = ["Formularz", "Faktura"];
const currentStep = ref(1);

const nextStep = () => {
  if (currentStep.value < steps.length) currentStep.value++;
};

const prevStep = () => {
  if (currentStep.value > 1) currentStep.value--;
};

const containerClasses = computed(() => {
  return currentStep.value === 2
      ? 'bg-white w-full max-w-5xl rounded-lg shadow-lg p-6 transition-all duration-300'
      : 'bg-white w-full max-w-xl rounded-lg shadow-lg p-6 transition-all duration-300';
});

onMounted(() => {
  watchEvent('go-to-next-step', nextStep)
})

</script>
