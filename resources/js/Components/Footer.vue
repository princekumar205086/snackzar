<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="bg-amber-950 text-amber-100 mt-auto border-t-[8px] border-amber-500 rounded-t-3xl sm:rounded-t-[3rem] overflow-hidden relative shadow-inner">
        <!-- Optional Decorative Element -->
        <div class="absolute inset-0 opacity-5 pointer-events-none" style="background-image: url('data:image/svg+xml,%3Csvg width=\'20\' height=\'20\' viewBox=\'0 0 20 20\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Ccircle cx=\'2\' cy=\'2\' r=\'2\' fill=\'%23ffffff\' fill-rule=\'evenodd\'/%3E%3C/svg%3E');"></div>
        
        <!-- Main Footer -->
        <div class="max-w-7xl mx-auto px-6 lg:px-8 pt-16 pb-12 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-12 lg:gap-8">
                
                <!-- Brand -->
                <div class="lg:col-span-4 flex flex-col items-center md:items-start text-center md:text-left">
                    <Link href="/" class="mb-6 inline-block hover:scale-105 transition-transform duration-300">
                        <img src="/images/logo/snackzar%20logo.png" alt="Snackzar Logo" class="h-14 sm:h-16 w-auto drop-shadow-lg filter brightness-0 invert" />
                    </Link>
                    <p class="text-amber-200/90 text-sm leading-relaxed mb-8 max-w-sm">
                        Premium Bihari snacks delivered fresh to your doorstep. Experience the authentic taste of Bihar's finest Makhana and traditional snacks with love.
                    </p>
                    <div class="flex gap-3 justify-center md:justify-start">
                        <a href="#" class="w-10 h-10 bg-amber-900/50 hover:bg-amber-600 rounded-full flex items-center justify-center transition-all duration-300 hover:-translate-y-1 shadow-sm ring-1 ring-amber-800/50" aria-label="Facebook">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-amber-900/50 hover:bg-amber-600 rounded-full flex items-center justify-center transition-all duration-300 hover:-translate-y-1 shadow-sm ring-1 ring-amber-800/50" aria-label="Instagram">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-amber-900/50 hover:bg-amber-600 rounded-full flex items-center justify-center transition-all duration-300 hover:-translate-y-1 shadow-sm ring-1 ring-amber-800/50" aria-label="Twitter">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="lg:col-span-2 text-center md:text-left">
                    <h3 class="text-white font-bold text-sm uppercase tracking-widest mb-6 border-b-2 border-amber-600/50 pb-2 inline-block md:block w-max md:w-full">Explore</h3>
                    <ul class="space-y-3">
                        <li><Link href="/products" class="text-amber-200/80 hover:text-white hover:translate-x-1 block transition-all duration-200 text-sm">All Products</Link></li>
                        <li><Link href="/products?featured=1" class="text-amber-200/80 hover:text-white hover:translate-x-1 block transition-all duration-200 text-sm">Featured Snacks</Link></li>
                        <li><Link href="/about" class="text-amber-200/80 hover:text-white hover:translate-x-1 block transition-all duration-200 text-sm">About Us</Link></li>
                        <li><Link href="/contact" class="text-amber-200/80 hover:text-white hover:translate-x-1 block transition-all duration-200 text-sm">Contact Us</Link></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div class="lg:col-span-3 text-center md:text-left">
                    <h3 class="text-white font-bold text-sm uppercase tracking-widest mb-6 border-b-2 border-amber-600/50 pb-2 inline-block md:block w-max md:w-full">Customer Care</h3>
                    <ul class="space-y-3">
                        <li><Link href="/profile" class="text-amber-200/80 hover:text-white hover:translate-x-1 block transition-all duration-200 text-sm">My Account</Link></li>
                        <li><Link href="/orders" class="text-amber-200/80 hover:text-white hover:translate-x-1 block transition-all duration-200 text-sm">Track Order</Link></li>
                        <li><a href="#" class="text-amber-200/80 hover:text-white hover:translate-x-1 block transition-all duration-200 text-sm">Shipping & Delivery</a></li>
                        <li><a href="#" class="text-amber-200/80 hover:text-white hover:translate-x-1 block transition-all duration-200 text-sm">Returns & Refunds</a></li>
                        <li><a href="#" class="text-amber-200/80 hover:text-white hover:translate-x-1 block transition-all duration-200 text-sm">FAQs & Support</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="lg:col-span-3 bg-amber-900/20 p-6 rounded-2xl border border-amber-800/30">
                    <h3 class="text-white font-bold text-sm uppercase tracking-widest mb-6">Contact Us</h3>
                    <ul class="space-y-5">
                        <li class="flex items-start gap-4">
                            <div class="bg-amber-800/50 p-2 rounded-lg shrink-0">
                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <span class="text-amber-100/90 text-sm leading-relaxed block mt-0.5">Patna, Bihar, India<br/><span class="text-xs text-amber-300">HQ Office</span></span>
                        </li>
                        <li class="flex items-center gap-4 group">
                            <div class="bg-amber-800/50 p-2 rounded-lg shrink-0 group-hover:bg-amber-600 transition-colors">
                                <svg class="w-5 h-5 text-amber-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <a href="mailto:support@snackzar.com" class="text-amber-100/90 text-sm hover:text-white transition-colors block leading-tight mt-0.5">support@snackzar.com</a>
                        </li>
                        <li class="flex items-center gap-4 group">
                            <div class="bg-amber-800/50 p-2 rounded-lg shrink-0 group-hover:bg-amber-600 transition-colors">
                                <svg class="w-5 h-5 text-amber-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <a href="tel:+919876543210" class="text-amber-100/90 text-sm hover:text-white transition-colors block leading-tight mt-0.5 font-medium">+91 98765 43210</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="bg-amber-950/80 border-t border-amber-900/60 backdrop-blur-sm relative z-10">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-5 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-amber-200/60 text-xs sm:text-sm font-medium">
                    &copy; 2024 Snackzar. All rights reserved. Built with ❤️ in Bihar.
                </p>
                <div class="flex flex-wrap justify-center items-center gap-x-6 gap-y-2">
                    <a href="#" class="text-amber-200/60 hover:text-white text-xs sm:text-sm font-medium transition-colors">Terms of Service</a>
                    <span class="text-amber-800/50 hidden sm:inline">•</span>
                    <a href="#" class="text-amber-200/60 hover:text-white text-xs sm:text-sm font-medium transition-colors">Privacy Policy</a>
                    <span class="text-amber-800/50 hidden sm:inline">•</span>
                    <a href="#" class="text-amber-200/60 hover:text-white text-xs sm:text-sm font-medium transition-colors">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>
</template>
