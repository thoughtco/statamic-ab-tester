//import ABExperimentResults from './components/ABExperimentResults'
import ABExperimentSetup from './components/ABExperimentSetup.vue';

Statamic.booting(() => {
    //Statamic.$components.register('ab-tester-experiment-results', ABExperimentResults);
    Statamic.$components.register('ab-tester-experiment-setup', ABExperimentSetup)
});
