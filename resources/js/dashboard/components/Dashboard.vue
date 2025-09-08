<template>
  <AppLayout
    :navigation="navigation"
    :current-view="currentDashboardView"
    :is-sidebar-open="isSidebarOpen"
    header-title="Facturación de Clientes"
    sidebar-title="Cliente"
    :user-profile="userProfile"
    @navigate="currentDashboardView = $event"
    @logout="handleLogout"
    @toggle-sidebar="isSidebarOpen = !isSidebarOpen"
  >
    <BaseAlert />
    <!-- Billing Section -->
    <div v-if="currentDashboardView === 'billing'">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <div class="lg:col-span-1 bg-white rounded-xl shadow-lg p-6">
          <h3 class="text-xl font-bold mb-4 text-gray-800 border-b pb-3">Firma Electrónica</h3>
          <p class="text-gray-600 mb-4">Cargue su certificado de firma electrónica para poder emitir comprobantes.</p>
          <SignatureUpload />
        </div>
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
          <h3 class="text-xl font-bold mb-4 text-gray-800 border-b pb-3">Cargar Archivo de Datos</h3>
          <p class="text-gray-600 mb-4">Seleccione un archivo de Excel (.xlsx, .xls) con los datos de los clientes y las facturas a emitir.</p>
          <a href="/plantilla_facturacion.xlsx" download class="text-blue-500 hover:text-blue-700 underline mb-4 block">
            Descargar plantilla de Excel
          </a>
          <FileUpload @file-parsed="handleFileParsed" @parsing-start="isParsingFile = true" @parsing-complete="isParsingFile = false" />
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-lg">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <BaseSelect
                        id="establecimiento-select"
                        label="Establecimiento"
                        v-model="selectedEstablecimientoId"
                        :options="establecimientoOptions"
                        placeholder="Seleccione un establecimiento"
                    />
                </div>
                <div>
                    <BaseSelect
                        id="punto-emision-select"
                        label="Punto de Emisión"
                        v-model="selectedPuntoEmisionId"
                        :options="puntoEmisionOptions"
                        :disabled="!selectedEstablecimientoId"
                        placeholder="Seleccione un punto de emisión"
                    />
                </div>
            </div>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
              <div>
                <h2 class="text-2xl font-bold text-gray-800">Facturación Masiva</h2>
                <p class="text-gray-600">Revise los datos cargados y proceda a emitir las facturas.</p>
              </div>
              <div class="flex items-center space-x-4">
               
                <!-- Action Buttons -->
                <div v-if="!isBilling" class="flex items-center space-x-2">
                    <BaseButton @click="clearState" :disabled="tableData.length === 0" variant="danger">
                        Limpiar Tabla
                    </BaseButton>
                    <BaseButton @click="startBilling" :disabled="tableData.length === 0 || !selectedPuntoEmisionId" variant="primary">
                        <template #icon>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </template>
                        Iniciar Facturación
                    </BaseButton>
                </div>
                <!-- Billing In Progress Controls -->
                <div v-if="isBilling" class="flex items-center space-x-4">
                    <div class="flex items-center text-lg font-medium text-gray-700">
                        <svg class="animate-spin mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Facturando... ({{ currentIndex + 1 }} / {{ rowsToBill.length }})</span>
                    </div>
                    <BaseButton v-if="!isPaused" @click="pauseBilling" variant="warning">Pausar</BaseButton>
                    <BaseButton v-if="isPaused" @click="resumeBilling" variant="success">Reanudar</BaseButton>
                    <BaseButton @click="cancelBilling" variant="danger">Cancelar</BaseButton>
                </div>
              </div>
            </div>
        </div>
        <div class="flex justify-end my-4 px-6">
            <div class="relative w-full max-w-xs">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="text" v-model="searchQuery" placeholder="Buscar..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
        </div>
        <TableSkeleton v-if="isParsingFile" />
        <template v-else>
            <div class="overflow-x-auto">
                <DataTable :data="paginatedPendingRows" :headers="tableHeaders" :sort-key="sortKey" :sort-order="sortOrder" @sort="sortBy" @toggle-expansion="toggleRowExpansion" />
            </div>
            <div v-if="totalPages > 1" class="py-4 px-6 flex justify-center">
                <Pagination :currentPage="currentPage" :totalPages="totalPages" @prev-page="currentPage--" @next-page="currentPage++" />
            </div>
        </template>
      </div>
    </div>

    <!-- Status Section -->
    <div v-if="currentDashboardView === 'status'">
      <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-xl font-bold mb-4 text-gray-800 border-b pb-3">Estado de Factura</h3>
        <p class="text-gray-600 mb-4">Verifique el estado de los servicios del SRI.</p>
        <StatusChecker :token="token" />
      </div>
    </div>

    <!-- My Invoices Section -->
    <div v-if="currentDashboardView === 'my-invoices'">
      <MyInvoices :token="token" :is-sidebar-open="isSidebarOpen" />
    </div>

    <!-- Corrective Billing Section -->
    <div v-if="currentDashboardView === 'corrective'">
      <CorrectiveBilling :is-sidebar-open="isSidebarOpen" />
    </div>

    <!-- Individual Billing Section -->
    <div v-if="currentDashboardView === 'individual-billing'">
      <IndividualBilling :token="token" />
    </div>

    <!-- Configuration Section -->
    <div v-if="currentDashboardView === 'configuration'">
      <Configuration :is-sidebar-open="isSidebarOpen" />
    </div>
  </AppLayout>
