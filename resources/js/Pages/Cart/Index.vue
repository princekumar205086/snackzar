<script setup>
import { computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useCart } from '@/composables/useCart';
import { useToast } from '@/composables/useToast';

const { cartItems: items, cartLoaded: loaded, loadCart, updateQuantity, removeFromCart, cartTotal: subtotal } = useCart();
const { show: showToast } = useToast();

const deliveryFee = computed(() => subtotal.value >= 499 ? 0 : 49);
const total = computed(() => subtotal.value + deliveryFee.value);

function currency(v) {
    return '₹' + Number(v ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 0 });
}

async function handleQty(item, qty) {
    try {
        await updateQuantity(item.id, qty);
    } catch {
        showToast('Could not update quantity.', 'error');
    }
}

async function handleRemove(item) {
    try {
        await removeFromCart(item.id);
        showToast('Item removed from cart.', 'info');
    } catch {
        showToast('Could not remove item.', 'error');
    }
}

onMounted(loadCart);
</script>

<template>
    <Head title="My Cart" />
    <AppLayout>
        <div class="max-w-5xl mx-auto px-4 py-6 sm:py-8">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-5 sm:mb-6">Shopping Cart</h1>

            <!-- Loading skeleton -->
            <div v-if="!loaded" class="space-y-3">
                <div v-for="i in 3" :key="i" class="bg-white rounded-xl h-24 animate-pulse"></div>
            </div>

            <!-- Cart items -->
            <div v-else-if="items.length" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-3">
                    <div v-for="item in items" :key="item.id" class="bg-white rounded-2xl border border-gray-100 p-3 sm:p-4 hover:border-amber-100 transition-colors">
                        <div class="flex items-center gap-3">
                            <!-- Product image -->
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-gray-100 border border-gray-100 overflow-hidden shrink-0">
                                <img
                                    v-if="item.product?.primary_image?.url || item.product?.primary_image?.image_url"
                                    :src="item.product.primary_image?.url || item.product.primary_image?.image_url"
                                    :alt="item.product?.name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <a :href="item.product?.slug ? '/products/' + item.product.slug : '#'"
                                    class="font-semibold text-gray-900 text-sm leading-tight line-clamp-2 hover:text-amber-600 transition-colors">
                                    {{ item.product?.name }}
                                </a>
                                <p v-if="item.variant?.name" class="text-xs text-gray-500 mt-0.5 font-medium">
                                    <span class="bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded-md">{{ item.variant.name }}</span>
                                </p>
                                <p class="text-xs text-amber-600 font-semibold mt-0.5">{{ currency(item.unit_price) }}</p>
                            </div>

                            <!-- Remove button (top-right on mobile) -->
                            <button @click="handleRemove(item)" class="text-gray-300 hover:text-red-400 transition-colors shrink-0 self-start">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Bottom row: qty + subtotal -->
                        <div class="flex items-center justify-between mt-3 pl-0">
                            <!-- Qty controls -->
                            <div class="flex items-center gap-2">
                                <button @click="handleQty(item, item.quantity - 1)"
                                    class="w-8 h-8 rounded-full border-2 border-amber-500 text-amber-600 hover:bg-amber-50 text-base font-bold flex items-center justify-center transition-colors">−</button>
                                <span class="w-8 text-center text-sm font-bold text-gray-900">{{ item.quantity }}</span>
                                <button @click="handleQty(item, item.quantity + 1)"
                                    :disabled="item.quantity >= (item.variant?.stock ?? item.product?.stock ?? 99)"
                                    class="w-8 h-8 rounded-full bg-amber-600 hover:bg-amber-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-base font-bold flex items-center justify-center transition-colors">+</button>
                            </div>

                            <!-- Line total -->
                            <p class="font-bold text-gray-900 text-sm">{{ currency(item.quantity * parseFloat(item.unit_price ?? 0)) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Order Summary — desktop sidebar / mobile inline -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl border border-gray-100 p-4 sm:p-5 lg:sticky lg:top-24 space-y-4">
                        <h2 class="font-bold text-gray-900 text-base sm:text-lg">Order Summary</h2>
                        <div class="space-y-2.5 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal ({{ items.length }} item{{ items.length !== 1 ? 's' : '' }})</span>
                                <span class="font-semibold text-gray-900">{{ currency(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Delivery</span>
                                <span :class="deliveryFee === 0 ? 'text-green-600 font-semibold' : 'font-semibold text-gray-900'">
                                    {{ deliveryFee === 0 ? 'FREE' : currency(deliveryFee) }}
                                </span>
                            </div>
                            <div v-if="deliveryFee > 0" class="bg-amber-50 rounded-lg px-3 py-2 text-xs text-amber-700">
                                🚚 Add {{ currency(499 - subtotal) }} more for <strong>free delivery</strong>
                            </div>
                            <div class="border-t border-gray-100 pt-3 flex justify-between font-bold text-gray-900 text-base">
                                <span>Total</span>
                                <span class="text-amber-600">{{ currency(total) }}</span>
                            </div>
                        </div>
                        <a href="/checkout" class="block w-full bg-amber-600 hover:bg-amber-700 text-white text-center font-bold py-3 sm:py-3.5 rounded-xl transition-colors text-sm">
                            Proceed to Checkout →
                        </a>
                        <a href="/products" class="block text-center text-sm text-gray-400 hover:text-amber-600 transition-colors">
                            ← Continue Shopping
                        </a>
                    </div>
                </div>
            </div>

            <!-- Empty cart -->
            <div v-else class="bg-white rounded-2xl border border-gray-100 p-10 sm:p-16 text-center">
                <p class="text-5xl sm:text-6xl mb-4">🛒</p>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Your cart is empty</h2>
                <p class="text-gray-500 text-sm mt-2 mb-6">Looks like you haven't added anything yet.</p>
                <a href="/products" class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold px-7 py-3 rounded-full inline-block transition-colors">
                    Shop Now
                </a>
            </div>
        </div>
    </AppLayout>
</template>
