<template>
  <div
    v-if="isLoading"
    class="fixed inset-0 bg-white bg-opacity-80 flex items-center justify-center z-50"
  >
    <div class="flex flex-col justify-center items-center text-center animate-fade-in">
      <img :src="logo" :alt="altText" class="w-90 animate-pulse" />
      <p v-if="loadingText" class="mt-4 text-gray-700 text-lg font-medium animate-pulse">
        {{ loadingText }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, ref, onMounted } from "vue";
import { useEventable } from "@/shared/utilities/eventBus";

const { watchEvent } = useEventable();

// Props for customization
defineProps({
  logo: {
    type: String,
    required: true, // Path to your logo or image
  },
  altText: {
    type: String,
    default: "Loading...", // Alt text for the image
  },
  loadingText: {
    type: String,
    default: null, // Optional loading text (e.g., "Loading, please wait...")
  },
  isLoading: {
    type: Boolean,
    default: false, // Control visibility of the loader
  },
});

const isLoading = ref(false);

const toggleLoader = () => {
  isLoading.value = !isLoading.value;
};

const showLoader = () => {
  isLoading.value = true;
};

const hideLoader = () => {
  isLoading.value = false;
};

onMounted(() => {
  watchEvent("show-logo-loader", showLoader);
  watchEvent("hide-logo-loader", hideLoader);
});
</script>

<style>
/* Add Tailwind animations */
@keyframes fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
.animate-fade-in {
  animation: fade-in 0.25s ease-in-out;
}
</style>
