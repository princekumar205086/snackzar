<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import SellerLayout from '@/Layouts/SellerLayout.vue';

const profile = ref(null);
const form = ref(null);
const loading = ref(true);
const saving = ref(false);
const errors = ref({});
const success = ref('');

async function load() {
    try {
        const res = await window.axios.get('/api/v1/seller/profile');
        const d = res.data.data ?? res.data;
        profile.value = d;
        form.value = {
            business_name: d.business_name ?? '',
            description: d.description ?? '',
            address: d.address ?? '',
            city: d.city ?? '',
            state: d.state ?? '',
            pincode: d.pincode ?? '',
            phone: d.phone ?? '',
            bank_account: d.bank_account ?? '',
            upi_id: d.upi_id ?? '',
        };
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function submit() {
    saving.value = true;
    errors.value = {};
    success.value = '';
    try {
        await window.axios.put('/api/v1/seller/profile', form.value);
        success.value = 'Profile updated successfully!';
        setTimeout(() => success.value = '', 3000);
    } catch (e) {
        if (e.response?.status === 422) errors.value = e.response.data.errors ?? {};
        else alert('Failed to update profile.');
    } finally {
        saving.value = false;
    }
}

onMounted(load);
</script>

<template>
    <Head title="Seller Profile" />
    <SellerLayout>
        <div class="max-w-2xl space-y-5">
            <h1 class="text-2xl font-bold text-gray-900">Seller Profile</h1>

            <div v-if="success" class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
                {{ success }}
            </div>

            <div v-if="loading" class="space-y-3">
                <div v-for="i in 6" :key="i" class="bg-white rounded-xl h-14 animate-pulse"></div>
            </div>

            <form v-else-if="form" @submit.prevent="submit" class="space-y-5">
                <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Business Info</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business Name *</label>
                        <input v-model="form.business_name" type="text" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                        <p v-if="errors.business_name" class="text-red-500 text-xs mt-1">{{ errors.business_name[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea v-model="form.description" rows="3"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input v-model="form.phone" type="tel"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Address</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                        <input v-model="form.address" type="text"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input v-model="form.city" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                            <input v-model="form.state" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                            <input v-model="form.pincode" type="text" maxlength="6"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Payment Details</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account / IFSC</label>
                        <input v-model="form.bank_account" type="text" placeholder="AccountNumber/IFSC"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">UPI ID</label>
                        <input v-model="form.upi_id" type="text" placeholder="name@upi"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="saving"
                        class="px-6 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg disabled:opacity-50 transition-colors">
                        {{ saving ? 'Saving…' : 'Save Profile' }}
                    </button>
                </div>
            </form>
        </div>
    </SellerLayout>
</template>
