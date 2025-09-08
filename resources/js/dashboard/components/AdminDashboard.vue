<template>
  <AppLayout
    :navigation="navigation"
    :current-view="currentView"
    :is-sidebar-open="isSidebarOpen"
    header-title="Panel de Administración"
    sidebar-title="Admin"
    @navigate="currentView = $event"
    @logout="$emit('logout')"
    @toggle-sidebar="isSidebarOpen = !isSidebarOpen"
  >
    <AdminUsers v-if="currentView === 'users'" :token="token" :is-sidebar-open="isSidebarOpen" />
    <AdminClients v-if="currentView === 'clients'" :token="token" :is-sidebar-open="isSidebarOpen" />
  </AppLayout>
</template>

<script>
import { h } from 'vue';
import AppLayout from './AppLayout.vue';
import AdminUsers from './AdminUsers.vue';
import AdminClients from './AdminClients.vue';

const IconUsers = {
  render() { return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', xmlns: 'http://www.w3.org/2000/svg', 'stroke-width': 2 }, [ h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' }), h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z' }) ]); }
};
const IconClients = {
  render() { return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', xmlns: 'http://www.w3.org/2000/svg', 'stroke-width': 2 }, [ h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' }) ]); }
};

export default {
  name: 'AdminDashboard',
  components: {
    AppLayout,
    AdminUsers,
    AdminClients,
  },
  props: {
    token: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      isSidebarOpen: true,
      currentView: 'users',
      navigation: [
        { name: 'Gestionar Usuarios', view: 'users', icon: IconUsers },
        { name: 'Gestionar Clientes', view: 'clients', icon: IconClients },
      ],
    };
  },
};
</script>
