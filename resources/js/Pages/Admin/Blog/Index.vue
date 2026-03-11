<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const posts = ref([]);
const meta = ref({});
const loading = ref(true);
const page = ref(1);
const deleting = ref(null);

async function load() {
    loading.value = true;
    try {
        const res = await window.axios.get('/api/v1/admin/blog', { params: { page: page.value } });
        const d = res.data.data;
        posts.value = d.data ?? d;
        meta.value = d.meta ?? {};
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function deletePost(post) {
    if (!confirm(`Delete "${post.title}"?`)) return;
    deleting.value = post.id;
    try {
        await window.axios.delete(`/api/v1/admin/blog/${post.id}`);
        posts.value = posts.value.filter(p => p.id !== post.id);
    } catch (e) {
        alert('Could not delete post.');
    } finally {
        deleting.value = null;
    }
}

function formatDate(d) {
    return new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}

onMounted(load);
</script>

<template>
    <Head title="Blog | Admin" />
    <AdminLayout>
        <div>
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-white">Blog Posts</h1>
                <a href="/admin/blog/create" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    + New Post
                </a>
            </div>

            <div v-if="loading" class="space-y-2">
                <div v-for="i in 5" :key="i" class="bg-gray-800 rounded-xl h-14 animate-pulse"></div>
            </div>
            <div v-else class="bg-gray-800 rounded-xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Title</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3 hidden md:table-cell">Category</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Status</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3 hidden lg:table-cell">Published</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="post in posts" :key="post.id" class="border-b border-gray-700/50 hover:bg-gray-700/30">
                            <td class="px-5 py-3.5 text-white font-medium max-w-xs truncate">{{ post.title }}</td>
                            <td class="px-5 py-3.5 text-gray-400 hidden md:table-cell">{{ post.category }}</td>
                            <td class="px-5 py-3.5">
                                <span :class="post.is_published ? 'bg-green-900/50 text-green-300' : 'bg-yellow-900/50 text-yellow-300'"
                                    class="px-2.5 py-0.5 rounded-full text-xs font-medium">
                                    {{ post.is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-500 text-xs hidden lg:table-cell">
                                {{ post.published_at ? formatDate(post.published_at) : '—' }}
                            </td>
                            <td class="px-5 py-3.5 flex items-center gap-3">
                                <a :href="`/admin/blog/${post.id}/edit`" class="text-xs text-blue-400 hover:text-blue-300 font-medium">Edit</a>
                                <button @click="deletePost(post)" :disabled="deleting === post.id"
                                    class="text-xs text-red-400 hover:text-red-300 font-medium disabled:opacity-50">
                                    {{ deleting === post.id ? '...' : 'Delete' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!posts.length">
                            <td colspan="5" class="text-center text-gray-500 py-8">No blog posts yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta.last_page > 1" class="flex justify-center gap-2 mt-4">
                <button v-for="p in meta.last_page" :key="p" @click="page = p; load()"
                    :class="p === meta.current_page ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'"
                    class="w-9 h-9 rounded-lg text-sm font-medium transition-colors">{{ p }}</button>
            </div>
        </div>
    </AdminLayout>
</template>
