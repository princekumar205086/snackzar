<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const items = ref([]);
const loading = ref(true);
const updatingItem = ref(null);

async function load() {
    loading.value = true;
    try {
        const res = await window.axios.get('/api/v1/user/cart');
        items.value = res.data.data ?? res.data ?? [];
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function updateQty(item, qty) {
    if (qty < 1) return removeItem(item);
    updatingItem.value = item.id;
    try {
        await window.axios.put(`/api/v1/user/cart/${item.id}`, { quantity: qty });
        item.quantity = qty;
    } catch (e) {
        alert('Could not update quantity.');
    } finally {
        updatingItem.value = null;
    }
}

async function removeItem(item) {
    updatingItem.value = item.id;
    try {
        await window.axios.delete(`/api/v1/user/cart/${item.id}`);
        items.value = items.value.filter(i => i.id !== item.id);
    } catch (e) {
        alert('Could not remove item.');
    } finally {
        updatingItem.value = null;
    }
}

const subtotal = computed(() =>
    items.value.reduce((sum, i) => sum + (i.quantity * (i.product?.price ?? i.price ?? 0)), 0)
);
const deliveryFee = computed(() => subtotal.value >= 499 ? 0 : 49);
const total = computed(() => subtotal.value + deliveryFee.value);

function currency(v) {
    return '₹' + Number(v ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
}

onMounted(load);
</script>

<template>
    <Head title="My Cart" />
    <AppLayout>
        <div class="max-w-5xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Shopping Cart</h1>

            <div v-if="loading" class="space-y-3">
                <div v-for="i in 3" :key="i" class="bg-white rounded-xl h-24 animate-pulse"></div>
            </div>

            <div v-else-if="items.length" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Cart Items -->
                <div class="lg:col-span-2 space-y-3">
                    <div v-for="item in items" :key="item.id"
                        class="bg-white rounded-xl shadow-sm p-4 flex items-center gap-4">
                        <img v-if="item.product?.thumbnail" :src="item.product.thumbnail" :alt="item.product?.name"
                            class="w-16 h-16 rounded-lg object-cover shrink-0" />
                        <div v-else class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center text-2xl shrink-0">📦</div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 truncate">{{ item.product?.name ?? item.name }}</p>
                            <p class="text-sm text-amber-600 font-semibold mt-0.5">{{ currency(item.product?.price ?? item.price) }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button @click="updateQty(item, item.quantity - 1)"
                                :disabled="updatingItem === item.id"
                                class="w-7 h-7 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 text-sm font-bold flex items-center justify-center transition-colors">
                                −
                            </button>
                            <span class="w-8 text-center text-sm font-medium text-gray-900">
                                {{ updatingItem === item.id ? '…' : item.quantity }}
                            </span>
                            <button @click="updateQty(item, item.quantity + 1)"
                                :disabled="updatingItem === item.id"
                                class="w-7 h-7 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 text-sm font-bold flex items-center justify-center transition-colors">
                                +
                            </button>
                        </div>
                        <p class="w-20 text-right font-semibold text-gray-900 shrink-0">
                            {{ currency(item.quantity * (item.product?.price ?? item.price ?? 0)) }}
                        </p>
                        <button @click="removeItem(item)" :disabled="updatingItem === item.id"
                            class="text-gray-300 hover:text-red-400 disabled:opacity-50 transition-colors ml-1 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm p-5 sticky top-24 space-y-4">
                        <h2 class="font-semibold text-gray-900">Order Summary</h2>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal ({{ items.length }} item{{ items.length !== 1 ? 's' : '' }})</span>
                                <span>{{ currency(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Delivery</span>
                                <span :class="deliveryFee === 0 ? 'text-green-600 font-medium' : ''">
                                    {{ deliveryFee === 0 ? 'FREE' : currency(deliveryFee) }}
                                </span>
                            </div>
                            <div v-if="deliveryFee > 0" class="text-xs text-gray-400">
                                Add {{ currency(499 - subtotal) }} more for free delivery
                            </div>
                            <div class="border-t border-gray-100 pt-2 flex justify-between font-semibold text-gray-900">
                                <span>Total</span>
                                <span class="text-amber-600">{{ currency(total) }}</span>
                            </div>
                        </div>
                        <a href="/checkout"
                            class="block w-full bg-amber-500 hover:bg-amber-600 text-white text-center font-semibold py-3 rounded-xl transition-colors">
                            Proceed to Checkout
                        </a>
                        <a href="/products" class="block text-center text-sm text-gray-500 hover:text-amber-600 transition-colors">
                            ← Continue Shopping
                        </a>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white rounded-xl shadow-sm p-16 text-center">
                <p class="text-5xl mb-4">🛒</p>
                <h2 class="text-xl font-semibold text-gray-700">Your cart is empty</h2>
                <p class="text-gray-500 text-sm mt-2 mb-6">Looks like you haven't added anything yet.</p>
                <a href="/products" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-6 py-2.5 rounded-xl inline-block transition-colors">
                    Shop Now
                </a>
            </div>
        </div>
    </AppLayout>
</template>
