<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

const form = ref({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const submitted = ref(false);
const submitting = ref(false);

const handleSubmit = async () => {
    submitting.value = true;
    // In production, this would send to server
    // For now, just simulate a submission
    setTimeout(() => {
        submitting.value = false;
        submitted.value = true;
        form.value = { name: '', email: '', subject: '', message: '' };
    }, 1000);
};

const contactInfo = [
    {
        icon: 'email',
        title: 'Email Us',
        detail: 'support@snackzar.com',
        sub: 'We reply within 24 hours',
    },
    {
        icon: 'phone',
        title: 'Call Us',
        detail: '+91 98765 43210',
        sub: 'Mon-Sat, 9AM-6PM IST',
    },
    {
        icon: 'location',
        title: 'Visit Us',
        detail: 'Bihar, India',
        sub: 'Headquarters',
    },
];
</script>

<template>
    <AppLayout title="Contact Us">
        <!-- Hero -->
        <section class="bg-gradient-to-br from-amber-800 to-amber-950 py-16 lg:py-24">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl lg:text-5xl font-bold text-white mb-4">Get In Touch</h1>
                <p class="text-lg text-amber-200 max-w-xl mx-auto">
                    Have a question, feedback, or want to become a seller? We'd love to hear from you.
                </p>
            </div>
        </section>

        <section class="py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-3 gap-10">
                    <!-- Contact Info Cards -->
                    <div class="space-y-6">
                        <div v-for="info in contactInfo" :key="info.title" class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center shrink-0">
                                    <svg v-if="info.icon === 'email'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <svg v-if="info.icon === 'phone'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <svg v-if="info.icon === 'location'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">{{ info.title }}</h3>
                                    <p class="text-amber-700 font-medium text-sm">{{ info.detail }}</p>
                                    <p class="text-gray-500 text-xs mt-0.5">{{ info.sub }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Social Links -->
                        <div class="bg-amber-50 rounded-2xl border border-amber-100 p-6">
                            <h3 class="font-semibold text-gray-900 mb-3">Follow Us</h3>
                            <div class="flex gap-3">
                                <a href="#" class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-gray-600 hover:text-amber-600 hover:border-amber-200 transition-all">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                                <a href="#" class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-gray-600 hover:text-amber-600 hover:border-amber-200 transition-all">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                </a>
                                <a href="#" class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-gray-600 hover:text-amber-600 hover:border-amber-200 transition-all">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-2xl border border-gray-100 p-8 shadow-sm">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">Send us a Message</h2>

                            <!-- Success Message -->
                            <div v-if="submitted" class="bg-green-50 border border-green-200 text-green-700 rounded-xl p-4 mb-6">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="font-medium">Thank you! Your message has been sent. We'll get back to you soon.</span>
                                </div>
                            </div>

                            <form @submit.prevent="handleSubmit" class="space-y-5">
                                <div class="grid sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Your Name</label>
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            required
                                            placeholder="John Doe"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-amber-500 focus:ring-amber-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                                        <input
                                            v-model="form.email"
                                            type="email"
                                            required
                                            placeholder="you@example.com"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-amber-500 focus:ring-amber-500"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject</label>
                                    <input
                                        v-model="form.subject"
                                        type="text"
                                        required
                                        placeholder="How can we help?"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-amber-500 focus:ring-amber-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Message</label>
                                    <textarea
                                        v-model="form.message"
                                        rows="6"
                                        required
                                        placeholder="Tell us more about your query..."
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-amber-500 focus:ring-amber-500 resize-none"
                                    ></textarea>
                                </div>
                                <button
                                    type="submit"
                                    :disabled="submitting"
                                    class="bg-amber-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-amber-700 transition-all disabled:opacity-50 shadow-lg shadow-amber-600/25"
                                >
                                    {{ submitting ? 'Sending...' : 'Send Message' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
