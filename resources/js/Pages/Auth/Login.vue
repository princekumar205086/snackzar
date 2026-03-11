<script setup>
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const statusMessage = computed(() => page.props.flash?.status || page.props.status || null);

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Sign In" />

    <div class="min-h-screen bg-gradient-to-br from-amber-50 via-white to-orange-50 flex">
        <!-- Left Branding Panel (hidden on mobile) -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-amber-700 via-amber-800 to-amber-950 relative overflow-hidden items-center justify-center p-12">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
            </div>
            <div class="relative z-10 max-w-md text-center">
                <div class="text-7xl mb-6">🥜</div>
                <h2 class="text-4xl font-bold text-white mb-4">Welcome Back!</h2>
                <p class="text-amber-200 text-lg leading-relaxed mb-8">
                    Sign in to explore Bihar's finest Makhana, traditional namkeen, and artisanal snacks delivered fresh to your doorstep.
                </p>
                <div class="flex justify-center gap-6 text-amber-300">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-white">50+</p>
                        <p class="text-xs">Products</p>
                    </div>
                    <div class="w-px bg-amber-600"></div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-white">500+</p>
                        <p class="text-xs">Customers</p>
                    </div>
                    <div class="w-px bg-amber-600"></div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-white">4.8★</p>
                        <p class="text-xs">Rating</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="flex-1 flex items-center justify-center px-4 sm:px-8 py-12">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <Link href="/" class="inline-flex items-center gap-2">
                        <span class="text-3xl">🥜</span>
                        <span class="text-2xl font-bold text-amber-900">Snackzar</span>
                    </Link>
                </div>

                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Sign In</h1>
                    <p class="text-gray-500 mt-2">Enter your credentials to access your account</p>
                </div>

                <!-- Status Message (e.g., password reset link sent) -->
                <div v-if="statusMessage" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
                    {{ statusMessage }}
                </div>

                <!-- Google Sign In -->
                <a
                    href="/auth/google"
                    class="w-full flex items-center justify-center gap-3 bg-white border-2 border-gray-200 text-gray-700 py-3 px-4 rounded-xl font-medium hover:bg-gray-50 hover:border-gray-300 transition-all mb-6"
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
                                class="block w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-amber-500 text-gray-900 placeholder-gray-400"
                                placeholder="you@example.com"
                            />
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
                                class="block w-full pl-10 pr-12 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-amber-500 text-gray-900 placeholder-gray-400"
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
                        class="w-full bg-amber-600 text-white py-3 px-4 rounded-xl font-semibold text-lg hover:bg-amber-700 transition-all shadow-lg shadow-amber-600/25 disabled:opacity-50 disabled:cursor-not-allowed"
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
