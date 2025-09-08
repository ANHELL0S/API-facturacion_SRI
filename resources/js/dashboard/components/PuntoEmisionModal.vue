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
            <label for="establecimiento" class="block text-sm font-medium text-gray-700">Establecimiento</label>
            <select id="establecimiento" v-model="form.establecimiento_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                <option disabled value="">Seleccione un establecimiento</option>
                <option v-for="est in establecimientos" :key="est.id" :value="est.id">
                    {{ est.codigo }} - {{ est.nombre }}
                </option>
            </select>
          </div>
          <div>
            <label for="numero" class="block text-sm font-medium text-gray-700">Número de Punto de Emisión (3 dígitos)</label>
            <input type="text" id="numero" v-model="form.numero" maxlength="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
          </div>
          <div>
            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del Punto de Emisión</label>
            <input type="text" id="nombre" v-model="form.nombre" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
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
  name: 'PuntoEmisionModal',
  components: { BaseButton },
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    puntoEmision: {
      type: Object,
      default: null,
    },
    establecimientos: {
      type: Array,
      required: true,
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
        establecimiento_id: '',
      },
    };
  },
  computed: {
    isEditing() {
      return !!this.puntoEmision;
    },
    formTitle() {
      return this.isEditing ? 'Editar Punto de Emisión' : 'Crear Nuevo Punto de Emisión';
    },
  },
  watch: {
    puntoEmision: {
      handler(newVal) {
        if (newVal) {
          this.form = {
            numero: newVal.numero,
            nombre: newVal.nombre,
            establecimiento_id: newVal.establecimiento_id,
           };
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
        establecimiento_id: '',
      };
    },
    handleSubmit() {
      this.$emit('save', { ...this.form });
    },
  },
};
</script>
