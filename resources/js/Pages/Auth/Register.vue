<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Create Account" />

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
                <h2 class="mt-8 text-4xl font-bold tracking-tight text-white">Join Snackzar</h2>
                <p class="mx-auto mt-4 max-w-lg text-lg leading-8 text-amber-100/90">
                    Create your account and start exploring Bihar's authentic Makhana, namkeen, and premium snacks.
                </p>
                <div class="mt-10 space-y-3 text-left">
                    <div class="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm text-amber-100/90">
                        <svg class="w-5 h-5 text-green-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm">Free delivery on orders above ₹499</span>
                    </div>
                    <div class="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm text-amber-100/90">
                        <svg class="w-5 h-5 text-green-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm">Track orders in real-time</span>
                    </div>
                    <div class="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm text-amber-100/90">
                        <svg class="w-5 h-5 text-green-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm">Exclusive member-only deals</span>
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
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">Create your account</span>
                    <h1 class="mt-4 text-3xl font-bold text-gray-900">Create Account</h1>
                    <p class="mt-2 text-gray-500">Fill in your details to get started</p>
                </div>

                <!-- Google Sign Up -->
                <a
                    href="/auth/google"
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
                        <span class="px-4 bg-gradient-to-br from-amber-50 via-white to-orange-50 text-gray-400">or register with email</span>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                autofocus
                                class="block w-full rounded-2xl border border-gray-200 bg-white pl-10 pr-4 py-3 text-gray-900 placeholder-gray-400 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                placeholder="Your name"
                            />
                        </div>
                        <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

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
                                class="block w-full rounded-2xl border border-gray-200 bg-white pl-10 pr-4 py-3 text-gray-900 placeholder-gray-400 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                placeholder="you@example.com"
                            />
                        </div>
                        <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-gray-400">(optional)</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                class="block w-full rounded-2xl border border-gray-200 bg-white pl-10 pr-4 py-3 text-gray-900 placeholder-gray-400 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                placeholder="9876543210"
                            />
                        </div>
                        <p v-if="form.errors.phone" class="mt-1.5 text-sm text-red-600">{{ form.errors.phone }}</p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
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
                                placeholder="Min 8 characters"
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

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                class="block w-full rounded-2xl border border-gray-200 bg-white pl-10 pr-4 py-3 text-gray-900 placeholder-gray-400 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                placeholder="••••••••"
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="mt-2 w-full rounded-2xl bg-amber-600 px-4 py-3 text-lg font-semibold text-white shadow-lg shadow-amber-600/25 transition-all hover:bg-amber-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span v-if="form.processing" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            Creating account...
                        </span>
                        <span v-else>Create Account</span>
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-gray-500">
                    Already have an account?
                    <Link href="/login" class="text-amber-600 hover:text-amber-700 font-semibold"> Sign In</Link>
                </p>

                <p class="mt-4 text-center text-xs text-gray-400">
                    By creating an account, you agree to our Terms of Service and Privacy Policy.
                </p>
            </div>
        </div>
    </div>
</template>
