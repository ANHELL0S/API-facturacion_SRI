<template>
    <div class="bg-white rounded-xl shadow-lg">
        <div class="flex justify-between items-center mb-4 px-6 pt-6">
            <h3 class="text-xl font-bold text-gray-800">Gestionar Puntos de Emisión</h3>
            <div class="flex space-x-4">
                <RefreshButton :is-loading="isLoading" @click="fetchPuntosEmision" />
                <BaseButton @click="openCreateModal" variant="primary" :disabled="establecimientos.length === 0">
                    <template #icon>
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    Nuevo Punto de Emisión
                </BaseButton>
            </div>
        </div>
        <p v-if="establecimientos.length === 0" class="text-sm text-yellow-600 bg-yellow-50 p-3 rounded-md">
            Debe crear al menos un establecimiento antes de poder agregar puntos de emisión.
        </p>

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
        <DataTable v-else :data="processedPuntosEmision" :headers="headers" :sort-key="sortKey" :sort-order="sortOrder" @sort="sortBy">
            <template #cell(actions)="{ row }">
                <div class="space-x-2 text-center">
                    <button @click="openEditModal(row)" title="Editar" class="p-1 text-yellow-600 hover:text-yellow-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </button>
                    <button @click="openSecuencialModal(row)" title="Editar Secuencial" class="p-1 text-green-600 hover:text-green-800 transition-colors">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                            <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button @click="confirmDelete(row)" title="Eliminar" class="p-1 text-red-600 hover:text-red-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </template>
        </DataTable>

        <PuntoEmisionModal
            :show="isModalOpen"
            :punto-emision="selectedPuntoEmision"
            :establecimientos="establecimientos"
            :is-loading="isSubmitting"
            :is-sidebar-open="isSidebarOpen"
            @close="closeModal"
            @save="handleSave"
        />

        <SecuencialModal
            :show="isSecuencialModalOpen"
            :punto-emision="selectedPuntoEmisionForSecuencial"
            :is-loading="isSubmitting"
            :is-sidebar-open="isSidebarOpen"
            @close="closeSecuencialModal"
            @save="handleSaveSecuencial"
        />
    </div>
</template>

<script>
import axios from 'axios';
import DataTable from './DataTable.vue';
import BaseButton from './BaseButton.vue';
import PuntoEmisionModal from './PuntoEmisionModal.vue';
import SecuencialModal from './SecuencialModal.vue';
import RefreshButton from './RefreshButton.vue';
import TableSkeleton from './TableSkeleton.vue';

