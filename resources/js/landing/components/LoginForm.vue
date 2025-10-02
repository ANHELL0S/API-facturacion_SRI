<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl border border-border bg-card p-8 shadow-2xl">
      <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-primary">
          <LogIn class="h-6 w-6 text-primary-foreground" />
        </div>
        <h2 class="text-2xl font-bold text-card-foreground">Iniciar Sesión</h2>
        <p class="text-sm text-muted-foreground">Accede a tu cuenta de FacturaPro</p>
      </div>
      
      <form @submit.prevent="handleLogin" class="space-y-6">
        <div class="space-y-2">
          <label class="text-sm font-medium text-card-foreground">Email</label>
          <input 
            v-model="form.email"
            type="email" 
            required
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
            placeholder="tu@empresa.com"
          />
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium text-card-foreground">Contraseña</label>
          <input 
            v-model="form.password"
            type="password" 
            required
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
            placeholder="••••••••"
          />
        </div>
        
        <div class="flex items-center justify-between">
          <label class="flex items-center space-x-2">
            <input type="checkbox" class="rounded border-border text-primary focus:ring-primary/20" />
            <span class="text-sm text-muted-foreground">Recordarme</span>
          </label>
          <a href="#" class="text-sm text-primary hover:underline">¿Olvidaste tu contraseña?</a>
        </div>
        
        <button 
          type="submit"
          class="w-full rounded-md bg-primary py-2.5 text-sm font-semibold text-primary-foreground shadow transition-colors hover:bg-primary/90"
        >
          Iniciar Sesión
        </button>
      </form>
      
      <div class="mt-6 text-center">
        <p class="text-sm text-muted-foreground">
          ¿No tienes cuenta? 
          <button @click="$emit('switchToRegister')" class="text-primary hover:underline">
            Regístrate aquí
          </button>
        </p>
      </div>
      
      <button 
        @click="$emit('close')"
        class="absolute right-4 top-4 rounded-md p-2 text-muted-foreground hover:bg-muted hover:text-foreground"
      >
        <X class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { LogIn, X } from 'lucide-vue-next'

defineEmits(['close', 'switchToRegister'])

const form = ref({
  email: '',
  password: ''
})

const handleLogin = () => {
  console.log('Login attempt:', form.value)
  // Handle login logic here
}
</script>
