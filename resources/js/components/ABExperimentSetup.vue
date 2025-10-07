<script setup>
    import axios from 'axios';
    import { computed, ref } from 'vue';

    import {
        PublishContainer,
        PublishFieldsProvider as FieldsProvider,
        PublishFields,
    } from '@statamic/cms/ui';

    const props = defineProps({
        action: { type: Object, required: true },
    });

    const action = props.action;

    const fieldset = ref({ tabs: [{ fields: props.action.fields }] });

    const errors = ref({});
    const selectedFields = ref([]);
    const selectedGoals = ref([]);
    const title = ref('');
    const values = ref(props.action.ab_tester.values);

    const fieldErrors = computed(() => {
       let fieldErrs = {};
        Object.keys(errors.value).forEach(field => {
           if (field.indexOf('values.') === 0) {
               fieldErrs[field.replace('values.', '')] = errors.value[field];
           }
       });

       return fieldErrs;
    });

    const selectableFields = computed(() => {
        return action.ab_tester.fields
            .map(field => {
                return {
                    label: field.display,
                    value: field.handle
                };
            });
    });

    const createExperiment = async () => {

        let sendValues = {};
        for (let field of selectedFields.value) {
            sendValues[field] = values.value[field];
        }

        const data = {
            entry_id: action.ab_tester.entry_id,
            fields: selectedFields.value,
            goals: selectedGoals.value,
            title: title.value,
            values: sendValues,
        };

        try {
            let response = await axios.post(action.ab_tester.route, data);
        } catch (error) {
            errors.value = error.response.data?.errors ?? {};

            Statamic.$toast.error('Error creating experiment.');

            return;
        }

        response = await response.json();

        if (response.redirect) {
            location.href = response.redirect;

            return;
        }

        Statamic.$toast.success('Experiment created successfully.');
    }
</script>

<template>
    <div class="mt-4">

        <template v-if="action.ab_tester.exists">
            <ui-description>An A/B experiment on this item already exists and is not yet complete. <a :href="action.ab_tester.exists">You can view it here</a>.</ui-description>
        </template>

        <template v-else>
            <ui-description>To setup your A/B Experiment, select the fields you want to vary and enter the alternative values:</ui-description>

            <ui-field class="mt-4" :error="errors.fields ?? ''">
                <ui-label>Select field(s):</ui-label>

                <ui-combobox
                    label="Select a field"
                    :options="selectableFields"
                    class="w-full"
                    v-model="selectedFields"
                    :clearable="true"
                    :multiple="true"
                    :closeOnSelect="true"
                />
            </ui-field>

            <ui-card-panel class="mt-8" v-if="selectedFields.length" heading="Alternative values">
                <PublishContainer
                    name="ab-tester-action"
                    :blueprint="fieldset"
                    v-model="values"
                    :meta="action.meta"
                    :errors="fieldErrors"
                >
                    <FieldsProvider :fields="action.ab_tester.fields.filter(field => selectedFields.includes(field.handle))">
                        <PublishFields />
                    </FieldsProvider>
                </PublishContainer>
            </ui-card-panel>

            <ui-field class="mt-4" v-if="selectedFields.length" :error="errors.goals ?? ''">
                <ui-label>Select goal(s):</ui-label>

                <ui-combobox
                    label="Select a goal"
                    :options="action.ab_tester.goals"
                    class="w-full"
                    v-model="selectedGoals"
                    :clearable="true"
                    :multiple="true"
                    :closeOnSelect="true"
                />
            </ui-field>

            <ui-field class="mt-8" :error="errors.title ?? ''" v-if="selectedFields.length && selectedGoals.length">
                <ui-label>Now, give your experiment a name:</ui-label>

                <ui-input
                    label="Name your experiment"
                    class="w-full"
                    v-model="title"

                />
            </ui-field>

            <ui-button text="Setup Experiment" variant="primary" v-if="selectedFields.length && selectedGoals.length && title" class="mt-4 mb-8" @click="createExperiment" />

        </template>
    </div>
</template>
