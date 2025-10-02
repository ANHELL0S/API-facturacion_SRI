<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4" :style="{ paddingLeft: isSidebarOpen ? '16rem' : '5rem' }" @click.self="$emit('close')">
    <div class="relative mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl leading-6 font-medium text-gray-900">Editar Factura</h3>
            <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="mt-2 text-sm text-gray-700">
          <form @submit.prevent="save" class="max-h-[60vh] overflow-y-auto pr-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
              <!-- Loop through all fields in the editable row data -->
              <div v-for="(value, key) in editableRow" :key="key">
                  <!-- We only want to edit specific, user-facing fields -->
                  <template v-if="isEditableField(key)">
                      <label :for="`edit-${key}`" class="block text-sm font-medium text-gray-700">{{ formatLabel(key) }}</label>
                      <input
                          :type="getFieldType(key)"
                          :id="`edit-${key}`"
                          v-model="editableRow[key]"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      >
                  </template>
              </div>
            </div>
          </form>
        </div>

        <div class="mt-6 flex justify-end space-x-4">
          <button @click="$emit('close')" type="button" class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-md hover:bg-gray-300">
            Cancelar
          </button>
          <button @click="save" type="button" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
            Guardar Cambios
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EditInvoiceModal',
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    rowData: {
      type: Object,
      default: () => ({}),
    },
    isSidebarOpen: {
        type: Boolean,
        default: false,
    }
  },
  data() {
    return {
      editableRow: {},
      editableFields: ['Nombres', 'Cédula', 'Dirección', 'Email', 'Teléfono', 'Evento', 'Precio', 'Código'],
    };
  },
  watch: {
    rowData: {
      handler(newVal) {
        // Create a deep copy to avoid modifying the original object directly
        this.editableRow = JSON.parse(JSON.stringify(newVal || {}));
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    save() {
      this.$emit('save', this.editableRow);
      this.$emit('close');
    },
    isEditableField(key) {
        return this.editableFields.includes(key);
    },
    formatLabel(key) {
        // Simple formatter, can be expanded
        return key.charAt(0).toUpperCase() + key.slice(1);
    },
    getFieldType(key) {
        if (key.toLowerCase().includes('email')) return 'email';
        if (key.toLowerCase().includes('precio')) return 'number';
        if (key.toLowerCase().includes('teléfono')) return 'tel';
        return 'text';
    }
  },
};
</script>
