<template>
  <Navbar />

  <main class="min-h-screen bg-gray-50 p-6">
<!--   <pre>-->

<!--         {{variants}}-->
<!--   </pre>-->



    <PriceTable
        :panel-models="panelModels"
        :variants="variants"
    />
  </main>
</template>

<script setup>
import Navbar from "@/Components/Navbar.vue";
import PriceTable from "@/Components/PriceTable.vue";

// Propsy Inertia
const props = defineProps({
  panelModels: Array,
  variants: Array,
})

// Rozpakowujemy dla wygody
const { panelModels, variants } = props
</script>
