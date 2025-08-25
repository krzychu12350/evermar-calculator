<template>
  <BaseModalForm
      :isOpen="props.isOpen"
      title="Dodaj model panelu"
      message="Wprowadź dane dotyczące panelu fotowoltaicznego"
      confirmText="Zatwierdź"
      cancelText="Anuluj"
      @close="closeModal"
  >
    <template #icon>
      <div
          class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10"
      >
        <PlusIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
      </div>
    </template>

    <template #default>
      <form @submit="onSubmit" class="space-y-6">
        <CustomInput
            id="name"
            name="name"
            label="Nazwa modelu"
            v-model="name"
            :attrs="nameAttrs"
            :error="errors.name"
        />

        <CustomInput
            id="manufacturer"
            name="manufacturer"
            label="Producent"
            v-model="manufacturer"
            :attrs="manufacturerAttrs"
            :error="errors.manufacturer"
        />

        <CustomInput
            id="power_watt"
            name="power_watt"
            label="Moc panelu (Wp)"
            type="number"
            v-model="powerWatt"
            :attrs="powerWattAttrs"
            :error="errors.power_watt"
        />

        <!-- Submit / Cancel Buttons -->
        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
          <button
              type="submit"
              class="w-full inline-flex justify-center rounded-md border border-transparent
                   shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white
                   hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2
                   focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Zatwierdź
          </button>
          <button
              type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300
                   shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700
                   hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2
                   focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
              @click="closeModal"
          >
            Anuluj
          </button>
        </div>
      </form>
    </template>
  </BaseModalForm>
</template>

<script setup lang="ts">
import { ref, defineProps, defineEmits } from "vue";
import BaseModalForm from "@/Shared/modals/BaseModalForm.vue";
import CustomInput from "@/Shared/forms/inputs/CustomInput.vue";
import { useForm } from "vee-validate";
import * as yup from "yup";
import { PlusIcon } from "@heroicons/vue/20/solid";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useEventable } from "@/Shared/utilities/eventBus";

const { emit } = useEventable();
const props = defineProps<{ isOpen: boolean }>();
const emiter = defineEmits<{
  (e: "close"): void;
  (e: "confirm", newPanel: object): void;
}>();

const closeModal = () => emiter("close");

// Vee-Validate Form
const { errors, defineField, handleSubmit, resetForm } = useForm({
  validationSchema: yup.object({
    name: yup.string().required("Nazwa modelu jest wymagana"),
    manufacturer: yup.string().nullable(),
    power_watt: yup
        .number()
        .typeError("Moc musi być liczbą")
        .min(100, "Minimalna moc to 100W")
        .max(1000, "Maksymalna moc to 1000W")
        .required("Moc jest wymagana"),
  }),
});

const [name, nameAttrs] = defineField("name");
const [manufacturer, manufacturerAttrs] = defineField("manufacturer");
const [powerWatt, powerWattAttrs] = defineField("power_watt");

// Form Submit
const onSubmit = handleSubmit(async (values) => {
  closeModal();
  emit("show-logo-loader");

  console.log("Submitting panel:", values);


  router.post(route("panels.store"), values, {
    onSuccess: () => {
      resetForm();
      emiter("confirm", values);
      emit("hide-logo-loader");
      emit("reload-panels");
      emit("show-notification", {
        title: "Sukces!",
        message: "Model panelu został dodany pomyślnie!",
        type: "success",
        timeout: 2000,
      });
    },
    onError: (errors) => console.error("Form errors:", errors),
  });
});
</script>
