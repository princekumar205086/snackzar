<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

// ── State ──────────────────────────────────────────────────────────────────────
const coupons        = ref([]);
const stats          = ref(null);
const loading        = ref(true);
const saving         = ref(false);
const bulkGenerating = ref(false);
const pagination     = ref(null);
const users          = ref([]);
const usersLoading   = ref(false);

const filters = ref({ scope: '', search: '', is_active: '' });

// ── Modals ─────────────────────────────────────────────────────────────────────
const showCreateModal    = ref(false);
const showEditModal      = ref(false);
const showAssignModal    = ref(false);
const showBulkModal      = ref(false);
const showBulkGenModal   = ref(false);
const showAssignedUsers  = ref(false);

const editingCoupon      = ref(null);
const assigningCoupon    = ref(null);
const assignedUsersData  = ref([]);
const assignedLoading    = ref(false);
const toast              = ref({ show: false, msg: '', type: 'success' });

// ── Forms ──────────────────────────────────────────────────────────────────────
const form = ref({
    code: '', scope: 'public', type: 'percent', value: '', max_discount: '',
    min_order_amount: 0, max_uses: 0, max_uses_per_user: 1,
    expires_at: '', is_active: true, description: '', label: '', prefix: '',
});

const bulkGenForm = ref({
    count: 10, scope: 'bulk', type: 'percent', value: 10, max_discount: '',
    min_order_amount: 0, max_uses: 1, max_uses_per_user: 1,
    expires_at: '', description: '', label: '', prefix: 'BULK',
});

const assignForm = ref({ user_id: '' });
const bulkAssignForm = ref({ user_ids: '', filter: 'manual' });
const userSearch = ref('');
const formErrors = ref({});

// ── Computed ───────────────────────────────────────────────────────────────────
const filteredUsers = computed(() => {
    if (!userSearch.value) return users.value.slice(0, 20);
    const s = userSearch.value.toLowerCase();
    return users.value.filter(u =>
        u.name.toLowerCase().includes(s) || u.email.toLowerCase().includes(s)
    ).slice(0, 20);
});

const scopeColors = {
    public:     'bg-blue-100 text-blue-700',
    individual: 'bg-purple-100 text-purple-700',
    bulk:       'bg-amber-100 text-amber-700',
    enterprise: 'bg-green-100 text-green-700',
};

const scopeIcons = { public: '🌍', individual: '👤', bulk: '👥', enterprise: '🏢' };

// ── Data fetching ──────────────────────────────────────────────────────────────
async function load(page = 1) {
    loading.value = true;
    try {
        const params = { page, per_page: 15, ...filters.value };
        const [cRes, sRes] = await Promise.all([
            window.axios.get('/api/v1/admin/coupons', { params }),
            window.axios.get('/api/v1/admin/coupons/stats'),
        ]);
        coupons.value    = cRes.data.data?.data ?? cRes.data.data ?? [];
        pagination.value = cRes.data.data;
        stats.value      = sRes.data.data;
    } catch (e) {
        showToast('Failed to load coupons', 'error');
    } finally {
        loading.value = false;
    }
}

async function loadUsers() {
    if (users.value.length) return;
    usersLoading.value = true;
    try {
        const res = await window.axios.get('/api/v1/admin/users', { params: { per_page: 200 } });
        users.value = res.data.data?.data ?? res.data.data ?? [];
    } finally {
        usersLoading.value = false;
    }
}

async function loadAssignedUsers(couponId) {
    assignedLoading.value = true;
    try {
        const res = await window.axios.get(`/api/v1/admin/coupons/${couponId}/users`);
        assignedUsersData.value = res.data.data?.data ?? res.data.data ?? [];
    } finally {
        assignedLoading.value = false;
    }
}

// ── CRUD ───────────────────────────────────────────────────────────────────────
function openCreate() {
    form.value = {
        code: '', scope: 'public', type: 'percent', value: '', max_discount: '',
        min_order_amount: 0, max_uses: 0, max_uses_per_user: 1,
        expires_at: '', is_active: true, description: '', label: '', prefix: '',
    };
    formErrors.value  = {};
    showCreateModal.value = true;
}

