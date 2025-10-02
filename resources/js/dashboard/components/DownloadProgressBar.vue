<template>
  <div class="w-72 bg-white shadow-lg rounded-lg p-4">
    <div v-if="job.status === 'compressing'">
      <div class="flex justify-between items-center mb-2">
        <span class="text-sm font-medium text-gray-700">Comprimiendo...</span>
        <span class="text-xs text-gray-500">{{ job.total_files }} archivos</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
        <!-- Indeterminate progress bar -->
        <div class="bg-blue-600 h-2.5 w-1/2 animate-pulse"></div>
      </div>
    </div>
    <div v-else>
      <div class="flex justify-between items-center mb-2">
        <span class="text-sm font-medium text-gray-700">Descargando {{ job.format.toUpperCase() }}...</span>
        <span class="text-xs text-gray-500">{{ job.processed_files }} / {{ job.total_files }}</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2.5">
        <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: progress + '%' }"></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DownloadProgressBar',
  props: {
    job: {
      type: Object,
      required: true,
    },
  },
  computed: {
    progress() {
      if (!this.job.total_files || this.job.total_files === 0) return 0;
      return (this.job.processed_files / this.job.total_files) * 100;
    },
  },
};
</script>
