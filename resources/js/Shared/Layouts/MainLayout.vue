<template>
  <template v-if="authenticated">
    <navbar/>

    <main class="min-h-screen bg-gray-50 sm:p-12 rounded-lg shadow-lg">
      <!-- Default slot for page-specific content -->
      <slot/>
    </main>
    <logo-loader
        :logo="'https://evermar.net/wp-content/uploads/2025/02/cropped-Evermar_color.png'"
        :altText="'Logo Loader'"
        :loadingText="'Ładowanie, proszę czekać...'"
    />
    <notification-container></notification-container>
  </template>

  <PasswordModal
      :authenticated="authenticated"
      @authenticated-change="authenticated = $event"
  />
  <!--
  1.it needs pinia store to perserve user state (auth or not)
  2.add caclulator step form state into the pinia store
  3. upgrade first step caclulation and price configuration by using abstract components
  4. send in homecontroller index method data for first step calucalator form
  5. hide pagination if there is no a least minimal 10 records in a table
  6. convert app into vite pwa
  7. secure generated pdf for max single a4 size
  8. improve all small ui details like colors consistency and size
  -->

</template>

<script setup lang="ts">
import Navbar from "@/Components/Navbar.vue";
import LogoLoader from "@/Shared/loaders/LogoLoader.vue";
import NotificationContainer from "@/Shared/notifications/NotificationContainer.vue";
import PasswordModal from "@/Components/PasswordModal.vue";
import {ref} from "vue";


const authenticated = ref(true);
</script>
