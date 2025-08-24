<template>
  <div class="space-y-2">
    <label class="block text-sm font-medium text-gray-700">{{ label }}</label>
    <div class="mt-2 space-y-2">
      <div v-for="option in options" :key="option.value" class="flex items-center">
        <input
          type="checkbox"
          :id="option.value"
          :value="option.value"
          v-model="selectedValues"
          :disabled="disabled"
          class="mr-2"
        />
        <label :for="option.value" class="text-sm text-gray-600">{{
          option.label
        }}</label>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits, ref, watch } from "vue";

// Define the props for the checkbox group
const props = defineProps({
  label: String, // Label for the checkbox group
  options: {
    type: Array,
    required: true,
  }, // Options for the checkboxes
  disabled: {
    type: Boolean,
    default: false,
  }, // Whether the checkboxes are disabled
  //   value: {
  //     type: Array,
  //     required: true,
  //   }, // The selected values
});

// Emit the updated values when checkboxes are checked/unchecked
const emit = defineEmits(["update:value"]);

const selectedValues = ref([...props.value]);

watch(selectedValues, (newValues) => {
  emit("update:value", newValues); // Emit the updated selected values when they change
});
</script>
