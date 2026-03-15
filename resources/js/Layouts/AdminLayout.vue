<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const mobileNavOpen = ref(false);
const navExpanded = ref(true); // matches infobip style default expanded

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
    <div class="flex h-screen bg-[#e9edf3] text-gray-900 font-sans overflow-hidden">
        
        <!-- Mobile Overlay -->
        <div v-if="mobileNavOpen" class="fixed inset-0 bg-[#071220]/30 z-[60] lg:hidden" @click="mobileNavOpen = false"></div>

        <!-- Sidebar -->
        <aside 
            :class="[
                mobileNavOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                navExpanded ? 'w-[250px]' : 'w-[56px]',
            ]"
            style="transition: width 0.22s ease, transform 0.22s ease;"
            class="fixed lg:static inset-y-0 left-0 z-[70] flex flex-col bg-white border-r border-[#d9dfe8] h-full"
        >
            <!-- Top brand rail -->
            <div class="flex items-center h-[56px] border-b border-[#dfe4eb] flex-shrink-0 relative overflow-visible px-4">
                <!-- Inner wrap to align nicely -->
                <div class="flex items-center w-full" :class="!navExpanded ? 'justify-center' : ''">
                    <Link href="/admin/dashboard" class="flex-shrink-0 flex items-center justify-center w-[28px] h-[28px] rounded-full bg-[#ff6c18] text-white">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="4" y="4" width="16" height="16" rx="4" stroke-width="2.5" />
                        </svg>
                    </Link>
                    
                    <!-- Expanded view brand name -->
                    <div v-if="navExpanded" class="flex-1 whitespace-nowrap overflow-hidden pl-3">
                        <span class="font-bold text-[#142033] tracking-widest text-[13px]">SNACKZAR</span>
                    </div>
                </div>

                <!-- Collapse Toggle -->
                <div class="absolute -right-[13px] top-[16px] z-50 hidden lg:flex">
                    <button 
                        @click="toggleNav" 
                        class="bg-white border border-[#d9dfe8] text-[#7c889d] hover:text-[#253954] rounded-full w-6 h-6 flex items-center justify-center shadow-sm cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" :class="!navExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto overflow-x-hidden flex flex-col pt-3" :class="navExpanded ? 'px-3 pb-3 gap-[2px]' : 'py-3 gap-2 px-1 text-center items-center'">
                <Link 
                    v-for="item in navItems" :key="item.href" :href="item.href"
                    class="flex items-center relative group transition-colors cursor-pointer"
                    :class="[
                        navExpanded ? 'px-3 py-[9px] rounded-[6px]' : 'justify-center w-10 h-10 rounded-[8px]',
                        isActive(item.href) && navExpanded ? 'bg-[#edf1f6] text-[#1a64f0]' : '',
                        isActive(item.href) && !navExpanded ? 'bg-[#eef3fb] text-[#1666ff]' : '',
                        !isActive(item.href) ? 'text-[#253954] hover:bg-[#edf1f6] hover:text-[#1a64f0]' : ''
                    ]"
                    :title="!navExpanded ? item.label : ''"
                >
                    <!-- Expand Active Indicator (Orange line on far left just like infobip if expanded) -->
                    <div v-if="isActive(item.href) && navExpanded" class="absolute left-[-12px] top-[15%] h-[70%] w-0.5 bg-[#ff6c18] rounded-r-md"></div>

                    <!-- Icons -->
                    <svg class="flex-shrink-0 transition-colors" :class="[navExpanded ? 'w-[18px] h-[18px] mr-3 text-[#8392a7] group-hover:text-[#1a64f0]' : 'w-[18px] h-[18px] text-[#6a778c] group-hover:text-[#1666ff]',  isActive(item.href) && !navExpanded ? 'text-[#1666ff]' : '', isActive(item.href) && navExpanded ? 'text-[#1a64f0]' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="item.icon === 'home'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 11l8-7 8 7M6 10.5V20h12v-9.5"/>
                        <path v-else-if="item.icon === 'guide'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z M8 10h8 M8 14h8"/>
                        <path v-else-if="item.icon === 'channels'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        <path v-else-if="item.icon === 'developer'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        <path v-else-if="item.icon === 'broadcast'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        <path v-else-if="item.icon === 'moments'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        <path v-else-if="item.icon === 'conversations'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        <path v-else-if="item.icon === 'notifications'" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                    </svg>

                    <!-- Text area -->
                    <span v-if="navExpanded" class="font-medium text-[13px] whitespace-nowrap">
                        {{ item.label }}
                    </span>
                </Link>
            </nav>

            <!-- Bottom Profile & Status (Collapsed vs Expanded) -->
            <div class="flex-shrink-0 flex flex-col" :class="navExpanded ? 'p-4' : 'pb-5 items-center gap-4'">
                <!-- Expanded Infobip style Trial Card -->
                <div v-if="navExpanded" class="p-3 bg-[#f4f6f9] border border-[#e4e9f0] rounded-lg mb-4">
                    <p class="text-[12px] font-bold text-[#2c3f5a] mb-0.5">Admin Account</p>
                    <p class="text-[11px] text-[#73839a] leading-tight mb-2">You have full access to manage store tools.</p>
                    <a href="/" target="_blank" class="text-[11px] text-[#0062ff] font-medium hover:underline">View live store</a>
                </div>

                <!-- Account -->
                <div v-if="navExpanded" class="flex items-center pt-2 border-t border-[#e4e9f0]">
                    <div class="w-7 h-7 rounded-full bg-[#1b2b41] text-white flex items-center justify-center text-[11px] font-bold mr-2">
                        {{ user?.name?.charAt(0)?.toUpperCase() || 'S' }}
                    </div>
                    <div class="flex-1 min-w-0 pr-2">
                        <p class="text-[13px] font-semibold text-[#142033] truncate">{{ user?.name || 'Administrator' }}</p>
                    </div>
                </div>

                <!-- Collapsed Version of user -->
                <template v-if="!navExpanded">
                    <div class="relative group">
                        <button class="w-[30px] h-[30px] rounded-full border border-[#c8d1df] text-[#6a778c] flex items-center justify-center font-bold text-[11px] bg-white hover:bg-[#f6f9fd] cursor-default">
                            {{ user?.name?.charAt(0)?.toUpperCase() || 'S' }}
                        </button>
                    </div>
                </template>
            </div>
        </aside>

        <!-- Main Content View -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
            
            <!-- App Bar (Infobip style top header) -->
            <header class="flex-shrink-0 h-[56px] border-b border-[#d6dee8] bg-white flex items-center justify-between px-3 md:px-5 z-10 w-full">
                <!-- Left (Title) -->
                <div class="flex items-center gap-4">
                    <button @click="mobileNavOpen = true" class="lg:hidden p-1.5 focus:outline-none text-[#5c6b80] hover:text-[#142033] rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16"/>
                        </svg>
                    </button>
                    <!-- Instead of page title, infobip just has 'Getting started as a Developer' dropping down -->
                    <div class="hidden md:flex items-center gap-2">
                        <span class="text-[16px] font-semibold text-[#142033]">Admin Panel</span>
                        <div class="px-2 py-[2px] rounded border border-[#d6dee8] flex items-center gap-1.5 cursor-pointer hover:bg-gray-50 text-[13px] font-medium text-[#142033]">
                            <svg class="w-3.5 h-3.5 text-[#6a778c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            Super Admin
                            <svg class="w-3 h-3 text-[#6a778c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Search (ctrl+K) -->
                <div class="hidden lg:flex items-center justify-end flex-1 max-w-[450px] mx-8">
                    <div class="relative w-full max-w-[320px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#8d9ab0]">
                            <svg class="h-[16px] w-[16px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 110-15 7.5 7.5 0 010 15z"/>
                            </svg>
                        </div>
                        <input type="text" class="block w-full pl-9 pr-14 py-[6px] border border-[#d6dee8] rounded-full leading-5 bg-white text-[#142033] placeholder-[#8d9ab0] focus:outline-none focus:ring-1 focus:ring-[#1666ff] focus:border-[#1666ff] text-[13px] hover:border-[#b0bac9] transition-colors" placeholder="Search anything">
                        <div class="absolute inset-y-0 right-0 pr-[3px] flex items-center pointer-events-none">
                            <span class="text-[#8d9ab0] text-[11px] font-medium border border-[#e5e9f0] px-2 py-0.5 rounded-[10px] bg-white">Ctrl + K</span>
                        </div>
                    </div>
                </div>

                <!-- Right Tools: PRICING, ADD FUNDS, Help, Profile -->
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-[6px] border-r border-[#e5e9f0] pr-3 mr-1">
                        <a href="/" target="_blank" class="px-3 py-[5px] text-[11px] font-bold text-[#1666ff] hover:bg-[#eef3fb] rounded-[4px] uppercase tracking-wide transition-colors">VIEW STORE</a>
                        <button @click="logout" class="px-3 py-[5px] border border-[#dee4ed] rounded-[4px] text-[11px] font-bold text-[#1666ff] bg-white hover:bg-[#f6f9fd] uppercase tracking-wide transition-colors">
                            SIGN OUT
                        </button>
                    </div>

                    <!-- Help -->
                    <button class="w-[32px] h-[32px] rounded-full border border-[#d6dee8] bg-white text-[#5c6b80] flex items-center justify-center hover:bg-[#f6f9fd] transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                    <!-- Settings/Account icon -->
                    <button class="w-[32px] h-[32px] rounded-full bg-[#ff7b30] text-white flex items-center justify-center hover:bg-[#e66012] transition-colors overflow-hidden border-2 border-white cursor-pointer relative shadow-sm">
                         <span class="text-[12px] font-bold absolute">{{ user?.name?.charAt(0)?.toUpperCase() || 'S' }}</span>
                    </button>
                </div>
            </header>

            <!-- Inner Scrollable Application Content -->
            <div class="flex-1 overflow-y-auto px-4 py-8 sm:px-8 lg:px-10 bg-[#f9fafb]">
                <div class="max-w-[1280px] w-full mx-auto pb-12">
                    
                    <!-- Flash -->
                    <div v-if="$page.props.flash?.success" class="mb-6 bg-[#ebfff2] border border-[#b8e8ca] text-[#1f7a48] px-4 py-3.5 rounded-[8px] text-[13px] font-medium shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        {{ $page.props.flash.success }}
                    </div>
                    <div v-if="$page.props.flash?.error" class="mb-6 bg-[#fff0f0] border border-[#efc5c5] text-[#ab3a3a] px-4 py-3.5 rounded-[8px] text-[13px] font-medium shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                        {{ $page.props.flash.error }}
                    </div>
                    
                    <!-- Wrapping legacy dark mode components and mapping them nicely -->
                    <div class="legacy-content-wrapper">
                        <slot />
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style>
/* CSS overrides to make the dark-mode legacy pages ("all my content as previous") fit seamlessly into the new Infobip white theme without altering their source code structure. */
.legacy-content-wrapper .bg-gray-800,
.legacy-content-wrapper .bg-gray-900 {
    background-color: #ffffff !important;
    border: 1px solid #d9dfe8 !important;
    box-shadow: 0 8px 24px rgba(10, 20, 36, 0.03) !important;
    border-radius: 12px !important;
}

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

/* Metric card specific overrides from the actual Dashboard.vue you had */
.legacy-content-wrapper .bg-blue-600 { background-color: #1666ff !important; color: white !important; }
.legacy-content-wrapper .bg-purple-600 { background-color: #8942ff !important; color: white !important; }
.legacy-content-wrapper .bg-amber-500,
.legacy-content-wrapper .text-amber-400 { background-color: #f77f00 !important; color: white !important; }
.legacy-content-wrapper .bg-green-600,
.legacy-content-wrapper .text-green-400 { background-color: #0cb551 !important; color: white !important; }

/* Keep specific text colors inside badges transparent bg */
.legacy-content-wrapper p.text-green-400 { color: #0cb551 !important; background-color: transparent !important; }
.legacy-content-wrapper p.text-amber-400 { color: #f77f00 !important; background-color: transparent !important; }

/* Links used as buttons in the Legacy code */
.legacy-content-wrapper a.bg-gray-800 {
    background-color: #ffffff !important;
    transition: all 0.2s;
    display: block; /* some links in the grid */
}
.legacy-content-wrapper a.bg-gray-800:hover {
    background-color: #f6f9fd !important;
    border-color: #c0cddf !important;
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(10, 20, 36, 0.06) !important;
}

/* Page titles in legacy content */
.legacy-content-wrapper h1.text-white {
    color: #142033 !important;
    font-size: 1.6rem !important;
    font-weight: 700 !important;
    margin-bottom: 1.5rem !important;
}

/* Tables inside cards */
.legacy-content-wrapper table th {
    background-color: #f4f6f9 !important;
    color: #4b5971 !important;
    font-size: 0.75rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    border-bottom: 1px solid #d9dfe8 !important;
}
.legacy-content-wrapper table td {
    color: #142033 !important;
    border-bottom: 1px solid #f0f3f7 !important;
}
</style>
