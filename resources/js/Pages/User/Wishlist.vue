<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';

const items = ref([]);
const meta = ref({});
const loading = ref(true);
const page = ref(1);
const removing = ref(null);

async function load() {
    loading.value = true;
    try {
        const res = await window.axios.get('/api/v1/user/wishlist', { params: { page: page.value } });
        const d = res.data.data;
        items.value = d?.data ?? d ?? [];
        meta.value = d?.meta ?? {};
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function remove(item) {
    removing.value = item.id;
    try {
        await window.axios.delete(`/api/v1/user/wishlist/${item.product_id ?? item.id}`);
        items.value = items.value.filter(i => i.id !== item.id);
    } catch (e) {
        alert('Could not remove item.');
    } finally {
        removing.value = null;
    }
}

function currency(v) {
    return '₹' + Number(v ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
}

onMounted(load);
</script>

<template>
    <Head title="My Wishlist" />
    <UserLayout>
        <div class="space-y-5">
            <h1 class="text-2xl font-bold text-gray-900">My Wishlist</h1>

            <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <div v-for="i in 6" :key="i" class="bg-white rounded-xl h-64 animate-pulse"></div>
            </div>

            <div v-else-if="items.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <div v-for="item in items" :key="item.id"
                    class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow group">
                    <div class="relative">
                        <img v-if="item.product?.thumbnail" :src="item.product.thumbnail" :alt="item.product?.name"
                            class="w-full h-40 object-cover" />
                        <div v-else class="w-full h-40 bg-gray-100 flex items-center justify-center text-4xl">📦</div>
                        <button @click="remove(item)" :disabled="removing === item.id"
                            class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full shadow flex items-center justify-center text-red-500 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-all disabled:opacity-50 text-sm">
                            {{ removing === item.id ? '…' : '✕' }}
                        </button>
                    </div>
                    <div class="p-3">
                        <h3 class="text-sm font-medium text-gray-900 truncate">{{ item.product?.name }}</h3>
                        <div class="mt-1.5 flex items-center justify-between">
                            <div>
                                <span v-if="item.product?.sale_price" class="text-sm font-bold text-amber-600">{{ currency(item.product.sale_price) }}</span>
                                <span v-else class="text-sm font-bold text-gray-900">{{ currency(item.product?.price) }}</span>
                                <span v-if="item.product?.sale_price" class="text-xs text-gray-400 line-through ml-1">{{ currency(item.product?.price) }}</span>
                            </div>
                        </div>
                        <a :href="`/products/${item.product?.slug ?? item.product_id}`"
                            class="mt-2 block text-center bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium py-1.5 rounded-lg transition-colors">
                            View Product
                        </a>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white rounded-xl shadow-sm p-12 text-center">
                <p class="text-4xl mb-3">💛</p>
                <h2 class="text-lg font-semibold text-gray-700">Your wishlist is empty</h2>
                <p class="text-gray-500 text-sm mt-1 mb-4">Save products you love for later.</p>
                <a href="/products" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-5 py-2 rounded-lg inline-block transition-colors">
                    Explore Products
                </a>
            </div>

            <div v-if="meta.last_page > 1" class="flex justify-center gap-2">
                <button v-for="p in meta.last_page" :key="p" @click="page = p; load()"
                    :class="p === meta.current_page ? 'bg-amber-500 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                    class="w-9 h-9 rounded-lg text-sm font-medium transition-colors">{{ p }}</button>
            </div>
        </div>
    </UserLayout>
</template>
