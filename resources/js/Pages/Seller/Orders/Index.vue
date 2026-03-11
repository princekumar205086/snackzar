<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import SellerLayout from '@/Layouts/SellerLayout.vue';

const orders = ref([]);
const meta = ref({});
const loading = ref(true);
const page = ref(1);
const statusFilter = ref('');

const statuses = ['', 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

async function load() {
    loading.value = true;
    try {
        const res = await window.axios.get('/api/v1/seller/orders', {
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
    const map = {
        pending: 'bg-yellow-100 text-yellow-700',
        confirmed: 'bg-blue-100 text-blue-700',
        processing: 'bg-indigo-100 text-indigo-700',
        shipped: 'bg-purple-100 text-purple-700',
        delivered: 'bg-green-100 text-green-700',
        cancelled: 'bg-red-100 text-red-700',
    };
    return map[s] ?? 'bg-gray-100 text-gray-600';
}

onMounted(load);
</script>

<template>
    <Head title="Orders | Seller" />
    <SellerLayout>
        <div class="space-y-5">
            <h1 class="text-2xl font-bold text-gray-900">Orders</h1>

            <div class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap gap-2">
                <button v-for="s in statuses" :key="s"
                    @click="statusFilter = s; page = 1; load()"
                    :class="statusFilter === s ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium capitalize transition-colors">
                    {{ s || 'All' }}
                </button>
            </div>

            <div v-if="loading" class="space-y-2">
                <div v-for="i in 5" :key="i" class="bg-white rounded-xl h-14 animate-pulse"></div>
            </div>

            <div v-else class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Order #</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3 hidden md:table-cell">Customer</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Status</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Total</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3 hidden lg:table-cell">Date</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in orders" :key="order.id" class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="px-5 py-3.5 font-mono text-xs text-amber-600">#{{ order.id }}</td>
                            <td class="px-5 py-3.5 text-gray-700 hidden md:table-cell">{{ order.customer_name ?? order.user?.name }}</td>
                            <td class="px-5 py-3.5">
                                <span :class="statusColor(order.status)" class="px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">{{ order.status }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-900 font-medium">{{ currency(order.total) }}</td>
                            <td class="px-5 py-3.5 text-gray-500 text-xs hidden lg:table-cell">{{ formatDate(order.created_at) }}</td>
                            <td class="px-5 py-3.5">
                                <a :href="`/seller/orders/${order.id}`" class="text-xs text-amber-600 hover:text-amber-700 font-medium">View</a>
                            </td>
                        </tr>
                        <tr v-if="!orders.length">
                            <td colspan="6" class="text-center text-gray-400 py-10">No orders found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta.last_page > 1" class="flex justify-center gap-2">
                <button v-for="p in meta.last_page" :key="p" @click="page = p; load()"
                    :class="p === meta.current_page ? 'bg-amber-500 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                    class="w-9 h-9 rounded-lg text-sm font-medium transition-colors">{{ p }}</button>
            </div>
        </div>
    </SellerLayout>
</template>
