<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Panel Models</h1>

    <div class="mb-4 flex justify-between items-center">
      <input v-model="search" placeholder="Szukaj paneli..."
             class="border rounded p-2 w-64"/>
      <button @click="addPanel" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Add Panel
      </button>
    </div>

    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
      <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manufacturer</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Power (Wp)</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
      </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
      <tr v-for="panel in filteredPanels" :key="panel.id">
        <td class="px-6 py-4">{{ panel.name }}</td>
        <td class="px-6 py-4">{{ panel.manufacturer || '-' }}</td>
        <td class="px-6 py-4">{{ panel.power_watt }}</td>
        <td class="px-6 py-4 space-x-2">
          <button @click="editPanel(panel)" class="px-2 py-1 bg-yellow-500 text-white rounded">Edit</button>
          <button @click="deletePanel(panel.id)" class="px-2 py-1 bg-red-600 text-white rounded">Delete</button>
        </td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const search = ref('')
const panelModels = ref([
  { id: 1, name: "LONGi 450W", manufacturer: "LONGi", power_watt: 450 },
  { id: 2, name: "JA Solar 400W", manufacturer: "JA Solar", power_watt: 400 },
  { id: 3, name: "Trina Solar 410W", manufacturer: "Trina", power_watt: 410 },
])

const filteredPanels = computed(() =>
    panelModels.value.filter(p => p.name.toLowerCase().includes(search.value.toLowerCase()))
)

function addPanel() { alert("Open add panel modal") }
function editPanel(panel) { alert("Edit panel: " + panel.name) }
function deletePanel(id) { alert("Delete panel id: " + id) }
</script>
