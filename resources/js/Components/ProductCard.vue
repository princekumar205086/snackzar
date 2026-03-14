<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useCart } from '@/composables/useCart';
import { useToast } from '@/composables/useToast';
import { useWishlist } from '@/composables/useWishlist';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const { getItemForProduct, getItemForProductVariant, addToCart, updateQuantity } = useCart();
const { show: showToast } = useToast();
const { isWishlisted, toggleWishlist } = useWishlist();

const wishlisted = computed(() => isWishlisted(props.product.id));
const wishlistLoading = ref(false);
const hasVariants = computed(() => (props.product.variants?.length ?? 0) > 0);
const selectedVariant = ref(null);
const variantModalOpen = ref(false);

const variantStock = computed(() => selectedVariant.value?.stock ?? 0);
const productStock = computed(() => (hasVariants.value ? variantStock.value : (props.product.stock ?? 99)));

const variantsMinPrice = computed(() => {
    if (!hasVariants.value) return null;
    const prices = props.product.variants.map((v) => parseFloat(v.price ?? 0)).filter((p) => Number.isFinite(p) && p > 0);
    if (!prices.length) return null;
    return Math.min(...prices);
});

const displayPrice = computed(() => {
    if (selectedVariant.value) return selectedVariant.value.price;
    if (hasVariants.value && variantsMinPrice.value != null) return variantsMinPrice.value;
    return props.product.price;
});

const cartItem = computed(() => {
    if (hasVariants.value) {
        if (!selectedVariant.value) return null;
        return getItemForProductVariant(props.product.id, selectedVariant.value.id);
    }
    return getItemForProduct(props.product.id);
});

const inCart = computed(() => !!cartItem.value);

const loading = ref(false);

const loginRedirectUrl = () => `/login?redirect=${encodeURIComponent(window.location.pathname + window.location.search)}`;

const imageUrl = computed(() => {
    return props.product.primary_image?.url || props.product.primary_image?.image_url || '/images/placeholder-product.png';
});

