<script setup>
import { ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ id: [String, Number] });
const form = ref(null);
const errors = ref({});
const loading = ref(true);
const saving = ref(false);

function slugify(v) {
    return v.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}
function onTitleInput() {
    const auto = slugify(form.value.title);
    form.value.slug = auto;
}

async function load() {
    try {
        const res = await window.axios.get(`/api/v1/admin/blog/${props.id}`);
        const d = res.data.data ?? res.data;
        form.value = {
            title: d.title ?? '',
            slug: d.slug ?? '',
            category: d.category ?? '',
            excerpt: d.excerpt ?? '',
            content: d.content ?? '',
            is_published: !!d.is_published,
            published_at: d.published_at ? d.published_at.slice(0, 16) : '',
            meta_title: d.meta_title ?? '',
            meta_description: d.meta_description ?? '',
        };
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function submit() {
    saving.value = true;
    errors.value = {};
    try {
        await window.axios.put(`/api/v1/admin/blog/${props.id}`, form.value);
        router.visit('/admin/blog');
    } catch (e) {
        if (e.response?.status === 422) errors.value = e.response.data.errors ?? {};
        else alert('Failed to update post.');
    } finally {
        saving.value = false;
    }
}

onMounted(load);
</script>

<template>
    <Head title="Edit Blog Post | Admin" />
    <AdminLayout>
        <div class="max-w-3xl">
            <div class="flex items-center gap-3 mb-6">
                <a href="/admin/blog" class="text-gray-400 hover:text-white text-sm">← Blog</a>
                <h1 class="text-2xl font-bold text-white">Edit Blog Post</h1>
            </div>

            <div v-if="loading" class="space-y-4">
                <div v-for="i in 6" :key="i" class="bg-gray-800 rounded-xl h-14 animate-pulse"></div>
            </div>

            <form v-else-if="form" @submit.prevent="submit" class="space-y-5">
                <div class="bg-gray-800 rounded-xl p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Title *</label>
                        <input v-model="form.title" @input="onTitleInput" type="text" required
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500" />
                        <p v-if="errors.title" class="text-red-400 text-xs mt-1">{{ errors.title[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Slug *</label>
                        <input v-model="form.slug" type="text" required
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500" />
                        <p v-if="errors.slug" class="text-red-400 text-xs mt-1">{{ errors.slug[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Category</label>
                        <input v-model="form.category" type="text"
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Excerpt</label>
                        <textarea v-model="form.excerpt" rows="2"
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Content *</label>
                        <textarea v-model="form.content" rows="12" required
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 font-mono"></textarea>
                        <p v-if="errors.content" class="text-red-400 text-xs mt-1">{{ errors.content[0] }}</p>
                    </div>
                </div>

                <div class="bg-gray-800 rounded-xl p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wide">SEO</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Meta Title</label>
                        <input v-model="form.meta_title" type="text"
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Meta Description</label>
                        <textarea v-model="form.meta_description" rows="2"
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500"></textarea>
                    </div>
                </div>

                <div class="bg-gray-800 rounded-xl p-5 flex flex-wrap gap-6 items-center">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="form.is_published" class="w-4 h-4 rounded accent-blue-500" />
                        <span class="text-sm text-gray-300">Published</span>
                    </label>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Publish date</label>
                        <input v-model="form.published_at" type="datetime-local"
                            class="bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500" />
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="/admin/blog" class="px-5 py-2 rounded-lg bg-gray-700 text-gray-300 text-sm hover:bg-gray-600">Cancel</a>
                    <button type="submit" :disabled="saving"
                        class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium disabled:opacity-50">
                        {{ saving ? 'Saving…' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
