<template>
  <Navbar></Navbar>
  <main class="min-h-screen bg-white p-6">
    <CalculationForm v-if="authenticated" />
    <PasswordModal
        :authenticated="authenticated"
        @authenticated-change="authenticated = $event"
    />
  </main>
</template>

<script setup>
import Navbar from "@/Components/Navbar.vue";
import CalculationForm from "@/Components/CalculationForm.vue";
import PasswordModal from "@/Components/PasswordModal.vue";
import { ref } from "vue";

const authenticated = ref(true);
</script>
