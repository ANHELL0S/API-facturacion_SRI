<template>
  <section class="relative overflow-hidden bg-gradient-to-br from-background via-muted/30 to-background py-24 lg:py-32">
    <div class="container mx-auto px-4">
      <div class="grid items-center gap-12 lg:grid-cols-12">
        <!-- Content Column -->
        <div class="lg:col-span-7 space-y-8">
          <div class="space-y-6">
            <div class="inline-flex items-center rounded-full border border-border bg-muted px-3 py-1 text-xs font-medium">
              <Shield class="mr-2 h-3 w-3 text-primary" />
              Certificado por el SRI Ecuador
            </div>
            
            <h1 class="text-4xl font-bold tracking-tight text-foreground lg:text-6xl">
              Facturación Masiva
              <span class="bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">
                Inteligente
              </span>
            </h1>
            
            <p class="text-xl text-muted-foreground leading-relaxed max-w-2xl">
              Automatiza completamente tu proceso de facturación con integración directa al SRI. 
              Procesa miles de facturas y envíalas automáticamente por email en segundos.
            </p>
          </div>
          
          <div class="flex flex-col gap-4 sm:flex-row">
            <button 
              @click="$emit('openRegister')"
              class="inline-flex h-12 items-center justify-center rounded-md bg-primary px-8 text-base font-semibold text-primary-foreground shadow-lg transition-all hover:bg-primary/90 hover:shadow-xl"
            >
              <Zap class="mr-2 h-5 w-5" />
              Comenzar Ahora
            </button>
            <button class="inline-flex h-12 items-center justify-center rounded-md border border-border bg-background px-8 text-base font-semibold text-foreground shadow-sm transition-all hover:bg-muted">
              <Play class="mr-2 h-4 w-4" />
              Ver Demo
            </button>
          </div>
          
          <div class="flex items-center space-x-8 text-sm text-muted-foreground">
            <div class="flex items-center space-x-2">
              <CheckCircle class="h-4 w-4 text-accent" />
              <span>Setup en 5 minutos</span>
            </div>
            <div class="flex items-center space-x-2">
              <CheckCircle class="h-4 w-4 text-accent" />
              <span>Soporte 24/7</span>
            </div>
            <div class="flex items-center space-x-2">
              <CheckCircle class="h-4 w-4 text-accent" />
              <span>99.9% Uptime</span>
            </div>
          </div>
        </div>
        
        <!-- Dashboard Preview Column -->
        <div class="lg:col-span-5">
          <div class="relative">
            <div class="rounded-2xl border border-border bg-card p-6 shadow-2xl">
              <div class="mb-6 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-card-foreground">Dashboard en Vivo</h3>
                <div class="flex items-center space-x-2">
                  <div class="h-2 w-2 rounded-full bg-accent animate-pulse"></div>
                  <span class="text-xs text-muted-foreground">En línea</span>
                </div>
              </div>
              
              <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div class="rounded-lg bg-muted p-4">
                    <div class="text-2xl font-bold text-primary">{{ stats.invoicesProcessed.toLocaleString() }}</div>
                    <div class="text-xs text-muted-foreground">Facturas Hoy</div>
                  </div>
                  <div class="rounded-lg bg-muted p-4">
                    <div class="text-2xl font-bold text-accent">{{ stats.avgProcessingTime }}s</div>
                    <div class="text-xs text-muted-foreground">Tiempo Promedio</div>
                  </div>
                </div>
                
                <div class="rounded-lg bg-muted p-4">
                  <div class="mb-2 flex items-center justify-between">
                    <span class="text-sm font-medium">Tasa de Éxito SRI</span>
                    <span class="text-sm font-bold text-accent">{{ stats.successRate }}%</span>
                  </div>
                  <div class="h-2 w-full rounded-full bg-background">
                    <div 
                      class="h-2 rounded-full bg-gradient-to-r from-primary to-accent transition-all duration-1000"
                      :style="{ width: `${stats.successRate}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Shield, Zap, Play, CheckCircle } from 'lucide-vue-next'

defineEmits(['openRegister'])

const stats = ref({
  invoicesProcessed: 0,
  avgProcessingTime: 0,
  successRate: 0
})

onMounted(() => {
  // Animate counters
  const animateValue = (target, finalValue, duration = 2000) => {
    const startTime = Date.now()
    const animate = () => {
      const elapsed = Date.now() - startTime
      const progress = Math.min(elapsed / duration, 1)
      const easeOut = 1 - Math.pow(1 - progress, 3)
      target.value = Math.floor(finalValue * easeOut)
      
      if (progress < 1) {
        requestAnimationFrame(animate)
      }
    }
    animate()
  }

  setTimeout(() => {
    animateValue({ value: 0, set: (v) => stats.value.invoicesProcessed = v }, 2847)
    animateValue({ value: 0, set: (v) => stats.value.avgProcessingTime = v }, 1.2)
    animateValue({ value: 0, set: (v) => stats.value.successRate = v }, 99.8)
  }, 500)
})
</script>
