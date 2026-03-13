<script setup>
import axios from 'axios';
import { ref, watch } from "vue";
import { vConfetti } from '@neoconfetti/vue';
import { useWindowSize } from '@vueuse/core'
import { Head } from '@statamic/cms/inertia';

const { width, height } = useWindowSize()

const props = defineProps({
    experiment: { type: Object, required: true },
    hasResults: { type: Boolean, required: true, default: true },
    results: { type: Object, required: true, default: [] },
    significance: { type: Object, default: null },
    routes: { type: Object, required: true },
});

const experiment = ref(props.experiment);
const showCompleteModal = ref(false);
const showConfetti = ref(false);

watch(showConfetti, (value) => {
    if (value) {
        setTimeout(() => {
            showConfetti.value = false;
        }, 4000);
    }
});

const applyVariant = async (variant) => {
    try {
        let response = await axios.post(props.routes.complete, {
            variant
        });

        showCompleteModal.value = false;
        showConfetti.value = true;

        if (response.data?.redirect) {
            location.href = response.data.redirect;

            return;
        }

        experiment.value = response.data.experiment;

        Statamic.$toast.success('Experiment completed!');
    } catch (error) {
        console.error(error);
        Statamic.$toast.error('Error completing experiment.');

        return;
    };
}

</script>

