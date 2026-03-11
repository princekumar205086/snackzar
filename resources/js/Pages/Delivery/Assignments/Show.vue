<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DeliveryLayout from '@/Layouts/DeliveryLayout.vue';

const props = defineProps({ id: [String, Number] });
const assignment = ref(null);
const loading = ref(true);
const actionLoading = ref(false);

async function load() {
    try {
        const res = await window.axios.get(`/api/v1/delivery/assignments/${props.id}`);
        assignment.value = res.data.data ?? res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function doAction(action) {
    actionLoading.value = true;
    try {
        const res = await window.axios.patch(`/api/v1/delivery/assignments/${props.id}/${action}`);
        assignment.value.status = res.data.data?.status ?? assignment.value.status;
    } catch (e) {
        alert(`Failed: ${e.response?.data?.message ?? 'Unknown error'}`);
    } finally {
        actionLoading.value = false;
    }
}

function formatDate(d) {
    return new Date(d).toLocaleString('en-IN');
}
function statusColor(s) {
    const map = { pending: 'bg-yellow-100 text-yellow-700', accepted: 'bg-blue-100 text-blue-700', picked_up: 'bg-indigo-100 text-indigo-700', delivered: 'bg-green-100 text-green-700' };
    return map[s] ?? 'bg-gray-100 text-gray-600';
}

onMounted(load);
</script>

<template>
    <Head title="Assignment Detail | Delivery" />
    <DeliveryLayout>
        <div>
            <div class="flex items-center gap-3 mb-6">
                <a href="/delivery/assignments" class="text-gray-500 hover:text-gray-700 text-sm">← Assignments</a>
                <h1 class="text-2xl font-bold text-gray-900">Assignment #{{ id }}</h1>
            </div>

            <div v-if="loading" class="space-y-4">
                <div v-for="i in 3" :key="i" class="bg-white rounded-xl h-28 animate-pulse"></div>
            </div>

            <div v-else-if="assignment" class="space-y-5">
                <!-- Status Card -->
                <div class="bg-white rounded-xl shadow-sm p-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Order</p>
                        <p class="text-xl font-bold text-green-600 mt-1 font-mono">#{{ assignment.order_id }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ formatDate(assignment.created_at) }}</p>
                    </div>
                    <span :class="statusColor(assignment.status)" class="px-4 py-1.5 rounded-full text-sm font-semibold capitalize">
                        {{ assignment.status?.replace('_', ' ') }}
                    </span>
                </div>

                <!-- Order Info -->
                <div class="bg-white rounded-xl shadow-sm p-5 space-y-3">
                    <h2 class="font-semibold text-gray-900">Delivery Details</h2>
                    <div class="grid sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Customer Name</p>
                            <p class="text-gray-900 font-medium">{{ assignment.order?.user?.name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Phone</p>
                            <p class="text-gray-900 font-medium">{{ assignment.order?.user?.phone ?? 'N/A' }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Delivery Address</p>
                            <p class="text-gray-900">{{ assignment.order?.delivery_address ?? assignment.delivery_address ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Order Total</p>
                            <p class="text-gray-900 font-semibold">₹{{ Number(assignment.order?.total ?? 0).toLocaleString('en-IN') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Payment Mode</p>
                            <p class="text-gray-900">{{ assignment.order?.payment_mode ?? 'Online' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-white rounded-xl shadow-sm p-5 flex flex-wrap gap-3">
                    <button v-if="assignment.status === 'pending'" @click="doAction('accept')"
                        :disabled="actionLoading"
                        class="flex-1 sm:flex-none px-5 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg disabled:opacity-50 transition-colors">
                        {{ actionLoading ? '…' : 'Accept Assignment' }}
                    </button>
                    <button v-if="assignment.status === 'accepted'" @click="doAction('pickup')"
                        :disabled="actionLoading"
                        class="flex-1 sm:flex-none px-5 py-2.5 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium rounded-lg disabled:opacity-50 transition-colors">
                        {{ actionLoading ? '…' : 'Mark Picked Up' }}
                    </button>
                    <button v-if="assignment.status === 'picked_up'" @click="doAction('deliver')"
                        :disabled="actionLoading"
                        class="flex-1 sm:flex-none px-5 py-2.5 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg disabled:opacity-50 transition-colors">
                        {{ actionLoading ? '…' : 'Mark Delivered' }}
                    </button>
                    <div v-if="assignment.status === 'delivered'" class="text-green-600 font-semibold text-sm flex items-center gap-2">
                        ✅ Delivered successfully
                    </div>
                </div>
            </div>
        </div>
    </DeliveryLayout>
</template>
