<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const mobileNavOpen = ref(false);
const navExpanded = ref(true); // Matches infobip style default expanded on desktop

const windowWidth = ref(1024);

const updateWidth = () => {
    windowWidth.value = window.innerWidth;
};

onMounted(() => {
    updateWidth();
    window.addEventListener('resize', updateWidth);
});

onUnmounted(() => {
    window.removeEventListener('resize', updateWidth);
});

// A computed property to force the expanded view on mobile regardless of navExpanded toggle
const isExpanded = computed(() => {
    if (typeof window !== 'undefined' && windowWidth.value < 1024) {
        return true;
    }
    return navExpanded.value;
});

const navItems = [
    { label: 'Dashboard',         href: '/admin/dashboard',          icon: 'home' },
    { label: 'Orders',            href: '/admin/orders',             icon: 'guide' },
    { label: 'Users',             href: '/admin/users',              icon: 'channels' },
    { label: 'Sellers',           href: '/admin/sellers',            icon: 'developer' },
    { label: 'Delivery Partners', href: '/admin/delivery-partners',  icon: 'broadcast' },
    { label: 'Categories',        href: '/admin/categories',         icon: 'moments' },
    { label: 'Coupons',           href: '/admin/coupons',            icon: 'conversations' },
    { label: 'Blog',              href: '/admin/blog',               icon: 'notifications' },
];

function isActive(href) {
    return page.url === href || page.url.startsWith(href + '/');
}

