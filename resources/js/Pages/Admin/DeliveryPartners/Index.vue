<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const partners = ref([]);
const loading = ref(true);
const updating = ref(null);

async function load() {
    try {
        const res = await window.axios.get('/api/v1/admin/delivery-partners');
        partners.value = res.data.data?.data ?? res.data.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function approve(p) {
    updating.value = p.id;
    try {
        await window.axios.patch(`/api/v1/admin/delivery-partners/${p.id}/approve`);
        p.status = 'active';
    } catch (e) {
        alert('Failed.');
    } finally {
        updating.value = null;
    }
}

onMounted(load);
</script>

<template>
    <Head title="Delivery Partners | Admin" />
    <AdminLayout>
        <div>
            <h1 class="text-2xl font-bold text-white mb-6">Delivery Partners</h1>
            <div v-if="loading" class="space-y-2">
                <div v-for="i in 5" :key="i" class="bg-gray-800 rounded-xl h-14 animate-pulse"></div>
            </div>
            <div v-else class="bg-gray-800 rounded-xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Name</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3 hidden md:table-cell">Vehicle</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Status</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in partners" :key="p.id" class="border-b border-gray-700/50 hover:bg-gray-700/30">
                            <td class="px-5 py-3.5 text-white font-medium">{{ p.user?.name }}</td>
                            <td class="px-5 py-3.5 text-gray-400 hidden md:table-cell capitalize">{{ p.vehicle_type }}</td>
                            <td class="px-5 py-3.5">
                                <span :class="p.status === 'active' ? 'bg-green-900/50 text-green-300' : 'bg-yellow-900/50 text-yellow-300'"
                                    class="px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                                    {{ p.status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <button v-if="p.status !== 'active'" @click="approve(p)" :disabled="updating === p.id"
                                    class="text-xs text-green-400 hover:text-green-300 font-medium disabled:opacity-50">
                                    Approve
                                </button>
                                <span v-else class="text-xs text-gray-600">Active</span>
                            </td>
                        </tr>
                        <tr v-if="!partners.length">
                            <td colspan="4" class="text-center text-gray-500 py-8">No delivery partners yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
