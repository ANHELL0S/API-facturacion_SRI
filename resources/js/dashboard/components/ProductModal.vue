<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="close">
    <div :class="['relative top-20 mx-auto p-5 border w-full shadow-lg rounded-md bg-white', isSidebarOpen ? 'max-w-xl' : 'max-w-2xl']" @click.stop>
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold">{{ isEditMode ? 'Editar Producto' : 'Nuevo Producto' }}</h3>
        <button @click="close" class="text-black">&times;</button>
      </div>
      <div class="mt-2">
        <form @submit.prevent="save">
          <div class="grid grid-cols-1 gap-6">
            <div>
              <label for="code" class="block text-sm font-medium text-gray-700">Código</label>
              <input type="text" id="code" v-model="form.code" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
              <input type="text" id="description" v-model="form.description" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
              <label for="unit_price" class="block text-sm font-medium text-gray-700">Precio Unitario</label>
              <input type="number" step="0.01" id="unit_price" v-model="form.unit_price" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
              <label for="tax_code" class="block text-sm font-medium text-gray-700">Impuesto</label>
              <select id="tax_code" v-model="form.tax_code" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option v-for="tax in taxOptions" :key="tax.value" :value="tax.value">{{ tax.text }}</option>
              </select>
            </div>
          </div>
          <div class="mt-6 flex justify-end">
            <button type="button" @click="close" class="bg-gray-200 text-gray-800 py-2 px-4 rounded-md mr-2">Cancelar</button>
            <button type="submit" :disabled="isLoading" class="bg-indigo-600 text-white py-2 px-4 rounded-md disabled:opacity-50">
              {{ isLoading ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProductModal',
  props: {
    show: Boolean,
    product: Object,
    isLoading: Boolean,
    isSidebarOpen: Boolean,
  },
  data() {
    return {
      form: {
        code: '',
        description: '',
        unit_price: 0,
        tax_code: '4', // Default IVA 15%
      },
      taxOptions: [
        { value: '4', text: 'IVA 15% (general vigente)' },
        { value: '5', text: 'IVA 5%' },
        { value: '8', text: 'IVA 8% (diferenciado)' },
        { value: '0', text: 'IVA 0%' },
        { value: '6', text: 'No objeto de IVA' },
        { value: '7', text: 'Exento de IVA' },
      ],
    };
  },
  computed: {
    isEditMode() {
      return this.product && this.product.id;
    },
  },
  watch: {
    show(newVal) {
      if (newVal) {
        if (this.isEditMode) {
          this.form = { ...this.product };
        } else {
          this.resetForm();
        }
      }
    },
  },
  methods: {
    close() {
      this.$emit('close');
    },
    save() {
      const dataToSave = { ...this.form };
      if (this.isEditMode) {
        dataToSave.id = this.product.id;
      }
      this.$emit('save', dataToSave);
    },
    resetForm() {
      this.form = {
        code: '',
        description: '',
        unit_price: 0,
        tax_code: '4',
      };
    },
  },
};
</script>
