<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    redirectTo: { type: String, default: '' },
});

const step = ref('phone'); // 'phone' | 'otp'
const phone = ref('');
const otp = ref('');
const loading = ref(false);
const error = ref('');
const countdown = ref(0);
let timer = null;

function startCountdown() {
    countdown.value = 60;
    clearInterval(timer);
    timer = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) clearInterval(timer);
    }, 1000);
}

async function sendOtp() {
    if (!phone.value.match(/^[6-9]\d{9}$/)) {
        error.value = 'Enter a valid 10-digit mobile number.';
        return;
    }
    error.value = '';
    loading.value = true;
    try {
        await window.axios.post('/login/otp/send', {
            phone: phone.value,
            redirect: props.redirectTo || undefined,
        });
        step.value = 'otp';
        startCountdown();
    } catch (e) {
        error.value = e.response?.data?.errors?.phone?.[0] ?? e.response?.data?.message ?? 'Failed to send OTP.';
    } finally {
        loading.value = false;
    }
}

async function verifyOtp() {
    if (!otp.value || otp.value.length < 4) {
        error.value = 'Enter the OTP sent to your phone.';
        return;
    }
    error.value = '';
    loading.value = true;
    try {
        const res = await window.axios.post('/login/otp/verify', {
            phone: phone.value,
            otp: otp.value,
            redirect: props.redirectTo || undefined,
        });
        window.location.href = res.data?.redirect || '/dashboard';
    } catch (e) {
        error.value = e.response?.data?.errors?.otp?.[0]
            ?? e.response?.data?.errors?.phone?.[0]
            ?? e.response?.data?.message
            ?? 'Invalid OTP. Please try again.';
    } finally {
        loading.value = false;
    }
}

async function resend() {
    if (countdown.value > 0) return;
    await sendOtp();
}
</script>

<template>
    <Head title="Login with OTP" />
    <div class="min-h-screen bg-amber-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-lg max-w-sm w-full p-8">
            <!-- Logo -->
            <div class="text-center mb-6">
                <a href="/" class="inline-block">
                    <span class="text-3xl font-black text-amber-500 tracking-tight">SNACK<span class="text-gray-900">ZAR</span></span>
                </a>
                <h2 class="text-xl font-bold text-gray-900 mt-4">
                    {{ step === 'phone' ? 'Login with OTP' : 'Enter OTP' }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ step === 'phone'
                        ? 'We\'ll send a one-time password to your mobile'
                        : `OTP sent to +91 ${phone}` }}
                </p>
            </div>

            <!-- Error -->
            <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 mb-4">
                {{ error }}
            </div>

            <!-- Phone Step -->
            <form v-if="step === 'phone'" @submit.prevent="sendOtp">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Mobile Number</label>
                    <div class="flex rounded-xl border border-gray-200 overflow-hidden focus-within:border-amber-400 transition-colors">
                        <span class="px-3 py-2.5 bg-gray-50 text-gray-500 text-sm border-r border-gray-200 select-none">+91</span>
                        <input v-model="phone" type="tel" maxlength="10" placeholder="9876543210" required autofocus
                            class="flex-1 px-3 py-2.5 text-sm outline-none" />
                    </div>
                </div>
                <button type="submit" :disabled="loading"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl transition-colors disabled:opacity-50">
                    {{ loading ? 'Sending…' : 'Send OTP' }}
                </button>
            </form>

            <!-- OTP Step -->
            <form v-else @submit.prevent="verifyOtp">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">OTP</label>
                    <input v-model="otp" type="text" inputmode="numeric" maxlength="6" placeholder="••••••" required autofocus
                        class="w-full text-center text-2xl tracking-[0.5em] font-bold border border-gray-200 rounded-xl px-3 py-3 focus:outline-none focus:border-amber-400 transition-colors" />
                </div>
                <button type="submit" :disabled="loading"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl transition-colors disabled:opacity-50 mb-3">
                    {{ loading ? 'Verifying…' : 'Verify OTP' }}
                </button>
                <div class="text-center">
                    <button type="button" @click="resend"
                        :disabled="countdown > 0"
                        class="text-sm text-gray-500 disabled:opacity-50">
                        {{ countdown > 0 ? `Resend OTP in ${countdown}s` : 'Resend OTP' }}
                    </button>
                </div>
                <button type="button" @click="step = 'phone'; otp = ''; error = ''"
                    class="w-full mt-2 text-sm text-gray-400 hover:text-gray-600 text-center">
                    ← Change number
                </button>
            </form>

            <div class="mt-5 text-center text-sm text-gray-500">
                Or
                <a href="/login" class="text-amber-600 hover:text-amber-700 font-medium ml-1">Login with email</a>
            </div>
        </div>
    </div>
</template>
