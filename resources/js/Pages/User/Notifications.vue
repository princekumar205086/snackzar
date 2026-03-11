<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';

const notifications = ref([]);
const meta = ref({});
const loading = ref(true);
const page = ref(1);
const markingAll = ref(false);

async function load() {
    loading.value = true;
    try {
        const res = await window.axios.get('/api/v1/user/notifications', { params: { page: page.value } });
        const d = res.data.data;
        notifications.value = d?.data ?? d ?? [];
        meta.value = d?.meta ?? {};
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function markRead(n) {
    if (n.read_at) return;
    try {
        await window.axios.patch(`/api/v1/user/notifications/${n.id}/read`);
        n.read_at = new Date().toISOString();
    } catch (e) {
        console.error(e);
    }
}

async function markAllRead() {
    markingAll.value = true;
    try {
        await window.axios.post('/api/v1/user/notifications/read-all');
        notifications.value.forEach(n => n.read_at = n.read_at ?? new Date().toISOString());
    } catch (e) {
        alert('Failed to mark all as read.');
    } finally {
        markingAll.value = false;
    }
}

function formatDate(d) {
    const dt = new Date(d);
    const now = new Date();
    const diffMs = now - dt;
    const diffMins = Math.floor(diffMs / 60000);
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    const diffHrs = Math.floor(diffMins / 60);
    if (diffHrs < 24) return `${diffHrs}h ago`;
    return dt.toLocaleDateString('en-IN', { day: '2-digit', month: 'short' });
}

const unreadCount = () => notifications.value.filter(n => !n.read_at).length;

onMounted(load);
</script>

<template>
    <Head title="Notifications" />
    <UserLayout>
        <div class="space-y-5 max-w-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                    <p v-if="unreadCount()" class="text-sm text-gray-500 mt-0.5">{{ unreadCount() }} unread</p>
                </div>
                <button v-if="unreadCount()" @click="markAllRead" :disabled="markingAll"
                    class="text-sm text-amber-600 hover:text-amber-700 font-medium disabled:opacity-50">
                    {{ markingAll ? '…' : 'Mark all read' }}
                </button>
            </div>

            <div v-if="loading" class="space-y-2">
                <div v-for="i in 5" :key="i" class="bg-white rounded-xl h-16 animate-pulse"></div>
            </div>

            <div v-else class="space-y-2">
                <div v-for="n in notifications" :key="n.id"
                    @click="markRead(n)"
                    :class="n.read_at ? 'bg-white' : 'bg-amber-50 border-l-4 border-amber-400'"
                    class="rounded-xl shadow-sm p-4 flex items-start gap-3 cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="w-9 h-9 rounded-full bg-amber-100 flex items-center justify-center text-lg shrink-0">
                        {{ n.type === 'order' ? '🛒' : n.type === 'promo' ? '🎉' : '🔔' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 leading-snug">{{ n.title ?? n.data?.title ?? 'Notification' }}</p>
                        <p class="text-xs text-gray-500 mt-0.5 leading-snug">{{ n.message ?? n.data?.message }}</p>
                    </div>
                    <div class="shrink-0 flex flex-col items-end gap-1">
                        <span class="text-xs text-gray-400">{{ formatDate(n.created_at) }}</span>
                        <span v-if="!n.read_at" class="w-2 h-2 rounded-full bg-amber-500"></span>
                    </div>
                </div>
                <div v-if="!notifications.length" class="bg-white rounded-xl shadow-sm p-10 text-center">
                    <p class="text-3xl mb-2">🔔</p>
                    <p class="text-gray-400">No notifications yet.</p>
                </div>
            </div>

            <div v-if="meta.last_page > 1" class="flex justify-center gap-2">
                <button v-for="p in meta.last_page" :key="p" @click="page = p; load()"
                    :class="p === meta.current_page ? 'bg-amber-500 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                    class="w-9 h-9 rounded-lg text-sm font-medium transition-colors">{{ p }}</button>
            </div>
        </div>
    </UserLayout>
</template>
