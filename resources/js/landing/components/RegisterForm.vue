<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl border border-border bg-card p-8 shadow-2xl">
      <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-accent">
          <UserPlus class="h-6 w-6 text-accent-foreground" />
        </div>
        <h2 class="text-2xl font-bold text-card-foreground">Crear Cuenta</h2>
        <p class="text-sm text-muted-foreground">Comienza tu prueba gratuita de 14 días</p>
      </div>
      
      <form @submit.prevent="handleRegister" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <label class="text-sm font-medium text-card-foreground">Nombre</label>
            <input 
              v-model="form.firstName"
              type="text" 
              required
              class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
              placeholder="Juan"
            />
          </div>
          <div class="space-y-2">
            <label class="text-sm font-medium text-card-foreground">Apellido</label>
            <input 
              v-model="form.lastName"
              type="text" 
              required
              class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
              placeholder="Pérez"
            />
          </div>
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium text-card-foreground">Email Empresarial</label>
          <input 
            v-model="form.email"
            type="email" 
            required
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
            placeholder="juan@tuempresa.com"
          />
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium text-card-foreground">Empresa</label>
          <input 
            v-model="form.company"
            type="text" 
            required
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
            placeholder="Tu Empresa S.A."
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
        
        <div class="flex items-start space-x-2">
          <input type="checkbox" required class="mt-1 rounded border-border text-primary focus:ring-primary/20" />
          <label class="text-sm text-muted-foreground">
            Acepto los <a href="#" class="text-primary hover:underline">términos y condiciones</a> 
            y la <a href="#" class="text-primary hover:underline">política de privacidad</a>
          </label>
        </div>
        
        <button 
          type="submit"
          class="w-full rounded-md bg-accent py-2.5 text-sm font-semibold text-accent-foreground shadow transition-colors hover:bg-accent/90"
        >
          Crear Cuenta Gratis
        </button>
      </form>
      
      <div class="mt-6 text-center">
        <p class="text-sm text-muted-foreground">
          ¿Ya tienes cuenta? 
          <button @click="$emit('switchToLogin')" class="text-primary hover:underline">
            Inicia sesión aquí
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
import { UserPlus, X } from 'lucide-vue-next'

defineEmits(['close', 'switchToLogin'])

const form = ref({
  firstName: '',
  lastName: '',
  email: '',
  company: '',
  password: ''
})

const handleRegister = () => {
  console.log('Register attempt:', form.value)
  // Handle registration logic here
}
</script>
