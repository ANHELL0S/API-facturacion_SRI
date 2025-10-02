<template>
    <div>
        <!-- Signature Status View -->
        <div v-if="isSignatureValid && !showForm" class="p-4 bg-gray-50 rounded-lg text-center border border-green-200">
            <svg class="mx-auto h-12 w-12 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">Firma Electrónica Cargada</h3>
            <p class="mt-1 text-sm text-gray-600">
            La firma es válida y expira el: <span class="font-semibold">{{ formattedExpiresAt }}</span>
            </p>
            <BaseButton @click="showForm = true" variant="secondary" class="mt-4">Reemplazar Firma</BaseButton>
        </div>

        <!-- Upload Form View -->
        <form v-else @submit.prevent="uploadSignature" class="space-y-4">
          <div>
            <label for="signatureFile" class="block text-sm font-medium text-gray-700">Archivo de Firma (.p12)</label>
            <input type="file" @change="handleFileChange" id="signatureFile" required accept=".p12" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
          </div>
          <div>
            <label for="signaturePassword" class="block text-sm font-medium text-gray-700">Contraseña de la Firma</label>
            <input type="password" v-model="password" id="signaturePassword" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
          </div>
           <div class="flex justify-end">
             <BaseButton @click="uploadSignature" :is-loading="loading" variant="primary">
                {{ isSignatureValid ? 'Reemplazar' : 'Cargar Firma' }}
             </BaseButton>
           </div>
        </form>
    </div>
</template>

<script>
import axios from 'axios';
import BaseButton from './BaseButton.vue';

export default {
  name: 'SignatureManager',
  components: { BaseButton },
  props: {
    client: {
      type: Object,
      required: true,
    },
    token: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      file: null,
      password: '',
      loading: false,
      showForm: false,
    };
  },
  computed: {
    isSignatureValid() {
        return this.client.signature_expires_at && new Date(this.client.signature_expires_at) > new Date();
    },
    formattedExpiresAt() {
        if (!this.client.signature_expires_at) return '';
        return new Date(this.client.signature_expires_at).toLocaleDateString('es-EC', {
            year: 'numeric', month: 'long', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }
  },
  methods: {
    handleFileChange(event) {
      this.file = event.target.files[0];
    },
    async uploadSignature() {
      if (!this.file) {
        this.$emitter.emit('show-alert', { type: 'error', message: 'Por favor seleccione un archivo.' });
        return;
      }
      if (!this.password) {
        this.$emitter.emit('show-alert', { type: 'error', message: 'Por favor ingrese la contraseña de la firma.' });
        return;
      }

      this.loading = true;
      const formData = new FormData();
      formData.append('signature_file', this.file);
      formData.append('signature_key', this.password);

      try {
        await axios.post(`/api/admin/clients/${this.client.id}/load_signature`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            Authorization: `Bearer ${this.token}`,
          },
        });
        this.$emitter.emit('show-alert', { type: 'success', message: 'Firma cargada con éxito.' });
        this.$emit('signature-uploaded');
        // Reset state after upload
        this.showForm = false;
        this.file = null;
        this.password = '';
      } catch (error) {
        console.error('Error uploading signature:', error);
        const errorMessage = error.response?.data?.message || 'No se pudo cargar la firma.';
        this.$emitter.emit('show-alert', { type: 'error', message: errorMessage });
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
