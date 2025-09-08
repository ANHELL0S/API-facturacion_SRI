<template>
  <div>
    <div class="bg-white rounded-xl shadow-lg p-6">
      <h2 class="text-2xl font-bold text-gray-800 mb-4">Facturación Individual</h2>

      <!-- Establishment and Emission Point -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <BaseSelect
          id="establecimiento-select"
          label="Establecimiento"
          v-model="selectedEstablecimientoId"
          :options="establecimientoOptions"
          placeholder="Seleccione un establecimiento"
        />
        <BaseSelect
          id="punto-emision-select"
          label="Punto de Emisión"
          v-model="selectedPuntoEmisionId"
          :options="puntoEmisionOptions"
          :disabled="!selectedEstablecimientoId"
          placeholder="Seleccione un punto de emisión"
        />
      </div>

      <!-- Client Information -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
          <label for="ruc" class="block text-sm font-medium text-gray-700">RUC/CI</label>
          <input type="text" id="ruc" v-model="client.ruc" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Razón Social / Nombres</label>
          <input type="text" id="name" v-model="client.name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div>
          <label for="address" class="block text-sm font-medium text-gray-700">Dirección</label>
          <input type="text" id="address" v-model="client.address" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input type="email" id="email" v-model="client.email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div>
          <label for="phone" class="block text-sm font-medium text-gray-700">Teléfono</label>
          <input type="tel" id="phone" v-model="client.telefono" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
      </div>

      <!-- Invoice Items -->
      <div class="mb-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Items de la Factura</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unitario</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descuento</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Impuestos</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                <th scope="col" class="relative px-6 py-3">
                  <span class="sr-only">Eliminar</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(item, index) in items" :key="index">
                <td class="px-6 py-4 whitespace-nowrap">
                  <input type="text" v-model="item.description" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <input type="number" v-model.number="item.quantity" class="mt-1 block w-24 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <input type="number" v-model.number="item.price" class="mt-1 block w-32 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <input type="number" v-model.number="item.discount" class="mt-1 block w-24 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <select v-model="item.tax" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option v-for="tax in taxOptions" :key="tax.value" :value="tax.value">{{ tax.text }}</option>
                  </select>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ calculateItemTotal(item) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <button @click="removeItem(index)" class="text-red-600 hover:text-red-900" title="Eliminar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <button @click="addItem" class="mt-4 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-2 rounded">
          <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> Agregar Item
        </button>
      </div>

      <!-- Totals -->
      <div class="flex justify-end mb-6">
        <div class="w-full max-w-sm">
          <div class="flex justify-between py-2 border-b">
            <span class="font-medium text-gray-600">Subtotal:</span>
            <span class="font-bold text-gray-800">{{ totals.subtotal }}</span>
          </div>
          <div class="flex justify-between py-2 border-b">
            <span class="font-medium text-gray-600">Descuento:</span>
            <span class="font-bold text-gray-800">{{ totals.discount }}</span>
          </div>
          <div v-for="(tax, code) in totals.iva" :key="code" class="flex justify-between py-2 border-b">
            <span class="font-medium text-gray-600">IVA ({{ getTarifaFromCodigoPorcentaje(code) }}%):</span>
            <span class="font-bold text-gray-800">{{ tax.valor.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between py-2">
            <span class="text-xl font-bold text-gray-800">Total:</span>
            <span class="text-xl font-bold text-gray-800">{{ totals.total }}</span>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end">
        <button @click="generateInvoice" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
          Generar Factura
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import BaseSelect from './BaseSelect.vue';

export default {
  name: 'IndividualBilling',
  components: {
    BaseSelect,
  },
  props: {
    token: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      client: {
        ruc: '',
        name: '',
        address: '',
        email: '',
        telefono: ''
      },
      items: [
        {
          description: '',
          quantity: 1,
          price: 0,
          discount: 0,
          taxes: []
        }
      ],
      establecimientos: [],
      puntosEmision: [],
      selectedEstablecimientoId: null,
      selectedPuntoEmisionId: null,
      userProfile: {
        tipo_impuesto: '2',
        codigo_porcentaje_iva: '4',
      },
      taxOptions: [
        { value: '4', text: 'IVA 15% (general vigente)' },
        { value: '5', text: 'IVA 5%' },
        { value: '8', text: 'IVA 8% (diferenciado)' },
        { value: '0', text: 'IVA 0%' },
        { value: '6', text: 'No objeto de IVA' },
        { value: '7', text: 'Exento de IVA' },
      ]
    };
  },
  computed: {
    establecimientoOptions() {
      return this.establecimientos.map(est => ({
        value: est.id,
        text: `${est.numero} - ${est.nombre}`,
      }));
    },
    puntoEmisionOptions() {
      if (!this.selectedEstablecimientoId) {
        return [];
      }
      return this.puntosEmision
        .filter(p => p.establecimiento_id == this.selectedEstablecimientoId)
        .map(pto => ({
          value: pto.id,
          text: `${pto.numero} - ${pto.nombre}`,
        }));
    },
    totals() {
      let subtotal = 0;
      let discount = 0;
      const iva = {};

      this.items.forEach(item => {
        const itemSubtotal = item.quantity * item.price;
        subtotal += itemSubtotal;
        discount += item.discount;
        const taxRate = this.getTarifaFromCodigoPorcentaje(item.tax) / 100;
        const taxValue = (itemSubtotal - item.discount) * taxRate;
        if (!iva[item.tax]) {
          iva[item.tax] = {
            base: 0,
            valor: 0,
          };
        }
        iva[item.tax].base += itemSubtotal - item.discount;
        iva[item.tax].valor += taxValue;
      });

      const totalIva = Object.values(iva).reduce((acc, tax) => acc + tax.valor, 0);

      return {
        subtotal: subtotal.toFixed(2),
        discount: discount.toFixed(2),
        iva: iva,
        total: (subtotal - discount + totalIva).toFixed(2),
      };
    }
  },
  watch: {
    selectedEstablecimientoId() {
      this.selectedPuntoEmisionId = null;
    },
    puntoEmisionOptions(newOptions) {
      if (newOptions.length > 0 && !this.selectedPuntoEmisionId) {
        this.selectedPuntoEmisionId = newOptions[0].value;
      }
    }
  },
  mounted() {
    this.fetchUserProfile();
    this.fetchEstablecimientos();
    this.fetchPuntosEmision();
    this.$emitter.on('profile-updated', this.fetchUserProfile);
  },
  beforeUnmount() {
    this.$emitter.off('profile-updated', this.fetchUserProfile);
  },
  methods: {
    addItem() {
      this.items.push({
        description: '',
        quantity: 1,
        price: 0,
        discount: 0,
        tax: this.userProfile.codigo_porcentaje_iva
      });
    },
    removeItem(index) {
      this.items.splice(index, 1);
    },
    calculateItemTotal(item) {
      const subtotal = item.quantity * item.price;
      const total = subtotal - item.discount;
      const taxRate = this.getTarifaFromCodigoPorcentaje(item.tax) / 100;
      return (total * (1 + taxRate)).toFixed(2);
    },
    getTarifaFromCodigoPorcentaje(codigo) {
        const map = {
            '0': 0,
            '2': 12,
            '3': 14,
            '4': 15,
            '5': 5,
            '6': 0, // No objeto de IVA
            '7': 0, // Exento de IVA
            '8': 8,
            '10': 13,
        };
        return map[codigo] || 0;
    },
    validatePhoneNumber(phone) {
        if (!phone || phone.trim() === '') {
            return true; // Optional field, valid if empty
        }
        const cleaned = phone.replace(/\s+/g, '');

        if (cleaned.startsWith('+593')) {
            return cleaned.length === 13;
        }
        if (cleaned.startsWith('593')) {
            return cleaned.length === 12;
        }
        if (cleaned.startsWith('0')) {
            return cleaned.length === 10;
        }
        return false;
    },
    normalizePhoneNumber(phone) {
        if (!phone) {
            return '';
        }
        let cleaned = phone.replace(/\s+/g, ''); // Remove spaces
        if (cleaned.startsWith('+593')) {
            return cleaned;
        }
        if (cleaned.startsWith('593')) {
            return `+${cleaned}`;
        }
        if (cleaned.length === 10 && cleaned.startsWith('0')) {
            return `+593${cleaned.substring(1)}`;
        }
        return phone; // Return original if no rule matches
    },
    async fetchUserProfile() {
      try {
        const response = await axios.get('/api/profile', {
          headers: { 'Authorization': `Bearer ${this.token}` },
        });
        this.userProfile = response.data.data;
        // Set default tax for new items
        this.items.forEach(item => {
          if (!item.tax) {
            item.tax = this.userProfile.codigo_porcentaje_iva;
          }
        });
      } catch (error) {
        console.error('Error fetching user profile:', error);
        // Use default values if profile fetch fails
        this.userProfile = { tipo_impuesto: '2', codigo_porcentaje_iva: '4' };
      }
    },
    async fetchEstablecimientos() {
      try {
        const response = await axios.get('/api/establecimientos', {
          headers: { 'Authorization': `Bearer ${this.token}` }
        });
        this.establecimientos = response.data.data.data;
        if (this.establecimientos.length > 0) {
          this.selectedEstablecimientoId = this.establecimientos[0].id;
        }
      } catch (error) {
        console.error('Error fetching establecimientos:', error);
      }
    },
    async fetchPuntosEmision() {
      try {
        const response = await axios.get('/api/puntos-emision', {
          headers: { 'Authorization': 'Bearer ' + this.token }
        });
        this.puntosEmision = response.data.data.data;
      } catch (error) {
        console.error('Error fetching puntos de emision:', error);
      }
    },
    async generateInvoice() {
      if (!this.selectedPuntoEmisionId) {
        this.$emitter.emit('show-alert', { type: 'error', message: 'Por favor, seleccione un punto de emisión.' });
        return;
      }

      if (this.client.ruc.length !== 10 && this.client.ruc.length !== 13) {
        this.$emitter.emit('show-alert', { type: 'error', message: 'El RUC/CI debe tener 10 o 13 dígitos.' });
        return;
      }

      if (!this.validatePhoneNumber(this.client.telefono)) {
        this.$emitter.emit('show-alert', { type: 'error', message: 'El número de teléfono no es válido. Formatos aceptados: +593..., 593... (12 dígitos), o 0... (10 dígitos).' });
        return;
      }

      const totalSinImpuestos = this.items.reduce((acc, item) => acc + (item.quantity * item.price) - item.discount, 0);

      const totalConImpuestos = Object.entries(this.totals.iva).map(([codigoPorcentaje, tax]) => ({
        codigo: this.userProfile.tipo_impuesto,
        codigoPorcentaje: codigoPorcentaje,
        baseImponible: tax.base.toFixed(2),
        valor: tax.valor.toFixed(2),
      }));

      const detalles = this.items.map((item, index) => {
        const itemSubtotal = item.quantity * item.price;
        const taxRate = this.getTarifaFromCodigoPorcentaje(item.tax);
        const taxValue = (itemSubtotal - item.discount) * (taxRate / 100);
        return {
          codigoPrincipal: 'PROD' + Date.now() + '-' + index,
          descripcion: item.description,
          cantidad: item.quantity,
          precioUnitario: item.price,
          descuento: item.discount,
          precioTotalSinImpuesto: (itemSubtotal - item.discount).toFixed(2),
          impuestos: [{
            codigo: this.userProfile.tipo_impuesto,
            codigoPorcentaje: item.tax,
            tarifa: taxRate,
            baseImponible: (itemSubtotal - item.discount).toFixed(2),
            valor: taxValue.toFixed(2),
          }],
        };
      });

      const infoAdicional = {
        email: this.client.email,
      };

      if (this.client.telefono) {
          infoAdicional.telefono = this.normalizePhoneNumber(this.client.telefono);
      }

      const payload = {
        tipoIdentificacionComprador: String(this.client.ruc).length === 13 ? '04' : '05',
        razonSocialComprador: this.client.name,
        identificacionComprador: this.client.ruc,
        direccionComprador: this.client.address,
        totalSinImpuestos: totalSinImpuestos.toFixed(2),
        totalDescuento: this.totals.discount,
        totalConImpuestos: totalConImpuestos,
        importeTotal: this.totals.total,
        pagos: [{ formaPago: '01', total: this.totals.total }], // Assuming cash payment for now
        detalles: detalles,
        infoAdicional: infoAdicional,
      };

      try {
        await axios.post(`/api/comprobantes/factura/${this.selectedPuntoEmisionId}`, payload, {
          headers: { 'Authorization': `Bearer ${this.token}` }
        });
        this.$emitter.emit('show-alert', { type: 'success', message: 'Factura generada exitosamente. Se está procesando.' });
        // Reset form
        this.client = { ruc: '', name: '', address: '', email: '', telefono: '' };
        this.items = [{ description: '', quantity: 1, price: 0, discount: 0, tax: this.userProfile.codigo_porcentaje_iva }];
      } catch (error) {
        console.error('Error generating invoice:', error);
        const errorMessage = error.response?.data?.message || 'Error al generar la factura.';
        this.$emitter.emit('show-alert', { type: 'error', message: errorMessage });
      }
    }
  }
};
</script>
