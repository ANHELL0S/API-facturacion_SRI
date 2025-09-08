<template>
  <button
    :disabled="disabled || isLoading"
    :class="buttonClasses"
    class="px-4 py-2 text-sm font-medium rounded-md shadow-sm flex items-center justify-center transition-colors duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2"
  >
    <svg v-if="isLoading" class="animate-spin -ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <slot v-if="!isLoading" name="icon"></slot>
    <span :class="{'ml-2': hasIcon && !isLoading }">
      <slot></slot> <!-- Default slot for text -->
    </span>
  </button>
</template>

<script>
export default {
  name: 'BaseButton',
  props: {
    variant: {
      type: String,
      default: 'primary', // primary, secondary, danger, success, warning
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    isLoading: {
      type: Boolean,
      default: false,
    }
  },
  computed: {
    hasIcon() {
      return !!this.$slots.icon;
    },
    buttonClasses() {
      const variants = {
        primary: 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
        danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
        warning: 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500',
        secondary: 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200 focus:ring-indigo-500',
      };

      let classes = variants[this.variant] || variants.primary;

      if (this.disabled || this.isLoading) {
        classes += ' disabled:bg-gray-400 disabled:cursor-not-allowed';
      }

      return classes;
    }
  }
}
</script>
