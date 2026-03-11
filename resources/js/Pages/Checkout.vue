<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    razorpayKey: { type: String, default: '' },
});

const cartItems = ref([]);
const addresses = ref([]);
const selectedAddressId = ref(null);
const loading = ref(true);
const placingOrder = ref(false);
const paymentMethod = ref('razorpay'); // 'razorpay' | 'cod'
const errors = ref({});

async function load() {
    loading.value = true;
    try {
        const [cartRes, addrRes] = await Promise.all([
            window.axios.get('/api/v1/user/cart'),
            window.axios.get('/api/v1/user/addresses'),
        ]);
        cartItems.value = cartRes.data.data ?? cartRes.data ?? [];
        addresses.value = addrRes.data.data ?? addrRes.data ?? [];
        // Default to first default address
        const def = addresses.value.find(a => a.is_default) ?? addresses.value[0];
        if (def) selectedAddressId.value = def.id;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

const subtotal = computed(() =>
    cartItems.value.reduce((sum, i) => sum + (i.quantity * (i.product?.price ?? i.price ?? 0)), 0)
);
const deliveryFee = computed(() => subtotal.value >= 499 ? 0 : 49);
const total = computed(() => subtotal.value + deliveryFee.value);

function currency(v) {
    return '₹' + Number(v ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
}

async function placeOrder() {
    if (!selectedAddressId.value) {
        errors.value.address = 'Please select a delivery address.';
        return;
    }
    errors.value = {};
    placingOrder.value = true;

    try {
        if (paymentMethod.value === 'cod') {
            // COD: create order directly
            const res = await window.axios.post('/api/v1/user/orders', {
                address_id: selectedAddressId.value,
                payment_method: 'cod',
            });
            const orderId = res.data.data?.id ?? res.data.id;
            router.visit(`/orders/${orderId}?success=1`);
            return;
        }

        // Razorpay: first create server-side Razorpay order
        const rpRes = await window.axios.post('/payment/create-order', {
            address_id: selectedAddressId.value,
        });
        const { razorpay_order_id, amount, currency: rpCurrency, order_id } = rpRes.data;

        // Open Razorpay checkout modal
        const options = {
            key: props.razorpayKey,
            amount: amount,
            currency: rpCurrency ?? 'INR',
            order_id: razorpay_order_id,
            name: 'SNACKZAR',
            description: `Order #${order_id}`,
            handler: async function (response) {
                // Verify payment
                await window.axios.post('/payment/verify', {
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature,
                    order_id: order_id,
                });
                router.visit(`/orders/${order_id}?success=1`);
            },
            prefill: {},
            theme: { color: '#f59e0b' },
            modal: {
                ondismiss: function () {
                    placingOrder.value = false;
                }
            }
        };

        const rzp = new window.Razorpay(options);
        rzp.open();
        // Note: placingOrder reset happens in modal dismiss or after handler resolves
    } catch (e) {
        placingOrder.value = false;
        alert(e.response?.data?.message ?? 'Failed to place order. Please try again.');
    }
}

onMounted(() => {
    // Load Razorpay script if not already loaded
    if (!window.Razorpay) {
        const script = document.createElement('script');
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.async = true;
        document.head.appendChild(script);
    }
    load();
});
</script>

<template>
    <Head title="Checkout" />
    <AppLayout>
        <div class="max-w-5xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Checkout</h1>

            <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-4">
                    <div v-for="i in 3" :key="i" class="bg-white rounded-xl h-28 animate-pulse"></div>
                </div>
                <div class="bg-white rounded-xl h-64 animate-pulse"></div>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Address + Payment -->
                <div class="lg:col-span-2 space-y-5">
                    <!-- Delivery Address -->
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-gray-900">Delivery Address</h2>
                            <a href="/addresses" class="text-xs text-amber-600 hover:text-amber-700 font-medium">+ Add New</a>
                        </div>
                        <p v-if="errors.address" class="text-red-500 text-sm mb-3">{{ errors.address }}</p>
                        <div v-if="addresses.length" class="space-y-3">
                            <label v-for="addr in addresses" :key="addr.id"
                                :class="selectedAddressId === addr.id ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-gray-300'"
                                class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors">
                                <input type="radio" :value="addr.id" v-model="selectedAddressId" class="mt-1 accent-amber-500" />
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-900 text-sm">{{ addr.full_name }}</span>
                                        <span v-if="addr.is_default" class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Default</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-0.5">{{ addr.address_line1 }}, {{ addr.city }}, {{ addr.state }} - {{ addr.pincode }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">📱 {{ addr.phone }}</p>
                                </div>
                            </label>
                        </div>
                        <div v-else class="text-center py-6">
                            <p class="text-gray-500 text-sm">No saved addresses.</p>
                            <a href="/addresses" class="mt-2 inline-block text-amber-600 text-sm hover:text-amber-700 font-medium">Add an address →</a>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <h2 class="font-semibold text-gray-900 mb-4">Payment Method</h2>
                        <div class="space-y-3">
                            <label :class="paymentMethod === 'razorpay' ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-gray-300'"
                                class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors">
                                <input type="radio" value="razorpay" v-model="paymentMethod" class="accent-amber-500" />
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="w-10 h-7 bg-blue-600 rounded flex items-center justify-center">
                                        <span class="text-white text-xs font-bold">RP</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Pay Online</p>
                                        <p class="text-xs text-gray-500">UPI, Cards, Netbanking via Razorpay</p>
                                    </div>
                                </div>
            </label>
                            <label :class="paymentMethod === 'cod' ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-gray-300'"
                                class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors">
                                <input type="radio" value="cod" v-model="paymentMethod" class="accent-amber-500" />
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="w-10 h-7 bg-green-100 rounded flex items-center justify-center text-lg">💵</div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Cash on Delivery</p>
                                        <p class="text-xs text-gray-500">Pay when your order arrives</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Right: Order Summary -->
                <div>
                    <div class="bg-white rounded-xl shadow-sm p-5 sticky top-24 space-y-4">
                        <h2 class="font-semibold text-gray-900">Order Summary</h2>

                        <!-- Items list -->
                        <div class="space-y-3 max-h-52 overflow-y-auto">
                            <div v-for="item in cartItems" :key="item.id" class="flex items-center gap-2.5">
                                <img v-if="item.product?.thumbnail" :src="item.product.thumbnail"
                                    class="w-10 h-10 rounded-lg object-cover shrink-0" />
                                <div v-else class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-base shrink-0">📦</div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-900 truncate">{{ item.product?.name }}</p>
                                    <p class="text-xs text-gray-500">× {{ item.quantity }}</p>
                                </div>
                                <p class="text-xs font-semibold text-gray-900 shrink-0">
                                    {{ currency(item.quantity * (item.product?.price ?? item.price ?? 0)) }}
                                </p>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-3 space-y-2 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span>{{ currency(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Delivery</span>
                                <span :class="deliveryFee === 0 ? 'text-green-600 font-medium' : ''">
                                    {{ deliveryFee === 0 ? 'FREE' : currency(deliveryFee) }}
                                </span>
                            </div>
                            <div class="border-t border-gray-100 pt-2 flex justify-between font-bold text-gray-900">
                                <span>Total</span>
                                <span class="text-amber-600 text-base">{{ currency(total) }}</span>
                            </div>
                        </div>

                        <button @click="placeOrder" :disabled="placingOrder || !cartItems.length || !selectedAddressId"
                            class="w-full bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-semibold py-3 rounded-xl transition-colors">
                            {{ placingOrder ? 'Processing…' : (paymentMethod === 'cod' ? 'Place Order' : 'Pay ' + currency(total)) }}
                        </button>

                        <p class="text-xs text-center text-gray-400">
                            🔒 Secured by Razorpay · All transactions are encrypted
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
