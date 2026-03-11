<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';

const addresses = ref([]);
const loading = ref(true);
const showModal = ref(false);
const editing = ref(null);
const saving = ref(false);
const deleting = ref(null);
const errors = ref({});

const emptyForm = () => ({
    full_name: '',
    phone: '',
    address_line1: '',
    address_line2: '',
    city: '',
    state: '',
    pincode: '',
    is_default: false,
});
const form = ref(emptyForm());

async function load() {
    loading.value = true;
    try {
        const res = await window.axios.get('/api/v1/user/addresses');
        addresses.value = res.data.data ?? res.data ?? [];
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

function openAdd() {
    editing.value = null;
    form.value = emptyForm();
    errors.value = {};
    showModal.value = true;
}
function openEdit(addr) {
    editing.value = addr.id;
    form.value = { ...addr };
    errors.value = {};
    showModal.value = true;
}

async function submit() {
    saving.value = true;
    errors.value = {};
    try {
        if (editing.value) {
            await window.axios.put(`/api/v1/user/addresses/${editing.value}`, form.value);
        } else {
            await window.axios.post('/api/v1/user/addresses', form.value);
        }
        showModal.value = false;
        await load();
    } catch (e) {
        if (e.response?.status === 422) errors.value = e.response.data.errors ?? {};
        else alert('Failed to save address.');
    } finally {
        saving.value = false;
    }
}

async function deleteAddr(addr) {
    if (!confirm('Delete this address?')) return;
    deleting.value = addr.id;
    try {
        await window.axios.delete(`/api/v1/user/addresses/${addr.id}`);
        addresses.value = addresses.value.filter(a => a.id !== addr.id);
    } catch (e) {
        alert('Could not delete address.');
    } finally {
        deleting.value = null;
    }
}

async function setDefault(addr) {
    try {
        await window.axios.patch(`/api/v1/user/addresses/${addr.id}/default`);
        addresses.value.forEach(a => a.is_default = a.id === addr.id);
    } catch (e) {
        alert('Could not set default.');
    }
}

onMounted(load);
</script>

<template>
    <Head title="My Addresses" />
    <UserLayout>
        <div class="space-y-5">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">My Addresses</h1>
                <button @click="openAdd"
                    class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    + Add Address
                </button>
            </div>

            <div v-if="loading" class="space-y-3">
                <div v-for="i in 2" :key="i" class="bg-white rounded-xl h-28 animate-pulse"></div>
            </div>

            <div v-else class="space-y-3">
                <div v-for="addr in addresses" :key="addr.id"
                    class="bg-white rounded-xl shadow-sm p-5 relative">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="font-semibold text-gray-900">{{ addr.full_name }}</p>
                                <span v-if="addr.is_default" class="bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded-full font-medium">Default</span>
                            </div>
                            <p class="text-sm text-gray-600">{{ addr.address_line1 }}</p>
                            <p v-if="addr.address_line2" class="text-sm text-gray-600">{{ addr.address_line2 }}</p>
                            <p class="text-sm text-gray-600">{{ addr.city }}, {{ addr.state }} - {{ addr.pincode }}</p>
                            <p class="text-sm text-gray-500 mt-1">📱 {{ addr.phone }}</p>
                        </div>
                        <div class="flex flex-col gap-2">
                            <button @click="openEdit(addr)" class="text-xs text-amber-600 hover:text-amber-700 font-medium">Edit</button>
                            <button @click="deleteAddr(addr)" :disabled="deleting === addr.id"
                                class="text-xs text-red-500 hover:text-red-600 font-medium disabled:opacity-50">
                                {{ deleting === addr.id ? '…' : 'Delete' }}
                            </button>
                            <button v-if="!addr.is_default" @click="setDefault(addr)"
                                class="text-xs text-gray-500 hover:text-gray-700 font-medium">Set Default</button>
                        </div>
                    </div>
                </div>
                <div v-if="!addresses.length" class="bg-white rounded-xl shadow-sm p-10 text-center">
                    <p class="text-gray-400">No addresses saved yet.</p>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/40">
                <div class="bg-white rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-xl">
                    <div class="flex items-center justify-between p-5 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900">{{ editing ? 'Edit Address' : 'Add Address' }}</h2>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                    </div>
                    <form @submit.prevent="submit" class="p-5 space-y-4">
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                                <input v-model="form.full_name" type="text" required
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                                <p v-if="errors.full_name" class="text-red-500 text-xs mt-1">{{ errors.full_name[0] }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                                <input v-model="form.phone" type="tel" required
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                                <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone[0] }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 1 *</label>
                            <input v-model="form.address_line1" type="text" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                            <p v-if="errors.address_line1" class="text-red-500 text-xs mt-1">{{ errors.address_line1[0] }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 2</label>
                            <input v-model="form.address_line2" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                                <input v-model="form.city" type="text" required
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">State *</label>
                                <input v-model="form.state" type="text" required
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pincode *</label>
                                <input v-model="form.pincode" type="text" required maxlength="6" pattern="\d{6}"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.is_default" class="w-4 h-4 accent-amber-500" />
                            <span class="text-sm text-gray-700">Set as default address</span>
                        </label>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showModal = false"
                                class="px-5 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm hover:bg-gray-200">Cancel</button>
                            <button type="submit" :disabled="saving"
                                class="px-5 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium disabled:opacity-50">
                                {{ saving ? 'Saving…' : (editing ? 'Update' : 'Add Address') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </UserLayout>
</template>
