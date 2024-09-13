<?php

namespace Thoughtco\StatamicABTester;

use Statamic\Facades\CP\Nav;
use Statamic\Facades\Stache;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        Tags\ABTags::class,
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $scripts = [
        __DIR__.'/../resources/dist/js/ab.js',
    ];

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ab');

        $this->mergeConfigFrom(__DIR__.'/../config/statamic-ab-tester.php', 'statamic-ab-tester');

        $this->publishes([
            __DIR__.'/../config/statamic-ab-tester.php' => config_path('statamic-ab-tester.php'),
        ], 'config');

        Nav::extend(function ($nav) {
            $nav->create(__('A/B Experiments'))
                ->section(__('Tools'))
                ->route('ab.experiments.index')
                ->active('ab/experiments')
                ->icon('color');
        });

        Stache::registerStore((new Experiment\Stache\ExperimentStore)->directory(config('statamic-ab-tester.experiments_path')));

        Statamic::repository(Contracts\ExperimentRepository::class, Experiment\Stache\ExperimentRepository::class);
    }
}
