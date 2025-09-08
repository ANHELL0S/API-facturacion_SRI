<template>
  <div>
    <!-- Sidebar -->
    <div :class="['fixed inset-y-0 left-0 z-30 bg-gray-800 text-white transition-all duration-300 ease-in-out flex flex-col h-full', isSidebarOpen ? 'w-64' : 'w-20']">
      <div class="px-8 py-6 text-center overflow-hidden h-20 flex items-center justify-center">
        <img v-if="userProfile && userProfile.logo_path" :src="`/storage/${userProfile.logo_path}`" alt="Logo" class="h-16 w-auto mx-auto transition-all duration-300" :class="isSidebarOpen ? 'max-w-xs' : 'opacity-0 max-w-0'">
        <h2 v-else class="text-2xl font-semibold whitespace-nowrap transition-all duration-300" :class="isSidebarOpen ? 'max-w-xs' : 'opacity-0 max-w-0'">{{ sidebarTitle }}</h2>
      </div>
      <nav class="flex-grow px-4 py-2 space-y-2">
        <a v-for="item in navigation" :key="item.name" href="#" @click.prevent="$emit('navigate', item.view)"
           :class="['flex items-center px-4 py-2 rounded-md transition-colors', currentView === item.view ? 'bg-gray-700' : 'hover:bg-gray-700']">
          <component :is="item.icon" :class="['w-6 h-6 transition-all duration-300 flex-shrink-0', isSidebarOpen ? 'mr-3' : 'mx-auto']" />
          <span class="relative whitespace-nowrap transition-all duration-300" :class="isSidebarOpen ? '' : 'opacity-0'">
            {{ item.name }}
                  <span v-if="item.count > 0" class="absolute -top-2 -right-3 bg-red-500 text-white rounded-full w-3 h-3 flex items-center justify-center" style="font-size: 0.6rem;">
          {{ item.count }}
        </span>
          </span>
        </a>
      </nav>
      <div class="px-4 py-4 mt-auto">
        <button @click="$emit('logout')" class="w-full flex items-center justify-center px-4 py-2 font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors duration-200">
          <svg :class="['w-5 h-5 transition-all duration-300', isSidebarOpen ? 'mr-2' : 'mx-auto']" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
          <span class="whitespace-nowrap transition-all duration-300 overflow-hidden" :class="isSidebarOpen ? 'max-w-xs' : 'opacity-0 max-w-0'">Cerrar Sesión</span>
        </button>
      </div>
    </div>

    <div class="relative z-10 flex flex-col flex-1 transition-all duration-300 ease-in-out" :class="isSidebarOpen ? 'ml-64' : 'ml-20'">
      <!-- Header -->
      <header class="sticky top-0 z-20 flex justify-between items-center p-4 bg-white border-b-2 border-gray-200">
        <button @click="$emit('toggle-sidebar')" class="text-gray-500 focus:outline-none">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
        <h1 class="text-xl font-semibold">{{ headerTitle }}</h1>
      </header>

      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-8">
        <slot></slot>
      </main>
    </div>

    <!-- Global Download Progress Floating Alert -->
    <div v-if="downloadStore.activeBulkDownloads.length > 0"
         class="fixed bottom-4 right-4 w-80 bg-white p-4 rounded-lg shadow-lg border border-gray-200 z-50">
        <div v-for="job in downloadStore.activeBulkDownloads" :key="job.id" class="not-prose">
            <div class="flex items-center mb-2">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="font-bold text-gray-800">Descarga en progreso ({{ job.format.toUpperCase() }})</p>
            </div>
            <p v-if="job.status === 'pending'" class="text-sm text-gray-600">Iniciando...</p>
            <p v-if="job.status === 'processing'" class="text-sm text-gray-600">
                Procesando: {{ job.processed_files }} de {{ job.total_files }} archivos.
            </p>
            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                <div class="bg-blue-600 h-2.5 rounded-full"
                     :style="{ width: (job.processed_files / job.total_files * 100) + '%' }"
                     style="transition: width 0.5s ease;">
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
import downloadStore from '../utils/downloadStore.js';

export default {
    name: 'AppLayout',
    props: {
        userProfile: {
            type: Object,
            default: () => ({})
        },
        navigation: {
            type: Array,
            required: true,
        },
        currentView: {
            type: String,
            required: true,
        },
        headerTitle: {
            type: String,
            default: 'Dashboard',
        },
        sidebarTitle: {
            type: String,
            default: 'Dashboard'
        },
        isSidebarOpen: {
            type: Boolean,
            required: true,
        }
    },
    emits: ['navigate', 'logout', 'toggle-sidebar'],
    data() {
        return {
            downloadStore: downloadStore
        };
    },
    beforeUnmount() {
        this.downloadStore.clearPollers();
    }
};
</script>
