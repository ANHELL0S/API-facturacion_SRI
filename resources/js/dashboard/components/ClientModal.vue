<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4" :style="{ paddingLeft: isSidebarOpen ? '16rem' : '5rem' }" @click.self="$emit('close')">
    <div class="relative mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white" @click.stop>
      <div class="mt-3">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl leading-6 font-medium text-gray-900">{{ formTitle }}</h3>
            <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Left Column: Client Details Form -->
            <fieldset :disabled="isClientCreated">
                <h4 class="text-lg font-medium text-gray-800 border-b pb-2 mb-4">Detalles del Cliente</h4>
                <form @submit.prevent="saveClient" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input type="text" v-model="form.name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        <p v-if="formErrors.name" class="text-red-500 text-xs mt-1">{{ formErrors.name[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Correo</label>
                        <input type="email" v-model="form.email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        <p v-if="formErrors.email" class="text-red-500 text-xs mt-1">{{ formErrors.email[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">RUC</label>
                        <input type="text" v-model="form.ruc" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        <p v-if="formErrors.ruc" class="text-red-500 text-xs mt-1">{{ formErrors.ruc[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Razón Social</label>
                        <input type="text" v-model="form.razonSocial" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        <p v-if="formErrors.razonSocial" class="text-red-500 text-xs mt-1">{{ formErrors.razonSocial[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre Comercial</label>
                        <input type="text" v-model="form.nombreComercial" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dirección Matriz</label>
                        <input type="text" v-model="form.dirMatriz" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input type="password" v-model="form.password" :required="!isEditMode" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        <p v-if="formErrors.password" class="text-red-500 text-xs mt-1">{{ formErrors.password[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tarifa</label>
                        <select v-model="form.tarifa" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                            <option value="comprobante">Tarifa por Comprobante</option>
                            <option value="establecimiento">Tarifa por Establecimiento</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ambiente</label>
                        <select v-model="form.ambiente" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                            <option value="1">Pruebas</option>
                            <option value="2">Producción</option>
                        </select>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" v-model="form.obligadoContabilidad" id="obligadoContabilidad" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <label for="obligadoContabilidad" class="ml-2 block text-sm text-gray-900">Obligado Contabilidad</label>
                    </div>
                    </div>
                </form>
            </fieldset>

            <!-- Right Column: Signature Manager -->
            <div>
                <h4 class="text-lg font-medium text-gray-800 border-b pb-2 mb-4">Firma Electrónica</h4>
                <div v-if="!isEditMode" class="p-4 bg-gray-100 rounded-lg text-center text-gray-500">
                    Guarde el cliente para poder cargar la firma.
                </div>
                <SignatureManager v-else :client="editableClient" :token="token" @signature-uploaded="$emit('client-saved')" />
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-4">
          <BaseButton @click="$emit('close')" variant="secondary">Finalizar</BaseButton>
          <BaseButton v-if="!isClientCreated" @click="saveClient" variant="primary">{{ isEditMode ? 'Guardar Cambios' : 'Crear Cliente' }}</BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import BaseButton from './BaseButton.vue';
import SignatureManager from './SignatureManager.vue';

export default {
  name: 'ClientModal',
  components: { BaseButton, SignatureManager },
  props: {
    client: {
      type: Object,
      default: null,
    },
    token: {
      type: String,
      required: true,
    },
    isSidebarOpen: {
      type: Boolean,
      default: false,
    }
  },
  data() {
    return {
      editableClient: null,
      form: {
        name: '',
        email: '',
        password: '',
        ruc: '',
        razonSocial: '',
        nombreComercial: '',
        dirMatriz: '',
        obligadoContabilidad: false,
        tarifa: 'comprobante',
        ambiente: '1',
      },
      formErrors: {},
    };
  },
  computed: {
    isEditMode() {
      return !!this.editableClient;
    },
    isClientCreated() {
        // This should only be true after a client is created within the modal,
        // to disable the form and encourage signature upload.
        // It should NOT be true when opening the modal for an existing client.
        return this.isEditMode && !this.client;
    },
    formTitle() {
      return this.isEditMode ? 'Editar Cliente' : 'Crear Cliente';
    },
  },
  watch: {
    client: {
      handler(newVal) {
        this.formErrors = {};
        if (newVal) {
            this.editableClient = { ...newVal };
            this.form.name = newVal.name || '';
            this.form.email = newVal.email || '';
            this.form.ruc = newVal.ruc || '';
            this.form.razonSocial = newVal.razonSocial || '';
            this.form.nombreComercial = newVal.nombreComercial || '';
            this.form.dirMatriz = newVal.dirMatriz || '';
            this.form.obligadoContabilidad = newVal.obligadoContabilidad || false;
            this.form.tarifa = newVal.tarifa || 'comprobante';
            this.form.ambiente = newVal.ambiente || '1';
            this.form.password = '';
        } else {
          this.editableClient = null;
          this.resetForm();
        }
      },
      immediate: true,
    },
  },
  methods: {
    resetForm() {
      this.form = {
        name: '',
        email: '',
        password: '',
        ruc: '',
        razonSocial: '',
        nombreComercial: '',
        dirMatriz: '',
        obligadoContabilidad: false,
        tarifa: 'comprobante',
        ambiente: '1',
      };
    },
    async saveClient() {
      this.formErrors = {};
      // When creating, we only save the client details first.
      // The signature can only be uploaded after the client exists.
      const method = this.isEditMode ? 'put' : 'post';
      const url = this.isEditMode ? `/api/admin/clients/${this.editableClient.id}` : '/api/admin/clients';

      const payload = { ...this.form };
      if (this.isEditMode && !payload.password) {
        delete payload.password;
      }

      try {
        const response = await axios[method](url, payload, {
          headers: { Authorization: `Bearer ${this.token}` },
        });

        if (this.isEditMode) {
            this.$emitter.emit('show-alert', { type: 'success', message: 'Cliente actualizado con éxito.' });
            this.$emit('client-saved');
            this.$emit('close');
        } else {
            // This was a creation. Now we transition to edit mode within the modal.
            this.$emitter.emit('show-alert', { type: 'success', message: 'Cliente creado. Ahora puede cargar la firma.' });
            this.editableClient = response.data.data;
            this.$emit('client-saved'); // Refresh list in the background
        }
      } catch (error) {
        if (error.response && (error.response.status === 400 || error.response.status === 422) && error.response.data.errors) {
            this.formErrors = error.response.data.errors;
        } else {
            console.error('Error saving client:', error);
            this.$emitter.emit('show-alert', { type: 'error', message: 'No se pudo guardar el cliente.' });
        }
      }
    },
  },
};
</script>
