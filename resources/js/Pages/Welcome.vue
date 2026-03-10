<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-amber-50 flex flex-col items-center justify-center">
        <div class="text-center max-w-2xl mx-auto px-4">
            <h1 class="text-6xl font-bold text-amber-900 mb-4">🥜 Snackzar</h1>
            <p class="text-xl text-amber-700 mb-8">
                Premium Bihari Snacks — Makhana &amp; More, Delivered Fresh
            </p>
            <div class="flex gap-4 justify-center">
                <a href="/login" class="bg-amber-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-amber-700 transition">
                    Get Started
                </a>
                <a href="/products" class="border-2 border-amber-600 text-amber-700 px-6 py-3 rounded-lg font-semibold hover:bg-amber-100 transition">
                    Browse Snacks
                </a>
            </div>
        </div>
    </div>
</template>
