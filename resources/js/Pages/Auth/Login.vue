<script setup>
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    googleClientId: { type: String, default: '' },
    redirectTo: { type: String, default: '' },
});

const page = usePage();
const statusMessage = computed(() => page.props.flash?.status || page.props.status || null);

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
    redirect: props.redirectTo || '',
});

const googleAuthUrl = computed(() => {
    if (!form.redirect) {
        return '/auth/google';
    }

    return `/auth/google?redirect=${encodeURIComponent(form.redirect)}`;
});

onMounted(() => {
    if (!props.googleClientId) {
        return;
    }

    const setupGoogleOneTap = () => {
        if (!window.google?.accounts?.id) {
            return;
        }

        window.google.accounts.id.initialize({
            client_id: props.googleClientId,
            callback: async (response) => {
                try {
                    const res = await window.axios.post('/auth/google/one-tap', {
                        credential: response.credential,
                        redirect: form.redirect || undefined,
                    });
                    window.location.href = res.data?.redirect || '/dashboard';
                } catch (error) {
                    console.error('Google One Tap failed', error);
                }
            },
            auto_select: false,
            cancel_on_tap_outside: true,
            context: 'signin',
        });

        window.google.accounts.id.prompt();
    };

    if (window.google?.accounts?.id) {
        setupGoogleOneTap();
        return;
    }

    const existing = document.getElementById('google-identity-services');
    if (existing) {
        existing.addEventListener('load', setupGoogleOneTap, { once: true });
        return;
    }

    const script = document.createElement('script');
    script.id = 'google-identity-services';
    script.src = 'https://accounts.google.com/gsi/client';
    script.async = true;
    script.defer = true;
    script.onload = setupGoogleOneTap;
    document.head.appendChild(script);
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Sign In" />

    <div class="relative min-h-screen overflow-hidden bg-[#fff8ef] flex">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(251,146,60,0.16),_transparent_34%),radial-gradient(circle_at_bottom_right,_rgba(245,158,11,0.14),_transparent_30%),linear-gradient(135deg,_#fff8ef_0%,_#fffdf8_45%,_#fff4e7_100%)]"></div>
        <div class="absolute -left-24 top-20 h-72 w-72 rounded-full bg-amber-300/20 blur-3xl"></div>
        <div class="absolute -right-20 bottom-10 h-80 w-80 rounded-full bg-orange-300/20 blur-3xl"></div>

        <!-- Left Branding Panel (hidden on mobile) -->
        <div class="relative hidden lg:flex lg:w-[46%] items-center justify-center overflow-hidden bg-gradient-to-br from-[#8b3f0b] via-[#b45309] to-[#d97706] px-12 py-14">
            <div class="absolute inset-0 opacity-15" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.35&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
            <div class="absolute inset-0 bg-[linear-gradient(160deg,rgba(255,255,255,0.06),transparent_32%,rgba(255,255,255,0.08)_74%,transparent)]"></div>
            <div class="relative z-10 max-w-xl text-center text-white">
                <div class="inline-flex items-center justify-center rounded-[1.75rem] bg-white/12 px-7 py-6 ring-1 ring-white/15 shadow-2xl shadow-black/10 backdrop-blur-sm">
                    <img src="/images/logo/snackzar%20logo.png" alt="Snackzar" class="h-16 w-auto drop-shadow-lg" />
                </div>
                <h2 class="mt-8 text-4xl font-bold tracking-tight text-white">Welcome Back</h2>
                <p class="mx-auto mt-4 max-w-lg text-lg leading-8 text-amber-100/90">
                    Sign in to continue with Bihar's premium snacks, from crisp makhana to traditional namkeen and fresh artisanal picks.
                </p>
                <div class="mt-10 grid grid-cols-3 gap-4 text-left">
                    <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                        <p class="text-2xl font-semibold text-white">50+</p>
                        <p class="mt-1 text-xs uppercase tracking-[0.2em] text-amber-100/80">Products</p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                        <p class="text-2xl font-semibold text-white">500+</p>
                        <p class="mt-1 text-xs uppercase tracking-[0.2em] text-amber-100/80">Customers</p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                        <p class="text-2xl font-semibold text-white">4.8★</p>
                        <p class="mt-1 text-xs uppercase tracking-[0.2em] text-amber-100/80">Rating</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="relative z-10 flex-1 flex items-center justify-center px-4 sm:px-8 py-10 lg:py-12">
            <div class="w-full max-w-md rounded-[2rem] border border-white/70 bg-white/85 p-6 sm:p-8 shadow-[0_24px_90px_rgba(180,83,9,0.12)] backdrop-blur-xl">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-6">
                    <Link href="/" class="inline-flex items-center justify-center rounded-[1.5rem] bg-white px-5 py-3 shadow-sm ring-1 ring-amber-100">
                        <img src="/images/logo/snackzar%20logo.png" alt="Snackzar" class="h-12 w-auto sm:h-14" />
                    </Link>
                    <p class="mt-3 text-xs font-medium uppercase tracking-[0.24em] text-amber-700/70">Premium Bihari snacks</p>
                </div>

                <div class="mb-7">
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">Secure access</span>
                    <h1 class="mt-4 text-3xl font-bold text-gray-900">Sign In</h1>
                    <p class="mt-2 text-gray-500">Enter your credentials to access your account</p>
                </div>

                <!-- Status Message (e.g., password reset link sent) -->
                <div v-if="statusMessage" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
                    {{ statusMessage }}
                </div>

                <!-- Google Sign In -->
                <a
                    :href="googleAuthUrl"
                    class="mb-6 flex w-full items-center justify-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3 font-medium text-gray-700 transition-all hover:border-gray-300 hover:bg-gray-50"
                >
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Continue with Google
                </a>

                <div class="relative mb-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-gradient-to-br from-amber-50 via-white to-orange-50 text-gray-400">or sign in with email</span>
                    </div>
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
                                class="block w-full rounded-2xl border border-gray-200 bg-white pl-10 pr-4 py-3 text-gray-900 placeholder-gray-400 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                placeholder="you@example.com"
                            />
                            <input v-model="form.redirect" type="hidden" />
                        </div>
                        <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <Link href="/forgot-password" class="text-sm text-amber-600 hover:text-amber-700 font-medium">
                                Forgot password?
                            </Link>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                class="block w-full rounded-2xl border border-gray-200 bg-white pl-10 pr-12 py-3 text-gray-900 placeholder-gray-400 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                placeholder="••••••••"
                            />
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                        />
                        <label for="remember" class="ml-2 text-sm text-gray-600">Keep me logged in</label>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-2xl bg-amber-600 px-4 py-3 text-lg font-semibold text-white shadow-lg shadow-amber-600/25 transition-all hover:bg-amber-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span v-if="form.processing" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            Signing in...
                        </span>
                        <span v-else>Sign In</span>
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-gray-500">
                    Don't have an account?
                    <Link href="/register" class="text-amber-600 hover:text-amber-700 font-semibold"> Create one</Link>
                </p>
            </div>
        </div>
    </div>
</template>
