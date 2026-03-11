<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const sellers = ref([]);
const loading = ref(true);
const updating = ref(null);

async function load() {
    try {
        const res = await window.axios.get('/api/v1/admin/sellers');
        sellers.value = res.data.data?.data ?? res.data.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function approve(profile) {
    updating.value = profile.id;
    try {
        await window.axios.patch(`/api/v1/admin/sellers/${profile.id}/approve`);
        profile.status = 'active';
    } catch (e) {
        alert('Failed to approve seller.');
    } finally {
        updating.value = null;
    }
}

async function suspend(profile) {
    if (!confirm('Suspend this seller?')) return;
    updating.value = profile.id;
    try {
        await window.axios.patch(`/api/v1/admin/sellers/${profile.id}/suspend`);
        profile.status = 'suspended';
    } catch (e) {
        alert('Failed to suspend seller.');
    } finally {
        updating.value = null;
    }
}

onMounted(load);
</script>

<template>
    <Head title="Sellers | Admin" />
    <AdminLayout>
        <div>
            <h1 class="text-2xl font-bold text-white mb-6">Sellers</h1>
            <div v-if="loading" class="space-y-2">
                <div v-for="i in 5" :key="i" class="bg-gray-800 rounded-xl h-14 animate-pulse"></div>
            </div>
            <div v-else class="bg-gray-800 rounded-xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Business Name</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3 hidden md:table-cell">Seller</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Status</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in sellers" :key="s.id" class="border-b border-gray-700/50 hover:bg-gray-700/30">
                            <td class="px-5 py-3.5 text-white font-medium">{{ s.business_name }}</td>
                            <td class="px-5 py-3.5 text-gray-400 hidden md:table-cell">{{ s.user?.name }}</td>
                            <td class="px-5 py-3.5">
                                <span :class="{
                                    'bg-yellow-900/50 text-yellow-300': s.status === 'pending',
                                    'bg-green-900/50 text-green-300': s.status === 'active',
                                    'bg-red-900/50 text-red-300': s.status === 'suspended',
                                }" class="px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                                    {{ s.status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 flex items-center gap-3">
                                <button v-if="s.status !== 'active'" @click="approve(s)" :disabled="updating === s.id"
                                    class="text-xs text-green-400 hover:text-green-300 font-medium disabled:opacity-50">
                                    Approve
                                </button>
                                <button v-if="s.status === 'active'" @click="suspend(s)" :disabled="updating === s.id"
                                    class="text-xs text-red-400 hover:text-red-300 font-medium disabled:opacity-50">
                                    Suspend
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!sellers.length">
                            <td colspan="4" class="text-center text-gray-500 py-8">No sellers registered.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
