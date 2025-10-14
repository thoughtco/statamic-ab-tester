<script setup>
defineProps({
    experimentsIsEmpty: { type: Boolean, required: true },
    routes: { type: Object, required: true },
});
</script>

<template>
    <template v-if="! experimentsIsEmpty">

        <ui-header :title="__('A/B Experiments')" icon="labs-idea-experimental-flask">
            <ui-button variant="primary" :text="__('Create')" :href="routes.create" />
        </ui-header>

        <ui-listing
            :url="routes.json"
            :action-url="routes.actions"
            preferences-prefix="ab.experiments"
        >
            <template #cell-title="{ row }">
                <a class="title-index-field" :href="row.show_url" @click.stop>
                    <ui-status-indicator :status="row.published" />
                    <span v-text="row.title" />
                </a>
            </template>
            <template #prepended-row-actions="{ row }">
                <ui-dropdown-item :text="__('View')" :href="row.show_url" icon="eye" />
                <ui-dropdown-item :text="__('Edit')" :href="row.edit_url" icon="edit" v-if="! row.completed_at" />
            </template>
        </ui-listing>

    </template>

    <template v-else>
        <header class="py-8 mt-8 text-center starting-style-transition" v-cloak>
            <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-3">
                <span>A/B Experiments</span>
            </h1>
        </header>

        <ui-empty-state-menu :heading="__('Add your first experiment with these easy steps')">
            <ui-empty-state-item
                :href="routes.goal_create"
                icon="favorite-trophy"
                :heading="__('Add a goal')"
                :description="__('statamic::messages.collection_next_steps_configure_description')"
            />
            <ui-empty-state-item
                icon="labs-idea-experimental-flask"
                :heading="__('Add an A/B version')"
                :description="__('statamic::messages.collection_next_steps_configure_description')"
            />
        </ui-empty-state-menu>

    </template>
</template>
