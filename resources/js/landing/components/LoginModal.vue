<template>
  <div v-if="show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl p-8 w-full max-w-md">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Iniciar Sesión</h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      
      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
          <input 
            v-model="email" 
            type="email" 
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="tu@empresa.com"
          >
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña</label>
          <input 
            v-model="password" 
            type="password" 
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="••••••••"
          >
        </div>
        
        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
          Iniciar Sesión
        </button>
      </form>
      
      <div class="mt-6 text-center">
        <p class="text-gray-600">¿No tienes cuenta? 
          <button @click="$emit('switch-to-register')" class="text-blue-600 hover:text-blue-700 font-medium">
            Regístrate aquí
          </button>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps(['show'])
defineEmits(['close', 'switch-to-register'])

const email = ref('')
const password = ref('')

const handleLogin = () => {
  console.log('Login attempt:', { email: email.value, password: password.value })
}
</script>
