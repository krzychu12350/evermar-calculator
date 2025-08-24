<template>
  <div>
    <label class="text-base font-medium text-gray-900">{{ label }}</label>
    <p class="text-sm leading-5 text-gray-500">{{ description }}</p>
    <fieldset class="mt-4">
      <legend class="sr-only">Notification method</legend>
      <div class="space-y-4 sm:flex sm:items-center sm:space-y-0 sm:space-x-10">
        <!-- Loop through notification options -->
        <div
          v-for="notificationMethod in options"
          :key="notificationMethod.value"
          class="flex items-center"
        >
          <input
            :id="notificationMethod.value"
            type="radio"
            :value="notificationMethod.value"
            :name="name"
            v-model="selectedValue"
            class="h-4 w-4 text-yellow-600 border-gray-300 focus:ring-yellow-500"
          />
          <label
            :for="notificationMethod.value"
            class="ml-3 text-sm font-medium text-gray-700 cursor-pointer hover:text-yellow-600 transition-colors duration-200"
          >
            {{ notificationMethod.label }}
          </label>
        </div>
      </div>
    </fieldset>
  </div>
</template>

<script setup lang="ts">
import { ref, defineProps, defineEmits, watch } from "vue";

const props = defineProps({
  label: String,
  description: String,
  name: String,
  options: {
    type: Array,
    required: true,
  },
  modelValue: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(["update:modelValue"]);

const selectedValue = ref(props.modelValue);

// Watch selectedValue and emit change to the parent
watch(selectedValue, (newVal) => {
  emit("update:modelValue", newVal);
});
</script>
