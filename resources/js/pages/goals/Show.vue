<script setup>
defineProps({
    goal: { type: Object, required: true },
    routes: { type: Object, required: true },
});
</script>

<template>
    <ui-header :title="goal.title" icon="labs-idea-experimental-flask">
        <ui-button :href="routes.edit" class="btn-primary" v-text="__('Edit')" />
    </ui-header>

    <div>
        <p>Link to experiments using this Goal</p>
    </div>
</template>
