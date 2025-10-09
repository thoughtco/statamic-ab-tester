<script setup>
import { ref } from "vue";

defineProps({
    experiment: { type: Object, required: true },
    hasResults: { type: Boolean, required: true, default: true },
    routes: { type: Object, required: true },
});

const showCompleteModal = ref(false);

const applyVariant = (variant) => {
    alert('apply me');
}
</script>

<template>
    <ui-header :title="experiment.title" icon="labs-idea-experimental-flask">
        <ui-button :href="routes.edit" class="btn-primary" v-text="__('Edit')" />
    </ui-header>

    <template v-if="! hasResults">
        <ui-description>{{ __('This experiment has no results.') }}</ui-description>
    </template>

    <template v-else>
        <ui-card>
            <header>
                <ui-heading size="lg">{{ __('Results by variation') }}</ui-heading>
            </header>
            <ui-table>
                <ui-table-columns>
                    <ui-table-column>{{ __('Variant') }}</ui-table-column>
                    <ui-table-column>{{ __('Hits') }}</ui-table-column>
                    <ui-table-column>{{ __('Failures') }}</ui-table-column>
                    <ui-table-column>{{ __('Successes') }}</ui-table-column>
                    <ui-table-column>{{ __('Success rate') }}</ui-table-column>
                </ui-table-columns>
                <ui-table-rows>
                    <ui-table-row>
                        <ui-table-cell>Mechanical Keyboard</ui-table-cell>
                        <ui-table-cell class="font-semibold text-black">11</ui-table-cell>
                        <ui-table-cell class="font-semibold text-black">1</ui-table-cell>
                        <ui-table-cell class="font-semibold text-black">10</ui-table-cell>
                        <ui-table-cell class="font-semibold text-black">90%</ui-table-cell>
                    </ui-table-row>
                </ui-table-rows>
            </ui-table>
        </ui-card>

        <div class="mt-4 flex flex-row gap-4">
            <ui-card class="w-1/2">
                <header>
                    <ui-heading size="lg">{{ __('Results by User') }}</ui-heading>
                </header>
                <ui-table>
                    <ui-table-columns>
                        <ui-table-column>{{ __('User') }}</ui-table-column>
                        <ui-table-column>{{ __('Hits') }}</ui-table-column>
                        <ui-table-column>{{ __('Success rate') }}</ui-table-column>
                    </ui-table-columns>
                    <ui-table-rows>
                        <ui-table-row>
                            <ui-table-cell>Mechanical Keyboard</ui-table-cell>
                            <ui-table-cell class="font-semibold text-black">11</ui-table-cell>
                            <ui-table-cell class="font-semibold text-black">90%</ui-table-cell>
                        </ui-table-row>
                    </ui-table-rows>
                </ui-table>
            </ui-card>

            <ui-card class="w-1/2">
                <header>
                    <ui-heading size="lg">{{ __('Results by IP Address') }}</ui-heading>
                </header>
                <ui-table>
                    <ui-table-columns>
                        <ui-table-column>{{ __('IP Address') }}</ui-table-column>
                        <ui-table-column>{{ __('Hits') }}</ui-table-column>
                        <ui-table-column>{{ __('Success rate') }}</ui-table-column>
                    </ui-table-columns>
                    <ui-table-rows>
                        <ui-table-row>
                            <ui-table-cell>Mechanical Keyboard</ui-table-cell>
                            <ui-table-cell class="font-semibold text-black">11</ui-table-cell>
                            <ui-table-cell class="font-semibold text-black">90%</ui-table-cell>
                        </ui-table-row>
                    </ui-table-rows>
                </ui-table>
            </ui-card>
        </div>

        <div class="mt-8 flex justify-center">
            <ui-button variant="primary" v-text="__('Complete Experiment')" @click="showCompleteModal = true" />
        </div>

        <ui-modal
            :title="__('Which variant do you want to apply?')"
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
                    <ui-table-row>
                        <ui-table-cell>Mechanical Keyboard</ui-table-cell>
                        <ui-table-cell class="font-semibold text-black">90%</ui-table-cell>
                        <ui-table-cell>
                            <ui-button size="sm" @click="applyVariant(1)">{{ __('Apply') }}</ui-button>
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
