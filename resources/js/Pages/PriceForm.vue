<script setup>
import { ref, computed } from "vue"

const mode = ref("tabela") // "tabela" | "formularz"

const panelModels = ref([
  { id: 1, name: "LONGi 450W" },
  { id: 2, name: "JA Solar 400W" },
  { id: 3, name: "Trina Solar 410W" },
  { id: 4, name: "Canadian Solar 455W" },
])

const installTypes = ref(["String", "Storage"])
const storageCaps = ref([5, 10, 15])

// =====================================================================
// TABELA wariantów (wariant 8–100 paneli, każdy wariant ma swoje ceny)
// =====================================================================
const variants = ref(
    Array.from({ length: 93 }, (_, i) => {
      const panelCount = i + 8
      return {
        id: i + 1,
        panel_count: panelCount,
        panels: panelModels.value.map(m => ({
          panel_model_id: m.id,
          model: m.name,
          prices: {
            String: (400 + panelCount * 2).toFixed(2),
            Storage: (420 + panelCount * 2.2).toFixed(2)
          }
        })),
        inverterHybridPrice: (5000 + panelCount * 15).toFixed(2),
        storagePrices: storageCaps.value.reduce((acc, cap, idx) => {
          acc[cap] = (8000 + panelCount * (5 + idx)).toFixed(2)
          return acc
        }, {})
      }
    })
)

// paginacja
const currentPage = ref(1)
const itemsPerPage = 10
const totalPages = computed(() => Math.ceil(variants.value.length / itemsPerPage))

const paginatedVariants = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return variants.value.slice(start, start + itemsPerPage)
})

function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) currentPage.value = page
}

function saveTable() {
  console.log("💾 Save table", variants.value)
}

// =====================================================================
// FORMULARZ GLOBALNY (ceny niezależne od wariantu)
// =====================================================================
const globalConfig = ref({
  panels: panelModels.value.map(m => ({
    id: m.id,
    name: m.name,
    prices: {
      String: "",
      Storage: ""
    }
  })),
  inverterHybridPrice: "",
  storagePrices: {
    "5": "",
    "10": "",
    "15": ""
  }
})

function saveGlobal() {
  console.log("💾 Save global config", globalConfig.value)
}
</script>

<template>
  <div class="space-y-6">
    <!-- PRZEŁĄCZNIK TRYBÓW -->
    <div class="flex space-x-4">
      <button
          @click="mode = 'tabela'"
          :class="['px-4 py-2 rounded', mode==='tabela' ? 'bg-blue-600 text-white' : 'bg-gray-200']"
      >
        Tryb tabela
      </button>
      <button
          @click="mode = 'formularz'"
          :class="['px-4 py-2 rounded', mode==='formularz' ? 'bg-blue-600 text-white' : 'bg-gray-200']"
      >
        Tryb formularz
      </button>
    </div>

    <!-- ================================================================= -->
    <!-- TRYB FORMULARZA -->
    <!-- ================================================================= -->
    <div v-if="mode==='formularz'" class="bg-white p-6 rounded shadow space-y-6">
      <h2 class="text-lg font-semibold text-gray-700">Konfiguracja globalna cen</h2>

      <!-- PANELE -->
      <div>
        <h3 class="font-medium text-gray-600 mb-2">Ceny paneli</h3>
        <div class="grid grid-cols-3 gap-4">
          <div v-for="panel in globalConfig.panels" :key="panel.id" class="border rounded p-4 space-y-2">
            <p class="font-semibold">{{ panel.name }}</p>
            <label class="block text-sm text-gray-500">
              String:
              <input v-model="panel.prices.String" type="number" class="mt-1 border rounded p-1 w-full"/>
            </label>
            <label class="block text-sm text-gray-500">
              Storage:
              <input v-model="panel.prices.Storage" type="number" class="mt-1 border rounded p-1 w-full"/>
            </label>
          </div>
        </div>
      </div>

      <!-- INWERTER -->
      <div>
        <h3 class="font-medium text-gray-600 mb-2">Cena inwertera hybrydowego</h3>
        <input v-model="globalConfig.inverterHybridPrice" type="number" class="border rounded p-1 w-48"/>
      </div>

      <!-- MAGAZYNY -->
      <div>
        <h3 class="font-medium text-gray-600 mb-2">Ceny magazynów</h3>
        <div class="flex space-x-6">
          <div v-for="cap in storageCaps" :key="cap" class="flex flex-col">
            <label class="text-sm text-gray-500">Magazyn {{ cap }} kWh</label>
            <input v-model="globalConfig.storagePrices[cap]" type="number" class="border rounded p-1 w-32"/>
          </div>
        </div>
      </div>

      <div class="flex justify-end">
        <button @click="saveGlobal" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
          Zapisz konfigurację
        </button>
      </div>
    </div>

    <!-- ================================================================= -->
    <!-- TRYB TABELI -->
    <!-- ================================================================= -->
    <div v-if="mode==='tabela'" class="bg-white p-6 rounded shadow">
      <h2 class="text-lg font-semibold text-gray-700 mb-4">Tabela wariantów cen</h2>

      <div class="overflow-x-auto max-h-[70vh] overflow-y-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50 sticky top-0 z-10">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Liczba paneli</th>
            <th v-for="panel in panelModels" :key="panel.id" colspan="2"
                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
              {{ panel.name }}
            </th>
            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Inwerter hybrydowy</th>
            <th v-for="cap in storageCaps" :key="cap" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">
              Magazyn {{ cap }} kWh
            </th>
          </tr>
          <tr class="bg-gray-100 sticky top-10 z-10">
            <th></th>
            <template v-for="panel in panelModels" :key="'sub-' + panel.id">
              <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">String</th>
              <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Storage</th>
            </template>
            <th></th>
            <th v-for="cap in storageCaps" :key="'sub-storage-' + cap"></th>
          </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="variant in paginatedVariants" :key="variant.id">
            <td class="px-6 py-4 text-sm font-medium text-gray-900">
              {{ variant.panel_count }}
            </td>
            <template v-for="panel in variant.panels" :key="panel.panel_model_id">
              <td class="px-6 py-4">
                <input v-model="panel.prices.String" type="number" class="border rounded p-1 w-24"/>
              </td>
              <td class="px-6 py-4">
                <input v-model="panel.prices.Storage" type="number" class="border rounded p-1 w-24"/>
              </td>
            </template>
            <td class="px-6 py-4">
              <input v-model="variant.inverterHybridPrice" type="number" class="border rounded p-1 w-24"/>
            </td>
            <td v-for="cap in storageCaps" :key="cap" class="px-6 py-4">
              <input v-model="variant.storagePrices[cap]" type="number" class="border rounded p-1 w-24"/>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- paginacja -->
      <div class="mt-4 flex justify-between items-center">
        <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50">
          Prev
        </button>
        <span>Page {{ currentPage }} of {{ totalPages }}</span>
        <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50">
          Next
        </button>
      </div>

      <div class="mt-4 flex justify-end">
        <button @click="saveTable" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
          Save all
        </button>
      </div>
    </div>
  </div>
</template>
