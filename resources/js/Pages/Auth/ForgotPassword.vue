<script setup>
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const statusMessage = computed(() => page.props.flash?.status || page.props.status || null);

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
};
</script>

<template>
    <Head title="Forgot Password" />

    <div class="min-h-screen bg-gradient-to-br from-amber-50 via-white to-orange-50 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <Link href="/" class="inline-flex items-center gap-2">
                    <span class="text-4xl">🥜</span>
                    <span class="text-2xl font-bold text-amber-900">Snackzar</span>
                </Link>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Reset Password</h1>
                    <p class="text-gray-500 mt-2 text-sm">Enter your email and we'll send you a reset link</p>
                </div>

                <div v-if="statusMessage" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
                    {{ statusMessage }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                                class="block w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-amber-500 text-gray-900 placeholder-gray-400"
                                placeholder="you@example.com"
                            />
                        </div>
                        <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-amber-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-amber-700 transition-all shadow-lg shadow-amber-600/25 disabled:opacity-50"
                    >
                        <span v-if="form.processing" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            Sending...
                        </span>
                        <span v-else>Send Reset Link</span>
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-500">
                    Remember your password?
                    <Link href="/login" class="text-amber-600 hover:text-amber-700 font-semibold"> Back to Sign In</Link>
                </p>
            </div>
        </div>
    </div>
</template>
