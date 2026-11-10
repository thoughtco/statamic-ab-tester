
<?php

use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Permission;
use Statamic\StaticCaching\StaticCacheManager;
use Thoughtco\StatamicABTester\Contracts\ExperimentRepository;
use Thoughtco\StatamicABTester\Contracts\GoalRepository;
use Thoughtco\StatamicABTester\StaticCaching\ABCacher;
use Thoughtco\StatamicABTester\Tests\TestCase;

uses(TestCase::class);

describe('Service Provider', function () {
    it('registers repositories', function () {
        expect(app()->bound(ExperimentRepository::class))->toBeTrue();
        expect(app()->bound(GoalRepository::class))->toBeTrue();
    });

    it('registers permissions', function () {
        $permissions = Permission::all();

        expect($permissions->has('create a/b experiments'))->toBeTrue();
        expect($permissions->has('create a/b goals'))->toBeTrue();
    });

    it('registers cache strategy', function () {
        $strategies = config('statamic.static_caching.strategies');

        expect($strategies)->toHaveKey('ab');
        expect($strategies['ab']['driver'])->toBe('ab');
    });

    it('uses the static_cache store for the cache strategy when the app defines one', function () {
        config(['cache.stores.static_cache' => ['driver' => 'array']]);

        $cacher = app(StaticCacheManager::class)->driver('ab');

        expect($cacher)->toBeInstanceOf(ABCacher::class)
            ->and((fn () => $this->cache)->call($cacher))->toBe(Cache::store('static_cache'));
    });

    it('uses the default store for the cache strategy otherwise', function () {
        $cacher = app(StaticCacheManager::class)->driver('ab');

        expect((fn () => $this->cache)->call($cacher))->toBe(Cache::store());
    });

    it('loads config correctly', function () {
        expect(config('statamic-ab-tester'))->toBeArray();
        expect(config('statamic-ab-tester.experiments.path'))->not->toBeEmpty();
        expect(config('statamic-ab-tester.goals.path'))->not->toBeEmpty();
    });
});
