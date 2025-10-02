<template>
  <div class="min-h-screen bg-white">
    <!-- Navigation -->
    <Navigation @show-login="showLogin = true" @show-register="showRegister = true" />
    
    <!-- Hero Section - Completely redesigned layout -->
    <section class="pt-24 pb-16 bg-gradient-to-br from-blue-50 via-white to-green-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-8 items-center">
          <!-- Left content - 7 columns -->
          <div class="lg:col-span-7 space-y-8">
            <div class="space-y-6">
              <div class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Certificado por el SRI Ecuador
              </div>
              
              <h1 class="text-5xl lg:text-6xl font-bold text-gray-900 leading-tight">
                Facturación
                <span class="text-blue-600">Masiva</span>
                <br>Inteligente
              </h1>
              
              <p class="text-xl text-gray-600 leading-relaxed max-w-2xl">
                Automatiza completamente tu proceso de facturación con integración directa al SRI. 
                Procesa miles de facturas y envíalas por email en segundos.
              </p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4">
              <button @click="showRegister = true" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium text-lg">
                Comenzar Gratis
              </button>
              <button class="border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:border-gray-400 transition-colors font-medium text-lg">
                Ver Demo
              </button>
            </div>
            
            <!-- Trust indicators -->
            <div class="flex items-center space-x-8 pt-4">
              <div class="flex items-center space-x-2 text-sm text-gray-500">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span>Sin tarjeta de crédito</span>
              </div>
              <div class="flex items-center space-x-2 text-sm text-gray-500">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span>Configuración en 5 min</span>
              </div>
            </div>
          </div>
          
          <!-- Right content - 5 columns -->
          <div class="lg:col-span-5">
            <div class="relative">
              <!-- Main dashboard mockup -->
              <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                  <h3 class="text-lg font-semibold text-gray-900">Dashboard SRI</h3>
                  <div class="flex space-x-2">
                    <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                    <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                  </div>
                </div>
                
                <!-- Stats cards -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                  <div class="bg-blue-50 p-4 rounded-xl">
                    <div class="text-2xl font-bold text-blue-600">{{ animatedStats.invoices }}</div>
                    <div class="text-sm text-gray-600">Facturas Hoy</div>
                  </div>
                  <div class="bg-green-50 p-4 rounded-xl">
                    <div class="text-2xl font-bold text-green-600">{{ animatedStats.success }}%</div>
                    <div class="text-sm text-gray-600">Éxito SRI</div>
                  </div>
                </div>
                
                <!-- Progress bar -->
                <div class="space-y-3">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Procesamiento</span>
                    <span class="text-gray-900 font-medium">{{ animatedStats.progress }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-1000" :style="`width: ${animatedStats.progress}%`"></div>
                  </div>
                </div>
              </div>
              
              <!-- Floating notification -->
              <div class="absolute -bottom-4 -left-4 bg-green-500 text-white p-3 rounded-lg shadow-lg">
                <div class="flex items-center space-x-2 text-sm">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  <span>Factura enviada</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section - New asymmetric layout -->
    <section id="features" class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-4xl font-bold text-gray-900 mb-4">
            Todo lo que necesitas en una plataforma
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Desde la generación hasta el envío, gestiona todo tu proceso de facturación con herramientas profesionales
          </p>
        </div>
        
        <div class="grid lg:grid-cols-3 gap-8">
          <!-- Feature cards with new design -->
          <div v-for="(feature, index) in features" :key="index" 
               class="bg-white p-8 rounded-2xl shadow-sm border border-gray-200 hover:shadow-lg transition-shadow group">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-blue-200 transition-colors">
              <component :is="feature.icon" class="w-6 h-6 text-blue-600"/>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ feature.title }}</h3>
            <p class="text-gray-600 leading-relaxed">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section - Redesigned -->
    <section class="py-16 bg-blue-600">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-8 text-center text-white">
          <div v-for="stat in stats" :key="stat.label" class="space-y-2">
            <div class="text-4xl font-bold">{{ stat.value }}</div>
            <div class="text-blue-100">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials - New card design -->
    <section id="testimonials" class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-4xl font-bold text-gray-900 mb-4">
            Empresas que ya optimizaron su facturación
          </h2>
          <p class="text-xl text-gray-600">
            Más de 5,000 empresas confían en nuestra plataforma
          </p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
          <div v-for="testimonial in testimonials" :key="testimonial.name" 
               class="bg-gray-50 p-8 rounded-2xl border border-gray-200">
            <div class="flex items-center space-x-1 mb-4">
              <svg v-for="i in 5" :key="i" class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
            </div>
            <p class="text-gray-700 mb-6 leading-relaxed">"{{ testimonial.content }}"</p>
            <div class="flex items-center space-x-3">
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-blue-600 font-semibold text-lg">{{ testimonial.name.charAt(0) }}</span>
              </div>
              <div>
                <div class="font-semibold text-gray-900">{{ testimonial.name }}</div>
                <div class="text-sm text-gray-600">{{ testimonial.role }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section - New design -->
    <section class="py-20 bg-gradient-to-r from-blue-600 to-blue-700">
      <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl font-bold text-white mb-6">
          ¿Listo para automatizar tu facturación?
        </h2>
        <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
          Únete a miles de empresas que ya optimizaron su proceso. Comienza gratis hoy mismo.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <button @click="showRegister = true" class="bg-white text-blue-600 px-8 py-4 rounded-lg hover:bg-gray-50 transition-colors font-semibold text-lg">
            Comenzar Gratis
          </button>
          <button class="border-2 border-white text-white px-8 py-4 rounded-lg hover:bg-white/10 transition-colors font-semibold text-lg">
            Hablar con Ventas
          </button>
        </div>
      </div>
    </section>

    <!-- Footer - Simplified -->
    <footer class="py-12 bg-gray-900 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-8">
          <div class="space-y-4">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
              <span class="text-xl font-bold">FacturaPro</span>
            </div>
            <p class="text-gray-400">
              La solución más confiable para facturación masiva en Ecuador.
            </p>
          </div>
          
          <div>
            <h4 class="font-semibold mb-4">Producto</h4>
            <ul class="space-y-2 text-gray-400">
              <li><a href="#" class="hover:text-white transition-colors">Características</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Precios</a></li>
              <li><a href="#" class="hover:text-white transition-colors">API</a></li>
            </ul>
          </div>
          
          <div>
            <h4 class="font-semibold mb-4">Soporte</h4>
            <ul class="space-y-2 text-gray-400">
              <li><a href="#" class="hover:text-white transition-colors">Documentación</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Centro de Ayuda</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Contacto</a></li>
            </ul>
          </div>
          
          <div>
            <h4 class="font-semibold mb-4">Empresa</h4>
            <ul class="space-y-2 text-gray-400">
              <li><a href="#" class="hover:text-white transition-colors">Acerca de</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Blog</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Carreras</a></li>
            </ul>
          </div>
        </div>
        
        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
          <p>&copy; 2024 FacturaPro SRI. Todos los derechos reservados.</p>
        </div>
      </div>
    </footer>

    <!-- Modals -->
    <LoginModal 
      :show="showLogin" 
      @close="showLogin = false"
      @switch-to-register="showLogin = false; showRegister = true"
    />
    
    <RegisterModal 
      :show="showRegister" 
      @close="showRegister = false"
      @switch-to-login="showRegister = false; showLogin = true"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Navigation from './components/Navigation.vue'
import LoginModal from './components/LoginModal.vue'
import RegisterModal from './components/RegisterModal.vue'

// Modal states
const showLogin = ref(false)
const showRegister = ref(false)

// Animated stats for hero
const animatedStats = ref({
  invoices: 0,
  success: 0,
  progress: 0
})

// Features data
const features = ref([
  {
    title: "Integración SRI Certificada",
    description: "Conexión directa y certificada con el SRI para cumplimiento automático de normativas fiscales.",
    icon: "ShieldIcon"
  },
  {
    title: "Facturación Masiva",
    description: "Procesa miles de facturas simultáneamente con nuestro sistema optimizado para alto volumen.",
    icon: "BoltIcon"
  },
  {
    title: "Envío Automático",
    description: "Distribución automática de facturas por correo electrónico con confirmación de entrega.",
    icon: "MailIcon"
  },
  {
    title: "Dashboard en Tiempo Real",
    description: "Monitorea el estado de tus facturas y métricas desde un panel intuitivo.",
    icon: "ChartIcon"
  },
  {
    title: "API Robusta",
    description: "Integra fácilmente con tus sistemas existentes mediante nuestra API RESTful.",
    icon: "CodeIcon"
  },
  {
    title: "Soporte 24/7",
    description: "Equipo de soporte técnico especializado disponible las 24 horas.",
    icon: "SupportIcon"
  }
])

// Stats data
const stats = ref([
  { value: "5,000+", label: "Empresas Activas" },
  { value: "2M+", label: "Facturas Procesadas" },
  { value: "99.9%", label: "Uptime" },
  { value: "< 2s", label: "Tiempo Respuesta" }
])

// Testimonials data
const testimonials = ref([
  {
    name: "María González",
    role: "CFO, TechCorp Ecuador",
    content: "FacturaPro transformó completamente nuestro proceso de facturación. Lo que antes nos tomaba días, ahora lo hacemos en minutos."
  },
  {
    name: "Carlos Mendoza", 
    role: "Contador, Distribuidora Andina",
    content: "La integración con el SRI es perfecta. Nunca más tuvimos problemas de cumplimiento fiscal desde que implementamos el sistema."
  },
  {
    name: "Ana Rodríguez",
    role: "Gerente Financiera, RetailPlus", 
    content: "El soporte técnico es excepcional. Cualquier duda se resuelve inmediatamente y el sistema nunca falla."
  }
])

// Icon components
const ShieldIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>`
}

const BoltIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>`
}

const MailIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>`
}

const ChartIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>`
}

const CodeIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>`
}

const SupportIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>`
}

// Animation for hero stats
onMounted(() => {
  const animateValue = (target, key, finalValue, duration = 2000) => {
    const startTime = Date.now()
    const animate = () => {
      const elapsed = Date.now() - startTime
      const progress = Math.min(elapsed / duration, 1)
      const easeOut = 1 - Math.pow(1 - progress, 3)
      target[key] = Math.floor(finalValue * easeOut)
      
      if (progress < 1) {
        requestAnimationFrame(animate)
      }
    }
    animate()
  }

  setTimeout(() => {
    animateValue(animatedStats.value, 'invoices', 1247, 2000)
    animateValue(animatedStats.value, 'success', 99, 2000)
    animateValue(animatedStats.value, 'progress', 87, 2500)
  }, 500)
})
</script>