function openEdit(coupon) {
    editingCoupon.value = coupon;
    form.value = {
        code:              coupon.code,
        scope:             coupon.scope,
        type:              coupon.type,
        value:             coupon.value,
        max_discount:      coupon.max_discount ?? '',
        min_order_amount:  coupon.min_order_amount ?? 0,
        max_uses:          coupon.max_uses ?? 0,
        max_uses_per_user: coupon.max_uses_per_user ?? 1,
        expires_at:        coupon.expires_at ? coupon.expires_at.slice(0, 16) : '',
        is_active:         coupon.is_active,
        description:       coupon.description ?? '',
        label:             coupon.label ?? '',
        prefix:            coupon.prefix ?? '',
    };
    formErrors.value  = {};
    showEditModal.value = true;
}

async function saveCoupon() {
    saving.value   = true;
    formErrors.value = {};
    try {
        if (showCreateModal.value) {
            await window.axios.post('/api/v1/admin/coupons', form.value);
            showToast('Coupon created!');
            showCreateModal.value = false;
        } else {
            await window.axios.put(`/api/v1/admin/coupons/${editingCoupon.value.id}`, form.value);
            showToast('Coupon updated!');
            showEditModal.value = false;
        }
        load();
    } catch (e) {
        formErrors.value = e.response?.data?.errors ?? {};
        showToast(e.response?.data?.message ?? 'Save failed', 'error');
    } finally {
        saving.value = false;
    }
}

async function deleteCoupon(coupon) {
    if (!confirm(`Delete coupon "${coupon.code}"? This cannot be undone.`)) return;
    try {
        await window.axios.delete(`/api/v1/admin/coupons/${coupon.id}`);
        showToast('Coupon deleted.');
        load();
    } catch (e) {
        showToast('Delete failed', 'error');
    }
}

async function toggleActive(coupon) {
    try {
        const res = await window.axios.patch(`/api/v1/admin/coupons/${coupon.id}/toggle`);
        coupon.is_active = res.data.data?.is_active ?? !coupon.is_active;
        showToast(coupon.is_active ? 'Coupon activated.' : 'Coupon deactivated.');
    } catch {
        showToast('Toggle failed', 'error');
    }
}

// ── Assign ─────────────────────────────────────────────────────────────────────
function openAssign(coupon) {
    assigningCoupon.value = coupon;
    assignForm.value      = { user_id: '' };
    userSearch.value      = '';
    loadUsers();
    showAssignModal.value = true;
}

async function assignToUser() {
    if (!assignForm.value.user_id) return;
    try {
        await window.axios.post(`/api/v1/admin/coupons/${assigningCoupon.value.id}/assign`, assignForm.value);
        showToast('Coupon assigned!');
        showAssignModal.value = false;
    } catch (e) {
        showToast(e.response?.data?.message ?? 'Assign failed', 'error');
    }
}

function openBulkAssign(coupon) {
    assigningCoupon.value    = coupon;
    bulkAssignForm.value     = { user_ids: '', filter: 'manual' };
    loadUsers();
    showBulkModal.value = true;
}

async function bulkAssign() {
    saving.value = true;
    try {
        if (bulkAssignForm.value.filter === 'manual') {
            const ids = bulkAssignForm.value.user_ids
                .split(',').map(id => parseInt(id.trim())).filter(Boolean);
            await window.axios.post(`/api/v1/admin/coupons/${assigningCoupon.value.id}/bulk-assign`, { user_ids: ids });
        } else {
            await window.axios.post(`/api/v1/admin/coupons/${assigningCoupon.value.id}/bulk-filter`, {
                filter: bulkAssignForm.value.filter,
            });
        }
        showToast('Bulk assignment done!');
        showBulkModal.value = false;
    } catch (e) {
        showToast(e.response?.data?.message ?? 'Bulk assign failed', 'error');
    } finally {
        saving.value = false;
    }
}

// ── Bulk generate ──────────────────────────────────────────────────────────────
async function bulkGenerate() {
    bulkGenerating.value = true;
    try {
        const res = await window.axios.post('/api/v1/admin/coupons/bulk-generate', bulkGenForm.value);
        showToast(`${res.data.data.count} coupons generated!`);
        showBulkGenModal.value = false;
        load();
    } catch (e) {
        showToast(e.response?.data?.message ?? 'Generation failed', 'error');
    } finally {
        bulkGenerating.value = false;
    }
}

