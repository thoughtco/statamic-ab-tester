<script setup>
import axios from 'axios';
import { ref, watch } from "vue";
import { vConfetti } from '@neoconfetti/vue';
import { useWindowSize } from '@vueuse/core'

const { width, height } = useWindowSize()

const props = defineProps({
    experiment: { type: Object, required: true },
    hasResults: { type: Boolean, required: true, default: true },
    results: { type: Array, required: true, default: [] },
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
    <ui-header :title="experiment.title" icon="labs-idea-experimental-flask">
        <ui-button variant="primary" v-text="__('Complete Experiment')" @click="showCompleteModal = true" v-if="! experiment.completed_at" />

        <ui-button :href="routes.edit" class="btn-primary" v-text="__('Edit')" v-if="! experiment.completed_at" />

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
                            <ui-table-cell class="font-semibold text-black">{{ result.hits }}</ui-table-cell>
                            <ui-table-cell class="font-semibold text-black">{{ result.failed }}</ui-table-cell>
                            <ui-table-cell class="font-semibold text-black">{{ result.success }}</ui-table-cell>
                            <ui-table-cell class="font-semibold text-black">{{ result.rate.toFixed(2) }}%</ui-table-cell>
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
                                <ui-table-cell class="font-semibold text-black dark:text-white">{{ result.hits }}</ui-table-cell>
                                <ui-table-cell class="font-semibold text-black dark:text-white">{{ result.rate.toFixed(2) }}%</ui-table-cell>
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
                                <ui-table-cell class="font-semibold text-black dark:text-white">{{ result.hits }}</ui-table-cell>
                                <ui-table-cell class="font-semibold text-black dark:text-white">{{ result.rate.toFixed(2) }}%</ui-table-cell>
                            </ui-table-row>
                        </ui-table-rows>
                    </ui-table>
                </ui-card>
            </ui-panel>
        </div>

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
