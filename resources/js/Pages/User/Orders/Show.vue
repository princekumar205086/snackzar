<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';

const props = defineProps({ id: [String, Number] });
const order = ref(null);
const loading = ref(true);
const cancelling = ref(false);

async function load() {
    try {
        const res = await window.axios.get(`/api/v1/user/orders/${props.id}`);
        order.value = res.data.data ?? res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function cancelOrder() {
    if (!confirm('Are you sure you want to cancel this order?')) return;
    cancelling.value = true;
    try {
        await window.axios.patch(`/api/v1/user/orders/${props.id}/cancel`);
        order.value.status = 'cancelled';
    } catch (e) {
        alert(e.response?.data?.message ?? 'Cannot cancel this order.');
    } finally {
        cancelling.value = false;
    }
}

function currency(v) {
    return '₹' + Number(v ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
}
function formatDate(d) {
    return new Date(d).toLocaleString('en-IN');
}
function statusColor(s) {
    const map = { pending: 'bg-yellow-100 text-yellow-700', confirmed: 'bg-blue-100 text-blue-700', processing: 'bg-indigo-100 text-indigo-700', shipped: 'bg-purple-100 text-purple-700', delivered: 'bg-green-100 text-green-700', cancelled: 'bg-red-100 text-red-700' };
    return map[s] ?? 'bg-gray-100 text-gray-600';
}

const cancellable = ['pending', 'confirmed'];

onMounted(load);
</script>

<template>
    <Head title="Order Detail" />
    <UserLayout>
        <div>
            <div class="flex items-center gap-3 mb-6">
                <a href="/orders" class="text-gray-500 hover:text-gray-700 text-sm">← My Orders</a>
                <h1 class="text-2xl font-bold text-gray-900">Order #{{ id }}</h1>
            </div>

            <div v-if="loading" class="space-y-4">
                <div v-for="i in 3" :key="i" class="bg-white rounded-xl h-24 animate-pulse"></div>
            </div>

            <div v-else-if="order" class="space-y-5">
                <!-- Header -->
                <div class="bg-white rounded-xl shadow-sm p-5 flex flex-wrap gap-4 items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-xs text-gray-500">Placed on {{ formatDate(order.created_at) }}</p>
                        <span :class="statusColor(order.status)" class="px-3 py-1 rounded-full text-sm font-semibold capitalize inline-block">
                            {{ order.status?.replace('_', ' ') }}
                        </span>
                    </div>
                    <button v-if="cancellable.includes(order.status)" @click="cancelOrder" :disabled="cancelling"
                        class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-sm font-medium rounded-lg disabled:opacity-50 transition-colors">
                        {{ cancelling ? 'Cancelling…' : 'Cancel Order' }}
                    </button>
                </div>

                <!-- Progress Tracker -->
                <div v-if="order.status !== 'cancelled'" class="bg-white rounded-xl shadow-sm p-5">
                    <div class="flex items-center gap-0">
                        <template v-for="(step, idx) in ['pending','confirmed','processing','shipped','delivered']" :key="step">
                            <div class="flex flex-col items-center flex-1 min-w-0">
                                <div :class="['pending','confirmed','processing','shipped','delivered'].indexOf(order.status) >= idx ? 'bg-amber-500' : 'bg-gray-200'"
                                    class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold transition-colors">
                                    {{ idx + 1 }}
                                </div>
                                <p class="text-xs text-gray-500 mt-1 text-center capitalize leading-tight">{{ step }}</p>
                            </div>
                            <div v-if="idx < 4" :class="['pending','confirmed','processing','shipped','delivered'].indexOf(order.status) > idx ? 'bg-amber-500' : 'bg-gray-200'"
                                class="h-1 flex-1 transition-colors rounded"></div>
                        </template>
                    </div>
                </div>

                <!-- Items -->
                <div class="bg-white rounded-xl shadow-sm divide-y divide-gray-50">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900">Items</h2>
                    </div>
                    <div v-for="item in (order.items ?? [])" :key="item.id" class="px-5 py-4 flex items-center gap-4">
                        <img v-if="item.product?.thumbnail" :src="item.product.thumbnail" class="w-12 h-12 rounded-lg object-cover" />
                        <div v-else class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-lg">📦</div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ item.product?.name }}</p>
                            <p class="text-xs text-gray-500">Qty: {{ item.quantity }} × {{ currency(item.price) }}</p>
                        </div>
                        <p class="text-sm font-semibold text-gray-900">{{ currency(item.total ?? item.price * item.quantity) }}</p>
                    </div>
                    <div class="px-5 py-4 flex justify-end">
                        <div class="text-right space-y-1">
                            <p class="text-sm text-gray-500">Subtotal: <span class="font-medium text-gray-900">{{ currency(order.subtotal) }}</span></p>
                            <p v-if="order.delivery_fee" class="text-sm text-gray-500">Delivery: <span class="font-medium text-gray-900">{{ currency(order.delivery_fee) }}</span></p>
                            <p class="text-base font-bold text-gray-900">Total: {{ currency(order.total) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Delivery Address -->
                <div v-if="order.address" class="bg-white rounded-xl shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-3">Delivery Address</h2>
                    <p class="text-sm font-medium text-gray-900">{{ order.address.full_name }}</p>
                    <p class="text-sm text-gray-600">{{ order.address.address_line1 }}</p>
                    <p v-if="order.address.address_line2" class="text-sm text-gray-600">{{ order.address.address_line2 }}</p>
                    <p class="text-sm text-gray-600">{{ order.address.city }}, {{ order.address.state }} - {{ order.address.pincode }}</p>
                    <p class="text-sm text-gray-500 mt-1">📱 {{ order.address.phone }}</p>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
