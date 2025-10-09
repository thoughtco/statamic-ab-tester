<?php

return [

    /*
    * Config related to experiments
    */
    'experiments' => [

        /*
        * Experiments Path
        * Where your experiment YAML files are stored.
        */
        'path' => resource_path('ab-experiments/experiments'),

    ],

    /*
    * Config related to goals
    */
    'goals' => [

        /*
        * Goals Path
        * Where your goals YAML files are stored.
        */
        'path' => resource_path('ab-experiments/goals'),
    ],

    /*
    * Config related to results
    */
    'results' => [

        /*
        * Database connection to use, defaults to your app default
        */
        'database_connection' => env('AB_TESTER_RESULTS_CONNECTION'),
    ],

];
