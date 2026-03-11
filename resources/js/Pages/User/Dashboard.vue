<script setup>
import { ref, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';

const page = usePage();
const orders = ref([]);
const wishlistCount = ref(0);
const loading = ref(true);

async function load() {
    try {
        const [oRes, wRes] = await Promise.all([
            window.axios.get('/api/v1/user/orders', { params: { per_page: 5 } }),
            window.axios.get('/api/v1/user/wishlist', { params: { per_page: 1 } }),
        ]);
        const od = oRes.data.data;
        orders.value = od?.data ?? od ?? [];
        wishlistCount.value = wRes.data.meta?.total ?? (wRes.data.data?.length ?? 0);
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
    const map = { pending: 'bg-yellow-100 text-yellow-700', confirmed: 'bg-blue-100 text-blue-700', delivered: 'bg-green-100 text-green-700', cancelled: 'bg-red-100 text-red-700' };
    return map[s] ?? 'bg-gray-100 text-gray-600';
}

onMounted(load);
</script>

<template>
    <Head title="My Dashboard" />
    <UserLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Hello, {{ page.props.auth?.user?.name ?? 'Guest' }}! 👋</h1>
                <p class="text-gray-500 text-sm mt-1">Welcome back to your account dashboard.</p>
            </div>

            <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div v-for="i in 4" :key="i" class="bg-white rounded-xl h-20 animate-pulse"></div>
            </div>

            <div v-else class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <a href="/orders" class="bg-white rounded-xl shadow-sm p-4 text-center hover:shadow-md transition-shadow">
                    <p class="text-2xl font-bold text-gray-900">{{ orders.length }}</p>
                    <p class="text-xs text-gray-500 mt-1">Recent Orders</p>
                </a>
                <a href="/wishlist" class="bg-white rounded-xl shadow-sm p-4 text-center hover:shadow-md transition-shadow">
                    <p class="text-2xl font-bold text-red-500">{{ wishlistCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">Wishlist Items</p>
                </a>
                <a href="/addresses" class="bg-white rounded-xl shadow-sm p-4 text-center hover:shadow-md transition-shadow">
                    <p class="text-2xl">📍</p>
                    <p class="text-xs text-gray-500 mt-1">My Addresses</p>
                </a>
                <a href="/profile" class="bg-white rounded-xl shadow-sm p-4 text-center hover:shadow-md transition-shadow">
                    <p class="text-2xl">👤</p>
                    <p class="text-xs text-gray-500 mt-1">My Profile</p>
                </a>
            </div>

            <!-- Quick Actions -->
            <div class="bg-amber-50 border border-amber-100 rounded-xl p-5 flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                <div>
                    <h3 class="font-semibold text-amber-900">Continue Shopping</h3>
                    <p class="text-sm text-amber-700 mt-0.5">Explore fresh snacks and more!</p>
                </div>
                <a href="/products" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors whitespace-nowrap">
                    Browse Products →
                </a>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Recent Orders</h2>
                    <a href="/orders" class="text-sm text-amber-600 hover:text-amber-700">View all →</a>
                </div>
                <div v-if="loading" class="p-5 space-y-2">
                    <div v-for="i in 3" :key="i" class="h-12 bg-gray-100 rounded-lg animate-pulse"></div>
                </div>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Order</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Status</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Total</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3 hidden lg:table-cell">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in orders" :key="order.id" class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <a :href="`/orders/${order.id}`" class="font-mono text-xs text-amber-600 hover:text-amber-700">#{{ order.id }}</a>
                            </td>
                            <td class="px-5 py-3">
                                <span :class="statusColor(order.status)" class="px-2 py-0.5 rounded-full text-xs font-medium capitalize">{{ order.status }}</span>
                            </td>
                            <td class="px-5 py-3 text-gray-900 font-medium">{{ currency(order.total) }}</td>
                            <td class="px-5 py-3 text-gray-400 text-xs hidden lg:table-cell">{{ formatDate(order.created_at) }}</td>
                        </tr>
                        <tr v-if="!orders.length">
                            <td colspan="4" class="text-center text-gray-400 py-8">No orders yet. <a href="/products" class="text-amber-600">Start shopping!</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </UserLayout>
</template>