<template>
    <Head :title="__('View Experiment')" />

    <ui-header :title="experiment.title" icon="labs-idea-experimental-flask">
        <ui-button variant="primary" v-text="__('Complete Experiment')" @click="showCompleteModal = true" v-if="! experiment.completed_at" />

        <ui-button :href="routes.edit" v-text="__('Edit')" v-if="! experiment.completed_at" />

        <ui-button as="a" :href="routes.export" v-if="hasResults">{{ __('Export CSV') }}</ui-button>

        <ui-badge color="red" v-if="experiment.completed_at">Completed</ui-badge>
    </ui-header>

    <template v-if="! hasResults">
        <ui-description>{{ __('This experiment has no results.') }}</ui-description>
    </template>

    <template v-else>

        <ui-panel>
            <ui-panel-header class="flex items-center justify-between">
                <ui-heading :text=" __('Results by variation')"></ui-heading>
            </ui-panel-header>
            <ui-card>
                <ui-table>
                    <ui-table-columns>
                        <ui-table-column>{{ __('Variant') }}</ui-table-column>
                        <ui-table-column>{{ __('Hits') }}</ui-table-column>
                        <ui-table-column>{{ __('Failures') }}</ui-table-column>
                        <ui-table-column>{{ __('Successes') }}</ui-table-column>
                        <ui-table-column>{{ __('Success rate') }}</ui-table-column>
                    </ui-table-columns>
                    <ui-table-rows>
                        <ui-table-row v-for="result in results.variant">
                            <ui-table-cell class="flex flex-row"><ui-icon name="favorite-trophy" class="mr-2 w-4 h-4" v-if="experiment.completed_at && experiment.winner == result.id " />{{ result.label }}</ui-table-cell>
                            <ui-table-cell class="font-semibold">{{ result.hits }}</ui-table-cell>
                            <ui-table-cell class="font-semibold">{{ result.failed }}</ui-table-cell>
                            <ui-table-cell class="font-semibold">{{ result.success }}</ui-table-cell>
                            <ui-table-cell class="font-semibold">{{ result.rate.toFixed(2) }}%</ui-table-cell>
                        </ui-table-row>
                    </ui-table-rows>
                </ui-table>
            </ui-card>
        </ui-panel>

        <div class="mt-4 flex flex-row gap-4">
            <ui-panel class="w-1/2">
                <ui-panel-header class="flex items-center justify-between">
                    <ui-heading :text=" __('Results by user')"></ui-heading>
                </ui-panel-header>
                <ui-card>
                    <ui-table>
                        <ui-table-columns>
                            <ui-table-column>{{ __('User') }}</ui-table-column>
                            <ui-table-column>{{ __('Hits') }}</ui-table-column>
                            <ui-table-column>{{ __('Success rate') }}</ui-table-column>
                        </ui-table-columns>
                        <ui-table-rows>
                            <ui-table-row v-for="result in results.user">
                                <ui-table-cell>{{ result.label }}</ui-table-cell>
                                <ui-table-cell class="font-semibold">{{ result.hits }}</ui-table-cell>
                                <ui-table-cell class="font-semibold">{{ result.rate.toFixed(2) }}%</ui-table-cell>
                            </ui-table-row>
                        </ui-table-rows>
                    </ui-table>
                </ui-card>
            </ui-panel>

            <ui-panel class="w-1/2">
                <ui-panel-header class="flex items-center justify-between">
                    <ui-heading :text=" __('Results by IP address')"></ui-heading>
                </ui-panel-header>
                <ui-card>
                    <ui-table>
                        <ui-table-columns>
                            <ui-table-column>{{ __('IP Address') }}</ui-table-column>
                            <ui-table-column>{{ __('Hits') }}</ui-table-column>
                            <ui-table-column>{{ __('Success rate') }}</ui-table-column>
                        </ui-table-columns>
                        <ui-table-rows>
                            <ui-table-row v-for="result in results.ip">
                                <ui-table-cell>{{ result.label }}</ui-table-cell>
                                <ui-table-cell class="font-semibold">{{ result.hits }}</ui-table-cell>
                                <ui-table-cell class="font-semibold">{{ result.rate.toFixed(2) }}%</ui-table-cell>
                            </ui-table-row>
                        </ui-table-rows>
                    </ui-table>
                </ui-card>
            </ui-panel>
        </div>

        <ui-panel class="mt-2" v-if="results.variant.length >= 2">
            <ui-panel-header>
                <ui-heading :text="__('Statistical significance')"></ui-heading>
            </ui-panel-header>
            <ui-card>
                <template v-if="significance">
                    <div class="flex items-center gap-6 flex-wrap">
                        <div class="flex items-center gap-2">
                            <ui-badge :color="significance.is_significant ? 'green' : significance.confidence >= 80 ? 'yellow' : 'gray'">
                                {{ significance.is_significant ? __('Significant') : significance.confidence >= 80 ? __('Trending') : __('Not significant') }}
                            </ui-badge>
                            <ui-heading>{{ significance.confidence.toFixed(1) }}%</ui-heading>
                            <ui-description class="text-sm">{{ __('confidence') }}</ui-description>
                        </div>
                        <div class="flex flex-col gap-1">
                            <ui-description class="text-sm">
                                <span class="font-semibold">{{ significance.leader }}</span>
                                {{ __('is the current leader') }}
                                <template v-if="significance.uplift !== null">
                                    &mdash;
                                    <span :class="significance.uplift >= 0 ? 'text-green-600' : 'text-red-600'" class="font-semibold">
                                        {{ significance.uplift >= 0 ? '+' : '' }}{{ significance.uplift }}%
                                    </span>
                                    {{ __('relative to control') }}
                                </template>
                            </ui-description>
                            <ui-description v-if="! significance.is_significant">
                                {{ __('Not yet significant — collect more data before drawing conclusions.') }}
                            </ui-description>
                        </div>
                        <ui-description class="flex gap-4 ml-auto self-center">
                            <span>{{ __('Z-score') }}: <span class="font-mono">{{ significance.z_score }}</span></span>
                            <span>{{ __('P-value') }}: <span class="font-mono">{{ significance.p_value }}</span></span>
                        </ui-description>
                    </div>
                </template>
                <template v-else>
                    <p class="text-sm text-gray-500">{{ __('Insufficient data to calculate significance. Keep running the experiment to collect more results.') }}</p>
                </template>
            </ui-card>
        </ui-panel>

        <div class="absolute top-0 right-0 w-full h-full flex items-center justify-center" v-if="showConfetti">
            <div v-confetti="{ stageWidth: width, stageHeight: height, force: 1 }" />
        </div>

        <ui-modal
            :title="__('Which variant do you want to mark as the winner?')"
            :open="showCompleteModal"
            @update:open="showCompleteModal = $event"
        >

            <ui-table>
                <ui-table-columns>
                    <ui-table-column>{{ __('Variant') }}</ui-table-column>
                    <ui-table-column>{{ __('Success rate') }}</ui-table-column>
                    <ui-table-column></ui-table-column>
                </ui-table-columns>
                <ui-table-rows>
                    <ui-table-row v-for="result in results.variant">
                        <ui-table-cell>{{ result.label }}</ui-table-cell>
                        <ui-table-cell class="font-semibold text-black">{{ result.rate }}%</ui-table-cell>
                        <ui-table-cell>
                            <ui-button size="sm" @click="applyVariant(result.id)">{{ __('Apply') }}</ui-button>
                        </ui-table-cell>
                    </ui-table-row>
                </ui-table-rows>
            </ui-table>

            <template #footer>
                <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                    <ui-modal-close>
                        <ui-button text="Cancel" variant="ghost" />
                    </ui-modal-close>
                </div>
            </template>
        </ui-modal>
    </template>

</template>
