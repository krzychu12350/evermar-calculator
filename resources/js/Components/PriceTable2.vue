<script setup>
import {ref, computed} from "vue"

const panelModels = ref([
  {id: 1, name: "LONGi 450W"},
  {id: 2, name: "JA Solar 400W"},
  {id: 3, name: "Trina Solar 410W"},
  {id: 4, name: "Canadian Solar 455W"},
])

const installTypes = ref(["String", "Storage"])
const storageCaps = ref([5, 10, 15])

const variants = ref(
    Array.from({length: 93}, (_, i) => ({
      id: i + 1,
      panel_count: i + 8,
    }))
)

function generateMockPrice(variantCount, base, multiplier = 1) {
  return (base + variantCount * multiplier + Math.random() * 50).toFixed(2)
}

function generatePrices() {
  return variants.value.map((variant) => ({
    variant_id: variant.id,
    panel_count: variant.panel_count,
    panels: panelModels.value.map((model, idx) => ({
      panel_model_id: model.id,
      model: model.name,
      string_price: generateMockPrice(variant.panel_count, 400 + idx * 20, 1.2),
      with_storage_price: generateMockPrice(variant.panel_count, 420 + idx * 20, 1.3),
    })),
    inverter_price: generateMockPrice(variant.panel_count, 5000, 10),
    storage_prices: {
      "5": generateMockPrice(variant.panel_count, 8000, 5),
      "10": generateMockPrice(variant.panel_count, 12000, 7),
      "15": generateMockPrice(variant.panel_count, 16000, 9),
    },
  }))
}

const prices = ref(generatePrices())

// Filtry
const selectedModels = ref(panelModels.value.map(m => m.id))
const selectedTypes = ref(["String", "Storage"])
const selectedStorage = ref([5, 10, 15])

const filteredPrices = computed(() => {
  return prices.value.map(variant => ({
    ...variant,
    panels: variant.panels.filter(p => selectedModels.value.includes(p.panel_model_id))
  }))
})

// paginacja
const currentPage = ref(1)
const itemsPerPage = 10
const totalPages = computed(() => Math.ceil(filteredPrices.value.length / itemsPerPage))

const paginatedPrices = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredPrices.value.slice(start, start + itemsPerPage)
})

function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) currentPage.value = page
}

// saveAll (porównanie zmian) pozostaje takie samo jak w Twoim poprzednim kodzie
function saveAll() {
  console.log("🔄 Save triggered")
}

</script>
<template>
  <div class="flex space-x-6">
    <!-- LEFT PANEL: Filtry -->
    <div class="w-64 bg-white p-4 rounded shadow space-y-4">
      <h2 class="font-semibold text-gray-700">Filtruj modele paneli</h2>
      <div v-for="model in panelModels" :key="model.id" class="flex items-center space-x-2">
        <input type="checkbox" v-model="selectedModels" :value="model.id"/>
        <label>{{ model.name }}</label>
      </div>

      <h2 class="font-semibold text-gray-700 mt-4">Typ instalacji</h2>
      <div v-for="type in installTypes" :key="type" class="flex items-center space-x-2">
        <input type="checkbox" v-model="selectedTypes" :value="type"/>
        <label>{{ type }}</label>
      </div>

      <h2 class="font-semibold text-gray-700 mt-4">Magazyn (kWh)</h2>
      <div v-for="cap in storageCaps" :key="cap" class="flex items-center space-x-2">
        <input type="checkbox" v-model="selectedStorage" :value="cap"/>
        <label>{{ cap }}</label>
      </div>
    </div>

    <!-- RIGHT PANEL: Tabela -->
    <div class="flex-1 -my-2 overflow-x-auto">
      <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg max-h-[80vh] overflow-y-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0 z-10">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Liczba paneli
              </th>
              <th v-for="panel in filteredPrices[0]?.panels || []" :key="panel.panel_model_id" colspan="2"
                  class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ panel.model }}
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Inwerter
              </th>
              <th v-for="cap in selectedStorage" :key="cap"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Magazyn {{ cap }} kWh
              </th>
            </tr>
            <tr class="bg-gray-100 sticky top-12 z-10">
              <th></th>
              <template v-for="panel in filteredPrices[0]?.panels || []" :key="'sub-' + panel.panel_model_id">
                <th v-if="selectedTypes.includes('String')"
                    class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">String
                </th>
                <th v-if="selectedTypes.includes('Storage')"
                    class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Storage
                </th>
              </template>
              <th></th>
              <th v-for="cap in selectedStorage" :key="'sub-storage-' + cap"></th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="variant in paginatedPrices" :key="variant.variant_id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ variant.panel_count }}
              </td>

              <template v-for="panel in variant.panels" :key="panel.panel_model_id">
                <!-- String zawsze input -->
                <td v-if="selectedTypes.includes('String')" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <input v-model="panel.string_price" type="number" class="border rounded p-1 w-24"/>
                </td>

                <!-- Storage: tylko jeśli panel_count <= 30 -->
                <td v-if="selectedTypes.includes('Storage')" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <template v-if="variant.panel_count <= 30">
                    <input v-model="panel.with_storage_price" type="number" class="border rounded p-1 w-24"/>
                  </template>
                  <template v-else>
                    <span class="text-gray-400">-</span>
                  </template>
                </td>
              </template>

              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <template v-if="selectedTypes.includes('String')">
                  <input v-model="variant.inverter_price" type="number" class="border rounded p-1 w-24"/>
                </template>
                <template v-else>
                  <span class="text-gray-400">-</span>
                </template>
              </td>


              <td v-for="cap in selectedStorage" :key="cap" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <input v-model="variant.storage_prices[cap]" type="number" class="border rounded p-1 w-24"/>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <!-- paginacja i przycisk save -->
        <div class="mt-4 flex justify-between items-center">
          <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                  class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50">
            Prev
          </button>
          <span>Page {{ currentPage }} of {{ totalPages }}</span>
          <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                  class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50">
            Next
          </button>
        </div>

        <div class="mt-4 flex justify-end">
          <button @click="saveAll" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            Save all
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
