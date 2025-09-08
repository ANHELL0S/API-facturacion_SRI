<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-40"
       :class="isSidebarOpen ? 'ml-64' : 'ml-20'"
       @click.self="close">
    <div class="relative shadow-lg rounded-md bg-white w-11/12 md:w-3/4 lg:w-4/5 flex flex-col" style="height: 90vh;">
      <!-- Modal Header -->
      <div class="flex justify-between items-center p-4 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Previsualización de PDF</h3>
        <button @click="close" class="p-2 text-gray-600 hover:text-gray-900 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="flex-1 p-2 bg-gray-200">
        <iframe v-if="pdfUrl" :src="pdfUrl" class="w-full h-full" frameborder="0"></iframe>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PdfPreviewModal',
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    pdfUrl: {
      type: String,
      default: null,
    },
    isSidebarOpen: {
        type: Boolean,
        default: false,
    }
  },
  methods: {
    close() {
      this.$emit('close');
    }
  }
};
</script>
