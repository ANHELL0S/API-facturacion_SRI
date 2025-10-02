<template>
   <h2 class="text-2xl font-bold text-gray-800">Facturación Individual</h2>
  <div class="bg-gray-100 p-4 font-sans text-sm">
    
    <div class="max-w-7xl mx-auto bg-white shadow-lg p-6 rounded-lg">

      <!-- Header -->
      <div class="flex justify-between items-start mb-4 pb-4">
        
        <!-- Emitter Info -->
        <div class="w-1/2 pr-4 text-base">
          <img v-if="userProfile.logo_path" :src="`/storage/${userProfile.logo_path}`" alt="Logo" class="max-w-xs max-h-30 mb-4 rounded-md">
          <p><span class="font-bold">Emisor:</span> {{ userProfile.name }}</p>
          <p><span class="font-bold">RUC:</span> {{ userProfile.ruc }}</p>
          <p><span class="font-bold">Correo:</span> {{ userProfile.email }}</p>
          <p><span class="font-bold">Matriz:</span> {{ userProfile.dirMatriz }}</p>
          <p><span class="font-bold">Obligado a llevar contabilidad:</span> {{ userProfile.obligadoContabilidad ? 'SI' : 'NO' }}</p>
        </div>

        <!-- Invoice Info -->
        <div class="w-1/2 pl-4">
          <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-xl font-bold">FACTURA</p>
            <p class="text-red-600 font-bold text-xl mb-1">{{ establecimientoCode }}-{{ puntoEmisionCode }}-{{ proximoSecuencial }}</p>

            <div class="grid grid-cols-1 gap-y-2">
                <BaseSelect
                    id="ind-establecimiento-select"
                    label="Establecimiento"
                    v-model="selectedEstablecimientoId"
                    :options="establecimientoOptions"
                    placeholder="Seleccione establecimiento"
                />
                <BaseSelect
                    id="ind-punto-emision-select"
                    label="Punto de Emisión"
                    v-model="selectedPuntoEmisionId"
                    :options="puntoEmisionOptions"
                    :disabled="!selectedEstablecimientoId"
                    placeholder="Seleccione punto de emisión"
                />
            </div>
            <p><span class="font-bold">Ambiente:</span> {{ userProfile.ambiente == '1' ? 'PRUEBAS' : 'PRODUCCIÓN' }}</p>
            <p><span class="font-bold">Emisión:</span> NORMAL</p>
          </div>
        </div>
      </div>

      <!-- Client Information -->
      <div class="mb-4 p-4 bg-gray-50 rounded-lg">
        <div class="grid grid-cols-2 gap-x-8 gap-y-2">
             <div>
            <label class="font-bold">RUC / CI:</label>
            <input type="text" v-model="client.ruc" class="form-input-box">
          </div>
          <div>
            <label class="font-bold">Fecha Emisión:</label>
            <input type="date" v-model="client.fechaEmision" class="form-input-box" disabled>
          </div>
          <div>
            <label class="font-bold">Razón Social / Nombres:</label>
            <input type="text" v-model="client.name" class="form-input-box">
          </div>
          
       
          <div>
            <label class="font-bold">Correo:</label>
            <input type="email" v-model="client.email" class="form-input-box">
          </div>
          <div>
            <label class="font-bold">Dirección:</label>
            <input type="text" v-model="client.address" class="form-input-box">
          </div>
          <div>
            <label class="font-bold">Teléfono:</label>
            <input type="tel" v-model="client.telefono" class="form-input-box">
          </div>
        </div>
      </div>

      <!-- Invoice Items -->
      <div class="mb-4">
        <table class="w-full">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-2 py-2 text-left font-bold rounded-tl-lg">Código</th>
              <th class="px-2 py-2 text-left font-bold w-2/5">Descripción</th>
              <th class="px-2 py-2 text-left font-bold">Cantidad</th>
              <th class="px-2 py-2 text-left font-bold">P. Unitario</th>
              <th class="px-2 py-2 text-left font-bold">Descuento</th>
              <th class="px-2 py-2 text-left font-bold">Impuesto</th>
              <th class="px-2 py-2 text-left font-bold">Total</th>
              <th class="px-2 py-2 text-left font-bold rounded-tr-lg"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in items" :key="index" class="border-b">
              <td class="px-2 py-1"><input type="text" v-model="item.codigoPrincipal" class="form-input-box"></td>
              <td class="px-2 py-1 relative">
                <input type="text" v-model="item.description" @input="searchProducts(index)" @focus="activeAutocomplete = index" class="form-input-box">
                <div v-if="activeAutocomplete === index && filteredProducts.length" class="absolute z-10 w-full bg-white border rounded shadow-lg mt-1">
                  <ul><li v-for="p in filteredProducts" :key="p.id" @click="selectProduct(index, p)" class="px-3 py-2 cursor-pointer hover:bg-gray-100">{{ p.description }}</li></ul>
                </div>
              </td>
              <td class="px-2 py-1"><input type="number" v-model.number="item.quantity" class="form-input-box w-24 text-right"></td>
              <td class="px-2 py-1"><input type="number" v-model.number="item.price" class="form-input-box w-24 text-right"></td>
              <td class="px-2 py-1"><input type="number" v-model.number="item.discount" class="form-input-box w-24 text-right"></td>
              <td class="px-2 py-1">
                <select v-model="item.tax" class="form-input-box w-full">
                  <option v-for="tax in taxOptions" :key="tax.value" :value="tax.value">{{ tax.text }}</option>
                </select>
              </td>
              <td class="px-2 py-1 text-right font-medium">${{ calculateItemTotal(item) }}</td>
              <td class="px-2 py-1 text-center">
                <button @click="removeItem(index)" class="text-red-600 hover:text-red-800" title="Eliminar">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <button @click="addItem" class="mt-2 text-sm text-indigo-600 font-bold hover:text-indigo-800">+ Agregar Item</button>
      </div>

      <!-- Footer -->
      <div class="flex justify-between mt-4">
        <!-- Additional Info & Payment -->
        <div class="w-1/2 pr-4 space-y-4">
          <div class="rounded-lg p-4 bg-gray-50">
            <p class="font-bold">Información Adicional</p>
            <div v-for="(info, index) in additionalInfo" :key="index" class="flex items-center mt-1">
              <input type="text" v-model="info.name" class="form-input-box w-1/3" placeholder="Nombre">
              <span class="mx-2">:</span>
              <input type="text" v-model="info.value" class="form-input-box w-2/3" placeholder="Valor">
              <button @click="removeAdditionalInfo(index)" class="text-red-600 hover:text-red-800 ml-2" title="Eliminar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
              </button>
            </div>
            <button @click="addAdditionalInfo" class="mt-2 text-sm text-indigo-600 font-bold hover:text-indigo-800">+ Agregar Info</button>
          </div>
          <div class="rounded-lg p-4 bg-gray-50">
            <p class="font-bold">Formas de Pago</p>
            <BaseSelect v-model="selectedPaymentMethod" :options="paymentMethodOptions" class="w-full mt-2"/>
          </div>
        </div>

        <!-- Totals -->
        <div class="w-2/5">
          <table class="w-full rounded-lg overflow-hidden">
            <tbody>
              <tr v-for="(tax, code) in totals.iva" :key="code">
                <td class="px-2 py-1 border font-bold bg-gray-50">Subtotal {{ getTarifaFromCodigoPorcentaje(code) }}%</td>
                <td class="px-2 py-1 border text-right">${{ tax.base.toFixed(2) }}</td>
              </tr>
              <tr><td class="px-2 py-1 border font-bold bg-gray-50">Descuento</td><td class="px-2 py-1 border text-right">${{ totals.discount }}</td></tr>
              <tr v-for="(tax, code) in totals.iva" :key="code">
                <td class="px-2 py-1 border font-bold bg-gray-50">IVA {{ getTarifaFromCodigoPorcentaje(code) }}%</td>
                <td class="px-2 py-1 border text-right">${{ tax.valor.toFixed(2) }}</td>
              </tr>
              <tr class="bg-gray-200"><td class="px-2 py-2 border font-bold text-lg">Valor Total</td><td class="px-2 py-2 border text-right font-bold text-lg">${{ totals.total }}</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end mt-8">
        <button @click="generateInvoice" :disabled="isSubmitting" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-md">
          <span v-if="isSubmitting">Generando...</span><span v-else>Generar Factura</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import BaseSelect from './BaseSelect.vue';
