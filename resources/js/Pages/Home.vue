<template>
  <Navbar></Navbar>
  <main class="min-h-screen bg-white p-6">

    <CalculationForm></CalculationForm>
<!--    <h1 class="text-3xl font-bold mb-6">Panel Variants</h1>-->

<!--    <ul class="space-y-2">-->
<!--      <li-->
<!--          v-for="variant in variants"-->
<!--          :key="variant.id"-->
<!--          class="p-4 bg-white rounded shadow"-->
<!--      >-->
<!--        {{ variant.panel_count }} panels-->
<!--      </li>-->
<!--    </ul>-->
  </main>

</template>

<script setup>
import Navbar from "@/Components/Navbar.vue";
import CalculationForm from "@/Components/CalculationForm.vue";

defineProps({ variants: Object })
</script>
