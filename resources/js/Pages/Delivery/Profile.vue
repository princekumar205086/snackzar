<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DeliveryLayout from '@/Layouts/DeliveryLayout.vue';

const form = ref(null);
const loading = ref(true);
const saving = ref(false);
const errors = ref({});
const success = ref('');

async function load() {
    try {
        const res = await window.axios.get('/api/v1/delivery/profile');
        const d = res.data.data ?? res.data;
        form.value = {
            name: d.name ?? '',
            phone: d.phone ?? '',
            vehicle_type: d.vehicle_type ?? '',
            vehicle_number: d.vehicle_number ?? '',
            license_number: d.license_number ?? '',
            bank_account: d.bank_account ?? '',
            upi_id: d.upi_id ?? '',
            address: d.address ?? '',
            city: d.city ?? '',
            state: d.state ?? '',
            pincode: d.pincode ?? '',
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
        await window.axios.put('/api/v1/delivery/profile', form.value);
        success.value = 'Profile updated!';
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
    <Head title="Delivery Profile" />
    <DeliveryLayout>
        <div class="max-w-xl space-y-5">
            <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>

            <div v-if="success" class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
                {{ success }}
            </div>

            <div v-if="loading" class="space-y-3">
                <div v-for="i in 5" :key="i" class="bg-white rounded-xl h-14 animate-pulse"></div>
            </div>

            <form v-else-if="form" @submit.prevent="submit" class="space-y-5">
                <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Personal Info</h2>
                    <div class="grid gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input v-model="form.name" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-400" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input v-model="form.phone" type="tel"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-400" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input v-model="form.address" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-400" />
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                <input v-model="form.city" type="text"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                <input v-model="form.state" type="text"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                                <input v-model="form.pincode" type="text" maxlength="6"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-400" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Vehicle Info</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Type</label>
                            <select v-model="form.vehicle_type"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-400">
                                <option value="">— Select —</option>
                                <option value="bicycle">Bicycle</option>
                                <option value="motorcycle">Motorcycle</option>
                                <option value="scooter">Scooter</option>
                                <option value="car">Car</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Number</label>
                            <input v-model="form.vehicle_number" type="text" placeholder="MH12AB1234"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-400" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Driving License #</label>
                            <input v-model="form.license_number" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Payment Details</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account / IFSC</label>
                        <input v-model="form.bank_account" type="text"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-400" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">UPI ID</label>
                        <input v-model="form.upi_id" type="text"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-400" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="saving"
                        class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg disabled:opacity-50 transition-colors">
                        {{ saving ? 'Saving…' : 'Save Profile' }}
                    </button>
                </div>
            </form>
        </div>
    </DeliveryLayout>
</template>
