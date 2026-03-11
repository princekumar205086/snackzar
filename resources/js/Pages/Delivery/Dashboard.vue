<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DeliveryLayout from '@/Layouts/DeliveryLayout.vue';

const stats = ref(null);
const assignments = ref([]);
const loading = ref(true);

async function load() {
    try {
        const res = await window.axios.get('/api/v1/delivery/dashboard');
        const d = res.data.data ?? res.data;
        stats.value = d;
        assignments.value = d.recent_assignments ?? [];
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
    const map = { pending: 'bg-yellow-100 text-yellow-700', accepted: 'bg-blue-100 text-blue-700', picked_up: 'bg-indigo-100 text-indigo-700', delivered: 'bg-green-100 text-green-700' };
    return map[s] ?? 'bg-gray-100 text-gray-600';
}

onMounted(load);
</script>

<template>
    <Head title="Delivery Dashboard" />
    <DeliveryLayout>
        <div class="space-y-6">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>

            <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="i in 4" :key="i" class="bg-white rounded-xl h-24 animate-pulse"></div>
            </div>

            <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total Deliveries</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats?.total_deliveries ?? 0 }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Today</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ stats?.today_deliveries ?? 0 }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Pending</p>
                    <p class="text-3xl font-bold text-amber-600 mt-2">{{ stats?.pending_assignments ?? 0 }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Earnings</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">{{ currency(stats?.total_earnings) }}</p>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <a href="/delivery/assignments" class="bg-green-50 hover:bg-green-100 border border-green-200 rounded-xl p-4 text-center transition-colors">
                    <p class="text-2xl mb-1">📋</p>
                    <p class="text-sm font-medium text-green-800">My Assignments</p>
                </a>
                <a href="/delivery/assignments?status=pending" class="bg-yellow-50 hover:bg-yellow-100 border border-yellow-200 rounded-xl p-4 text-center transition-colors">
                    <p class="text-2xl mb-1">⏳</p>
                    <p class="text-sm font-medium text-yellow-800">Pending</p>
                </a>
                <a href="/delivery/profile" class="bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl p-4 text-center transition-colors">
                    <p class="text-2xl mb-1">👤</p>
                    <p class="text-sm font-medium text-blue-800">My Profile</p>
                </a>
            </div>

            <!-- Recent Assignments -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Recent Assignments</h2>
                    <a href="/delivery/assignments" class="text-sm text-green-600 hover:text-green-700">View all →</a>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Order #</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3 hidden md:table-cell">Customer</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Status</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3 hidden lg:table-cell">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="a in assignments" :key="a.id" class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="px-5 py-3 font-mono text-xs text-green-600">#{{ a.order_id ?? a.id }}</td>
                            <td class="px-5 py-3 text-gray-700 hidden md:table-cell">{{ a.customer_name ?? a.order?.user?.name }}</td>
                            <td class="px-5 py-3">
                                <span :class="statusColor(a.status)" class="px-2 py-0.5 rounded-full text-xs font-medium capitalize">{{ a.status }}</span>
                            </td>
                            <td class="px-5 py-3 text-gray-400 text-xs hidden lg:table-cell">{{ formatDate(a.created_at) }}</td>
                        </tr>
                        <tr v-if="!assignments.length">
                            <td colspan="4" class="text-center text-gray-400 py-8">No assignments yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DeliveryLayout>
</template>
