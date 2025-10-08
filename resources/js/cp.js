//import ABExperimentResults from './components/ABExperimentResults'
import ABExperimentSetup from './components/ABExperimentSetup.vue';
import ExperimentFields from "./fieldtypes/ExperimentFields.vue";

import ExperimentsEditPage from './pages/experiments/Edit.vue';
import ExperimentsIndexPage from './pages/experiments/Index.vue';
import ExperimentsShowPage from './pages/experiments/Show.vue';

import GoalsCreatePage from './pages/goals/Create.vue';
import GoalsEditPage from './pages/goals/Edit.vue';
import GoalsIndexPage from './pages/goals/Index.vue';
import GoalsShowPage from './pages/goals/Show.vue';


Statamic.booting(() => {
    //Statamic.$components.register('ab-tester-experiment-results', ABExperimentResults);
    Statamic.$components.register('ab-tester-experiment-setup', ABExperimentSetup);

    Statamic.$components.register('ab_tester_experiment_fields-fieldtype', ExperimentFields);

    Statamic.$components.register('Pages/AB/Experiments/Edit', ExperimentsEditPage);
    Statamic.$components.register('Pages/AB/Experiments/Index', ExperimentsIndexPage);
    Statamic.$components.register('Pages/AB/Experiments/Show', ExperimentsShowPage);

    Statamic.$components.register('Pages/AB/Goals/Create', GoalsCreatePage);
    Statamic.$components.register('Pages/AB/Goals/Edit', GoalsEditPage);
    Statamic.$components.register('Pages/AB/Goals/Index', GoalsIndexPage);
    Statamic.$components.register('Pages/AB/Goals/Show', GoalsShowPage);
});
