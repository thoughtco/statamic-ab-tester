<script setup>
defineProps({
    goal: { type: Object, required: true },
    hasResults: { type: Boolean, required: true, default: true },
    results: { type: Array, required: true, default: [] },
    routes: { type: Object, required: true },
});
</script>

<template>
    <ui-header :title="goal.title" icon="labs-idea-experimental-flask">
        <ui-button :href="routes.edit" class="btn-primary" v-text="__('Edit')" />
    </ui-header>

    <template v-if="! hasResults">
        <ui-description>{{ __('This goal is not attached to any experiments.') }}</ui-description>
    </template>

    <template v-else>
        <ui-card>
            <header>
                <ui-heading size="lg">Experiments using this goal</ui-heading>
            </header>
            <ui-table>
                <ui-table-columns>
                    <ui-table-column>{{ __('Experiment') }}</ui-table-column>
                    <ui-table-column>Failures</ui-table-column>
                    <ui-table-column>Successes</ui-table-column>
                    <ui-table-column></ui-table-column>
                </ui-table-columns>
                <ui-table-rows>
                    <ui-table-row v-for="result in results.experiments">
                        <ui-table-cell>{{ result.label }}</ui-table-cell>
                        <ui-table-cell class="font-semibold text-black dark:text-white">{{ result.hits }}</ui-table-cell>
                        <ui-table-cell class="font-semibold text-black dark:text-white">{{ result.failed }}</ui-table-cell>
                        <ui-table-cell class="font-semibold text-black dark:text-white">{{ result.success }}</ui-table-cell>
                        <ui-table-cell class="font-semibold text-black dark:text-white">{{ result.rate }}%</ui-table-cell>
                    </ui-table-row>

                    <ui-table-row class="opacity-50">
                        <ui-table-cell class="text-grey dark:text-white">Total</ui-table-cell>
                        <ui-table-cell class="font-semibold">{{ results.experimentsTotal.hits }}</ui-table-cell>
                        <ui-table-cell class="font-semibold">{{ results.experimentsTotal.failed }}</ui-table-cell>
                        <ui-table-cell class="font-semibold text-secondary">{{ results.experimentsTotal.success }}</ui-table-cell>
                        <ui-table-cell class="font-semibold text-secondary">{{ results.experimentsTotal.rate }}%</ui-table-cell>
                    </ui-table-row>
                </ui-table-rows>
            </ui-table>
        </ui-card>
    </template>

</template>
