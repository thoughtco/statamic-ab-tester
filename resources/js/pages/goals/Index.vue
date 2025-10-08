<script setup>
defineProps({
    routes: { type: Object, required: true },
});
</script>

<template>

    <ui-header :title="__('Goals')" icon="favorite-trophy">
        <ui-button variant="primary" text="{{ __('Create') }}" :href="routes.create" />
    </ui-header>

    <ui-listing
        :url="routes.json"
        :action-url="routes.actions"
        preferences-prefix="ab.goals"
    >
        <template #cell-title="{ row }">
            <a class="title-index-field" :href="row.show_url" @click.stop>
                <span v-text="row.title" />
            </a>
        </template>
        <template #prepended-row-actions="{ row }">
            <ui-dropdown-item :text="__('View')" :href="row.show_url" icon="eye" />
            <ui-dropdown-item :text="__('Edit')" :href="row.edit_url" icon="edit" />
        </template>
    </ui-listing>

</template>
