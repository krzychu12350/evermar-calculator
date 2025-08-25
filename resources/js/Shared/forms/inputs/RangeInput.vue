<template>
  <div class="col-span-4 space-y-2">
    <label class="block text-sm font-medium text-gray-700">{{ label }}</label>
    <div class="flex items-center gap-4">
      <input
          type="number"
          v-model.number="internalValue.from"
          @input="updateParent"
          class="w-20 border rounded px-2 py-1"
          :min="min"
          :max="max"
      />
      <span>-</span>
      <input
          type="number"
          v-model.number="internalValue.to"
          @input="updateParent"
          class="w-20 border rounded px-2 py-1"
          :min="min"
          :max="max"
      />
    </div>
<!--    <div class="text-sm text-gray-500">-->
<!--      Zakres: {{ internalValue.from }}{{ unit }} - {{ internalValue.to }}{{ unit }}-->
<!--    </div>-->
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits, reactive, watch } from "vue";

const props = defineProps({
  label: String,
  min: { type: Number, default: 0 },
  max: { type: Number, default: 100 },
  value: { type: Object, required: true }, // { from: number, to: number }
  unit: { type: String, default: "" },
});

const emit = defineEmits(["update:value"]);

const internalValue = reactive({ from: props.value.from, to: props.value.to });

// Keep internal value synced if parent changes
watch(
    () => props.value,
    (val) => {
      internalValue.from = val.from;
      internalValue.to = val.to;
    }
);

const updateParent = () => {
  // Ensure from <= to
  if (internalValue.from > internalValue.to) internalValue.to = internalValue.from;
  emit("update:value", { ...internalValue });
};
</script>

<style scoped>
/* Only number inputs styling, no sliders */
input[type="number"] {
  appearance: none;
  text-align: center;
}
</style>
