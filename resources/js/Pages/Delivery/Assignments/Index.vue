<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DeliveryLayout from '@/Layouts/DeliveryLayout.vue';

const assignments = ref([]);
const meta = ref({});
const loading = ref(true);
const page = ref(1);
const statusFilter = ref('');

const statuses = ['', 'pending', 'accepted', 'picked_up', 'delivered'];

async function load() {
    loading.value = true;
    try {
        const res = await window.axios.get('/api/v1/delivery/assignments', {
            params: { page: page.value, status: statusFilter.value || undefined }
        });
        const d = res.data.data;
        assignments.value = d?.data ?? d ?? [];
        meta.value = d?.meta ?? {};
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function accept(a) {
    a._loading = true;
    try {
        await window.axios.patch(`/api/v1/delivery/assignments/${a.id}/accept`);
        a.status = 'accepted';
    } catch (e) {
        alert('Could not accept assignment.');
    } finally {
        a._loading = false;
    }
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
    <Head title="Assignments | Delivery" />
    <DeliveryLayout>
        <div class="space-y-5">
            <h1 class="text-2xl font-bold text-gray-900">My Assignments</h1>

            <div class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap gap-2">
                <button v-for="s in statuses" :key="s"
                    @click="statusFilter = s; page = 1; load()"
                    :class="statusFilter === s ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium capitalize transition-colors">
                    {{ s || 'All' }}
                </button>
            </div>

            <div v-if="loading" class="space-y-2">
                <div v-for="i in 5" :key="i" class="bg-white rounded-xl h-14 animate-pulse"></div>
            </div>

            <div v-else class="space-y-3">
                <div v-for="a in assignments" :key="a.id"
                    class="bg-white rounded-xl shadow-sm p-4 flex flex-col sm:flex-row sm:items-center gap-4 justify-between">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center text-xl shrink-0">🚚</div>
                        <div>
                            <p class="font-semibold text-gray-900">Order <span class="font-mono text-green-600">#{{ a.order_id ?? a.id }}</span></p>
                            <p class="text-sm text-gray-500">{{ a.order?.delivery_address ?? a.delivery_address ?? 'Address not available' }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(a.created_at) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 ml-14 sm:ml-0">
                        <span :class="statusColor(a.status)" class="px-2.5 py-1 rounded-full text-xs font-medium capitalize">{{ a.status }}</span>
                        <a v-if="a.status !== 'delivered'" :href="`/delivery/assignments/${a.id}`"
                            class="text-xs text-green-600 hover:text-green-700 font-medium bg-green-50 px-3 py-1.5 rounded-lg">
                            Details
                        </a>
                        <button v-if="a.status === 'pending'" @click="accept(a)" :disabled="a._loading"
                            class="text-xs text-white font-medium bg-green-500 hover:bg-green-600 px-3 py-1.5 rounded-lg disabled:opacity-50 transition-colors">
                            {{ a._loading ? '…' : 'Accept' }}
                        </button>
                    </div>
                </div>
                <div v-if="!assignments.length" class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
                    No assignments found.
                </div>
            </div>

            <div v-if="meta.last_page > 1" class="flex justify-center gap-2">
                <button v-for="p in meta.last_page" :key="p" @click="page = p; load()"
                    :class="p === meta.current_page ? 'bg-green-500 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                    class="w-9 h-9 rounded-lg text-sm font-medium transition-colors">{{ p }}</button>
            </div>
        </div>
    </DeliveryLayout>
</template>