// ── View assigned users ────────────────────────────────────────────────────────
async function viewAssignedUsers(coupon) {
    assigningCoupon.value = coupon;
    await loadAssignedUsers(coupon.id);
    showAssignedUsers.value = true;
}

// ── Utilities ──────────────────────────────────────────────────────────────────
function showToast(msg, type = 'success') {
    toast.value = { show: true, msg, type };
    setTimeout(() => { toast.value.show = false; }, 3500);
}

function currency(v) {
    return '₹' + Number(v ?? 0).toLocaleString('en-IN');
}

function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}

function isExpired(c) {
    return c.expires_at && new Date(c.expires_at) < new Date();
}

function copyCode(code) {
    navigator.clipboard?.writeText(code);
    showToast(`Copied: ${code}`);
}

onMounted(() => load());
</script>

<template>
    <Head title="Coupon Management" />
    <AdminLayout>

        <!-- Toast -->
        <Teleport to="body">
            <div v-if="toast.show"
                :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
                class="fixed top-5 right-5 z-[9999] text-white text-sm font-medium px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 min-w-[220px] animate-fade-in">
                <span>{{ toast.type === 'success' ? '✓' : '✕' }}</span>
                {{ toast.msg }}
            </div>
        </Teleport>

        <div class="space-y-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Coupon Management</h1>
                    <p class="text-sm text-gray-400 mt-1">Enterprise-level coupon system · public, individual, bulk & enterprise</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="showBulkGenModal = true"
                        class="flex items-center gap-1.5 px-3 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 text-sm rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Bulk Generate
                    </button>
                    <button @click="openCreate"
                        class="flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        New Coupon
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div v-if="stats" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-gray-800 rounded-xl p-4">
                    <p class="text-xs text-gray-400 mb-1">Total Coupons</p>
                    <p class="text-2xl font-bold text-white">{{ stats.total }}</p>
                </div>
                <div class="bg-gray-800 rounded-xl p-4">
                    <p class="text-xs text-gray-400 mb-1">Active</p>
                    <p class="text-2xl font-bold text-green-400">{{ stats.active }}</p>
                </div>
                <div class="bg-gray-800 rounded-xl p-4">
                    <p class="text-xs text-gray-400 mb-1">Total Redemptions</p>
                    <p class="text-2xl font-bold text-blue-400">{{ stats.total_redemptions }}</p>
                </div>
                <div class="bg-gray-800 rounded-xl p-4">
                    <p class="text-xs text-gray-400 mb-1">Expired</p>
                    <p class="text-2xl font-bold text-red-400">{{ stats.expired }}</p>
                </div>
            </div>

            <!-- Top used -->
            <div v-if="stats?.top_used?.length" class="bg-gray-800 rounded-xl p-4">
                <p class="text-xs text-gray-400 mb-3 uppercase tracking-wider font-medium">Top Used Coupons</p>
                <div class="flex flex-wrap gap-2">
                    <div v-for="cp in stats.top_used" :key="cp.id"
                        class="flex items-center gap-2 bg-gray-700 rounded-lg px-3 py-1.5 text-sm">
                        <span class="font-mono text-amber-300 font-semibold">{{ cp.code }}</span>
                        <span class="text-gray-400">{{ cp.used_count }} uses</span>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-gray-800 rounded-xl p-4 flex flex-wrap gap-3">
                <input v-model="filters.search" @input="load()" type="text" placeholder="Search code, description…"
                    class="flex-1 min-w-[160px] bg-gray-700 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                <select v-model="filters.scope" @change="load()"
                    class="bg-gray-700 border border-gray-600 text-gray-300 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Scopes</option>
                    <option value="public">🌍 Public</option>
                    <option value="individual">👤 Individual</option>
                    <option value="bulk">👥 Bulk</option>
                    <option value="enterprise">🏢 Enterprise</option>
                </select>
                <select v-model="filters.is_active" @change="load()"
                    class="bg-gray-700 border border-gray-600 text-gray-300 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <!-- Table -->
            <div class="bg-gray-800 rounded-xl overflow-hidden">
                <div v-if="loading" class="p-8 text-center text-gray-400">Loading…</div>
                <div v-else-if="!coupons.length" class="p-12 text-center text-gray-500">
                    <p class="text-3xl mb-3">🎟️</p>
                    <p class="font-medium">No coupons found</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-700">
                            <tr class="text-left text-xs text-gray-400 uppercase tracking-wider">
                                <th class="px-4 py-3 font-medium">Code</th>
                                <th class="px-4 py-3 font-medium">Scope</th>
                                <th class="px-4 py-3 font-medium">Discount</th>
                                <th class="px-4 py-3 font-medium hidden sm:table-cell">Usage</th>
                                <th class="px-4 py-3 font-medium hidden md:table-cell">Expires</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            <tr v-for="cp in coupons" :key="cp.id"
                                :class="isExpired(cp) ? 'opacity-60' : ''"
                                class="hover:bg-gray-700/30 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-amber-300 tracking-wider cursor-pointer hover:text-amber-200"
                                            @click="copyCode(cp.code)">{{ cp.code }}</span>
                                        <button @click="copyCode(cp.code)" class="text-gray-500 hover:text-gray-300 shrink-0" title="Copy">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                        </button>
                                    </div>
                                    <p v-if="cp.description" class="text-xs text-gray-500 mt-0.5 max-w-[180px] truncate">{{ cp.description }}</p>
                                    <p v-if="cp.label" class="text-xs text-blue-400 mt-0.5">{{ cp.label }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="scopeColors[cp.scope]" class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full font-medium">
                                        {{ scopeIcons[cp.scope] }} {{ cp.scope }}
                                    </span>
                                    <div v-if="cp.owner" class="text-xs text-gray-500 mt-0.5">{{ cp.owner.name }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-300">
                                    <div class="font-semibold">
                                        {{ cp.type === 'percent' ? cp.value + '%' : currency(cp.value) }} off
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <span v-if="cp.max_discount">Max {{ currency(cp.max_discount) }} · </span>
                                        <span v-if="cp.min_order_amount > 0">Min {{ currency(cp.min_order_amount) }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell text-gray-400 text-xs">
                                    {{ cp.used_count }} / {{ cp.max_uses || '∞' }}
                                    <div v-if="cp.assigned_users_count" class="text-gray-500">{{ cp.assigned_users_count }} users</div>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell text-gray-400 text-xs">
                                    <span :class="isExpired(cp) ? 'text-red-400' : ''">{{ formatDate(cp.expires_at) }}</span>
                                    <div v-if="isExpired(cp)" class="text-red-500">Expired</div>
                                </td>
                                <td class="px-4 py-3">
                                    <button @click="toggleActive(cp)"
                                        :class="cp.is_active ? 'bg-green-900/40 text-green-400 border-green-700' : 'bg-gray-700 text-gray-400 border-gray-600'"
                                        class="text-xs border px-2.5 py-1 rounded-lg font-medium transition-colors hover:opacity-80">
                                        {{ cp.is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1 flex-wrap">
                                        <button @click="openEdit(cp)" title="Edit"
                                            class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-600 rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button v-if="['bulk','enterprise','individual'].includes(cp.scope)"
                                            @click="openAssign(cp)" title="Assign to user"
                                            class="p-1.5 text-gray-400 hover:text-blue-400 hover:bg-gray-600 rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </button>
                                        <button v-if="['bulk','enterprise'].includes(cp.scope)"
                                            @click="openBulkAssign(cp)" title="Bulk assign"
                                            class="p-1.5 text-gray-400 hover:text-amber-400 hover:bg-gray-600 rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </button>
                                        <button v-if="cp.assigned_users_count > 0"
                                            @click="viewAssignedUsers(cp)" title="View assigned users"
                                            class="p-1.5 text-gray-400 hover:text-green-400 hover:bg-gray-600 rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        <button @click="deleteCoupon(cp)" title="Delete"
                                            class="p-1.5 text-gray-400 hover:text-red-400 hover:bg-gray-600 rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="pagination && pagination.last_page > 1" class="border-t border-gray-700 px-4 py-3 flex items-center justify-between">
                    <p class="text-xs text-gray-500">{{ pagination.from }}–{{ pagination.to }} of {{ pagination.total }} coupons</p>
                    <div class="flex gap-1">
                        <button v-for="p in pagination.last_page" :key="p" @click="load(p)"
                            :class="p === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-400 hover:bg-gray-600'"
                            class="w-7 h-7 rounded-lg text-xs transition-colors">{{ p }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Create/Edit Modal ─────────────────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="showCreateModal || showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/70" @click="showCreateModal = false; showEditModal = false"></div>
                <div class="relative w-full max-w-xl bg-gray-900 rounded-2xl p-6 shadow-2xl z-10 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-semibold text-white">{{ showCreateModal ? 'Create Coupon' : 'Edit Coupon' }}</h3>
                        <button @click="showCreateModal = false; showEditModal = false" class="text-gray-500 hover:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Scope + Code -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Scope *</label>
                                <select v-model="form.scope" class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="public">🌍 Public</option>
                                    <option value="individual">👤 Individual</option>
                                    <option value="bulk">👥 Bulk</option>
                                    <option value="enterprise">🏢 Enterprise</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Code <span class="text-gray-600">(auto if empty)</span></label>
                                <div class="flex gap-2 mt-1">
                                    <input v-model="form.code" type="text" placeholder="e.g. SNACK20"
                                        class="flex-1 bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 uppercase focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder:normal-case" />
                                    <input v-if="!form.code" v-model="form.prefix" type="text" placeholder="Prefix"
                                        class="w-20 bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-2 py-2 uppercase focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                </div>
                                <p v-if="formErrors.code" class="text-xs text-red-400 mt-0.5">{{ formErrors.code?.[0] }}</p>
                            </div>
                        </div>

                        <!-- Type + Value -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Type *</label>
                                <select v-model="form.type" class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="percent">% Percentage</option>
                                    <option value="flat">₹ Flat Amount</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Value *</label>
                                <input v-model="form.value" type="number" step="0.01" min="0.01"
                                    :placeholder="form.type === 'percent' ? 'e.g. 10 (10%)' : 'e.g. 50 (₹50)'"
                                    class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <p v-if="formErrors.value" class="text-xs text-red-400 mt-0.5">{{ formErrors.value?.[0] }}</p>
                            </div>
                        </div>

                        <!-- Max discount + Min order -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Max Discount (₹) <span class="text-gray-600">optional</span></label>
                                <input v-model="form.max_discount" type="number" step="0.01" min="0" placeholder="e.g. 100"
                                    class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Min Order Amount (₹)</label>
                                <input v-model="form.min_order_amount" type="number" step="0.01" min="0" placeholder="0"
                                    class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>

                        <!-- Usage limits -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Global Max Uses <span class="text-gray-600">(0 = unlimited)</span></label>
                                <input v-model="form.max_uses" type="number" min="0" placeholder="0"
                                    class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Per-User Limit <span class="text-gray-600">(0 = unlimited)</span></label>
                                <input v-model="form.max_uses_per_user" type="number" min="0" placeholder="1"
                                    class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>

                        <!-- Expiry -->
                        <div>
                            <label class="text-xs text-gray-400 font-medium">Expires At <span class="text-gray-600">optional</span></label>
                            <input v-model="form.expires_at" type="datetime-local"
                                class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <!-- Description + Label -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Description</label>
                                <input v-model="form.description" type="text" placeholder="Short description"
                                    class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Label / Tag</label>
                                <input v-model="form.label" type="text" placeholder="e.g. Diwali Sale"
                                    class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input id="is_active" type="checkbox" v-model="form.is_active" class="accent-blue-500 w-4 h-4" />
                            <label for="is_active" class="text-sm text-gray-300 cursor-pointer">Active immediately</label>
                        </div>
                    </div>

                    <div class="mt-5 flex gap-3">
                        <button @click="showCreateModal = false; showEditModal = false"
                            class="flex-1 border border-gray-600 text-gray-400 font-medium py-2.5 rounded-xl text-sm hover:bg-gray-800">Cancel</button>
                        <button @click="saveCoupon" :disabled="saving"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold py-2.5 rounded-xl text-sm">
                            {{ saving ? 'Saving…' : (showCreateModal ? 'Create Coupon' : 'Update Coupon') }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ── Bulk Generate Modal ──────────────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="showBulkGenModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/70" @click="showBulkGenModal = false"></div>
                <div class="relative w-full max-w-lg bg-gray-900 rounded-2xl p-6 shadow-2xl z-10 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-semibold text-white">🔢 Bulk Generate Coupons</h3>
                        <button @click="showBulkGenModal = false" class="text-gray-500 hover:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Count *</label>
                                <input v-model="bulkGenForm.count" type="number" min="1" max="500"
                                    class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Prefix</label>
                                <input v-model="bulkGenForm.prefix" type="text" placeholder="BULK"
                                    class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 uppercase focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Scope</label>
                                <select v-model="bulkGenForm.scope" class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2">
                                    <option value="public">Public</option>
                                    <option value="bulk">Bulk</option>
                                    <option value="enterprise">Enterprise</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Type</label>
                                <select v-model="bulkGenForm.type" class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2">
                                    <option value="percent">% Percent</option>
                                    <option value="flat">₹ Flat</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Value *</label>
                                <input v-model="bulkGenForm.value" type="number" min="0.01"
                                    class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2" />
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Max Discount</label>
                                <input v-model="bulkGenForm.max_discount" type="number" min="0" placeholder="—"
                                    class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Min Order (₹)</label>
                                <input v-model="bulkGenForm.min_order_amount" type="number" min="0"
                                    class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2" />
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Max Uses Each</label>
                                <input v-model="bulkGenForm.max_uses" type="number" min="0"
                                    class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2" />
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 font-medium">Expires At</label>
                            <input v-model="bulkGenForm.expires_at" type="datetime-local"
                                class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Description</label>
                                <input v-model="bulkGenForm.description" type="text" placeholder="Optional"
                                    class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2" />
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 font-medium">Label</label>
                                <input v-model="bulkGenForm.label" type="text" placeholder="Campaign name"
                                    class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2" />
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 flex gap-3">
                        <button @click="showBulkGenModal = false" class="flex-1 border border-gray-600 text-gray-400 py-2.5 rounded-xl text-sm hover:bg-gray-800">Cancel</button>
                        <button @click="bulkGenerate" :disabled="bulkGenerating"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold py-2.5 rounded-xl text-sm">
                            {{ bulkGenerating ? 'Generating…' : `Generate ${bulkGenForm.count} Coupons` }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ── Assign to Single User Modal ──────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="showAssignModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/70" @click="showAssignModal = false"></div>
                <div class="relative w-full max-w-md bg-gray-900 rounded-2xl p-6 shadow-2xl z-10">
                    <h3 class="text-lg font-semibold text-white mb-1">👤 Assign to User</h3>
                    <p class="text-sm text-gray-400 mb-5">
                        Coupon: <span class="text-amber-300 font-mono">{{ assigningCoupon?.code }}</span>
                    </p>
                    <div>
                        <label class="text-xs text-gray-400 font-medium">Search & Select User</label>
                        <input v-model="userSearch" type="text" placeholder="Search by name or email…"
                            class="mt-1 mb-2 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <div class="max-h-48 overflow-y-auto space-y-1">
                            <div v-if="usersLoading" class="text-center text-gray-500 py-4 text-sm">Loading users…</div>
                            <label v-for="u in filteredUsers" :key="u.id"
                                :class="assignForm.user_id === u.id ? 'bg-blue-600/20 border-blue-500' : 'bg-gray-800 border-gray-700'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg border cursor-pointer hover:border-gray-500">
                                <input type="radio" :value="u.id" v-model="assignForm.user_id" class="accent-blue-500 shrink-0" />
                                <div class="min-w-0">
                                    <p class="text-sm text-white font-medium truncate">{{ u.name }}</p>
                                    <p class="text-xs text-gray-400 truncate">{{ u.email }}</p>
                                </div>
                            </label>
                            <p v-if="!usersLoading && !filteredUsers.length" class="text-center text-gray-500 py-3 text-sm">No users found</p>
                        </div>
                    </div>
                    <div class="mt-5 flex gap-3">
                        <button @click="showAssignModal = false" class="flex-1 border border-gray-600 text-gray-400 py-2.5 rounded-xl text-sm hover:bg-gray-800">Cancel</button>
                        <button @click="assignToUser" :disabled="!assignForm.user_id"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold py-2.5 rounded-xl text-sm">
                            Assign Coupon
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ── Bulk Assign Modal ─────────────────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="showBulkModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/70" @click="showBulkModal = false"></div>
                <div class="relative w-full max-w-md bg-gray-900 rounded-2xl p-6 shadow-2xl z-10">
                    <h3 class="text-lg font-semibold text-white mb-1">👥 Bulk Assign Users</h3>
                    <p class="text-sm text-gray-400 mb-5">
                        Coupon: <span class="text-amber-300 font-mono">{{ assigningCoupon?.code }}</span>
                    </p>
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs text-gray-400 font-medium">Assignment Method</label>
                            <select v-model="bulkAssignForm.filter" class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2">
                                <option value="manual">Manual · Paste User IDs</option>
                                <option value="all">All Users</option>
                                <option value="no_orders">Users with No Orders</option>
                                <option value="role">By Role</option>
                            </select>
                        </div>
                        <div v-if="bulkAssignForm.filter === 'manual'">
                            <label class="text-xs text-gray-400 font-medium">User IDs <span class="text-gray-600">(comma-separated)</span></label>
                            <textarea v-model="bulkAssignForm.user_ids" rows="3" placeholder="e.g. 1, 5, 12, 34"
                                class="mt-1 w-full bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div v-if="bulkAssignForm.filter === 'all'" class="text-sm text-amber-400 bg-amber-900/20 border border-amber-700/40 rounded-lg px-3 py-2">
                            ⚠️ This will assign the coupon to ALL registered users.
                        </div>
                    </div>
                    <div class="mt-5 flex gap-3">
                        <button @click="showBulkModal = false" class="flex-1 border border-gray-600 text-gray-400 py-2.5 rounded-xl text-sm hover:bg-gray-800">Cancel</button>
                        <button @click="bulkAssign" :disabled="saving"
                            class="flex-1 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-semibold py-2.5 rounded-xl text-sm">
                            {{ saving ? 'Assigning…' : 'Assign to Users' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ── Assigned Users Modal ──────────────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="showAssignedUsers" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/70" @click="showAssignedUsers = false"></div>
                <div class="relative w-full max-w-md bg-gray-900 rounded-2xl p-6 shadow-2xl z-10 max-h-[80vh] flex flex-col">
                    <div class="flex items-center justify-between mb-4 shrink-0">
                        <div>
                            <h3 class="text-lg font-semibold text-white">Assigned Users</h3>
                            <p class="text-sm text-gray-400">{{ assigningCoupon?.code }}</p>
                        </div>
                        <button @click="showAssignedUsers = false" class="text-gray-500 hover:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="overflow-y-auto flex-1">
                        <div v-if="assignedLoading" class="text-center text-gray-400 py-8">Loading…</div>
                        <div v-else-if="!assignedUsersData.length" class="text-center text-gray-500 py-8">No users assigned yet.</div>
                        <div v-else class="space-y-2">
                            <div v-for="u in assignedUsersData" :key="u.id"
                                class="flex items-center justify-between bg-gray-800 rounded-xl px-3 py-2.5">
                                <div class="min-w-0">
                                    <p class="text-sm text-white font-medium truncate">{{ u.name }}</p>
                                    <p class="text-xs text-gray-400 truncate">{{ u.email }}</p>
                                </div>
                                <div class="text-right shrink-0 ml-3">
                                    <p class="text-xs text-gray-400">Used: {{ u.pivot?.used_count ?? 0 }}</p>
                                    <span :class="u.pivot?.is_active ? 'text-green-400' : 'text-red-400'" class="text-xs">
                                        {{ u.pivot?.is_active ? 'Active' : 'Revoked' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

    </AdminLayout>
</template>
