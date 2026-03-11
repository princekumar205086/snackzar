<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ id: [String, Number] });
const user = ref(null);
const orders = ref([]);
const loading = ref(true);
const toggling = ref(false);

async function load() {
    try {
        const [uRes, oRes] = await Promise.all([
            window.axios.get(`/api/v1/admin/users/${props.id}`),
            window.axios.get(`/api/v1/admin/orders`, { params: { user_id: props.id, per_page: 10 } }),
        ]);
        user.value = uRes.data.data ?? uRes.data;
        const od = oRes.data.data;
        orders.value = od?.data ?? od ?? [];
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function toggleStatus() {
    if (!user.value) return;
    toggling.value = true;
    try {
        const res = await window.axios.patch(`/api/v1/admin/users/${props.id}/status`);
        user.value.is_banned = res.data.data?.is_banned ?? !user.value.is_banned;
    } catch (e) {
        alert('Could not update status.');
    } finally {
        toggling.value = false;
    }
}

function formatDate(d) {
    return new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}
function currency(v) {
    return '₹' + Number(v ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
}

onMounted(load);
</script>

<template>
    <Head title="User Detail | Admin" />
    <AdminLayout>
        <div>
            <div class="flex items-center gap-3 mb-6">
                <a href="/admin/users" class="text-gray-400 hover:text-white text-sm">← Users</a>
                <h1 class="text-2xl font-bold text-white">User Detail</h1>
            </div>

            <div v-if="loading">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div v-for="i in 3" :key="i" class="bg-gray-800 rounded-xl h-32 animate-pulse"></div>
                </div>
            </div>

            <div v-else-if="user" class="space-y-6">
                <!-- Profile Card -->
                <div class="bg-gray-800 rounded-xl p-6 flex flex-col sm:flex-row gap-5 items-start">
                    <div class="w-16 h-16 rounded-full bg-blue-600 flex items-center justify-center text-2xl font-bold text-white shrink-0">
                        {{ user.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div class="flex-1 space-y-1">
                        <h2 class="text-xl font-semibold text-white">{{ user.name }}</h2>
                        <p class="text-gray-400 text-sm">{{ user.email }}</p>
                        <p class="text-gray-400 text-sm">{{ user.phone ?? 'No phone' }}</p>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <span v-for="role in (user.roles ?? [])" :key="role"
                                class="px-2 py-0.5 bg-blue-900/50 text-blue-300 text-xs rounded-full">{{ role }}</span>
                            <span :class="user.is_banned ? 'bg-red-900/50 text-red-300' : 'bg-green-900/50 text-green-300'"
                                class="px-2 py-0.5 text-xs rounded-full">{{ user.is_banned ? 'Banned' : 'Active' }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 shrink-0">
                        <p class="text-gray-500 text-xs">Joined {{ formatDate(user.created_at) }}</p>
                        <button @click="toggleStatus" :disabled="toggling"
                            :class="user.is_banned ? 'bg-green-700 hover:bg-green-600' : 'bg-red-700 hover:bg-red-600'"
                            class="px-4 py-1.5 rounded-lg text-white text-sm font-medium disabled:opacity-50 transition-colors">
                            {{ toggling ? '…' : (user.is_banned ? 'Activate' : 'Ban User') }}
                        </button>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-gray-800 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-white">{{ user.orders_count ?? orders.length }}</p>
                        <p class="text-xs text-gray-400 mt-1">Total Orders</p>
                    </div>
                    <div class="bg-gray-800 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-amber-400">{{ currency(user.total_spend ?? 0) }}</p>
                        <p class="text-xs text-gray-400 mt-1">Total Spent</p>
                    </div>
                    <div class="bg-gray-800 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-white">{{ user.wishlist_count ?? 0 }}</p>
                        <p class="text-xs text-gray-400 mt-1">Wishlist Items</p>
                    </div>
                    <div class="bg-gray-800 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-white">{{ user.address_count ?? 0 }}</p>
                        <p class="text-xs text-gray-400 mt-1">Addresses</p>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-gray-800 rounded-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-700">
                        <h3 class="text-base font-semibold text-white">Recent Orders</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="text-left text-gray-400 font-medium px-5 py-3">Order #</th>
                                <th class="text-left text-gray-400 font-medium px-5 py-3">Status</th>
                                <th class="text-left text-gray-400 font-medium px-5 py-3">Total</th>
                                <th class="text-left text-gray-400 font-medium px-5 py-3 hidden md:table-cell">Date</th>
                                <th class="text-left text-gray-400 font-medium px-5 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in orders" :key="order.id" class="border-b border-gray-700/50">
                                <td class="px-5 py-3 text-blue-400 font-mono text-xs">#{{ order.id }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-700 text-gray-300">{{ order.status }}</span>
                                </td>
                                <td class="px-5 py-3 text-white">{{ currency(order.total) }}</td>
                                <td class="px-5 py-3 text-gray-500 text-xs hidden md:table-cell">{{ formatDate(order.created_at) }}</td>
                                <td class="px-5 py-3">
                                    <a :href="`/admin/orders/${order.id}`" class="text-xs text-blue-400 hover:text-blue-300">View</a>
                                </td>
                            </tr>
                            <tr v-if="!orders.length">
                                <td colspan="5" class="text-center text-gray-500 py-8">No orders found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
