<template>
  <div
    v-if="message"
    :class="['fixed top-5 right-5 p-4 rounded-lg', typeClass]"
    role="alert"
  >
    <div class="flex">
      <div class="ml-3 text-sm font-medium text-white">{{ message }}</div>
      <button
        @click="closeNotification"
        class="ml-4 -mx-1.5 -my-1.5 bg-transparent text-white rounded-lg focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-50 focus:ring-white"
      >
        <span class="sr-only">Close</span>
        <svg
          aria-hidden="true"
          class="w-5 h-5"
          fill="currentColor"
          viewBox="0 0 20 20"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fill-rule="evenodd"
            d="M6.293 4.293a1 1 0 011.414 0L10 6.586l2.293-2.293a1 1 0 111.414 1.414L11.414 8l2.293 2.293a1 1 0 01-1.414 1.414L10 9.414l-2.293 2.293a1 1 0 01-1.414-1.414L8.586 8 6.293 5.707a1 1 0 010-1.414z"
            clip-rule="evenodd"
          ></path>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { defineEmits, computed } from "vue";

// Emitting close notification event to parent
const emit = defineEmits();

const props = defineProps({
  message: {
    type: String,
    default: "",
  },
  type: {
    type: String,
    default: "success", // or 'error'
  },
});

const typeClass = computed(() =>
  props.type === "error" ? "bg-red-500" : "bg-green-500"
);

const closeNotification = () => {
  // Emit event to parent component to clear message
  emit("close-notification");
};
</script>

<style scoped>
/* Add any custom styles for the notification here */
</style>
