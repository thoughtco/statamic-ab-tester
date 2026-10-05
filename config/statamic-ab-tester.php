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
        'driver' => 'file',

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
        'driver' => 'file',

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

    /*
    * Config related to visitors
    */
    'visitor' => [

        /*
        * Visitors are identified by a random ID held in their session, so by
        * default a visitor is remembered for as long as their session lasts.
        *
        * Set a cookie name here to also store the ID in a cookie, so returning
        * visitors keep their variant and are only counted once. You may need
        * to cover this cookie in your consent banner.
        */
        'cookie' => env('AB_TESTER_VISITOR_COOKIE'),

        /*
        * How long the visitor cookie lasts, in minutes
        */
        'cookie_lifetime' => 60 * 24 * 30,
    ],

];
