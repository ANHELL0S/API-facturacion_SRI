<template>
  <div
    class="flex items-center justify-center min-h-screen bg-cover bg-center grayscale"
    style="background-image: url('https://i0.wp.com/picjumbo.com/wp-content/uploads/business-meeting-in-the-office-free-photo.jpg');"
  >
    <div class="w-full max-w-lg p-8 space-y-6 bg-white rounded-lg shadow-2xl bg-opacity-80 border border-gray-200">
      <h2 class="text-2xl font-bold text-center text-gray-900">Crear una cuenta</h2>
      <form @submit.prevent="handleRegister">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nombres</label>
            <input v-model="form.name" id="name" name="name" type="text" required
                   class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <span v-if="errors.name" class="text-sm text-red-600">{{ translateError(errors.name[0]) }}</span>
          </div>
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
            <input v-model="form.email" id="email" name="email" type="email" autocomplete="email" required
                   class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <span v-if="errors.email" class="text-sm text-red-600">{{ translateError(errors.email[0]) }}</span>
          </div>
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
            <input v-model="form.password" id="password" name="password" type="password" autocomplete="new-password" required
                   class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <span v-if="errors.password" class="text-sm text-red-600">{{ translateError(errors.password[0]) }}</span>
          </div>
          <div>
            <label for="ruc" class="block text-sm font-medium text-gray-700">RUC</label>
            <input v-model="form.ruc" id="ruc" name="ruc" type="text" required
                   class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <span v-if="errors.ruc" class="text-sm text-red-600">{{ translateError(errors.ruc[0]) }}</span>
          </div>
          <div class="md:col-span-2">
            <label for="razonSocial" class="block text-sm font-medium text-gray-700">Razón Social</label>
            <input v-model="form.razonSocial" id="razonSocial" name="razonSocial" type="text" required
                   class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <span v-if="errors.razonSocial" class="text-sm text-red-600">{{ translateError(errors.razonSocial[0]) }}</span>
          </div>
          <div class="md:col-span-2">
            <label for="nombreComercial" class="block text-sm font-medium text-gray-700">Nombre Comercial</label>
            <input v-model="form.nombreComercial" id="nombreComercial" name="nombreComercial" type="text"
                   class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <span v-if="errors.nombreComercial" class="text-sm text-red-600">{{ translateError(errors.nombreComercial[0]) }}</span>
          </div>
          <div class="md:col-span-2">
            <label for="dirMatriz" class="block text-sm font-medium text-gray-700">Dirección Matriz</label>
            <input v-model="form.dirMatriz" id="dirMatriz" name="dirMatriz" type="text" required
                   class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <span v-if="errors.dirMatriz" class="text-sm text-red-600">{{ translateError(errors.dirMatriz[0]) }}</span>
          </div>
          <div class="md:col-span-2 flex items-center">
            <input v-model="form.obligadoContabilidad" id="obligadoContabilidad" name="obligadoContabilidad" type="checkbox"
                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <label for="obligadoContabilidad" class="block ml-2 text-sm text-gray-900">Obligado a llevar contabilidad</label>
          </div>
        </div>
        <div class="mt-6">
          <button type="submit"
                  class="w-full px-4 py-2 font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Registrarse
          </button>
        </div>
      </form>
      <p class="text-sm text-center text-gray-600">
        ¿Ya tienes una cuenta?
        <button @click="$emit('show-login')" class="font-medium text-indigo-600 hover:text-indigo-500">
          Inicia sesión
        </button>
      </p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Register',
  data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
        ruc: '',
        razonSocial: '',
        nombreComercial: '',
        dirMatriz: '',
        obligadoContabilidad: false,
      },
      errors: {},
    };
  },
  methods: {
    async handleRegister() {
      this.errors = {};
      try {
        await axios.post('/api/register', this.form);
        this.$emitter.emit('show-alert', { type: 'success', message: 'Registro exitoso. Ahora puedes iniciar sesión.' });
        this.$emit('show-login');
      } catch (error) {
        if (error.response && (error.response.status === 422 || error.response.status === 400)) {
          if (error.response.data.errors) {
            this.errors = error.response.data.errors;
          } else {
            const message = error.response.data.message || 'Por favor, verifique los datos ingresados.';
            this.$emitter.emit('show-alert', { type: 'error', message: `Error en el registro: ${message}` });
          }
        } else {
          console.error('Error during registration:', error);
          const message = error.response?.data?.message || error.message;
          this.$emitter.emit('show-alert', { type: 'error', message: `Error en el registro: ${message}` });
        }
      }
    },
    translateError(message) {
      const translations = {
        "The ruc has already been taken.": "El RUC ya ha sido registrado.",
        "The email has already been taken.": "El correo electrónico ya ha sido registrado.",
        "The name field is required.": "El campo nombre es obligatorio.",
        "The email field is required.": "El campo correo electrónico es obligatorio.",
        "The password field is required.": "El campo contraseña es obligatorio.",
        "The ruc field is required.": "El campo RUC es obligatorio.",
        "The razonSocial field is required.": "El campo razón social es obligatorio.",
        "The dirMatriz field is required.": "El campo dirección matriz es obligatorio.",
        "The password must be at least 8 characters.": "La contraseña debe tener al menos 8 caracteres.",
      };

      // Simple replacements for attributes
      let translatedMessage = translations[message] || message;
      translatedMessage = translatedMessage.replace('name', 'nombre');
      translatedMessage = translatedMessage.replace('email', 'correo electrónico');
      translatedMessage = translatedMessage.replace('password', 'contraseña');
      translatedMessage = translatedMessage.replace('ruc', 'RUC');
      translatedMessage = translatedMessage.replace('razonSocial', 'razón social');
      translatedMessage = translatedMessage.replace('dirMatriz', 'dirección matriz');

      return translatedMessage;
    }
  },
};
</script>
