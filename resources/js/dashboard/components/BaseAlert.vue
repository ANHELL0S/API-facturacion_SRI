<template>
  <div
    v-if="show"
    class="fixed top-5 right-5 w-full max-w-sm rounded-lg shadow-lg pointer-events-auto z-50"
    :class="alertClasses"
  >
    <div class="rounded-lg shadow-xs overflow-hidden">
      <div class="p-4">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6" :class="iconClasses" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              <path v-if="type === 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              <path v-if="type === 'info'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3 w-0 flex-1 pt-0.5">
            <p class="text-sm font-medium" :class="messageClasses">{{ message }}</p>
          </div>
          <div class="ml-4 flex-shrink-0 flex">
            <button @click="show = false" class="inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2" :class="buttonFocusClasses">
              <span class="sr-only">Close</span>
              <svg class="h-5 w-5" :class="iconClasses" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BaseAlert',
  data() {
    return {
      show: false,
      message: '',
      type: 'success', // 'success', 'error', or 'info'
      timeout: null,
    };
  },
  computed: {
    alertClasses() {
      return {
        'bg-green-100 border-l-4 border-green-500': this.type === 'success',
        'bg-red-100 border-l-4 border-red-500': this.type === 'error',
        'bg-blue-100 border-l-4 border-blue-500': this.type === 'info',
      };
    },
    iconClasses() {
      return {
        'text-green-500': this.type === 'success',
        'text-red-500': this.type === 'error',
        'text-blue-500': this.type === 'info',
      };
    },
    messageClasses() {
      return {
        'text-green-800': this.type === 'success',
        'text-red-800': this.type === 'error',
        'text-blue-800': this.type === 'info',
      };
    },
    buttonFocusClasses() {
        return {
            'ring-green-500': this.type === 'success',
            'ring-red-500': this.type === 'error',
            'ring-blue-500': this.type === 'info',
        }
    }
  },
  created() {
    this.$emitter.on('show-alert', ({ type, message }) => {
      this.type = type;
      this.message = message;
      this.show = true;

      if (this.timeout) {
        clearTimeout(this.timeout);
      }

      this.timeout = setTimeout(() => {
        this.show = false;
      }, 5000);
    });
  },
  beforeUnmount() {
    this.$emitter.off('show-alert');
    if (this.timeout) {
      clearTimeout(this.timeout);
    }
  },
};
</script>
