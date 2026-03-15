<script setup>
defineProps({
    eyebrow: { type: String, default: '' },
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>

<template>
    <section class="admin-page-wrapper">
        <header class="admin-page-wrapper__hero">
            <div class="admin-page-wrapper__copy">
                <p v-if="eyebrow" class="admin-page-wrapper__eyebrow">{{ eyebrow }}</p>
                <h1 class="admin-page-wrapper__title">{{ title }}</h1>
                <p v-if="description" class="admin-page-wrapper__description">{{ description }}</p>
            </div>
            <div v-if="$slots.actions" class="admin-page-wrapper__actions">
                <slot name="actions" />
            </div>
        </header>

        <div class="admin-page-wrapper__body">
            <slot />
        </div>
    </section>
</template>

<style scoped>
.admin-page-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.admin-page-wrapper__hero {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.55rem;
    border: 1px solid #d7dee8;
    border-radius: 1.3rem;
    background: #fff;
}

.admin-page-wrapper__copy {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.admin-page-wrapper__eyebrow {
    margin: 0;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #ff6c18;
}

.admin-page-wrapper__title {
    margin: 0;
    font-size: clamp(1.95rem, 2vw, 2.35rem);
    line-height: 1.1;
    font-weight: 700;
    color: #1f2d41;
}

.admin-page-wrapper__description {
    margin: 0;
    max-width: 47rem;
    font-size: 0.92rem;
    line-height: 1.6;
    color: #60728a;
}

.admin-page-wrapper__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    justify-content: flex-end;
}

.admin-page-wrapper__body {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .admin-page-wrapper__hero {
        flex-direction: column;
        padding: 1.35rem;
        border-radius: 1.35rem;
    }

    .admin-page-wrapper__actions {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>