export default {
    name: 'PuntosEmisionManager',
    props: {
        isSidebarOpen: {
            type: Boolean,
            default: false,
        }
    },
    components: {
        DataTable,
        BaseButton,
        PuntoEmisionModal,
        SecuencialModal,
        RefreshButton,
        TableSkeleton,
    },
    data() {
        return {
            puntosEmision: [],
            establecimientos: [],
            headers: [
                { text: 'Establecimiento', value: 'establecimiento_codigo' },
                { text: 'Número', value: 'numero' },
                { text: 'Nombre', value: 'nombre' },
                { text: 'Próximo Secuencial', value: 'proximo_secuencial' },
                { text: 'Acciones', value: 'actions' },
            ],
            isLoading: false,
            isSubmitting: false,
            isModalOpen: false,
            selectedPuntoEmision: null,
            isSecuencialModalOpen: false,
            selectedPuntoEmisionForSecuencial: null,
            token: localStorage.getItem('jwt_token'),
            searchQuery: '',
            sortKey: 'establecimiento_codigo',
            sortOrder: 'asc',
        };
    },
    computed: {
        processedPuntosEmision() {
            let filtered = [...this.puntosEmision];

            if (this.searchQuery) {
                const lowerCaseQuery = this.searchQuery.toLowerCase();
                filtered = filtered.filter(item => {
                    return (item.nombre || '').toLowerCase().includes(lowerCaseQuery) ||
                           (item.numero || '').toLowerCase().includes(lowerCaseQuery) ||
                           (item.establecimiento_codigo || '').toLowerCase().includes(lowerCaseQuery);
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
        this.isLoading = true;
        await this.fetchEstablecimientos();
        if (this.establecimientos.length > 0) {
            await this.fetchPuntosEmision();
        }
        this.isLoading = false;
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
            try {
                const response = await axios.get('/api/establecimientos', {
                    headers: { 'Authorization': `Bearer ${this.token}` }
                });
                this.establecimientos = response.data.data.data;
            } catch (error) {
                console.error('Error fetching establecimientos:', error);
            }
        },
        async fetchPuntosEmision() {
            this.isLoading = true;
            try {
                const response = await axios.get('/api/puntos-emision', {
                    headers: { 'Authorization': `Bearer ${this.token}` }
                });
                this.puntosEmision = response.data.data.data.map(p => {
                    p.establecimiento_codigo = p.establecimiento?.numero || 'N/A';
                    return p;
                });
            } catch (error) {
                console.error('Error fetching puntos de emision:', error);
            } finally {
                this.isLoading = false;
            }
        },
        openCreateModal() {
            this.selectedPuntoEmision = null;
            this.isModalOpen = true;
        },
        openEditModal(puntoEmision) {
            this.selectedPuntoEmision = puntoEmision;
            this.isModalOpen = true;
        },
        closeModal() {
            this.isModalOpen = false;
            this.selectedPuntoEmision = null;
        },
        openSecuencialModal(puntoEmision) {
            this.selectedPuntoEmisionForSecuencial = puntoEmision;
            this.isSecuencialModalOpen = true;
        },
        closeSecuencialModal() {
            this.isSecuencialModalOpen = false;
            this.selectedPuntoEmisionForSecuencial = null;
        },
        async handleSaveSecuencial(data) {
            this.isSubmitting = true;
            try {
                await axios.put(`/api/puntos-emision/${this.selectedPuntoEmisionForSecuencial.id}/secuencial`, data, {
                    headers: { 'Authorization': `Bearer ${this.token}` }
                });
                this.closeSecuencialModal();
                await this.fetchPuntosEmision();
                this.$emitter.emit('puntos-emision-updated');
                this.$emitter.emit('show-alert', { type: 'success', message: 'Secuencial actualizado exitosamente.' });
            } catch (error) {
                console.error('Error updating secuencial:', error);
                const errorMessage = error.response?.data?.message || 'Error al actualizar el secuencial.';
                this.$emitter.emit('show-alert', { type: 'error', message: errorMessage });
            } finally {
                this.isSubmitting = false;
            }
        },
        async handleSave(data) {
            if (this.selectedPuntoEmision) {
                await this.handleUpdate(data);
            } else {
                await this.handleCreate(data);
            }
        },
        async handleCreate(data) {
            this.isSubmitting = true;
            try {
                await axios.post('/api/puntos-emision', data, {
                    headers: { 'Authorization': `Bearer ${this.token}` }
                });
                this.closeModal();
                await this.fetchPuntosEmision();
                this.$emitter.emit('puntos-emision-updated');
            } catch (error) {
                console.error('Error creating punto de emision:', error);
            } finally {
                this.isSubmitting = false;
            }
        },
        async handleUpdate(data) {
            this.isSubmitting = true;
            try {
                await axios.put(`/api/puntos-emision/${this.selectedPuntoEmision.id}`, data, {
                    headers: { 'Authorization': `Bearer ${this.token}` }
                });
                this.closeModal();
                await this.fetchPuntosEmision();
                this.$emitter.emit('puntos-emision-updated');
            } catch (error) {
                console.error('Error updating punto de emision:', error);
            } finally {
                this.isSubmitting = false;
            }
        },
        async confirmDelete(puntoEmision) {
            if (window.confirm(`¿Está seguro de que desea eliminar el punto de emisión "${puntoEmision.nombre}"?`)) {
                try {
                    await axios.delete(`/api/puntos-emision/${puntoEmision.id}`, {
                        headers: { 'Authorization': `Bearer ${this.token}` }
                    });
                    await this.fetchPuntosEmision();
                    this.$emitter.emit('puntos-emision-updated');
                    this.$emitter.emit('show-alert', { type: 'success', message: 'Punto de emisión eliminado exitosamente.' });
                } catch (error) {
                    console.error('Error deleting punto de emision:', error);
                    this.$emitter.emit('show-alert', { type: 'error', message: 'Error al eliminar el punto de emisión.' });
                }
            }
        },
    }
}
</script>
