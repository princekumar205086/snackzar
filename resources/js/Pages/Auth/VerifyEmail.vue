<script setup>
import { ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
const resending = ref(false);
const resent = ref(false);

async function resend() {
    resending.value = true;
    resent.value = false;
    try {
        await window.axios.post('/email/verification-notification');
        resent.value = true;
    } catch (e) {
        alert('Could not resend verification email.');
    } finally {
        resending.value = false;
    }
}
</script>

<template>
    <Head title="Verify Email" />
    <div class="min-h-screen bg-amber-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-lg max-w-md w-full p-8 text-center">
            <div class="w-16 h-16 bg-amber-100 rounded-full mx-auto flex items-center justify-center text-3xl mb-5">
                ✉️
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Verify your email</h1>
            <p class="text-gray-600 text-sm leading-relaxed mb-6">
                Thanks for signing up! Before getting started, please verify your email address by clicking the link we just emailed to you.
            </p>
            <div v-if="resent" class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3 mb-4">
                A new verification link has been sent to your email!
            </div>
            <button @click="resend" :disabled="resending"
                class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl transition-colors disabled:opacity-50 mb-4">
                {{ resending ? 'Sending…' : 'Resend Verification Email' }}
            </button>
            <form method="POST" action="/logout">
                <input type="hidden" name="_token" :value="page.props.csrf_token" />
                <button type="submit" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 rounded-xl transition-colors text-sm">
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</template>
