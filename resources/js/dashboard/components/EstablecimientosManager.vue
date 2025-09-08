<template>
    <div class="bg-white rounded-xl shadow-lg">
        <div class="flex justify-between items-center mb-4 px-6 pt-6">
            <h3 class="text-xl font-bold text-gray-800">Gestionar Establecimientos</h3>
            <div class="flex space-x-4">
                <RefreshButton :is-loading="isLoading" @click="fetchEstablecimientos" />
                <BaseButton @click="openCreateModal" variant="primary">
                    <template #icon>
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    Nuevo Establecimiento
                </BaseButton>
            </div>
        </div>

        <div class="flex justify-end my-4">
            <div class="relative w-full max-w-xs">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="text" v-model="searchQuery" placeholder="Buscar..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
        </div>

        <TableSkeleton v-if="isLoading" />
        <DataTable v-else :data="processedEstablecimientos" :headers="headers" :sort-key="sortKey" :sort-order="sortOrder" @sort="sortBy">
            <template #cell(actions)="{ row }">
                <div class="space-x-2 text-center">
                    <button @click="openEditModal(row)" title="Editar" class="p-1 text-yellow-600 hover:text-yellow-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </button>
                    <button @click="confirmDelete(row)" title="Eliminar" class="p-1 text-red-600 hover:text-red-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </template>
        </DataTable>

        <EstablecimientoModal
            :show="isModalOpen"
            :establecimiento="selectedEstablecimiento"
            :is-loading="isSubmitting"
            :is-sidebar-open="isSidebarOpen"
            @close="closeModal"
            @save="handleSave"
        />
    </div>
</template>

<script>
import axios from 'axios';
import DataTable from './DataTable.vue';
import BaseButton from './BaseButton.vue';
import EstablecimientoModal from './EstablecimientoModal.vue';
import RefreshButton from './RefreshButton.vue';
import TableSkeleton from './TableSkeleton.vue';

export default {
    name: 'EstablecimientosManager',
    props: {
        isSidebarOpen: {
            type: Boolean,
            default: false,
        }
    },
    components: {
        DataTable,
        BaseButton,
        EstablecimientoModal,
        RefreshButton,
        TableSkeleton,
    },
    data() {
        return {
            establecimientos: [],
            headers: [
                { text: 'Número', value: 'numero' },
                { text: 'Nombre', value: 'nombre' },
                { text: 'Dirección', value: 'direccion' },
                { text: 'Acciones', value: 'actions' },
            ],
            isLoading: false,
            isSubmitting: false,
            isModalOpen: false,
            selectedEstablecimiento: null,
            token: localStorage.getItem('jwt_token'),
            searchQuery: '',
            sortKey: 'numero',
            sortOrder: 'asc',
        };
    },
    computed: {
        processedEstablecimientos() {
            let filtered = [...this.establecimientos];

            if (this.searchQuery) {
                const lowerCaseQuery = this.searchQuery.toLowerCase();
                filtered = filtered.filter(item => {
                    return (item.nombre || '').toLowerCase().includes(lowerCaseQuery) ||
                           (item.numero || '').toLowerCase().includes(lowerCaseQuery) ||
                           (item.direccion || '').toLowerCase().includes(lowerCaseQuery);
                });
            }

            if (this.sortKey) {
                filtered.sort((a, b) => {
                    let valA = a[this.sortKey];
                    let valB = b[this.sortKey];
                    if (valA < valB) return this.sortOrder === 'asc' ? -1 : 1;
                    if (valA > valB) return this.sortOrder === 'asc' ? 1 : -1;
                    return 0;
                });
            }

            return filtered;
        }
    },
    async created() {
        await this.fetchEstablecimientos();
    },
    methods: {
        sortBy(key) {
            if (this.sortKey === key) {
                this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortKey = key;
                this.sortOrder = 'asc';
            }
        },
        async fetchEstablecimientos() {
            this.isLoading = true;
            try {
                const response = await axios.get('/api/establecimientos', {
                    headers: { 'Authorization': `Bearer ${this.token}` }
                });
                this.establecimientos = response.data.data.data;
            } catch (error) {
                console.error('Error fetching establecimientos:', error);
                // TODO: Show user-friendly error message
            } finally {
                this.isLoading = false;
            }
        },
        openCreateModal() {
            this.selectedEstablecimiento = null;
            this.isModalOpen = true;
        },
        openEditModal(establecimiento) {
            this.selectedEstablecimiento = establecimiento;
            this.isModalOpen = true;
        },
        closeModal() {
            this.isModalOpen = false;
            this.selectedEstablecimiento = null;
        },
        async handleSave(establecimientoData) {
            if (this.selectedEstablecimiento) {
                await this.handleUpdate(establecimientoData);
            } else {
                await this.handleCreate(establecimientoData);
            }
        },
        async handleCreate(data) {
            this.isSubmitting = true;
            try {
                await axios.post('/api/establecimientos', data, {
                    headers: { 'Authorization': `Bearer ${this.token}` }
                });
                this.closeModal();
                await this.fetchEstablecimientos(); // Refresh list
                this.$emitter.emit('establishments-updated');
            } catch (error) {
                console.error('Error creating establecimiento:', error);
                // TODO: Show user-friendly error message
            } finally {
                this.isSubmitting = false;
            }
        },
        async handleUpdate(data) {
            this.isSubmitting = true;
            try {
                await axios.put(`/api/establecimientos/${data.id}`, data, {
                    headers: { 'Authorization': `Bearer ${this.token}` }
                });
                this.closeModal();
                await this.fetchEstablecimientos(); // Refresh list
                this.$emitter.emit('establishments-updated');
            } catch (error) {
                console.error('Error updating establecimiento:', error);
                // TODO: Show user-friendly error message
            } finally {
                this.isSubmitting = false;
            }
        },
        async confirmDelete(establecimiento) {
            if (window.confirm(`¿Está seguro de que desea eliminar el establecimiento "${establecimiento.nombre}"?`)) {
                this.isLoading = true; // Use main loading indicator for delete
                try {
                    await axios.delete(`/api/establecimientos/${establecimiento.id}`, {
                        headers: { 'Authorization': `Bearer ${this.token}` }
                    });
                    await this.fetchEstablecimientos(); // Refresh list
                    this.$emitter.emit('establishments-updated');
                } catch (error) {
                    console.error('Error deleting establecimiento:', error);
                    // TODO: Show user-friendly error message
                } finally {
                    this.isLoading = false;
                }
            }
        },
    }
}
</script>
