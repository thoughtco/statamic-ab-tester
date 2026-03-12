<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Thoughtco\StatamicABTester\Facades\Experiment;

describe('Traffic split', function () {
    describe('item experiments', function () {
        it('always returns the control variant when split is 100', function () {
            $experiment = tap(Experiment::make('split-100')
                ->type('item')
                ->data(['traffic_split' => 100]))
                ->save();

            $variants = collect(range(1, 100))
                ->map(fn () => $experiment->chooseVariation(fromSession: false));

            expect($variants->unique()->values()->all())->toBe([1]);
        });

        it('always returns the test variant when split is 0', function () {
            $experiment = tap(Experiment::make('split-0')
                ->type('item')
                ->data(['traffic_split' => 0]))
                ->save();

            $variants = collect(range(1, 100))
                ->map(fn () => $experiment->chooseVariation(fromSession: false));

            expect($variants->unique()->values()->all())->toBe([2]);
        });

        it('distributes traffic roughly 50/50 with the default split', function () {
            $experiment = tap(Experiment::make('split-default')
                ->type('item'))
                ->save();

            $variants = collect(range(1, 1000))
                ->map(fn () => $experiment->chooseVariation(fromSession: false));

            $controlCount = $variants->filter(fn ($v) => $v === 1)->count();
            $variantCount = $variants->filter(fn ($v) => $v === 2)->count();

            // With 1000 trials at 50/50 both counts should be in a reasonable range
            expect($controlCount)->toBeGreaterThan(400);
            expect($variantCount)->toBeGreaterThan(400);
        });

        it('persists and retrieves the traffic split value', function () {
            $experiment = tap(Experiment::make('split-persist')
                ->type('item')
                ->data(['traffic_split' => 75]))
                ->save();

            expect($experiment->fresh()->get('traffic_split'))->toBe(75);
        });

        it('respects a 75/25 split over many trials', function () {
            $experiment = tap(Experiment::make('split-75')
                ->type('item')
                ->data(['traffic_split' => 75]))
                ->save();

            $variants = collect(range(1, 1000))
                ->map(fn () => $experiment->chooseVariation(fromSession: false));

            $controlCount = $variants->filter(fn ($v) => $v === 1)->count();

            // With 75% to control and 1000 trials, control should dominate clearly
            expect($controlCount)->toBeGreaterThan(600);
            expect($controlCount)->toBeLessThan(900);
        });
    });

    describe('manual experiments', function () {
        it('routes all traffic to the only weighted variant', function () {
            $experiment = tap(Experiment::make('manual-weighted')
                ->type('manual')
                ->data([
                    'manual_fields' => [
                        ['handle' => 'control', 'label' => 'Control', 'weight' => 100],
                        ['handle' => 'variant_a', 'label' => 'Variant A', 'weight' => 0],
                    ],
                ]))
                ->save();

            $variants = collect(range(1, 50))
                ->map(fn () => $experiment->chooseVariation(fromSession: false));

            expect($variants->unique()->values()->all())->toBe(['control']);
        });

        it('distributes traffic proportionally to weights', function () {
            $experiment = tap(Experiment::make('manual-proportional')
                ->type('manual')
                ->data([
                    'manual_fields' => [
                        ['handle' => 'control', 'label' => 'Control', 'weight' => 75],
                        ['handle' => 'variant_a', 'label' => 'Variant A', 'weight' => 25],
                    ],
                ]))
                ->save();

            $variants = collect(range(1, 1000))
                ->map(fn () => $experiment->chooseVariation(fromSession: false));

            $controlCount = $variants->filter(fn ($v) => $v === 'control')->count();

            // With 75% weight on control, it should clearly dominate
            expect($controlCount)->toBeGreaterThan(600);
            expect($controlCount)->toBeLessThan(900);
        });

        it('falls back to uniform random when no weights are set', function () {
            $experiment = tap(Experiment::make('manual-no-weights')
                ->type('manual')
                ->data([
                    'manual_fields' => [
                        ['handle' => 'control', 'label' => 'Control'],
                        ['handle' => 'variant_a', 'label' => 'Variant A'],
                    ],
                ]))
                ->save();

            $variants = collect(range(1, 200))
                ->map(fn () => $experiment->chooseVariation(fromSession: false));

            // Both variants should appear
            expect($variants->contains('control'))->toBeTrue();
            expect($variants->contains('variant_a'))->toBeTrue();
        });

        it('returns null when manual_fields is empty', function () {
            $experiment = tap(Experiment::make('manual-empty')
                ->type('manual'))
                ->save();

            expect($experiment->chooseVariation(fromSession: false))->toBeNull();
        });
    });

    describe('session stickiness', function () {
        it('returns the session variant regardless of the configured split', function () {
            $experiment = tap(Experiment::make('session-sticky')
                ->type('item')
                ->data(['traffic_split' => 100])) // always control without session
                ->save();

            // Simulate a session that has already assigned variant 2
            session(['statamic.ab.'.$experiment->id() => 2]);

            expect($experiment->chooseVariation(fromSession: true))->toBe(2);
        });

        it('ignores the session when fromSession is false', function () {
            $experiment = tap(Experiment::make('session-ignored')
                ->type('item')
                ->data(['traffic_split' => 100])) // always control
                ->save();

            session(['statamic.ab.'.$experiment->id() => 2]);

            // Split of 100 always returns 1, session is bypassed
            $variant = $experiment->chooseVariation(fromSession: false);
            expect($variant)->toBe(1);
        });
    });
});
