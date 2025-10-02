<template>
  <header class="sticky top-0 z-50 w-full border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
    <div class="container mx-auto flex h-16 items-center justify-between px-4">
      <div class="flex items-center space-x-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary">
          <FileText class="h-5 w-5 text-primary-foreground" />
        </div>
        <div class="flex flex-col">
          <span class="text-lg font-bold text-foreground">FacturaPro</span>
          <span class="text-xs text-muted-foreground">SRI Certified</span>
        </div>
      </div>
      
      <nav class="hidden items-center space-x-8 md:flex">
        <a href="#features" class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground">
          Características
        </a>
        <a href="#pricing" class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground">
          Precios
        </a>
        <a href="#testimonials" class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground">
          Testimonios
        </a>
        <div class="flex items-center space-x-3">
          <button 
            @click="$emit('openLogin')"
            class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
          >
            Iniciar Sesión
          </button>
          <button 
            @click="$emit('openRegister')"
            class="inline-flex h-9 items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90"
          >
            Comenzar Gratis
          </button>
        </div>
      </nav>
    </div>
  </header>
</template>

<script setup>
import { FileText } from 'lucide-vue-next'

defineEmits(['openLogin', 'openRegister'])
</script>
