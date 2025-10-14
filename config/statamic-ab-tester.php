<?php

return [

    /*
    * Config related to experiments
    */
    'experiments' => [

        /*
        * Experiments Driver
        * file or eloquent
        */
        'driver' => 'eloquent',

        /*
        * Experiments Path
        * Where your experiment YAML files are stored when driver: file
        */
        'path' => resource_path('ab-experiments/experiments'),

    ],

    /*
     * Do you want fields to be available to test by default (opt-out) or by
     * selection only (opt-in)
     */
    'blueprint_fields_approach' => 'opt-out',

    /*
    * Config related to goals
    */
    'goals' => [

        /*
        * Goals Driver
        * file or eloquent
        */
        'driver' => 'eloquent',

        /*
        * Goals Path
        * Where your goals YAML files are stored when driver: file
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
