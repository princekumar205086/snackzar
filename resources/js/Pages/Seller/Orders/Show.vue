<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import SellerLayout from '@/Layouts/SellerLayout.vue';

const props = defineProps({ id: [String, Number] });
const order = ref(null);
const loading = ref(true);
const updatingStatus = ref(false);
const newStatus = ref('');

const statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

async function load() {
    try {
        const res = await window.axios.get(`/api/v1/seller/orders/${props.id}`);
        order.value = res.data.data ?? res.data;
        newStatus.value = order.value.status;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function updateStatus() {
    updatingStatus.value = true;
    try {
        await window.axios.patch(`/api/v1/seller/orders/${props.id}/status`, { status: newStatus.value });
        order.value.status = newStatus.value;
    } catch (e) {
        alert('Could not update status.');
    } finally {
        updatingStatus.value = false;
    }
}

function currency(v) {
    return '₹' + Number(v ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
}
function formatDate(d) {
    return new Date(d).toLocaleString('en-IN');
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
    <Head title="Order Detail | Seller" />
    <SellerLayout>
        <div>
            <div class="flex items-center gap-3 mb-6">
                <a href="/seller/orders" class="text-gray-500 hover:text-gray-700 text-sm">← Orders</a>
                <h1 class="text-2xl font-bold text-gray-900">Order #{{ id }}</h1>
            </div>

            <div v-if="loading" class="space-y-4">
                <div v-for="i in 3" :key="i" class="bg-white rounded-xl h-24 animate-pulse"></div>
            </div>

            <div v-else-if="order" class="space-y-5">
                <!-- Header -->
                <div class="bg-white rounded-xl shadow-sm p-5 flex flex-wrap gap-4 items-start justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Order placed</p>
                        <p class="text-sm text-gray-700 mt-1">{{ formatDate(order.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Customer</p>
                        <p class="text-sm font-medium text-gray-900 mt-1">{{ order.user?.name }}</p>
                        <p class="text-xs text-gray-500">{{ order.user?.email }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span :class="statusColor(order.status)" class="px-3 py-1 rounded-full text-sm font-medium capitalize">{{ order.status }}</span>
                    </div>
                </div>

                <!-- Items -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900">Order Items</h2>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <div v-for="item in (order.items ?? [])" :key="item.id" class="px-5 py-4 flex items-center gap-4">
                            <img v-if="item.product?.thumbnail" :src="item.product.thumbnail" :alt="item.product?.name"
                                class="w-12 h-12 rounded-lg object-cover" />
                            <div v-else class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-xl">📦</div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ item.product?.name }}</p>
                                <p class="text-xs text-gray-500">Qty: {{ item.quantity }}</p>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">{{ currency(item.total ?? item.price * item.quantity) }}</p>
                        </div>
                    </div>
                    <div class="px-5 py-4 border-t border-gray-100 flex justify-end">
                        <div class="text-right space-y-1">
                            <p class="text-sm text-gray-500">Subtotal: <span class="text-gray-900 font-medium">{{ currency(order.subtotal) }}</span></p>
                            <p v-if="order.delivery_fee" class="text-sm text-gray-500">Delivery: <span class="text-gray-900 font-medium">{{ currency(order.delivery_fee) }}</span></p>
                            <p class="text-base font-bold text-gray-900">Total: {{ currency(order.total) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Update Status -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-3">Update Status</h2>
                    <div class="flex flex-wrap gap-3 items-center">
                        <select v-model="newStatus"
                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400">
                            <option v-for="s in statuses" :key="s" :value="s" class="capitalize">{{ s }}</option>
                        </select>
                        <button @click="updateStatus" :disabled="updatingStatus || newStatus === order.status"
                            class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg disabled:opacity-50 transition-colors">
                            {{ updatingStatus ? 'Saving…' : 'Update' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </SellerLayout>
</template>
