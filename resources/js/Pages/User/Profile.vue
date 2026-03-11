<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';

const profile = ref(null);
const form = ref(null);
const loading = ref(true);
const saving = ref(false);
const errors = ref({});
const success = ref('');

async function load() {
    try {
        const res = await window.axios.get('/api/v1/user/profile');
        const d = res.data.data ?? res.data;
        profile.value = d;
        form.value = {
            name: d.name ?? '',
            email: d.email ?? '',
            phone: d.phone ?? '',
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
        await window.axios.put('/api/v1/user/profile', form.value);
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
    <Head title="My Profile" />
    <UserLayout>
        <div class="max-w-xl space-y-5">
            <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>

            <div v-if="success" class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">{{ success }}</div>

            <div v-if="loading" class="space-y-3">
                <div v-for="i in 3" :key="i" class="bg-white rounded-xl h-14 animate-pulse"></div>
            </div>

            <form v-else-if="form" @submit.prevent="submit" class="bg-white rounded-xl shadow-sm p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input v-model="form.name" type="text" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                    <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name[0] }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input v-model="form.email" type="email" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                    <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email[0] }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input v-model="form.phone" type="tel"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                    <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone[0] }}</p>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" :disabled="saving"
                        class="px-6 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg disabled:opacity-50 transition-colors">
                        {{ saving ? 'Saving…' : 'Save Changes' }}
                    </button>
                </div>
            </form>

            <!-- Change Password (placeholder section) -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h2 class="font-semibold text-gray-900 mb-3">Password</h2>
                <p class="text-sm text-gray-500 mb-3">To change your password, use the forgot password flow.</p>
                <a href="/forgot-password" class="text-sm text-amber-600 hover:text-amber-700 font-medium">Change password →</a>
            </div>
        </div>
    </UserLayout>
</template>
