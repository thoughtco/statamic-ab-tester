<script setup>
defineProps({
    blueprint: { type: Object, required: true },
    goal: { type: Object, required: true },
    meta: { type: Object, required: true },
    routes: { type: Object, required: true },
    values: { type: Object, required: true },
});
</script>

<template>
    <ui-publish-form
        ref="container"
        name="goal-form"
        :title="goal.title"
        :blueprint="blueprint"
        :initial-values="values"
        :initial-meta="meta"
        :submit-url="routes.submit"
        submit-method="PATCH"
    >
    </ui-publish-form>
</template>
