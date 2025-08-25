<template>
  <!-- Global notification live region -->
  <div
    aria-live="assertive"
    class="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-start z-10"
  >
    <div class="w-full flex flex-col items-center space-y-4 sm:items-end">
      <!-- Render each notification -->
      <transition-group
        name="notification"
        tag="div"
        class="w-full max-w-[100%] sm:max-w-sm space-y-4"
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <Notification
          v-for="(notification, index) in notifications"
          :key="notification.id"
          :title="notification.title"
          :message="notification.message"
          :type="notification.type"
          :timeout="notification.timeout"
          @hide-notification="removeNotification(notification.id)"
        />
      </transition-group>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import Notification from "./Notification.vue"; // Import the single notification component
import {useEventable} from "@/Shared/utilities/eventBus";

const notifications = ref<
  {
    id: number;
    title: string;
    message: string;
    type: string;
    timeout?: number;
  }[]
>([]);

// Initialize event bus
const { watchEvent } = useEventable();

// Function to add a notification to the list
const showNotification = (data: {
  title: string;
  message: string;
  type: string;
  timeout?: number;
}) => {
  const id = Date.now(); // Unique ID for each notification
  notifications.value.push({
    id,
    title: data.title,
    message: data.message,
    type: data.type,
    timeout: data.timeout,
  });
};

// Function to remove a notification from the list
const removeNotification = (id: number) => {
  notifications.value = notifications.value.filter((n) => n.id !== id);
};

// Watch for the 'show-notification' event and call showNotification function
watchEvent("show-notification", (data: any) => {
  showNotification(data);
});
</script>
