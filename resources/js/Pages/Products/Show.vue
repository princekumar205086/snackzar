<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import DeliveryCheck from '@/Components/DeliveryCheck.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useCart } from '@/composables/useCart';
import { useToast } from '@/composables/useToast';
import { useWishlist } from '@/composables/useWishlist';

const props = defineProps({
    product: { type: Object, required: true },
    reviews: { type: Object, default: () => ({}) },
    relatedProducts: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const { addToCart: cartAdd, updateQuantity, getItemForProductVariant, cartProductIds } = useCart();
const { show: showToast } = useToast();
const { isWishlisted, toggleWishlist } = useWishlist();

const selectedImage = ref(0);
const selectedVariant = ref(null);
const quantity = ref(1);
const activeTab = ref('description');
const addingToCart = ref(false);
const wishlistLoading = ref(false);

// Stock for the currently selected option
const currentStock = computed(() => selectedVariant.value?.stock ?? props.product.stock ?? 0);

const images = computed(() => {
    return props.product.images?.length ? props.product.images : [{ url: '/images/placeholder-product.png', alt: props.product.name }];
});

const currentPrice = computed(() => selectedVariant.value?.price ?? props.product.price);
const comparePrice = computed(() => selectedVariant.value?.compare_price ?? props.product.compare_price);

const discountPercent = computed(() => {
    if (comparePrice.value && parseFloat(comparePrice.value) > parseFloat(currentPrice.value)) {
        return Math.round(((comparePrice.value - currentPrice.value) / comparePrice.value) * 100);
    }
    return 0;
});

const inStock = computed(() => {
    return selectedVariant.value ? selectedVariant.value.stock > 0 : props.product.stock > 0;
});

const cartItem = computed(() => getItemForProductVariant(props.product.id, selectedVariant.value?.id ?? null));
const inCart = computed(() => !!cartItem.value);
const wishlisted = computed(() => isWishlisted(props.product.id));
const hasVariants = computed(() => props.product.variants?.length > 0);
const variantRequired = computed(() => hasVariants.value && !selectedVariant.value);

const formatPrice = (price) => '₹' + Number(price ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 0 });

async function handleAddToCart() {
    if (!user.value) {
        window.location.href = '/login';
        return;
    }
    const safeQty = Math.min(quantity.value, currentStock.value || 1);
    addingToCart.value = true;
    try {
        await cartAdd(props.product.id, selectedVariant.value?.id ?? null, safeQty);
        showToast(`${props.product.name} added to cart! 🛒`, 'success');
    } catch (e) {
        showToast(e.response?.data?.message || 'Failed to add to cart', 'error');
    } finally {
        addingToCart.value = false;
    }
}

async function handleCartQty(newQty) {
    if (!cartItem.value) return;
    if (newQty > currentStock.value) return; // never exceed actual stock
    try {
        await updateQuantity(cartItem.value.id, newQty);
    } catch {
        showToast('Could not update quantity', 'error');
    }
}

async function addToWishlist() {
    if (!user.value) {
        window.location.href = '/login';
        return;
    }
    wishlistLoading.value = true;
    try {
        const res = await toggleWishlist(props.product.id);
        const added = res.data?.added ?? false;
        showToast(added ? 'Added to wishlist! ❤️' : 'Removed from wishlist', added ? 'success' : 'info');
    } catch (e) {
        showToast(e.response?.data?.message || 'Failed to update wishlist', 'error');
    } finally {
        wishlistLoading.value = false;
    }
}

function shareProduct() {
    if (navigator.share) {
        navigator.share({ title: props.product.name, url: window.location.href }).catch(() => {});
    } else {
        navigator.clipboard.writeText(window.location.href);
        showToast('Product link copied! 🔗', 'success');
    }
}

async function handleBuyNow() {
    if (!user.value) { window.location.href = '/login'; return; }
    addingToCart.value = true;
    try {
        await cartAdd(props.product.id, selectedVariant.value?.id ?? null, quantity.value);
        window.location.href = '/checkout';
    } catch (e) {
        showToast(e.response?.data?.message || 'Failed to add to cart', 'error');
        addingToCart.value = false;
    }
}
</script>

<template>
    <AppLayout :title="product.name">
        <div class="bg-gray-50 min-h-screen">

            <!-- Breadcrumb bar -->
            <div class="bg-white border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                    <nav class="flex items-center gap-1.5 text-sm text-gray-500 flex-wrap">
                        <Link href="/" class="hover:text-amber-600 transition-colors">Home</Link>
                        <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        <Link href="/products" class="hover:text-amber-600 transition-colors">Products</Link>
                        <template v-if="product.category">
                            <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            <Link :href="`/products?category=${product.category.slug}`" class="hover:text-amber-600 transition-colors">{{ product.category.name }}</Link>
                        </template>
                        <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        <span class="text-gray-900 font-semibold truncate max-w-[200px]">{{ product.name }}</span>
                    </nav>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-10">

                <!-- Main Product Section -->
                <div class="grid lg:grid-cols-2 gap-6 lg:gap-14 items-start">

                    <!-- Image Gallery — sticky on desktop -->
                    <div class="lg:sticky lg:top-6 lg:self-start">
                        <!-- Main Image -->
                        <div class="aspect-square bg-gray-100 rounded-3xl overflow-hidden shadow-lg mb-3 relative">
                            <img
                                :src="images[selectedImage]?.url || images[selectedImage]?.image_url || '/images/placeholder-product.png'"
                                :alt="product.name"
                                class="w-full h-full object-cover"
                            />
                            <!-- Badges -->
                            <div class="absolute top-4 left-4 flex flex-col gap-2">
                                <span v-if="discountPercent > 0" class="bg-green-500 text-white font-bold px-3 py-1 rounded-xl text-sm shadow-lg">
                                    {{ discountPercent }}% OFF
                                </span>
                                <span v-if="product.is_featured" class="bg-amber-500 text-white font-bold px-3 py-1 rounded-xl text-sm shadow-lg">
                                    Featured
                                </span>
                            </div>
                        </div>
                        <!-- Thumbnails -->
                        <div v-if="images.length > 1" class="flex gap-2.5 overflow-x-auto pb-1">
                            <button
                                v-for="(img, idx) in images"
                                :key="idx"
                                @click="selectedImage = idx"
                                class="w-[72px] h-[72px] rounded-xl overflow-hidden border-2 transition-all shrink-0"
                                :class="selectedImage === idx ? 'border-amber-500 shadow-md' : 'border-transparent hover:border-gray-300'"
                            >
                                <img :src="img.url || img.image_url" :alt="`${product.name} ${idx + 1}`" class="w-full h-full object-cover" />
                            </button>
                        </div>
                    </div>

                    <!-- Product Info Panel -->
                    <div class="flex flex-col">
                        <!-- Category tag -->
                        <div v-if="product.category" class="mb-3">
                            <Link :href="`/products?category=${product.category.slug}`"
                                class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 text-xs font-bold px-3 py-1.5 rounded-full hover:bg-amber-100 transition-colors border border-amber-200">
                                {{ product.category.name }}
                            </Link>
                        </div>

                        <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900 mb-3 leading-tight">{{ product.name }}</h1>

                        <!-- Rating row -->
                        <div v-if="product.avg_rating > 0" class="flex items-center gap-2 mb-4">
                            <div class="flex gap-0.5">
                                <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= Math.round(product.avg_rating) ? 'text-amber-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-700">{{ product.avg_rating }}</span>
                            <span class="text-sm text-gray-400">({{ product.total_reviews }} reviews)</span>
                        </div>

                        <!-- Price block -->
                        <div class="flex items-baseline gap-3 mb-5 pb-5 border-b border-gray-100">
                            <span class="text-3xl lg:text-4xl font-extrabold text-gray-900">{{ formatPrice(currentPrice) }}</span>
                            <span v-if="discountPercent > 0" class="text-lg text-gray-400 line-through">{{ formatPrice(comparePrice) }}</span>
                            <span v-if="discountPercent > 0" class="bg-red-100 text-red-600 text-sm font-bold px-2.5 py-1 rounded-full">
                                Save {{ discountPercent }}%
                            </span>
                        </div>

                        <!-- Short description -->
                        <p v-if="product.short_description" class="text-gray-600 leading-relaxed mb-5 text-sm lg:text-base">{{ product.short_description }}</p>

                        <!-- Variants -->
                        <div v-if="product.variants?.length > 0" class="mb-5">
                            <h3 class="text-sm font-bold text-gray-900 mb-2.5">Select Variant</h3>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="variant in product.variants"
                                    :key="variant.id"
                                    @click="selectedVariant = variant"
                                    class="px-4 py-2 rounded-xl border-2 text-sm font-semibold transition-all"
                                    :class="selectedVariant?.id === variant.id ? 'border-amber-500 bg-amber-50 text-amber-700' : 'border-gray-200 text-gray-700 hover:border-gray-300'"
                                >
                                    {{ variant.name }} — {{ formatPrice(variant.price) }}
                                </button>
                            </div>
                        </div>

                        <!-- Stock & Meta row -->
                        <div class="flex items-center flex-wrap gap-3 mb-4">
                            <span :class="inStock ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'"
                                class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full border">
                                <span class="w-1.5 h-1.5 rounded-full inline-block" :class="inStock ? 'bg-green-500' : 'bg-red-500'"></span>
                                {{ inStock ? 'In Stock' : 'Out of Stock' }}
                            </span>
                            <span v-if="product.sku" class="text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full">SKU: {{ product.sku }}</span>
                            <span v-if="product.weight" class="text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full">{{ product.weight }}{{ product.unit || 'g' }}</span>
                        </div>

                        <!-- Low stock alert -->
                        <div v-if="inStock && currentStock <= 10" class="flex items-center gap-2 text-xs text-orange-700 bg-orange-50 border border-orange-100 rounded-xl px-3 py-2.5 mb-4">
                            <svg class="w-4 h-4 text-orange-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            Only {{ currentStock }} left in stock — order soon!
                        </div>

                        <!-- Quantity & Action Buttons -->
                        <div class="space-y-3 mb-6">
                            <!-- In cart state -->
                            <div v-if="inCart" class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center bg-amber-50 border-2 border-amber-400 rounded-xl overflow-hidden">
                                    <button @click="handleCartQty(cartItem.quantity - 1)"
                                        class="w-11 h-11 flex items-center justify-center text-amber-600 hover:bg-amber-100 font-bold text-xl transition-colors">−</button>
                                    <span class="w-12 text-center font-bold text-amber-700 text-lg">{{ cartItem?.quantity }}</span>
                                    <button @click="handleCartQty(cartItem.quantity + 1)" :disabled="cartItem.quantity >= currentStock"
                                        class="w-11 h-11 flex items-center justify-center text-amber-600 hover:bg-amber-100 disabled:opacity-40 font-bold text-xl transition-colors">+</button>
                                </div>
                                <a href="/cart" class="flex-1 inline-flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white px-5 py-3 rounded-xl font-bold text-sm transition-all shadow-lg shadow-amber-600/25">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Go to Cart
                                </a>
                            </div>

                            <!-- Not in cart state -->
                            <div v-else>
                                <!-- Qty picker + action icons row -->
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="flex items-center border-2 border-gray-200 hover:border-gray-300 rounded-xl overflow-hidden transition-colors">
                                        <button @click="quantity = Math.max(1, quantity - 1)"
                                            class="w-11 h-11 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                                        </button>
                                        <span class="w-12 text-center font-bold text-gray-900 text-lg">{{ quantity }}</span>
                                        <button @click="quantity = Math.min(currentStock || 10, quantity + 1)" :disabled="quantity >= currentStock"
                                            class="w-11 h-11 flex items-center justify-center text-gray-600 hover:bg-gray-50 disabled:opacity-40 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        </button>
                                    </div>
                                    <button @click="addToWishlist" :disabled="wishlistLoading"
                                        class="w-11 h-11 border-2 rounded-xl flex items-center justify-center transition-all"
                                        :class="wishlisted ? 'border-red-400 bg-red-50 text-red-500' : 'border-gray-200 text-gray-400 hover:text-red-400 hover:border-red-200'"
                                        title="Wishlist">
                                        <svg class="w-5 h-5" :fill="wishlisted ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                    </button>
                                    <button @click="shareProduct"
                                        class="w-11 h-11 border-2 border-gray-200 rounded-xl flex items-center justify-center text-gray-400 hover:text-blue-500 hover:border-blue-200 transition-all"
                                        title="Share">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Variant required hint -->
                                <div v-if="variantRequired" class="flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5 mb-3">
                                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span class="text-xs text-amber-700 font-semibold">Please select a variant above before adding to cart</span>
                                </div>

                                <!-- Add to Cart + Buy Now -->
                                <div class="flex gap-3">
                                    <button @click="handleAddToCart" :disabled="!inStock || addingToCart || variantRequired"
                                        class="flex-1 bg-amber-600 text-white px-6 py-3.5 rounded-xl font-bold hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-amber-600/20 flex items-center justify-center gap-2 text-sm">
                                        <svg v-if="addingToCart" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        {{ addingToCart ? 'Adding…' : (!inStock ? 'Out of Stock' : variantRequired ? 'Select Variant' : 'Add to Cart') }}
                                    </button>
                                    <button v-if="inStock && !variantRequired" @click="handleBuyNow" :disabled="addingToCart"
                                        class="flex-1 bg-gray-900 text-white px-6 py-3.5 rounded-xl font-bold hover:bg-gray-800 disabled:opacity-50 transition-all flex items-center justify-center gap-2 text-sm">
                                        Buy Now
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Feature grid -->
                        <div class="grid grid-cols-2 gap-2.5 mb-6">
                            <div class="flex items-center gap-2.5 bg-white rounded-xl p-3 border border-gray-100 shadow-sm">
                                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                                </div>
                                <span class="text-xs text-gray-600 font-semibold">Free delivery ₹499+</span>
                            </div>
                            <div class="flex items-center gap-2.5 bg-white rounded-xl p-3 border border-gray-100 shadow-sm">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <span class="text-xs text-gray-600 font-semibold">100% authentic</span>
                            </div>
                            <div class="flex items-center gap-2.5 bg-white rounded-xl p-3 border border-gray-100 shadow-sm">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                </div>
                                <span class="text-xs text-gray-600 font-semibold">7-day returns</span>
                            </div>
                            <div class="flex items-center gap-2.5 bg-white rounded-xl p-3 border border-gray-100 shadow-sm">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                </div>
                                <span class="text-xs text-gray-600 font-semibold">Secure payment</span>
                            </div>
                        </div>

                        <!-- Delivery check -->
                        <div class="border-t border-gray-100 pt-5">
                            <DeliveryCheck />
                        </div>
                    </div>
                </div>

                <!-- Tabs: Description / Reviews -->
                <div class="mt-10 bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="flex border-b border-gray-100">
                        <button @click="activeTab = 'description'"
                            class="flex-1 sm:flex-none px-6 py-4 text-sm font-bold border-b-2 transition-colors"
                            :class="activeTab === 'description' ? 'border-amber-500 text-amber-700' : 'border-transparent text-gray-400 hover:text-gray-700'">
                            Description
                        </button>
                        <button @click="activeTab = 'reviews'"
                            class="flex-1 sm:flex-none px-6 py-4 text-sm font-bold border-b-2 transition-colors"
                            :class="activeTab === 'reviews' ? 'border-amber-500 text-amber-700' : 'border-transparent text-gray-400 hover:text-gray-700'">
                            Reviews
                            <span class="ml-1.5 bg-gray-100 text-gray-500 text-xs font-bold px-2 py-0.5 rounded-full">{{ product.total_reviews || 0 }}</span>
                        </button>
                    </div>

                    <!-- Description tab -->
                    <div v-if="activeTab === 'description'" class="p-6 lg:p-8">
                        <div class="prose prose-amber max-w-none" v-html="product.description || '<p class=\'text-gray-400 text-sm\'>No description available.</p>'"></div>
                        <!-- Attributes table -->
                        <div v-if="product.attributes && Object.keys(product.attributes).length > 0" class="mt-8">
                            <h3 class="text-base font-bold text-gray-900 mb-4">Specifications</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div v-for="(val, key) in product.attributes" :key="key"
                                    class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                    <span class="text-sm font-medium text-gray-500 capitalize">{{ key.replace('_', ' ') }}</span>
                                    <span class="text-sm font-bold text-gray-900">{{ val }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews tab -->
                    <div v-if="activeTab === 'reviews'" class="p-6 lg:p-8">
                        <div v-if="reviews.data?.length > 0" class="space-y-5">
                            <div v-for="review in reviews.data" :key="review.id" class="flex items-start gap-4 pb-5 border-b border-gray-50 last:border-0">
                                <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-500 text-white rounded-full flex items-center justify-center text-sm font-bold shrink-0">
                                    {{ review.user?.name?.charAt(0)?.toUpperCase() }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-1.5">
                                        <span class="font-bold text-gray-900 text-sm">{{ review.user?.name }}</span>
                                        <div class="flex gap-0.5">
                                            <svg v-for="i in 5" :key="i" class="w-3.5 h-3.5" :class="i <= review.rating ? 'text-amber-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="text-gray-600 text-sm leading-relaxed">{{ review.comment }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-14">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                            </div>
                            <p class="text-gray-500 text-sm font-medium">No reviews yet. Be the first to review!</p>
                        </div>
                    </div>
                </div>

                <!-- Related Products -->
                <section v-if="relatedProducts.length > 0" class="mt-12">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">You Might Also Like</h2>
                        <Link href="/products" class="text-sm font-semibold text-amber-600 hover:text-amber-700 transition-colors">
                            View All →
                        </Link>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
                        <ProductCard v-for="p in relatedProducts" :key="p.id" :product="p" />
                    </div>
                </section>

            </div>
        </div>
    </AppLayout>
</template>
