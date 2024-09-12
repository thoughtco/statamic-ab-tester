<?php

namespace Thoughtco\ABTester;

use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;

class ABTesterServiceProvider extends AddonServiceProvider
{
    // protected $fieldtypes = [
    //     \Thoughtco\ABTester\Fieldtypes\ABFieldtype::class,
    // ];

    protected $tags = [
        Tags\ABTags::class,
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
        'web' => __DIR__.'/../routes/web.php',
    ];

    protected $scripts = [
        __DIR__.'/../resources/dist/js/ab.js',
        // __DIR__.'/../resources/dist/js/ab-fieldtype.js',
    ];

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ab');

        $this->mergeConfigFrom(__DIR__.'/../config/ab-tester.php', 'ab');

        $this->publishes([
            __DIR__.'/../config/ab-tester.php' => config_path('ab-tester.php'),
        ], 'config');

        Nav::extend(function ($nav) {
            $nav->create(__('A/B Experiments'))
                ->section(__('Tools'))
                ->route('ab.experiments.index')
                ->active('ab/experiments')
                ->icon('color');
        });
    }

    //    protected function schedule($schedule)
    //    {
    //        $schedule->call(function () {
    //            Experiment::all()->each->persistResults();
    //        })->everyMinute(); // increase to every 5/10/15 min?
    //    }
}
