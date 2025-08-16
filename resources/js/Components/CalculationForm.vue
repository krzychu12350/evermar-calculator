<script setup>
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const formRef = ref()

// Tworzymy instancję Inertia Form
const installationForm = useForm({
  installationType: 'string',
  panelCount: 8,
  panelType: 'tongwei',
  storageCapacity: 0,
  hybridInverter: false,
  groundInstallation: '',
  additionalInverterKW: 0,
  backup: false,
  proJoy: false,
  extraStorage: 0
})

// zmienna do przechowywania wyniku z backendu
const totalFromBackend = ref(null)

const handleSubmit = () => {
  installationForm.post('/installation/calculate', {
    preserveScroll: true, // opcjonalnie zachowuje scroll
    onSuccess: (page) => {
      // backend zwraca total w props
      totalFromBackend.value = page.props.total
      console.log(totalFromBackend.value)
    }
  })
}

// Pola dopłat dla instalacji gruntowej
const groundCosts = {
  grunt: 300,
  ekierka: 300,
  gont: 300,
  dachowka: 250,
  rąbek: 250
}

// debounce timer
let debounceTimer = null

// watchujemy wszystkie pola formularza
// watch(
//     installationForm,
//     () => {
//       clearTimeout(debounceTimer)
//       debounceTimer = setTimeout(() => {
//         handleSubmit()
//       }, 5000) // 5 sekund
//     },
//     { deep: true } // bardzo ważne, żeby wykryło zmiany w obiekcie
// )
</script>

<template>
  <!-- Wyświetlenie wyniku z backendu -->
  <div v-if="totalFromBackend !== null" class="mt-4 mb-4 p-4 bg-gray-100 rounded text-center">
    <h3 class="text-lg font-semibold mb-2 text-gray-700">Całkowity koszt instalacji:</h3>
    <!--    <p class="text-2xl font-bold text-green-600">{{ totalFromBackend }} zł</p>-->
    <vue3-autocounter class="text-2xl font-bold text-green-600" ref='counter' :startAmount='0' :endAmount='totalFromBackend' :duration='1'  suffix=' zł' separator='' decimalSeparator='.' :decimals='2' :autoinit='true' />
  </div>


  <div class="installation-form max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg">
    <h2 class="text-2xl font-semibold mb-4 text-gray-800">Oblicz koszt instalacji</h2>

    <form @submit.prevent="handleSubmit">

      <!-- Typ instalacji -->
      <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">Rodzaj instalacji</label>
        <select v-model="installationForm.installationType" name="installationType" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400">
          <option value="string">Na stringach</option>
          <option value="storage">Z magazynami</option>
        </select>
      </div>

      <!-- Ilość paneli -->
      <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">Ilość paneli</label>
        <input type="number" v-model.number="installationForm.panelCount" name="panelCount" min="8" max="100" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" />
      </div>

      <!-- Typ paneli -->
      <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">Typ paneli</label>
        <select v-model="installationForm.panelType" name="panelType" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400">
          <option value="tongwei">Tongwei 500W</option>
          <option value="jasolar">JASOLAR 500W FB</option>
        </select>
      </div>

      <!-- Wybór magazynu -->
      <div class="mb-4" v-if="installationForm.installationType === 'storage'">
        <label class="block text-gray-700 font-medium mb-1">Wybierz magazyn</label>
        <select v-model.number="installationForm.storageCapacity" name="storageCapacity" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400">
          <option :value="0">Brak</option>
          <option :value="5">5 kWh</option>
          <option :value="10">10 kWh</option>
          <option :value="15">15 kWh</option>
          <option :value="20">20 kWh (+ dopłata)</option>
        </select>
      </div>

      <!-- Konstrukcja gruntowa / dach -->
      <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">Miejsce montażu instalacji</label>
        <select v-model="installationForm.groundInstallation" name="groundInstallation" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400">
          <option value="">Brak dopłaty</option>
          <option value="grunt">Grunt (+300 zł/kW)</option>
          <option value="ekierka">Ekierka (+300 zł/kW)</option>
          <option value="gont">Gont (+300 zł/kW)</option>
          <option value="dachowka">Dachówka (+250 zł/kW)</option>
          <option value="rąbek">Rąbek (+250 zł/kW)</option>
        </select>
      </div>

      <!-- Dodatkowy inwerter -->
      <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">Dodatkowa moc inwertera (kW powyżej nominalnej)</label>
        <input type="number" v-model.number="installationForm.additionalInverterKW" name="additionalInverterKW" min="0" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" />
      </div>

      <!-- BackUp -->
      <div class="mb-4 flex items-center" v-if="installationForm.installationType === 'storage'">
        <input type="checkbox" v-model="installationForm.backup" name="backup" id="backup" class="mr-2 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" />
        <label for="backup" class="text-gray-700 font-medium">BackUp (+2000 zł)</label>
      </div>

      <!-- Pro Joy -->
      <div class="mb-4 flex items-center">
        <input type="checkbox" v-model="installationForm.proJoy" name="proJoy" id="proJoy" class="mr-2 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" />
        <label for="proJoy" class="text-gray-700 font-medium">Pro Joy (+1250 zł)</label>
      </div>

      <button type="submit" class="w-full bg-green-600 text-white font-semibold py-2 px-4 rounded hover:bg-green-700">
        Oblicz koszt
      </button>
    </form>

<!--    <button class="w-full mt-2 bg-gray-500 text-white font-semibold py-2 px-4 rounded hover:bg-gray-600 transition-colors" @click="handleSubmit">-->
<!--      Submit Programmatically-->
<!--    </button>-->
  </div>


</template>
