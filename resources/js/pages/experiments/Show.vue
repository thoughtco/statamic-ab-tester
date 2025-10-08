<script setup>
defineProps({
    experiment: { type: Object, required: true },
    routes: { type: Object, required: true },
});
</script>

<template>
    <ui-header :title="experiment.title" icon="labs-idea-experimental-flask">
        <ui-button :href="routes.edit" class="btn-primary" v-text="__('Edit')" />
    </ui-header>

    <div>
        <ui-description class="mb-1" v-text="__('Results')" />

        <p>Need to show results</p>
    </div>
</template>