const discountPercent = computed(() => {
    if (props.product.compare_price && parseFloat(props.product.compare_price) > parseFloat(props.product.price)) {
        return Math.round(((props.product.compare_price - props.product.price) / props.product.compare_price) * 100);
    }
    return 0;
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
};

const handleAdd = async (e) => {
    e.preventDefault();
    if (!user.value) {
        window.location.href = loginRedirectUrl();
        return;
    }

    if (hasVariants.value && !selectedVariant.value) {
        variantModalOpen.value = true;
        return;
    }

    if (inCart.value) return;

    loading.value = true;
    try {
        await addToCart(props.product.id, selectedVariant.value?.id ?? null, 1);
        showToast(`${props.product.name} added to cart`, 'success');
        variantModalOpen.value = false;
    } catch (err) {
        showToast(err.response?.data?.message || 'Could not add to cart. Please try again.', 'error');
    } finally {
        loading.value = false;
    }
};

const handleQtyChange = async (e, delta) => {
    e.preventDefault();
    if (!cartItem.value) return;
    const newQty = cartItem.value.quantity + delta;
    if (delta > 0 && newQty > productStock.value) return; // enforce stock limit
    loading.value = true;
    try {
        await updateQuantity(cartItem.value.id, newQty);
    } catch (err) {
        showToast(err.response?.data?.message || 'Could not update quantity.', 'error');
    } finally {
        loading.value = false;
    }
};

const openVariantModal = (e) => {
    e.preventDefault();
    variantModalOpen.value = true;
};

const selectVariant = (variant) => {
    selectedVariant.value = variant;
};

const closeVariantModal = () => {
    variantModalOpen.value = false;
};

const handleWishlist = async (e) => {
    e.preventDefault();
    if (!user.value) { window.location.href = loginRedirectUrl(); return; }
    wishlistLoading.value = true;
    try {
        const res = await toggleWishlist(props.product.id);
        const added = res.data?.added ?? false;
        showToast(added ? '❤️ Added to wishlist' : 'Removed from wishlist', added ? 'success' : 'info');
    } catch {
        showToast('Could not update wishlist', 'error');
    } finally {
        wishlistLoading.value = false;
    }
};
</script>

<template>
    <Link :href="`/products/${product.slug}`" class="group bg-white rounded-2xl overflow-hidden border border-gray-100 hover:border-amber-200 hover:shadow-xl transition-all duration-300 flex flex-col">
        <!-- Image -->
        <div class="relative overflow-hidden bg-gray-100 aspect-square">
            <img
                :src="imageUrl"
                :alt="product.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                loading="lazy"
            />
            <!-- Subtle bottom gradient -->
            <div class="absolute inset-x-0 bottom-0 h-1/4 bg-gradient-to-t from-black/20 to-transparent pointer-events-none"></div>
            <!-- Badges top-left (stacked) -->
            <div class="absolute top-2 left-2 flex flex-col gap-1">
                <span v-if="discountPercent > 0" class="bg-green-500 text-white text-xs font-bold px-2 py-0.5 rounded-lg shadow-sm leading-tight">
                    {{ discountPercent }}% OFF
                </span>
                <span v-if="product.is_featured" class="bg-amber-500 text-white text-xs font-bold px-2 py-0.5 rounded-lg shadow-sm leading-tight">
                    Featured
                </span>
            </div>
            <!-- Wishlist heart - top right -->
            <button
                @click.prevent="handleWishlist($event)"
                :disabled="wishlistLoading"
                class="absolute top-2 right-2 w-8 h-8 rounded-full flex items-center justify-center backdrop-blur-sm shadow-sm transition-all"
                :class="wishlisted ? 'bg-white text-red-500' : 'bg-white/80 text-gray-300 hover:text-red-400'"
                :title="wishlisted ? 'Remove from wishlist' : 'Add to wishlist'"
            >
                <svg class="w-4 h-4" :fill="wishlisted ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="p-3 flex flex-col flex-1">
            <p v-if="product.category" class="text-xs text-amber-600 font-medium mb-0.5">{{ product.category.name }}</p>
            <h3 class="text-sm font-semibold text-gray-900 line-clamp-2 mb-1.5 flex-1 leading-snug">{{ product.name }}</h3>
            <p v-if="hasVariants && selectedVariant" class="text-[11px] text-amber-700 font-semibold mb-1">
                {{ selectedVariant.name }}
            </p>

            <!-- Rating -->
            <div v-if="product.avg_rating > 0" class="flex items-center gap-1 mb-2">
                <div class="flex">
                    <svg v-for="i in 5" :key="i" class="w-3 h-3" :class="i <= Math.round(product.avg_rating) ? 'text-amber-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                <span class="text-xs text-gray-400">({{ product.total_reviews }})</span>
            </div>

            <!-- Price + Add/Qty -->
            <div class="flex items-center justify-between mt-auto gap-2">
                <div>
                    <span class="text-base font-bold text-gray-900">
                        {{ hasVariants && !selectedVariant && variantsMinPrice != null ? `From ${formatPrice(displayPrice)}` : formatPrice(displayPrice) }}
                    </span>
                    <span v-if="discountPercent > 0" class="block text-xs text-gray-400 line-through">{{ formatPrice(product.compare_price) }}</span>
                </div>

                <!-- Quantity controls when in cart -->
                <div v-if="inCart && cartItem" class="flex items-center gap-1" @click.prevent>
                    <button @click.prevent="handleQtyChange($event, -1)" :disabled="loading"
                        class="w-7 h-7 rounded-full border-2 border-amber-500 text-amber-600 hover:bg-amber-50 disabled:opacity-50 flex items-center justify-center font-bold text-base transition-colors">−</button>
                    <span class="w-6 text-center text-sm font-bold text-gray-900">{{ cartItem.quantity }}</span>
                    <button @click.prevent="handleQtyChange($event, 1)" :disabled="loading || cartItem.quantity >= productStock"
                        class="w-7 h-7 rounded-full bg-amber-600 hover:bg-amber-700 disabled:opacity-40 disabled:cursor-not-allowed text-white flex items-center justify-center font-bold text-base transition-colors">+</button>
                </div>

                <!-- Add button when not in cart -->
                <button
                    v-else-if="hasVariants && !selectedVariant"
                    @click.prevent="openVariantModal($event)"
                    class="h-9 px-3 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-full text-xs font-bold border border-amber-200 transition-all duration-200 shrink-0"
                >
                    Options
                </button>

                <button v-else @click.prevent="handleAdd($event)" :disabled="loading"
                    class="w-9 h-9 bg-amber-600 hover:bg-amber-700 disabled:bg-amber-400 text-white rounded-full flex items-center justify-center shadow-md transition-all duration-200 shrink-0"
                    :title="user ? 'Add to cart' : 'Login to add'">
                    <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            </div>
        </div>

        <div v-if="variantModalOpen" class="fixed inset-0 z-[90] bg-black/40 flex items-end sm:items-center justify-center p-3" @click.prevent="closeVariantModal">
            <div class="w-full max-w-sm rounded-2xl bg-white border border-gray-100 shadow-2xl p-4 sm:p-5" @click.stop>
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Select Variant</p>
                        <h4 class="text-sm font-bold text-gray-900 line-clamp-1">{{ product.name }}</h4>
                    </div>
                    <button @click.prevent="closeVariantModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-2 max-h-64 overflow-auto">
                    <button
                        v-for="variant in product.variants"
                        :key="variant.id"
                        @click.prevent="selectVariant(variant)"
                        class="w-full flex items-center justify-between rounded-xl border px-3 py-2.5 text-left transition-colors"
                        :class="selectedVariant?.id === variant.id ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-amber-300'"
                    >
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ variant.name }}</p>
                            <p class="text-xs text-gray-500">{{ variant.stock > 0 ? `${variant.stock} in stock` : 'Out of stock' }}</p>
                        </div>
                        <p class="text-sm font-bold text-gray-900">{{ formatPrice(variant.price) }}</p>
                    </button>
                </div>

                <button
                    @click.prevent="handleAdd($event)"
                    :disabled="!selectedVariant || selectedVariant.stock <= 0 || loading"
                    class="mt-4 w-full rounded-xl bg-amber-600 text-white py-2.5 text-sm font-bold hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ loading ? 'Adding...' : 'Add Selected Variant' }}
                </button>
            </div>
        </div>
    </Link>
</template>
