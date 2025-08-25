<template>
  <transition
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
  >
    <div
      v-if="visible"
      :class="[
        'sm:max-w-sm shadow-lg rounded-lg pointer-events-auto ring-opacity-5 overflow-hidden',
        notificationClass,
      ]"
    >
      <div class="p-4">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <!-- Notification Icon -->
            <component :is="icon" class="h-6 w-6" aria-hidden="true" />
          </div>
          <div class="ml-3 w-0 flex-1 pt-0.5">
            <p class="text-sm font-medium text-gray-900">{{ title }}</p>
            <p class="mt-1 text-sm md:text-sm text-gray-500">{{ message }}</p>
          </div>
          <div class="ml-4 flex-shrink-0 flex">
            <button
              @click="hide"
              class="rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <span class="sr-only">Close</span>
              <XMarkIcon class="h-5 w-5" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>
<script setup lang="ts">
import { ref, computed, onMounted, defineEmits } from "vue";
import {
  CheckCircleIcon,
  ExclamationCircleIcon,
  XMarkIcon,
} from "@heroicons/vue/24/outline";

const emit = defineEmits(["hide-notification"]);

const props = defineProps({
  title: String,
  message: String,
  type: String,
  timeout: {
    type: Number,
    default: 10000, // 10 seconds
  },
});

const visible = ref(true);
let timeoutId: number;

const hide = () => {
  visible.value = false;
  emit("hide-notification");
};

onMounted(() => {
  timeoutId = window.setTimeout(() => hide(),props.timeout);
});

const icon = computed(() => {
  switch (props.type) {
    case "error":
      return ExclamationCircleIcon;
    case "info":
      return ExclamationCircleIcon;
    case "warning":
      return ExclamationCircleIcon;
    default:
      return CheckCircleIcon;
  }
});

const notificationClass = computed(() => {
  switch (props.type) {
    case "error":
      return "bg-red-50 border-l-4 border-red-400 text-red-800";
    case "info":
      return "bg-gray-50 border-l-4 border-gray-400 text-gray-800";
    case "warning":
      return "bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800";
    default:
      return "bg-white border-l-4 border-green-400 text-green-800";
  }
});
</script>
