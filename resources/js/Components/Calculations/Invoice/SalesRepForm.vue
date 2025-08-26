<template>
  <div class="">
    <BaseForm
        title="Dane przedstawiciela sprzedaży"
        description="Wprowadź imię, nazwisko, telefon i e-mail"
        @submit.prevent="submitForm"
    >
      <CustomInput
          v-model="form.name"
          label="Imię i nazwisko"
          name="name"
          placeholder="Jan Kowalski"
          :error="errors.name"
      />
      <CustomInput
          v-model="form.phone"
          label="Telefon"
          name="phone"
          placeholder="600 123 456"
          :error="errors.phone"
      />
      <CustomInput
          v-model="form.email"
          label="E-mail"
          name="email"
          type="email"
          placeholder="jan.kowalski@gmail.com"
          :error="errors.email"
      />
    </BaseForm>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive } from "vue";
import * as yup from "yup";
import BaseForm from "@/Shared/forms/BaseForm.vue";
import CustomInput from "@/Shared/forms/inputs/CustomInput.vue";
import { useEventable } from "@/Shared/utilities/eventBus";

const { watchEvent } = useEventable();

const props = defineProps<{
  onNext: (data: any) => void;
}>();

// Reactive form data
const form = reactive({
  name: "Jan Kowalski",
  phone: "600 123 456",
  email: "jan.kowalski@gmail.com",
});

// Errors object
const errors = reactive({
  name: "",
  phone: "",
  email: "",
});

// Validation schema
const schema = yup.object({
  name: yup.string().required("Imię i nazwisko jest wymagane"),
  phone: yup.string().required("Telefon jest wymagany"),
  email: yup.string().email("Nieprawidłowy email").required("Email jest wymagany"),
});

// Submit handler
const submitForm = async () => {
  try {
    errors.name = "";
    errors.phone = "";
    errors.email = "";

    await schema.validate(form, { abortEarly: false });
    props.onNext({ ...form });
  } catch (err: any) {
    if (err.inner) {
      err.inner.forEach((e: any) => {
        errors[e.path] = e.message;
      });
    }
  }
};

onMounted(() => {
  watchEvent("submit-sales-rep-form", submitForm);
});
</script>
