<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useCart } from '@/composables/useCart';

const props = defineProps({
    razorpayKey: { type: String, default: '' },
});

const { cartItems, cartTotal: subtotal, loadCart } = useCart();
const addresses        = ref([]);
const selectedAddressId = ref(null);
const loading          = ref(true);
const placingOrder     = ref(false);
const paymentMethod    = ref('razorpay');
const errors           = ref({});

// ── Coupon state ──────────────────────────────────────────────────────────────
const couponCode       = ref('');
const couponApplied    = ref(null);   // { code, discount, description }
const couponLoading    = ref(false);
const couponError      = ref('');
const myCoupons        = ref([]);
const showMyCoupons    = ref(false);
const showAddressModal = ref(false);
const pincodeLookupLoading = ref(false);
const pincodeLookupError = ref('');
const pincodePostOffices = ref([]);

// ── New address form ──────────────────────────────────────────────────────────
const emptyAddressForm = () => ({
    name: '',
    phone: '',
    address_line_1: '',
    address_line_2: '',
    pincode: '',
    country: 'India',
    state: '',
    district: '',
    city: '',
    is_default: false,
});
const newAddr = ref(emptyAddressForm());
const savingAddr = ref(false);
const addrErrors = ref({});

async function load() {
    loading.value = true;
    try {
        const [, addrRes, couponsRes] = await Promise.all([
            loadCart(),
            window.axios.get('/api/v1/user/addresses'),
            window.axios.get('/api/v1/user/coupon/my-coupons').catch(() => ({ data: { data: [] } })),
        ]);
        addresses.value  = Array.isArray(addrRes.data.data) ? addrRes.data.data : [];
        myCoupons.value  = Array.isArray(couponsRes.data.data) ? couponsRes.data.data : [];
        const def = addresses.value.find(a => a.is_default) ?? addresses.value[0];
        if (def) selectedAddressId.value = def.id;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

// ── Computed ──────────────────────────────────────────────────────────────────
const deliveryFee = computed(() => subtotal.value >= 499 ? 0 : 49);
const discount    = computed(() => couponApplied.value?.discount ?? 0);
const total       = computed(() => Math.max(0, subtotal.value + deliveryFee.value - discount.value));

function currency(v) {
    return '₹' + Number(v ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
}

const addressName = (addr) => addr?.full_name ?? addr?.name ?? '';
const addressLine1 = (addr) => addr?.address_line1 ?? addr?.address_line_1 ?? '';

// ── Coupon ────────────────────────────────────────────────────────────────────
async function applyCoupon(code = null) {
    const c = (code ?? couponCode.value).trim().toUpperCase();
    if (!c) return;

    couponLoading.value = true;
    couponError.value   = '';
    couponApplied.value = null;

    try {
        const res = await window.axios.post('/api/v1/user/coupon/validate', {
            code:         c,
            order_amount: subtotal.value,
        });
        couponApplied.value = res.data.data;
        couponCode.value    = c;
        showMyCoupons.value = false;
    } catch (e) {
        couponError.value = e.response?.data?.message ?? 'Invalid coupon code.';
        couponApplied.value = null;
    } finally {
        couponLoading.value = false;
    }
}

function removeCoupon() {
    couponApplied.value = null;
    couponCode.value    = '';
    couponError.value   = '';
}

async function saveAddress() {
    addrErrors.value = {};
    pincodeLookupError.value = '';
    savingAddr.value = true;
    try {
        const payload = {
            name: newAddr.value.name,
            phone: newAddr.value.phone,
            address_line_1: newAddr.value.address_line_1,
            address_line_2: newAddr.value.address_line_2 || null,
            city: newAddr.value.city || newAddr.value.district,
            state: newAddr.value.state,
            pincode: newAddr.value.pincode,
            is_default: !!newAddr.value.is_default,
        };

        const res = await window.axios.post('/api/v1/user/addresses', payload);
        const addr = res.data.data;
        addresses.value.push(addr);
        selectedAddressId.value = addr.id;
        showAddressModal.value  = false;
        newAddr.value = emptyAddressForm();
        pincodePostOffices.value = [];
    } catch (e) {
        addrErrors.value = e.response?.data?.errors ?? {};
    } finally {
        savingAddr.value = false;
    }
}

async function lookupIndianPincode() {
    const pin = (newAddr.value.pincode || '').replace(/\D/g, '');
    newAddr.value.pincode = pin;

    if (pin.length !== 6) {
        pincodeLookupError.value = pin.length ? 'Enter a valid 6-digit Indian pincode.' : '';
        pincodePostOffices.value = [];
        return;
    }

    pincodeLookupLoading.value = true;
    pincodeLookupError.value = '';
    pincodePostOffices.value = [];

    try {
        const res = await window.axios.get(`/api/v1/user/pincode/${pin}`);
        const data = res.data?.data ?? {};

        newAddr.value.country = data.country || 'India';
        newAddr.value.state = data.state || '';
        newAddr.value.district = data.district || '';
        newAddr.value.city = data.city || data.district || '';
        pincodePostOffices.value = Array.isArray(data.post_offices) ? data.post_offices : [];
    } catch (e) {
        pincodeLookupError.value = e.response?.data?.message || 'Could not detect location for this pincode.';
        pincodePostOffices.value = [];
    } finally {
        pincodeLookupLoading.value = false;
    }
}

// ── Place order ───────────────────────────────────────────────────────────────
async function placeOrder() {
    if (!selectedAddressId.value) {
        errors.value.address = 'Please select a delivery address.';
        return;
    }
    errors.value    = {};
    placingOrder.value = true;

    const payload = {
        address_id:     selectedAddressId.value,
        payment_method: paymentMethod.value,
        coupon_code:    couponApplied.value?.code ?? undefined,
    };

    try {
        if (paymentMethod.value === 'cod') {
            const res = await window.axios.post('/api/v1/user/orders', payload);
            const orderId = res.data.data?.id ?? res.data.id;
            router.visit(`/orders/${orderId}?success=1`);
            return;
        }

        const orderRes = await window.axios.post('/api/v1/user/orders', payload);
        const orderId  = orderRes.data.data?.id;
        const rpRes = await window.axios.post('/payment/create-order', {
            order_id: orderId,
        });
        const { razorpay_order_id, amount, currency: rpCurrency } = rpRes.data.data ?? rpRes.data;

        const options = {
            key:         props.razorpayKey,
            amount,
            currency:    rpCurrency ?? 'INR',
            order_id:    razorpay_order_id,
            name:        'SNACKZAR',
            description: `Order #${orderId}`,
            handler: async function (response) {
                await window.axios.post('/payment/verify', {
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id:   response.razorpay_order_id,
                    razorpay_signature:  response.razorpay_signature,
                    order_id:            orderId,
                });
                router.visit(`/orders/${orderId}?success=1`);
            },
            prefill: {},
            theme:  { color: '#f59e0b' },
            modal:  { ondismiss: () => { placingOrder.value = false; } },
        };

        const rzp = new window.Razorpay(options);
        rzp.open();
    } catch (e) {
        placingOrder.value = false;
        errors.value.checkout = e.response?.data?.message ?? 'Failed to place order. Please try again.';
    }
}

onMounted(() => {
    if (!window.Razorpay) {
        const script = document.createElement('script');
        script.src   = 'https://checkout.razorpay.com/v1/checkout.js';
        script.async = true;
        document.head.appendChild(script);
    }
    load();
});
</script>

<template>
    <Head title="Checkout" />
    <AppLayout>
        <div class="max-w-5xl mx-auto px-4 py-6 sm:py-8">
            <div class="flex items-center gap-3 mb-6">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Checkout</h1>
                <span v-if="cartItems.length" class="text-sm text-gray-500">({{ cartItems.length }} item{{ cartItems.length > 1 ? 's' : '' }})</span>
            </div>

            <!-- Global error -->
            <div v-if="errors.checkout" class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm flex items-start gap-2">
                <span>⚠️</span>
                <span>{{ errors.checkout }}</span>
                <button @click="errors.checkout = null" class="ml-auto text-red-400 hover:text-red-600">✕</button>
            </div>

            <!-- Loading skeleton -->
            <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-4">
                    <div v-for="i in 3" :key="i" class="bg-white rounded-xl h-28 animate-pulse"></div>
                </div>
                <div class="bg-white rounded-xl h-80 animate-pulse"></div>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- ── Left panel ─────────────────────────────────────────── -->
                <div class="lg:col-span-2 space-y-5">

                    <!-- Delivery Address -->
                    <div class="bg-white rounded-xl shadow-sm p-4 sm:p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                                <span class="w-6 h-6 bg-amber-500 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                Delivery Address
                            </h2>
                            <button @click="showAddressModal = true" class="text-xs text-amber-600 hover:text-amber-700 font-semibold border border-amber-300 px-3 py-1 rounded-lg transition-colors">
                                + Add New
                            </button>
                        </div>
                        <p v-if="errors.address" class="text-red-500 text-sm mb-3">{{ errors.address }}</p>
                        <div v-if="addresses.length" class="space-y-2.5">
                            <label v-for="addr in addresses" :key="addr.id"
                                :class="selectedAddressId === addr.id ? 'border-amber-400 bg-amber-50 shadow-sm' : 'border-gray-200 hover:border-gray-300'"
                                class="flex items-start gap-3 p-3 sm:p-4 rounded-xl border-2 cursor-pointer transition-all">
                                <input type="radio" :value="addr.id" v-model="selectedAddressId" class="mt-1 accent-amber-500 shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-0.5">
                                        <span class="font-semibold text-gray-900 text-sm">{{ addressName(addr) }}</span>
                                        <span v-if="addr.label" class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full capitalize">{{ addr.label }}</span>
                                        <span v-if="addr.is_default" class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Default</span>
                                    </div>
                                    <p class="text-sm text-gray-600 break-words">{{ addressLine1(addr) }}, {{ addr.city }}, {{ addr.state }} - {{ addr.pincode }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">📱 {{ addr.phone }}</p>
                                </div>
                                <div v-if="selectedAddressId === addr.id" class="shrink-0 text-amber-500">
                                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                </div>
                            </label>
                        </div>
                        <div v-else class="text-center py-6">
                            <p class="text-gray-500 text-sm mb-2">No saved addresses.</p>
                            <button @click="showAddressModal = true" class="text-amber-600 text-sm hover:text-amber-700 font-medium">Add an address →</button>
                        </div>
                    </div>

                    <!-- Coupon -->
                    <div class="bg-white rounded-xl shadow-sm p-4 sm:p-5">
                        <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-amber-500 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                            Apply Coupon
                        </h2>

                        <!-- Applied coupon banner -->
                        <div v-if="couponApplied" class="mb-3 bg-green-50 border border-green-200 rounded-xl p-3 flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0 text-base">🎉</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-green-800">{{ couponApplied.code }} applied!</p>
                                <p class="text-xs text-green-600">You save {{ currency(couponApplied.discount) }}{{ couponApplied.description ? ' · ' + couponApplied.description : '' }}</p>
                            </div>
                            <button @click="removeCoupon" class="shrink-0 text-gray-400 hover:text-red-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Input row -->
                        <div v-if="!couponApplied" class="flex gap-2">
                            <input
                                v-model="couponCode"
                                @keyup.enter="applyCoupon()"
                                type="text"
                                placeholder="Enter coupon code"
                                class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 uppercase placeholder:capitalize placeholder:normal-case"
                            />
                            <button @click="applyCoupon()" :disabled="couponLoading || !couponCode.trim()"
                                class="bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-semibold text-sm px-4 py-2.5 rounded-xl transition-colors shrink-0">
                                {{ couponLoading ? '…' : 'Apply' }}
                            </button>
                        </div>
                        <p v-if="couponError" class="text-red-500 text-xs mt-2">{{ couponError }}</p>

                        <!-- My coupons accordion -->
                        <div v-if="myCoupons.length" class="mt-3">
                            <button @click="showMyCoupons = !showMyCoupons"
                                class="flex items-center gap-1.5 text-xs text-amber-600 hover:text-amber-700 font-medium">
                                <svg class="w-3.5 h-3.5 transition-transform" :class="showMyCoupons ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                {{ showMyCoupons ? 'Hide' : 'Show' }} my coupons ({{ myCoupons.length }})
                            </button>
                            <div v-if="showMyCoupons" class="mt-2 space-y-2">
                                <div v-for="cp in myCoupons" :key="cp.id"
                                    class="flex items-center justify-between bg-amber-50 border border-amber-100 rounded-xl px-3 py-2.5">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 tracking-wider">{{ cp.code }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ cp.type === 'percent' ? cp.value + '% off' : '₹' + cp.value + ' off' }}
                                            <span v-if="cp.min_order_amount > 0">· Min ₹{{ cp.min_order_amount }}</span>
                                            <span v-if="cp.expires_at"> · Expires {{ new Date(cp.expires_at).toLocaleDateString('en-IN') }}</span>
                                        </p>
                                        <p v-if="cp.description" class="text-xs text-amber-700 mt-0.5">{{ cp.description }}</p>
                                    </div>
                                    <button @click="applyCoupon(cp.code)"
                                        class="shrink-0 text-xs bg-amber-500 hover:bg-amber-600 text-white font-medium px-3 py-1.5 rounded-lg transition-colors">
                                        Apply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white rounded-xl shadow-sm p-4 sm:p-5">
                        <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-amber-500 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                            Payment Method
                        </h2>
                        <div class="space-y-2.5">
                            <label :class="paymentMethod === 'razorpay' ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-gray-300'"
                                class="flex items-center gap-3 p-3 sm:p-4 rounded-xl border-2 cursor-pointer transition-all">
                                <input type="radio" value="razorpay" v-model="paymentMethod" class="accent-amber-500 shrink-0" />
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="w-10 h-7 bg-blue-700 rounded flex items-center justify-center shrink-0">
                                        <span class="text-white text-xs font-bold">RP</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Pay Online</p>
                                        <p class="text-xs text-gray-500">UPI, Cards, Netbanking via Razorpay</p>
                                    </div>
                                </div>
                                <span v-if="paymentMethod === 'razorpay'" class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full shrink-0">Selected</span>
                            </label>
                            <label :class="paymentMethod === 'cod' ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-gray-300'"
                                class="flex items-center gap-3 p-3 sm:p-4 rounded-xl border-2 cursor-pointer transition-all">
                                <input type="radio" value="cod" v-model="paymentMethod" class="accent-amber-500 shrink-0" />
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="w-10 h-7 bg-green-100 rounded flex items-center justify-center text-base shrink-0">💵</div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Cash on Delivery</p>
                                        <p class="text-xs text-gray-500">Pay when your order arrives</p>
                                    </div>
                                </div>
                                <span v-if="paymentMethod === 'cod'" class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full shrink-0">Selected</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ── Right: Order Summary ────────────────────────────────── -->
                <div>
                    <div class="bg-white rounded-xl shadow-sm p-4 sm:p-5 lg:sticky lg:top-24 space-y-4">
                        <h2 class="font-semibold text-gray-900">Order Summary</h2>

                        <!-- Cart items -->
                        <div class="space-y-2.5 max-h-52 overflow-y-auto pr-1">
                            <div v-for="item in cartItems" :key="item.id" class="flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden shrink-0">
                                    <img v-if="item.product?.primary_image?.url || item.product?.primary_image?.image_url"
                                        :src="item.product.primary_image?.url || item.product.primary_image?.image_url"
                                        :alt="item.product?.name" class="w-full h-full object-cover" />
                                    <span v-else class="flex items-center justify-center h-full text-base">📦</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-900 truncate">{{ item.product?.name }}</p>
                                    <p class="text-xs text-gray-400">× {{ item.quantity }}{{ item.variant ? ' · ' + item.variant.name : '' }}</p>
                                </div>
                                <p class="text-xs font-semibold text-gray-900 shrink-0">
                                    {{ currency(item.quantity * parseFloat(item.unit_price ?? 0)) }}
                                </p>
                            </div>
                        </div>

                        <!-- Price breakdown -->
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
                            <div v-if="deliveryFee > 0" class="bg-amber-50 rounded-lg px-3 py-2 text-xs text-amber-700">
                                🚚 Add {{ currency(499 - subtotal) }} more for <strong>free delivery</strong>
                            </div>
                            <div v-if="couponApplied" class="flex justify-between text-green-600 font-medium">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    Coupon ({{ couponApplied.code }})
                                </span>
                                <span>- {{ currency(discount) }}</span>
                            </div>
                            <div class="border-t border-gray-100 pt-2 flex justify-between font-bold text-gray-900">
                                <span>Total</span>
                                <span class="text-amber-600 text-base">{{ currency(total) }}</span>
                            </div>
                            <p v-if="couponApplied" class="text-xs text-center text-green-600 font-medium">
                                🎉 You're saving {{ currency(discount) }} on this order!
                            </p>
                        </div>

                        <button @click="placeOrder" :disabled="placingOrder || !cartItems.length || !selectedAddressId"
                            class="w-full bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-bold py-3.5 rounded-xl transition-colors text-sm shadow-sm">
                            <span v-if="placingOrder" class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Processing…
                            </span>
                            <span v-else>
                                {{ paymentMethod === 'cod' ? '🛒 Place Order' : '💳 Pay ' + currency(total) }}
                            </span>
                        </button>
                        <p class="text-xs text-center text-gray-400">🔒 Secured · All transactions are encrypted</p>
                    </div>
                </div>
            </div>

            <!-- Mobile sticky bar -->
            <div v-if="!loading && cartItems.length" class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 px-4 py-3 flex items-center gap-3 z-40 shadow-lg">
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-500">Total <span v-if="couponApplied" class="text-green-600 ml-1">(Saved {{ currency(discount) }})</span></p>
                    <p class="font-bold text-gray-900 text-sm">{{ currency(total) }}</p>
                </div>
                <button @click="placeOrder" :disabled="placingOrder || !cartItems.length || !selectedAddressId"
                    class="bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-bold py-2.5 px-6 rounded-xl transition-colors text-sm shrink-0">
                    {{ placingOrder ? '…' : (paymentMethod === 'cod' ? 'Place Order' : 'Pay ' + currency(total)) }}
                </button>
            </div>
            <div class="lg:hidden h-20"></div>

            <!-- ── Add Address Modal ──────────────────────────────────────── -->
            <Teleport to="body">
                <div v-if="showAddressModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
                    <div class="absolute inset-0 bg-black/50" @click="showAddressModal = false"></div>
                    <div class="relative w-full sm:max-w-lg bg-white sm:rounded-2xl rounded-t-2xl p-5 sm:p-6 shadow-xl z-10">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900 text-lg">Add New Address</h3>
                            <button @click="showAddressModal = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs text-gray-500 font-medium">Full Name *</label>
                                    <input v-model="newAddr.name" type="text" placeholder="Full name"
                                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                                    <p v-if="addrErrors.name" class="text-xs text-red-500 mt-0.5">{{ addrErrors.name?.[0] }}</p>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 font-medium">Phone *</label>
                                    <input v-model="newAddr.phone" type="tel" placeholder="10-digit mobile"
                                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                                    <p v-if="addrErrors.phone" class="text-xs text-red-500 mt-0.5">{{ addrErrors.phone?.[0] }}</p>
                                </div>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 font-medium">Address Line *</label>
                                <input v-model="newAddr.address_line_1" type="text" placeholder="House no., Street, Area"
                                    class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                                <p v-if="addrErrors.address_line_1" class="text-xs text-red-500 mt-0.5">{{ addrErrors.address_line_1?.[0] }}</p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 font-medium">Address Line 2</label>
                                <input v-model="newAddr.address_line_2" type="text" placeholder="Apartment, Suite, Landmark (optional)"
                                    class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs text-gray-500 font-medium">Pincode *</label>
                                    <input v-model="newAddr.pincode" type="text" placeholder="6-digit"
                                        maxlength="6"
                                        inputmode="numeric"
                                        @blur="lookupIndianPincode"
                                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                                    <p v-if="pincodeLookupLoading" class="text-xs text-gray-500 mt-0.5">Detecting location...</p>
                                    <p v-if="pincodeLookupError" class="text-xs text-red-500 mt-0.5">{{ pincodeLookupError }}</p>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 font-medium">Country *</label>
                                    <input v-model="newAddr.country" type="text" readonly
                                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 font-medium">State *</label>
                                    <input v-model="newAddr.state" type="text" placeholder="State"
                                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 font-medium">District *</label>
                                    <input v-model="newAddr.district" type="text" placeholder="District"
                                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                                </div>
                                <div class="col-span-2">
                                    <label class="text-xs text-gray-500 font-medium">City *</label>
                                    <input v-model="newAddr.city" type="text" placeholder="City"
                                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                                </div>
                                <div class="col-span-2" v-if="pincodePostOffices.length">
                                    <label class="text-xs text-gray-500 font-medium">Nearest Post Offices</label>
                                    <p class="mt-1 text-xs text-gray-600">{{ pincodePostOffices.join(', ') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 flex gap-3">
                            <button @click="showAddressModal = false" class="flex-1 border border-gray-200 text-gray-600 font-medium py-2.5 rounded-xl text-sm hover:bg-gray-50 transition-colors">Cancel</button>
                            <button @click="saveAddress" :disabled="savingAddr" class="flex-1 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">
                                {{ savingAddr ? 'Saving…' : 'Save Address' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Teleport>
        </div>
    </AppLayout>
</template>
