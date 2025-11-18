import { inertia } from '@statamic/cms/api';
import ABExperimentSetup from './components/ABExperimentSetup.vue';
import ExperimentFields from "./fieldtypes/ExperimentFields.vue";

import ExperimentsCreatePage from './pages/experiments/Create.vue';
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

    inertia.register('abtester::Experiments.Create', ExperimentsCreatePage);
    inertia.register('abtester::Experiments.Edit', ExperimentsEditPage);
    inertia.register('abtester::Experiments.Index', ExperimentsIndexPage);
    inertia.register('abtester::Experiments.Show', ExperimentsShowPage);

    inertia.register('abtester::Goals.Create', GoalsCreatePage);
    inertia.register('abtester::Goals.Edit', GoalsEditPage);
    inertia.register('abtester::Goals.Index', GoalsIndexPage);
    inertia.register('abtester::Goals.Show', GoalsShowPage);
});
