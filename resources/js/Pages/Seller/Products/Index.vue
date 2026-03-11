<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import SellerLayout from '@/Layouts/SellerLayout.vue';

const products = ref([]);
const meta = ref({});
const loading = ref(true);
const page = ref(1);
const search = ref('');
let searchTimer = null;

async function load() {
    loading.value = true;
    try {
        const res = await window.axios.get('/api/v1/seller/products', {
            params: { page: page.value, search: search.value || undefined }
        });
        const d = res.data.data;
        products.value = d?.data ?? d ?? [];
        meta.value = d?.meta ?? {};
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

function onSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { page.value = 1; load(); }, 400);
}

async function toggleActive(product) {
    product._toggling = true;
    try {
        const res = await window.axios.patch(`/api/v1/seller/products/${product.id}/toggle-active`);
        product.is_active = res.data.data?.is_active ?? !product.is_active;
    } catch (e) {
        alert('Could not update product.');
    } finally {
        product._toggling = false;
    }
}

async function deleteProduct(product) {
    if (!confirm(`Delete "${product.name}"?`)) return;
    product._deleting = true;
    try {
        await window.axios.delete(`/api/v1/seller/products/${product.id}`);
        products.value = products.value.filter(p => p.id !== product.id);
    } catch (e) {
        alert('Could not delete product.');
    } finally {
        product._deleting = false;
    }
}

function currency(v) {
    return '₹' + Number(v ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
}

onMounted(load);
</script>

<template>
    <Head title="My Products | Seller" />
    <SellerLayout>
        <div class="space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h1 class="text-2xl font-bold text-gray-900">My Products</h1>
                <a href="/seller/products/create"
                    class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-1.5">
                    + Add Product
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4">
                <input v-model="search" @input="onSearch" type="text" placeholder="Search products..."
                    class="w-full sm:w-72 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
            </div>

            <div v-if="loading" class="space-y-2">
                <div v-for="i in 5" :key="i" class="bg-white rounded-xl h-16 animate-pulse"></div>
            </div>

            <div v-else class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Product</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3 hidden md:table-cell">Price</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3 hidden sm:table-cell">Stock</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Status</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in products" :key="product.id" class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <img v-if="product.thumbnail" :src="product.thumbnail" :alt="product.name"
                                        class="w-9 h-9 rounded-lg object-cover" />
                                    <div v-else class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-lg">📦</div>
                                    <span class="font-medium text-gray-900">{{ product.name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-gray-700 hidden md:table-cell">{{ currency(product.price) }}</td>
                            <td class="px-5 py-3.5 hidden sm:table-cell">
                                <span :class="product.stock > 0 ? 'text-green-600' : 'text-red-500'" class="font-medium">
                                    {{ product.stock ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <button @click="toggleActive(product)" :disabled="product._toggling"
                                    :class="product.is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                                    class="px-2.5 py-1 rounded-full text-xs font-medium disabled:opacity-50 transition-colors">
                                    {{ product._toggling ? '…' : (product.is_active ? 'Active' : 'Inactive') }}
                                </button>
                            </td>
                            <td class="px-5 py-3.5 flex items-center gap-3">
                                <a :href="`/seller/products/${product.id}/edit`" class="text-xs text-amber-600 hover:text-amber-700 font-medium">Edit</a>
                                <button @click="deleteProduct(product)" :disabled="product._deleting"
                                    class="text-xs text-red-500 hover:text-red-600 font-medium disabled:opacity-50">
                                    {{ product._deleting ? '…' : 'Delete' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!products.length">
                            <td colspan="5" class="text-center text-gray-400 py-10">No products found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta.last_page > 1" class="flex justify-center gap-2">
                <button v-for="p in meta.last_page" :key="p" @click="page = p; load()"
                    :class="p === meta.current_page ? 'bg-amber-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'"
                    class="w-9 h-9 rounded-lg text-sm font-medium transition-colors">{{ p }}</button>
            </div>
        </div>
    </SellerLayout>
</template>
