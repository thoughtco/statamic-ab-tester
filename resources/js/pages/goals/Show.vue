<script setup>
import { Head } from '@statamic/cms/inertia';

defineProps({
    goal: { type: Object, required: true },
    hasResults: { type: Boolean, required: true, default: true },
    results: { type: Array, required: true, default: [] },
    routes: { type: Object, required: true },
});
</script>

<template>
    <Head :title="__('View Goal')" />

    <ui-header :title="goal.title" icon="labs-idea-experimental-flask">
        <ui-button :href="routes.edit" class="btn-primary" v-text="__('Edit')" />
    </ui-header>

    <template v-if="! hasResults">
        <ui-description>{{ __('This goal is not attached to any experiments.') }}</ui-description>
    </template>

    <template v-else>
        <ui-panel>
            <ui-panel-header class="flex items-center justify-between">
                <ui-heading :text="__('Experiments using this goal')"></ui-heading>
            </ui-panel-header>
            <ui-card>
                <ui-table>
                    <ui-table-columns>
                        <ui-table-column>{{ __('Experiment') }}</ui-table-column>
                        <ui-table-column>Hits</ui-table-column>
                        <ui-table-column>Failures</ui-table-column>
                        <ui-table-column>Successes</ui-table-column>
                        <ui-table-column>Success Rate</ui-table-column>
                        <ui-table-column></ui-table-column>
                        <ui-table-column></ui-table-column>
                    </ui-table-columns>
                    <ui-table-rows>
                        <ui-table-row v-for="result in results.experiments">
                            <ui-table-cell>{{ result.label }}</ui-table-cell>
                            <ui-table-cell class="font-semibold">{{ result.hits }}</ui-table-cell>
                            <ui-table-cell class="font-semibold">{{ result.failed }}</ui-table-cell>
                            <ui-table-cell class="font-semibold">{{ result.success }}</ui-table-cell>
                            <ui-table-cell class="font-semibold">{{ result.rate.toFixed(2) }}%</ui-table-cell>
                            <ui-table-cell class="w-16">
                                <a :href="result.show_url" class="flex flex-row gap-1 items-center opacity-50">
                                    <ui-icon :text="__('View')" name="eye" />
                                    View
                                </a>
                            </ui-table-cell>
                            <ui-table-cell><ui-badge :color="result.status.color">{{  result.status.label }}</ui-badge></ui-table-cell>
                        </ui-table-row>

                        <ui-table-row class="opacity-50">
                            <ui-table-cell class="text-grey dark:text-white">Total</ui-table-cell>
                            <ui-table-cell class="font-semibold">{{ results.experimentsTotal.hits }}</ui-table-cell>
                            <ui-table-cell class="font-semibold">{{ results.experimentsTotal.failed }}</ui-table-cell>
                            <ui-table-cell class="font-semibold text-secondary">{{ results.experimentsTotal.success }}</ui-table-cell>
                            <ui-table-cell class="font-semibold text-secondary">{{ results.experimentsTotal.rate.toFixed(2) }}%</ui-table-cell>
                        </ui-table-row>
                    </ui-table-rows>
                </ui-table>
            </ui-card>
        </ui-panel>
    </template>

</template>
