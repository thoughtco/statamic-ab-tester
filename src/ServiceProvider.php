<?php

namespace Thoughtco\StatamicABTester;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Facades\Stache;
use Statamic\Fields\Fieldtype;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;
use Statamic\StaticCaching\StaticCacheManager;
use Thoughtco\StatamicABTester\StaticCaching\ABCacher;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        Tags\ABTags::class,
    ];

    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $vite = [
        'input' => ['resources/js/cp.js'],
        'publicDirectory' => 'dist',
        'hotFile' => __DIR__.'/../dist/hot',
    ];

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ab');

        $this->mergeConfigFrom(__DIR__.'/../config/statamic-ab-tester.php', 'statamic-ab-tester');

        $this->publishes([
            __DIR__.'/../config/statamic-ab-tester.php' => config_path('statamic-ab-tester.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->createAddonNavigation()
            ->createAddonExperimentRepository()
            ->createAddonGoalRepository()
            ->createAddonPermissions()
            ->pushAddonMiddleware()
            ->extendFieldConfigs();
    }

    public function register()
    {
        $this->createAddonCacheStrategy();
    }

    private function createAddonNavigation()
    {
        Nav::extend(function ($nav) {
            $nav->create(__('Experiments'))
                ->section(__('A/B Experiments'))
                ->route('ab.experiments.index')
                ->icon('labs-idea-experimental-flask');

            $nav->create(__('Goals'))
                ->section(__('A/B Experiments'))
                ->route('ab.goals.index')
                ->icon('favorite-trophy');
        });

        return $this;
    }

    private function createAddonExperimentRepository()
    {
        Stache::registerStore((new Experiment\Stache\ExperimentStore)->directory(config('statamic-ab-tester.experiments.path')));

        Statamic::repository(Contracts\ExperimentRepository::class, Experiment\Stache\ExperimentRepository::class);

        return $this;
    }

    private function createAddonGoalRepository()
    {
        Stache::registerStore((new Goal\Stache\GoalStore)->directory(config('statamic-ab-tester.goals.path')));

        Statamic::repository(Contracts\GoalRepository::class, Goal\Stache\GoalRepository::class);

        return $this;
    }

    private function createAddonPermissions()
    {
        Permission::group('ab-tester', 'A/B Tester', function () {
            Permission::register('create a/b experiments')
                ->label(__('Create Experiments'))
                ->description(__('Enable the action on item views to create experiments.'));

            Permission::register('create a/b goals')
                ->label(__('Create Goals'))
                ->description(__('Enable the action on item views to create experiments.'));
        });

        return $this;
    }

    private function createAddonCacheStrategy()
    {
        config()->set('statamic.static_caching.strategies.ab', [
            'driver' => 'ab',
        ]);

        app(StaticCacheManager::class)->extend('ab', function ($app, $config) {
            return new ABCacher($app[Repository::class], $config);
        });

        return $this;
    }

    private function pushAddonMiddleware()
    {
        Route::prependMiddlewareToGroup('web', Http\Middleware\ABTesterMiddleware::class);

        return $this;
    }

    private function extendFieldConfigs()
    {
        Fieldtype::appendConfigField('ab_tester_enable', [
            'type' => 'toggle',
            'display' => __('Allow this field to be A/B tested'),
            'default' => config('statamic-ab-tester.blueprint_fields_approach') == 'opt-out',
        ]);

        return $this;
    }
}
