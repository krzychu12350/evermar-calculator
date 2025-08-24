<template>
  <MainLayout>
<!--    <SidebarFilter-->
<!--        title="Modele paneli"-->
<!--        :filters="panelFilters"-->
<!--        @apply-filters="handleApplyFilters"-->
<!--    >-->
<!--   -->
<!--      <AbstractTable-->
<!--          v-if="panels.data.length > 0"-->
<!--          :data="panels.data"-->
<!--          :columns="columns"-->
<!--          :pagination="pagination"-->
<!--          :sortable="true"-->
<!--      >-->
<!--       -->
<!--      </AbstractTable>-->
<!--    </SidebarFilter>-->
    Varaiants
  </MainLayout>
</template>

<script setup lang="ts">
import MainLayout from "@/Layouts/MainLayout.vue"; // <-- import layout
import SidebarFilter from "@/Shared/abstract/FilterContainer.vue";
import AbstractTable from "@/Shared/tabels/AbstractTable.vue";

</script>
