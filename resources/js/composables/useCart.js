import { ref, computed } from 'vue';

// Module-level reactive state (shared across all component instances)
const cartItems = ref([]);
const cartLoaded = ref(false);

export function useCart() {
    const cartProductIds = computed(() => new Set(cartItems.value.map(i => i.product_id)));
    const cartTotal = computed(() => cartItems.value.reduce((sum, i) => sum + (i.quantity * parseFloat(i.unit_price ?? 0)), 0));
    const cartCount = computed(() => cartItems.value.reduce((sum, i) => sum + i.quantity, 0));

    const getItemForProduct = (productId) => cartItems.value.find(i => i.product_id === productId);

    // Variant-aware lookup: if variantId is null/undefined, match items without a variant
    const getItemForProductVariant = (productId, variantId = null) => {
        return cartItems.value.find(i =>
            i.product_id === productId &&
            (variantId == null ? !i.product_variant_id : i.product_variant_id == variantId)
        );
    };

    async function loadCart() {
        try {
            const res = await window.axios.get('/api/v1/user/cart');
            cartItems.value = res.data?.data?.cart?.items ?? [];
            cartLoaded.value = true;
        } catch {
            cartItems.value = [];
        }
    }

    async function addToCart(productId, variantId = null, quantity = 1) {
        const res = await window.axios.post('/api/v1/user/cart', {
            product_id: productId,
            product_variant_id: variantId,
            quantity,
        });
        cartItems.value = res.data?.data?.cart?.items ?? [];
        return res.data;
    }

    async function updateQuantity(itemId, quantity) {
        if (quantity <= 0) return removeFromCart(itemId);
        const res = await window.axios.put(`/api/v1/user/cart/${itemId}`, { quantity });
        cartItems.value = res.data?.data?.cart?.items ?? [];
        return res.data;
    }

    async function removeFromCart(itemId) {
        await window.axios.delete(`/api/v1/user/cart/${itemId}`);
        cartItems.value = cartItems.value.filter(i => i.id !== itemId);
    }

    return { cartItems, cartProductIds, cartTotal, cartCount, cartLoaded, getItemForProduct, getItemForProductVariant, loadCart, addToCart, updateQuantity, removeFromCart };
}
