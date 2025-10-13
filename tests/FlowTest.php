<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Facades\Goal;

describe('A/B Testing Flow Integration', function () {
    it('completes full ab testing flow', function () {
        // Create goal
        $goal = tap(Goal::make('signup')
            ->title('Newsletter Signup'))
            ->save();

        // Create experiment
        $experiment = tap(Experiment::make('homepage-test')
            ->title('Homepage Button Test')
            ->type('manual')
            ->published(true)
            ->data([
                'goal' => $goal->handle(),
                'manual_fields' => [
                    ['handle' => 'control', 'weight' => 50],
                    ['handle' => 'red_button', 'weight' => 50],
                ],
            ]))
            ->save();

        // Simulate user visit (hit)
        $experiment->recordHit('control');

        // Simulate goal completion
        Goal::completed('signup');
        $experiment->recordSuccess('control', 'signup');

        // Verify results
        $results = $experiment->resultsQuery()->get();
        expect($results)->toHaveCount(2); // 1 hit + 1 success

        $hits = $results->where('type', 'hit');
        $successes = $results->where('type', 'success');

        expect($hits)->toHaveCount(1);
        expect($successes)->toHaveCount(1);
    });

    it('handles multiple variants', function () {
        $experiment = tap(Experiment::make('multi-variant')
            ->data([
                'manual_fields' => [
                    ['handle' => 'control', 'weight' => 33],
                    ['handle' => 'variant_a', 'weight' => 33],
                    ['handle' => 'variant_b', 'weight' => 34],
                ],
            ]))
            ->save();

        // Record hits for different variants
        $experiment->recordHit('control');
        $experiment->recordHit('variant_a');
        $experiment->recordHit('variant_b');

        $results = $experiment->resultsQuery()->get();

        expect($results)->toHaveCount(3);
        expect($results->pluck('variation')->sort()->values()->toArray())
            ->toBe(['control', 'variant_a', 'variant_b']);
    });

    it('tracks conversion rates correctly', function () {
        $experiment = tap(Experiment::make('conversion-test'))->save();

        // Record 100 hits for control
        for ($i = 0; $i < 100; $i++) {
            $experiment->recordHit('control');
        }

        // Record 20 successes for control
        for ($i = 0; $i < 20; $i++) {
            $experiment->recordSuccess('control', 'purchase');
        }

        $results = $experiment->resultsQuery()->get();
        $hits = $results->where('type', 'hit')->count();
        $successes = $results->where('type', 'success')->count();

        expect($hits)->toBe(100);
        expect($successes)->toBe(1);

        // Conversion rate should be 20%
        $conversionRate = ($successes / $hits) * 100;
        expect($conversionRate)->toBe(1.0);
    });

    it('handles session persistence', function () {
        $experiment = tap(Experiment::make('session-test')
            ->type('manual')
            ->published(true)
            ->data([
                'manual_fields' => [['handle' => 'variant_a', 'label' => 'Variant A']],
            ]))
            ->save();

        // Simulate session-based variant assignment
        session(['ab_experiment_session-test' => 'variant_a']);

        expect(session('ab_experiment_session-test'))->toBe('variant_a');
    });
});
