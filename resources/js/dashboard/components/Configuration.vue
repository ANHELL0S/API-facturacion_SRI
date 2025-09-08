<template>
  <div>
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Configuración</h2>
    <p class="text-gray-600 mb-6">Administre la configuración general, establecimientos y puntos de emisión.</p>

    <div class="mb-4 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="#" @click.prevent="activeTab = 'general'" :class="['whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm', activeTab === 'general' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                General
            </a>
            <a href="#" @click.prevent="activeTab = 'establecimientos'" :class="['whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm', activeTab === 'establecimientos' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                Establecimientos
            </a>
            <a href="#" @click.prevent="activeTab = 'puntos_emision'" :class="['whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm', activeTab === 'puntos_emision' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                Puntos de Emisión
            </a>
        </nav>
    </div>

    <div>
      <div v-if="activeTab === 'general'">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración General</h3>
            <form @submit.prevent="saveConfiguration">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <BaseSelect
                            id="ambiente-select"
                            label="Ambiente de Facturación"
                            v-model="form.ambiente"
                            :options="ambienteOptions"
                        />
                        <p class="mt-2 text-sm text-gray-500">Seleccione el ambiente para la emisión de comprobantes.</p>
                    </div>
                    <div>
                        <label for="send-email-toggle" class="block text-sm font-medium text-gray-700">Enviar Factura por Correo</label>
                        <button
                            type="button"
                            @click="form.enviar_factura_por_correo = !form.enviar_factura_por_correo"
                            :class="[form.enviar_factura_por_correo ? 'bg-indigo-600' : 'bg-gray-200', 'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 mt-1']"
                            role="switch"
                            :aria-checked="form.enviar_factura_por_correo"
                        >
                            <span class="sr-only">Use setting</span>
                            <span
                                aria-hidden="true"
                                :class="[form.enviar_factura_por_correo ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out']"
                            ></span>
                        </button>
                        <p class="mt-2 text-sm text-gray-500">Si se activa, se enviará una copia de la factura al correo del cliente.</p>
                    </div>
                    <div>
                        <label for="from-email" class="block text-sm font-medium text-gray-700">Remitente</label>
                        <input
                            type="text"
                            id="from-email"
                            v-model="form.from_email"
                            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="remitente@ejemplo.com"
                        />
                        <p class="mt-2 text-sm text-gray-500">Establece el remitente desde el cual se enviarán las facturas. Si se deja en blanco, se usará el predeterminado.</p>
                    </div>
                    <div>
                        <BaseSelect
                            id="tipo-impuesto-select"
                            label="Tipo de Impuesto"
                            v-model="form.tipo_impuesto"
                            :options="tipoImpuestoOptions"
                        />
                        <p class="mt-2 text-sm text-gray-500">Seleccione el tipo de impuesto principal para los comprobantes.</p>
                    </div>
                    <div>
                        <BaseSelect
                            id="codigo-porcentaje-iva-select"
                            label="Porcentaje de IVA"
                            v-model="form.codigo_porcentaje_iva"
                            :options="codigoPorcentajeIvaOptions"
                        />
                        <p class="mt-2 text-sm text-gray-500">Seleccione el porcentaje de IVA a aplicar.</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Guardar Cambios
                    </button>
                </div>
            </form>

            <div class="mt-8 border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Logo de la Empresa</h3>
                <div class="flex items-center">
                    <div class="mr-4">
                        <img v-if="logoPreview" :src="logoPreview" alt="Vista previa del logo" class="h-20 w-20 rounded-full object-cover">
                        <div v-else class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-500">Sin logo</span>
                        </div>
                    </div>
                    <div>
                        <input type="file" ref="logoInput" @change="handleFileChange" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100"/>
                        <p class="mt-1 text-sm text-gray-500">PNG, JPG, GIF hasta 2MB.</p>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button @click="uploadLogo" :disabled="!logoFile" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-400 disabled:cursor-not-allowed">
                        Subir Logo
                    </button>
                </div>
            </div>
        </div>
      </div>
      <div v-if="activeTab === 'establecimientos'">
        <EstablecimientosManager :is-sidebar-open="isSidebarOpen" />
      </div>
      <div v-if="activeTab === 'puntos_emision'">
        <PuntosEmisionManager :is-sidebar-open="isSidebarOpen" />
      </div>
    </div>
  </div>
</template>

<script>
import EstablecimientosManager from './EstablecimientosManager.vue';
import PuntosEmisionManager from './PuntosEmisionManager.vue';
import BaseSelect from './BaseSelect.vue';
import axios from 'axios';

