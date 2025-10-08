<script setup>
import { Fieldtype } from '@statamic/cms';
import {
    PublishContainer,
    PublishFieldsProvider as FieldsProvider,
    PublishFields,
} from '@statamic/cms/ui';
import { computed, ref, watch } from "vue";

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update } = Fieldtype.use(emit, props);
defineExpose(expose);

const fieldset = ref({ tabs: [{ fields: props.meta.abTester.fields }] });

const errors = ref({});
const selectedFields = ref(props.value?.fields ?? []);
const values = ref(props.value?.values ?? props.meta.abTester.values);

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
    return props.meta.abTester.fields
        .map(field => {
            return {
                label: field.display,
                value: field.handle
            };
        });
});

const updateValue = () => {
    let sendValues = {};
    for (let field of selectedFields.value) {
        sendValues[field] = values.value[field];
    }

    update({
        fields: selectedFields.value,
        values: sendValues,
    });
}

watch(selectedFields, updateValue);
watch(values, updateValue);
</script>

<template>
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
            :meta="props.meta.abTester.meta"
            :errors="fieldErrors"
        >
            <FieldsProvider :fields="props.meta.abTester.fields.filter(field => selectedFields.includes(field.handle))">
                <PublishFields />
            </FieldsProvider>
        </PublishContainer>
    </ui-card-panel>
</template>
