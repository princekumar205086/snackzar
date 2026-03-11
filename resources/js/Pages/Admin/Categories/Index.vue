<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const categories = ref([]);
const loading = ref(true);
const saving = ref(false);
const deleting = ref(null);

const showForm = ref(false);
const editMode = ref(false);
const form = ref({ id: null, name: '', slug: '', description: '' });

async function load() {
    loading.value = true;
    try {
        const res = await window.axios.get('/api/v1/admin/categories');
        categories.value = res.data.data?.data ?? res.data.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

function openCreate() {
    editMode.value = false;
    form.value = { id: null, name: '', slug: '', description: '' };
    showForm.value = true;
}

function openEdit(cat) {
    editMode.value = true;
    form.value = { id: cat.id, name: cat.name, slug: cat.slug, description: cat.description ?? '' };
    showForm.value = true;
}

function autoSlug() {
    if (!editMode.value) {
        form.value.slug = form.value.name.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
    }
}

async function save() {
    saving.value = true;
    try {
        if (editMode.value) {
            await window.axios.put(`/api/v1/admin/categories/${form.value.id}`, form.value);
        } else {
            await window.axios.post('/api/v1/admin/categories', form.value);
        }
        showForm.value = false;
        await load();
    } catch (e) {
        alert(e.response?.data?.message ?? 'Save failed.');
    } finally {
        saving.value = false;
    }
}

async function destroy(cat) {
    if (!confirm(`Delete "${cat.name}"?`)) return;
    deleting.value = cat.id;
    try {
        await window.axios.delete(`/api/v1/admin/categories/${cat.id}`);
        categories.value = categories.value.filter(c => c.id !== cat.id);
    } catch (e) {
        alert('Could not delete category.');
    } finally {
        deleting.value = null;
    }
}

onMounted(load);
</script>

<template>
    <Head title="Categories | Admin" />
    <AdminLayout>
        <div>
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-white">Categories</h1>
                <button @click="openCreate" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    + New Category
                </button>
            </div>

            <!-- Form Modal -->
            <div v-if="showForm" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
                <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-md">
                    <h2 class="text-lg font-bold text-white mb-5">{{ editMode ? 'Edit' : 'New' }} Category</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Name</label>
                            <input v-model="form.name" @input="autoSlug" type="text"
                                class="w-full bg-gray-700 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:border-blue-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Slug</label>
                            <input v-model="form.slug" type="text"
                                class="w-full bg-gray-700 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:border-blue-500 outline-none" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Description</label>
                            <textarea v-model="form.description" rows="3"
                                class="w-full bg-gray-700 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:border-blue-500 outline-none resize-none"></textarea>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-5">
                        <button @click="showForm = false" class="flex-1 bg-gray-700 hover:bg-gray-600 text-gray-300 text-sm font-medium py-2 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button @click="save" :disabled="saving" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-lg transition-colors disabled:opacity-50">
                            {{ saving ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- List -->
            <div v-if="loading" class="space-y-2">
                <div v-for="i in 5" :key="i" class="bg-gray-800 rounded-xl h-14 animate-pulse"></div>
            </div>
            <div v-else class="bg-gray-800 rounded-xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Name</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3 hidden md:table-cell">Slug</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3 hidden lg:table-cell">Products</th>
                            <th class="text-left text-gray-400 font-medium px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="cat in categories" :key="cat.id" class="border-b border-gray-700/50 hover:bg-gray-700/30">
                            <td class="px-5 py-3.5 text-white font-medium">{{ cat.name }}</td>
                            <td class="px-5 py-3.5 text-gray-500 font-mono text-xs hidden md:table-cell">{{ cat.slug }}</td>
                            <td class="px-5 py-3.5 text-gray-400 hidden lg:table-cell">{{ cat.products_count ?? '—' }}</td>
                            <td class="px-5 py-3.5 flex items-center gap-3">
                                <button @click="openEdit(cat)" class="text-xs text-blue-400 hover:text-blue-300 font-medium">Edit</button>
                                <button @click="destroy(cat)" :disabled="deleting === cat.id"
                                    class="text-xs text-red-400 hover:text-red-300 font-medium disabled:opacity-50">
                                    {{ deleting === cat.id ? '...' : 'Delete' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!categories.length">
                            <td colspan="4" class="text-center text-gray-500 py-8">No categories.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
