<template>
  <ConfirmationModal
      :isOpen="isOpen"
      title="Usuń panel?"
      message="Czy na pewno chcesz usunąć ten panel? Tej operacji nie można cofnąć."
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
  panelId: number | null;
}>();

const emit = defineEmits<{
  (e: "close"): void;
  (e: "deleted", panelId: number): void;
}>();

const closeModal = () => emit("close");

const confirmDelete = () => {
  if (!props.panelId) return;

  router.delete(route("panels.destroy", { panel: props.panelId }), {
    onSuccess: () => {
      emit("deleted", props.panelId);
      closeModal();
    },
  });
};
</script>