export default {
  name: 'Configuration',
  props: {
    isSidebarOpen: {
      type: Boolean,
      default: false,
    }
  },
  components: {
    EstablecimientosManager,
    PuntosEmisionManager,
    BaseSelect,
  },
  data() {
    return {
      activeTab: 'general',
      form: {
        razonSocial: '',
        nombreComercial: '',
        dirMatriz: '',
        contribuyenteEspecial: '',
        obligadoContabilidad: false,
        ambiente: '1',
        enviar_factura_por_correo: true,
        from_email: '',
        logo_path: '',
        tipo_impuesto: '2',
        codigo_porcentaje_iva: '2',
      },
      logoFile: null,
      logoPreview: null,
      ambienteOptions: [
        { value: '1', text: 'Pruebas' },
        { value: '2', text: 'Producción' },
      ],
      tipoImpuestoOptions: [
        { value: '1', text: 'Impuesto a la Renta' },
        { value: '2', text: 'IVA' },
        { value: '3', text: 'ICE' },
        { value: '4', text: 'IRBPNR' },
        { value: '5', text: 'ISD' },
        { value: '6', text: 'IMPUESTO VERDE' },
      ],
      codigoPorcentajeIvaOptions: [
        { value: '4', text: 'IVA 15% (general vigente)' },
        { value: '5', text: 'IVA 5%' },
        { value: '8', text: 'IVA 8% (diferenciado)' },
        { value: '0', text: 'IVA 0%' },
        { value: '6', text: 'No objeto de IVA' },
        { value: '7', text: 'Exento de IVA' },
      ],
      token: localStorage.getItem('jwt_token'),
    };
  },
  mounted() {
    this.loadConfiguration();
  },
  methods: {
    async loadConfiguration() {
      try {
        const response = await axios.get('/api/profile', {
          headers: { 'Authorization': `Bearer ${this.token}` },
        });
        const profile = response.data.data;
        this.form = {
            ...this.form,
            razonSocial: profile.razonSocial,
            nombreComercial: profile.nombreComercial,
            dirMatriz: profile.dirMatriz,
            contribuyenteEspecial: profile.contribuyenteEspecial,
            obligadoContabilidad: profile.obligadoContabilidad,
            ambiente: profile.ambiente,
            enviar_factura_por_correo: profile.enviar_factura_por_correo,
            from_email: profile.from_email,
            logo_path: profile.logo_path,
            tipo_impuesto: profile.tipo_impuesto,
            codigo_porcentaje_iva: profile.codigo_porcentaje_iva,
        };
        if (profile.logo_path) {
            this.logoPreview = `/storage/${profile.logo_path}`;
        }
      } catch (error) {
        console.error('Error al cargar la configuración:', error);
        this.$emitter.emit('show-alert', { type: 'error', message: 'No se pudo cargar la configuración del perfil.' });
      }
    },
    async saveConfiguration() {
      try {
        await axios.put('/api/profile', this.form, {
          headers: { 'Authorization': `Bearer ${this.token}` },
        });
        this.$emitter.emit('show-alert', { type: 'success', message: 'Configuración guardada exitosamente.' });
        this.$emitter.emit('profile-updated');
      } catch (error) {
        console.error('Error al guardar la configuración:', error);
        if (error.response && error.response.status === 422) {
            this.$emitter.emit('show-alert', { type: 'error', message: 'Error de validación. Revise los datos e intente de nuevo.' });
        } else {
            this.$emitter.emit('show-alert', { type: 'error', message: 'No se pudo guardar la configuración.' });
        }
      }
    },
    handleFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        this.logoFile = file;
        this.logoPreview = URL.createObjectURL(file);
      }
    },
    async uploadLogo() {
      if (!this.logoFile) {
        this.$emitter.emit('show-alert', { type: 'error', message: 'Por favor, seleccione un archivo de logo.' });
        return;
      }

      const formData = new FormData();
      formData.append('logo', this.logoFile);

      try {
        const response = await axios.post('/api/profile/logo', formData, {
          headers: {
            'Authorization': `Bearer ${this.token}`,
            'Content-Type': 'multipart/form-data',
          },
        });
        this.form.logo_path = response.data.data.logo_path;
        this.logoPreview = `/storage/${response.data.data.logo_path}`;
        this.logoFile = null;
        this.$refs.logoInput.value = ''; // Reset file input element
        this.$emitter.emit('show-alert', { type: 'success', message: 'Logo actualizado exitosamente.' });
        this.$emitter.emit('profile-updated');
      } catch (error) {
        console.error('Error al subir el logo:', error);
        const errorMessage = error.response?.data?.errors?.logo?.[0] || 'No se pudo subir el logo.';
        this.$emitter.emit('show-alert', { type: 'error', message: errorMessage });
      }
    },
  },
};
</script>
