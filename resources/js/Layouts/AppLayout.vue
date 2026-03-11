<script setup>
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import MobileBottomNav from '@/Components/MobileBottomNav.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="min-h-screen flex flex-col bg-amber-50">
        <Head :title="title" />
        <Navbar />
        <main class="flex-1 pb-14 lg:pb-0">
            <slot />
        </main>
        <Footer class="hidden lg:block" />
        <MobileBottomNav />
    </div>
</template>
