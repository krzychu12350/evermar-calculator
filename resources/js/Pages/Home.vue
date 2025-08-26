<template>
<!--  <Navbar></Navbar>-->
<!--  <main class="min-h-screen bg-gray-50 p-6">-->
<!--    &lt;!&ndash; slot here &ndash;&gt;-->
<!--    <CalculationForm v-if="authenticated" />-->
<!--    <PasswordModal-->
<!--        :authenticated="authenticated"-->
<!--        @authenticated-change="authenticated = $event"-->
<!--    />-->
<!--  </main>-->
  <MainLayout>
    <CalculatorContainer
    title="Kalkulator instalacji"
    ></CalculatorContainer>

<!--    <div class="flex">-->
<!--      <CalculationForm v-if="authenticated" />-->
<!--      <InvoiceTable></InvoiceTable>-->
<!--    </div>-->
<!--        <PasswordModal-->
<!--            :authenticated="authenticated"-->
<!--            @authenticated-change="authenticated = $event"-->
<!--        />-->
  </MainLayout>
</template>

<script setup lang="ts">
import Navbar from "@/Components/Navbar.vue";
import CalculationForm from "@/Components/Calculations/CalculationForm.vue";
import PasswordModal from "@/Components/PasswordModal.vue";
import CalculatorContainer from "@/Components/Calculations/CalculatorContainer.vue";
import { ref } from "vue";
import MainLayout from "@/Shared/Layouts/MainLayout.vue";
import InvoiceTable from "@/Components/Calculations/Invoice/InvoiceTable.vue";

const authenticated = ref(true);
const showInvoiceTable = ref(false);
</script>
