<template>
  <form @submit.prevent="onSubmit">
    <div class="">
<!--      <div class="rounded-lg shadow">-->
      <div class="bg-white">
        <h2 class="text-lg leading-6 font-medium text-gray-900">{{ title }}</h2>
        <p class="mt-1 text-sm text-gray-500">{{ description }}</p>
        <div class="mt-6 grid grid-cols-1 gap-6">
          <slot></slot>
        </div>
        <!-- Slot for custom form fields -->
        <slot name="footer"></slot>
      </div>

      <!-- Conditionally render the submit button -->
      <div v-if="submitLabel" class="text-right pt-6">
        <CustomButton :label="submitLabel" type="submit" />
      </div>
    </div>
  </form>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from "vue";
import CustomButton from "@/Shared/forms/buttons/CustomButton.vue";

const props = defineProps({
  title: String,
  description: String,
  submitLabel: { type: String, default: null }, // Nullable
});

const emit = defineEmits();

// Handle form submission
const onSubmit = () => {
  // emit("submit");
};
</script>
