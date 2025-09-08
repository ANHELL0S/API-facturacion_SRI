<template>
  <div>
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold">Gestionar Usuarios Clientes</h2>
      <div class="flex space-x-2">
        <RefreshButton :is-loading="isLoading" @click="fetchClients(1)" />
        <BaseButton @click="showClientModal = true">
            <template #icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </template>
            Crear Cliente
        </BaseButton>
      </div>
    </div>

    <TableSkeleton v-if="isLoading" />
    <DataTable v-else :headers="headers" :data="clients">
        <template #cell(status)="{ row }">
            <span :class="row.active_account ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ row.active_account ? 'Activo' : 'Inactivo' }}
            </span>
        </template>
        <template #cell(actions)="{ row }">
            <div class="flex items-center space-x-2">
                <button @click="toggleClientStatus(row)" :title="row.active_account ? 'Desactivar' : 'Activar'" class="p-1 rounded-full transition-colors" :class="row.active_account ? 'text-red-500 hover:bg-red-100 hover:text-red-700' : 'text-green-500 hover:bg-green-100 hover:text-green-700'">
                    <svg v-if="row.active_account" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>
                <button @click="editClient(row)" title="Editar" class="p-1 text-yellow-600 hover:text-yellow-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </button>
                <button @click="deleteClient(row)" title="Eliminar" class="p-1 text-red-600 hover:text-red-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        </template>
    </DataTable>

    <div class="py-4 px-6 flex justify-center">
        <Pagination
            :currentPage="pagination.currentPage"
            :totalPages="pagination.totalPages"
            @prev-page="handlePrevPage"
            @next-page="handleNextPage"
        />
    </div>

    <ClientModal
      v-if="showClientModal"
      :client="selectedClient"
      :token="token"
      :is-sidebar-open="isSidebarOpen"
      @close="closeClientModal"
      @client-saved="fetchClients(1)"
    />

  </div>
</template>

<script>
import axios from 'axios';
import DataTable from './DataTable.vue';
import BaseButton from './BaseButton.vue';
import ClientModal from './ClientModal.vue';
import Pagination from './Pagination.vue';
import TableSkeleton from './TableSkeleton.vue';
import RefreshButton from './RefreshButton.vue';

export default {
  name: 'AdminClients',
  components: {
    DataTable,
    BaseButton,
    ClientModal,
    Pagination,
    TableSkeleton,
    RefreshButton,
  },
  props: {
    token: {
      type: String,
      required: true,
    },
    isSidebarOpen: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      clients: [],
      headers: [
        { text: 'Nombre', value: 'name' },
        { text: 'Correo', value: 'email' },
        { text: 'RUC', value: 'ruc' },
        { text: 'Estado', value: 'status' },
        { text: 'Acciones', value: 'actions' },
      ],
      showClientModal: false,
      selectedClient: null,
      isLoading: false,
      pagination: {
        currentPage: 1,
        totalPages: 1,
      },
    };
  },
  mounted() {
    this.fetchClients(1);
  },
  unmounted() {
    this.clients = [];
    this.pagination.currentPage = 1;
    this.pagination.totalPages = 1;
  },
  methods: {
    async fetchClients(page) {
      this.isLoading = true;
      try {
        const response = await axios.get(`/api/admin/clients?page=${page}`, {
          headers: { Authorization: `Bearer ${this.token}` },
        });
        const paginatedData = response.data.data;
        this.clients = paginatedData.data;
        this.pagination.currentPage = paginatedData.current_page;
        this.pagination.totalPages = paginatedData.last_page;
      } catch (error) {
        console.error('Error fetching clients:', error);
      } finally {
        this.isLoading = false;
      }
    },
    editClient(client) {
      this.selectedClient = { ...client };
      this.showClientModal = true;
    },
    async toggleClientStatus(client) {
        const newStatus = !client.active_account;
        const action = newStatus ? 'activar' : 'desactivar';
        if (confirm(`¿Está seguro de que desea ${action} a ${client.name}?`)) {
            try {
                await axios.put(`/api/admin/clients/${client.id}`,
                    { active_account: newStatus ? 1 : 0 },
                    { headers: { Authorization: `Bearer ${this.token}` } }
                );
                this.$emitter.emit('show-alert', { type: 'success', message: `Cliente ${action} con éxito.` });
                this.fetchClients(this.pagination.currentPage);
            } catch (error) {
                console.error(`Error toggling client status:`, error);
                this.$emitter.emit('show-alert', { type: 'error', message: `No se pudo ${action} el cliente.` });
            }
        }
    },
    async deleteClient(client) {
      if (confirm(`¿Está seguro de que desea eliminar a ${client.name}?`)) {
        try {
          await axios.delete(`/api/admin/clients/${client.id}`, {
            headers: { Authorization: `Bearer ${this.token}` },
          });
          this.$emitter.emit('show-alert', { type: 'success', message: 'Cliente eliminado con éxito.' });
          this.fetchClients(this.pagination.currentPage);
        } catch (error) {
          console.error('Error deleting client:', error);
          this.$emitter.emit('show-alert', { type: 'error', message: 'No se pudo eliminar el cliente.' });
        }
      }
    },
    closeClientModal() {
      this.showClientModal = false;
      this.selectedClient = null;
    },
    handlePrevPage() {
      if (this.pagination.currentPage > 1) {
        this.fetchClients(this.pagination.currentPage - 1);
      }
    },
    handleNextPage() {
      if (this.pagination.currentPage < this.pagination.totalPages) {
        this.fetchClients(this.pagination.currentPage + 1);
      }
    },
  },
};
</script>
