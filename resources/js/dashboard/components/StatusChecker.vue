<template>
  <div class="p-4 border rounded-lg shadow-sm bg-white">
    <h3 class="text-xl font-semibold mb-3 text-gray-700">Verificar Estado de Factura</h3>
    <form @submit.prevent="checkStatus" class="flex flex-col sm:flex-row gap-2">
      <input
        type="text"
        v-model="accessKey"
        placeholder="Ingrese la Clave de Acceso de 49 dígitos"
        maxlength="49"
        class="flex-grow w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
      >
      <button
        type="submit"
        :disabled="loading || accessKey.length !== 49"
        class="px-4 py-2 bg-gray-600 text-white font-medium text-sm rounded-lg shadow-md hover:bg-gray-700 disabled:bg-gray-400"
      >
        {{ loading ? 'Verificando...' : 'Verificar' }}
      </button>
    </form>
    <div v-if="result" class="mt-4 p-3 rounded-md" :class="result.isError ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'">
      <p><strong>Estado:</strong> {{ result.status }}</p>
      <p v-if="result.message" class="text-sm">{{ result.message }}</p>
      <p v-if="result.status === 'autorizado'" class="text-sm mt-1 text-green-800">Descargando PDF...</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'StatusChecker',
  props: {
      token: {
          type: String,
          required: true,
      }
  },
  data() {
    return {
      accessKey: '',
      loading: false,
      result: null, // { status: '...', message: '...', isError: false }
    };
  },
  methods: {
    async checkStatus() {
      if (this.accessKey.length !== 49) return;

      this.loading = true;
      this.result = null;

      try {
        const response = await axios.get(`/api/comprobantes/${this.accessKey}/estado`, {
          headers: { 'Authorization': `Bearer ${this.token}` },
        });

        this.result = {
          status: response.data.data.estado,
          message: response.data.message,
          isError: false,
        };

        if (this.result.status === 'autorizado') {
            this.downloadPdf();
        }

      } catch (error) {
        let status = 'Error';
        let message = 'No se pudo verificar el estado.';
        if (error.response) {
            status = `Error ${error.response.status}`;
            message = error.response.data.message || message;
        }
        this.result = {
          status: status,
          message: message,
          isError: true,
        };
        console.error('Status check error:', error);
      } finally {
        this.loading = false;
      }
    },
    async downloadPdf() {
        if (!this.accessKey || this.result?.status !== 'autorizado') return;

        try {
            const response = await axios.get(`/api/comprobantes/${this.accessKey}/pdf`, {
                headers: { 'Authorization': `Bearer ${this.token}` },
                responseType: 'blob', // Important for handling file downloads
            });

            const blob = new Blob([response.data], { type: 'application/pdf' });
            const url = window.URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `factura-${this.accessKey}.pdf`;

            document.body.appendChild(a);
            a.click();

            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

        } catch (error) {
            this.$emitter.emit('show-alert', { type: 'error', message: 'Error al descargar el PDF. Por favor, revisa la consola.' });
            console.error('PDF download error:', error);
        }
    },
  },
};
</script>