import { paymentMethodOptions } from '../utils/paymentMethods.js';

export default {
  name: 'IndividualBilling',
  components: { BaseSelect },
  props: { token: { type: String, required: true } },
  data() {
    return {
      client: {
        ruc: '', name: '', address: '', email: '', telefono: '',
        fechaEmision: new Date(new Date().getTime() - (new Date().getTimezoneOffset() * 60000)).toISOString().slice(0,10),
      },
      items: [{ description: '', quantity: 1, price: 0, discount: 0, tax: '4', codigoPrincipal: '' }],
      establecimientos: [],
      puntosEmision: [],
      selectedEstablecimientoId: null,
      selectedPuntoEmisionId: null,
      selectedPaymentMethod: '01',
      paymentMethodOptions: paymentMethodOptions,
      additionalInfo: [{ name: '', value: '' }],
      userProfile: { tipo_impuesto: '2', codigo_porcentaje_iva: '4', ambiente: '1' },
      taxOptions: [
        { value: '4', text: 'IVA 15%' }, { value: '5', text: 'IVA 5%' }, { value: '8', text: 'IVA 8%' },
        { value: '0', text: 'IVA 0%' }, { value: '6', text: 'No objeto de IVA' }, { value: '7', text: 'Exento de IVA' },
      ],
      products: [],
      activeAutocomplete: null,
      isSubmitting: false,
    };
  },
  computed: {
    filteredProducts() {
      if (this.activeAutocomplete === null) return [];
      const item = this.items[this.activeAutocomplete];
      if (!item || item.description.length < 1) return [];
      return this.products.filter(p => p.description.toLowerCase().includes(item.description.toLowerCase()));
    },
    establecimientoCode() {
      const est = this.establecimientos.find(e => e.id == this.selectedEstablecimientoId);
      return est ? est.numero : '000';
    },
    puntoEmisionCode() {
      const pto = this.puntosEmision.find(p => p.id == this.selectedPuntoEmisionId);
      return pto ? pto.numero : '000';
    },
    establecimientoOptions() { return this.establecimientos.map(e => ({ value: e.id, text: `${e.numero} - ${e.nombre}` })); },
    puntoEmisionOptions() {
      if (!this.selectedEstablecimientoId) return [];
      return this.puntosEmision.filter(p => p.establecimiento_id == this.selectedEstablecimientoId).map(p => ({ value: p.id, text: `${p.numero} - ${p.nombre}` }));
    },
    proximoSecuencial() {
      if (!this.selectedPuntoEmisionId) return '000000000';
      const pto = this.puntosEmision.find(p => p.id == this.selectedPuntoEmisionId);
      return pto ? String(pto.proximo_secuencial).padStart(9, '0') : '000000000';
    },
    totals() {
      let subtotal = 0, discount = 0, iva = {};
      this.items.forEach(item => {
        const itemSubtotal = item.quantity * item.price;
        subtotal += itemSubtotal;
        discount += item.discount;
        if (item.tax) {
            const taxRate = this.getTarifaFromCodigoPorcentaje(item.tax) / 100;
            const taxValue = (itemSubtotal - item.discount) * taxRate;
            if (!iva[item.tax]) iva[item.tax] = { base: 0, valor: 0 };
            iva[item.tax].base += itemSubtotal - item.discount;
            iva[item.tax].valor += taxValue;
        }
      });
      const totalIva = Object.values(iva).reduce((acc, tax) => acc + tax.valor, 0);
      return { subtotal: subtotal.toFixed(2), discount: discount.toFixed(2), iva, total: (subtotal - discount + totalIva).toFixed(2) };
    }
  },
  watch: {
    selectedEstablecimientoId() { this.selectedPuntoEmisionId = null; },
    puntoEmisionOptions(newOptions) { if (newOptions.length > 0 && !this.selectedPuntoEmisionId) this.selectedPuntoEmisionId = newOptions[0].value; },
    'client.ruc'(newVal) {
      if (newVal.length === 10 || newVal.length === 13) {
        this.fetchPersonaData(newVal);
      }
    }
  },
  mounted() {
    this.fetchUserProfile(); this.fetchEstablecimientos(); this.fetchPuntosEmision(); this.fetchProducts();
    this.$emitter.on('profile-updated', this.fetchUserProfile);
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeUnmount() {
    this.$emitter.off('profile-updated', this.fetchUserProfile);
    document.removeEventListener('click', this.handleClickOutside);
  },
  methods: {
    handleClickOutside(event) { if (this.$el.contains(event.target) && !this.$el.querySelector('.relative').contains(event.target)) this.activeAutocomplete = null; },
    searchProducts(index) {
      // The logic is now in the computed property. This method just needs to set the active index.
      this.activeAutocomplete = index;
    },
    selectProduct(index, product) {
      this.items[index] = { ...this.items[index], description: product.description, price: product.unit_price, tax: product.tax_code, codigoPrincipal: product.code };
      this.activeAutocomplete = null;
    },
    addAdditionalInfo() { this.additionalInfo.push({ name: '', value: '' }); },
    removeAdditionalInfo(index) { this.additionalInfo.splice(index, 1); },
    addItem() { this.items.push({ description: '', quantity: 1, price: 0, discount: 0, tax: this.userProfile.codigo_porcentaje_iva, codigoPrincipal: '' }); },
    removeItem(index) { this.items.splice(index, 1); },
    calculateItemTotal(item) {
      const total = (item.quantity * item.price) - item.discount;
      return total.toFixed(2);
    },
    getTarifaFromCodigoPorcentaje(codigo) {
      const map = { '0': 0, '2': 12, '3': 14, '4': 15, '5': 5, '8': 8, '10': 13 };
      return map[codigo] || 0;
    },
    validatePhoneNumber(phone) {
        if (!phone || phone.trim() === '') return true;
        const cleaned = phone.replace(/\s+/g, '');
        if (cleaned.startsWith('+593')) return cleaned.length === 13;
        if (cleaned.startsWith('593')) return cleaned.length === 12;
        if (cleaned.length === 10 && cleaned.startsWith('0')) return true;
        if (cleaned.length === 9 && !cleaned.startsWith('0')) return true;
        return false;
    },
    normalizePhoneNumber(phone) {
        if (!phone) return '';
        let cleaned = String(phone).replace(/\s+/g, '');
        if (cleaned.startsWith('+593')) return cleaned;
        if (cleaned.startsWith('593')) return `+${cleaned}`;
        if (cleaned.length === 10 && cleaned.startsWith('0')) return `+593${cleaned.substring(1)}`;
        if (cleaned.length === 9) return `+593${cleaned}`;
        return phone;
    },
    async fetchUserProfile() {
      try {
        const response = await axios.get('/api/profile', { headers: { 'Authorization': `Bearer ${this.token}` }});
        this.userProfile = response.data.data;
        this.items.forEach(item => { if (!item.tax) item.tax = this.userProfile.codigo_porcentaje_iva; });
      } catch (error) { console.error('Error fetching user profile:', error); }
    },
    async fetchEstablecimientos() {
      try {
        const response = await axios.get('/api/establecimientos', { headers: { 'Authorization': `Bearer ${this.token}` }});
        this.establecimientos = response.data.data.data;
        if (this.establecimientos.length > 0) this.selectedEstablecimientoId = this.establecimientos[0].id;
      } catch (error) { console.error('Error fetching establecimientos:', error); }
    },
    async fetchPuntosEmision() {
      try {
        const response = await axios.get('/api/puntos-emision', { headers: { 'Authorization': 'Bearer ' + this.token }});
        this.puntosEmision = response.data.data.data;
      } catch (error) { console.error('Error fetching puntos de emision:', error); }
    },
    async fetchProducts() {
      try {
        const response = await axios.get('/api/products', { headers: { 'Authorization': `Bearer ${this.token}` }});
        this.products = response.data.data;
      } catch (error) { console.error('Error fetching products:', error); }
    },
    async fetchPersonaData(id) {
      try {
        const response = await axios.get(`/api/persona/${id}`, {
          headers: { 'Authorization': `Bearer ${this.token}` }
        });
        if (response.data && response.data.success) {
          const persona = response.data.data.data;
          this.client.name = persona.full_name || '';
          this.client.address = persona.address || '';
          this.client.email = persona.email || '';
          this.client.telefono = persona.phone || '';
        }
      } catch (error) {
        console.warn('No se pudo obtener los datos de la persona. El usuario puede ingresarlos manualmente.', error);
      }
    },
    async generateInvoice() {
      if (this.isSubmitting) return;

      if (!this.validatePhoneNumber(this.client.telefono)) {
        this.$emitter.emit('show-alert', { type: 'error', message: 'El formato del número de teléfono no es válido.' });
        return;
      }

      this.isSubmitting = true;
      try {
        const totalSinImpuestos = this.items.reduce((acc, item) => acc + (item.quantity * item.price) - item.discount, 0);
        const detalles = this.items.map((item, index) => {
          const itemSubtotal = item.quantity * item.price;
          const taxRate = this.getTarifaFromCodigoPorcentaje(item.tax);
          const taxValue = (itemSubtotal - item.discount) * (taxRate / 100);
          return {
            codigoPrincipal: item.codigoPrincipal || 'PROD' + Date.now() + '-' + index,
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
        const infoAdicional = this.additionalInfo.reduce((acc, info) => {
            if (info.name && info.value) acc[info.name] = info.value;
            return acc;
        }, {});
        if (this.client.email) infoAdicional.email = this.client.email;
        if (this.client.telefono) infoAdicional.telefono = this.normalizePhoneNumber(this.client.telefono);

        const payload = {
          fechaEmision: this.client.fechaEmision,
          tipoIdentificacionComprador: String(this.client.ruc).length === 13 ? '04' : '05',
          razonSocialComprador: this.client.name,
          identificacionComprador: this.client.ruc,
          direccionComprador: this.client.address,
          totalSinImpuestos: totalSinImpuestos.toFixed(2),
          totalDescuento: this.totals.discount,
          totalConImpuestos: Object.values(this.totals.iva).map(tax => ({
            codigo: this.userProfile.tipo_impuesto,
            codigoPorcentaje: Object.keys(this.totals.iva).find(key => this.totals.iva[key] === tax),
            baseImponible: tax.base.toFixed(2),
            valor: tax.valor.toFixed(2),
          })),
          importeTotal: this.totals.total,
          pagos: [{ formaPago: this.selectedPaymentMethod, total: this.totals.total }],
          detalles: detalles,
          infoAdicional: infoAdicional,
        };
        await axios.post(`/api/comprobantes/factura/${this.selectedPuntoEmisionId}`, payload, { headers: { 'Authorization': `Bearer ${this.token}` }});
        this.$emitter.emit('show-alert', { type: 'success', message: 'Factura generada exitosamente.' });
        // Reset form for next invoice
        this.client = {
            ruc: '', name: '', address: '', email: '', telefono: '',
            fechaEmision: new Date(new Date().getTime() - (new Date().getTimezoneOffset() * 60000)).toISOString().slice(0,10),
        };
        this.items = [{ description: '', quantity: 1, price: 0, discount: 0, tax: this.userProfile.codigo_porcentaje_iva, codigoPrincipal: '' }];
        this.additionalInfo = [{ name: '', value: '' }];
      } catch (error) {
        let finalErrorMessage = "Ocurrió un error inesperado."; // Default message

        const sriError = error.response?.data?.errors?.sri_error;
        const apiMessage = error.response?.data?.message;
        const genericError = error.message;

        if (sriError) {
            finalErrorMessage = sriError;
        } else if (apiMessage) {
            finalErrorMessage = apiMessage;
        } else if (genericError) {
            finalErrorMessage = genericError;
        }
        
        this.$emitter.emit('show-alert', { type: 'error', message: finalErrorMessage });
      } finally {
        this.isSubmitting = false;
      }
    }
  }
};
</script>

<style scoped>
.form-input-box {
  display: block;
  width: 100%;
  margin-top: 4px;
  padding: 8px;
  background-color: #f9fafb; /* gray-50 */
  border-radius: 6px;
  border: 1px solid #d1d5db; /* gray-300 */
}
.form-input-box:focus {
  outline: none;
  border-color: #6366f1; /* indigo-500 */
  box-shadow: 0 0 0 1px #6366f1;
}
.form-input-table {
  border-width: 0;
  background-color: transparent;
  padding: 2px;
  width: 100%;
}
.form-input-table:focus {
  outline: none;
  background-color: #eff6ff; /* blue-50 */
}
</style>
