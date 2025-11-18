<script setup>
import { Head } from '@statamic/cms/inertia';

defineProps({
    blueprint: { type: Object, required: true },
    experiment: { type: Object, required: true },
    meta: { type: Object, required: true },
    routes: { type: Object, required: true },
    values: { type: Object, required: true },
});
</script>

<template>
    <Head :title="__('Edit Experiment')" />

    <ui-publish-form
        ref="container"
        name="experiment-form"
        :title="experiment.title"
        :blueprint="blueprint"
        :initial-values="values"
        :initial-meta="meta"
        :submit-url="routes.submit"
    >
    </ui-publish-form>
</template>
