<template>
  <main class="min-h-screen bg-gray-50 p-6">
    <h1 class="text-3xl font-bold mb-6">Panel Variants</h1>

    <ul class="space-y-2">
      <li
          v-for="variant in variants"
          :key="variant.id"
          class="p-4 bg-white rounded shadow"
      >
        {{ variant.panel_count }} panels
      </li>
    </ul>
  </main>
</template>

<script setup>
defineProps({ variants: Object })
</script>
