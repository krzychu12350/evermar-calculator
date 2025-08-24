<template>
  <button :type="type" :class="buttonClass" >
    <slot>{{ label }}</slot>
  </button>
</template>

<script setup lang="ts">
import { defineProps } from "vue";

const props = defineProps({
  label: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    default: "button",
  },
  buttonClass: {
    type: String,
    default:
      "cursor-pointer bg-green-500 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-green-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900",
  },
});

const handleClick = () => {
  // You can emit custom events if necessary
};
</script>
