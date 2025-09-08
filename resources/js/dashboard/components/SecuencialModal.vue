<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4" :style="{ paddingLeft: isSidebarOpen ? '16rem' : '5rem' }" @click.self="close">
    <div class="relative mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl leading-6 font-medium text-gray-900">Editar Próximo Secuencial</h3>
            <button @click="close" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <form @submit.prevent="save" class="space-y-4">
          <div>
            <label for="proximo_secuencial" class="block text-sm font-medium text-gray-700">Próximo Secuencial</label>
            <input
              type="text"
              id="proximo_secuencial"
              v-model="editableSecuencial"
              class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              maxlength="9"
              pattern="\d{9}"
              title="Debe ser un número de 9 dígitos."
              required
            />
            <p v-if="error" class="text-red-500 text-xs mt-1">{{ error }}</p>
          </div>
        </form>

        <div class="mt-6 flex justify-end space-x-4">
          <BaseButton @click="close" variant="secondary">Cancelar</BaseButton>
          <BaseButton @click="save" :is-loading="isLoading" variant="primary">Guardar Cambios</BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import BaseButton from './BaseButton.vue';

export default {
  name: 'SecuencialModal',
  components: { BaseButton },
  props: {
    show: Boolean,
    puntoEmision: Object,
    isLoading: Boolean,
    isSidebarOpen: {
        type: Boolean,
        default: false,
    }
  },
  data() {
    return {
      editableSecuencial: '',
      error: null,
    };
  },
  watch: {
    puntoEmision(newVal) {
      if (newVal) {
        this.editableSecuencial = newVal.proximo_secuencial || '000000001';
      }
    },
  },
  methods: {
    close() {
      this.$emit('close');
    },
    save() {
      this.error = null;
      if (!/^\d{9}$/.test(this.editableSecuencial)) {
        this.error = 'El secuencial debe ser un número de 9 dígitos.';
        return;
      }
      this.$emit('save', {
        proximo_secuencial: this.editableSecuencial,
      });
    },
  },
};
</script>
