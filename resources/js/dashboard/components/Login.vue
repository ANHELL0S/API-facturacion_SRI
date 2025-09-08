<template>
  <div
    class="flex items-center justify-center min-h-screen bg-cover bg-center grayscale"
    style="background-image: url('https://i0.wp.com/picjumbo.com/wp-content/uploads/business-meeting-in-the-office-free-photo.jpg');"
  >
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-2xl bg-opacity-80 border border-gray-200">
      <h1 class="text-2xl font-bold text-center text-gray-900">Iniciar Sesión</h1>
      <form @submit.prevent="handleLogin" class="space-y-6">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input type="email" v-model="loginForm.email" id="email" required class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
          <input type="password" v-model="loginForm.password" id="password" required class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div v-if="loginError" class="text-sm text-red-600">
          {{ loginError }}
        </div>
        <div>
          <button type="submit" :disabled="isLoggingIn" class="w-full px-4 py-2 font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:bg-blue-400">
            {{ isLoggingIn ? 'Iniciando...' : 'Entrar' }}
          </button>
        </div>
      </form>
      <p class="text-sm text-center text-gray-600">
        ¿No tienes una cuenta?
        <button @click="$emit('show-register')" class="font-medium text-blue-600 hover:text-blue-500">
          Regístrate
        </button>
      </p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Login',
  data() {
    return {
      loginForm: {
        email: '',
        password: '',
      },
      loginError: '',
      isLoggingIn: false,
    };
  },
  methods: {
    async handleLogin() {
      this.isLoggingIn = true;
      this.loginError = '';
      try {
        const response = await axios.post('/api/login', this.loginForm);
        const token = response.data.data.token;
        this.$emit('login-success', token);
      } catch (error) {
        if (error.response && error.response.data && error.response.data.message) {
          this.loginError = error.response.data.message;
        } else {
          this.loginError = 'Ocurrió un error al iniciar sesión.';
        }
      } finally {
        this.isLoggingIn = false;
      }
    },
  },
};
</script>
