<script setup>
import {ref, watch, computed} from 'vue'
import {useForm} from '@inertiajs/vue3'
import {useEventable} from "@/Shared/utilities/eventBus.ts";
const {emit } = useEventable();

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
  extraStorage: 0,
  taxType: 'private',
  marginPercent: 0
})

// toggles for string installation
const showInverterField = ref(false)
const showStorageField = ref(false)
const showMarginField = ref(false)


// These should be populated from your backend Inertia response
let totalFromBackend = ref(0);
let invoiceItemsFromBackend = ref([]);
let summaryCost = ref({});

// Optional helper to format prices nicely
function formatPrice(value) {
  if (!value && value !== 0) return '-';
  return new Intl.NumberFormat('pl-PL', {style: 'currency', currency: 'PLN'}).format(value);
}

// Final total including tax
const totalWithTax = computed(() => {
  if (!totalFromBackend.value) return 0;
  const multiplier = installationForm.taxType === 'private' ? 1.08 : 1.23;
  return totalFromBackend.value * multiplier;
});
const handleSubmit = () => {
  console.log(installationForm.data());
  installationForm.post('/installation/calculate', {
    preserveScroll: true,
    preserveState: true,
    replace: false,
    onSuccess: (page) => {
      totalFromBackend.value = page.props.total;
      invoiceItemsFromBackend.value = page.props.invoiceItems || [];
      summaryCost.value = page.props.summary || {};

      // Compute table-ready fields
      const taxMultiplier = installationForm.taxType === 'private' ? 1.08 : 1.23;
      const tableData = invoiceItemsFromBackend.value.map(item => ({
        ...item,
        vat: installationForm.taxType === 'private' ? '8%' : '23%',
        price_with_vat: Number(item.unit_price) * taxMultiplier,
        total_with_vat: Number(item.total) * taxMultiplier,
      }));


      // Trigger step change after table updated
      setTimeout(() => {
        emit('go-to-next-step');
      }, 100); // slightly delay to ensure reactivity

      setTimeout(() => {

        emit('updateInvoice', {
          data: tableData,
          columns: [
            { title: 'Pozycja', key: 'name' },
            { title: 'Ilość', key: 'quantity' },
            { title: 'Cena netto', key: 'unit_price' },
            { title: 'VAT', key: 'vat' },
            { title: 'Cena brutto', key: 'price_with_vat' },
            { title: 'Suma netto', key: 'total' },
            { title: 'Suma brutto', key: 'total_with_vat' },
          ],
          title: "Oferta szczegółowa",
          description: "Szczegóły pozycji instalacji",
        });

          emit('updateInvoiceSummary', {
            summaryCost: summaryCost.value,
            taxType: installationForm.taxType
          });

      }, 500); // slightly delay to ensure reactivity


    }

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
      // additionalInverterKW.value = 0;
    }
)

// Reset additionalInverterKW when inverter field hidden OR inverter deselected
watch(
    [showInverterField, () => installationForm.inverterSelected],
    ([newShowInverterField, newInverterSelected]) => {
      if (!newShowInverterField || newInverterSelected === false) {
        installationForm.additionalInverterKW = 0;
      }
    }
);

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

function handleReset() {
  //installationForm.reset();

  installationForm.installationType = 'string'
  installationForm.panelType = 'tongwei'
  installationForm.panelCount = 8;
  installationForm.groundInstallation = ''
  installationForm.inverterSelected = false
  installationForm.storageCapacity = 0
  installationForm.backup = false
  installationForm.proJoy = false
  installationForm.taxType = 'private'
  installationForm.inverterSelected = false
  installationForm.marginPercent = 0

  totalFromBackend.value = 0
}

const resultsDiv = ref(null);


// // Total netto including margin
// const totalNettoWithMargin = computed(() => {
//   const margin = installationForm.marginPercent / 100;
//   return totalFromBackend.value * (1 + margin);
// });
//
// // Margin amount itself
// const marginAmount = computed(() => {
//   const margin = installationForm.marginPercent / 100;
//   return totalFromBackend.value * margin;
// });
//
// // Total brutto including VAT and margin
// const totalBruttoWithMargin = computed(() => {
//   const multiplier = installationForm.taxType === 'private' ? 1.08 : 1.23;
//   return totalNettoWithMargin.value * multiplier;
// });

const toggleMargin = () => {
  showMarginField.value = !showMarginField.value
  if (!showMarginField.value) {
    installationForm.marginPercent = 0
  }
}

</script>

<template>
<!--  <div v-if="totalFromBackend !== 0" ref="resultsDiv" class="mt-4 mb-4 p-4 bg-gray-100 rounded">-->
<!--    &lt;!&ndash; Total Cost &ndash;&gt;-->
<!--    <div class="text-center mb-4">-->
<!--      <h3 class="text-lg font-semibold mb-2 text-gray-700">Całkowity koszt instalacji:</h3>-->
<!--      <vue3-autocounter-->
<!--          class="text-2xl font-bold text-green-600"-->
<!--          ref="counter"-->
<!--          :startAmount="0"-->
<!--          :endAmount="totalWithTax"-->
<!--          :duration="1"-->
<!--          suffix=" zł"-->
<!--          separator=""-->
<!--          decimalSeparator=","-->
<!--          :decimals="2"-->
<!--          :autoinit="true"-->
<!--      />-->
<!--    </div>-->

<!--    &lt;!&ndash; Detailed Invoice Table &ndash;&gt;-->
<!--    <div class="overflow-x-auto">-->
<!--      <table class="w-full text-left border border-gray-300 rounded">-->
<!--        <thead class="bg-gray-200">-->
<!--        <tr>-->
<!--          <th class="px-4 py-2 border-b">Pozycja</th>-->
<!--          <th class="px-4 py-2 border-b">Ilość</th>-->
<!--          <th class="px-4 py-2 border-b">Cena netto</th>-->
<!--          <th class="px-4 py-2 border-b">VAT</th>-->
<!--          <th class="px-4 py-2 border-b">Cena brutto</th>-->
<!--          <th class="px-4 py-2 border-b">Suma netto</th>-->
<!--          <th class="px-4 py-2 border-b">Suma brutto</th>-->
<!--        </tr>-->
<!--        </thead>-->
<!--        <tbody>-->
<!--        <tr v-for="(item, index) in invoiceItemsFromBackend" :key="index" class="hover:bg-gray-50">-->
<!--          <td class="px-4 py-2 border-b">{{ item.name }}</td>-->
<!--          <td class="px-4 py-2 border-b">{{ item.quantity }}</td>-->
<!--          <td class="px-4 py-2 border-b">{{ formatPrice(item.unit_price) }}</td>-->
<!--          <td class="px-4 py-2 border-b"> {{ installationForm.taxType === 'private' ? '8%' : '23%' }}</td>-->
<!--          <td class="px-4 py-2 border-b">-->
<!--            {{ formatPrice(item.unit_price * (installationForm.taxType === 'private' ? 1.08 : 1.23)) }}-->
<!--          </td>-->
<!--          <td class="px-4 py-2 border-b font-semibold">{{ formatPrice(item.total) }}</td>-->
<!--          <td class="px-4 py-2 border-b font-semibold">-->
<!--            {{ formatPrice(item.total * (installationForm.taxType === 'private' ? 1.08 : 1.23)) }}-->
<!--          </td>-->
<!--        </tr>-->
<!--        </tbody>-->
<!--        <tfoot>-->
<!--        <tr class="bg-gray-100 font-bold">-->
<!--          <td colspan="5" class="px-4 py-2 border-t text-right">Razem:</td>-->
<!--          <td class="px-4 py-2 border-t">{{ formatPrice(totalFromBackend) }}</td>-->
<!--          <td class="px-4 py-2 border-t">{{ formatPrice(totalWithTax) }}</td>-->
<!--        </tr>-->
<!--        </tfoot>-->
<!--      </table>-->
<!--    </div>-->
<!--  </div>-->


  <!--  <pre>-->

  <!--    {{installationForm}}-->
  <!--  </pre>-->

  <div class="installation-form max-w-lg mx-auto bg-white py-4">
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
              class="flex items-center justify-center w-8 h-8 text-white bg-green-600 rounded hover:bg-green-700 cursor-pointer"
          >
            {{ showInverterField ? '−' : '+' }}
          </button>
          <span class="text-gray-700 font-medium">Dodaj inwerter</span>
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


<!--      <div v-if="installationForm.installationType === 'string'" class="mb-4 space-y-4">-->
<!--        &lt;!&ndash; Storage toggle &ndash;&gt;-->
<!--        <div class="flex items-center space-x-2">-->
<!--          <button-->
<!--              type="button"-->
<!--              @click="showStorageField = !showStorageField"-->
<!--              class="flex items-center justify-center w-8 h-8 text-white bg-green-600 rounded hover:bg-green-700 cursor-pointer"-->
<!--          >-->
<!--            {{ showStorageField ? '−' : '+' }}-->
<!--          </button>-->
<!--          <span class="text-gray-700 font-medium">Dodaj magazyn</span>-->
<!--        </div>-->
<!--      </div>-->

      <!-- Storage selection -->
      <div v-if="installationForm.installationType === 'storage'" class="mb-4">
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

      <!-- VAT Selection -->
      <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-2">Rodzaj klienta (VAT)</label>
        <div class="flex items-center space-x-4">
          <label class="flex items-center space-x-2">
            <input type="radio" value="private" v-model="installationForm.taxType"/>
            <span>Klient prywatny (8%)</span>
          </label>
          <label class="flex items-center space-x-2">
            <input type="radio" value="company" v-model="installationForm.taxType"/>
            <span>Firma (23%)</span>
          </label>
        </div>
      </div>

      <!-- Margin toggle -->
      <div class="mb-4 space-y-4">
        <div class="flex items-center space-x-2">
          <button
              type="button"
              @click="toggleMargin"
              class="flex items-center justify-center w-8 h-8 text-white bg-green-600 rounded hover:bg-green-700 cursor-pointer"
          >
            {{ showMarginField ? '−' : '+' }}
          </button>
          <span class="text-gray-700 font-medium">Dodaj marżę handlowca</span>
        </div>
      </div>

      <!-- Margin slider -->
      <div v-if="showMarginField" class="mb-6">
        <label class="block text-gray-700 font-medium mb-2">
          Marża handlowca: {{ installationForm.marginPercent }}%
        </label>
        <input
            type="range"
            min="0"
            max="20"
            step="1"
            v-model.number="installationForm.marginPercent"
            class="w-full cursor-pointer accent-green-600"
        />
        <p class="text-sm text-gray-500">Ustaw od 0% do 20%</p>
      </div>


      <!-- Final Total Netto, Marża, Brutto -->
      <div  v-if="totalFromBackend !== 0" class="text-center mt-6 space-y-2">
        <h3 class="text-lg font-semibold text-gray-700">Podsumowanie:</h3>

        <!-- Netto (bez VAT) -->
        <p class="text-xl font-semibold text-gray-800">
          Netto (z marżą): {{ formatPrice(summaryCost.totalNettoWithMargin) }}
        </p>

        <!-- Marża -->
        <p class="text-md font-medium text-green-600">
          Zarobek handlowca: {{ formatPrice(summaryCost.marginAmount) }}
        </p>

        <!-- Brutto -->
        <p class="text-2xl font-bold text-blue-600">
          Brutto ({{ installationForm.taxType === 'private' ? '8%' : '23%' }} VAT):
          {{ formatPrice(summaryCost.totalBruttoWithMargin) }}
        </p>
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
