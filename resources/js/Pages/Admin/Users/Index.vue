<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const users = ref([]);
const meta = ref({});
const loading = ref(true);
const search = ref('');
const roleFilter = ref('');
const page = ref(1);
const statusUpdating = ref(null);

async function load() {
    loading.value = true;
    try {
        const res = await window.axios.get('/api/v1/admin/users', {
            params: { page: page.value, search: search.value, role: roleFilter.value },
        });
        users.value = res.data.data.data ?? res.data.data;
        meta.value = res.data.data.meta ?? {};
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function toggleStatus(user) {
    const newStatus = user.status === 'active' ? 'banned' : 'active';
    statusUpdating.value = user.id;
    try {
        await window.axios.patch(`/api/v1/admin/users/${user.id}/status`, { status: newStatus });
        user.status = newStatus;
    } catch (e) {
        alert('Failed to update status.');
    } finally {
        statusUpdating.value = null;
    }
}

let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { page.value = 1; load(); }, 400);
});
watch(roleFilter, () => { page.value = 1; load(); });

onMounted(load);
</script>

<template>
    <Head title="Users | Admin" />
    <AdminLayout>
        <div>
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <h1 class="text-2xl font-bold text-white">Users</h1>
                <div class="flex items-center gap-3">
                    <input v-model="search" type="text" placeholder="Search users..."
                        class="bg-gray-800 border border-gray-700 text-white placeholder-gray-500 text-sm rounded-lg px-3 py-2 focus:border-blue-500 outline-none w-48" />
                    <select v-model="roleFilter" class="bg-gray-800 border border-gray-700 text-gray-300 text-sm rounded-lg px-3 py-2 focus:border-blue-500 outline-none">
                        <option value="">All Roles</option>
                        <option value="user">User</option>
                        <option value="seller">Seller</option>
                        <option value="admin">Admin</option>
                        <option value="delivery_partner">Delivery</option>
                    </select>
                </div>
            </div>

            <!-- Loading state -->
            <div v-if="loading" class="space-y-2">
                <div v-for="i in 5" :key="i" class="bg-gray-800 rounded-xl h-14 animate-pulse"></div>
            </div>

            <!-- Table -->
            <div v-else class="bg-gray-800 rounded-xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Name</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3 hidden md:table-cell">Email</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3 hidden lg:table-cell">Role</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Status</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in users" :key="u.id" class="border-b border-gray-700/50 hover:bg-gray-700/30">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center text-xs font-bold text-gray-300">
                                        {{ u.name?.charAt(0)?.toUpperCase() }}
                                    </div>
                                    <span class="text-white font-medium">{{ u.name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-gray-400 hidden md:table-cell">{{ u.email }}</td>
                            <td class="px-5 py-3.5 hidden lg:table-cell">
                                <span v-for="role in (u.roles ?? [])" :key="role"
                                    class="inline-flex mr-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-900/50 text-blue-300">
                                    {{ role }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span :class="u.status === 'active' ? 'bg-green-900/50 text-green-400' : 'bg-red-900/50 text-red-400'"
                                    class="px-2 py-0.5 rounded-full text-xs font-medium">
                                    {{ u.status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <button @click="toggleStatus(u)" :disabled="statusUpdating === u.id"
                                    :class="u.status === 'active' ? 'text-red-400 hover:text-red-300' : 'text-green-400 hover:text-green-300'"
                                    class="text-xs font-medium transition-colors disabled:opacity-50">
                                    {{ statusUpdating === u.id ? '...' : (u.status === 'active' ? 'Ban' : 'Activate') }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!users.length">
                            <td colspan="5" class="text-center text-gray-500 py-8">No users found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="meta.last_page > 1" class="flex justify-center gap-2 mt-4">
                <button v-for="p in meta.last_page" :key="p" @click="page = p; load()"
                    :class="p === meta.current_page ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'"
                    class="w-9 h-9 rounded-lg text-sm font-medium transition-colors">
                    {{ p }}
                </button>
            </div>
        </div>
    </AdminLayout>
</template>
