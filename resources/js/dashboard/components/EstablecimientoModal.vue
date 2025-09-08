<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4" :style="{ paddingLeft: isSidebarOpen ? '16rem' : '5rem' }" @click.self="$emit('close')">
    <div class="relative mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl leading-6 font-medium text-gray-900">{{ formTitle }}</h3>
            <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label for="numero" class="block text-sm font-medium text-gray-700">Número de Establecimiento (3 dígitos)</label>
            <input type="text" id="numero" v-model="form.numero" maxlength="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
          </div>
          <div>
            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del Establecimiento</label>
            <input type="text" id="nombre" v-model="form.nombre" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
          </div>
          <div>
            <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
            <input type="text" id="direccion" v-model="form.direccion" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
          </div>
        </form>

        <div class="mt-6 flex justify-end space-x-4">
          <BaseButton @click="$emit('close')" variant="secondary">Cancelar</BaseButton>
          <BaseButton @click="handleSubmit" :is-loading="isLoading" variant="primary">{{ isEditing ? 'Guardar Cambios' : 'Crear' }}</BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import BaseButton from './BaseButton.vue';

export default {
  name: 'EstablecimientoModal',
  components: { BaseButton },
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    establecimiento: {
      type: Object,
      default: null,
    },
    isLoading: {
        type: Boolean,
        default: false,
    },
    isSidebarOpen: {
        type: Boolean,
        default: false,
    }
  },
  data() {
    return {
      form: {
        numero: '',
        nombre: '',
        direccion: '',
      },
    };
  },
  computed: {
    isEditing() {
      return !!this.establecimiento;
    },
    formTitle() {
      return this.isEditing ? 'Editar Establecimiento' : 'Crear Nuevo Establecimiento';
    },
  },
  watch: {
    establecimiento: {
      handler(newVal) {
        if (newVal) {
          this.form = { ...newVal };
        } else {
          this.resetForm();
        }
      },
      immediate: true,
    },
  },
  methods: {
    resetForm() {
      this.form = {
        numero: '',
        nombre: '',
        direccion: '',
      };
    },
    handleSubmit() {
      this.$emit('save', { ...this.form });
    },
  },
};
</script>
