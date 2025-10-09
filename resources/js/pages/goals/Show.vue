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

    <ui-card>
        <ui-table>
            <ui-table-columns>
                <ui-table-column>{{ __('Experiment') }}</ui-table-column>
                <ui-table-column>Failures</ui-table-column>
                <ui-table-column>Successes</ui-table-column>
                <ui-table-column></ui-table-column>
            </ui-table-columns>
            <ui-table-rows>
                <ui-table-row>
                    <ui-table-cell>Mechanical Keyboard</ui-table-cell>
                    <ui-table-cell class="font-semibold text-black">1</ui-table-cell>
                    <ui-table-cell class="font-semibold text-black">10</ui-table-cell>
                    <ui-table-cell class="text-right">
                        <ui-button size="sm">{{ __('View') }}</ui-button>
                    </ui-table-cell>
                </ui-table-row>

                <ui-table-row class="opacity-50">
                    <ui-table-cell class="text-grey">Total</ui-table-cell>
                    <ui-table-cell class="font-semibold">1</ui-table-cell>
                    <ui-table-cell class="font-semibold text-secondary">10</ui-table-cell>
                    <ui-table-cell class="text-right">
                    </ui-table-cell>
                </ui-table-row>
            </ui-table-rows>
        </ui-table>
    </ui-card>

</template>
