<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';

const orders = ref([]);
const meta = ref({});
const loading = ref(true);
const page = ref(1);
const statusFilter = ref('');

const statuses = ['', 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

async function load() {
    loading.value = true;
    try {
        const res = await window.axios.get('/api/v1/user/orders', {
            params: { page: page.value, status: statusFilter.value || undefined }
        });
        const d = res.data.data;
        orders.value = d?.data ?? d ?? [];
        meta.value = d?.meta ?? {};
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

function currency(v) {
    return '₹' + Number(v ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
}
function formatDate(d) {
    return new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}
function statusColor(s) {
    const map = { pending: 'bg-yellow-100 text-yellow-700', confirmed: 'bg-blue-100 text-blue-700', processing: 'bg-indigo-100 text-indigo-700', shipped: 'bg-purple-100 text-purple-700', delivered: 'bg-green-100 text-green-700', cancelled: 'bg-red-100 text-red-700' };
    return map[s] ?? 'bg-gray-100 text-gray-600';
}

onMounted(load);
</script>

<template>
    <Head title="My Orders" />
    <UserLayout>
        <div class="space-y-5">
            <h1 class="text-2xl font-bold text-gray-900">My Orders</h1>

            <div class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap gap-2">
                <button v-for="s in statuses" :key="s"
                    @click="statusFilter = s; page = 1; load()"
                    :class="statusFilter === s ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium capitalize transition-colors">
                    {{ s || 'All' }}
                </button>
            </div>

            <div v-if="loading" class="space-y-3">
                <div v-for="i in 4" :key="i" class="bg-white rounded-xl h-20 animate-pulse"></div>
            </div>

            <div v-else class="space-y-3">
                <div v-for="order in orders" :key="order.id"
                    class="bg-white rounded-xl shadow-sm p-4 flex flex-col sm:flex-row sm:items-center gap-4 justify-between">
                    <div>
                        <p class="font-mono text-sm text-amber-600 font-semibold">Order #{{ order.id }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ formatDate(order.created_at) }} · {{ order.items_count ?? order.items?.length ?? 0 }} item(s)</p>
                    </div>
                    <div class="flex items-center gap-4 ml-auto sm:ml-0">
                        <span :class="statusColor(order.status)" class="px-2.5 py-1 rounded-full text-xs font-medium capitalize">{{ order.status }}</span>
                        <p class="text-sm font-bold text-gray-900">{{ currency(order.total) }}</p>
                        <a :href="`/orders/${order.id}`" class="text-sm text-amber-600 hover:text-amber-700 font-medium">Details →</a>
                    </div>
                </div>
                <div v-if="!orders.length" class="bg-white rounded-xl shadow-sm p-10 text-center">
                    <p class="text-gray-400">No orders found.</p>
                    <a href="/products" class="mt-3 inline-block text-sm text-amber-600 hover:text-amber-700 font-medium">Start shopping →</a>
                </div>
            </div>

            <div v-if="meta.last_page > 1" class="flex justify-center gap-2">
                <button v-for="p in meta.last_page" :key="p" @click="page = p; load()"
                    :class="p === meta.current_page ? 'bg-amber-500 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                    class="w-9 h-9 rounded-lg text-sm font-medium transition-colors">{{ p }}</button>
            </div>
        </div>
    </UserLayout>
</template>
