<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import SellerLayout from '@/Layouts/SellerLayout.vue';

const form = ref({
    name: '',
    slug: '',
    description: '',
    price: '',
    sale_price: '',
    stock: '',
    sku: '',
    category_id: '',
    is_active: true,
    images: [],
});
const categories = ref([]);
const errors = ref({});
const saving = ref(false);
const imagePreview = ref([]);

function slugify(v) {
    return v.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}
function onNameInput() {
    form.value.slug = slugify(form.value.name);
}

function handleImages(e) {
    const files = Array.from(e.target.files);
    imagePreview.value = files.map(f => URL.createObjectURL(f));
    form.value.images = files;
}

async function submit() {
    saving.value = true;
    errors.value = {};
    try {
        const fd = new FormData();
        Object.entries(form.value).forEach(([k, v]) => {
            if (k === 'images') {
                v.forEach(img => fd.append('images[]', img));
            } else {
                fd.append(k, v === true ? 1 : v === false ? 0 : (v ?? ''));
            }
        });
        await window.axios.post('/api/v1/seller/products', fd, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        router.visit('/seller/products');
    } catch (e) {
        if (e.response?.status === 422) errors.value = e.response.data.errors ?? {};
        else alert('Failed to create product.');
    } finally {
        saving.value = false;
    }
}

window.axios.get('/api/v1/admin/categories').then(res => {
    categories.value = res.data.data ?? res.data ?? [];
}).catch(() => {});
</script>

<template>
    <Head title="Add Product | Seller" />
    <SellerLayout>
        <div class="max-w-2xl">
            <div class="flex items-center gap-3 mb-6">
                <a href="/seller/products" class="text-gray-500 hover:text-gray-700 text-sm">← Products</a>
                <h1 class="text-2xl font-bold text-gray-900">Add New Product</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Basic Info</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                            <input v-model="form.name" @input="onNameInput" type="text" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                            <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name[0] }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                            <input v-model="form.slug" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea v-model="form.description" rows="4"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price (₹) *</label>
                            <input v-model="form.price" type="number" min="0" step="0.01" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                            <p v-if="errors.price" class="text-red-500 text-xs mt-1">{{ errors.price[0] }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sale Price (₹)</label>
                            <input v-model="form.sale_price" type="number" min="0" step="0.01"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stock *</label>
                            <input v-model="form.stock" type="number" min="0" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                            <p v-if="errors.stock" class="text-red-500 text-xs mt-1">{{ errors.stock[0] }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                            <input v-model="form.sku" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select v-model="form.category_id"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-amber-400">
                                <option value="">— Select —</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2 pt-5">
                            <input type="checkbox" v-model="form.is_active" id="is_active" class="w-4 h-4 accent-amber-500" />
                            <label for="is_active" class="text-sm font-medium text-gray-700">Active (visible to customers)</label>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-5 space-y-3">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Images</h2>
                    <label class="block">
                        <span class="sr-only">Choose images</span>
                        <input type="file" multiple accept="image/*" @change="handleImages"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100" />
                    </label>
                    <div v-if="imagePreview.length" class="flex flex-wrap gap-3">
                        <img v-for="(src, i) in imagePreview" :key="i" :src="src"
                            class="w-20 h-20 object-cover rounded-lg border border-gray-200" />
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="/seller/products" class="px-5 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm hover:bg-gray-200">Cancel</a>
                    <button type="submit" :disabled="saving"
                        class="px-5 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium disabled:opacity-50">
                        {{ saving ? 'Saving…' : 'Create Product' }}
                    </button>
                </div>
            </form>
        </div>
    </SellerLayout>
</template>
