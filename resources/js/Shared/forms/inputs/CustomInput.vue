<template>
  <div class="col-span-4">
    <label :for="id" class="block text-sm font-medium text-gray-700">{{ label }}</label>
    <input
        v-bind="attrs"
        :value="modelValue"
        @input="updateValue($event)"
        :type="type"
        :id="id"
        :name="name"
        :placeholder="placeholder"
        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-gray-900 focus:border-gray-900 sm:text-sm"
    />
    <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from "vue";

// Define props including optional placeholder
const props = defineProps({
  label: String,
  id: String,
  name: String,
  type: {
    type: String,
    default: "text",
  },
  modelValue: {
    type: [String, Number],
    default: "",
  },
  attrs: Object, // Additional attributes
  error: String, // Validation error
  placeholder: {
    type: String,
    default: "", // Optional
  },
});

const emit = defineEmits(["update:modelValue"]);

const updateValue = (event: Event) => {
  const input = event.target as HTMLInputElement;
  emit("update:modelValue", input.value);
};
</script>
