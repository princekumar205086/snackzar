<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ orderId: Number });
const order = ref(null);
const loading = ref(true);
const updating = ref(false);
const newStatus = ref('');

const statusColors = {
    pending: 'bg-yellow-900/50 text-yellow-300',
    confirmed: 'bg-blue-900/50 text-blue-300',
    processing: 'bg-purple-900/50 text-purple-300',
    shipped: 'bg-indigo-900/50 text-indigo-300',
    delivered: 'bg-green-900/50 text-green-300',
    cancelled: 'bg-red-900/50 text-red-300',
};

async function load() {
    try {
        const res = await window.axios.get(`/api/v1/admin/orders/${props.orderId}`);
        order.value = res.data.data;
        newStatus.value = order.value.status;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function updateStatus() {
    updating.value = true;
    try {
        await window.axios.patch(`/api/v1/admin/orders/${props.orderId}/status`, { status: newStatus.value });
        order.value.status = newStatus.value;
    } catch (e) {
        alert('Failed to update status.');
    } finally {
        updating.value = false;
    }
}

function formatDate(d) {
    return new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

onMounted(load);
</script>

<template>
    <Head title="Order Details | Admin" />
    <AdminLayout>
        <div v-if="loading" class="animate-pulse space-y-4">
            <div class="bg-gray-800 h-8 rounded-xl w-48"></div>
            <div class="bg-gray-800 h-48 rounded-xl"></div>
        </div>

        <div v-else-if="order">
            <div class="flex items-center gap-4 mb-6">
                <a href="/admin/orders" class="text-gray-400 hover:text-white transition-colors">← Orders</a>
                <h1 class="text-2xl font-bold text-white">Order #{{ order.id }}</h1>
                <span :class="statusColors[order.status]" class="px-3 py-1 rounded-full text-sm font-medium capitalize">
                    {{ order.status }}
                </span>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Order items -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-gray-800 rounded-xl overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-700">
                            <h2 class="font-semibold text-white">Order Items</h2>
                        </div>
                        <div v-for="item in order.items" :key="item.id" class="flex items-center gap-4 px-5 py-4 border-b border-gray-700/50 last:border-0">
                            <div class="flex-1">
                                <p class="text-white font-medium">{{ item.product?.name }}</p>
                                <p class="text-gray-500 text-xs">Qty: {{ item.quantity }} × ₹{{ item.unit_price }}</p>
                            </div>
                            <p class="text-white font-semibold">₹{{ item.quantity * item.unit_price }}</p>
                        </div>
                        <div class="px-5 py-4 bg-gray-700/30 flex justify-between">
                            <span class="text-gray-400 font-medium">Total</span>
                            <span class="text-white font-bold text-lg">₹{{ order.total_amount }}</span>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-4">
                    <!-- Customer -->
                    <div class="bg-gray-800 rounded-xl p-5">
                        <h2 class="font-semibold text-white mb-3">Customer</h2>
                        <p class="text-gray-300 text-sm">{{ order.user?.name }}</p>
                        <p class="text-gray-500 text-xs mt-1">{{ order.user?.email }}</p>
                    </div>

                    <!-- Address -->
                    <div v-if="order.address" class="bg-gray-800 rounded-xl p-5">
                        <h2 class="font-semibold text-white mb-3">Delivery Address</h2>
                        <p class="text-gray-300 text-sm">{{ order.address.address_line1 }}</p>
                        <p v-if="order.address.address_line2" class="text-gray-400 text-sm">{{ order.address.address_line2 }}</p>
                        <p class="text-gray-400 text-sm">{{ order.address.city }}, {{ order.address.state }} {{ order.address.pincode }}</p>
                    </div>

                    <!-- Status Update -->
                    <div class="bg-gray-800 rounded-xl p-5">
                        <h2 class="font-semibold text-white mb-3">Update Status</h2>
                        <select v-model="newStatus" class="w-full bg-gray-700 border border-gray-600 text-gray-300 text-sm rounded-lg px-3 py-2 focus:border-blue-500 outline-none mb-3">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <button @click="updateStatus" :disabled="updating"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-lg transition-colors disabled:opacity-50">
                            {{ updating ? 'Updating...' : 'Update Status' }}
                        </button>
                    </div>

                    <div class="bg-gray-800 rounded-xl p-5">
                        <p class="text-xs text-gray-500 mb-1">Placed</p>
                        <p class="text-gray-300 text-sm">{{ formatDate(order.created_at) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
