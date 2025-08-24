<template>
  <navbar />

  <main class="min-h-screen bg-gray-50 sm:p-6">
    <!-- Default slot for page-specific content -->
    <slot />
  </main>
  <logo-loader
      :logo="'https://evermar.net/wp-content/uploads/2025/02/cropped-Evermar_color.png'"
      :altText="'Logo Loader'"
      :loadingText="'Ładowanie, proszę czekać...'"
  />
  <notification-container></notification-container>
</template>

<script setup lang="ts">
import Navbar from "@/Components/Navbar.vue";
import LogoLoader from "@/Shared/loaders/LogoLoader.vue";
import NotificationContainer from "@/Shared/notifications/NotificationContainer.vue";
</script>
