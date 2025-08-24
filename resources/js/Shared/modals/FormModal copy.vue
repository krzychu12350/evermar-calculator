<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
  >
    <div class="bg-white rounded-lg w-full sm:w-96 p-6 shadow-lg">
      <h2 class="text-lg leading-6 font-medium text-gray-900">{{ modalTitle }}</h2>
      <p class="mt-1 text-sm text-gray-500 mb-6">
        {{
          modalTitle === "Edytuj dane użytkownika"
            ? "Zaktualizuj dane użytkownika."
            : "Dodaj nowego użytkownika."
        }}
      </p>

      <form @submit.prevent="handleSubmitForm" class="space-y-6">
        <div v-for="field in fields" :key="field.name" class="mb-4">
          <label :for="field.name" class="block text-sm font-medium text-gray-700">
            {{ field.label }}
          </label>

          <!-- Text and email input types -->
          <div v-if="field.type === 'text' || field.type === 'email'">
            <input
              v-bind="fieldAttrs[field.name]"
              v-model="fieldsValues[field.name]"
              :type="field.type"
              :id="field.name"
              :name="field.name"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-gray-900 focus:border-gray-900 sm:text-sm"
              :disabled="isSubmitting"
            />
            <div v-if="errors[field.name]" class="text-red-500 text-sm mt-1">
              {{ errors[field.name] }}
            </div>
          </div>

          <!-- Select input type -->
          <div v-if="field.type === 'select'">
            <select
              v-bind="fieldAttrs[field.name]"
              v-model="fieldsValues[field.name]"
              :id="field.name"
              :name="field.name"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-gray-900 focus:border-gray-900 sm:text-sm"
              :disabled="isSubmitting"
            >
              <option
                v-for="option in field.options"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
            <div v-if="errors[field.name]" class="text-red-500 text-sm mt-1">
              {{ errors[field.name] }}
            </div>
          </div>
        </div>

        <div class="flex justify-end mt-6">
          <button
            type="button"
            class="bg-gray-500 text-white px-4 py-2 rounded-md mr-2"
            @click="handleClose"
          >
            Anuluj
          </button>
          <button
            type="submit"
            class="bg-yellow-500 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-yellow-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900"
            :disabled="isSubmitting"
          >
            {{ modalConfirmText }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, defineProps, defineEmits, computed, watch, onMounted, ref } from "vue";
import { useForm } from "vee-validate";
import * as yup from "yup";

// Props to receive data from parent component
const props = defineProps<{
  isOpen: boolean;
  title: string;
  user: any;
  actionType: string;
  fields: any[];
  validationSchema?: yup.ObjectSchema<any>;
}>();

const emit = defineEmits<{
  (e: "close"): void;
  (e: "save", updatedUser: any): void;
}>();

// Define form handling with vee-validate
const { errors, validate, handleSubmit, isSubmitting, resetForm, defineField } = useForm({
  initialValues: reactive(() => props.user || {}),
  validationSchema: computed(() => generateValidationSchema(props.fields)),
  validateOnMount: false,
});

// Reactive fields values (with ref for each field)
const fieldsValues = reactive<{ [key: string]: any }>({});
const fieldAttrs: { [key: string]: any } = {}; // Store field attributes for v-bind

const modalTitle = computed(() => {
  return props.actionType === "edit"
    ? "Edytuj dane użytkownika"
    : "Dodaj nowego użytkownika";
});

const modalConfirmText = computed(() => {
  return props.actionType === "edit" ? "Zapisz zmiany" : "Dodaj użytkownika";
});

const handleClose = () => {
  emit("close");
};

const handleSubmitForm = async () => {
  const isValid = await validate(); // Explicitly validate
  if (isValid) {
    emit("save", { ...fieldsValues });
    handleClose();
  } else {
    console.error("Validation errors:", errors.value);
  }
};

onMounted(() => {
  props.fields.forEach((field) => {
    const [fieldValue, fieldAttrsObj] = defineField(field.name);
    fieldsValues[field.name] = ref(fieldValue.value || ""); // Initialize as ref
    fieldAttrs[field.name] = fieldAttrsObj;
  });
});

watch(
  () => props.isOpen,
  (newVal) => {
    if (!newVal) {
      Object.keys(fieldsValues).forEach((key) => (fieldsValues[key].value = "")); // Reset fields when modal closes
      resetForm();
    } else {
      if (props.actionType === "edit" && props.user) {
        // Update fields values when opening the modal in 'edit' mode
        Object.keys(fieldsValues).forEach((key) => {
          fieldsValues[key].value = props.user[key] || "";
        });
      }
    }
  }
);

function generateValidationSchema(fields: any[]) {
  const schema: { [key: string]: any } = {};

  fields.forEach((field) => {
    if (field.required) {
      schema[field.name] = yup.string().required(`${field.label} jest wymagane`);
    } else {
      schema[field.name] = yup.string().notRequired();
    }

    if (field.type === "select" && field.options) {
      schema[field.name] = yup.string().oneOf(field.options.map((opt) => opt.value));
    }
  });

  return yup.object().shape(schema);
}
</script>

<style scoped>
.bg-opacity-50 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
