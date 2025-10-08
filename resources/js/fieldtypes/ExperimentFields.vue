<script setup>
import { Fieldtype } from '@statamic/cms';
import {
    injectPublishContext,
    PublishContainer,
    PublishFieldsProvider as FieldsProvider,
    PublishFields,
} from '@statamic/cms/ui';
import { computed, ref, watch } from "vue";

const emit = defineEmits(Fieldtype.emits);
const props = defineProps({ ...Fieldtype.props, errors: { type: Object, required: false }});
const { expose, update } = Fieldtype.use(emit, props);
defineExpose(expose);

const context = injectPublishContext();

const fieldset = ref({ tabs: [{ fields: props.meta.abTester.fields }] });

const errors = ref(props.errors ?? {});
const selectedFields = ref(props.value?.fields ?? []);
const values = ref(props.value?.values ?? props.meta.abTester.values);

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

const valueErrors = computed(() => {
    let fieldErrs = {};
    Object.keys(errors.value).forEach(field => {
        if (field.indexOf('values.') === 0) {
            fieldErrs[field.replace('values.', '')] = errors.value[field];
        }
    });

    console.log('value errors', fieldErrs);

    return fieldErrs;
});

watch(selectedFields, updateValue);
watch(values, updateValue, { deep: true });

// on the edit page, not the action page
if (context) {

    watch(context.errors, () => {
        let fieldErrs = {};
        Object.keys(context.errors.value).forEach(field => {
            if (field.indexOf('experiment_fields.') === 0) {
                fieldErrs[field.replace('experiment_fields.', '')] = context.errors.value[field];
            }
        });

        errors.value = fieldErrs;
    });

}

watch(props.errors, () => {
    console.log('prop errors', props.errors);
})

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
            :errors="valueErrors"
        >
            <FieldsProvider :fields="props.meta.abTester.fields.filter(field => selectedFields.includes(field.handle))">
                <PublishFields />
            </FieldsProvider>
        </PublishContainer>
    </ui-card-panel>
</template>
