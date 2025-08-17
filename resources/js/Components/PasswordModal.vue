<template>
  <div v-if="!authenticated" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
    <div class="bg-white p-6 rounded shadow-md w-full max-w-sm">
      <h2 class="text-xl font-semibold mb-4">Podaj hasło dostępowe</h2>
      <div class="relative mb-4">
        <input
            :type="showPassword ? 'text' : 'password'"
            v-model="form.password"
            placeholder="Hasło"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
        />
        <button
            type="button"
            @click="showPassword = !showPassword"
            class="absolute right-3 top-2.5 text-gray-500"
        >
          <!-- Eye icon -->
          <svg
              v-if="!showPassword"
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>

          <!-- Eye slash icon when visible -->
          <svg
              v-else
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.055 10.055 0 012.519-4.316M6.1 6.1l11.8 11.8M17.9 17.9A10.05 10.05 0 0012 19c-4.477 0-8.268-2.943-9.542-7 1.007-3.208 3.52-5.755 6.466-6.837" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </button>
      </div>

      <div class="flex justify-end space-x-2">
        <button
            @click="submitPassword"
            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
        >
          Sprawdź
        </button>
      </div>

      <p v-if="form.errors.password" class="text-red-500 mt-2">{{ form.errors.password }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from "vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
  authenticated: Boolean
});

const emit = defineEmits(["authenticated-change"]);

const form = useForm({
  password: ""
});

const showPassword = ref(false);

const submitPassword = () => {
  form.post("/login-password", {
    onSuccess: () => {
      emit("authenticated-change", true);
      form.reset("password");
    },
    onError: () => {
      form.setError("password", form.errors.password);
    }
  });
};
</script>
