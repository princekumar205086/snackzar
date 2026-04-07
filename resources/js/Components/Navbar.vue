<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useCart } from '@/composables/useCart';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const { cartCount, cartTotal } = useCart();
const loginUrl = computed(() => `/login?redirect=${encodeURIComponent(page.url || '/cart')}`);

const mobileMenuOpen = ref(false);
const mobileSearchOpen = ref(false);
const searchQuery = ref('');
const profileMenuOpen = ref(false);
const pincodeOpen = ref(false);
const pincode = ref('');
const pincodeResult = ref(null);
const pincodeChecking = ref(false);
const pincodeError = ref('');
const savedPincode = ref(localStorage.getItem('delivery_pincode') || '');
const savedCity = ref(localStorage.getItem('delivery_city') || '');

const submitSearch = () => {
    if (searchQuery.value.trim()) {
        router.get('/products', { search: searchQuery.value.trim() });
        mobileMenuOpen.value = false;
        mobileSearchOpen.value = false;
    }
};

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
    if (mobileMenuOpen.value) {
        mobileSearchOpen.value = false;
    }
};

const toggleMobileSearch = () => {
    mobileSearchOpen.value = !mobileSearchOpen.value;
    if (mobileSearchOpen.value) {
        mobileMenuOpen.value = false;
    }
};

const handleKeydown = (e) => {
    if (e.key === 'Escape') {
        pincodeOpen.value = false;
        profileMenuOpen.value = false;
        mobileMenuOpen.value = false;
        mobileSearchOpen.value = false;
    }
};

const serviceablePincodes = {
    '800001': 'Patna', '800002': 'Patna', '800003': 'Patna', '800004': 'Patna',
    '800005': 'Patna', '800006': 'Patna', '800007': 'Patna', '800008': 'Patna',
    '846004': 'Darbhanga', '842001': 'Muzaffarpur', '823001': 'Gaya',
    '812001': 'Bhagalpur', '845401': 'Motihari', '854301': 'Purnia',
    '110001': 'New Delhi', '110002': 'New Delhi', '201301': 'Noida',
    '122001': 'Gurgaon', '400001': 'Mumbai', '400050': 'Mumbai',
    '560001': 'Bangalore', '700001': 'Kolkata', '500001': 'Hyderabad',
    '600001': 'Chennai', '411001': 'Pune', '226001': 'Lucknow',
};

function checkPincode() {
    const cleaned = pincode.value.trim();
    pincodeError.value = '';
    pincodeResult.value = null;
    if (!/^\d{6}$/.test(cleaned)) {
        pincodeError.value = 'Enter a valid 6-digit pincode';
        return;
    }
    pincodeChecking.value = true;
    setTimeout(() => {
        const city = serviceablePincodes[cleaned];
        const prefix = cleaned.substring(0, 2);
        const biharPrefixes = ['80', '81', '82', '83', '84', '85', '86'];
        if (city || biharPrefixes.includes(prefix) || parseInt(cleaned) >= 100000) {
            const resolvedCity = city || (biharPrefixes.includes(prefix) ? 'Bihar' : 'India');
            pincodeResult.value = { available: true, city: resolvedCity };
            savedPincode.value = cleaned;
            savedCity.value = resolvedCity;
            localStorage.setItem('delivery_pincode', cleaned);
            localStorage.setItem('delivery_city', resolvedCity);
        } else {
            pincodeResult.value = { available: false };
        }
        pincodeChecking.value = false;
    }, 500);
}

const formatPrice = (price) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 0 }).format(price);

onMounted(() => document.addEventListener('keydown', handleKeydown));
onUnmounted(() => document.removeEventListener('keydown', handleKeydown));
</script>

