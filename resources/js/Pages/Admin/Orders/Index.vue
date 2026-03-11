<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const orders = ref([]);
const meta = ref({});
const loading = ref(true);
const search = ref('');
const statusFilter = ref('');
const page = ref(1);
const updating = ref(null);

const statusOptions = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
const statusColors = {
    pending: 'bg-yellow-900/50 text-yellow-300',
    confirmed: 'bg-blue-900/50 text-blue-300',
    processing: 'bg-purple-900/50 text-purple-300',
    shipped: 'bg-indigo-900/50 text-indigo-300',
    delivered: 'bg-green-900/50 text-green-300',
    cancelled: 'bg-red-900/50 text-red-300',
};

async function load() {
    loading.value = true;
    try {
        const res = await window.axios.get('/api/v1/admin/orders', {
            params: { page: page.value, status: statusFilter.value },
        });
        const d = res.data.data;
        orders.value = d.data ?? d;
        meta.value = d.meta ?? {};
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function updateStatus(order, status) {
    updating.value = order.id;
    try {
        await window.axios.patch(`/api/v1/admin/orders/${order.id}/status`, { status });
        order.status = status;
    } catch (e) {
        alert('Failed to update order status.');
    } finally {
        updating.value = null;
    }
}

watch(statusFilter, () => { page.value = 1; load(); });
onMounted(load);

function formatDate(d) {
    return new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

<template>
    <Head title="Orders | Admin" />
    <AdminLayout>
        <div>
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <h1 class="text-2xl font-bold text-white">Orders</h1>
                <select v-model="statusFilter" class="bg-gray-800 border border-gray-700 text-gray-300 text-sm rounded-lg px-3 py-2 focus:border-blue-500 outline-none">
                    <option value="">All Statuses</option>
                    <option v-for="s in statusOptions" :key="s" :value="s" class="capitalize">{{ s }}</option>
                </select>
            </div>

            <div v-if="loading" class="space-y-2">
                <div v-for="i in 5" :key="i" class="bg-gray-800 rounded-xl h-14 animate-pulse"></div>
            </div>

            <div v-else class="bg-gray-800 rounded-xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Order #</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3 hidden md:table-cell">Customer</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Amount</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Status</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3 hidden lg:table-cell">Date</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in orders" :key="order.id" class="border-b border-gray-700/50 hover:bg-gray-700/30">
                            <td class="px-5 py-3.5 text-white font-mono">#{{ order.id }}</td>
                            <td class="px-5 py-3.5 text-gray-300 hidden md:table-cell">{{ order.user?.name }}</td>
                            <td class="px-5 py-3.5 text-white font-medium">₹{{ order.total_amount }}</td>
                            <td class="px-5 py-3.5">
                                <span :class="statusColors[order.status] ?? 'bg-gray-700 text-gray-300'"
                                    class="px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                                    {{ order.status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-500 text-xs hidden lg:table-cell">{{ formatDate(order.created_at) }}</td>
                            <td class="px-5 py-3.5">
                                <select @change="updateStatus(order, $event.target.value)"
                                    :disabled="updating === order.id"
                                    :value="order.status"
                                    class="bg-gray-700 border border-gray-600 text-gray-300 text-xs rounded-lg px-2 py-1 focus:border-blue-500 outline-none disabled:opacity-50">
                                    <option v-for="s in statusOptions" :key="s" :value="s" class="capitalize">{{ s }}</option>
                                </select>
                            </td>
                        </tr>
                        <tr v-if="!orders.length">
                            <td colspan="6" class="text-center text-gray-500 py-8">No orders found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta.last_page > 1" class="flex justify-center gap-2 mt-4">
                <button v-for="p in meta.last_page" :key="p" @click="page = p; load()"
                    :class="p === meta.current_page ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'"
                    class="w-9 h-9 rounded-lg text-sm font-medium transition-colors">{{ p }}</button>
            </div>
        </div>
    </AdminLayout>
</template>
