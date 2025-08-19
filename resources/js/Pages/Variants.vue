<template>
  <div class="p-6 flex space-x-6">
    <!-- Sidebar filtrów -->
    <div class="w-64 border p-4 rounded bg-gray-50">
      <h2 class="font-bold mb-2">Filters</h2>
      <div class="mb-4">
        <label class="block mb-1">Panel Models:</label>
        <div v-for="model in panelModels" :key="model.id">
          <input type="checkbox" v-model="selectedModels" :value="model.id" /> {{ model.name }}
        </div>
      </div>

      <div class="mb-4">
        <label class="block mb-1">Install Types:</label>
        <div><input type="checkbox" v-model="selectedTypes" value="string" /> String</div>
        <div><input type="checkbox" v-model="selectedTypes" value="with_storage" /> Storage</div>
      </div>

      <div>
        <label class="block mb-1">Storage Capacity:</label>
        <div v-for="cap in [5,10,15]" :key="cap">
          <input type="checkbox" v-model="selectedStorage" :value="cap" /> {{ cap }} kWh
        </div>
      </div>
    </div>

    <!-- Lista wariantów -->
    <div class="flex-1">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Variants</h1>
        <button @click="addVariant" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
          Add Variant
        </button>
      </div>

      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"># Panels</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Models</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Install Types</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Storage</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="variant in filteredVariants" :key="variant.id">
          <td class="px-6 py-4">{{ variant.panel_count }}</td>
          <td class="px-6 py-4">{{ variant.models.map(m => m.name).join(', ') }}</td>
          <td class="px-6 py-4">{{ variant.install_types.join(', ') }}</td>
          <td class="px-6 py-4">{{ variant.storages.join(', ') }}</td>
          <td class="px-6 py-4 space-x-2">
            <button @click="editVariant(variant)" class="px-2 py-1 bg-yellow-500 text-white rounded">Edit</button>
            <button @click="deleteVariant(variant.id)" class="px-2 py-1 bg-red-600 text-white rounded">Delete</button>
          </td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const panelModels = ref([
  { id: 1, name: "LONGi 450W" },
  { id: 2, name: "JA Solar 400W" },
  { id: 3, name: "Trina Solar 410W" },
])

const variants = ref([
  { id: 1, panel_count: 8, models: [panelModels.value[0]], install_types: ['string'], storages: [5,10] },
  { id: 2, panel_count: 10, models: [panelModels.value[1], panelModels.value[2]], install_types: ['string','with_storage'], storages: [5,15] },
])

// filtry
const selectedModels = ref(panelModels.value.map(m=>m.id))
const selectedTypes = ref(['string','with_storage'])
const selectedStorage = ref([5,10,15])

const filteredVariants = computed(() =>
    variants.value.filter(v =>
        v.models.some(m => selectedModels.value.includes(m.id)) &&
        v.install_types.some(t => selectedTypes.value.includes(t)) &&
        v.storages.some(s => selectedStorage.value.includes(s))
    )
)

function addVariant() { alert('Add new variant') }
function editVariant(v) { alert('Edit variant: ' + v.id) }
function deleteVariant(id) { alert('Delete variant: ' + id) }
</script>
