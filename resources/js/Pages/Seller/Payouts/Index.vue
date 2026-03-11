<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import SellerLayout from '@/Layouts/SellerLayout.vue';

const payouts = ref([]);
const summary = ref(null);
const loading = ref(true);
const requesting = ref(false);

async function load() {
    try {
        const res = await window.axios.get('/api/v1/seller/payouts');
        const d = res.data.data ?? res.data;
        payouts.value = Array.isArray(d) ? d : (d.payouts ?? []);
        summary.value = d.summary ?? null;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function requestPayout() {
    if (!confirm('Request payout for available balance?')) return;
    requesting.value = true;
    try {
        await window.axios.post('/api/v1/seller/payouts');
        await load();
    } catch (e) {
        alert(e.response?.data?.message ?? 'Payout request failed.');
    } finally {
        requesting.value = false;
    }
}

function currency(v) {
    return '₹' + Number(v ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
}
function formatDate(d) {
    return new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}
function statusColor(s) {
    const map = { pending: 'bg-yellow-100 text-yellow-700', processed: 'bg-green-100 text-green-700', rejected: 'bg-red-100 text-red-700' };
    return map[s] ?? 'bg-gray-100 text-gray-600';
}

onMounted(load);
</script>

<template>
    <Head title="Payouts | Seller" />
    <SellerLayout>
        <div class="space-y-6">
            <h1 class="text-2xl font-bold text-gray-900">Payouts</h1>

            <!-- Summary -->
            <div v-if="summary" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow-sm p-5 text-center">
                    <p class="text-xs text-gray-500 uppercase">Total Earned</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">{{ currency(summary.total_earned) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 text-center">
                    <p class="text-xs text-gray-500 uppercase">Paid Out</p>
                    <p class="text-2xl font-bold text-green-600 mt-2">{{ currency(summary.total_paid) }}</p>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 text-center">
                    <p class="text-xs text-amber-700 uppercase font-medium">Available</p>
                    <p class="text-2xl font-bold text-amber-700 mt-2">{{ currency(summary.available) }}</p>
                    <button @click="requestPayout" :disabled="requesting || (summary.available ?? 0) <= 0"
                        class="mt-3 w-full bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium py-1.5 rounded-lg disabled:opacity-50 transition-colors">
                        {{ requesting ? 'Requesting…' : 'Request Payout' }}
                    </button>
                </div>
            </div>

            <!-- Payout History -->
            <div v-if="loading" class="space-y-2">
                <div v-for="i in 4" :key="i" class="bg-white rounded-xl h-14 animate-pulse"></div>
            </div>
            <div v-else class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Payout History</h2>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Amount</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Status</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3 hidden md:table-cell">Bank / UPI</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payout in payouts" :key="payout.id" class="border-b border-gray-50">
                            <td class="px-5 py-3.5 font-semibold text-gray-900">{{ currency(payout.amount) }}</td>
                            <td class="px-5 py-3.5">
                                <span :class="statusColor(payout.status)" class="px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">{{ payout.status }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-500 text-xs hidden md:table-cell">{{ payout.bank_account ?? payout.upi_id ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-gray-500 text-xs">{{ formatDate(payout.created_at) }}</td>
                        </tr>
                        <tr v-if="!payouts.length">
                            <td colspan="4" class="text-center text-gray-400 py-8">No payouts yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </SellerLayout>
</template>