function toggleNav() {
    navExpanded.value = !navExpanded.value;
}

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="flex h-[100dvh] bg-[#e9edf3] text-[14px] text-gray-900 font-sans overflow-hidden">
        
        <!-- Mobile Overlay -->
        <div v-if="mobileNavOpen" class="fixed inset-0 bg-[#071220]/40 z-[60] lg:hidden backdrop-blur-sm transition-opacity" @click="mobileNavOpen = false"></div>

        <!-- Sidebar -->
        <aside 
            :class="[
                mobileNavOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0',
                isExpanded ? 'w-[280px] lg:w-[250px]' : 'lg:w-[60px]',
            ]"
            style="transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1); will-change: transform, width;"
            class="fixed lg:static inset-y-0 left-0 z-[70] flex flex-col bg-white border-r border-[#d9dfe8] h-full"
        >
            <!-- Top brand rail -->
            <div class="flex items-center h-[56px] border-b border-[#dfe4eb] flex-shrink-0 relative px-4 lg:px-0">
                <!-- Inner wrap to align nicely -->
                <div class="flex items-center w-full" :class="isExpanded ? 'lg:px-4' : 'lg:justify-center'">
                    <Link href="/admin/dashboard" class="flex-shrink-0 flex items-center justify-center w-[44px] h-[28px]" aria-label="Snackzar admin dashboard">
                        <img src="/images/logo/snackzar%20logo.png" alt="Snackzar" class="max-h-[28px] w-auto object-contain" />
                    </Link>
                    
                    <!-- Expanded view brand name -->
                    <div v-if="isExpanded" class="flex-1 whitespace-nowrap overflow-hidden pl-3">
                        <span class="font-bold text-[#142033] tracking-widest text-[13px] uppercase">SNACKZAR</span>
                    </div>
                </div>

                <!-- Collapse Toggle (Desktop only) -->
                <div class="absolute -right-[12px] top-[24px] z-50 hidden lg:flex">
                    <button 
                        @click="toggleNav" 
                        class="bg-white border border-[#d9dfe8] text-[#7c889d] hover:text-[#253954] hover:shadow-md rounded-full w-6 h-6 flex items-center justify-center shadow-sm cursor-pointer transition-all"
                    >
                        <svg class="w-3.5 h-3.5" :class="!isExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto overflow-x-hidden flex flex-col pt-4 pb-2" :class="isExpanded ? 'px-3 gap-1' : 'px-2 gap-2 items-center'">
                <Link 
                    v-for="item in navItems" :key="item.href" :href="item.href"
                    @click="mobileNavOpen = false"
                    class="flex items-center relative group transition-all duration-200 cursor-pointer w-full focus:outline-none focus:ring-2 focus:ring-[#1666ff]/20"
                    :class="[
                        isExpanded ? 'px-3 py-2.5 rounded-lg' : 'justify-center w-10 h-10 rounded-[10px]',
                        isActive(item.href) && isExpanded ? 'bg-[#edf1f6] text-[#1a64f0]' : '',
                        isActive(item.href) && !isExpanded ? 'bg-[#eef3fb] text-[#1666ff]' : '',
                        !isActive(item.href) ? 'text-[#35465e] hover:bg-[#edf1f6] hover:text-[#1a64f0]' : ''
                    ]"
                    :title="!isExpanded ? item.label : ''"
                >
                    <!-- Expand Active Indicator -->
                    <div v-if="isActive(item.href) && isExpanded" class="absolute left-[-12px] top-1/2 -translate-y-1/2 h-[60%] w-0.5 bg-[#ff6c18] rounded-r-md"></div>

                    <!-- Icons -->
                    <svg class="flex-shrink-0 transition-colors" :class="[isExpanded ? 'w-[18px] h-[18px] mr-3 text-[#8392a7] group-hover:text-[#1a64f0]' : 'w-5 h-5 text-[#6a778c] group-hover:text-[#1666ff]',  isActive(item.href) && !isExpanded ? 'text-[#1666ff]' : '', isActive(item.href) && isExpanded ? 'text-[#1a64f0]' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="item.icon === 'home'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        <path v-else-if="item.icon === 'guide'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        <path v-else-if="item.icon === 'channels'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        <path v-else-if="item.icon === 'developer'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        <path v-else-if="item.icon === 'broadcast'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        <path v-else-if="item.icon === 'moments'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        <path v-else-if="item.icon === 'conversations'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        <path v-else-if="item.icon === 'notifications'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>

                    <!-- Text area -->
                    <span v-if="isExpanded" class="font-medium text-[13.5px] whitespace-nowrap overflow-hidden text-ellipsis">
                        {{ item.label }}
                    </span>
                </Link>
            </nav>

            <!-- Bottom Profile & Status -->
            <div class="flex-shrink-0 border-t border-[#e4e9f0]" :class="isExpanded ? 'p-4' : 'p-3'">
                <!-- Expanded Trial Card -->
                <div v-if="isExpanded" class="p-3 bg-[#f8f9fc] border border-[#eef0f4] rounded-[10px] mb-4">
                    <p class="text-[12px] font-bold text-[#142033] mb-0.5">Admin Account</p>
                    <p class="text-[11px] text-[#6a778c] leading-[1.4] mb-2.5">You have full access to manage store tools.</p>
                    <a href="/" target="_blank" class="text-[12px] text-[#1666ff] font-semibold hover:text-[#0052cc] hover:underline transition-colors w-full tracking-tight">View live store</a>
                </div>

                <!-- Account section expanded -->
                <div v-if="isExpanded" class="flex items-center w-full gap-3 group">
                    <div class="w-8 h-8 rounded-full bg-[#1b2b41] text-white flex items-center justify-center text-[12px] font-bold shadow-inner">
                        {{ user?.name?.charAt(0)?.toUpperCase() || 'S' }}
                    </div>
                    <div class="flex-1 min-w-0 flex flex-col justify-center">
                        <p class="text-[13px] font-semibold text-[#142033] truncate leading-tight">{{ user?.name || 'Super Admin' }}</p>
                    </div>
                </div>

                <!-- Collapsed Version log-out explicit action icon button -->
                <div v-if="!isExpanded" class="flex flex-col items-center gap-3">
                    <button @click="logout" class="w-10 h-10 rounded-full border border-[#d6dee8] text-[#4b5971] flex items-center justify-center font-bold text-[13px] bg-[#fdfdfd] hover:bg-[#ffe5e5] hover:text-[#dc2626] hover:border-[#fca5a5] cursor-pointer transition-all shadow-sm group">
                        <svg class="w-4 h-4 text-inherit" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content View -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden relative w-full lg:w-auto h-full">
            
            <!-- App Bar (Infobip style top header) -->
            <header class="flex-shrink-0 h-[56px] border-b border-[#d6dee8] bg-white flex items-center justify-between px-3 md:px-5 z-20 w-full shadow-sm lg:shadow-none">
                <!-- Left (Mobile Hamburger + Title) -->
                <div class="flex items-center gap-2">
                    <button @click="mobileNavOpen = true" class="lg:hidden p-2 -ml-2 focus:outline-none text-[#5c6b80] hover:text-[#142033] rounded-[8px] active:bg-gray-100">
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <!-- Small Logo visible only on mobile when sidebar is closed -->
                    <Link href="/admin/dashboard" class="lg:hidden flex items-center justify-center w-[44px] h-[26px] shadow-sm ml-1 mr-2" aria-label="Snackzar admin dashboard">
                        <img src="/images/logo/snackzar%20logo.png" alt="Snackzar" class="max-h-[26px] w-auto object-contain" />
                    </Link>
                    <div class="hidden md:flex items-center gap-2.5">
                        <span class="text-[15px] font-semibold text-[#142033] select-none">Admin Panel</span>
                        <div class="h-4 w-px bg-[#d6dee8]"></div>
                        <div class="px-2 py-[2px] rounded border border-[#d6dee8] flex items-center gap-1.5 cursor-pointer hover:bg-gray-50 text-[12px] font-medium text-[#142033] shadow-sm select-none">
                            <svg class="w-3.5 h-3.5 text-[#6a778c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            Super Admin
                            <svg class="w-3.5 h-3.5 text-[#6a778c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Spacer to replace Search for formatting -->
                <div class="hidden lg:flex flex-1 px-8 max-w-[450px]"></div>

                <!-- Right Tools: Profile Menu -->
                <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                    <div class="items-center gap-[8px] border-r border-[#e5e9f0] pr-3 mr-1 hidden sm:flex">
                        <a href="/" target="_blank" class="px-3 py-[6px] text-[11px] font-bold text-[#1666ff] hover:bg-[#eef3fb] rounded-[6px] uppercase tracking-wider transition-colors active:scale-95">STORE</a>
                        <button @click="logout" class="px-3 py-[6px] border border-[#dee4ed] rounded-[6px] text-[11px] font-bold text-[#45556d] bg-white hover:border-[#b0bac9] hover:bg-[#f6f9fd] hover:text-[#dc2626] uppercase tracking-wider transition-colors active:scale-95 shadow-sm">
                            LOGOUT
                        </button>
                    </div>

                    <!-- Settings/Account icon (Mobile Logout shortcut and Profile) -->
                    <div class="relative group">
                        <button class="w-[34px] h-[34px] rounded-full border border-[#d6dee8] bg-white text-[#142033] flex items-center justify-center hover:bg-[#f6f9fd] transition-colors cursor-pointer shadow-sm active:scale-95 overflow-hidden p-0">
                             <img v-if="user?.avatar" :src="user.avatar" class="w-full h-full object-cover" />
                             <span v-else class="text-[13px] font-bold text-[#1a64f0]">{{ user?.name?.charAt(0)?.toUpperCase() || 'S' }}</span>
                        </button>
                        
                        <!-- Mobile dropdown pseudo-element for easier interaction on small screens -->
                        <div class="absolute right-0 top-[110%] w-[180px] bg-white border border-[#e4e9f0] rounded-xl shadow-xl py-1 hidden group-focus-within:block lg:group-focus-within:hidden hover:block z-[80]">
                            <div class="px-4 py-2.5 border-b border-[#e4e9f0] mb-1">
                                <p class="text-[13px] font-semibold text-[#142033] line-clamp-1">{{ user?.name || 'Super Admin' }}</p>
                                <p class="text-[11px] text-[#6a778c] line-clamp-1 mt-0.5">{{ user?.email || 'admin@snackzar.com' }}</p>
                            </div>
                            <a href="/" class="block px-4 py-2.5 text-[13px] text-[#2c3f5a] hover:bg-[#f4f6f9] transition-colors">View Live Store</a>
                            <button @click="logout" class="w-full text-left px-4 py-2.5 text-[13px] font-medium text-[#e02b2b] hover:bg-[#fff0f0] transition-colors">Sign out securely</button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Inner Scrollable Application Content -->
            <div class="flex-1 overflow-x-hidden overflow-y-auto px-4 py-6 sm:px-6 lg:px-8 lg:py-8 bg-[#f5f7fa] scroll-smooth overscroll-y-contain pb-[100px] lg:pb-8">
                <div class="max-w-[1280px] w-full mx-auto">
                    
                    <!-- Flash -->
                    <div v-if="$page.props.flash?.success" class="mb-5 bg-[#ebfff2] border border-[#b8e8ca] text-[#1f7a48] px-4 py-3.5 rounded-[10px] text-[13px] font-semibold shadow-sm flex items-center gap-2.5 animate-fade-in-down">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        {{ $page.props.flash.success }}
                    </div>
                    <div v-if="$page.props.flash?.error" class="mb-5 bg-[#fff0f0] border border-[#efc5c5] text-[#ab3a3a] px-4 py-3.5 rounded-[10px] text-[13px] font-semibold shadow-sm flex items-center gap-2.5 animate-fade-in-down">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                        {{ $page.props.flash.error }}
                    </div>
                    
                    <!-- Legacy Wrapping -->
                    <div class="legacy-content-wrapper min-h-full">
                        <slot />
                    </div>
                </div>
            </div>
            
        </main>
    </div>
