<script setup>
    import axios from 'axios';
    import {computed, useTemplateRef, ref, watch} from 'vue';
    import ExperimentFields from '../fieldtypes/ExperimentFields.vue';

    const props = defineProps({
        action: { type: Object, required: true },
    });

    const action = props.action;

    const experimentFields = ref({ fields: [], values: {}});
    const errors = ref({});
    const selectedGoals = ref([]);
    const title = ref('');

    const fieldErrors = computed(() => {
       let fieldErrs = {};
        Object.keys(errors.value).forEach(field => {
           if (field.indexOf('experiment_fields.') === 0) {
               fieldErrs[field.replace('experiment_fields.', '')] = errors.value[field];
           }
       });

       return fieldErrs;
    });

    const createExperiment = async () => {
        let data = {
            item_id: action.abTester.item_id,
            experiment_fields: experimentFields.value,
            goals: selectedGoals.value,
            title: title.value,
            type: 'item',
        };

        let response;

        try {
            response = await axios.post(action.abTester.route, data);

            if (response.data?.redirect) {
                location.href = response.data.redirect;

                return;
            }

            Statamic.$toast.success('Experiment created successfully.');
        } catch (error) {
            errors.value = error.response.data?.errors ?? {};

            Statamic.$toast.error('Error creating experiment.');

            return;
        };
    };

    const hasExperimentFields = computed(() => {
        return experimentFields.value.fields.length > 0;
    });

    const hasGoals = computed(() => {
        return selectedGoals.value.length > 0;
    });
</script>

<template>
    <div class="mt-4">

        <template v-if="action.abTester.exists">
            <ui-description>An A/B experiment on this item already exists and is not yet complete. <a :href="action.abTester.exists">You can view it here</a>.</ui-description>
        </template>

        <template v-else>
            <ui-description class="mb-4">To setup your A/B Experiment, select the fields you want to vary and enter the alternative values:</ui-description>

            <ExperimentFields
                :errors="fieldErrors"
                :meta="{ abTester: action.abTester }"
                @update:value="experimentFields = $event;"
                ref="experiment-field"
            ></ExperimentFields>

            <ui-field class="mt-4" v-if="hasExperimentFields" :error="errors.goals ?? ''">
                <ui-label>Select goal(s):</ui-label>

                <ui-combobox
                    label="Select a goal"
                    :options="action.abTester.goals"
                    class="w-full"
                    v-model="selectedGoals"
                    :clearable="true"
                    :multiple="true"
                    :closeOnSelect="true"
                />
            </ui-field>

            <ui-field class="mt-8" :error="errors.title ?? ''" v-if="hasExperimentFields && hasGoals">
                <ui-label>Now, give your experiment a name:</ui-label>

                <ui-input
                    label="Name your experiment"
                    class="w-full"
                    v-model="title"
                />
            </ui-field>

            <div class="flex w-full justify-center mt-4">
                <ui-button text="Setup Experiment" variant="primary" v-if="hasExperimentFields && hasGoals && title" class="mt-4 mb-8" @click="createExperiment" />
            </div>

        </template>
    </div>
</template>
