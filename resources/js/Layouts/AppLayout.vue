<script setup>
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import MobileBottomNav from '@/Components/MobileBottomNav.vue';
import Toast from '@/Components/Toast.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { useCart } from '@/composables/useCart';
import { useWishlist } from '@/composables/useWishlist';

defineProps({
    title: {
        type: String,
        default: '',
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const { loadCart } = useCart();
const { loadWishlist } = useWishlist();

onMounted(() => {
    if (user.value) {
        loadCart();
        loadWishlist();
    }
});
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50">
        <Head :title="title" />
        <Navbar />
        <main class="flex-1 pt-24 pb-14 lg:pb-0">
            <slot />
        </main>
        <Footer class="hidden lg:block" />
        <MobileBottomNav />
        <Toast />
    </div>
</template>
