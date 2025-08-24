<template>
  <div>
    <!-- Main Label and Description -->
    <label class="text-base font-medium text-gray-900">{{ label }}</label>
    <p class="text-sm leading-5 text-gray-500">{{ description }}</p>

    <!-- Checkbox Group -->
    <fieldset class="mt-4">
      <legend class="sr-only">Notification Preferences</legend>
      <div class="space-y-4">
        <!-- Loop through options and create checkbox inputs -->
        <div
          v-for="option in options"
          :key="option.value"
          class="relative flex items-start"
        >
          <div class="flex items-center h-5">
            <input
              type="checkbox"
              :id="option.value"
              :value="option.value"
              v-model="selectedValues"
              :disabled="disabled"
              class="focus:ring-yellow-500 h-4 w-4 text-yellow-600 border-gray-300 rounded"
            />
          </div>
          <div class="ml-3 text-sm">
            <!-- Label for the checkbox -->
            <label
              :for="option.value"
              class="font-medium text-gray-700 cursor-pointer hover:text-yellow-600 transition-colors duration-200"
            >
              {{ option.label }}
            </label>
            <!-- Description for the checkbox -->
            <p :id="`${option.value}-description`" class="text-gray-500">
              {{ option.description }}
            </p>
          </div>
        </div>
      </div>
    </fieldset>
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits, ref, watch } from "vue";

// Define the props for the checkbox group
const props = defineProps({
  label: String, // Label for the checkbox group
  description: String, // Description for the checkbox group
  options: {
    type: Array,
    required: true,
  }, // Options for the checkboxes, each option should have `value`, `label`, and `description`
  disabled: {
    type: Boolean,
    default: false,
  }, // Whether the checkboxes are disabled
  modelValue: {
    type: Array,
    required: true,
  }, // The selected values, renamed to `modelValue` for v-model support
});

// Emit the updated values when checkboxes are checked/unchecked
const emit = defineEmits(["update:modelValue"]);

const selectedValues = ref([...props.modelValue]); // Initialize with the selected values from the parent

// Watch for changes in selectedValues and emit them to the parent
watch(selectedValues, (newValues) => {
  emit("update:modelValue", newValues); // Emit the updated selected values when they change
});
</script>