</template>

<script>
import { h } from 'vue';
import SignatureUpload from './SignatureUpload.vue';
import FileUpload from './FileUpload.vue';
import DataTable from './DataTable.vue';
import StatusChecker from './StatusChecker.vue';
import MyInvoices from './MyInvoices.vue';
import CorrectiveBilling from './CorrectiveBilling.vue';
import IndividualBilling from './IndividualBilling.vue';
import Pagination from './Pagination.vue';
import BaseButton from './BaseButton.vue';
import Configuration from './Configuration.vue';
import BaseAlert from './BaseAlert.vue';
import BaseSelect from './BaseSelect.vue';
import AppLayout from './AppLayout.vue';
import axios from 'axios';
import { parsePaymentMethods } from '../utils/paymentMethods.js';

const IconBilling = {
  render() { return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', xmlns: 'http://www.w3.org/2000/svg' }, [ h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': 2, d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' }) ]); }
};
const IconIndividualBilling = {
  render() { return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', xmlns: 'http://www.w3.org/2000/svg' }, [ h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': 2, d: 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.536l12.232-12.232z' }) ]); }
};
const IconStatus = {
  render() { return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', xmlns: 'http://www.w3.org/2000/svg' }, [ h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': 2, d: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' }) ]); }
};
const IconInvoices = {
  render() { return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', xmlns: 'http://www.w3.org/2000/svg' }, [ h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': 2, d: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' }) ]); }
};
const IconCorrective = {
  render() { return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', xmlns: 'http://www.w3.org/2000/svg' }, [ h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': 2, d: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' }) ]); }
};
const IconConfig = {
  render() { return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', xmlns: 'http://www.w3.org/2000/svg' }, [ h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': 2, d: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' }), h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': 2, d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z' }) ]); }
};

export default {
  name: 'Dashboard',
  components: {
    SignatureUpload,
    FileUpload,
    DataTable,
    StatusChecker,
    MyInvoices,
    CorrectiveBilling,
    IndividualBilling,
    Pagination,
    BaseButton,
    Configuration,
    BaseAlert,
    BaseSelect,
    AppLayout,
  },
  props: {
    token: {
        type: String,
        required: true,
    }
  },
  data() {
    return {
      userProfile: {
        tipo_impuesto: '2',
        codigo_porcentaje_iva: '2',
      },
      isSidebarOpen: true,
      currentDashboardView: 'billing',
      tableData: [],
      tableHeaders: [
        { text: 'Nombres', value: 'Nombres' },
        { text: 'Cédula', value: 'Cédula' },
        { text: 'Evento', value: 'Evento' },
        { text: 'Precio', value: 'Precio' },
        { text: 'Estado', value: 'Estado' },
      ],
      correctiveInvoicesCount: 0,
      isBilling: false,
      pollingIntervalId: null,
      establecimientos: [],
      puntosEmision: [],
      selectedEstablecimientoId: null,
      selectedPuntoEmisionId: null,
      filterStatus: 'Todos',
      currentPage: 1,
      itemsPerPage: 10,
      isParsingFile: false,
      isStopping: false, // To be replaced by isPaused
      isPaused: false,
      currentIndex: 0,
      rowsToBill: [],
    };
  },
  computed: {
    availablePuntosEmision() {
        if (!this.selectedEstablecimientoId) {
            return [];
        }
        return this.puntosEmision.filter(p => p.establecimiento_id == this.selectedEstablecimientoId);
    },
    pendingRows() {
        return this.tableData.filter(row => row.Estado === 'Pendiente' || row.Estado === 'Procesando');
    },
    paginatedPendingRows() {
      // This computed property will be used for the main table
      const data = this.pendingRows.filter(row => this.filterStatus === 'Todos' || row.Estado === this.filterStatus);
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return data.slice(start, end);
    },
    totalPages() {
      const data = this.pendingRows.filter(row => this.filterStatus === 'Todos' || row.Estado === this.filterStatus);
      return Math.ceil(data.length / this.itemsPerPage);
    },
    establecimientoOptions() {
      return this.establecimientos.map(est => ({
        value: est.id,
        text: `${est.numero} - ${est.nombre}`,
      }));
    },
    puntoEmisionOptions() {
      return this.availablePuntosEmision.map(pto => ({
        value: pto.id,
        text: `${pto.numero} - ${pto.nombre}`,
      }));
    },
    navigation() {
      return [
        { name: 'Facturación Masiva', view: 'billing', icon: IconBilling },
        { name: 'Facturación Individual', view: 'individual-billing', icon: IconIndividualBilling },
        { name: 'Estado de factura', view: 'status', icon: IconStatus },
        { name: 'Mis Comprobantes', view: 'my-invoices', icon: IconInvoices },
        { name: 'Facturación Correctiva', view: 'corrective', icon: IconCorrective, count: this.correctiveInvoicesCount },
        { name: 'Configuración', view: 'configuration', icon: IconConfig },
      ];
    },
  },
  watch: {
    tableData: {
        handler() {
            this.saveState();
        },
        deep: true,
    },
    isBilling(newValue) {
        this.saveState();
    },
    selectedEstablecimientoId() {
        // Reset punto de emision when establishment changes
        this.selectedPuntoEmisionId = null;
    },
    puntoEmisionOptions(newOptions) {
      if (newOptions.length > 0 && !this.selectedPuntoEmisionId) {
        this.selectedPuntoEmisionId = newOptions[0].value;
      }
    }
  },
  mounted() {
    this.loadState();
    if (this.isBilling) {
        // If billing was in progress, resume it.
        this.startBilling();
    }
    this.fetchUserProfile();
    this.fetchEstablecimientos();
    this.fetchPuntosEmision();
    this.$emitter.on('profile-updated', this.fetchUserProfile);
    this.$emitter.on('establishments-updated', this.fetchEstablecimientos);
    this.$emitter.on('puntos-emision-updated', this.fetchPuntosEmision);
    this.updateCorrectiveCount();
    window.addEventListener('corrective-billing-update', this.updateCorrectiveCount);
  },
  beforeUnmount() {
    this.stopPolling();
    this.$emitter.off('profile-updated', this.fetchUserProfile);
    this.$emitter.off('establishments-updated', this.fetchEstablecimientos);
    this.$emitter.off('puntos-emision-updated', this.fetchPuntosEmision);
    window.removeEventListener('corrective-billing-update', this.updateCorrectiveCount);
  },
  methods: {
    updateCorrectiveCount() {
      const correctiveData = JSON.parse(localStorage.getItem('correctiveBillingData') || '[]');
      this.correctiveInvoicesCount = correctiveData.length;
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
    normalizePhoneNumber(phone) {
        if (!phone) {
            return '';
        }
        let cleaned = String(phone).replace(/\s+/g, ''); // Ensure it's a string and remove spaces
        if (cleaned.startsWith('+593')) {
            return cleaned;
        }
        if (cleaned.startsWith('593')) {
            return `+${cleaned}`;
        }
        if (cleaned.length === 10 && cleaned.startsWith('0')) {
            return `+593${cleaned.substring(1)}`;
        }
        if (cleaned.length === 9) {
            return `+593${cleaned}`;
        }
        return phone; // Return original if no rule matches
    },
    async fetchUserProfile() {
      try {
        const response = await axios.get('/api/profile', {
          headers: { 'Authorization': `Bearer ${this.token}` },
        });
        this.userProfile = response.data.data;
      } catch (error) {
        console.error('Error fetching user profile:', error);
        // Use default values if profile fetch fails
        this.userProfile = { tipo_impuesto: '2', codigo_porcentaje_iva: '2' };
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
                headers: { 'Authorization': `Bearer ${this.token}` }
            });
            this.puntosEmision = response.data.data.data;
        } catch (error) {
            console.error('Error fetching puntos de emision:', error);
        }
    },
    saveState() {
        const state = {
            tableData: this.tableData,
            isBilling: this.isBilling,
        };
        localStorage.setItem('bulkBillingState', JSON.stringify(state));
    },
    loadState() {
        const savedState = localStorage.getItem('bulkBillingState');
        if (savedState) {
            const state = JSON.parse(savedState);
            this.tableData = state.tableData || [];
            this.isBilling = state.isBilling || false;
        }
    },
    clearState() {
        localStorage.removeItem('bulkBillingState');
        this.tableData = [];
        this.isBilling = false;
    },
    toggleRowExpansion(rowId) {
      const row = this.tableData.find(r => r.id === rowId);
      if (row) {
        row.isExpanded = !row.isExpanded;
      }
    },
    handleLogout() {
        this.clearState();
        this.$emit('logout');
    },
    handleFileParsed(data) {
      this.clearState(); // Clear any old state before loading new data
      this.tableData = data.map(row => {
        // Check for the 'estado' column, case-insensitive
        const estadoKey = Object.keys(row).find(key => key.toLowerCase().trim() === 'estado');
        const estadoValue = estadoKey ? String(row[estadoKey]).toLowerCase().trim() : '';

        let internalStatus = 'Pendiente'; // Default status, ready for billing
        if (estadoValue === 'pendiente') {
            internalStatus = 'Pago Pendiente'; // New status, will be skipped
        }

        return {
            ...row,
            id: Math.random().toString(36).substr(2, 9),
            Estado: internalStatus,
            clave_acceso: null,
            errorInfo: null,
            isExpanded: false,
        };
      });
    },
    createInvoicePayload(row) {
      const findValue = (keyToFind) => {
        if (!keyToFind) return undefined;
        const normalizedKey = keyToFind.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        for (const key in row) {
          const normalizedObjKey = key.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
          if (normalizedObjKey === normalizedKey) return row[key];
        }
        return undefined;
      };

      const formatToString = (value, decimals = 2) => {
          const num = parseFloat(String(value).replace(',', '.'));
          return isNaN(num) ? "0.00" : num.toFixed(decimals);
      };

      const cedula = findValue('Cédula');
      const nombres = findValue('Nombres');
      const direccion = findValue('Dirección');
      const codigo = findValue('Código');
      const evento = findValue('Evento');
      const email = findValue('Email');
      const telefono = this.normalizePhoneNumber(findValue('Teléfono'));
      const precio = parseFloat(formatToString(findValue('Precio')));

      // Validations
      if (!cedula || (String(cedula).length !== 10 && String(cedula).length !== 13)) {
        throw new Error('Cédula no válida');
      }

      if (!precio || precio <= 0) {
        throw new Error('Precio no válido');
      }
      if (!nombres || !codigo || !evento) {
        throw new Error('Una o más columnas requeridas (Nombres, Código, Evento) no se encontraron o están vacías en el archivo.');
      }

      // Use 6 decimal places for intermediate calculations to maintain precision
      const tarifa = this.getTarifaFromCodigoPorcentaje(this.userProfile.codigo_porcentaje_iva);
      const taxRate = 1 + (tarifa / 100);
      const totalSinImpuestos = precio / taxRate;
      const iva = precio - totalSinImpuestos;

      const metodoPago = findValue('metodo de pago');
      const pagos = parsePaymentMethods(metodoPago, precio).map(p => ({
        ...p,
        total: formatToString(p.total)
      }));

      return {
        // fechaEmision is now set by the server
        tipoIdentificacionComprador: String(cedula).length === 13 ? '04' : '05',
        razonSocialComprador: nombres,
        identificacionComprador: String(cedula),
        direccionComprador: direccion,
        totalSinImpuestos: formatToString(totalSinImpuestos),
        totalDescuento: "0.00",
        totalConImpuestos: [{
            codigo: this.userProfile.tipo_impuesto,
            codigoPorcentaje: this.userProfile.codigo_porcentaje_iva,
            baseImponible: formatToString(totalSinImpuestos),
            valor: formatToString(iva)
        }],
        importeTotal: formatToString(precio),
        pagos: pagos,
        detalles: [{
          codigoPrincipal: String(codigo),
          descripcion: evento,
          cantidad: formatToString(1, 2),
          precioUnitario: formatToString(totalSinImpuestos, 6),
          descuento: "0.00",
          precioTotalSinImpuesto: formatToString(totalSinImpuestos),
          impuestos: [{
                codigo: this.userProfile.tipo_impuesto,
                codigoPorcentaje: this.userProfile.codigo_porcentaje_iva,
                tarifa: tarifa,
                baseImponible: formatToString(totalSinImpuestos),
                valor: formatToString(iva)
            }],
        }],
        infoAdicional: { email: email, telefono: telefono },
      };
    },
    addFailedRowToCorrective(row, errorMessage) {
        row.Estado = 'No Facturado';
        row.errorInfo = errorMessage;

        const correctiveData = JSON.parse(localStorage.getItem('correctiveBillingData') || '[]');
        correctiveData.push(row);
        localStorage.setItem('correctiveBillingData', JSON.stringify(correctiveData));

        // Remove from the main table
        this.tableData = this.tableData.filter(item => item.id !== row.id);

        // Notify other components
        window.dispatchEvent(new Event('corrective-billing-update'));
    },
    async processSingleInvoice(row) {
      try {
        this.updateRowStatus(row.id, 'Procesando', null); // Clear previous errors
        const payload = this.createInvoicePayload(row);

        await axios.post(`/api/comprobantes/factura/${this.selectedPuntoEmisionId}`, payload, {
          headers: { 'Authorization': `Bearer ${this.token}`, 'Content-Type': 'application/json' },
        });

        // On successful POST, the backend job is queued.
        // Remove the row from the pending/failed tables. Its status will be updated
        // via polling in the 'My Invoices' tab.
        this.tableData = this.tableData.filter(item => item.id !== row.id);

      } catch (error) {
        const errorMessage = error.response?.data?.message || error.message;
        if (error.message === 'Cédula no válida') {
          this.addFailedRowToCorrective(row, 'Cédula debe tener 10 o 13 dígitos.');
        } else if (error.message === 'Precio no válido') {
          this.addFailedRowToCorrective(row, 'El Precio debe ser un número mayor a 0.');
        } else if (error.message.includes('columnas requeridas')) {
          this.addFailedRowToCorrective(row, 'Datos incompletos en la fila.');
        } else if (errorMessage.includes('ERROR SECUENCIAL REGISTRADO')) {
          // This error means the invoice was already processed successfully. Remove it.
          this.tableData = this.tableData.filter(item => item.id !== row.id);
        } else {
          this.addFailedRowToCorrective(row, errorMessage);
        }
      }
    },
    pauseBilling() {
        this.isPaused = true;
    },
    resumeBilling() {
        this.isPaused = false;
        // The loop in runBillingProcess will automatically resume.
    },
    cancelBilling() {
        this.isBilling = false;
        this.isPaused = false;
        // Revert any 'Procesando' rows back to 'Pendiente'
        this.tableData.forEach(row => {
            if (row.Estado === 'Procesando') {
                this.updateRowStatus(row.id, 'Pendiente');
            }
        });
    },
    startBilling() {
      this.rowsToBill = [...this.tableData.filter(row => row.Estado === 'Pendiente')];
      if (this.rowsToBill.length === 0) return;

      this.isBilling = true;
      this.isPaused = false;
      this.currentIndex = 0;

      // runBillingProcess is now non-blocking and will run in the background
      this.runBillingProcess();
    },
    async runBillingProcess() {
        while (this.currentIndex < this.rowsToBill.length) {
            if (!this.isBilling) break; // Canceled by user

            if (this.isPaused) {
                // Wait for a moment before checking if we should resume
                await new Promise(resolve => setTimeout(resolve, 500));
                continue;
            }

            const row = this.rowsToBill[this.currentIndex];
            await this.processSingleInvoice(row);

            this.currentIndex++;

            // Optional: small delay between requests to avoid overwhelming the server
            await new Promise(resolve => setTimeout(resolve, 250));
        }

        // Cleanup after the loop is finished or cancelled
        this.isBilling = false;
        this.isPaused = false;
        // Don't reset currentIndex here, so the progress bar stays at 100%
        // this.currentIndex = 0;

        if (!this.pollingIntervalId) {
            this.startPolling();
        }
    },
    startPolling() {
      if (this.pollingIntervalId) clearInterval(this.pollingIntervalId);
      this.pollingIntervalId = setInterval(this.pollInvoiceStatus, 5000);
    },
    stopPolling() {
      if (this.pollingIntervalId) {
        clearInterval(this.pollingIntervalId);
        this.pollingIntervalId = null;
      }
    },
    async pollInvoiceStatus() {
      const rowsToUpdate = this.tableData.filter(row => ['Procesando', 'Pendiente'].includes(row.Estado));
      if (rowsToUpdate.length === 0) {
        this.stopPolling();
        return;
      }
      try {
        const response = await axios.get('/api/comprobantes', {
          headers: { 'Authorization': `Bearer ${this.token}` },
          params: { per_page: 50 }
        });
        const comprobantes = response.data.data.data;
        rowsToUpdate.forEach(row => {
          const findValue = (keyToFind) => {
            if (!keyToFind) return undefined;
            const normalizedKey = keyToFind.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            for (const key in row) {
              const normalizedObjKey = key.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
              if (normalizedObjKey === normalizedKey) return row[key];
            }
            return undefined;
          };
          const match = comprobantes.find(c => c.cliente_ruc === findValue('Cédula') && JSON.parse(c.payload)?.detalles[0]?.codigoPrincipal === findValue('Código'));
          if (match) this.updateRowFromComprobante(row.id, match);
        });
      } catch (error) {
        console.error('Error polling statuses:', error);
      }
    },
    mapBackendStatus(status) {
      switch (status) {
        case 'autorizado': return 'Facturado';
        case 'rechazado': case 'fallido': return 'No Facturado';
        case 'firmado': return 'Enviado';
        case 'pendiente':
        case 'procesando':
        default: return 'Procesando';
      }
    },
    updateRowFromComprobante(rowId, comprobante) {
      const index = this.tableData.findIndex(r => r.id === rowId);
      if (index !== -1) {
        this.tableData[index].Estado = this.mapBackendStatus(comprobante.estado);
        this.tableData[index].clave_acceso = comprobante.clave_acceso;
        if(comprobante.error_message) this.tableData[index].errorInfo = comprobante.error_message;
      }
    },
    updateRowStatus(rowId, status, errorInfo = null) {
      const index = this.tableData.findIndex(r => r.id === rowId);
      if (index !== -1) {
        this.tableData[index].Estado = status;
        this.tableData[index].errorInfo = errorInfo;
      }
    },
    handleSaveRow(updatedRow) {
      const index = this.tableData.findIndex(row => row.id === updatedRow.id);
      if (index !== -1) {
        this.tableData.splice(index, 1, updatedRow);
      }
    },
  },
};
</script>
