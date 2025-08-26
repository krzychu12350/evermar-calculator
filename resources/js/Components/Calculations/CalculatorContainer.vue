<template>
  <div class="bg-gray-50 min-h-screen flex justify-center items-start">
    <!-- Desktop layout with steps -->
    <div :class="containerClasses">
      <!-- Step indicators (visible only on mobile) -->
<!--      <div class="flex justify-center mb-6 space-x-2 md:hidden">-->
<!--        <span-->
<!--            v-for="(s, index) in steps"-->
<!--            :key="index"-->
<!--            class="w-3 h-3 rounded-full cursor-pointer"-->
<!--            :class="currentStep === index + 1 ? 'bg-green-500' : 'bg-gray-300'"-->
<!--            @click="currentStep = index + 1"-->
<!--        ></span>-->
<!--      </div>-->

      <!-- Header -->
      <div class="flex items-baseline justify-between border-b border-gray-200 pb-4 mb-6">
        <h1 class="text-2xl lg:text-4xl font-bold tracking-tight text-gray-900">{{ title }}</h1>
      </div>

      <!-- Step content -->
      <section>
        <!-- Step 1: Calculation Form -->
        <div v-if="currentStep === 1">
          <CalculationForm @submit.prevent="nextStep"/>
        </div>

        <!-- Step 2: Invoice Table + Summary -->
        <div v-if="currentStep === 2">
          <InvoiceTable/>
          <InvoiceSummary/>


        </div>

        <!-- Step 3: Sales Rep Form -->
        <div v-if="currentStep === 3">
          <SalesRepForm :onNext="nextStepWithSalesRep"/>

        </div>


        <!-- Step 4: Invoice PDF Preview -->
        <div v-if="currentStep === 4">
          <InvoicePdf :invoiceData="invoiceData" :salesRepData="salesRepData"/>

        </div>
      </section>

      <!-- Navigation buttons -->
      <div v-if="currentStep > 1 && currentStep <= 4" class="flex justify-end gap-2 mt-6">
        <!-- Back button -->
        <button
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-400"
            @click="prevStep"
        >
          Wstecz
        </button>

        <!-- Step-specific buttons -->
        <button
            v-if="currentStep === 2"
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-400"
            @click="nextStep"
        >
          Dalej
        </button>

        <button
            v-if="currentStep === 3"
            type="button"
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-400"
            @click="emit('submit-sales-rep-form')"
        >
          Dalej
        </button>

        <button
            v-if="currentStep === 4"
            class="flex items-center gap-2 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-400 disabled:opacity-50 disabled:cursor-not-allowed"
            @click="downloadInvoice"
            :disabled="isDownloading"
        >
          <span>Pobierz ofertę</span>
          <VueSpinner
              v-if="isDownloading"
              size="20"
              color="white"
          />
        </button>


      </div>


      <!--        <button-->
      <!--            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-400"-->

      <!--            :disabled="currentStep === steps.length"-->
      <!--            @click="nextStep"-->
      <!--        >-->
      <!--          Dalej-->
      <!--        </button>-->
      <!--        <button-->
      <!--            class="cursor-pointer  px-4 py-2 bg-green-500 text-white rounded hover:bg-green-400"-->
      <!--            @click="emit('download-invoice')"-->
      <!--        >-->
      <!--          Pobierz ofete-->
      <!--        </button>-->
    </div>
  </div>

</template>

<script setup lang="ts">
import {onMounted, ref, computed, reactive} from "vue";
import {Dialog, DialogPanel, TransitionChild, TransitionRoot} from "@headlessui/vue";
import {XMarkIcon, FunnelIcon} from "@heroicons/vue/24/outline";
import CalculationForm from './CalculationForm.vue';
import InvoiceTable from "@/Components/Calculations/Invoice/InvoiceTable.vue";
import {useEventable} from "@/Shared/utilities/eventBus";
import InvoiceSummary from "@/Components/Calculations/Invoice/InvoiceSummary.vue";
import InvoicePdf from "@/Components/Calculations/Invoice/InvoicePdf.vue";
import SalesRepForm from "@/Components/Calculations/Invoice/SalesRepForm.vue";
import {VueSpinner} from "vue3-spinners";


const props = defineProps<{ title: string }>();
const salesRepFormRef = ref<any>(null);
const mobileFiltersOpen = ref(false);
const {emit, watchEvent} = useEventable();
const invoiceData = ref({});
// Step logic
const currentStep = ref(1);
const steps = ["Kalkulacja", "Faktura", "Przedstawiciel sprzedaży", "Podgląd PDF"];

// Reactive object to store sales rep data
const salesRepData = reactive({
  name: "",
  phone: "",
  email: "",
});

const nextStep = () => {
  if (currentStep.value < steps.length) currentStep.value++;
};

const prevStep = () => {
  if (currentStep.value > 1) currentStep.value--;
};
// Handle Step 3 submission
const nextStepWithSalesRep = (data: any) => {
  salesRepData.name = data.name;
  salesRepData.phone = data.phone;
  salesRepData.email = data.email;
  nextStep();
};

// Container classes for responsive layout
const containerClasses = computed(() => {
  const baseClasses =
      "bg-white w-full rounded-lg shadow-lg p-6 transition-all duration-300 min-h-screen md:min-h-0";

  switch (currentStep.value) {
    case 1:
      return baseClasses + " max-w-xl";
    case 3:
      return baseClasses + " max-w-lg";
    case 4:
      return baseClasses + " max-w-5xl";
    default:
      return baseClasses + " max-w-5xl";
  }
});


// Listen to CalculationForm events
watchEvent("updateInvoice", (payload: any) => {
  console.log(payload)
  invoiceData.value = payload;
});

watchEvent("updateInvoiceSummary", (payload: any) => {
  invoiceData.value.summary = payload.summaryCost;
});

// onMounted(() => {
//   watchEvent('go-to-next-step', nextStep)
// })

// Downloading state
const isDownloading = ref(false);

const downloadInvoice = () => {
  // Tell others that download started
  emit("download-invoice-start");
  emit("download-invoice");
};

// Listen for events to toggle spinner & button
watchEvent("download-invoice-start", () => {
  isDownloading.value = true;
});

watchEvent("download-invoice-finish", () => {
  isDownloading.value = false;
});


</script>
