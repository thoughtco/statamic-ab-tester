<script setup>
import { Head } from '@statamic/cms/inertia';

defineProps({
    experimentsIsEmpty: { type: Boolean, required: true },
    routes: { type: Object, required: true },
});
</script>

<template>
    <template v-if="! experimentsIsEmpty">

        <Head :title="__('Experiments')" />

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
                <ui-dropdown-item :text="__('Edit')" :href="row.edit_url" icon="edit" v-if="! row.is_complete" />
            </template>
        </ui-listing>

    </template>

    <template v-else>
        <Head :title="__('Experiments')" />

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
                :description="__('Goals are used to mark experiments as successes or failures. Make sure you add at last one and update your code to trigger the goal.')"
            />
            <ui-empty-state-item
                icon="alt"
                :heading="__('Add an A/B experiment')"
                :description="__('A/B experiments can be added on entries using the Create A/B Experiment action on the view page.')"
            />
            <ui-empty-state-item
                :href="routes.create"
                icon="labs-idea-experimental-flask"
                :heading="__('Add a manual experiment')"
                :description="__('Manual experiments require you to update your template code, for example to show a different navigation menu.')"
            />
        </ui-empty-state-menu>

    </template>
</template>
