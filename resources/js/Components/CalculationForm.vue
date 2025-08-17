<script setup>
import {ref, watch} from 'vue'
import {useForm} from '@inertiajs/vue3'

const installationForm = useForm({
  installationType: 'string',
  panelCount: 8,
  panelType: 'tongwei',
  storageCapacity: 0,
  hybridInverter: false,
  groundInstallation: '',
  inverterSelected: false,
  additionalInverterKW: 0,
  backup: false,
  proJoy: false,
  extraStorage: 0
})

// toggles for string installation
const showInverterField = ref(false)
const showStorageField = ref(false)

// These should be populated from your backend Inertia response
const totalFromBackend = ref(0);
const invoiceItemsFromBackend = ref([]);

// Optional helper to format prices nicely
function formatPrice(value) {
  if (!value && value !== 0) return '-';
  return new Intl.NumberFormat('pl-PL', {style: 'currency', currency: 'PLN'}).format(value);
}

const handleSubmit = () => {
  installationForm.post('/installation/calculate', {
    preserveScroll: true,      // Keep scroll position
    preserveState: true,       // Keep form state
    replace: false,             // Replace current history instead of pushing new URL
    onSuccess: (page) => {
      totalFromBackend.value = page.props.total;
      invoiceItemsFromBackend.value = page.props.invoiceItems || [];

      // Scroll to top
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
  });
};

// ground installation costs
const groundCosts = {
  grunt: 300,
  ekierka: 300,
  gont: 300,
  dachowka: 250,
  rąbek: 250
}

// Watch inverter selection and set hybridInverter automatically
watch(
    () => installationForm.inverterSelected,
    (newVal) => {
      installationForm.hybridInverter = newVal;
    }
)

// Watch installation type to set default storageCapacity
watch(
    () => installationForm.installationType,
    (newType) => {
      if (newType === 'storage') {
        installationForm.storageCapacity = 5 // default first option
      } else {
        installationForm.storageCapacity = 0 // reset if not storage
      }
    },
    {immediate: true} // run on component mount
)

const handleReset = () => {
  // Reset all form fields to their initial values
  installationForm.reset();

  // Reset UI toggles
  showInverterField.value = false;
  showStorageField.value = false;

  // Reset backend total if needed
  totalFromBackend.value = 0;
}

const resultsDiv = ref(null);

</script>

<template>
  <div v-if="totalFromBackend !== 0" ref="resultsDiv" class="mt-4 mb-4 p-4 bg-gray-100 rounded">
    <!-- Total Cost -->
    <div class="text-center mb-4">
      <h3 class="text-lg font-semibold mb-2 text-gray-700">Całkowity koszt instalacji:</h3>
      <vue3-autocounter
          class="text-2xl font-bold text-green-600"
          ref="counter"
          :startAmount="0"
          :endAmount="totalFromBackend"
          :duration="1"
          suffix=" zł"
          separator=""
          decimalSeparator="."
          :decimals="2"
          :autoinit="true"
      />
    </div>

    <!-- Detailed Invoice Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-left border border-gray-300 rounded">
        <thead class="bg-gray-200">
        <tr>
          <th class="px-4 py-2 border-b">Pozycja</th>
          <th class="px-4 py-2 border-b">Ilość</th>
          <th class="px-4 py-2 border-b">Cena jednostkowa</th>
          <th class="px-4 py-2 border-b">Suma</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(item, index) in invoiceItemsFromBackend" :key="index" class="hover:bg-gray-50">
          <td class="px-4 py-2 border-b">{{ item.name }}</td>
          <td class="px-4 py-2 border-b">{{ item.quantity }}</td>
          <td class="px-4 py-2 border-b">{{ formatPrice(item.unit_price) }}</td>
          <td class="px-4 py-2 border-b font-semibold">{{ formatPrice(item.total) }}</td>
        </tr>
        </tbody>
        <tfoot>
        <tr class="bg-gray-100 font-bold">
          <td colspan="3" class="px-4 py-2 border-t text-right">Razem:</td>
          <td class="px-4 py-2 border-t">{{ formatPrice(totalFromBackend) }}</td>
        </tr>
        </tfoot>
      </table>
    </div>
  </div>


  <!--  <pre>-->

  <!--    {{installationForm}}-->
  <!--  </pre>-->

  <div class="installation-form max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg">
    <h2 class="text-2xl font-semibold mb-4 text-gray-800">Oblicz koszt instalacji</h2>

    <form @submit.prevent="handleSubmit">
      <!-- Rodzaj instalacji -->
      <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">Rodzaj instalacji</label>
        <select
            v-model="installationForm.installationType"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
        >
          <option value="string">Na stringach</option>
          <option value="storage">Z magazynami</option>
        </select>
      </div>

      <!-- Ilość paneli -->
      <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">Ilość paneli</label>
        <input
            type="number"
            v-model.number="installationForm.panelCount"
            min="8"
            :max="installationForm.installationType === 'storage' ? 30 : 100"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
        />
        <p v-if="installationForm.installationType === 'storage'" class="text-sm text-gray-500 mt-1">
          Dla instalacji z magazynem maksymalna liczba paneli to 30.
        </p>
      </div>

      <!-- Typ paneli -->
      <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">Typ paneli</label>
        <select
            v-model="installationForm.panelType"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
        >
          <option value="tongwei">Tongwei 500W</option>
          <option value="jasolar">JASOLAR 500W FB</option>
        </select>
      </div>

      <!-- Miejsce montażu -->
      <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">Miejsce montażu instalacji</label>
        <select
            v-model="installationForm.groundInstallation"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
        >
          <option value="">Brak dopłaty</option>
          <option value="grunt">Grunt (+300 zł/kW)</option>
          <option value="ekierka">Ekierka (+300 zł/kW)</option>
          <option value="gont">Gont (+300 zł/kW)</option>
          <option value="dachowka">Dachówka (+250 zł/kW)</option>
          <option value="rąbek">Rąbek (+250 zł/kW)</option>
        </select>
      </div>

      <!-- STRING installation toggles -->
      <div v-if="installationForm.installationType === 'string'" class="mb-4 space-y-4">
        <!-- Inverter toggle -->
        <div class="flex items-center space-x-2">
          <button
              type="button"
              @click="showInverterField = !showInverterField"
              class="flex items-center justify-center w-8 h-8 text-white bg-green-600 rounded hover:bg-green-700"
          >
            {{ showInverterField ? '−' : '+' }}
          </button>
          <span class="text-gray-700 font-medium">Dodaj inwerter</span>
        </div>

        <!-- Storage toggle -->
        <div class="flex items-center space-x-2">
          <button
              type="button"
              @click="showStorageField = !showStorageField"
              class="flex items-center justify-center w-8 h-8 text-white bg-green-600 rounded hover:bg-green-700"
          >
            {{ showStorageField ? '−' : '+' }}
          </button>
          <span class="text-gray-700 font-medium">Dodaj magazyn</span>
        </div>
      </div>

      <!-- Inverter selection -->
      <div v-if="showInverterField" class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">Inwerter?</label>
        <select
            v-model="installationForm.inverterSelected"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
        >
          <option :value="false">Nie</option>
          <option :value="true">Tak</option>
        </select>
      </div>

      <!-- Additional inverter power -->
      <div
          v-if="(showInverterField && installationForm.inverterSelected === true) || installationForm.installationType === 'storage'"
          class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">
          Dodatkowa moc inwertera (kW powyżej nominalnej)
        </label>
        <input
            type="number"
            v-model.number="installationForm.additionalInverterKW"
            min="0"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
        />
      </div>


      <!-- Storage selection -->
      <div v-if="showStorageField || installationForm.installationType === 'storage'" class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">Wybierz magazyn</label>
        <select
            v-model.number="installationForm.storageCapacity"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
        >
          <option v-if=" installationForm.installationType === 'string'" :value="0">Brak</option>
          <option v-for="n in 10" :key="n" :value="n * 5">{{ n * 5 }} kWh</option>
        </select>
      </div>

      <!-- BackUp -->
      <div class="mb-4 flex items-center">
        <input type="checkbox" v-model="installationForm.backup"
               class="mr-2 h-4 w-4 text-green-600 border-gray-300 rounded"/>
        <label class="text-gray-700 font-medium">BackUp (+2000 zł)</label>
      </div>

      <!-- Pro Joy -->
      <div class="mb-4 flex items-center">
        <input type="checkbox" v-model="installationForm.proJoy"
               class="mr-2 h-4 w-4 text-green-600 border-gray-300 rounded"/>
        <label class="text-gray-700 font-medium">Pro Joy (+1250 zł)</label>
      </div>

      <div class="flex space-x-4 mt-4">
        <!-- Reset button -->
        <button
            type="button"
            @click="handleReset"
            class="flex-1 cursor-pointer bg-gray-500 text-white font-semibold py-2 px-4 rounded hover:bg-gray-600"
        >
          Resetuj formularz
        </button>

        <!-- Submit button -->
        <button
            type="submit"
            class="flex-1 cursor-pointer bg-green-600 text-white font-semibold py-2 px-4 rounded hover:bg-green-700"
        >
          Oblicz koszt
        </button>
      </div>


    </form>
  </div>
</template>
