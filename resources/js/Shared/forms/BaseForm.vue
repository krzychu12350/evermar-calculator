<template>
  <form @submit.prevent="onSubmit">
    <div class="rounded-lg shadow">
      <div class="bg-white py-6 px-4 sm:p-6 rounded-lg">
        <h2 class="text-lg leading-6 font-medium text-gray-900">{{ title }}</h2>
        <p class="mt-1 text-sm text-gray-500">{{ description }}</p>
        <div class="mt-6 grid grid-cols-1 gap-6">
          <slot></slot>
        </div>
        <!-- Slot for custom form fields -->
      </div>
      <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <CustomButton :label="submitLabel" type="submit" />
      </div>
    </div>
  </form>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from "vue";
import CustomButton from "@/presentation/shared/forms/buttons/CustomButton.vue"; // Fixed missing quote

const props = defineProps({
  title: String,
  description: String,
  submitLabel: String,
});

const emit = defineEmits();

// Handle form submission
const onSubmit = () => {
  // Emit any necessary updates to parent component if needed
  // For example, to notify the parent that the form has been submitted
  // emit("submit");
};
</script>
