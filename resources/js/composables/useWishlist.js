import { ref } from 'vue';

// Module-level state — shared across all component instances
const wishlistProductIds = ref(new Set());
const wishlistLoaded = ref(false);

export function useWishlist() {
    const isWishlisted = (productId) => wishlistProductIds.value.has(productId);

    async function loadWishlist() {
        try {
            const res = await window.axios.get('/api/v1/user/wishlist');
            const items = Array.isArray(res.data.data) ? res.data.data : [];
            wishlistProductIds.value = new Set(items.map(i => i.product_id));
            wishlistLoaded.value = true;
        } catch {
            wishlistProductIds.value = new Set();
        }
    }

    async function toggleWishlist(productId) {
        const res = await window.axios.post('/api/v1/user/wishlist', { product_id: productId });
        const added = res.data.data?.added ?? false;
        const newSet = new Set(wishlistProductIds.value);
        if (added) {
            newSet.add(productId);
        } else {
            newSet.delete(productId);
        }
        wishlistProductIds.value = newSet;
        return res.data;
    }

    return { wishlistProductIds, wishlistLoaded, isWishlisted, loadWishlist, toggleWishlist };
}
