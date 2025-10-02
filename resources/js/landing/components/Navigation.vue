<template>
  <nav class="fixed top-0 w-full bg-white/80 backdrop-blur-md border-b border-gray-200 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <span class="text-xl font-bold text-gray-900">FacturaPro</span>
        </div>
        
        <div class="hidden md:flex items-center space-x-8">
          <a href="#features" class="text-gray-600 hover:text-gray-900 font-medium transition-colors">Características</a>
          <a href="#pricing" class="text-gray-600 hover:text-gray-900 font-medium transition-colors">Precios</a>
          <a href="#testimonials" class="text-gray-600 hover:text-gray-900 font-medium transition-colors">Testimonios</a>
          <button @click="showLogin = true" class="text-gray-600 hover:text-gray-900 font-medium transition-colors">Iniciar Sesión</button>
          <button @click="showRegister = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm">
            Comenzar Gratis
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
defineEmits(['show-login', 'show-register'])
</script>
