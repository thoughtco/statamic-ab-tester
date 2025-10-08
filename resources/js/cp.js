//import ABExperimentResults from './components/ABExperimentResults'
import ABExperimentSetup from './components/ABExperimentSetup.vue';
import ExperimentFields from "./fieldtypes/ExperimentFields.vue";

Statamic.booting(() => {
    //Statamic.$components.register('ab-tester-experiment-results', ABExperimentResults);
    Statamic.$components.register('ab-tester-experiment-setup', ABExperimentSetup);

    Statamic.$components.register('ab_tester_experiment_fields-fieldtype', ExperimentFields);
});
