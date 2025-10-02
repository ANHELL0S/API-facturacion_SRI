<template>
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800">Gestionar Productos</h3>
            <div class="flex space-x-4">
                <RefreshButton :is-loading="isLoading" @click="fetchProducts" />
                <BaseButton @click="openCreateModal" variant="primary">
                    <template #icon>
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    Nuevo Producto
                </BaseButton>
            </div>
        </div>

        <TableSkeleton v-if="isLoading" />
        <DataTable v-else :data="products" :headers="headers">
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

        <ProductModal
            :show="isModalOpen"
            :product="selectedProduct"
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
import ProductModal from './ProductModal.vue';
import RefreshButton from './RefreshButton.vue';
import TableSkeleton from './TableSkeleton.vue';

export default {
    name: 'ProductsManager',
    props: {
        isSidebarOpen: {
            type: Boolean,
            default: false,
        }
    },
    components: {
        DataTable,
        BaseButton,
        ProductModal,
        RefreshButton,
        TableSkeleton,
    },
    data() {
        return {
            products: [],
            headers: [
                { text: 'Código', value: 'code' },
                { text: 'Descripción', value: 'description' },
                { text: 'Precio Unitario', value: 'unit_price' },
                { text: 'Acciones', value: 'actions' },
            ],
            isLoading: false,
            isSubmitting: false,
            isModalOpen: false,
            selectedProduct: null,
            token: localStorage.getItem('jwt_token'),
        };
    },
    async created() {
        await this.fetchProducts();
    },
    methods: {
        async fetchProducts() {
            this.isLoading = true;
            try {
                const response = await axios.get('/api/products', {
                    headers: { 'Authorization': `Bearer ${this.token}` }
                });
                this.products = response.data.data;
            } catch (error) {
                console.error('Error fetching products:', error);
                this.$emitter.emit('show-alert', { type: 'error', message: 'Error al cargar los productos.' });
            } finally {
                this.isLoading = false;
            }
        },
        openCreateModal() {
            this.selectedProduct = null;
            this.isModalOpen = true;
        },
        openEditModal(product) {
            this.selectedProduct = product;
            this.isModalOpen = true;
        },
        closeModal() {
            this.isModalOpen = false;
            this.selectedProduct = null;
        },
        async handleSave(productData) {
            if (this.selectedProduct) {
                await this.handleUpdate(productData);
            } else {
                await this.handleCreate(productData);
            }
        },
        async handleCreate(data) {
            this.isSubmitting = true;
            try {
                await axios.post('/api/products', data, {
                    headers: { 'Authorization': `Bearer ${this.token}` }
                });
                this.closeModal();
                await this.fetchProducts();
                this.$emitter.emit('show-alert', { type: 'success', message: 'Producto creado exitosamente.' });
            } catch (error) {
                console.error('Error creating product:', error);
                const message = error.response?.data?.message || 'Error al crear el producto.';
                this.$emitter.emit('show-alert', { type: 'error', message });
            } finally {
                this.isSubmitting = false;
            }
        },
        async handleUpdate(data) {
            this.isSubmitting = true;
            try {
                await axios.put(`/api/products/${data.id}`, data, {
                    headers: { 'Authorization': `Bearer ${this.token}` }
                });
                this.closeModal();
                await this.fetchProducts();
                this.$emitter.emit('show-alert', { type: 'success', message: 'Producto actualizado exitosamente.' });
            } catch (error) {
                console.error('Error updating product:', error);
                const message = error.response?.data?.message || 'Error al actualizar el producto.';
                this.$emitter.emit('show-alert', { type: 'error', message });
            } finally {
                this.isSubmitting = false;
            }
        },
        async confirmDelete(product) {
            if (window.confirm(`¿Está seguro de que desea eliminar el producto "${product.description}"?`)) {
                this.isLoading = true;
                try {
                    await axios.delete(`/api/products/${product.id}`, {
                        headers: { 'Authorization': `Bearer ${this.token}` }
                    });
                    await this.fetchProducts();
                    this.$emitter.emit('show-alert', { type: 'success', message: 'Producto eliminado exitosamente.' });
                } catch (error) {
                    console.error('Error deleting product:', error);
                    this.$emitter.emit('show-alert', { type: 'error', message: 'Error al eliminar el producto.' });
                } finally {
                    this.isLoading = false;
                }
            }
        },
    }
}
</script>
