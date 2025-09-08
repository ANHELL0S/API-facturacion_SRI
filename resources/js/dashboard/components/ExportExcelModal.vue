<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4" @click.self="$emit('close')">
    <div class="relative mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl leading-6 font-medium text-gray-900">Exportar a Excel</h3>
            <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="mt-2 text-sm text-gray-700">
          <p class="mb-4">Seleccione un rango de fechas para exportar o descargue todos los comprobantes autorizados.</p>
          <div class="flex items-center space-x-4 mb-4">
              <div class="flex-1">
                  <label for="fecha_desde_export_modal" class="block text-sm font-medium text-gray-700">Desde</label>
                  <input type="date" id="fecha_desde_export_modal" v-model="fecha_desde" class="mt-1 block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              </div>
              <div class="flex-1">
                  <label for="fecha_hasta_export_modal" class="block text-sm font-medium text-gray-700">Hasta</label>
                  <input type="date" id="fecha_hasta_export_modal" v-model="fecha_hasta" class="mt-1 block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end space-x-4">
          <button @click="exportByDate" type="button" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700" :disabled="!fecha_desde || !fecha_hasta">
            Descargar por Fecha
          </button>
          <button @click="exportAll" type="button" class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700">
            Descargar Todo
          </button>
          <button @click="$emit('close')" type="button" class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-md hover:bg-gray-300">
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ExportExcelModal',
  props: {
    show: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      fecha_desde: '',
      fecha_hasta: '',
    };
  },
  methods: {
    exportByDate() {
      if (this.fecha_desde && this.fecha_hasta) {
        this.$emit('export-by-date', { from: this.fecha_desde, to: this.fecha_hasta });
        this.$emit('close');
      }
    },
    exportAll() {
      this.$emit('export-all');
      this.$emit('close');
    },
  },
};
</script>