</template>

<style>
/* Utilities for Mobile-First App Feel */
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down {
    animation: fadeInDown 0.3s ease-out forwards;
}

/* Hide scrollbar for nav but allow scrolling */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Base Overrides for Legacy UI Grid Boxes to match new theme */
.legacy-content-wrapper .bg-gray-800,
.legacy-content-wrapper .bg-gray-900 {
    background-color: #ffffff !important;
    border: 1px solid #d9dfe8 !important;
    box-shadow: 0 4px 16px rgba(10, 20, 36, 0.03) !important;
    border-radius: 14px !important;
}

/* Form fields background matching */
.legacy-content-wrapper * {
    border-color: #e4e9f0;
}

.legacy-content-wrapper .text-white {
    color: #142033 !important;
}
.legacy-content-wrapper .text-gray-400,
.legacy-content-wrapper .text-gray-500 {
    color: #6a778c !important;
}
.legacy-content-wrapper .text-gray-300 {
    color: #142033 !important;
}

/* Action Gradients / Primary Colors for Metric Cards */
.legacy-content-wrapper .bg-blue-600 { background-color: #1a64f0 !important; color: white !important; }
.legacy-content-wrapper .bg-purple-600 { background-color: #8b5cf6 !important; color: white !important; }
.legacy-content-wrapper .bg-amber-500,
.legacy-content-wrapper .text-amber-400 { background-color: #f59e0b !important; color: white !important; }
.legacy-content-wrapper .bg-green-600,
.legacy-content-wrapper .text-green-400 { background-color: #10b981 !important; color: white !important; }
.legacy-content-wrapper p.text-green-400 { color: #10b981 !important; background-color: transparent !important; font-weight: 600 !important; }
.legacy-content-wrapper p.text-amber-400 { color: #f59e0b !important; background-color: transparent !important; font-weight: 600 !important; }

/* Grid tweaks for mobile layout on older dashboard boxes */
@media (max-width: 640px) {
    .legacy-content-wrapper .grid-cols-2 {
        grid-template-columns: repeat(2, minmax(130px, 1fr)) !important;
        gap: 0.75rem !important;
    }
    
    .legacy-content-wrapper .lg\:grid-cols-4,
    .legacy-content-wrapper .lg\:grid-cols-3 {
        gap: 0.75rem !important;
    }

    /* Force tables inside overflow scroll wrappers to be easily swiped */
    .legacy-content-wrapper .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
        margin-left: -1rem;
        margin-right: -1rem;
        padding-left: 1rem;
        padding-right: 1rem;
        width: auto;
    }

    /* Make text scale nicely */
    .legacy-content-wrapper .text-2xl {
        font-size: 1.25rem !important;
    }
}

/* Links used as Action Cards on Dashboard */
.legacy-content-wrapper a.bg-gray-800 {
    background-color: #ffffff !important;
    transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
    display: flex; 
    flex-direction: column;
    justify-content: center;
    border: 1px solid #d9dfe8;
    align-items: center;
}
.legacy-content-wrapper a.bg-gray-800:active {
    transform: scale(0.97);
}
@media (hover: hover) {
    .legacy-content-wrapper a.bg-gray-800:hover {
        background-color: #f6f9fd !important;
        border-color: #bdc8d9 !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(10, 20, 36, 0.06) !important;
    }
}

/* Title text */
.legacy-content-wrapper h1.text-white {
    color: #142033 !important;
    font-size: clamp(1.4rem, 4vw, 1.6rem) !important;
    font-weight: 700 !important;
    margin-bottom: 1.2rem !important;
    letter-spacing: -0.01em;
}

/* Ensure modlas fit inside the browser height in mobile */
.legacy-content-wrapper .fixed.inset-0 > div {
    background: #ffffff !important;
    border: 1px solid #d9dfe8 !important;
    border-radius: 12px !important;
    max-height: 90vh;
    overflow-y: auto;
    width: 90% !important;
    max-width: 500px !important;
}

/* Buttons inside the legacy wrappers */
.legacy-content-wrapper button.bg-blue-600 {
    border-radius: 8px !important;
    font-weight: 600 !important;
    padding-top: 0.6rem !important;
    padding-bottom: 0.6rem !important;
}

</style>
