<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4" @click.self="close">
    <div class="relative mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl leading-6 font-medium text-gray-900">Descarga por Lotes</h3>
            <button @click="close" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="mt-2 text-sm text-gray-700">
          <p class="mb-4">Puede descargar todos sus comprobantes, o filtrar por un rango de fechas.</p>

          <div class="space-y-4">
            <!-- Filtro por fecha -->
            <div class="mt-4">
              <label class="flex items-center">
                <input type="checkbox" v-model="useDateFilter" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-2 text-gray-800 font-medium">Filtrar por fecha</span>
              </label>
            </div>
            <div v-if="useDateFilter" class="space-y-4 pt-4 border-t">
              <div>
                <label for="fecha_desde_descarga_modal" class="block text-sm font-medium text-gray-700">Desde</label>
                <input type="date" id="fecha_desde_descarga_modal" v-model="filters.fecha_desde" class="mt-1 block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              </div>
              <div>
                <label for="fecha_hasta_descarga_modal" class="block text-sm font-medium text-gray-700">Hasta</label>
                <input type="date" id="fecha_hasta_descarga_modal" v-model="filters.fecha_hasta" class="mt-1 block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              </div>
            </div>

            <!-- Filtro por código de producto -->
            <div class="mt-4">
              <label class="flex items-center">
                <input type="checkbox" v-model="useProductFilter" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-2 text-gray-800 font-medium">Filtrar por código de producto</span>
              </label>
            </div>
            <div v-if="useProductFilter" class="space-y-4 pt-4 border-t">
              <div v-if="isLoadingProductCodes">
                <p>Cargando códigos de producto...</p>
              </div>
              <div v-else>
                <label for="product_code_descarga_modal" class="block text-sm font-medium text-gray-700">Código de Producto</label>
                <input list="product-codes-list" id="product_code_descarga_modal" v-model="filters.product_code" class="mt-1 block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Escriba o seleccione un código">
                <datalist id="product-codes-list">
                  <option v-for="code in productCodes" :key="code" :value="code">
                    {{ code }}
                  </option>
                </datalist>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end space-x-4">
            <BaseButton @click="download('xml')" variant="primary" :disabled="isDownloadDisabled">
                Descargar XML
            </BaseButton>
            <BaseButton @click="download('pdf')" variant="danger" :disabled="isDownloadDisabled">
                Descargar PDF
            </BaseButton>
            <BaseButton @click="close" variant="secondary">
                Cancelar
            </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import BaseButton from './BaseButton.vue';

export default {
  name: 'DownloadModal',
  components: {
    BaseButton,
  },
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    token: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      useDateFilter: false,
      useProductFilter: false,
      filters: {
        fecha_desde: '',
        fecha_hasta: '',
        product_code: '',
      },
      productCodes: [],
      isLoadingProductCodes: false,
    };
  },
  computed: {
    isDownloadDisabled() {
      if (this.useDateFilter) {
        const { fecha_desde, fecha_hasta } = this.filters;
        if (!(fecha_desde && fecha_hasta && new Date(fecha_hasta) >= new Date(fecha_desde))) {
          return true;
        }
      }
      if (this.useProductFilter) {
        if (!this.filters.product_code || !this.filters.product_code.trim()) {
          return true;
        }
      }
      return false;
    },
  },
  methods: {
    close() {
      this.$emit('close');
    },
    download(format) {
      if (this.isDownloadDisabled) return;

      let payload = { format };

      if (this.useDateFilter) {
        payload.fecha_desde = this.filters.fecha_desde;
        payload.fecha_hasta = this.filters.fecha_hasta;
      }

      if (this.useProductFilter) {
        payload.product_code = this.filters.product_code;
      }

      this.$emit('descargar', payload);
      this.close();
    },
    async fetchProductCodes() {
      this.isLoadingProductCodes = true;
      try {
        const response = await axios.get('/api/comprobantes/product-codes', {
          headers: { 'Authorization': `Bearer ${this.token}` }
        });
        this.productCodes = response.data.data;
      } catch (error) {
        console.error('Error fetching product codes:', error);
        // Optionally, show an error to the user
      } finally {
        this.isLoadingProductCodes = false;
      }
    },
  },
  watch: {
    useDateFilter(value) {
      if (!value) {
        this.filters.fecha_desde = '';
        this.filters.fecha_hasta = '';
      }
    },
    useProductFilter(value) {
      if (value) {
        this.fetchProductCodes();
      } else {
        this.filters.product_code = '';
        this.productCodes = [];
      }
    }
  }
};
</script>
