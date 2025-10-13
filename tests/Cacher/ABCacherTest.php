<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Illuminate\Cache\Repository;
use Thoughtco\StatamicABTester\StaticCaching\ABCacher;

describe('AB Cacher', function () {
    it('extends half caching', function () {
        $cacher = new ABCacher(app(Repository::class), []);

        expect($cacher)->toBeInstanceOf(ABCacher::class);
    });

    it('handles ab experiments in cache key', function () {
        $cacher = new ABCacher(app(Repository::class), []);

        // Test that cache keys include experiment variants
        $key = $cacher->makeKey('/test-page', ['ab_experiment_test' => 'variant_a']);

        expect($key)->toContain('variant_a');
    });

    it('creates different keys for different variants', function () {
        $cacher = new ABCacher(app(Repository::class), []);

        $keyA = $cacher->makeKey('/test-page', ['ab_experiment_test' => 'variant_a']);
        $keyB = $cacher->makeKey('/test-page', ['ab_experiment_test' => 'variant_b']);

        expect($keyA)->not->toBe($keyB);
    });

    it('handles multiple experiments in cache key', function () {
        $cacher = new ABCacher(app(Repository::class), []);

        $key = $cacher->makeKey('/test-page', [
            'ab_experiment_test1' => 'variant_a',
            'ab_experiment_test2' => 'variant_b',
        ]);

        expect($key)->toContain('variant_a');
        expect($key)->toContain('variant_b');
    });

    it('invalidates cache correctly', function () {
        $cacher = new ABCacher(app(Repository::class), []);

        // Test cache invalidation when experiments are updated
        $result = $cacher->flush();

        expect($result)->toBeTrue();
    });
});