<template>
    <div class="fixed top-0 left-0 right-0 z-[60]">
        <!-- Promo Bar -->
        <div class="bg-amber-600 text-white text-center text-xs sm:text-sm py-2 px-4 font-medium overflow-hidden">
            <span class="hidden sm:inline">🎉 Free delivery on orders above ₹499 · Authentic Bihari Snacks, Fresh from Source!</span>
            <span class="sm:hidden">🎉 Free delivery above ₹499</span>
        </div>

        <!-- Main Navbar -->
        <nav class="bg-white shadow-sm border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3 lg:gap-5 h-16">

                    <!-- Logo -->
                    <Link href="/" class="flex items-center shrink-0">
                        <img src="/images/logo/snackzar%20logo.png" alt="Snackzar Logo" class="h-10 w-auto sm:h-11 lg:h-12 xl:h-14" />
                    </Link>

                    <!-- Delivery Location (Desktop) -->
                    <div class="hidden lg:block shrink-0">
                        <button
                            @click="pincodeOpen = !pincodeOpen"
                            class="flex items-center gap-1.5 text-sm text-gray-700 hover:text-amber-700 transition-colors group"
                        >
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <div class="text-left">
                                <p class="text-xs text-gray-400 leading-none">Deliver to</p>
                                <p class="text-sm font-semibold leading-tight text-gray-800">
                                    {{ savedPincode ? savedPincode : 'Select location' }}
                                    <span v-if="savedCity" class="font-normal text-gray-500"> · {{ savedCity }}</span>
                                </p>
                            </div>
                            <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-amber-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Search Bar (Center) -->
                    <form @submit.prevent="submitSearch" class="hidden lg:flex flex-[1_1_0%] w-full min-w-0 relative">
                        <div class="flex w-full items-center bg-gray-50 border border-gray-200 rounded-full hover:border-amber-400 focus-within:border-amber-500 focus-within:bg-white transition-all shadow-sm">
                            <svg class="w-4 h-4 ml-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder='Search for "Makhana", "Namkeen", "Sweets"...'
                                class="flex-1 bg-transparent border-0 text-sm py-2.5 px-3 focus:ring-0 focus:outline-none placeholder-gray-400"
                            />
                            <button
                                v-if="searchQuery.trim()"
                                type="submit"
                                class="mr-1 bg-amber-600 text-white text-sm font-medium px-4 py-1.5 rounded-full hover:bg-amber-700 transition-colors"
                            >
                                Search
                            </button>
                        </div>
                    </form>

                    <!-- Right Actions -->
                    <div class="ml-auto lg:ml-0 flex items-center gap-1.5 sm:gap-2 shrink-0">

                        <!-- Profile / Login -->
                        <template v-if="user">
                            <div class="relative hidden lg:block">
                                <button
                                    @click="profileMenuOpen = !profileMenuOpen"
                                    class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-gray-50 transition-colors"
                                >
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 max-w-[80px] truncate">{{ user.name?.split(' ')[0] }}</span>
                                </button>
                                <Transition
                                    enter-active-class="transition ease-out duration-100"
                                    enter-from-class="opacity-0 scale-95 translate-y-1"
                                    enter-to-class="opacity-100 scale-100 translate-y-0"
                                    leave-active-class="transition ease-in duration-75"
                                    leave-from-class="opacity-100 scale-100"
                                    leave-to-class="opacity-0 scale-95"
                                >
                                    <div v-if="profileMenuOpen" v-click-outside="() => profileMenuOpen = false" class="absolute right-0 top-full mt-1 w-52 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50">
                                        <div class="px-4 py-2.5 border-b border-gray-50">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ user.name }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                                        </div>
                                        <Link href="/profile" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition-colors" @click="profileMenuOpen=false">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            My Profile
                                        </Link>
                                        <Link href="/orders" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition-colors" @click="profileMenuOpen=false">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            My Orders
                                        </Link>
                                        <Link href="/wishlist" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition-colors" @click="profileMenuOpen=false">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                            Wishlist
                                        </Link>
                                        <hr class="my-1.5 border-gray-100"/>
                                        <Link href="/logout" method="post" as="button" class="flex items-center gap-2.5 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Logout
                                        </Link>
                                    </div>
                                </Transition>
                            </div>

                            <!-- Cart Button -->
                            <Link
                                href="/cart"
                                class="hidden lg:flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2.5 rounded-full font-semibold text-sm transition-colors shadow-sm"
                            >
                                <span class="relative inline-flex">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                                    </svg>
                                    <span v-if="cartCount > 0" class="absolute -right-2 -top-2 min-w-[18px] rounded-full bg-red-500 px-1 py-0.5 text-center text-[10px] font-bold leading-none text-white">
                                        {{ cartCount > 99 ? '99+' : cartCount }}
                                    </span>
                                </span>
                                <span v-if="cartCount > 0" class="hidden sm:inline">{{ cartCount }} item{{ cartCount > 1 ? 's' : '' }}</span>
                                <span v-if="cartTotal > 0" class="hidden sm:inline font-bold">· ₹{{ cartTotal }}</span>
                                <span v-if="cartCount === 0" class="hidden sm:inline">Cart</span>
                            </Link>
                        </template>

                        <template v-else>
                            <Link :href="loginUrl" class="hidden lg:flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-600 hover:text-amber-700 rounded-xl hover:bg-amber-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Login
                            </Link>
                            <Link href="/register" class="hidden lg:flex bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-full font-semibold text-sm transition-colors shadow-sm">
                                Get Started
                            </Link>
                        </template>

                        <!-- Mobile search toggle -->
                        <button
                            @click="toggleMobileSearch"
                            class="lg:hidden flex items-center justify-center w-10 h-10 text-gray-800 bg-gray-100 hover:bg-amber-100 hover:text-amber-700 rounded-xl transition-colors border border-gray-200"
                            :aria-pressed="mobileSearchOpen"
                            aria-label="Open search"
                        >
                            <svg v-if="!mobileSearchOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-4.35-4.35"/>
                                <circle cx="11" cy="11" r="7" stroke-width="2.5" />
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        <!-- Mobile menu toggle -->
                        <button
                            @click="toggleMobileMenu"
                            class="lg:hidden flex items-center justify-center w-10 h-10 text-gray-800 bg-gray-100 hover:bg-amber-100 hover:text-amber-700 rounded-xl transition-colors border border-gray-200"
                            :aria-pressed="mobileMenuOpen"
                            aria-label="Open menu"
                        >
                            <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="mobileSearchOpen" class="lg:hidden border-t border-gray-100 bg-white shadow-lg px-4 py-3">
                        <form @submit.prevent="submitSearch" class="flex items-center gap-2">
                            <div class="flex-1 relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder='Search products...'
                                    class="w-full pl-10 pr-4 py-3 text-sm border border-gray-200 rounded-2xl focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none"
                                />
                            </div>
                            <button
                                type="submit"
                                class="px-4 py-3 bg-amber-600 text-white text-sm font-semibold rounded-2xl hover:bg-amber-700 transition-colors"
                            >
                                Go
                            </button>
                        </form>
                    </div>
                </Transition>
            </div>

            <!-- Mobile Menu -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="mobileMenuOpen" class="lg:hidden border-t border-gray-100 bg-white shadow-lg">
                    <!-- Mobile Location -->
                    <div class="px-4 pt-3">
                        <button @click="pincodeOpen = !pincodeOpen" class="flex items-center gap-2 w-full text-left bg-amber-50 rounded-xl px-4 py-3 border border-amber-100">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-sm text-gray-700 font-medium">{{ savedPincode ? `${savedPincode} · ${savedCity}` : 'Set delivery location' }}</span>
                        </button>
                    </div>

                    <div class="px-4 pt-2 pb-4 space-y-0.5">
                        <Link href="/" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50" @click="mobileMenuOpen=false">Home</Link>
                        <Link href="/products" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50" @click="mobileMenuOpen=false">Products</Link>
                        <Link href="/blog" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50" @click="mobileMenuOpen=false">Blog</Link>
                        <Link href="/about" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50" @click="mobileMenuOpen=false">About</Link>
                        <Link href="/contact" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50" @click="mobileMenuOpen=false">Contact</Link>
                        <hr class="my-2 border-gray-100"/>
                        <template v-if="user">
                            <Link href="/profile" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50" @click="mobileMenuOpen=false">Profile</Link>
                            <Link href="/orders" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50" @click="mobileMenuOpen=false">Orders</Link>
                            <Link href="/cart" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100" @click="mobileMenuOpen=false">
                                <span>My Cart</span>
                                <span v-if="cartCount > 0" class="bg-amber-600 text-white text-xs px-2 py-0.5 rounded-full">{{ cartCount }}</span>
                            </Link>
                            <Link href="/logout" method="post" as="button" class="flex items-center w-full px-3 py-2.5 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50" @click="mobileMenuOpen=false">Logout</Link>
                        </template>
                        <template v-else>
                            <Link :href="loginUrl" class="block text-center px-3 py-2.5 rounded-xl text-sm font-medium border border-gray-200 text-gray-700 hover:bg-gray-50" @click="mobileMenuOpen=false">Login</Link>
                            <Link href="/register" class="block text-center px-3 py-2.5 rounded-xl text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700" @click="mobileMenuOpen=false">Get Started</Link>
                        </template>
                    </div>
                </div>
            </Transition>
        </nav>
    </div>

    <!-- Pincode/Delivery Modal -->
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="pincodeOpen" class="fixed inset-0 z-[70] flex items-start justify-end" @click.self="pincodeOpen = false">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-sm mt-20 mr-4 lg:mr-8 overflow-hidden">
                <!-- Header -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <button @click="pincodeOpen = false" class="p-1 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <h3 class="text-base font-semibold text-gray-900">Select delivery location</h3>
                    </div>
                </div>

                <div class="p-5 space-y-4">
                    <!-- Current Location Button -->
                    <button class="flex items-center gap-3 w-full text-left border border-dashed border-gray-200 rounded-xl px-4 py-3.5 hover:border-amber-400 hover:bg-amber-50/50 transition-all group">
                        <div class="w-9 h-9 bg-amber-100 rounded-full flex items-center justify-center shrink-0 group-hover:bg-amber-200 transition-colors">
                            <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-amber-700">+ Use current location</span>
                    </button>

                    <!-- Pincode Check -->
                    <div>
                        <p class="text-sm font-medium text-gray-700 mb-2">Check Pincode Serviceability</p>
                        <div class="flex gap-2">
                            <div class="flex-1 relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <input
                                    v-model="pincode"
                                    type="text"
                                    maxlength="6"
                                    inputmode="numeric"
                                    placeholder="Enter 6-digit Pincode"
                                    class="w-full pl-9 pr-3 py-3 text-sm border border-gray-200 rounded-xl focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none"
                                    @keyup.enter="checkPincode"
                                />
                            </div>
                            <button
                                @click="checkPincode"
                                :disabled="pincodeChecking"
                                class="px-5 py-3 bg-amber-600 text-white text-sm font-semibold rounded-xl hover:bg-amber-700 transition-colors disabled:opacity-60"
                            >
                                <span v-if="pincodeChecking">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                </span>
                                <span v-else>Check</span>
                            </button>
                        </div>

                        <p v-if="pincodeError" class="mt-2 text-xs text-red-600">{{ pincodeError }}</p>

                        <div v-if="pincodeResult?.available" class="mt-3 flex items-start gap-2 bg-green-50 border border-green-200 rounded-xl p-3">
                            <svg class="w-5 h-5 text-green-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-green-800">Delivery available to {{ pincodeResult.city }}!</p>
                                <p class="text-xs text-green-700 mt-0.5">Location saved. Tap outside to continue.</p>
                            </div>
                        </div>
                        <div v-if="pincodeResult && !pincodeResult.available" class="mt-3 flex items-start gap-2 bg-red-50 border border-red-200 rounded-xl p-3">
                            <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <p class="text-sm text-red-700">Sorry, delivery not available at this pincode yet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
