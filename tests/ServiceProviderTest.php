
<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Thoughtco\StatamicABTester\Contracts\ExperimentRepository;
use Thoughtco\StatamicABTester\Contracts\GoalRepository;

describe('Service Provider', function () {
    it('registers navigation items', function () {
        $nav = Nav::build();

        expect($nav->has('Experiments'))->toBeTrue();
        expect($nav->has('Goals'))->toBeTrue();

        $experimentsItem = $nav->findByTitle('Experiments');
        expect($experimentsItem->route())->toBe('ab.experiments.index');
        expect($experimentsItem->icon())->toBe('labs-idea-experimental-flask');
    });

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

    it('loads config correctly', function () {
        expect(config('statamic-ab-tester'))->toBeArray();
        expect(config('statamic-ab-tester.experiments.path'))->not->toBeEmpty();
        expect(config('statamic-ab-tester.goals.path'))->not->toBeEmpty();
    });
});
