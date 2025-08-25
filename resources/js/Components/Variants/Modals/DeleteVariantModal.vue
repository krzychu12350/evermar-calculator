<template>
  <ConfirmationModal
      :isOpen="isOpen"
      title="Usuń wariant?"
      message="Czy na pewno chcesz usunąć ten wariant? Tej operacji nie można cofnąć."
      confirmText="Usuń"
      cancelText="Anuluj"
      @close="closeModal"
      @confirm="confirmDelete"
  />
</template>

<script setup lang="ts">
import ConfirmationModal from "@/Shared/modals/ConfirmationModal.vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps<{
  isOpen: boolean;
  variantId: number | null;
}>();

const emit = defineEmits<{
  (e: "close"): void;
  (e: "deleted", variantId: number): void;
}>();

const closeModal = () => emit("close");

const confirmDelete = () => {
  if (!props.variantId) return;

  router.delete(route("variants.destroy", { variant: props.variantId }), {
    onSuccess: () => {
      emit("deleted", props.variantId);
      closeModal();
    },
  });
};
</script>
