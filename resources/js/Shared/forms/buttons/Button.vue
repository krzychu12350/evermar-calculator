<template>
  <button
    :class="[
      'px-4 py-2 rounded-md text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-offset-2',
      buttonClass,
      { 'opacity-60 pointer-events-none': isLoading },
    ]"
    :disabled="isLoading || disabled"
    @click="handleClick"
  >
    <slot></slot>
  </button>
</template>

<script lang="ts">
export default {
  name: "Button",
  props: {
    buttonType: {
      type: String,
      default: "primary", // primary, secondary, etc.
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    buttonClass() {
      switch (this.buttonType) {
        case "primary":
          return "bg-blue-500 text-white hover:bg-blue-600 focus:ring-blue-500";
        case "secondary":
          return "bg-gray-500 text-white hover:bg-gray-600 focus:ring-gray-500";
        case "danger":
          return "bg-red-500 text-white hover:bg-red-600 focus:ring-red-500";
        case "success":
          return "bg-green-500 text-white hover:bg-green-600 focus:ring-green-500";
        default:
          return "bg-blue-500 text-white hover:bg-blue-600 focus:ring-blue-500";
      }
    },
  },
  methods: {
    handleClick() {
      if (!this.disabled && !this.isLoading) {
        this.$emit("click");
      }
    },
  },
};
</script>
