<template>
  <div class="col-span-4">
    <label :for="id" class="block text-sm font-medium text-gray-700">{{ label }}</label>
    <input
      v-bind="attrs"
      :v-model="modelValue"
      :value="modelValue"
      @input="updateValue($event)"
      :type="type"
      :id="id"
      :name="name"
      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-gray-900 focus:border-gray-900 sm:text-sm"
    />
    <!-- {{ modelValue }} -->
    <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from "vue";

// Define the props
const props = defineProps({
  label: String,
  id: String,
  name: String,
  type: {
    type: String,
    default: "text",
  },
  modelValue: {
    // Accept modelValue as prop
    type: [String, Number],
    default: "",
  },
  attrs: Object, // Accept additional attributes like validation
  error: String, // Accept error messages from parent
});

// Emit the updated value when the input changes
const emit = defineEmits(["update:modelValue"]);

const updateValue = (event: Event) => {
  const input = event.target as HTMLInputElement;
  emit("update:modelValue", input.value); // Emit the updated value to parent
};
</script>
